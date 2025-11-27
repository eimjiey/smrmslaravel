<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class IncidentController extends Controller
{
    // Define the list of valid programs for dropdown validation
    const VALID_PROGRAMS = [
        'BSIT', 'BSCS', 'BSDSA', 'BLIS', 'BSIS', 
    ];

    /**
     * Define a simple rule matrix (Optimization Logic).
     */
    const RECOMMENDATION_MATRIX = [
        'Minor Offense' => [
            1 => 'Verbal Warning',
            2 => 'Written Warning and Parent Notification',
            3 => 'Mandatory Counseling Session',
            4 => '1-Day Suspension', // 4+ offenses
        ],
        'Major Offense' => [
            1 => '3-Day Suspension and Parent Conference',
            2 => 'Formal Behavioral Contract and Suspension',
            3 => 'Recommendation for Expulsion', // 3+ offenses
        ],
    ];

    /**
     * Display a listing of all incident reports.
     */
    public function index()
    {
        $incidents = Incident::orderBy('id', 'desc')->get();
        return response()->json($incidents);
    }

    /**
     * Display the specified incident report.
     */
    public function show($id)
    {
        $incident = Incident::find($id);
        if (!$incident) {
            return response()->json(['message' => 'Incident not found for viewing.'], 404);
        }
        return response()->json($incident);
    }
    
    /**
     * Helper function to get the optimal disciplinary action recommendation.
     */
    private function getDisciplinaryRecommendation(string $studentId, string $offenseCategory): string
    {
        // Count previous incidents for this student of the same category.
        $previousOffenseCount = Incident::where('student_id', $studentId)
            ->where('offense_category', $offenseCategory)
            ->count();
            
        $offenseNumber = $previousOffenseCount + 1;
        $matrix = self::RECOMMENDATION_MATRIX[$offenseCategory] ?? [];

        if (empty($matrix)) {
            return 'No specific recommendation found for this category.';
        }

        if (isset($matrix[$offenseNumber])) {
            return $matrix[$offenseNumber];
        }

        // If the number exceeds the defined keys, return the last (most severe) option.
        $keys = array_keys($matrix);
        $maxKey = end($keys);

        if ($offenseNumber > $maxKey) {
            return $matrix[$maxKey];
        }

        return 'Default action: Further Review Required.';
    }

    /**
     * Store a newly created incident report in the database, including the system's recommendation.
     */
    public function store(Request $request)
    {
        try {
            // Define validation rules
            $validatedData = $request->validate([
                'studentId' => ['required', 'string', 'max:7', 'regex:/^\d{2}-\d{4}$/'], 
                'fullName' => 'required|string|max:255',
                'program' => ['nullable', 'string', 'max:255', 'in:' . implode(',', self::VALID_PROGRAMS)],
                'yearLevel' => 'required|string|max:50',
                'section' => 'nullable|string|max:50',
                'dateOfIncident' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) {
                        if (date('w', strtotime($value)) == 0) {
                            $fail('The date of incident cannot be a Sunday.');
                        }
                    },
                ],
                'timeOfIncident' => [
                    'required',
                    'date_format:H:i',
                    function ($attribute, $value, $fail) {
                        $start = strtotime('07:00');
                        $end = strtotime('17:00');
                        $inputTime = strtotime($value);

                        if ($inputTime < $start || $inputTime > $end) {
                            $fail('The time of incident must be between 7:00 AM and 5:00 PM.');
                        }
                    },
                ],
                'location' => 'required|string|max:255',
                'offenseCategory' => 'required|string|in:Minor Offense,Major Offense',
                'specificOffense' => 'required|string|max:255',
                'description' => 'required|string',
                'actionTaken' => 'nullable|string|max:255', 
            ]);

            // Calculate the recommendation based on history
            $recommendation = $this->getDisciplinaryRecommendation(
                $validatedData['studentId'], 
                $validatedData['offenseCategory']
            );

            // Create the incident record
            $incident = Incident::create([
                'student_id' => $validatedData['studentId'],
                'full_name' => $validatedData['fullName'],
                'program' => $validatedData['program'],
                'year_level' => $validatedData['yearLevel'],
                'section' => $validatedData['section'],
                'date_of_incident' => $validatedData['dateOfIncident'],
                'time_of_incident' => $validatedData['timeOfIncident'],
                'location' => $validatedData['location'],
                'offense_category' => $validatedData['offenseCategory'],
                'specific_offense' => $validatedData['specificOffense'],
                'description' => $validatedData['description'],
                'status' => 'Pending',
                'recommendation' => $recommendation, 
                'action_taken' => $validatedData['actionTaken'] ?? null, 
            ]);
            
            // Return the recommendation to the client
            return response()->json([
                'message' => 'Incident report filed successfully.', 
                'incident' => $incident, 
                'recommendation' => $recommendation 
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Store Incident Error: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while filing the report.', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update the specified incident report fully.
     */
    public function update(Request $request, Incident $incident)
    {
        try {
            $validatedData = $request->validate([
                'student_id' => ['required', 'string', 'max:7', 'regex:/^\d{2}-\d{4}$/'], 
                'full_name' => 'required|string|max:255',
                'program' => ['nullable', 'string', 'max:255', 'in:' . implode(',', self::VALID_PROGRAMS)],
                'year_level' => 'required|string|max:50',
                'section' => 'nullable|string|max:50',
                'date_of_incident' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) {
                        if (date('w', strtotime($value)) == 0) {
                            $fail('The date of incident cannot be a Sunday.');
                        }
                    },
                ],
                'time_of_incident' => [
                    'required',
                    'date_format:H:i',
                    function ($attribute, $value, $fail) {
                        $start = strtotime('07:00');
                        $end = strtotime('17:00');
                        $inputTime = strtotime($value);

                        if ($inputTime < $start || $inputTime > $end) {
                            $fail('The time of incident must be between 7:00 AM and 5:00 PM.');
                        }
                    },
                ],
                'location' => 'required|string|max:255',
                'offenseCategory' => 'required|string|in:Minor Offense,Major Offense',
                'specificOffense' => 'required|string|max:255',
                'description' => 'required|string',
                'recommendation' => 'nullable|string|max:255', 
                'action_taken' => 'nullable|string|max:255', 
                'status' => 'nullable|string|in:Pending,Resolved,Under Review,Closed',
            ]);

            $incident->update($validatedData);

            return response()->json([
                'message' => 'Incident report updated successfully.',
                'incident' => $incident,
            ], 200);

        } catch (ValidationException $e) {
             Log::error('Validation Failed during full update: ' . json_encode($e->errors()));
             return response()->json([
                 'message' => 'Validation Failed: One or more fields are invalid.',
                 'errors' => $e->errors() 
             ], 422);
        } catch (\Exception $e) {
            Log::error('Full update general error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An internal error occurred during full update.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the status of a specific incident.
     */
    public function updateStatus(Request $request, Incident $incident)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Pending,Resolved,Under Review,Closed',
        ]);

        $incident->status = $validated['status'];
        $incident->save();

        return response()->json([
            'message' => 'Incident status updated successfully.',
            'incident' => $incident,
        ], 200);
    }
    
    /**
     * Update only the action taken for a specific incident (via PATCH /incidents/{incident}/action).
     */
    public function updateActionTaken(Request $request, Incident $incident)
    {
        try {
            $validated = $request->validate([
                'action_taken' => 'required|string|max:255',
            ]);

            $incident->action_taken = $validated['action_taken'];
            // Status is set to Resolved upon recording a final action
            $incident->status = 'Resolved'; 
            $incident->save();

            return response()->json([
                'message' => 'Disciplinary action successfully recorded and incident resolved.',
                'incident' => $incident,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.', 
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating action taken: ' . $e->getMessage());
            return response()->json([
                'message' => 'An internal error occurred while recording the action.', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified incident report from storage.
     */
    public function destroy(Incident $incident)
    {
        try {
            $incidentId = $incident->id;
            $incident->delete();
            return response()->json(['message' => "Incident report ID {$incidentId} deleted successfully."], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete the incident report.', 'error' => $e->getMessage()], 500);
        }
    }
}