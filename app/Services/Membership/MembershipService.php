<?php

namespace App\Services\Membership;

use App\Models\MembershipSetting;

class MembershipService
{
    public function getSettings(): array
    {
        $setting = MembershipSetting::current();

        return [
            'share_capital_amount' => $setting->share_capital_amount,
            'payment_options' => collect($setting->allowed_term_months)
                ->map(fn($months) => [
                    'term_months' => $months,
                    'label' => $months === 1
                        ? 'Pay in Full'
                        : "{$months} Monthly Installments",
                    'amount_per_term' => (int) ceil($setting->share_capital_amount / $months),
                ]),
        ];
    }
}
