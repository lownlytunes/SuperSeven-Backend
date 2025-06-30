<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePackageRequest extends FormRequest
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
            'package_name' => 'nullable|string|max:30',
            'package_details' => 'nullable|string|max:150',
            'package_price' => 'nullable|numeric|min:0|max:999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'package_name.string' => 'Package name must be a string.',
            'accepted.string' => 'Package name must be a string.',
            'package_name.max' => 'Package name must not exceed 30 characters.',
            'package_details.string' => 'Package details must be a string.',
            'package_details.max' => 'Package details must not exceed 150 characters.',
            'package_price.numeric' => 'Package price must be a number.',
            'package_price.min' => 'Package price must be at least 0.',
            'package_price.max' => 'Package price must not exceed 999999.99.',
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
