<?php

namespace App\Http\Requests\IntellectualProperty;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIntellectualPropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->route('intellectualProperty')->status?->name === 'pending';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'creation_type' => ['sometimes', 'in:business_idea,invention'],
            'form_type' => ['sometimes', 'in:payment,grant'],
            'priority_details' => ['nullable', 'string'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'applicability' => ['sometimes', 'string'],

            'claims' => ['sometimes', 'array', 'min:1'],
            'claims.*.description' => ['required_with:claims', 'string'],

            'delete_document_ids' => ['nullable', 'array'],
            'delete_document_ids.*' => ['integer', 'exists:intellectual_property_documents,id'],

            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:jpg,jpeg,png,pdf,svg', 'max:10240'],

            'is_original' => ['sometimes', 'boolean'],
            'agreed_terms' => ['sometimes', 'boolean'],
            'agreed_privacy' => ['sometimes', 'boolean'],
        ];
    }
}
