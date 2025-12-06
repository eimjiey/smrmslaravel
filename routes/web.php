<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificateController;

Route::get('/', function () {
    return view('welcome');
});

// Public routes for download (MUST be in web.php to match non-API URL)
Route::get('/certificates/download/{id}', [CertificateController::class, 'download'])->name('certificates.download');
Route::get('/certificates/verify/{certificate_number}', [CertificateController::class, 'verify'])->name('certificates.verify');