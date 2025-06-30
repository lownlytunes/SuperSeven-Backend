<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateReportRequest extends FormRequest
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
            'booking_year' => [
                'nullable',
                'integer',
                'min:2023',
            ],
            'package_year' => [
                'nullable',
                'integer',
                'min:2023',
            ],
            'package_month' => [
                'nullable',
                'integer',
                'min:1',
                'max:12',
            ],
            'transaction_start' => [
                'nullable',
                'integer',
                'min:2023',
            ],
            'transaction_end' => [
                'nullable',
                'integer',
                'min:2023',
            ],
        ];
    }
}
