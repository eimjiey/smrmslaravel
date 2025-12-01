<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CertificateController extends Controller
{
    /**
     * Store certificate data and generate PDF.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'recipient_name' => 'required|string|max:255',
                'title' => 'nullable|string|max:255',
                'issued_at' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            // Generate certificate number
            $data['certificate_number'] = strtoupper('CERT-' . Str::random(8));
            $data['issued_at'] = $data['issued_at'] ?? now()->toDateString();

            $cert = Certificate::create($data);

            // Test write inside storage/app/public/certificates/
            $test = Storage::disk('public')->put('certificates/test.txt', 'Hello world');
            Log::info('Storage test result: ' . ($test ? 'success' : 'failed'));

            // Generate HTML for PDF
            $html = view('certificates.template', [
                'recipient_name' => $cert->recipient_name,
                'title' => $cert->title,
                'certificate_number' => $cert->certificate_number,
                'issued_at' => $cert->issued_at,
                'notes' => $cert->notes,
                'qrBase64' => 'test',
                'logo_url' => asset('images/cert-logo.png'),
                'signature_url' => asset('images/signature.png'),
            ])->render();

            $pdf = PDF::loadHTML($html)->setPaper('a4', 'landscape');

            // Save generated PDF
            $filePath = "certificates/certificate_{$cert->id}.pdf";
            $save = Storage::disk('public')->put($filePath, $pdf->output());

            Log::info('PDF save result: ' . ($save ? 'success' : 'failed'));

            return response()->json([
                'success' => true,
                'id' => $cert->id,
                'certificate_number' => $cert->certificate_number,
            ]);

        } catch (\Exception $e) {
            Log::error("Certificate store failed: " . $e->getMessage());

            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Download certificate file.
     */
    public function download($id)
    {
        $cert = Certificate::findOrFail($id);
        $fileName = "certificates/certificate_{$cert->id}.pdf";

        if (!Storage::disk('public')->exists($fileName)) {
            Log::warning("Download FAILED: File '{$fileName}' not found on public disk for ID {$id}.");
            abort(404, 'Certificate file not found.');
        }

        return Storage::disk('public')->download(
            $fileName,
            "certificate_{$cert->certificate_number}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Verify certificate using certificate number.
     */
    public function verify($certificate_number)
    {
        $cert = Certificate::where('certificate_number', $certificate_number)->first();

        if (!$cert) {
            return response()->json(['valid' => false], 404);
        }

        return response()->json([
            'valid' => true,
            'certificate' => [
                'recipient_name' => $cert->recipient_name,
                'title' => $cert->title,
                'issued_at' => $cert->issued_at ? $cert->issued_at->toDateString() : null,
                'certificate_number' => $cert->certificate_number,
            ],
        ]);
    }
}
