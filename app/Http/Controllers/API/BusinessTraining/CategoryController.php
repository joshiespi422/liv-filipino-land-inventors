<?php

namespace App\Http\Controllers\API\BusinessTraining;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BusinessTraining\CategoryResource;
use App\Models\BusinessTrainingCategory;
use App\Models\BusinessTrainingType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryController extends Controller
{
    /**
     * List categories under a type.
     *
     * Returns a paginated list of categories belonging to the given training type.
     * Supports filtering by `name` (partial) and `slug` (exact),
     * and sorting by `name`, `slug`, or `created_at`.
     *
     * @tags Business Training > Categories
     *
     * @queryParam filter[name] string Filter by partial name match. Example: marketing
     * @queryParam filter[slug] string Filter by exact slug. Example: digital-marketing
     * @queryParam sort string Sort field. Prefix with `-` for descending. Example: -created_at
     * @queryParam page[number] int Page number. Example: 1
     * @queryParam page[size] int Page size. Example: 15
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "type": "categories",
     *       "id": "1",
     *       "attributes": {
     *         "name": "Digital Marketing",
     *         "slug": "digital-marketing",
     *         "description": "Learn digital marketing strategies.",
     *         "created_at": "2024-01-01T00:00:00Z",
     *         "updated_at": "2024-01-01T00:00:00Z"
     *       },
     *       "relationships": {
     *         "type": {
     *           "data": { "type": "types", "id": "1" }
     *         },
     *         "trainings": {
     *           "data": [{ "type": "trainings", "id": "1" }]
     *         }
     *       }
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "from": 1,
     *     "last_page": 5,
     *     "per_page": 15,
     *     "to": 15,
     *     "total": 70
     *   },
     *   "links": {
     *     "first": "https://example.com/api/v1/business-training/types/1/categories?page[number]=1",
     *     "last": "https://example.com/api/v1/business-training/types/1/categories?page[number]=5",
     *     "prev": null,
     *     "next": "https://example.com/api/v1/business-training/types/1/categories?page[number]=2"
     *   }
     * }
     * @response 404 { "message": "No query results for model [BusinessTrainingType]." }
     */
    public function index(Request $request, BusinessTrainingType $type): AnonymousResourceCollection
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
     * Get a category.
     *
     * Returns a single category belonging to the given training type.
     *
     * @tags Business Training > Categories
     *
     * @response 200 {
     *   "data": {
     *     "type": "categories",
     *     "id": "1",
     *     "attributes": {
     *       "name": "Digital Marketing",
     *       "slug": "digital-marketing",
     *       "description": "Learn digital marketing strategies.",
     *       "created_at": "2024-01-01T00:00:00Z",
     *       "updated_at": "2024-01-01T00:00:00Z"
     *     },
     *     "relationships": {
     *       "type": {
     *         "data": { "type": "types", "id": "1" }
     *       },
     *       "trainings": {
     *         "data": [{ "type": "trainings", "id": "1" }]
     *       }
     *     }
     *   }
     * }
     * @response 404 { "message": "Category not found under this type." }
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
