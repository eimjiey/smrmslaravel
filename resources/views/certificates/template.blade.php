<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            text-align: center;
            padding: 50px;
        }
        .certificate-container {
            border: 10px solid #4CAF50;
            padding: 50px;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
        }
        .logo {
            width: 150px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 36px;
            font-weight: bold;
            margin-top: 20px;
        }
        .recipient {
            font-size: 28px;
            font-weight: bold;
            margin: 40px 0;
        }
        .certificate-number {
            margin-top: 20px;
            font-size: 16px;
        }
        .issued-at {
            margin-top: 10px;
            font-size: 16px;
        }
        .notes {
            margin-top: 20px;
            font-size: 16px;
        }
        .signature {
            margin-top: 50px;
            width: 200px;
        }
        .qr-code {
            margin-top: 20px;
            width: 150px;
            height: 150px;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        @if(isset($logo_url))
            <img src="{{ $logo_url }}" class="logo" alt="Logo">
        @endif

        <div class="title">{{ $title ?? 'Certificate of Completion' }}</div>

        <div class="recipient">{{ $recipient_name }}</div>

        @if(isset($notes))
            <div class="notes">{{ $notes }}</div>
        @endif

        <div class="certificate-number">Certificate No: {{ $certificate_number }}</div>
        <div class="issued-at">Issued on: {{ $issued_at }}</div>

        @if(isset($signature_url))
            <img src="{{ $signature_url }}" class="signature" alt="Signature">
        @endif

        @if(isset($qrBase64))
            <img src="{{ $qrBase64 }}" class="qr-code" alt="QR Code">
        @endif
    </div>
</body>
</html>
