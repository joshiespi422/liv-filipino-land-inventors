<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserType;
use App\Models\LoanSetting;
use Inertia\Inertia;

class LoanAssistanceController extends Controller
{
    // for write permission
    private function canMutate(): bool
    {
        return Auth::user()->user_type_id === UserType::ADMIN;
    }

    public function index()
    {
        $globalSettings = LoanSetting::whereNull('user_id')->first();

        return Inertia::render('loan-assistance/Index', [
            'global_settings' => $globalSettings,
            'can_mutate' => $this->canMutate(), 
        ]);
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
