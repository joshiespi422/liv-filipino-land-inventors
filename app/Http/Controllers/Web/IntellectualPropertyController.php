<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use App\Models\UserType;
use App\Models\IntellectualProperty;
use App\Http\Resources\IntellectualPropertyResource;
use Inertia\Inertia;

class IntellectualPropertyController extends Controller
{
    // for write permission
    private function canMutate(): bool
    {
        return Auth::user()->user_type_id === UserType::SUPER_ADMIN;
    }

    public function index(Request $request): Response
    {
        // Validate all filters
        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(['pending', 'registered', 'rejected', 'expired', 'waiting_for_payment'])],
        ]);

        // Set defaults
        $filters = [
            'status' => $validated['status'] ?? 'pending',
        ];

        // Build and execute query
        $query = $this->buildBaseQuery($filters);
        $intellectualProperties = $query->get();

        return Inertia::render('intellectual-property/Index', [
            'intellectual_properties' => IntellectualPropertyResource::collection($intellectualProperties),
            'can_mutate' => $this->canMutate(), 
            'filters' => $filters
        ]);
    }

    /**
     * Creates the base query with all "WHERE" conditions.
     */
    private function buildBaseQuery(array $filters): Builder
    {
        $query = IntellectualProperty::with([
            'status:id,name',
            'user:id,name',
        ])->whereHas('status', fn($q) => $q->where('name', $filters['status']));

        return $query;
    }
}
