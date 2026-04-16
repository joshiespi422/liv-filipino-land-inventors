<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function store(Request $request)
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

    public function storeType(Request $request)
    {
        if (! $this->canMutate()) {
            abort(403, 'Unauthorized action.');
        }

        // $validated = $request->validate([
        //     ...
        // ]);

        return back();
    }
}
