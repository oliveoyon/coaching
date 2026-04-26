<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Batch Admission Form</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: "Instrument Sans", sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-9">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="bg-primary text-white p-4 p-lg-5">
                        <h1 class="h3 mb-2">Batch Admission Form</h1>
                        <p class="mb-0 opacity-75">Submit your information for admin verification and batch enrollment.</p>
                    </div>

                    <div class="card-body p-4 p-lg-5">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="alert alert-light border mb-4">
                            <div class="fw-semibold">{{ $link->batch?->name }}</div>
                            <div class="small text-muted">
                                Class: {{ $link->batch?->academicClass?->name }}
                                @if ($link->batch?->subject)
                                    | Subject: {{ $link->batch?->subject?->name }}
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admission.submit', $link->token) }}" enctype="multipart/form-data">
                            @csrf

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Student Name</label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Student WhatsApp / Mobile</label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror">
                                    <div class="form-text">Use the student's own WhatsApp/mobile number if available.</div>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="guardian_phone" class="form-label">Guardian WhatsApp / Mobile</label>
                                    <input type="text" name="guardian_phone" id="guardian_phone" value="{{ old('guardian_phone') }}" class="form-control @error('guardian_phone') is-invalid @enderror" required>
                                    <div class="form-text">A guardian number is required for follow-up.</div>
                                    @error('guardian_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="school" class="form-label">School</label>
                                    <input type="text" name="school" id="school" value="{{ old('school') }}" class="form-control @error('school') is-invalid @enderror">
                                    @error('school')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="photo" class="form-label">Student Photo</label>
                                    <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" accept=".jpg,.jpeg,.png" required>
                                    <div class="form-text">This will help later student identification and attendance features.</div>
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Submit Admission Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
