<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of students (Paginated Index View/API).
     */
    public function index(Request $request) 
    {
        // Start the query
        $query = Student::query();
        
        // If trashed parameter is present, include trashed students
        if ($request->has('trashed')) {
            $query = Student::withTrashed();
        }

        // Filter by program if selected
        if ($request->filled('program')) {
            $query->where('program', $request->program);
        }

        // Search by student number if provided
        if ($request->filled('student_number')) {
            $query->where('student_number', 'like', '%' . $request->student_number . '%');
        }

        // Paginate the results
        $students = $query->orderBy('last_name')->paginate(10);

        // Return JSON for API requests
        if ($request->wantsJson()) {
            return response()->json($students, 200);
        }

        // Fetch distinct programs for the filter dropdown
        $programs = Student::select('program')->distinct()->pluck('program');

        // Pass both $students and $programs to the view
        return view('students.index', compact('students', 'programs'));
    }

    /**
     * Fetch a list of all active students for the frontend dropdown.
     * Maps fields to 'id' (student_number) and 'fullName'.
     */
    public function getAllForDropdown()
    {
        // Select only necessary active students (not trashed)
        $students = Student::select(
            'student_number', 
            'first_name',
            'last_name',
            'program',
            'year_level',
            'section'
        )
        ->get()
        ->map(function ($student) {
            // Map the Laravel model attributes to the format Vue expects
            return [
                'id' => $student->student_number, // The value for the dropdown
                'fullName' => $student->first_name . ' ' . $student->last_name, // The display text
                'program' => $student->program,
                'year_level' => $student->year_level,
                'section' => $student->section,
            ];
        });

        return response()->json($students, 200);
    }
    
    //-------------------------------------------------------------
    
    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        return view('students.create');
    }

    /**
     * Display the specified student.
     */
    public function show($id)
    {
        $student = Student::withTrashed()->find($id);
        
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }
        
        // This is primarily used for API lookups based on PK, adjust output if needed.
        return response()->json([
            'id' => $student->student_number,
            'fullName' => $student->first_name . ' ' . $student->last_name,
            'program' => $student->program,
            'year_level' => $student->year_level,
            'section' => $student->section,
            // ... include other fields ...
        ], 200);
    }

    /**
     * Store a newly created student in storage.
     */
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

        $student = Student::create($validated);

        // Return JSON for API requests
        if ($request->wantsJson()) {
            return response()->json($student, 201);
        }

        return redirect()->route('students.index')
                         ->with('success', 'Student created successfully.');
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit($id)
    {
        $student = Student::withTrashed()->find($id);
        
        if (!$student) {
            return redirect()->route('students.index')
                             ->with('error', 'Student not found.');
        }
        
        // Prevent editing of trashed students
        if ($student->trashed()) {
            return redirect()->route('students.index')
                             ->with('error', 'Cannot edit a deleted student.');
        }
        
        return view('students.edit', compact('student'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, $id)
    {
        $student = Student::withTrashed()->find($id);
        
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }
        
        // Prevent updating of trashed students
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
            'year_level' => 'required|in:1st Year,2nd Year,3rd Year,4th Year,5th Year',
            'section' => 'nullable|string|max:50',
            'contact_number' => 'required|string|max:20',
            'email' => 'required|email|max:150|unique:students,email,' . $student->student_id . ',student_id',
            'address' => 'required|string',
            'guardian_name' => 'required|string|max:150',
            'guardian_contact' => 'required|string|max:50',
        ]);

        $student->update($validated);

        // Return JSON for API requests
        if ($request->wantsJson()) {
            return response()->json($student, 200);
        }

        return redirect()->route('students.index')
                         ->with('success', 'Student updated successfully.');
    }

    /**
     * Soft delete the specified student from storage.
     */
    public function destroy(Request $request, $id)
    {
        $student = Student::withTrashed()->find($id);
        
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->delete();

        // Return JSON for API requests
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Student moved to trash successfully'], 200);
        }

        return redirect()->route('students.index')
                        ->with('success', 'Student deleted successfully.');
    }
    
    /**
     * Restore a soft deleted student.
     */
    public function restore($id)
    {
        $student = Student::withTrashed()->find($id);
        
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }
        
        if ($student->trashed()) {
            $student->restore();
            return response()->json(['message' => 'Student restored successfully'], 200);
        }
        
        return response()->json(['message' => 'Student is not in trash'], 400);
    }
    
    /**
     * Permanently delete a student.
     */
    public function forceDelete($id)
    {
        $student = Student::withTrashed()->find($id);
        
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