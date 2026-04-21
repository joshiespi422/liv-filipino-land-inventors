<?php

namespace App\Http\Controllers\API\IntellectualProperty;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntellectualProperty\StoreIntellectualPropertyRequest;
use App\Http\Requests\IntellectualProperty\UpdateIntellectualPropertyRequest;
use App\Http\Resources\Api\IntellectualProperty\ApiIntellectualPropertyResource;
use App\Models\IntellectualProperty;
use App\Services\IntellectualProperty\IntellectualPropertyService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class IntellectualPropertyController extends Controller
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly IntellectualPropertyService $intellectualPropertyService,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        $includes = $this->parseIncludes(request()->query('include'));

        $intellectualProperties = $this->intellectualPropertyService
            ->listIntellectualProperty($user, $includes);

        return ApiIntellectualPropertyResource::collection($intellectualProperties);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIntellectualPropertyRequest $request)
    {
        try {
            $application = $this->intellectualPropertyService->create(
                $request->validated(),
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Your Intellectual Property Assistance request has been submitted successfully.',
                'data' => new ApiIntellectualPropertyResource($application->load(['status'])),
            ], 201);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, IntellectualProperty $intellectualProperty)
    {
        if ($intellectualProperty->user_id !== $request->user()->id) {
            abort(403, 'You are not authorized to view this intellectual property.');
        }

        $intellectualProperty->load(['status', 'claims', 'documents', 'schedules', 'payments']);

        return new ApiIntellectualPropertyResource($intellectualProperty);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IntellectualProperty $intellectualProperty)
    {
        //
    }

    /**
     * PUT /api/intellectual-properties/{intellectualProperty}
     * Update a draft application.
     */
    public function update(UpdateIntellectualPropertyRequest $request, IntellectualProperty $intellectualProperty): JsonResponse
    {
        try {
            $application = $this->intellectualPropertyService->update($intellectualProperty, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Your Intellectual Property Assistance request has been updated successfully.',
                'data' => new ApiIntellectualPropertyResource($application->load(['status', 'claims', 'documents', 'schedules', 'payments'])),
            ]);

        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IntellectualProperty $intellectualProperty)
    {
        //
    }

    public function settings(IntellectualProperty $intellectualProperty): JsonResponse
    {
        return response()->json(
            $this->intellectualPropertyService->getSettings($intellectualProperty)
        );
    }

    /**
     * Parse and whitelist comma-separated include string from query param.
     */
    private function parseIncludes(?string $include): array
    {
        $allowed = ['settings', 'claims', 'documents', 'schedules', 'payments', 'status'];

        if (empty($include)) {
            return [];
        }

        return array_intersect(
            array_map('trim', explode(',', $include)),
            $allowed
        );
    }
}
