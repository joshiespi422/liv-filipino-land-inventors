<?php

namespace App\Http\Controllers\API\Membership;

use App\Http\Controllers\Controller;
use App\Models\MembershipSchedule;
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

        return response()->json(
            $membership->load('schedules'),
            201
        );
    }

    // POST /memberships/schedules/{schedule}/pay
    public function pay(Request $request, MembershipSchedule $schedule): JsonResponse
    {
        $validated = $request->validate([
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
        ]);

        $result = $this->paymentService->initiate(
            schedule: $schedule,
            paymentMethodId: $validated['payment_method_id'],
        );

        return response()->json($result);
    }
}
