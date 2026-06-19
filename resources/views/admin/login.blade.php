<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reso Admin — Restricted Access</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f1f5f9;
        }

        /* Left brand panel */
        .brand-panel {
            width: 45%;
            background: linear-gradient(145deg, #0f172a 0%, #1e3a5f 60%, #1d4ed8 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 4rem;
            position: relative;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            top: -100px; right: -100px;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(29, 78, 216, 0.15);
        }

        .brand-panel::after {
            content: '';
            position: absolute;
            bottom: -80px; left: -80px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255, 0.04);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 3rem;
            z-index: 1;
        }

        .brand-logo img {
            width: 42px;
            height: 42px;
            border-radius: 10px;
        }

        .brand-logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.3px;
        }

        .brand-logo-sub {
            font-size: 0.65rem;
            font-weight: 600;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 1px;
        }

        .brand-headline {
            font-size: 2.4rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
            margin-bottom: 1.25rem;
            z-index: 1;
        }

        .brand-headline span {
            color: #60a5fa;
        }

        .brand-desc {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.55);
            line-height: 1.7;
            max-width: 340px;
            z-index: 1;
        }

        .brand-features {
            margin-top: 2.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            z-index: 1;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            color: rgba(255,255,255,0.7);
            font-size: 0.82rem;
            font-weight: 500;
        }

        .brand-feature-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #60a5fa;
            flex-shrink: 0;
        }

        /* Right form panel */
        .form-panel {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 3rem 4rem;
            background: #ffffff;
        }

        .form-card {
            width: 100%;
            max-width: 400px;
        }

        .form-header {
            margin-bottom: 2.25rem;
        }

        .form-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.3px;
        }

        .form-subtitle {
            margin-top: 0.4rem;
            font-size: 0.85rem;
            color: #64748b;
        }

        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            color: #dc2626;
            font-size: 0.82rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.45rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 0.875rem 0.75rem 2.6rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            color: #0f172a;
            background: #f8fafc;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            outline: none;
        }

        .form-input:focus {
            border-color: #1d4ed8;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.08);
        }

        .form-input::placeholder { color: #cbd5e1; }

        .submit-btn {
            width: 100%;
            padding: 0.85rem;
            background: #1d4ed8;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
            letter-spacing: 0.2px;
        }

        .submit-btn:hover {
            background: #1e40af;
            box-shadow: 0 4px 20px rgba(29, 78, 216, 0.25);
            transform: translateY(-1px);
        }

        .submit-btn:active { transform: translateY(0); }

        .form-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #64748b;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 2rem;
            transition: color 0.15s;
        }

        .back-link:hover { color: #1d4ed8; }

        @media (max-width: 768px) {
            .brand-panel { display: none; }
            .form-panel { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

    <!-- Left Brand Panel -->
    <div class="brand-panel">
        <div class="brand-logo">
            <img src="/icons/reso.png" alt="Reso" onerror="this.style.display='none'">
            <div>
                <div class="brand-logo-text">Reso</div>
                <div class="brand-logo-sub">Admin Console</div>
            </div>
        </div>

        <h1 class="brand-headline">
            Platform<br><span>Control Centre</span>
        </h1>

        <p class="brand-desc">
            Manage users, moderate content, monitor the AI recommendation engine, and keep MusicSocial running at its best.
        </p>

        <div class="brand-features">
            <div class="brand-feature">
                <div class="brand-feature-dot"></div>
                User management &amp; moderation tools
            </div>
            <div class="brand-feature">
                <div class="brand-feature-dot"></div>
                Live AI recommendation engine preview
            </div>
            <div class="brand-feature">
                <div class="brand-feature-dot"></div>
                Platform analytics &amp; activity monitoring
            </div>
        </div>
    </div>

    <!-- Right Form Panel -->
    <div class="form-panel">
        <div class="form-card">

            <a href="/" class="back-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Back to site
            </a>

            <div class="form-header">
                <h2 class="form-title">Admin Sign In</h2>
                <p class="form-subtitle">Restricted area — authorised personnel only</p>
            </div>

            @if($errors->any())
                <div class="error-box">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </span>
                        <input id="email" class="form-input" type="email" name="email"
                               value="{{ old('email') }}" required autofocus placeholder="admin@musicsocial.com">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input id="password" class="form-input" type="password" name="password"
                               required placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    Access Dashboard →
                </button>
            </form>

            <div class="form-footer">
                Reso Admin Panel &nbsp;·&nbsp; MusicSocial v3.8.2
            </div>

        </div>
    </div>

</body>
</html>
