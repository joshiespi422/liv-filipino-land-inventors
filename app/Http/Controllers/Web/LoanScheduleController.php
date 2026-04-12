<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Models\LoanSchedule;
use App\Http\Resources\LoanScheduleResource;
use Inertia\Response;
use Inertia\Inertia;

class LoanScheduleController extends Controller
{
    public function index(Loan $loan): Response
    {
        $schedules = LoanSchedule::query()
            ->where('loan_id', $loan->id)
            ->with(['status:id,name'])
            ->withExists('loanPayments')
            ->orderBy('due_date', 'asc')
            ->get();

        return Inertia::render('loan-assistance/ScheduleIndex', [
            'schedules' => LoanScheduleResource::collection($schedules),
            'loanId' => $loan->id
        ]);
    }
}
