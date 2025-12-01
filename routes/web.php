<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertificateController; // <-- Ensure this is present

Route::get('/', function () {
    return view('welcome');
});



// ... existing routes ...

// Public routes for download (MUST be in web.php to match non-API URL)
Route::get('/certificates/download/{id}', [CertificateController::class, 'download'])->name('certificate.download');
Route::get('/certificates/verify/{certificate_number}', [CertificateController::class, 'verify'])->name('certificate.verify');