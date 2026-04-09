<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserType;
use Inertia\Inertia;

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
            // 'trainings' => BusinessTraining::latest()->get(),
            'can_mutate' => $this->canMutate(), 
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
