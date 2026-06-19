<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Explicitly tell email clients to ignore dark mode -->
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Verify Your Email</title>
    <style>
        /* FORCED LIGHT MODE */
        body {
            background-color: #f9fafb !important;
            color: #111827 !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .card {
            background-color: #ffffff !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 24px;
        }
        h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 16px;
            color: #4f46e5 !important; /* Reso Indigo */
        }
        p {
            color: #4b5563 !important;
            font-size: 16px;
            margin-bottom: 32px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
        }
        .footer {
            margin-top: 32px;
            font-size: 12px;
            color: #9ca3af !important;
        }

        /* REMOVED DARK MODE MEDIA QUERY TO PREVENT SYSTEM OVERRIDE */
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <img src="{{ asset('icons/reso.png') }}" alt="Reso" class="logo">
            <h1>Welcome to Reso</h1>
            <p>Ready to vibe? Please verify your email address to unlock the full Reso experience—curate playlists, share tracks, and connect with your friends.</p>
            
            <a href="{{ $url }}" class="button">Verify Email Address</a>
            
            <div class="footer">
                If you did not create an account, no further action is required.
            </div>
        </div>
    </div>
</body>
</html>
