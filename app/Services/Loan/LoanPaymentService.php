<?php

namespace App\Services\Loan;

use App\Exceptions\Loan\LoanGatewayException;
use App\Exceptions\Loan\LoanInvalidPaymentAmountException;
use App\Exceptions\Loan\LoanPendingPaymentExistsException;
use App\Exceptions\Loan\LoanScheduleAlreadyPaidException;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\LoanSchedule;
use App\Models\PaymentMethod;
use App\Models\Status;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Support\Facades\DB;
use Throwable;

class LoanPaymentService
{
    public function pay(Loan $loan, array $data): array
    {
        $paymentMethod = PaymentMethod::findOrFail($data['payment_method_id']);

        return DB::transaction(function () use ($loan, $data, $paymentMethod) {
            $schedule = $this->lockAndValidateSchedule($loan, $data['loan_schedule_id']);
            $payment = $this->createPendingPayment($loan, $schedule, $data);

            if ($paymentMethod->isOffline()) {
                return $this->settleOffline($payment, $schedule);
            }

            return $this->processGatewayPayment($payment, $paymentMethod, $data);
        });
    }

    /**
     * Summary of lockAndValidateSchedule
     *
     * @param Loan $loan
     * @param int $scheduleId
     * @throws LoanScheduleAlreadyPaidException
     * @throws LoanPendingPaymentExistsException
     * @return LoanSchedule|\stdClass
     */
    private function lockAndValidateSchedule(Loan $loan, int $scheduleId): LoanSchedule
    {
        $schedule = LoanSchedule::query()
            ->where('id', $scheduleId)
            ->where('loan_id', $loan->id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($schedule->status_id === Status::PAID) {
            throw new LoanScheduleAlreadyPaidException($schedule);
        }

        if ($this->hasPendingPayment($schedule)) {
            throw new LoanPendingPaymentExistsException($schedule);
        }

        return $schedule;
    }

    private function validateAmount(LoanSchedule $schedule, float $amount): void
    {
        $paid = (float) $schedule->payments()
            ->where('status_id', Status::SUCCESS)
            ->sum('amount');

        $remaining = round($schedule->total_payment - $paid, 2);

        if ($remaining <= 0) {
            throw new LoanScheduleAlreadyPaidException($schedule);
        }

        if ((int) round($amount * 100) !== (int) round($remaining * 100)) {
            throw new LoanInvalidPaymentAmountException(required: $remaining, provided: $amount);
        }
    }

    private function createPendingPayment(Loan $loan, LoanSchedule $schedule, array $data): Payment
    {
        $this->validateAmount($schedule, (float) $data['amount']);

        return $schedule->payments()->create([
            'payment_method_id' => $data['payment_method_id'],
            'payment_date' => now(),
            'amount' => $data['amount'],
            'gateway' => $data['gateway'] ?? 'paymongo',
            'status_id' => Status::PENDING,
        ]);
    }

    private function settleOffline(Payment $payment, LoanSchedule $schedule): array
    {
        $payment->update(['status_id' => Status::SUCCESS]);
        $schedule->update(['status_id' => Status::PAID]);

        return ['payment' => $payment, 'next_action' => null];
    }

    private function processGatewayPayment(Payment $payment, PaymentMethod $paymentMethod, array $data): array
    {
        try {
            $gateway = PaymentGatewayFactory::make($data['gateway'] ?? 'paymongo');
            $gatewayMethodId = $this->resolveGatewayMethodId($gateway, $paymentMethod, $data);

            $intent = $gateway->createPaymentIntent($data['amount']);
            $intentId = data_get($intent, 'data.id')
                ?? throw LoanGatewayException::failedToCreatePaymentIntent(
                    data_get($intent, 'errors')
                );

            $attached = $gateway->attach($intentId, $gatewayMethodId);

            $payment->update([
                'gateway_payment_intent_id' => $intentId,
                'gateway_response' => $attached,
            ]);

            return [
                'payment' => $payment,
                'next_action' => $gateway->getNextAction($attached),
            ];
        } catch (Throwable $e) {
            $payment->update([
                'status_id' => Status::FAILED,
                'gateway_response' => ['error' => $e->getMessage()],
            ]);

            throw $e;
        }
    }

    private function resolveGatewayMethodId($gateway, PaymentMethod $paymentMethod, array $data): string
    {
        if ($paymentMethod->isClientSide()) {
            return $data['gateway_payment_method_id']
                ?? throw LoanGatewayException::missingClientSideMethodId();
        }

        $method = $gateway->createPaymentMethod($paymentMethod->gateway_type);

        return data_get($method, 'data.id')
            ?? throw LoanGatewayException::failedToCreatePaymentMethod(
                data_get($method, 'errors')
            );
    }

    private function hasPendingPayment(LoanSchedule $schedule): bool
    {
        return $schedule->payments()
            ->where('status_id', Status::PENDING)
            ->exists();
    }
}
