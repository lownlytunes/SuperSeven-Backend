<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddBookingRequest extends FormRequest
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
            'first_name' => 'required|string|max:30',
            'last_name' => 'required|string|max:30',
            'address' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'contact_no' => ['required','regex:/^(09\d{9}|\\+639\d{9})$/'],
            'booking_date' => [
                'required',
                'date',
                'after_or_equal:' . today()->addDays(30)->toDateString()
            ],
            'ceremony_time' => 'required|date_format:H:i',
            'package_id' => 'required|integer',
            'event_name' => 'required|string|max:100',
            'booking_address' => 'required|string|max:100',
            'category' => 'required|integer',
            'deliverable_status' => 'nullable|integer',
            'completion_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'The first name is required.',
            'first_name.string' => 'The first name must be a string.',
            'first_name.max'=> 'The first name must not exceed 30 characters.',
            'last_name.required' => 'The last name is required.',
            'last_name.string' => 'The last name must be a string.',
            'last_name.max' => 'The last name must not exceed 30 characters.',
            'address.required' => 'The address is required.',
            'address.max'=> 'address must not exceed 100 characters.',
            'email.required' => 'The email is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email is already taken.',
            'contact_no.required'=> 'The contact number is required.',
            'contact_no.regex' => 'The contact number must be a valid Philippine number (e.g., 09171234567).',
            'booking_date.required' => 'The booking date is required.',
            'booking_date.after_or_equal'=> 'The booking date must be at least 30 days from today.',
            'ceremony_time.date_format' => 'The ceremony time must be in the format HH:MM.',
            'ceremony_time.required' => 'The ceremony time is required.',
            'package_id.required' => 'The package ID is required.',
            'event_name.required' => 'The event name is required.',
            'event_name.max' => 'The event name must not exceed 100 characters.',
            'booking_address.required' => 'The booking address is required.',
            'category.required' => 'The category is required.',
            'category.integer' => 'The category must be an integer.',
            'deliverable_status.integer' => 'The deliverable status must be an integer.',
            'completion_date.date' => 'The completion date must be a valid date.',
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
