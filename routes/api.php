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

    // User/Profile Routes
    Route::get('/user', [UserController::class, 'current']);
    Route::get('/me', [UserController::class, 'current']);
    Route::post('/user/profile', [UserController::class, 'updateProfile']);
    Route::post('/profile/picture', [UserProfileController::class, 'updateProfilePicture']);

    // STUDENT API ROUTES
    Route::get('/students/dropdown', [StudentController::class, 'getAllForDropdown']);
    Route::resource('students', StudentController::class)->except(['destroy']);
    Route::delete('/students/{id}', [StudentController::class, 'destroy']);
    Route::post('/students/{id}/restore', [StudentController::class, 'restore']);
    Route::delete('/students/{id}/force-delete', [StudentController::class, 'forceDelete']);

    // INCIDENT MANAGEMENT (FULL CRUD)
    Route::resource('incidents', IncidentController::class)->except(['destroy']);
    Route::put('/incidents/{id}/status', [IncidentController::class, 'updateStatus']);
    Route::delete('/incidents/{id}', [IncidentController::class, 'destroy']);
    Route::patch('/incidents/{id}/action', [IncidentController::class, 'updateActionTaken']);
    Route::post('/incidents/{id}/restore', [IncidentController::class, 'restore']);
    Route::delete('/incidents/{id}/force-delete', [IncidentController::class, 'forceDelete']);

    // 📊 DASHBOARD / STATS ROUTES
    // Admin Dashboard Routes (for detailed admin view)
    Route::get('/admin/stats', [DashboardController::class, 'getStats']);
    Route::get('/admin/stats/monthly-misconduct', [DashboardController::class, 'getMonthlyMisconduct']);
    Route::get('/admin/stats/misconduct-distribution', [DashboardController::class, 'getMisconductDistribution']);
    Route::get('/admin/stats/specific-misconduct-distribution', [DashboardController::class, 'getSpecificMisconductDistribution']);
    Route::get('/admin/stats/misconduct-per-program', [DashboardController::class, 'getMisconductPerProgram']);


    // NEW: General User Statistics Routes (accessible by any authenticated user)
    Route::get('/stats', [DashboardController::class, 'getStats']);
    Route::get('/stats/monthly-misconduct', [DashboardController::class, 'getMonthlyMisconduct']);
    Route::get('/stats/misconduct-distribution', [DashboardController::class, 'getMisconductDistribution']);
    Route::get('/stats/specific-misconduct', [DashboardController::class, 'getSpecificMisconductDistribution']);
    Route::get('/stats/misconduct-per-program', [DashboardController::class, 'getMisconductPerProgram']);

    // Certificate routes
    Route::resource('certificates', CertificateController::class)->except(['destroy']);
    Route::delete('/certificates/{id}', [CertificateController::class, 'destroy']);
    Route::post('/certificates/{id}/restore', [CertificateController::class, 'restore']);
    Route::delete('/certificates/{id}/force-delete', [CertificateController::class, 'forceDelete']);
});