<?php

namespace App\Http\Controllers\API\BusinessTraining;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BusinessTraining\TypeResource;
use App\Models\BusinessTrainingType;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class TypeController extends Controller
{
    /**
     * GET /api/v1/business-training/types
     */
    public function index(Request $request)
    {
        $types = QueryBuilder::for(BusinessTrainingType::class)
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::exact('slug'),
            )
            ->allowedSorts(
                'name',
                'slug',
                'created_at',
            )
            ->allowedFields(
                'types.id',
                'types.name',
                'types.slug',
                'types.icon',
            )
            ->jsonPaginate()
            ->appends($request->query());

        return TypeResource::collection($types);
    }

    /**
     * GET /api/v1/business-training/types/{type}
     * Screen: Single Type details
     */
    public function show(BusinessTrainingType $type)
    {
        return new TypeResource($type);
    }
}
