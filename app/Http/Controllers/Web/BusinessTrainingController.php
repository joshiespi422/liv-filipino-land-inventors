<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserType;
use Inertia\Inertia;
use App\Models\BusinessTrainingType;
use App\Models\BusinessTrainingCategory;


class BusinessTrainingController extends Controller
{
    // for write permission
    private function canMutate(): bool
    {
        return Auth::user()->user_type_id === UserType::ADMIN;
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

    public function create()
    {
        if (! $this->canMutate()) {
            abort(403, 'Unauthorized action.');
        }

        return inertia('business-training/Create');
    }

    public function store(Request $request)
    {
        if (! $this->canMutate()) {
            abort(403, 'Unauthorized action.');
        }

        // ... validation and creation logic ...

        return redirect()->route('business-trainings.index');
    }
}
