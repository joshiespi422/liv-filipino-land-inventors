<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $userId = $this->user()->id;

        return [
            // 'name' => 'required|string|max:255',
            // 'phone' => "required|string|max:20|unique:users,phone,{$userId}",

            'gender' => [
                'required',
                'in:Male,Female,Other,Prefer not to say',
            ],

            'birthdate' => [
                'required',
                'date',
                'before:today',
            ],

            'email' => [
                'required',
                'email',
                "unique:users,email,{$userId}",
            ],

            'region' => [
                'required',
                'string',
            ],

            'province' => [
                'nullable',
                'string',
            ],

            'city' => [
                'required',
                'string',
            ],

            'barangay' => [
                'required',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | VALID ID TYPE
            |--------------------------------------------------------------------------
            */

            'valid_id_type' => [
                'required',
                'string',
                Rule::in([
                    'National ID',
                    'Passport',
                    'Driver License',
                    'UMID',
                    'SSS ID',
                    'PhilHealth ID',
                    'Pag-IBIG Loyalty Card',
                    'Postal ID',
                    'PRC ID',
                    'Voter ID',
                    'Senior Citizen ID',
                    'PWD ID',
                    'School ID',
                    'Company ID',
                    'Barangay ID',
                    'National Police Clearance',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | VALID ID NUMBER
            |--------------------------------------------------------------------------
            */

            'valid_id_number' => [
                'required',
                'string',
                'max:50',
                "unique:users,valid_id_number,{$userId}",
            ],

            /*
            |--------------------------------------------------------------------------
            | FRONT VALID ID
            |--------------------------------------------------------------------------
            */

            'front_valid_id_picture' => $this->hasFile(
                'front_valid_id_picture'
            )
                ? 'required|image|max:10240'
                : 'nullable',

            /*
            |--------------------------------------------------------------------------
            | BACK VALID ID
            |--------------------------------------------------------------------------
            */

            'back_valid_id_picture' => $this->hasFile(
                'back_valid_id_picture'
            )
                ? 'required|image|max:10240'
                : 'nullable',

            /*
            |--------------------------------------------------------------------------
            | ADDRESS
            |--------------------------------------------------------------------------
            */

            'street' => [
                'required',
                'string',
            ],

            'postal_code' => [
                'required',
                'string',
                'max:20',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * This helps the frontend provide user-friendly error messages.
     * The frontend will map these messages to even more friendly versions.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Email validation
            'email.required' => 'The email field is required',
            'email.email' => 'The email must be a valid email address',
            'email.unique' => 'The email has already been taken',

            // Gender validation
            'gender.required' => 'The gender field is required',
            'gender.in' => 'The selected gender is invalid',

            // Birthdate validation
            'birthdate.required' => 'The birthdate field is required',
            'birthdate.date' => 'The birthdate must be a valid date',
            'birthdate.before' => 'The birthdate must be before today',

            // Address validation
            'region.required' => 'The region field is required',
            'province.required' => 'The province field is required',
            'city.required' => 'The city field is required',
            'barangay.required' => 'The barangay field is required',
            'street.required' => 'The street field is required',
            'postal_code.required' => 'The postal code field is required',
            'postal_code.max' => 'The postal code must not exceed 20 characters',

            // Valid ID validation
            'valid_id_type.required' => 'The valid id type field is required',
            'valid_id_type.in' => 'The selected valid ID type is invalid',
            'valid_id_number.required' => 'The valid id number field is required',
            'valid_id_number.unique' => 'The valid id number has already been taken',
            'valid_id_number.max' => 'The ID number must not exceed 50 characters',

            // Valid ID pictures
            'front_valid_id_picture.required' => 'The front ID picture is required',
            'front_valid_id_picture.image' => 'The front ID picture must be an image',
            'front_valid_id_picture.max' => 'The front ID picture must not exceed 10MB',

            'back_valid_id_picture.required' => 'The back ID picture is required',
            'back_valid_id_picture.image' => 'The back ID picture must be an image',
            'back_valid_id_picture.max' => 'The back ID picture must not exceed 10MB',
        ];
    }
}
