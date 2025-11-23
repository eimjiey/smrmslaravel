<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Set this to true to allow the request to proceed.
        // In a real application, you might check if the user is authenticated.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'studentId' => ['required', 'string', 'max:255'],
            'fullName' => ['required', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'], // Optional
            'yearLevel' => ['required', 'string', 'max:50'],
            'section' => ['required', 'string', 'max:50'],
            'dateOfIncident' => ['required', 'date'],
            // Validate time in HH:MM format (e.g., 14:30)
            'timeOfIncident' => ['required', 'string', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'location' => ['required', 'string', 'max:255'],
            'offenseCategory' => ['required', 'string', 'in:Minor Offense,Major Offense'],
            'specificOffense' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ];
    }
}