<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg: #f5f7fb;
            --text: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --brand: #0f766e;
            --brand-deep: #134e4a;
            --panel: rgba(255, 255, 255, .92);
        }

        body {
            margin: 0;
            font-family: "Instrument Sans", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(16, 185, 129, .15) 0, transparent 28%),
                radial-gradient(circle at bottom right, rgba(59, 130, 246, .10) 0, transparent 24%),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
            min-height: 100vh;
        }

        .shell {
            max-width: 1140px;
            margin: 0 auto;
            padding: 28px 18px 48px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 36px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            letter-spacing: -.02em;
        }

        .brand-mark {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-deep) 100%);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .hero {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            gap: 22px;
            align-items: stretch;
        }

        .panel {
            background: var(--panel);
            border: 1px solid rgba(255,255,255,.82);
            border-radius: 30px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .08);
            backdrop-filter: blur(10px);
        }

        .hero-copy {
            padding: 42px 36px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(15, 118, 110, .08);
            color: var(--brand);
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        h1 {
            margin: 0 0 16px;
            font-size: clamp(2.4rem, 4vw, 4.2rem);
            line-height: 1.02;
            letter-spacing: -.05em;
            max-width: 9ch;
        }

        .lead {
            margin: 0;
            color: var(--muted);
            font-size: 1.02rem;
            line-height: 1.8;
            max-width: 54ch;
        }

        .feature-grid {
            margin-top: 28px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .feature-card {
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 18px;
            background: linear-gradient(180deg, rgba(255,255,255,.96) 0%, rgba(248,250,252,.92) 100%);
        }

        .feature-card strong {
            display: block;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .feature-card ul {
            margin: 0;
            padding-left: 18px;
            color: var(--muted);
            line-height: 1.75;
            font-size: .94rem;
        }

        .login-panel {
            padding: 34px 28px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-panel h2 {
            margin: 0 0 8px;
            font-size: 1.5rem;
        }

        .login-panel p {
            margin: 0 0 22px;
            color: var(--muted);
            line-height: 1.7;
        }

        .form-control {
            border-radius: 14px;
            padding: .86rem .95rem;
            border-color: var(--line);
            box-shadow: none;
        }

        .form-control:focus {
            border-color: #99f6e4;
            box-shadow: 0 0 0 .2rem rgba(20, 184, 166, .12);
        }

        .form-check-label,
        .form-label,
        .forgot-link {
            font-size: .95rem;
        }

        .forgot-link {
            text-decoration: none;
        }

        .login-btn {
            border-radius: 14px;
            padding: .9rem 1rem;
            font-weight: 600;
            background: var(--brand);
            border-color: var(--brand);
        }

        .login-btn:hover,
        .login-btn:focus {
            background: var(--brand-deep);
            border-color: var(--brand-deep);
        }

        @media (max-width: 980px) {
            .hero,
            .feature-grid {
                grid-template-columns: 1fr;
            }

            .hero-copy {
                padding: 34px 24px;
            }

            .login-panel {
                padding: 28px 20px;
            }

            h1 {
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="topbar">
            <div class="brand">
                <span class="brand-mark">C</span>
                <span>{{ config('app.name', 'Coaching CMS') }}</span>
            </div>
        </div>

        <section class="hero">
            <div class="panel hero-copy">
                <div class="eyebrow">Coaching Management System</div>
                <h1>Simple, organized coaching operations.</h1>
                <p class="lead">Manage batches, admissions, students, attendance, fees, and reports from one clean system.</p>

                <div class="feature-grid">
                    <div class="feature-card">
                        <strong>Admissions</strong>
                        <ul>
                            <li>Online admission links</li>
                            <li>Review and approval flow</li>
                            <li>Student profile tracking</li>
                        </ul>
                    </div>
                    <div class="feature-card">
                        <strong>Academic Flow</strong>
                        <ul>
                            <li>Classes and subjects</li>
                            <li>Batch schedules</li>
                            <li>Teacher assignment</li>
                        </ul>
                    </div>
                    <div class="feature-card">
                        <strong>Attendance</strong>
                        <ul>
                            <li>Face-ready attendance</li>
                            <li>QR and manual fallback</li>
                            <li>Mobile-friendly use</li>
                        </ul>
                    </div>
                    <div class="feature-card">
                        <strong>Daily Management</strong>
                        <ul>
                            <li>Fee collection</li>
                            <li>Due tracking</li>
                            <li>Reports and follow-up</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="panel login-panel">
                <h2>Login</h2>
                <p>Use your email or username to continue.</p>

                @if (session('status'))
                    <div class="alert alert-success rounded-4">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="login" class="form-label">Email or Username</label>
                        <input id="login" type="text" name="login" value="{{ old('login') }}" class="form-control @error('login') is-invalid @enderror" required autofocus autocomplete="username">
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary w-100 login-btn">Login</button>
                </form>
            </div>
        </section>
    </div>
</body>
</html>
