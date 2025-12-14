<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\Student;
use App\Models\OffenseCategory;
use App\Models\Offense;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;

class IncidentController extends Controller
{
    const STUDENT_ID_REGEX = '/^\d{2}-\d{3,4}(-TS)?$/';
    const EDIT_ROLES = ['admin', 'staff', 'basic_filer', 'user'];

    const RECOMMENDATION_MATRIX = [
        'Minor Offense' => [
            1 => 'Reprimand and apology, promissory letter, restitution, summons for parent/s guardian/s.',
            2 => 'Suspension from one (1) to four (4) days, community service as determined by the Office of Student Affairs and Services.',
            3 => 'Suspension up to seven (7) days or equivalent community service.',
        ],
        'Major Offense' => [
            1 => 'Suspension from five (5) to ten (10) days or Community Service, as determined by the Office of Student Affairs and Services.',
            2 => 'Suspension from eleven (11) to fifteen (15) days.',
            2 => 'Suspension from eleven (11) to fifteen (15) days.',
            3 => 'Suspension up to forty-five (45) calendar days to dismissal depending upon the gravity of the offense after due process.',
        ],
    ];

    private function authorizeDelete(User $user, Incident $incident)
    {
        if ($user->role === 'admin') {
            return null;
        }

        if ($incident->filer_id == $user->id) {
            return null;
        }

        return response()->json([
            'message' => 'Unauthorized to delete this incident. Only the Admin or the original Filer can delete the report.'
        ], 403);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $query = Incident::with(['student.program', 'category', 'offense']);

        if ($request->has('trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('all')) {
            $query->withTrashed();
        } else {
            $query->withoutTrashed();
        }

        if ($user->role !== 'admin') {
            $query->where('filer_id', $user->id);
        }

        $incidents = $query->orderBy('id', 'desc')->get();

        $mappedIncidents = $incidents->map(function ($incident) {
            $studentFullName = trim("{$incident->student?->first_name} {$incident->student?->last_name}") ?: 'N/A';

            return [
                'id'               => $incident->id,
                'full_name'        => $studentFullName,
                'specific_offense' => $incident->offense?->name ?? 'N/A',
                'offense_category' => $incident->category?->name ?? 'N/A',
                'date_of_incident' => $incident->date_of_incident,
                'time_of_incident' => $incident->time_of_incident,
                'status'           => $incident->status,
                'deleted_at'       => $incident->deleted_at,
            ];
        });

        return response()->json(['data' => $mappedIncidents]);
    }

    public function show($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $incident = Incident::withTrashed()
            ->with(['student.program', 'category', 'offense'])
            ->find($id);

        if (!$incident) {
            return response()->json(['message' => 'Incident not found.'], 404);
        }

        if ($user->role !== 'admin' && $incident->filer_id != $user->id) {
            return response()->json(['message' => 'Unauthorized access to incident report.'], 403);
        }

        $programName = $incident->student?->program?->code
            ?? $incident->student?->program?->name
            ?? 'N/A';

        $mappedIncident = [
            'id'                  => $incident->id,
            'incidentId'          => $incident->id,
            'student_id'          => $incident->student_id,
            'filer_id'            => $incident->filer_id,
            'date_of_incident'    => $incident->date_of_incident,
            'time_of_incident'    => $incident->time_of_incident,
            'location'            => $incident->location,
            'description'         => $incident->description,
            'status'              => $incident->status,
            'recommendation'      => $incident->recommendation,
            'actionTaken'         => $incident->disciplinary_action,
            'full_name'           => $incident->student ? "{$incident->student->first_name} {$incident->student->last_name}" : 'N/A',
            'program'             => $programName,
            'year_level'          => $incident->student?->year_level ?? 'N/A',
            'section'             => $incident->student?->section ?? 'N/A',
            'offenseCategory'     => $incident->category?->name ?? 'N/A',
            'specificOffense'     => $incident->offense?->name ?? 'N/A',
            'category_id'         => $incident->category_id,
            'specific_offense_id' => $incident->specific_offense_id,
            'program_id'          => $incident->student?->program_id,
        ];

        return response()->json(['incident' => $mappedIncident]);
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            $validatedData = $request->validate([
                'student_id' => ['required', 'string', 'max:10', 'regex:' . self::STUDENT_ID_REGEX],
                'offenseCategory' => 'required|string|max:255',
                'specificOffense' => 'required|string|max:255',
                'dateOfIncident' => ['required', 'date', function ($attribute, $value, $fail) {
                    if (date('w', strtotime($value)) == 0) {
                        $fail('The date of incident cannot be a Sunday.');
                    }
                }],
                'timeOfIncident' => ['required', 'date_format:H:i', function ($attribute, $value, $fail) {
                    $start = strtotime('07:00');
                    $end = strtotime('17:00');
                    $inputTime = strtotime($value);
                    if ($inputTime < $start || $inputTime > $end) {
                        $fail('The time of incident must be between 7:00 AM and 5:00 PM.');
                    }
                }],
                'location' => 'required|string|max:255',
                'description' => 'required|string',
                'actionTaken' => 'nullable|string|max:255',
                'fullName' => 'nullable|string',
                'program' => 'nullable|string',
                'yearLevel' => 'nullable|string',
                'section' => 'nullable|string',
            ]);

            $studentIdInput = $validatedData['student_id'];
            $student = Student::where('student_number', $studentIdInput)->first();
            if (!$student) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'studentNumber' => ["The student with ID {$studentIdInput} does not exist in the record."]
                    ]
                ], 422);
            }

            $offenseCategory = OffenseCategory::where('name', $validatedData['offenseCategory'])->first();
            if (!$offenseCategory) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => ['offenseCategory' => ['Invalid offense category provided.']]
                ], 422);
            }

            $specificOffense = Offense::where('category_id', $offenseCategory->id)
                ->where('name', $validatedData['specificOffense'])
                ->first();

            if (!$specificOffense) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => ['specificOffense' => ['Invalid specific offense provided for the selected category.']]
                ], 422);
            }

            $recommendation = $this->getDisciplinaryRecommendation(
                $studentIdInput,
                $offenseCategory->name
            );

            $incident = Incident::create([
                'filer_id' => Auth::id(),
                'student_id' => $studentIdInput,
                'category_id' => $offenseCategory->id,
                'specific_offense_id' => $specificOffense->id,
                'date_of_incident' => $validatedData['dateOfIncident'],
                'time_of_incident' => $validatedData['timeOfIncident'],
                'location' => $validatedData['location'],
                'description' => $validatedData['description'],
                'status' => 'Pending',
                'disciplinary_action' => $recommendation,
            ]);

            return response()->json([
                'message' => 'Incident report filed successfully.',
                'incident' => $incident,
                'recommendation' => $recommendation
            ], 201);
        } catch (ValidationException $e) {
            $errorMap = [];
            foreach ($e->errors() as $key => $messages) {
                $errorMap[Str::camel($key)] = $messages;
            }
            return response()->json([
                'message' => 'Validation failed. Please correct the highlighted fields.',
                'errors' => $errorMap
            ], 422);
        } catch (\Exception $e) {
            Log::error('Store Incident Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while filing the report.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $incident = Incident::withTrashed()->find($id);
        if (!$incident) {
            return response()->json(['message' => 'Incident not found.'], 404);
        }

        if ($incident->trashed() && $request->input('status') !== 'Deleted') {
            return response()->json(['message' => 'Cannot update a deleted incident.'], 400);
        }

        try {
            $canEdit = (
                $user->role === 'admin' ||
                $incident->filer_id === $user->id ||
                in_array($user->role, self::EDIT_ROLES)
            );

            if (!$canEdit) {
                return response()->json([
                    'message' => 'Unauthorized action. You must be the filer or have a designated administrative role to edit this report.'
                ], 403);
            }

            $validatedData = $request->validate([
                'studentId' => ['sometimes', 'required', 'string', 'max:10', 'regex:' . self::STUDENT_ID_REGEX],
                'categoryId' => 'sometimes|required|integer|exists:offense_categories,id',
                'specificOffenseId' => 'sometimes|required|integer|exists:offenses,id',
                'dateOfIncident' => 'sometimes|required|date',
                'timeOfIncident' => 'sometimes|required|date_format:H:i',
                'location' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'status' => ['sometimes', 'nullable', 'string', 'in:Pending,Investigation,Resolved,Closed,Deleted'],
                'actionTaken' => 'sometimes|nullable|string|max:255',
                'recommendation' => 'sometimes|nullable|string|max:255',
                'fullName' => 'nullable|string',
                'program' => 'nullable|string',
                'yearLevel' => 'nullable|string',
                'section' => 'nullable|string',
            ]);

            $updateData = [];
            foreach ($validatedData as $key => $value) {
                if (!in_array($key, ['fullName', 'program', 'yearLevel', 'section', 'recommendation', 'actionTaken'])) {
                    $updateData[Str::snake($key)] = $value;
                }
            }

            if (isset($validatedData['studentId'])) {
                $updateData['student_id'] = $validatedData['studentId'];
            }
            if (isset($validatedData['categoryId'])) {
                $updateData['category_id'] = $validatedData['categoryId'];
            }
            if (isset($validatedData['specificOffenseId'])) {
                $updateData['specific_offense_id'] = $validatedData['specificOffenseId'];
            }
            if (isset($validatedData['actionTaken'])) {
                $updateData['disciplinary_action'] = $validatedData['actionTaken'];
            }

            if (isset($updateData['status']) && $updateData['status'] === 'Deleted') {
                if ($user->role === 'admin' || $incident->filer_id == $user->id) {
                    $incident->delete();
                    return response()->json(['message' => 'Incident report soft-deleted successfully.'], 200);
                }
                return response()->json([
                    'message' => 'Unauthorized: Only Admin or the original Filer can soft-delete this report.'
                ], 403);
            }

            if ($user->role === 'admin' && ($request->has('categoryId') || $request->has('studentId'))) {
                $newCategoryId = $updateData['category_id'] ?? $incident->category_id;
                $newStudentId = $updateData['student_id'] ?? $incident->student_id;
                $offenseCategory = OffenseCategory::find($newCategoryId);

                if ($offenseCategory) {
                    $calculatedRecommendation = $this->getDisciplinaryRecommendation(
                        $newStudentId,
                        $offenseCategory->name,
                        $incident->id
                    );

                    if (!isset($updateData['disciplinary_action'])) {
                        $updateData['disciplinary_action'] = $calculatedRecommendation;
                    }
                }
            }

            $incident->update($updateData);

            return response()->json([
                'message' => 'Incident report updated successfully.',
                'incident' => $incident
            ], 200);
        } catch (ValidationException $e) {
            Log::error('Validation Failed during update: ' . json_encode($e->errors()));
            $errorMap = [];
            foreach ($e->errors() as $key => $messages) {
                $errorMap[Str::camel($key)] = $messages;
            }
            return response()->json([
                'message' => 'Validation Failed: One or more fields are invalid.',
                'errors' => $errorMap
            ], 422);
        } catch (\Exception $e) {
            Log::error('Update general error: ' . $e->getMessage());
            Log::error('Update Exception Trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'An internal error occurred during update.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $incident = Incident::withTrashed()->find($id);
        if (!$incident) {
            return response()->json(['message' => 'Incident not found.'], 404);
        }

        if ($incident->trashed()) {
            return response()->json(['message' => 'Cannot update status of a deleted incident.'], 400);
        }

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to update incident status.'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:Pending,Investigation,Resolved,Closed'
        ]);

        $incident->status = $validated['status'];
        $incident->save();

        return response()->json([
            'message' => 'Incident status updated successfully.',
            'incident' => $incident
        ], 200);
    }

    public function updateActionTaken(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $incident = Incident::withTrashed()->find($id);
        if (!$incident) {
            return response()->json(['message' => 'Incident not found.'], 404);
        }

        if ($incident->trashed()) {
            return response()->json(['message' => 'Cannot update action taken for a deleted incident.'], 400);
        }

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to record action taken.'], 403);
        }

        try {
            $validated = $request->validate([
                'actionTaken' => 'required|string|max:255'
            ]);

            $incident->disciplinary_action = $validated['actionTaken'];
            $incident->status = 'Resolved';
            $incident->save();

            return response()->json([
                'message' => 'Disciplinary action successfully recorded and incident resolved.',
                'incident' => $incident
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

    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $incident = Incident::withTrashed()->find($id);
        if (!$incident) {
            return response()->json(['message' => 'Incident not found.'], 404);
        }

        $authResponse = $this->authorizeDelete($user, $incident);
        if ($authResponse) {
            return $authResponse;
        }

        if ($incident->trashed()) {
            return response()->json(['message' => 'Incident is already deleted.'], 400);
        }

        try {
            $incident->delete();
            return response()->json([
                'message' => "Incident report ID {$id} moved to trash successfully."
            ], 200);
        } catch (\Exception $e) {
            Log::error('Soft Delete Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete the incident report.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to restore incidents.'], 403);
        }

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

    public function forceDelete($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized to permanently delete incidents.'], 403);
        }

        $incident = Incident::withTrashed()->find($id);
        if (!$incident) {
            return response()->json(['message' => 'Incident not found.'], 404);
        }

        if ($incident->trashed()) {
            $incident->forceDelete();
            return response()->json(['message' => 'Incident permanently deleted.'], 200);
        }

        return response()->json([
            'message' => 'Incident must be soft deleted before permanent deletion.'
        ], 400);
    }

    private function getDisciplinaryRecommendation(
        string $studentId,
        string $offenseCategoryName,
        $excludeIncidentId = null
    ): string {
        $query = Incident::where('student_id', $studentId)
            ->whereHas('category', function ($q) use ($offenseCategoryName) {
                $q->where('name', $offenseCategoryName);
            })
            ->whereNotNull('disciplinary_action');

        if ($excludeIncidentId !== null) {
            $query->where('id', '!=', $excludeIncidentId);
        }

        $previousOffenseCount = $query->count();
        $offenseNumber = $previousOffenseCount + 1;
        $matrix = self::RECOMMENDATION_MATRIX[$offenseCategoryName] ?? [];

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
}
