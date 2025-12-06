<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\Student; 
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 

class IncidentController extends Controller
{
    // Define the single, comprehensive regex for student IDs
    // Matches: 
    // 1. XX-XXXX (e.g., 22-0612)
    // 2. XX-XXX-TS (e.g., 23-0660-TS or 23-063-TS if the numbers can be 3 or 4 digits)
    // **FIXED REGEX**: It now handles both formats. I've allowed 3 or 4 digits in the middle for flexibility.
    const STUDENT_ID_REGEX = '/^\d{2}-\d{3,4}(-TS)?$/';

    const VALID_PROGRAMS = [
        'BSIT', 'BSCS', 'BSDSA', 'BLIS', 'BSIS', 
    ];

    const RECOMMENDATION_MATRIX = [
    'Minor Offense' => [
        1 => 'Reprimand and apology, promissory letter, restitution, summons for parent/s guardian/s.',
        2 => 'Suspension from one (1) to four (4) days, community service as determined by the Office of Student Affairs and Services.',
        // Added 3rd offense to handle escalating cases
        3 => 'Suspension up to seven (7) days or equivalent community service.',
    ],
    'Major Offense' => [
        1 => 'Suspension from five (5) to ten (10) days or Community Service, as determined by the Office of Student Affairs and Services.',
        2 => 'Suspension from eleven (11) to fifteen (15) days.',
        3 => 'Suspension up to forty-five (45) calendar days to dismissal depending upon the gravity of the offense after due process.',
    ],
];

    /**
     * Display a listing of incident reports.
     * Filters results based on the authenticated user's role (Admin sees all, User sees own).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // 🛡️ SECURITY CHECK: Must be authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        // Get only non-deleted incidents by default
        $query = Incident::query();
        
        // If trashed parameter is present, include trashed incidents
        if ($request->has('trashed')) {
            $query = Incident::withTrashed();
        }
        
        // Apply filtering unless the user is an 'admin'
        if ($user->role !== 'admin') {
            // Note: The original had $query = Incident::withTrashed() which overwrites previous query
            // $query is a clean query at this point, but applying withTrashed() inside the if
            // means a non-admin cannot see their own trashed reports, which may be desired.
            // If the intent is for a non-admin to see *their own* trashed reports when $request->has('trashed'), 
            // the logic for $query = Incident::withTrashed(); should be before $query = Incident::query(); and 
            // should not be re-assigned. The original logic is kept for simplicity as it filters to non-deleted reports by default.
            $query->where('filer_id', $user->id); 
        } 

        $incidents = $query->orderBy('id', 'desc')->get();

        return response()->json($incidents);
    }
    
    /**
     * Display the specified incident report.
     * Uses Route Model Binding to automatically handle 404 if incident is not found.
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // 🛡️ SECURITY CHECK 1: Must be authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        // Find the incident including trashed ones
        $incident = Incident::withTrashed()->find($id);
        
        if (!$incident) {
            return response()->json(['message' => 'Incident not found.'], 404);
        }
        
        // 🛡️ SECURITY CHECK 2 (Authorization Fix):
        if ($user->role != 'admin' && $incident->filer_id != $user->id) {
            return response()->json(['message' => 'Unauthorized access to incident report.'], 403);
        }
        
        return response()->json($incident);
    }
    
    /**
     * Update the specified incident report fully.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) { return response()->json(['message' => 'Unauthenticated.'], 401); }

        // Find the incident including trashed ones
        $incident = Incident::withTrashed()->find($id);
        
        if (!$incident) {
            return response()->json(['message' => 'Incident not found.'], 404);
        }
        
        // Check if incident is trashed
        if ($incident->trashed()) {
            return response()->json(['message' => 'Cannot update a deleted incident.'], 400);
        }

        try {
            // 🛡️ SECURITY CHECK: Admin OR Filer can update
            if ($user->role !== 'admin' && $incident->filer_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            
            // Validation rules
            $validatedData = $request->validate([ 
                // **FIX APPLIED HERE**: Use the comprehensive regex
                'student_id' => ['required', 'string', 'max:10', 'regex:' . self::STUDENT_ID_REGEX], 
                'full_name' => 'required|string|max:255',
                'status' => 'nullable|string|in:Pending,Resolved,Under Review,Closed',
                // Add any other fields needed for update...
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
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) { return response()->json(['message' => 'Unauthenticated.'], 401); }
        
        // Find the incident including trashed ones
        $incident = Incident::withTrashed()->find($id);
        
        if (!$incident) {
            return response()->json(['message' => 'Incident not found.'], 404);
        }
        
        // Check if incident is trashed
        if ($incident->trashed()) {
            return response()->json(['message' => 'Cannot update status of a deleted incident.'], 400);
        }
        
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
    public function updateActionTaken(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) { return response()->json(['message' => 'Unauthenticated.'], 401); }
        
        // Find the incident including trashed ones
        $incident = Incident::withTrashed()->find($id);
        
        if (!$incident) {
            return response()->json(['message' => 'Incident not found.'], 404);
        }
        
        // Check if incident is trashed
        if ($incident->trashed()) {
            return response()->json(['message' => 'Cannot update action taken for a deleted incident.'], 400);
        }
        
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
     * Soft delete the specified incident report from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user) { return response()->json(['message' => 'Unauthenticated.'], 401); }
        
        // Find the incident including trashed ones
        $incident = Incident::withTrashed()->find($id);
        
        if (!$incident) {
            return response()->json(['message' => 'Incident not found.'], 404);
        }
        
        // 🛡️ SECURITY CHECK: Only Admins or the Filer can delete
        if ($user->role !== 'admin' && $incident->filer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to delete this incident.'], 403);
        }

        try {
            // Perform soft delete
            $incident->delete();
            return response()->json(['message' => "Incident report ID {$id} moved to trash successfully."], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete the incident report.', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Restore a soft deleted incident.
     */
    public function restore($id)
    {
        $user = Auth::user();
        if (!$user) { return response()->json(['message' => 'Unauthenticated.'], 401); }
        
        // Only admins can restore incidents
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to restore incidents.'], 403);
        }
        
