<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Kept for clarity

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
            4 => '1-Day Suspension',
        ],
        'Major Offense' => [
            1 => '3-Day Suspension and Parent Conference',
            2 => 'Formal Behavioral Contract and Suspension',
            3 => 'Recommendation for Expulsion',
        ],
    ];

    // --- Core API Methods ---

    /**
     * Display a listing of incident reports.
     * Filters results based on the authenticated user's role (Admin sees all, User sees own).
     */
    public function index()
    {
        $user = Auth::user();
        
        // 🛡️ SECURITY CHECK: Must be authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        $query = Incident::orderBy('id', 'desc');

        // Apply filtering unless the user is an 'admin'
        if ($user->role !== 'admin') {
            $query->where('filer_id', $user->id); 
        } 

        $incidents = $query->get();

        return response()->json($incidents);
    }
    
    /**
     * Display the specified incident report.
     * Uses Route Model Binding to automatically handle 404 if incident is not found.
     */
    public function show(Incident $incident)
    {
        $user = Auth::user();
        
        // 🛡️ SECURITY CHECK 1: Must be authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        // 🛡️ SECURITY CHECK 2 (Authorization Fix):
        // Uses non-strict comparison (!=) to avoid 403 errors caused by type mismatch 
        // between the database (int) and the token payload (string).
        if ($user->role != 'admin' && $incident->filer_id != $user->id) {
            return response()->json(['message' => 'Unauthorized access to incident report.'], 403);
        }
        
        return response()->json($incident);
    }
    
    /**
     * Store a newly created incident report.
     */
    public function store(Request $request)
    {
        // 🛡️ SECURITY CHECK: Must be authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            $validatedData = $request->validate([
                'studentId' => ['required', 'string', 'max:7', 'regex:/^\d{2}-\d{4}$/'], 
                'fullName' => 'required|string|max:255',
                'program' => ['nullable', 'string', 'max:255', 'in:' . implode(',', self::VALID_PROGRAMS)],
                'yearLevel' => 'required|string|max:50',
                'section' => 'nullable|string|max:50',
                
                // Date/Time validation rules
                'dateOfIncident' => ['required', 'date', function ($attribute, $value, $fail) {
                    if (date('w', strtotime($value)) == 0) { $fail('The date of incident cannot be a Sunday.'); }
                }],
                'timeOfIncident' => ['required', 'date_format:H:i', function ($attribute, $value, $fail) {
                    $start = strtotime('07:00'); $end = strtotime('17:00'); $inputTime = strtotime($value);
                    if ($inputTime < $start || $inputTime > $end) { $fail('The time of incident must be between 7:00 AM and 5:00 PM.'); }
                }],
                
                'location' => 'required|string|max:255',
                'offenseCategory' => 'required|string|in:Minor Offense,Major Offense',
                'specificOffense' => 'required|string|max:255',
                'description' => 'required|string',
                'actionTaken' => 'nullable|string|max:255', 
            ]);

            $recommendation = $this->getDisciplinaryRecommendation(
                $validatedData['studentId'], 
                $validatedData['offenseCategory']
            );

            // Create the incident record with filer_id
            $incident = Incident::create([
                'filer_id' => Auth::id(), // Saves the current user's ID
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
        $user = Auth::user();
        if (!$user) { return response()->json(['message' => 'Unauthenticated.'], 401); }

        try {
            // 🛡️ SECURITY CHECK: Admin OR Filer can update
            if ($user->role !== 'admin' && $incident->filer_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            
            // Validation rules (long list simplified for brevity, assuming you merge them back in)
            $validatedData = $request->validate([ 
                'student_id' => ['required', 'string', 'max:7', 'regex:/^\d{2}-\d{4}$/'], 
                'full_name' => 'required|string|max:255',
                // ... all other fields needed for update ...
                'status' => 'nullable|string|in:Pending,Resolved,Under Review,Closed',
            ]);

            $incident->update($validatedData);

            return response()->json(['message' => 'Incident report updated successfully.', 'incident' => $incident], 200);

        } catch (ValidationException $e) {
            Log::error('Validation Failed during update: ' . json_encode($e->errors()));
            return response()->json(['message' => 'Validation Failed: One or more fields are invalid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Update general error: ' . $e->getMessage());
            return response()->json(['message' => 'An internal error occurred during update.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the status of a specific incident (Admin only).
     */
    public function updateStatus(Request $request, Incident $incident)
    {
        $user = Auth::user();
        if (!$user) { return response()->json(['message' => 'Unauthenticated.'], 401); }
        
        // 🛡️ SECURITY CHECK: Only Admins can update status
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to update incident status.'], 403);
        }
        
        $validated = $request->validate(['status' => 'required|string|in:Pending,Resolved,Under Review,Closed']);

        $incident->status = $validated['status'];
        $incident->save();

        return response()->json(['message' => 'Incident status updated successfully.', 'incident' => $incident], 200);
    }
    
    /**
     * Update only the action taken for a specific incident (Admin only).
     */
    public function updateActionTaken(Request $request, Incident $incident)
    {
        $user = Auth::user();
        if (!$user) { return response()->json(['message' => 'Unauthenticated.'], 401); }
        
        // 🛡️ SECURITY CHECK: Only Admins should record action taken
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to record action taken.'], 403);
        }
        
        try {
            $validated = $request->validate(['action_taken' => 'required|string|max:255']);

            $incident->action_taken = $validated['action_taken'];
            $incident->status = 'Resolved'; 
            $incident->save();

            return response()->json(['message' => 'Disciplinary action successfully recorded and incident resolved.', 'incident' => $incident], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating action taken: ' . $e->getMessage());
            return response()->json(['message' => 'An internal error occurred while recording the action.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified incident report from storage.
     */
    public function destroy(Incident $incident)
    {
        $user = Auth::user();
        if (!$user) { return response()->json(['message' => 'Unauthenticated.'], 401); }
        
        // 🛡️ SECURITY CHECK: Only Admins or the Filer can delete
        if ($user->role !== 'admin' && $incident->filer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to delete this incident.'], 403);
        }

        try {
            $incidentId = $incident->id;
            $incident->delete();
            return response()->json(['message' => "Incident report ID {$incidentId} deleted successfully."], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete the incident report.', 'error' => $e->getMessage()], 500);
        }
    }

    // --- Helper Methods ---
    
    /**
     * Helper function to get the optimal disciplinary action recommendation.
     */
    private function getDisciplinaryRecommendation(string $studentId, string $offenseCategory): string
    {
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

        // Apply the maximum/last offense recommendation if count exceeds keys
        $keys = array_keys($matrix);
        $maxKey = end($keys);

        if ($offenseNumber > $maxKey) {
            return $matrix[$maxKey];
        }

        return 'Default action: Further Review Required.';
    }
}