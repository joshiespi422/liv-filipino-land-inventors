<?php

namespace App\Http\Requests\Loan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:1'],
            'term' => ['required', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'agree_terms' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'agree_terms.accepted' => 'You must accept the terms and conditions.',
        ];
    }
}
