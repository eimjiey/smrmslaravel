<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident; // Ensure this line exists and is correct
use Illuminate\Validation\ValidationException;

class IncidentController extends Controller
{
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
     * Store a newly created incident report in the database.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'studentId' => 'required|string|max:255', 
                'fullName' => 'required|string|max:255',
                'program' => 'nullable|string|max:255',
                'yearLevel' => 'required|string|max:50',
                'section' => 'nullable|string|max:50',
                'dateOfIncident' => 'required|date',
                'timeOfIncident' => 'required|date_format:H:i', // FIXED: H:i
                'location' => 'required|string|max:255',
                'offenseCategory' => 'required|string|in:Minor Offense,Major Offense',
                'specificOffense' => 'required|string|max:255',
                'description' => 'required|string',
            ]);

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
            ]);
            return response()->json(['message' => 'Incident report filed successfully.', 'incident' => $incident], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while filing the report.', 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update the specified incident report fully.
     */
    public function update(Request $request, Incident $incident)
    {
        try {
            // 1. Validation 
            $validatedData = $request->validate([
                'student_id' => 'required|string|max:255', 
                'full_name' => 'required|string|max:255',
                'program' => 'nullable|string|max:255',
                'year_level' => 'required|string|max:50',
                'section' => 'nullable|string|max:50',
                'date_of_incident' => 'required|date',
                'time_of_incident' => 'required|date_format:H:i', // FIXED: Changed from H:i:s to H:i
                'location' => 'required|string|max:255',
                'offense_category' => 'required|string|in:Minor Offense,Major Offense',
                'specific_offense' => 'required|string|max:255',
                'description' => 'required|string',
            ]);

            // 2. Perform the update
            $incident->update($validatedData);

            // 3. Return success response
            return response()->json([
                'message' => 'Incident report updated successfully.',
                'incident' => $incident,
            ], 200);

        } catch (ValidationException $e) {
             // Log the detailed errors for backend debugging
             \Log::error('Validation Failed during full update: ' . json_encode($e->errors()));
             return response()->json([
                'message' => 'Validation Failed: One or more fields are invalid.',
                'errors' => $e->errors() 
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Full update general error: ' . $e->getMessage());
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