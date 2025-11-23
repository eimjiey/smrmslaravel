<?php

// app/Http/Requests/StudentStoreRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // This should return true if an authenticated user (with admin privileges) is making the request.
        // If your route middleware handles the authorization (e.g., auth:sanctum, admin), setting this to true is standard.
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Student Details
            'student_number' => 'required|string|max:20|unique:students',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female,Other',
            'date_of_birth' => 'required|date',
            
            // Academic Details
            'program' => 'required|string|max:150',
            'year_level' => 'required|in:1st Year,2nd Year,3rd Year,4th Year',
            'section' => 'nullable|string|max:50',
            
            // Contact Details
            'contact_number' => 'required|string|max:20',
            'email' => 'required|email|max:150|unique:students',
            'address' => 'required|string',
            
            // Guardian Details
            'guardian_name' => 'required|string|max:150',
            'guardian_contact' => 'required|string|max:50',
        ];
    }
}