<?php

namespace App\Http\Controllers\API\BusinessTraining;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BusinessTraining\TrainingResource;
use App\Models\BusinessTrainingCategory;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class TrainingController extends Controller
{
    /**
     * GET /api/v1/business-training/categories/{category}/trainings
     */
    public function index(Request $request, BusinessTrainingCategory $category)
    {
        $trainings = QueryBuilder::for(
            $category->trainings()
        )
            ->allowedSorts('module', 'created_at')
            ->jsonPaginate()
            ->appends(request()->query());

        return TrainingResource::collection($trainings);
    }

    /**
     * GET /api/v1/business-training/categories/{category}/trainings/{training}
     */
    public function show(BusinessTrainingCategory $category, int $module)
    {
        $totalModules = $category->trainings()->count();

        $training = $category->trainings()
            ->where('module', $module)
            ->firstOrFail();

        return (new TrainingResource($training))
            ->additional([
                'meta' => [
                    'current_module' => $module,
                    'total_modules' => $totalModules,
                    'is_last' => $module >= $totalModules,
                    'is_first' => $module === 1,
                    'next_module' => $module < $totalModules ? $module + 1 : null,
                    'prev_module' => $module > 1 ? $module - 1 : null,
                ]
            ]);
    }
}
