<?php

namespace App\Http\Controllers\API\Membership;

use App\Exceptions\Membership\MembershipAlreadyExistsException;
use App\Http\Controllers\Controller;
use App\Models\MemberMembership;
use App\Models\MembershipSchedule;
use App\Models\Status;
use App\Services\Membership\MembershipApplicationService;
use App\Services\Membership\MembershipPaymentService;
use App\Services\Membership\MembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly MembershipService $membershipService,
        private readonly MembershipApplicationService $applicationService,
        private readonly MembershipPaymentService $paymentService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $membership = MemberMembership::where('user_id', $request->user()->id)
            ->whereIn('status_id', [Status::PENDING, Status::ACTIVE])
            ->with([
                'status',
                'schedules' => fn($q) => $q->orderBy('installment_no'),
                'schedules.status',
                'schedules.payments' => fn($q) => $q->latest(),
                'schedules.payments.status',
            ])
            ->first();

        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'No active or pending membership found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'membership' => $membership,
                'summary' => [
                    'total_amount' => $membership->amount,
                    'term_months' => $membership->term_months,
                    'paid_schedules' => $membership->schedules->where('status_id', Status::PAID)->count(),
                    'unpaid_schedules' => $membership->schedules->whereIn('status_id', [Status::UNPAID, Status::OVERDUE])->count(),
                    'total_schedules' => $membership->schedules->count(),
                    'activated_at' => $membership->activated_at,
                    'expires_at' => $membership->expires_at,
                ],
            ],
        ]);
    }

    // GET /memberships/settings
    public function settings(): JsonResponse
    {
        return response()->json(
            $this->membershipService->getSettings()
        );
    }

    // POST /memberships/apply
    public function apply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'term_months' => ['required', 'integer', 'min:1'],
        ]);

        $membership = $this->applicationService->apply(
            user: $request->user(),
            termMonths: $validated['term_months'],
        );

        return response()->json($membership->load('schedules'), 201);
    }

    // POST /memberships/schedules/{schedule}/pay
    public function pay(Request $request, MembershipSchedule $schedule): JsonResponse
    {
        abort_if(
            $schedule->membership->user_id !== $request->user()->id,
            403,
            'Unauthorized'
        );

        $validated = $request->validate([
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
        ]);

        $result = $this->paymentService->initiate(
            schedule: $schedule,
            paymentMethodId: $validated['payment_method_id'],
        );

        return response()->json($result);
    }

    public function cancel(Request $request): JsonResponse
    {
        $this->applicationService->cancel($request->user());

        return response()->json(['message' => 'Membership cancelled.']);
    }
}
