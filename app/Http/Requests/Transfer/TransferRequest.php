<?php

namespace App\Http\Requests\Transfer;

use App\Services\Transfer\TransferService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'channel_id' => ['required', Rule::in(array_keys(TransferService::CHANNELS))],
            'amount' => ['required', 'numeric', 'min:'.TransferService::MIN_TRANSFER],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:255'],

            // How the user proved it's really them before this transfer is sent.
            'verification_method' => ['required', Rule::in(['password', 'biometric'])],
            'password' => ['required_if:verification_method,password', 'string'],
            'device_id' => ['required_if:verification_method,biometric', 'string'],
        ];
    }
}
