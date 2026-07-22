<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('QR Code') }} - {{ $channel->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #f9fafb;
        }

        .sheet {
            width: 100%;
            max-width: 420px;
            text-align: center;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 2.5rem 2rem;
        }

        .channel-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 1.5rem;
        }

        .qr-image {
            width: 260px;
            height: 260px;
            margin: 0 auto 1.5rem;
        }

        .qr-link {
            font-size: 0.75rem;
            color: #9ca3af;
            word-break: break-all;
            margin-bottom: 1rem;
        }

        .welcome-message {
            font-size: 1.05rem;
            color: #374151;
            line-height: 1.5;
            white-space: pre-line;
        }

        .print-btn {
            margin-top: 2rem;
            padding: 0.75rem 2rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
        }

        @media print {
            body { background: white; padding: 0; }
            .sheet { border: none; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="channel-name">{{ $channel->name }}</div>
        <img src="data:image/svg+xml;base64,{{ $qrImage }}" alt="QR code" class="qr-image">
        <p class="qr-link">{{ $qrCode->url }}</p>
        @if(!empty($qrCode->metadata['welcome_message'] ?? null))
            <p class="welcome-message">{{ $qrCode->metadata['welcome_message'] }}</p>
        @endif
        <button type="button" class="print-btn" onclick="window.print()">{{ __('Print') }}</button>
    </div>
</body>
</html>
