<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// ... (other controllers)
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CertificateController;
// Ensure you have UserProfileController if you use it below
use App\Http\Controllers\UserProfileController; 


// --- Authentication Routes (Public) ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// --- Authenticated Routes (Requires Bearer Token) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // User/Profile Routes
    Route::get('/user', [UserController::class, 'current']);
    Route::get('/me', [UserController::class, 'current']);
    Route::post('/user/profile', [UserController::class, 'updateProfile']); 
    
    // Student API routes
    Route::resource('students', StudentController::class); // Using resource routing for simplicity
    // Route::get('/students', [StudentController::class, 'index']);
    // Route::post('/students', [StudentController::class, 'store']);
    // Route::get('/students/{student}', [StudentController::class, 'show']);
    // Route::put('/students/{student}', [StudentController::class, 'update']);
    // Route::delete('/students/{student}', [StudentController::class, 'destroy']);

    // --- INCIDENT MANAGEMENT ROUTES (FULL CRUD) ---
    
    // 🎯 IMPORTANT: This single route now handles both ADMIN (all) and USER (filed by me) views.
    Route::get('/incidents', [IncidentController::class, 'index']); 
    
    Route::post('/incidents', [IncidentController::class, 'store']); 
    Route::get('/incidents/{incident}', [IncidentController::class, 'show']); 
    Route::put('/incidents/{incident}', [IncidentController::class, 'update']); 
    Route::put('/incidents/{incident}/status', [IncidentController::class, 'updateStatus']); 
    Route::delete('/incidents/{incident}', [IncidentController::class, 'destroy']);

    // 🗑️ REMOVED: This specific route is no longer needed/recommended
    // Route::get('/incidents/user/{filerId}', [IncidentController::class, 'userIndex']); 

    // Dedicated route to update ONLY the final action taken by the administrator
    Route::patch('/incidents/{incident}/action', [IncidentController::class, 'updateActionTaken']);

    // Dashboard Stats Route
    Route::get('/admin/stats', [DashboardController::class, 'getStats']); 

    Route::post('/profile/picture', [UserProfileController::class, 'updateProfilePicture']);
    Route::get('/me', [AuthController::class, 'me']);
});