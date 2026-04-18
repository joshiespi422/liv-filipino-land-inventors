<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Models\LoanSchedule;
use App\Http\Resources\LoanScheduleResource;
use App\Http\Resources\LoanAssistanceResource;
use Inertia\Response;
use Inertia\Inertia;

class LoanScheduleController extends Controller
{
    public function index(Loan $loan): Response
    {
        $schedules = LoanSchedule::query()
            ->where('loan_id', $loan->id)
            ->with(['status:id,name'])
            ->withExists('payments')
            ->orderBy('due_date', 'asc')
            ->get();

        $loan->loadMissing(['status:id,name', 'user:id,name']);

        $totalInterest = $schedules->sum('interest_amount');
        $totalPayment = $schedules->sum('total_payment');

        return Inertia::render('loan-assistance/ScheduleIndex', [
            'schedules' => LoanScheduleResource::collection($schedules),
            'loan' => LoanAssistanceResource::make($loan),
            'summary' => [
                'total_interest' => $totalInterest,
                'total_payment' => $totalPayment,
            ],
        ]);
    }
}
