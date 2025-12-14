<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Program; 
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; 

class StudentController extends Controller
{
    public function index(Request $request) 
    {
        $query = Student::with(['program']); 
        
        if ($request->has('trashed')) {
            $query->withTrashed();
        }

        if ($request->filled('program')) {
            $programCode = $request->program;
            
            $query->whereHas('program', function ($q) use ($programCode) {
                $q->where('code', $programCode)
                  ->orWhere('description', $programCode);
            });
        }
        
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('student_number', 'like', $search)
                  ->orWhere('first_name', 'like', $search)
                  ->orWhere('last_name', 'like', $search)
                  ->orWhereHas('program', function($q2) use ($search) {
                       $q2->where('code', 'like', $search)
                          ->orWhere('description', 'like', $search);
                  });
            });
        }
        
        $students = $query->orderBy('last_name')->paginate($request->input('per_page', 10));

        if ($request->wantsJson()) {
            $students->getCollection()->transform(function ($student) {
                $programName = $student->program?->code ?? $student->program?->name ?? 'N/A';
                
                return array_merge($student->toArray(), [
                    'full_name' => "{$student->first_name} {$student->last_name}",
                    'program' => $programName, 
                ]);
            });
            return response()->json($students, 200);
        }

        $programs = Student::select('program')->distinct()->pluck('program');
        return view('students.index', compact('students', 'programs'));
    }

    public function getAllForDropdown()
    {
        try {
            $students = Student::with(['program'])
                ->select(
                    'student_id', 'student_number', 'first_name', 'last_name', 
                    'program_id', 'year_level', 'section'
                )
                ->get()
                ->map(function ($student) {
                    
                    $programName = $student->program?->code ?? $student->program?->name ?? 'N/A';
                    
                    return [
                        'id' => $student->student_id,
                        'student_id' => $student->student_number, 
                        'full_name' => "{$student->first_name} {$student->last_name}",
                        'program_id' => $student->program_id, 
                        'program' => $programName,
                        'year_level' => $student->year_level,
                        'section' => $student->section,
                        'first_name' => $student->first_name, 
                        'last_name' => $student->last_name, 
                    ];
                });

            return response()->json($students, 200);

        } catch (\Exception $e) {
            Log::error('Error fetching students for dropdown: ' . $e->getMessage());
            return response()->json([
                'message' => 'Server error loading student list. Check logs for details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        $student = Student::withTrashed()->where('student_id', $id)->first(); 
        
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json($student, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|max:20|unique:students',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female,Other',
            'date_of_birth' => 'required|date',
            'program' => 'required|string|max:150',
            'year_level' => 'required|in:1st Year,2nd Year,3rd Year,4th Year',
            'section' => 'nullable|string|max:50',
            'contact_number' => 'required|string|max:20',
            'email' => 'required|email|max:150|unique:students',
            'address' => 'required|string',
            'guardian_name' => 'required|string|max:150',
            'guardian_contact' => 'required|string|max:50',
        ]);
        
        $programCode = $validated['program'];
        $program = Program::where('code', $programCode)
            ->orWhere('description', $programCode)
            ->first();

        if (!$program) {
            throw ValidationException::withMessages([
                'program' => ['The selected program is invalid or does not exist.']
            ]);
        }
        
        $createData = array_merge($validated, [
            'program_id' => $program->id,
            'program' => $programCode, 
        ]);
        
        $student = Student::create($createData);

        if ($request->wantsJson()) {
            return response()->json($student, 201);
        }

        return redirect()->route('students.index')->with('success', 'Student created successfully.');
    }

    public function update(Request $request, $id)
    {
        $student = Student::withTrashed()->where('student_id', $id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        if ($student->trashed()) {
            return response()->json(['message' => 'Cannot update a deleted student'], 400);
        }

        $validated = $request->validate([
            'student_number' => 'required|string|max:10|unique:students,student_number,' . $student->student_id . ',student_id', 
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:Male,Female,Other',
            'date_of_birth' => 'required|date',
            'program' => 'required|string|max:150',
            'year_level' => 'required|in:1st Year,2nd Year,3rd Year,4th Year',
            'section' => 'nullable|string|max:50',
            'contact_number' => 'required|string|max:20',
            'email' => 'required|email|max:150|unique:students,email,' . $student->student_id . ',student_id',
            'address' => 'required|string',
            'guardian_name' => 'required|string|max:150',
            'guardian_contact' => 'required|string|max:50',
        ]);
        
        $programCode = $validated['program'];
        $program = Program::where('code', $programCode)
            ->orWhere('description', $programCode)
            ->first();
        
        if (!$program) {
            throw ValidationException::withMessages([
                'program' => ['The selected program is invalid or does not exist.']
            ]);
        }
        
        $updateData = array_merge($validated, [
            'program_id' => $program->id,
            'program' => $programCode, 
        ]);

        $student->update($updateData);

        if ($request->wantsJson()) {
            return response()->json($student, 200);
        }

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $student = Student::withTrashed()->where('student_id', $id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Student moved to trash successfully'], 200);
        }

        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }
    
    public function restore($id)
    {
        $student = Student::withTrashed()->where('student_id', $id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        if ($student->trashed()) {
            $student->restore();
            return response()->json(['message' => 'Student restored successfully'], 200);
        }

        return response()->json(['message' => 'Student is not in trash'], 400);
    }
    
    public function forceDelete($id)
    {
        $student = Student::withTrashed()->where('student_id', $id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        if ($student->trashed()) {
            $student->forceDelete();
            return response()->json(['message' => 'Student permanently deleted'], 200);
        }

        return response()->json(['message' => 'Student must be soft deleted before permanent deletion'], 400);
    }
}
