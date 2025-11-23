<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(Request $request) 
    {
        // Start the query
        $query = Student::query();

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
     * Show the form for creating a new student.
     */
    public function create()
    {
        return view('students.create');
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        return response()->json($student, 200);
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
    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|max:20|unique:students,student_number,' . $student->student_id . ',student_id',
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
     * Remove the specified student from storage.
     */
    public function destroy(Request $request, Student $student)
    {
        $student->delete();

        // Return JSON for API requests
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Student deleted successfully'], 200);
        }

        return redirect()->route('students.index')
                        ->with('success', 'Student deleted successfully.');
    }
}
