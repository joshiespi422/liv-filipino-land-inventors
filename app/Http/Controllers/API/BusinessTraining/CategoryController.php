<?php

namespace App\Http\Controllers\API\BusinessTraining;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BusinessTraining\CategoryResource;
use App\Models\BusinessTrainingCategory;
use App\Models\BusinessTrainingType;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryController extends Controller
{
    /**
     * Get all categories under a specific type
     * GET /api/v1/business-training/types/{type}/categories
     */
    public function index(Request $request, BusinessTrainingType $type)
    {
        $categories = QueryBuilder::for(
            $type->categories()
        )
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::exact('slug'),
            )
            ->allowedSorts(
                'name',
                'slug',
                'created_at',
            )
            ->jsonPaginate()
            ->appends(request()->query());

        return CategoryResource::collection($categories);
    }

    /**
     * Get single category
     * GET /api/v1/business-training/types/{type}/categories/{category}
     */
    public function show(BusinessTrainingType $type, BusinessTrainingCategory $category): CategoryResource
    {
        abort_if(
            $category->business_training_type_id !== $type->id,
            404,
            'Category not found under this type.'
        );

        return new CategoryResource($category);
    }
}
