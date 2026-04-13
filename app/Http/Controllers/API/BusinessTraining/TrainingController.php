<?php

namespace App\Http\Controllers\API\BusinessTraining;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BusinessTraining\TrainingResource;
use App\Models\BusinessTrainingCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\QueryBuilder;

class TrainingController extends Controller
{
    /**
     * List trainings under a category.
     *
     * Returns a paginated list of training modules belonging to the given category.
     * Supports sorting by `module` or `created_at`.
     *
     * @tags Business Training > Trainings
     *
     * @queryParam sort string Sort field. Prefix with `-` for descending. Example: -created_at
     * @queryParam page[number] int Page number. Example: 1
     * @queryParam page[size] int Items per page. Example: 15
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "type": "trainings",
     *       "id": "1",
     *       "attributes": {
     *         "module": 1,
     *         "title": "Introduction to Digital Marketing",
     *         "description": "An overview of digital marketing concepts.",
     *         "created_at": "2024-01-01T00:00:00Z",
     *         "updated_at": "2024-01-01T00:00:00Z"
     *       },
     *       "relationships": {
     *         "category": {
     *           "data": { "type": "categories", "id": "1" }
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
     *     "first": "https://example.com/api/v1/business-training/categories/1/trainings?page[number]=1",
     *     "last": "https://example.com/api/v1/business-training/categories/1/trainings?page[number]=5",
     *     "prev": null,
     *     "next": "https://example.com/api/v1/business-training/categories/1/trainings?page[number]=2"
     *   }
     * }
     * @response 404 { "message": "No query results for model [BusinessTrainingCategory]." }
     */
    public function index(Request $request, BusinessTrainingCategory $category): AnonymousResourceCollection
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
     * Get a training module.
     *
     * Returns a single training module by its module number within the given category.
     * Includes navigation meta to help clients move between modules sequentially.
     *
     * @tags Business Training > Trainings
     *
     * @urlParam module integer required The module number (not the ID). Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "type": "trainings",
     *     "id": "1",
     *     "attributes": {
     *       "module": 1,
     *       "title": "Introduction to Digital Marketing",
     *       "description": "An overview of digital marketing concepts.",
     *       "created_at": "2024-01-01T00:00:00Z",
     *       "updated_at": "2024-01-01T00:00:00Z"
     *     },
     *     "relationships": {
     *       "category": {
     *         "data": { "type": "categories", "id": "1" }
     *       }
     *     }
     *   },
     *   "meta": {
     *     "current_module": 1,
     *     "total_modules": 10,
     *     "is_first": true,
     *     "is_last": false,
     *     "next_module": 2,
     *     "prev_module": null
     *   }
     * }
     * @response 404 { "message": "No query results for model [BusinessTrainingCategory]." }
     */
    public function show(BusinessTrainingCategory $category, int $module): TrainingResource
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
                ],
            ]);
    }
}
