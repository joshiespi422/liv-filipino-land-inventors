<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Response;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\UserType;
use App\Models\LoanSetting;
use App\Models\Loan;
use App\Models\Status;
use App\Http\Resources\LoanAssistanceResource;
use Inertia\Inertia;

class LoanAssistanceController extends Controller
{
    // for write permission
    private function canMutate(): bool
    {
        return Auth::user()->user_type_id === UserType::ADMIN;
    }

    public function index(Request $request): Response
    {
        // Validate all filters
        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(['pending', 'active', 'rejected', 'cancelled', 'finished'])],
        ]);

        // Set defaults
        $filters = [
            'status' => $validated['status'] ?? 'pending',
        ];

        // Build and execute query
        $query = $this->buildBaseQuery($filters);
        $loans = $query->get();

        $globalSettings = LoanSetting::whereNull('user_id')->first();

        return Inertia::render('loan-assistance/Index', [
            'loans' => LoanAssistanceResource::collection($loans),
            'global_settings' => $globalSettings,
            'can_mutate' => $this->canMutate(), 
            'filters' => $filters
        ]);
    }

    /**
     * Creates the base query with all "WHERE" conditions.
     */
    private function buildBaseQuery(array $filters): Builder
    {
        $query = Loan::with([
            'status:id,name',
            'user:id,name',
        ])->whereHas('status', fn($q) => $q->where('name', $filters['status']));

        return $query;
    }

    public function updateStatus(Loan $loan, Request $request)
    {
        if (!$this->canMutate()) {
            abort(403, 'Unauthorized action.');
        }

        $action = $request->input('action');

        if ($action === 'approve') {
            $loan->update([
                'status_id' => Status::ACTIVE,
            ]);
        }

        if ($action === 'decline') {
            $loan->update([
                'status_id' => Status::REJECTED,
            ]);
        }

        return back();
    }

}
