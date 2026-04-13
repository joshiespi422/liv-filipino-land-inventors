<?php

namespace App\Http\Controllers\API\BusinessTraining;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BusinessTraining\TypeResource;
use App\Models\BusinessTrainingType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TypeController extends Controller
{
    /**
     * List training types.
     *
     * Returns a paginated list of all available business training types.
     * Supports filtering by `name` (partial) and `slug` (exact),
     * sorting by `name`, `slug`, or `created_at`,
     * and sparse fieldsets via `fields[types]`.
     *
     * @tags Business Training > Types
     *
     * @queryParam filter[name] string Filter by partial name match. Example: finance
     * @queryParam filter[slug] string Filter by exact slug. Example: personal-finance
     * @queryParam sort string Sort field. Prefix with `-` for descending. Example: -created_at
     * @queryParam fields[types] string Comma-separated list of fields to include. Example: id,name,slug
     * @queryParam page[number] int Page number. Example: 1
     * @queryParam page[size] int Items per page. Example: 15
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "type": "types",
     *       "id": "1",
     *       "attributes": {
     *         "name": "Personal Finance",
     *         "slug": "personal-finance",
     *         "icon": "https://example.com/icons/personal-finance.png",
     *         "created_at": "2024-01-01T00:00:00Z",
     *         "updated_at": "2024-01-01T00:00:00Z"
     *       }
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "from": 1,
     *     "last_page": 3,
     *     "per_page": 15,
     *     "to": 15,
     *     "total": 40
     *   },
     *   "links": {
     *     "first": "https://example.com/api/v1/business-training/types?page[number]=1",
     *     "last": "https://example.com/api/v1/business-training/types?page[number]=3",
     *     "prev": null,
     *     "next": "https://example.com/api/v1/business-training/types?page[number]=2"
     *   }
     * }
     */
    public function index(Request $request): AnonymousResourceCollection
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
     * Get a training type.
     *
     * Returns a single business training type by its ID or slug.
     *
     * @tags Business Training > Types
     *
     * @response 200 {
     *   "data": {
     *     "type": "types",
     *     "id": "1",
     *     "attributes": {
     *       "name": "Personal Finance",
     *       "slug": "personal-finance",
     *       "icon": "https://example.com/icons/personal-finance.png",
     *       "created_at": "2024-01-01T00:00:00Z",
     *       "updated_at": "2024-01-01T00:00:00Z"
     *     }
     *   }
     * }
     * @response 404 { "message": "No query results for model [BusinessTrainingType]." }
     */
    public function show(BusinessTrainingType $type): TypeResource
    {
        return new TypeResource($type);
    }
}
