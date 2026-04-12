<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Status;
use App\Models\User;
use App\Models\UserType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiminishingLoanSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Fetch 3 users who are 'Members' and don't have a loan yet
        $users = User::where('user_type_id', UserType::MEMBER)
            ->whereDoesntHave('loans')
            ->limit(3)
            ->get();

        if ($users->isEmpty()) {
            $this->command->warn('No eligible member users found without loans.');
            return;
        }

        foreach ($users as $user) {
            // 2. Get Loan Settings (Global default where user_id is null)
            $settings = $user->getActiveLoanSetting();

            if (!$settings) {
                $this->command->error("No loan settings found for User ID: {$user->id}");
                continue;
            }

            // Define loan parameters based on settings
            $maxAmount = $settings->default_amount;
            $maxMonths = $settings->default_term_months;

            $amount = rand(10, (int)($maxAmount / 100)) * 100; // Random amount in increments of 100
            $termMonths = rand(3, $maxMonths);
            $interestRate = $settings->default_interest_rate; // e.g., 0.03 for 3%
            $startDate = Carbon::now()->addMonth()->startOfMonth();
            
            // Fixed Monthly Principal (Straight line principal, diminishing interest)
            $monthlyPrincipal = round($amount / $termMonths, 2);

            DB::transaction(function () use ($user, $amount, $interestRate, $termMonths, $startDate, $monthlyPrincipal) {
                
                // 3. Create the Main Loan Record
                $loan = Loan::create([
                    'user_id'           => $user->id,
                    'status_id'         => Status::PENDING,
                    'amount'            => $amount,
                    'interest_rate'     => $interestRate,
                    'term_months'       => $termMonths,
                    'start_date'        => $startDate,
                    'end_date'          => $startDate->copy()->addMonths($termMonths - 1),
                    'monthly_principal' => $monthlyPrincipal,
                ]);

                $currentBalance = $amount;

                // 4. Generate Diminishing Loan Schedules
                for ($month = 1; $month <= $termMonths; $month++) {
                    $beginningBalance = $currentBalance;
                    
                    // Interest is calculated based on the REMAINING balance
                    $interestAmount = round($beginningBalance * $interestRate, 2);
                    
                    // Adjust last month's principal to clear the balance perfectly due to rounding
                    $principalAmount = ($month == $termMonths) ? $beginningBalance : $monthlyPrincipal;
                    
                    $totalPayment = $interestAmount + $principalAmount;
                    $endingBalance = round($beginningBalance - $principalAmount, 2);

                    LoanSchedule::create([
                        'loan_id'           => $loan->id,
                        'status_id'         => Status::UNPAID,
                        'month_no'          => $month,
                        'beginning_balance' => $beginningBalance,
                        'interest_amount'   => $interestAmount,
                        'principal_amount'  => $principalAmount,
                        'total_payment'     => $totalPayment,
                        'ending_balance'    => $endingBalance < 0 ? 0 : $endingBalance,
                        'due_date'          => $startDate->copy()->addMonths($month - 1),
                    ]);

                    $currentBalance = $endingBalance;
                }
            });
        }
    }
}
