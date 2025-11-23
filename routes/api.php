<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\IncidentController;
// Added import for a new User-specific Controller
use App\Http\Controllers\UserController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


// Student API routes
Route::get('/students', [StudentController::class, 'index']);
Route::post('/students', [StudentController::class, 'store']);
Route::get('/students/{student}', [StudentController::class, 'show']);
Route::put('/students/{student}', [StudentController::class, 'update']);
Route::delete('/students/{student}', [StudentController::class, 'destroy']);


// User Profile Routes: Allows Flutter/frontend to fetch details of the currently authenticated user
// Middleware ensures the user is logged in before accessing these routes.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'current']);
    // New route for fetching user profile details
    Route::get('/me', [UserController::class, 'current']);
});


// This route handles the POST request from your Vue component
Route::post('incidents', [IncidentController::class, 'store']);