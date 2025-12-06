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

// --- Public Routes (No Authentication Required) ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// --- Public Access Routes for Certificates ---
// NOTE: These routes are moved to web.php to avoid conflicts and ensure proper handling
// Route::get('/certificates/download/{id}', [CertificateController::class, 'download'])
//     ->name('certificates.download'); 

// Route::get('/certificates/verify/{certificate_number}', [CertificateController::class, 'verify'])
//     ->name('certificates.verify');

// --- Authenticated Routes (Requires Bearer Token) ---
Route::middleware('auth:sanctum')->group(function () {

    // User/Profile Routes
    Route::get('/user', [UserController::class, 'current']);
    Route::get('/me', [UserController::class, 'current']);
    Route::post('/user/profile', [UserController::class, 'updateProfile']);
    Route::post('/profile/picture', [UserProfileController::class, 'updateProfilePicture']);

    // Student API routes
    Route::resource('students', StudentController::class);

    // INCIDENT MANAGEMENT (FULL CRUD)
    Route::get('/incidents', [IncidentController::class, 'index']);
    Route::post('/incidents', [IncidentController::class, 'store']);
    Route::get('/incidents/{incident}', [IncidentController::class, 'show']);
    Route::put('/incidents/{incident}', [IncidentController::class, 'update']);
    Route::put('/incidents/{incident}/status', [IncidentController::class, 'updateStatus']);
    Route::delete('/incidents/{incident}', [IncidentController::class, 'destroy']);
    Route::patch('/incidents/{incident}/action', [IncidentController::class, 'updateActionTaken']);

    // Dashboard Stats Route
    Route::get('/admin/stats', [DashboardController::class, 'getStats']);

    // Certificate create route (This API endpoint MUST be protected)
    Route::post('/certificates', [CertificateController::class, 'store']);
});