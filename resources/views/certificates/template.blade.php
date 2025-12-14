<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Student Misconduct Record</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        
        html, body {
            width: 297mm;
            height: 210mm;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background-color: #fff;
        }

        .certificate-container {
            width: 277mm;
            height: 190mm;
            padding: 10mm; 
            margin: 0 auto;
            text-align: left;
            box-sizing: border-box;
            border: 2px solid #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .title {
            font-size: 24pt;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 10px;
            margin-bottom: 30px;
            color: #CC0000;
        }

        .content-block {
            font-size: 12pt;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .offense-details {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 20px 0;
        }

        .detail-line {
            margin-bottom: 10px;
            font-size: 11pt;
        }

        .issued-info {
            margin-top: 30px;
            font-size: 11pt;
        }

        .signature-block {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }

        .signature-line {
            text-align: center;
        }

        .official-name {
            font-weight: bold;
            margin-top: 60px;
        }

        .official-details {
            font-size: 11pt;
            margin-top: 5px;
        }

        .qr-code-section {
            text-align: center;
        }

        .qr-code {
            width: 100px;
            height: 100px;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        
        <div class="header">
            @if(isset($school_logo_url))
                <img src="{{ $school_logo_url }}" style="width: 80px;" alt="School Logo">
            @endif
            <div style="font-size: 14pt;">{{ $school_name ?? '[School Name]' }}</div>
            <div class="title">CERTIFICATE OF STUDENT MISCONDUCT RECORD</div>
        </div>
        
        <div class="content-block">
            <p>This certifies that <strong>{{ $recipient_name ?? '[Student Name]' }}</strong>, Student ID <strong>{{ $student_id ?? '[Student ID]' }}</strong>, enrolled in <strong>{{ $program_grade ?? '[Program/Grade Level]' }}</strong>, has the following recorded misconduct in the official Student Misconduct Report Management System of {{ $school_name ?? '[School Name]' }}:</p>
        </div>
        
        <div class="offense-details">
            <div class="detail-line"><strong>Offense Type:</strong> {{ $offense_type ?? '[Offense]' }}</div>
            <div class="detail-line"><strong>Date of Incident:</strong> {{ $date_of_incident ?? '[Date]' }}</div>
            <div class="detail-line"><strong>Disciplinary Action:</strong> {{ $disciplinary_action ?? '[Action]' }}</div>
            <div class="detail-line"><strong>Status:</strong> {{ $status ?? '[Resolved / Pending]' }}</div>
            <div class="detail-line" style="margin-top: 15px;"><strong>Certificate No:</strong> {{ $certificate_number ?? '[Certificate Number]' }}</div>
        </div>

        <div class="content-block" style="font-style: italic;">
            <p>This certificate is issued upon request of the student for personal, academic, or administrative purposes.</p>
        </div>

        <div class="issued-info">
            Issued on <strong>{{ $issued_date ?? '[Date]' }}</strong> at <strong>{{ $school_name ?? '[School Name]' }}</strong>, <strong>{{ $school_location ?? '[Location]' }}</strong>.
        </div>

        <div class="signature-block">
            <div class="signature-line">
                <div style="border-bottom: 1px solid #000; width: 250px; margin-bottom: 5px;"></div>
                <div class="official-name">
                    {{ $official_name ?? '[Name of School Official]' }}
                </div>
                <div class="official-details">
                    {{ $official_position ?? '[Position / Office]' }}
                </div>
            </div>

            <div class="qr-code-section">
                @if(isset($qrBase64))
                    <img src="{{ $qrBase64 }}" class="qr-code" alt="System QR Code">
                    <div class="official-details">[System QR Code]</div>
                @endif
                @if(isset($school_seal_url))
                    <img src="{{ $school_seal_url }}" class="qr-code" alt="School Seal">
                    <div class="official-details">[School Seal]</div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
