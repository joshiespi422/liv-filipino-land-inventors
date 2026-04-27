<?php

namespace App\Http\Requests\IntellectualProperty;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\UserType;
use Illuminate\Validation\Rule;

class UpdateStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->user_type_id === UserType::ADMIN;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Retrieve the model from the route
        $property = $this->route('property');

        return [
            'action' => ['required', Rule::in(['approve', 'decline'])],
            'amount' => [
                'nullable',
                'numeric',
                'min:1',
                'max:1000000000',
                Rule::requiredIf(function () use ($property) {
                    return $property && $this->input('action') === 'approve' && 
                        $property->form_type === 'payment';
                }),
            ],
        ];
    }
}
