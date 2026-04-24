<?php

namespace App\Services\Loan;

use App\Exceptions\Loan\LoanGatewayException;
use App\Exceptions\Loan\LoanInvalidPaymentAmountException;
use App\Exceptions\Loan\LoanPendingApplicationException;
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
            $this->checkLoanStatus($loan, $data['loan_schedule_id']);
            $schedule = $this->lockAndValidateSchedule($loan, $data['loan_schedule_id']);
            $payment = $this->createPendingPayment($schedule, $data, $paymentMethod);

            if ($paymentMethod->isOffline()) {
                return $this->settleOffline($payment);
            }

            return $this->processGatewayPayment($payment, $paymentMethod, $data);
        });
    }

    /**
     * Summary of checkLoanStatus
     *
     * @param Loan $loan
     * @throws LoanPendingApplicationException
     * @return void
     */
    public function checkLoanStatus(Loan $loan, int $scheduleId): void
    {
        if ($loan->status_id === Status::PENDING) {
            throw new LoanPendingApplicationException($loan, $scheduleId ?? null);
        }
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

    private function validateAmount(LoanSchedule $schedule, float $amountInPhp): void
    {
        $paidInCents = (int) $schedule->payments()
            ->where('status_id', Status::SUCCESS)
            ->sum('amount');

        $totalInCents = (int) round($schedule->total_payment * 100);
        $remainingInCents = $totalInCents - $paidInCents;

        if ($remainingInCents <= 0) {
            throw new LoanScheduleAlreadyPaidException($schedule);
        }

        $providedInCents = (int) round($amountInPhp * 100);

        if ($providedInCents !== $remainingInCents) {
            throw new LoanInvalidPaymentAmountException(
                required: $remainingInCents / 100,
                provided: $amountInPhp,
            );
        }
    }

    private function createPendingPayment(LoanSchedule $schedule, array $data, PaymentMethod $paymentMethod): Payment
    {
        $this->validateAmount($schedule, (float) $data['amount']);

        return $schedule->payments()->create([
            'payment_method_id' => $data['payment_method_id'],
            'payment_date' => now(),
            'amount' => (int) round((float) $data['amount'] * 100),
            'gateway' => PaymentGatewayFactory::resolveGateway($paymentMethod), // ← derive it
            'status_id' => Status::PENDING,
        ]);
    }

    private function settleOffline(Payment $payment): array
    {
        $payment->update(['status_id' => Status::SUCCESS]);
        $payment->payable->onPaymentSuccess($payment);

        return ['payment' => $payment, 'next_action' => null];
    }

    private function processGatewayPayment(Payment $payment, PaymentMethod $paymentMethod, array $data): array
    {
        try {
            $gateway = PaymentGatewayFactory::make($data['gateway'] ?? 'paymongo');
            $gatewayMethodId = $this->resolveGatewayMethodId($gateway, $paymentMethod, $data);

            $amountInPhp = $payment->amount / 100;
            $intent = $gateway->createPaymentIntent($amountInPhp);
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
        $method = $gateway->createPaymentMethod($paymentMethod->gateway_type);

        $methodId = data_get($method, 'data.id');

        if (!$methodId) {
            throw LoanGatewayException::failedToCreatePaymentMethod(
                data_get($method, 'errors')
            );
        }

        return $methodId;
    }

    private function hasPendingPayment(LoanSchedule $schedule): bool
    {
        return $schedule->payments()
            ->where('status_id', Status::PENDING)
            ->where('created_at', '>', now()->subMinutes(1))
            ->exists();
    }
}
