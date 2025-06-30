<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAddonRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'add_on_name' => 'nullable|string|max:30',
            'add_on_details' => 'nullable|string|max:150',
            'add_on_price' => 'nullable|numeric|min:0|max:999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'add_on_name.string' => 'Add-on name must be a string.',
            'add_on_name.max' => 'Add-on name must not exceed 30 characters.',
            'add_on_details.string' => 'Add-on details must be a string.',
            'add_on_details.max' => 'Add-on details must not exceed 150 characters.',
            'add_on_price.numeric' => 'Add-on price must be a number.',
            'add_on_price.min' => 'Add-on price must be at least 0.',
            'add_on_price.max' => 'Add-on price must not exceed 999999.99.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422));
    }
}
