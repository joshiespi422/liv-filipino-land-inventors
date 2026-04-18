<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Membership\ApiMembershipResource;
use App\Http\Resources\Api\Membership\ApiMembershipScheduleResource;
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

    public function index(Request $request): ApiMembershipResource|JsonResponse
    {
        $membership = MemberMembership::where('user_id', $request->user()->id)
            ->whereIn('status_id', [Status::PENDING, Status::ACTIVE])
            ->with([
                'status',
                'schedules' => fn($q) => $q->orderBy('installment_no'),
                'schedules.status',
                'schedules.payments' => fn($q) => $q->latest(),
                'schedules.payments.status',
                'schedules.payments.paymentMethod',
            ])
            ->first();

        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'No active or pending membership found.',
            ], 404);
        }

        return new ApiMembershipResource($membership);
    }

    // GET /memberships/settings
    public function settings(): JsonResponse
    {
        return response()->json(
            $this->membershipService->getSettings()
        );
    }

    // POST /memberships/apply
    public function apply(Request $request): ApiMembershipResource
    {
        $validated = $request->validate([
            'term_months' => ['required', 'integer', 'min:1'],
        ]);

        $membership = $this->applicationService->apply(
            user: $request->user(),
            termMonths: $validated['term_months'],
        );

        $membership->load([
            'status',
            'schedules.status',
        ]);

        return new ApiMembershipResource($membership);
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

        $schedule->load([
            'status',
            'payments.status',
            'payments.paymentMethod',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment initiated.',
            'data' => new ApiMembershipScheduleResource($schedule),
            'next_action' => $result['next_action'],
        ]);
    }

    public function cancel(Request $request): JsonResponse
    {
        $this->applicationService->cancel($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Membership cancelled.',
        ]);
    }
}
