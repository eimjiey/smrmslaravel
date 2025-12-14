<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use Illuminate\Http\JsonResponse;

class ProgramController extends Controller
{
    public function index()
    {
        $program = Program::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => Program::all()
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $program = Program::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Program created successfully',
            'data' => $program,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $program = Program::find($id);

        if (!$program) {
            return response()->json([
                'status' => 'error',
                'message' => 'Program not found',
            ], 404);
        }

        return response()->json([
            $program
        ]);
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $program = Program::find($id);

        if (!$program) {
            return response()->json([
                'status' => 'error',
                'message' => 'Program not found',
            ], 404);
        }

        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
        ]);

        $program->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Program updated successfully',
            'data' => $program,
        ]);
    }

    public function destroy(string $id)
    {
        $program = Program::find($id);

        if (!$program) {
            return response()->json([
                'status' => 'error',
                'message' => 'Program not found',
            ], 404);
        }

        $program->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Program deleted successfully',
        ]);
    }
}