        // Find the trashed incident
        $incident = Incident::withTrashed()->find($id);
        
        if (!$incident) {
            return response()->json(['message' => 'Incident not found.'], 404);
        }
        
        if ($incident->trashed()) {
            $incident->restore();
            return response()->json(['message' => 'Incident restored successfully.'], 200);
        }
        
        return response()->json(['message' => 'Incident is not in trash.'], 400);
    }
    
    /**
     * Permanently delete an incident.
     */
    public function forceDelete($id)
    {
        $user = Auth::user();
        if (!$user) { return response()->json(['message' => 'Unauthenticated.'], 401); }
        
        // Only admins can permanently delete incidents
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to permanently delete incidents.'], 403);
        }
        
        // Find the trashed incident
        $incident = Incident::withTrashed()->find($id);
        
        if (!$incident) {
            return response()->json(['message' => 'Incident not found.'], 404);
        }
        
        if ($incident->trashed()) {
            $incident->forceDelete();
            return response()->json(['message' => 'Incident permanently deleted.'], 200);
        }
        
        return response()->json(['message' => 'Incident must be soft deleted before permanent deletion.'], 400);
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
                // **FIX APPLIED HERE**: Use the comprehensive regex
                'studentId' => ['required', 'string', 'max:15', 'regex:' . self::STUDENT_ID_REGEX], 
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

            // --- 1. Student Existence Check ---
            $studentIdInput = $validatedData['studentId'];
            // Using student_number for consistency with typical database naming
            $student = Student::where('student_number', $studentIdInput)->first(); 
            
            if (!$student) {
                return response()->json([
                    'message' => 'The given data was invalid.', 
                    'errors' => [
                        'studentId' => ["The student with ID **{$studentIdInput}** does not exist in the record."]
                    ]
                ], 422);
            }

            // --- 2. Data Consistency Check (Verification) ---
            $verificationErrors = [];
            
            // a. Full Name Verification
            $dbFullName = trim(
                $student->first_name . ' ' . 
                $student->last_name
            );
            $inputFullName = trim($validatedData['fullName']);
            
            if (strcasecmp($inputFullName, $dbFullName) !== 0) {
                $verificationErrors['fullName'][] = "The submitted name ('{$inputFullName}') does not match the name on record ('{$dbFullName}') for this Student ID.";
            }

            // b. Program Verification
            if (!empty($validatedData['program']) && strcasecmp($validatedData['program'], $student->program) !== 0) {
                 $verificationErrors['program'][] = "The submitted program ('{$validatedData['program']}') does not match the program on record ('{$student->program}').";
            }
            
            // c. Year Level Verification
            if (strcasecmp($validatedData['yearLevel'], $student->year_level) !== 0) {
                 $verificationErrors['yearLevel'][] = "The submitted year level ('{$validatedData['yearLevel']}') does not match the year level on record ('{$student->year_level}').";
            }
            
            // d. Section Verification (Optional field, check only if provided)
            if (!empty($validatedData['section']) && strcasecmp($validatedData['section'], $student->section) !== 0) {
                 $verificationErrors['section'][] = "The submitted section ('{$validatedData['section']}') does not match the section on record ('{$student->section}').";
            }

            // --- 3. Return Mismatch Errors (if any) ---
            if (!empty($verificationErrors)) {
                return response()->json([
                    'message' => 'Data verification failed. Please check the student details.',
                    'errors' => $verificationErrors
                ], 422);
            }
            
            // --- 4. Proceed with Incident Creation ---
            
            $recommendation = $this->getDisciplinaryRecommendation(
                $studentIdInput, 
                $validatedData['offenseCategory']
            );

            // Create the incident record with filer_id
            $incident = Incident::create([
                'filer_id' => Auth::id(), 
                'student_id' => $studentIdInput, 
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
            // Catches standard validation errors (e.g., missing fields, bad format)
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Store Incident Error: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while filing the report.', 'error' => $e->getMessage()], 500);
        }
    }

    // --- Helper Methods ---
    
    /**
     * Helper function to get the optimal disciplinary action recommendation.
     */
    private function getDisciplinaryRecommendation(string $studentId, string $offenseCategory): string
    {
        // Count previous offenses that are NOT the current one (i.e., not null deleted_at AND not the current one being created)
        $previousOffenseCount = Incident::where('student_id', $studentId)
            ->where('offense_category', $offenseCategory)
            ->whereNotNull('action_taken') // Only count incidents where action has been recorded/taken
            ->count();
            
        // The offense number for the CURRENT incident is the count of previous actions taken + 1
        $offenseNumber = $previousOffenseCount + 1;
        $matrix = self::RECOMMENDATION_MATRIX[$offenseCategory] ?? [];

        if (empty($matrix)) {
            return 'No specific recommendation found for this category.';
        }

        // Check for an exact match for the offense number
        if (isset($matrix[$offenseNumber])) {
            return $matrix[$offenseNumber];
        }

        // Apply the maximum/last offense recommendation if count exceeds keys (e.g., 4th offense gets the 3rd recommendation)
        $keys = array_keys($matrix);
        $maxKey = end($keys);

        if ($offenseNumber > $maxKey) {
            return $matrix[$maxKey];
        }

        return 'Default action: Further Review Required.';
    }
}