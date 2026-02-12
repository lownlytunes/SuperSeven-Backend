<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateBookingRequest extends FormRequest
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
            'booking_date' => [
                'date',
                'required',
                'after_or_equal:' . today()->addDays(30)->toDateString()
            ],
            'ceremony_time' => 'required|date_format:H:i',
            'event_name' => 'required|string|max:100',
            'package_id' => 'required|integer',
            'booking_address' => 'required|string|max:100',
            'category' => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'booking_date.required'=> 'The booking date is required.',
            'booking_date.after_or_equal'=> 'The booking date must be at least 30 days from today.',
            'ceremony_time.date_format' => 'The ceremony time must be in the format HH:MM.',
            'ceremony_time.required' => 'The ceremony time is required.',
            'event_name.required' => 'The event name is required.',
            'event_name.max' => 'The event name must not exceed 100 characters.',
            'package_id.required' => 'The package ID is required.',
            'booking_address.required' => 'The booking address is required.',
            'booking_address.max' => 'The booking address must not exceed 100 characters.',
            'category.required' => 'The category is required.',
            'category.integer' => 'The category must be an integer.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
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
