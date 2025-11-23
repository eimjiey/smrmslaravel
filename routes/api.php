<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;

// --- Authentication Routes (Public) ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// --- Authenticated Routes (Requires Bearer Token) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // User/Profile Routes
    Route::get('/user', [UserController::class, 'current']);
    Route::get('/me', [UserController::class, 'current']);
    
    // 1. ADDED PROFILE UPDATE ROUTE FOR PICTURE UPLOAD
    Route::post('/user/profile', [UserController::class, 'updateProfile']); 
    

    // Student API routes
    Route::get('/students', [StudentController::class, 'index']);
    Route::post('/students', [StudentController::class, 'store']);
    Route::get('/students/{student}', [StudentController::class, 'show']);
    Route::put('/students/{student}', [StudentController::class, 'update']);
    Route::delete('/students/{student}', [StudentController::class, 'destroy']);

    // --- INCIDENT MANAGEMENT ROUTES (FULL CRUD) ---
    Route::get('/incidents/{incident}', [IncidentController::class, 'show']); 
    Route::put('/incidents/{incident}', [IncidentController::class, 'update']); 
    Route::get('/incidents', [IncidentController::class, 'index']); 
    Route::post('/incidents', [IncidentController::class, 'store']); 
    Route::put('/incidents/{incident}/status', [IncidentController::class, 'updateStatus']); 
    Route::delete('/incidents/{incident}', [IncidentController::class, 'destroy']);

    // Dashboard Stats Route
    Route::get('/admin/stats', [DashboardController::class, 'getStats']); 
});