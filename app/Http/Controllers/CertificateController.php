<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftware\QrCode\Facades\QrCode;
use Carbon\Carbon;

class CertificateController extends Controller
{
    public function generateCertificate($resolutionId)
    {
        // 1. Fetch Mock Data (Replace with your actual Eloquent query)
        // In a real application, you would fetch:
        // $resolution = MisconductResolution::with('student')->findOrFail($resolutionId);
        $resolution = (object)[
            'id' => $resolutionId,
            'student_name' => 'Alexander J. Hamilton',
            'status_description' => 'Completion of mandatory counseling and disciplinary action.',
            'verification_code' => "CERT-RES{$resolutionId}-" . rand(100, 999),
        ];

        // 2. Generate QR Code Data URI
        // The QR code links to a verification endpoint (you should implement this)
        $verificationLink = route('certificate.verify', ['code' => $resolution->verification_code]);
        
        $qrCodeDataUri = base64_encode(
            QrCode::format('png')
                ->size(150)
                ->errorCorrection('H')
                ->generate($verificationLink)
        );
        
        // 3. Prepare Data for the Blade View
        $data = [
            'studentName' => $resolution->student_name,
            'violationStatus' => $resolution->status_description,
            'dateIssued' => Carbon::now()->format('F d, Y'),
            'verificationCode' => $resolution->verification_code,
            'qrCodeDataUri' => 'data:image/png;base64,' . $qrCodeDataUri,
        ];

        // 4. Generate and Stream the PDF
        $pdf = Pdf::loadView('certificates.clearance', $data);

        $filename = str_replace(' ', '_', $resolution->student_name) . "_Clearance_{$resolution->id}.pdf";
        
        return $pdf->download($filename);
    }

    // You would implement a simple view here to verify the QR code's link
    public function verifyCertificate($code)
    {
        // $resolution = MisconductResolution::where('verification_code', $code)->first();
        // if ($resolution) { return view('verification.success', compact('resolution')); }
        // return view('verification.failed');
        return view('verification.status', ['code' => $code]);
    }
}