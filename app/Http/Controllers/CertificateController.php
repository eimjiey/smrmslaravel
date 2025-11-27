<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CertificateController extends Controller
{
    /**
     * Receives HTML content from Vue and generates a PDF certificate.
     */
    public function generateCertificate(Request $request)
    {
        // 1. Validation
        $validator = Validator::make($request->all(), [
            'studentId' => 'required|integer',
            'htmlContent' => 'required|string',
            'fileName' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        $htmlContent = $request->input('htmlContent');
        $fileName = $request->input('fileName');

        // 2. Generate the PDF from the raw HTML string
        $pdf = Pdf::loadHtml($htmlContent);

        // 3. Stream the PDF back to the browser/frontend as a blob
        return $pdf->stream($fileName . '.pdf');
    }
}