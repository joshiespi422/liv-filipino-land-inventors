<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BusinessTraining;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\UserType;
use Inertia\Inertia;
use App\Models\BusinessTrainingType;
use App\Models\BusinessTrainingCategory;

class BusinessTrainingController extends Controller
{
    // for write permission
    private function canMutate(): bool
    {
        return Auth::user()->user_type_id === UserType::SUPER_ADMIN;
    }

    public function index()
    {
        return Inertia::render('business-training/Index', [
            'types' => BusinessTrainingType::all(),
            'can_mutate' => $this->canMutate(), 
        ]);
    }

    public function showType($slug)
    {
        $type = BusinessTrainingType::where('slug', $slug)
            ->with('categories')
            ->firstOrFail();

        return Inertia::render('business-training/ShowType', [
            'type' => $type,
            'can_mutate' => $this->canMutate(),
        ]);
    }

    public function getCategoryModules($slug)
    {
        $category = BusinessTrainingCategory::where('slug', $slug)
            ->with('trainings')
            ->firstOrFail();

        return response()->json([
            'category' => $category,
            'modules' => $category->trainings
        ]);
    }

    public function storeType(Request $request)
    {
        if (! $this->canMutate()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Generate the Slug
        $validated['slug'] = Str::slug($validated['name']);

        // Handle File Upload
        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('business-training-icons', 'public');
            // Save the path to the database
            $validated['icon'] = $path;
        }

        // Create the Record
        BusinessTrainingType::create($validated);

        return back();
    }

    public function storeCategory(Request $request, BusinessTrainingType $type)
    {
        if (! $this->canMutate()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:business_training_categories,name',
            'description' => 'required|string|max:1000',
            
            'modules' => 'required|array|size:6',

            // MODULE 1
            'modules.0.intro_title' => 'required|string|max:255',
            'modules.0.intro_description' => 'required|string|max:1000',

            'modules.0.advantages' => 'array|max:10',
            'modules.0.challenges' => 'array|max:10',
            'modules.0.advantages.*' => 'required|string|max:255',
            'modules.0.challenges.*' => 'required|string|max:255',

            'modules.0.required_mindset' => 'array|max:10',
            'modules.0.required_mindset.*.name' => 'required|string|max:255',
            'modules.0.required_mindset.*.description' => 'required|string|max:1000',

            // MODULE 2,4,5,6
            'modules.1.items' => 'array|max:5',
            'modules.3.items' => 'array|max:5',
            'modules.4.items' => 'array|max:5',
            'modules.5.items' => 'array|max:5',
            'modules.*.items.*.title' => 'required|string|max:255',
            'modules.*.items.*.description' => 'required|string|max:1000',

            // MODULE 3
            'modules.2.budget' => 'array|max:10',
            'modules.2.budget.*.item' => 'required|string|max:255',
            'modules.2.budget.*.min_cost' => 'required|numeric|min:0|max:1000000000',
            'modules.2.budget.*.max_cost' => 'required|numeric|min:0|max:1000000000',

            'modules.2.min_cost' => 'required|numeric|min:0|max:1000000000',
            'modules.2.max_cost' => 'required|numeric|min:0|max:1000000000',
        ]);

        DB::transaction(function () use ($validated, $type) {

            // CREATE CATEGORY
            $category = BusinessTrainingCategory::create([
                'business_training_type_id' => $type->id,
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'],
            ]);

            // CREATE 6 MODULES
            foreach ($validated['modules'] as $index => $moduleData) {

                BusinessTraining::create([
                    'business_training_category_id' => $category->id,
                    'module' => $index + 1,
                    'content' => $this->buildModuleContent($index + 1, $moduleData),
                ]);
            }
        });

        return back();
    }

    private function buildModuleContent(int $module, array $data): array
    {
        return match ($module) {

            // MODULE 1
            1 => [
                [
                    'title' => $data['intro_title'],
                    'description' => $data['intro_description'],
                ],
                [
                    'title' => 'Advantages & Challenges',
                    'advantages' => array_slice($data['advantages'] ?? [], 0, 10),
                    'challenges' => array_slice($data['challenges'] ?? [], 0, 10),
                ],
                [
                    'title' => 'Required mindset',
                    'description' => $data['mindset_description'],
                    'required_mindset' => array_slice($data['required_mindset'] ?? [], 0, 10),
                ],
            ],

            // MODULE 2,4,5,6 (same structure)
            2,4,5,6 => array_slice($data['items'] ?? [], 0, 5),

            // MODULE 3
            3 => [
                'title' => 'Sample Budget Breakdown (Philippines)',
                'budget' => array_slice($data['budget'] ?? [], 0, 10),
                'estimated_total' => [
                    'min_cost' => $data['min_cost'],
                    'max_cost' => $data['max_cost'],
                ],
            ],

            default => [],
        };
    }
}
