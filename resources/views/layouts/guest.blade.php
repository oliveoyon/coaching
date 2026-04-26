<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: "Instrument Sans", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(13, 110, 253, .12), transparent 30%),
                radial-gradient(circle at bottom right, rgba(25, 135, 84, .10), transparent 28%),
                #eef2f7;
            min-height: 100vh;
        }

        .auth-shell {
            min-height: 100vh;
        }

        .auth-card {
            border: 0;
            border-radius: 1.25rem;
            box-shadow: 0 18px 50px rgba(15, 23, 42, .10);
        }

        .auth-brand {
            width: 64px;
            height: 64px;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container auth-shell py-5 d-flex align-items-center justify-content-center">
        <div class="row justify-content-center w-100">
            <div class="col-12 col-md-8 col-lg-5 col-xl-4">
                <div class="text-center mb-4">
                    <a href="/" class="text-decoration-none">
                        <span class="auth-brand bg-primary rounded-4 d-inline-flex align-items-center justify-content-center shadow-sm mb-3">
                            <i class="bi bi-mortarboard-fill text-white fs-3"></i>
                        </span>
                    </a>
                    <h1 class="h3 fw-bold mb-1">{{ $heading ?? 'Coaching Management System' }}</h1>
                    @isset($subheading)
                        <p class="text-muted mb-0">{{ $subheading }}</p>
                    @endisset
                </div>

                <div class="card auth-card">
                    <div class="card-body p-4 p-lg-5">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
