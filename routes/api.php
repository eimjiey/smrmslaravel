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

// --- Authenticated Routes (Requires Bearer Token) ---
Route::middleware('auth:sanctum')->group(function () {

    // ==========================================================
    // USER AND STUDENT MANAGEMENT
    // ==========================================================
    Route::get('/user', [UserController::class, 'current']);
    Route::get('/me', [UserController::class, 'current']);
    Route::post('/user/profile', [UserController::class, 'updateProfile']);
    Route::post('/profile/picture', [UserProfileController::class, 'updateProfilePicture']);

    Route::get('/students/dropdown', [StudentController::class, 'getAllForDropdown']);
    Route::resource('students', StudentController::class)->except(['destroy']);
    Route::delete('/students/{id}', [StudentController::class, 'destroy']);
    Route::post('/students/{id}/restore', [StudentController::class, 'restore']);
    Route::delete('/students/{id}/force-delete', [StudentController::class, 'forceDelete']);

    // ==========================================================
    // INCIDENT MANAGEMENT
    // ==========================================================

    // Main listing for Vue – returns only the logged‑in user's incidents
    Route::get('/incidents', [IncidentController::class, 'index']);

    // Resource routes for incidents (exclude index so it doesn't override above)
    Route::resource('incidents', IncidentController::class)->except(['index']);

    // Extra update routes if needed (status / action taken)
    Route::put('/incidents/{id}', [IncidentController::class, 'update']);
    Route::patch('/incidents/{id}', [IncidentController::class, 'update']);
    Route::put('/incidents/{id}/status', [IncidentController::class, 'updateStatus']);
    Route::patch('/incidents/{id}/action', [IncidentController::class, 'updateActionTaken']);

    // Deletion and restoration routes
    // DELETE /incidents/{id} -> IncidentController@destroy (soft delete)
    Route::delete('/incidents/{id}', [IncidentController::class, 'destroy']);
    Route::post('/incidents/{id}/restore', [IncidentController::class, 'restore']);
    Route::delete('/incidents/{id}/force-delete', [IncidentController::class, 'forceDelete']);

    // General Stats Routes (used by Vue)
    Route::get('/stats', [DashboardController::class, 'getStats']);
    Route::get('/stats/monthly-misconduct', [DashboardController::class, 'getMonthlyMisconduct']);

    // Alias for the route requested by Vue frontend (misconduct-distribution)
    Route::get('/stats/misconduct-distribution', [DashboardController::class, 'getOffenseTypeDistribution']);
    Route::get('/stats/offense-type-distribution', [DashboardController::class, 'getOffenseTypeDistribution']);

    // Endpoint for specific misconduct distribution
    Route::get('/stats/specific-misconduct', [DashboardController::class, 'getSpecificMisconductDistribution']);

    Route::get('/stats/misconduct-per-program', [DashboardController::class, 'getMisconductPerProgram']);
    Route::get('/stats/predictive', [DashboardController::class, 'getPredictiveMisconduct']);

    // Admin Stats Routes (access via /api/admin/stats/...)
    Route::get('/admin/stats', [DashboardController::class, 'getStats']);
    Route::get('/admin/stats/monthly-misconduct', [DashboardController::class, 'getMonthlyMisconduct']);
    Route::get('/admin/stats/specific-misconduct-distribution', [DashboardController::class, 'getSpecificDistribution']);
    Route::get('/admin/stats/misconduct-per-program', [DashboardController::class, 'getMisconductPerProgram']);
    Route::get('/admin/stats/predictive', [DashboardController::class, 'getPredictiveMisconduct']);

    // ==========================================================
    // CERTIFICATE ROUTES
    // ==========================================================
    Route::resource('certificates', CertificateController::class)->except(['destroy']);
    Route::delete('/certificates/{id}', [CertificateController::class, 'destroy']);
    Route::post('/certificates/{id}/restore', [CertificateController::class, 'restore']);
    Route::delete('/certificates/{id}/force-delete', [CertificateController::class, 'forceDelete']);
});
