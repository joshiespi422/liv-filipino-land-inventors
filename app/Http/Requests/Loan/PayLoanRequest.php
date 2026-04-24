<?php

namespace App\Http\Requests\Loan;

use App\Models\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PayLoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'loan_schedule_id' => ['required', 'exists:loan_schedules,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'amount' => ['required', 'numeric', 'gt:0'],

            'gateway' => ['required', 'string', 'in:paymongo,wallet,cash'],

            'gateway_payment_method_id' => [
                'required_if:payment_method_id,' . PaymentMethod::CARD,
                'nullable',
                'string',
            ],
        ];
    }
}
