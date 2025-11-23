<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncidentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class IncidentController extends Controller
{
    /**
     * Store a newly created incident report in storage.
     *
     * @param  \App\Http\Requests\StoreIncidentRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreIncidentRequest $request): JsonResponse
    {
        // $request->validated() automatically contains all validated data
        $validatedData = $request->validated();

        // ----------------------------------------------------------------------
        // NOTE: In a real application, this is where you would save the data 
        // to your database, e.g., using Eloquent:
        //
        // $incident = Incident::create($validatedData);
        // ----------------------------------------------------------------------

        // For demonstration, we'll just log the data and return a success response.
        Log::info('New Incident Report Received', $validatedData);

        return response()->json([
            'message' => 'Incident report filed successfully.',
            'data' => $validatedData
        ], 201); // 201 Created status
    }
}