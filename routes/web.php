<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\StudentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/certificates/download/{id}', [CertificateController::class, 'download'])->name('certificates.download');
Route::get('/certificates/verify/{certificate_number}', [CertificateController::class, 'verify'])->name('certificates.verify');

Route::post('/certificates/{id}/restore', [CertificateController::class, 'restore'])->name('certificates.restore');
Route::delete('/certificates/{id}/force-delete', [CertificateController::class, 'forceDelete'])->name('certificates.force-delete');

Route::post('/incidents/{id}/restore', [IncidentController::class, 'restore'])->name('incidents.restore');
Route::delete('/incidents/{id}/force-delete', [IncidentController::class, 'forceDelete'])->name('incidents.force-delete');

Route::post('/students/{id}/restore', [StudentController::class, 'restore'])->name('students.restore');
Route::delete('/students/{id}/force-delete', [StudentController::class, 'forceDelete'])->name('students.force-delete');
