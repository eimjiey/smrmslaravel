<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException; 

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = Certificate::query();
        
        if ($request->has('trashed')) {
            $query = Certificate::withTrashed();
        }
        
        $certificates = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return response()->json($certificates);
    }

    public function store(Request $request)
    {
        $logoPath = public_path('images/ISULOGO.png');

        $sealPath = public_path('images/SMRMSgreen.png'); 
        $schoolLogoBase64 = '';
        $schoolSealBase64 = '';
        $qrBase64 = '';
        $assetLoadFailed = false;

        try {
            if (file_exists($logoPath) && is_readable($logoPath)) {
                $schoolLogoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            } else {
                Log::error("PDF Asset Error: ISULOGO.png not found or unreadable at $logoPath");
                $assetLoadFailed = true;
            }

            if (file_exists($sealPath) && is_readable($sealPath)) {
                $schoolSealBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($sealPath));
            } else {
                Log::error("PDF Asset Error: SMRMSgreen.png not found or unreadable at $sealPath"); 
                $assetLoadFailed = true;
            }

            $data = $request->validate([
                'student_name' => 'required|string|max:255',
                'student_id' => 'required|string|max:50', 
                'program_grade' => 'nullable|string|max:255', 
                'offense_type' => 'required|string',
                'date_of_incident' => 'required|date',
                'disciplinary_action' => 'required|string',
                'status' => 'required|in:Resolved,Pending',
                'issued_date' => 'nullable|date',
                'school_name' => 'nullable|string|max:255',
                'school_location' => 'nullable|string|max:255',
                'official_name' => 'nullable|string|max:255',
                'official_position' => 'nullable|string|max:255',
            ]);

            $data['certificate_number'] = strtoupper('CERT-' . Str::random(8));
            $data['issued_date'] = $data['issued_date'] ?? now()->toDateString();
            $data['recipient_name'] = $data['student_name']; 
            
            unset($data['student_name']);

            $cert = Certificate::create($data); 

            Log::warning("Skipping QR code generation due to missing PHP extensions (ImageMagick/GD)");
            $qrBase64 = '';
            $svgContent = $this->generateCertificateSVG($cert, $data);
            $fileName = "certificate_{$cert->id}.svg";
            $filePath = public_path("certificates/{$fileName}");
            
            file_put_contents($filePath, $svgContent);
            Log::info("Certificate SVG saved to public directory: " . $filePath);

            Log::info('Certificate process completed');

            $messageText = $assetLoadFailed ? 
                            "Certificate generated but images were missing. Check logs for Asset Error." : 
                            "Certificate generated successfully!";

            return response()->json([
                'success' => true,
                'id' => $cert->id,
                'certificate_number' => $cert->certificate_number,
                'message' => $messageText,
                'file_path' => url("certificates/{$fileName}"),
            ]);

        } catch (ValidationException $e) { 
            Log::error("Validation failed for certificate store: " . json_encode($e->errors()));

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(), 
            ], 422);

        } catch (\Exception $e) {
            Log::error("Certificate store failed (Final Catch): " . $e->getMessage());

            return response()->json([
                'error' => "Server Error: Final attempt to generate failed. Check 'laravel.log' for file access errors.",
                'detail' => $e->getMessage(),
            ], 500);
        }
    }
    
    private function generateCertificateSVG($cert, $data)
    {
        $svg = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';
        $svg .= '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';
        $svg .= '<style>';
        $svg .= 'text { font-family: Arial, sans-serif; }';
        $svg .= '.title { font-size: 24px; font-weight: bold; text-anchor: middle; }';
        $svg .= '.heading { font-size: 18px; font-weight: bold; }';
        $svg .= '.label { font-size: 14px; font-weight: bold; }';
        $svg .= '.value { font-size: 14px; }';
        $svg .= '</style>';
        $svg .= '<text x="400" y="50" class="title">CERTIFICATE OF STUDENT MISCONDUCT RECORD</text>';
        $svg .= '<line x1="100" y1="60" x2="700" y2="60" stroke="black" stroke-width="2"/>';
        $svg .= '<text x="100" y="100" class="label">Certificate Number:</text>';
        $svg .= '<text x="250" y="100" class="value">' . htmlspecialchars($cert->certificate_number) . '</text>';
        $svg .= '<text x="100" y="130" class="label">Issued Date:</text>';
        $svg .= '<text x="250" y="130" class="value">' . htmlspecialchars($cert->issued_date) . '</text>';
        $svg .= '<text x="100" y="160" class="label">School:</text>';
        $svg .= '<text x="250" y="160" class="value">' . htmlspecialchars($data['school_name'] ?? 'N/A') . '</text>';
        $svg .= '<text x="100" y="190" class="label">Location:</text>';
        $svg .= '<text x="250" y="190" class="value">' . htmlspecialchars($data['school_location'] ?? 'N/A') . '</text>';
        $svg .= '<text x="100" y="240" class="heading">STUDENT INFORMATION</text>';
        $svg .= '<line x1="100" y1="250" x2="700" y2="250" stroke="black" stroke-width="1"/>';
        $svg .= '<text x="100" y="280" class="label">Name:</text>';
        $svg .= '<text x="250" y="280" class="value">' . htmlspecialchars($cert->recipient_name) . '</text>';
        $svg .= '<text x="100" y="310" class="label">Student ID:</text>';
        $svg .= '<text x="250" y="310" class="value">' . htmlspecialchars($cert->student_id) . '</text>';
        $svg .= '<text x="100" y="340" class="label">Program/Grade:</text>';
        $svg .= '<text x="250" y="340" class="value">' . htmlspecialchars($cert->program_grade ?? 'N/A') . '</text>';
        $svg .= '<text x="100" y="390" class="heading">MISCONDUCT DETAILS</text>';
        $svg .= '<line x1="100" y1="400" x2="700" y2="400" stroke="black" stroke-width="1"/>';
        $svg .= '<text x="100" y="430" class="label">Offense Type:</text>';
        $svg .= '<text x="250" y="430" class="value">' . htmlspecialchars($cert->offense_type) . '</text>';
        $svg .= '<text x="100" y="460" class="label">Date of Incident:</text>';
        $svg .= '<text x="250" y="460" class="value">' . htmlspecialchars($cert->date_of_incident) . '</text>';
        $svg .= '<text x="100" y="490" class="label">Disciplinary Action:</text>';
        $svg .= '<text x="250" y="490" class="value">' . htmlspecialchars($cert->disciplinary_action) . '</text>';
        $svg .= '<text x="100" y="520" class="label">Status:</text>';
        $svg .= '<text x="250" y="520" class="value">' . htmlspecialchars($cert->status) . '</text>';
        $svg .= '<text x="100" y="570" class="label">Issued By: ' . htmlspecialchars($data['official_name'] ?? 'N/A') . ' (' . htmlspecialchars($data['official_position'] ?? 'N/A') . ')</text>';
        $svg .= '</svg>';
        
        return $svg;
    }

    public function show($id)
    {
        $cert = Certificate::withTrashed()->findOrFail($id);
        return response()->json($cert);
    }

    public function destroy($id)
    {
        $cert = Certificate::findOrFail($id);
        $cert->delete(); 
        
        return response()->json([
            'message' => 'Certificate moved to trash successfully.'
        ]);
    }

    public function restore($id)
    {
        $cert = Certificate::withTrashed()->findOrFail($id);
        
        if ($cert->trashed()) {
            $cert->restore();
            return response()->json([
                'message' => 'Certificate restored successfully.'
            ]);
        }
        
        return response()->json([
            'message' => 'Certificate is not in trash.'
        ]);
    }

    public function forceDelete($id)
    {
        $cert = Certificate::withTrashed()->findOrFail($id);
        
        if ($cert->trashed()) {
            $fileName = "certificate_{$cert->id}.svg";
            $filePath = public_path("certificates/{$fileName}");
            
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            $cert->forceDelete();
            
            return response()->json([
                'message' => 'Certificate permanently deleted.'
            ]);
        }
        
        return response()->json([
            'message' => 'Certificate must be soft deleted before permanent deletion.'
        ], 400);
    }

    public function download($id)
    {
        $cert = Certificate::withTrashed()->findOrFail($id);
        
        if ($cert->trashed()) {
            abort(404, 'Certificate not found.');
        }
        
        $fileName = "certificate_{$cert->id}.svg";
        $filePath = public_path("certificates/{$fileName}");

        if (!file_exists($filePath)) {
            Log::warning("Download FAILED: File '{$fileName}' not found in public/certificates for ID {$id}.");
            abort(404, 'Certificate file not found.'); 
        }

        return response()->file($filePath, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="certificate_' . $cert->certificate_number . '.svg"',
        ]);
    }

    public function verify($certificate_number)
    {
        $cert = Certificate::withTrashed()->where('certificate_number', $certificate_number)->first();

        if (!$cert) {
            return response()->json(['valid' => false, 'message' => 'Certificate not found.'], 404);
        }

        if ($cert->trashed()) {
            return response()->json([
                'valid' => true,
                'certificate' => $cert->toArray(),
                'message' => 'Certificate is valid but has been deleted.'
            ]);
        }

        return response()->json([
            'valid' => true,
            'certificate' => $cert->toArray()
        ]);
    }
}