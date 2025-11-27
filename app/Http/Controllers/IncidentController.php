<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Not strictly needed, but kept for clarity

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

    /**
     * 🎯 FIX Applied Here: Display a listing of incident reports.
     * Filters results based on the authenticated user's role.
     */
    public function index()
    {
        $user = Auth::user();
        
        // 🚨 FIX: If Auth::user() is null (missing/expired token), return 401 instead of crashing (500).
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        // Start the base query
        $query = Incident::orderBy('id', 'desc');

        // Apply filtering unless the user is an 'admin'
        if ($user->role !== 'admin') {
            // Filter to show only incidents filed by the current user
            $query->where('filer_id', $user->id); 
        } 
        // If $user->role IS 'admin', no filter is applied.

        $incidents = $query->get();

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
        
        $user = Auth::user();
        
        // 🛡️ SECURITY FIX: Check for null user first
        if (!$user) {
             return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        // Final Security Check
        if ($user->role !== 'admin' && $incident->filer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized access to incident report.'], 403);
        }
        
        return response()->json($incident);
    }
    
    /**
     * Helper function to get the optimal disciplinary action recommendation.
     */
    private function getDisciplinaryRecommendation(string $studentId, string $offenseCategory): string
    {
        // ... (Logic remains the same)
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
         // 🛡️ SECURITY FIX: Check for null user before Auth::id() is called
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            // ... (Validation code remains the same)
            $validatedData = $request->validate([
                'studentId' => ['required', 'string', 'max:7', 'regex:/^\d{2}-\d{4}$/'], 
                'fullName' => 'required|string|max:255',
                'program' => ['nullable', 'string', 'max:255', 'in:' . implode(',', self::VALID_PROGRAMS)],
                'yearLevel' => 'required|string|max:50',
                'section' => 'nullable|string|max:50',
                'dateOfIncident' => [ /* ... validation ... */ ],
                'timeOfIncident' => [ /* ... validation ... */ ],
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

            // Create the incident record
            $incident = Incident::create([
                'filer_id' => Auth::id(), // Auth::id() is safe since we checked Auth::check()
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
        // 🛡️ SECURITY FIX: Check for null user first
        if (!$user) {
             return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            // Security check using the null-checked $user
            if ($user->role !== 'admin' && $incident->filer_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            
            // ... (Validation and update code remains the same)
            $validatedData = $request->validate([
                // ... (long list of validation rules)
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
         $user = Auth::user();
        // 🛡️ SECURITY FIX: Check for null user first
        if (!$user) {
             return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        // SECURITY CHECK: Typically only Admins should update status
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to update incident status.'], 403);
        }
        
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
     * Update only the action taken for a specific incident.
     */
    public function updateActionTaken(Request $request, Incident $incident)
    {
         $user = Auth::user();
        // 🛡️ SECURITY FIX: Check for null user first
        if (!$user) {
             return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        // SECURITY CHECK: Typically only Admins should take action
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to record action taken.'], 403);
        }
        
        try {
            $validated = $request->validate([
                'action_taken' => 'required|string|max:255',
            ]);

            $incident->action_taken = $validated['action_taken'];
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
        $user = Auth::user();
        // 🛡️ SECURITY FIX: Check for null user first
        if (!$user) {
             return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        // SECURITY CHECK: Only Admins or the Filer can delete
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
}