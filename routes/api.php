<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CertificateController;
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
    Route::resource('students', StudentController::class);

    // INCIDENT MANAGEMENT (FULL CRUD)
    Route::get('/incidents', [IncidentController::class, 'index']);
    Route::post('/incidents', [IncidentController::class, 'store']);
    Route::get('/incidents/{incident}', [IncidentController::class, 'show']);
    Route::put('/incidents/{incident}', [IncidentController::class, 'update']);
    Route::put('/incidents/{incident}/status', [IncidentController::class, 'updateStatus']);
    Route::delete('/incidents/{incident}', [IncidentController::class, 'destroy']);

    // Update final action taken by admin
    Route::patch('/incidents/{incident}/action', [IncidentController::class, 'updateActionTaken']);

    // Dashboard Stats Route
    Route::get('/admin/stats', [DashboardController::class, 'getStats']);

    Route::post('/profile/picture', [UserProfileController::class, 'updateProfilePicture']);
    Route::get('/me', [AuthController::class, 'me']);

    // Certificate create route
    Route::post('/certificates', [CertificateController::class, 'store']);
});

// NOTE: Download + Verify routes belong in routes/web.php, not here.
// (You already removed them)
