<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Coaching CMS') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f6f8fb;
            --text: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --brand: #0f766e;
            --brand-deep: #134e4a;
            --card: rgba(255, 255, 255, .92);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Instrument Sans", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(16, 185, 129, .15) 0, transparent 28%),
                radial-gradient(circle at bottom right, rgba(59, 130, 246, .10) 0, transparent 24%),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        }

        .shell {
            max-width: 1120px;
            margin: 0 auto;
            padding: 28px 18px 52px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 42px;
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

        .login-link {
            text-decoration: none;
            color: var(--text);
            background: rgba(255, 255, 255, .88);
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 12px 18px;
            font-weight: 600;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .05);
        }

        .hero {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 22px;
            align-items: stretch;
            margin-bottom: 22px;
        }

        .panel {
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, .8);
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

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 26px;
        }

        .btn {
            text-decoration: none;
            border-radius: 999px;
            padding: 13px 20px;
            font-weight: 600;
            transition: .2s ease;
        }

        .btn-primary {
            background: var(--brand);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--brand-deep);
        }

        .btn-light {
            background: #fff;
            color: var(--text);
            border: 1px solid var(--line);
        }

        .hero-side {
            padding: 28px;
            display: grid;
            gap: 14px;
        }

        .mini-card {
            border-radius: 22px;
            padding: 20px;
            background: linear-gradient(180deg, rgba(255,255,255,.96) 0%, rgba(248,250,252,.92) 100%);
            border: 1px solid var(--line);
        }

        .mini-card strong {
            display: block;
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .mini-card span {
            color: var(--muted);
            line-height: 1.7;
            font-size: .95rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }

        .feature-card {
            padding: 22px 20px;
            border-radius: 24px;
            background: rgba(255, 255, 255, .9);
            border: 1px solid rgba(255, 255, 255, .82);
            box-shadow: 0 18px 44px rgba(15, 23, 42, .05);
        }

        .feature-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            font-weight: 700;
            color: var(--brand-deep);
            background: rgba(15, 118, 110, .1);
        }

        .feature-card h2 {
            margin: 0 0 8px;
            font-size: 1.02rem;
        }

        .feature-card ul {
            margin: 0;
            padding-left: 18px;
            color: var(--muted);
            line-height: 1.75;
            font-size: .94rem;
        }

        .feature-card li + li {
            margin-top: 4px;
        }

        @media (max-width: 980px) {
            .hero,
            .feature-grid {
                grid-template-columns: 1fr;
            }

            .hero-copy {
                padding: 34px 24px;
            }

            .hero-side,
            .feature-card {
                padding: 22px 18px;
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

            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="login-link">Login</a>
            @endif
        </div>

        <section class="hero">
            <div class="panel hero-copy">
                <div class="eyebrow">Smart Coaching Operations</div>
                <h1>Simple, modern support for daily coaching work.</h1>
                <p class="lead">A clean system for admissions, attendance, student records, batch management, and communication.</p>

                <div class="hero-actions">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                    @endif
                    <a href="#features" class="btn btn-light">Explore Features</a>
                </div>
            </div>

            <div class="panel hero-side">
                <div class="mini-card">
                    <strong>Built for teachers and admin</strong>
                    <span>Fast access to batches, students, attendance, and admissions from one place.</span>
                </div>
                <div class="mini-card">
                    <strong>Easy student journey</strong>
                    <span>From online admission to enrollment, fees, attendance, and profile tracking.</span>
                </div>
                <div class="mini-card">
                    <strong>Clear and organized</strong>
                    <span>Simple screens, practical workflows, and less clutter for day-to-day use.</span>
                </div>
            </div>
        </section>

        <section id="features" class="feature-grid">
            <article class="feature-card">
                <div class="feature-icon">01</div>
                <h2>Admissions</h2>
                <ul>
                    <li>Batch-specific admission links</li>
                    <li>Online student application</li>
                    <li>Approval before enrollment</li>
                </ul>
            </article>

            <article class="feature-card">
                <div class="feature-icon">02</div>
                <h2>Students</h2>
                <ul>
                    <li>Clean student profiles</li>
                    <li>Multi-batch enrollment support</li>
                    <li>Quick search and lookup</li>
                </ul>
            </article>

            <article class="feature-card">
                <div class="feature-icon">03</div>
                <h2>Attendance</h2>
                <ul>
                    <li>Face-ready attendance flow</li>
                    <li>QR and manual fallback</li>
                    <li>Mobile-friendly marking</li>
                </ul>
            </article>

            <article class="feature-card">
                <div class="feature-icon">04</div>
                <h2>Management</h2>
                <ul>
                    <li>Batch schedules and teachers</li>
                    <li>Fee setup and collection</li>
                    <li>Reports and daily tracking</li>
                </ul>
            </article>
        </section>
    </div>
</body>
</html>
