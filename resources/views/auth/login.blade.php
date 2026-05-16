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
            --card: rgba(255, 255, 255, .94);
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Instrument Sans", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(16, 185, 129, .12) 0, transparent 26%),
                radial-gradient(circle at bottom right, rgba(59, 130, 246, .08) 0, transparent 24%),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        }

        .login-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .login-card {
            width: 100%;
            max-width: 430px;
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, .85);
            border-radius: 28px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .08);
            backdrop-filter: blur(10px);
            padding: 32px 28px 28px;
        }

        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 24px;
        }

        .brand-mark {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-deep) 100%);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .brand h1 {
            margin: 0;
            font-size: 1.5rem;
            letter-spacing: -.03em;
        }

        .brand p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: .95rem;
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

        .login-btn {
            border-radius: 14px;
            padding: .88rem 1rem;
            font-weight: 600;
            background: var(--brand);
            border-color: var(--brand);
        }

        .login-btn:hover,
        .login-btn:focus {
            background: var(--brand-deep);
            border-color: var(--brand-deep);
        }

        .forgot-link {
            text-decoration: none;
            font-size: .92rem;
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="login-card">
            <div class="brand">
                <span class="brand-mark">C</span>
                <h1>{{ config('app.name', 'Coaching CMS') }}</h1>
                <p>Sign in to continue</p>
            </div>

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
    </div>
</body>
</html>
