<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Online Admission</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --line: #e5e7eb;
            --text: #0f172a;
            --muted: #64748b;
            --muted-soft: #94a3b8;
            --brand: #0f766e;
            --brand-dark: #115e59;
            --soft: #ecfdf5;
        }

        body {
            font-family: "Instrument Sans", "Noto Sans Bengali", sans-serif;
            background:
                radial-gradient(circle at top left, #dcfce7 0, transparent 24%),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
            color: var(--text);
            min-height: 100vh;
        }

        .page-shell {
            max-width: 1080px;
            margin: 0 auto;
            padding: 26px 16px 40px;
        }

        .brand-row {
            margin-bottom: 22px;
        }

        .brand-link {
            text-decoration: none;
            color: var(--text);
            font-weight: 700;
            font-size: 1rem;
        }

        .main-card {
            border: 0;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .08);
        }

        .hero-side {
            background: linear-gradient(160deg, #0f766e 0%, #115e59 100%);
            color: #fff;
            height: 100%;
            padding: 34px 28px;
        }

        .hero-side h1 {
            font-size: clamp(1.7rem, 2.5vw, 2.4rem);
            line-height: 1.1;
            letter-spacing: -.03em;
            margin-bottom: 12px;
        }

        .hero-side p {
            margin-bottom: 0;
            color: rgba(255, 255, 255, .8);
            line-height: 1.7;
        }

        .batch-box {
            margin-top: 24px;
            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: 22px;
            padding: 18px;
            background: rgba(255, 255, 255, .08);
        }

        .batch-box .muted {
            color: rgba(255, 255, 255, .68);
            font-size: 14px;
        }

        .batch-box .title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-top: 4px;
            margin-bottom: 6px;
        }

        .teacher-list {
            margin-top: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .teacher-chip {
            border-radius: 999px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, .12);
            font-size: 14px;
        }

        .form-side {
            background: var(--card);
            padding: 30px 22px;
        }

        .section-block + .section-block {
            margin-top: 24px;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .field-label {
            font-weight: 600;
            margin-bottom: 6px;
        }

        .field-note {
            color: var(--muted-soft);
            font-size: 13px;
            margin-top: 4px;
        }

        .field-note.bn {
            font-size: 12px;
        }

        .form-control,
        .form-select {
            border-radius: 14px;
            padding: .82rem .95rem;
            border-color: var(--line);
            box-shadow: none;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #99f6e4;
            box-shadow: 0 0 0 .2rem rgba(20, 184, 166, .12);
        }

        .camera-box {
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 18px;
            background: #f8fafc;
        }

        .camera-stage {
            aspect-ratio: 4 / 3;
            border-radius: 20px;
            overflow: hidden;
            background: #0f172a;
            position: relative;
        }

        .camera-stage video,
        .camera-stage img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .camera-placeholder {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: rgba(255,255,255,.88);
            padding: 20px;
        }

        .camera-actions .btn,
        .submit-btn {
            border-radius: 14px;
            padding: .85rem 1rem;
            font-weight: 600;
        }

        .submit-btn {
            background: var(--brand);
            border-color: var(--brand);
        }

        .submit-btn:hover,
        .submit-btn:focus {
            background: var(--brand-dark);
            border-color: var(--brand-dark);
        }

        .info-strip {
            border: 1px solid #dbeafe;
            background: #f8fbff;
            color: #1e3a8a;
            border-radius: 18px;
            padding: 14px 16px;
            font-size: 14px;
        }

        @media (max-width: 991.98px) {
            .hero-side,
            .form-side {
                padding: 24px 18px;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <div class="brand-row">
            <a href="{{ url('/') }}" class="brand-link">Coaching CMS</a>
        </div>

        <div class="card main-card">
            <div class="row g-0">
                <div class="col-lg-4">
                    <div class="hero-side">
                        <h1>Online Admission</h1>
                        <p>Fill in the student details and submit for approval.</p>
                        <p class="small mt-2 mb-0" style="color: rgba(255,255,255,.66);">তথ্য পূরণ করে অনুমোদনের জন্য জমা দিন।</p>

                        <div class="batch-box">
                            <div class="muted">Batch</div>
                            <div class="title">{{ $link->batch?->name ?: '-' }}</div>
                            <div class="muted">
                                {{ $link->batch?->academicClass?->name ?: '-' }}
                                @if ($link->batch?->subject)
                                    | {{ $link->batch->subject->name }}
                                @endif
                            </div>

                            @php
                                $dayLabels = [
                                    'sat' => 'Sat',
                                    'sun' => 'Sun',
                                    'mon' => 'Mon',
                                    'tue' => 'Tue',
                                    'wed' => 'Wed',
                                    'thu' => 'Thu',
                                    'fri' => 'Fri',
                                ];
                            @endphp

                            @if (count($link->batch?->schedule_entries ?? []) > 0)
                                <div class="muted mt-3">Time</div>
                                @foreach ($link->batch->schedule_entries as $entry)
                                    <div class="small mt-1">
                                        {{ $dayLabels[strtolower($entry['day'] ?? '')] ?? ucfirst((string) ($entry['day'] ?? '')) }}
                                        |
                                        {{ \Carbon\Carbon::createFromFormat('H:i', $entry['start_time'])->format('h:i A') }}
                                        -
                                        {{ \Carbon\Carbon::createFromFormat('H:i', $entry['end_time'])->format('h:i A') }}
                                    </div>
                                @endforeach
                            @endif

                            @if ($link->batch?->teachers?->isNotEmpty())
                                <div class="muted mt-3">Teachers</div>
                                <div class="teacher-list">
                                    @foreach ($link->batch->teachers as $teacher)
                                        <span class="teacher-chip">{{ $teacher->user?->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="form-side">
                        @if (session('success'))
                            <div class="alert alert-success rounded-4 mb-4">{{ session('success') }}</div>
                        @endif

                        <div class="info-strip mb-4">
                            Submit once. Admin will review the request and confirm the student.
                        </div>

                        <form method="POST" action="{{ route('admission.submit', $link->token) }}" enctype="multipart/form-data">
                            @csrf

                            <div class="section-block">
                                <div class="section-title">Student Info</div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="name" class="field-label">Student Name</label>
                                        <div class="field-note bn">শিক্ষার্থীর নাম</div>
                                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="school" class="field-label">School</label>
                                        <div class="field-note bn">স্কুল</div>
                                        <input type="text" name="school" id="school" value="{{ old('school') }}" class="form-control @error('school') is-invalid @enderror">
                                        @error('school')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="phone" class="field-label">Student Mobile</label>
                                        <div class="field-note">WhatsApp / mobile</div>
                                        <div class="field-note bn">শিক্ষার্থীর মোবাইল / হোয়াটসঅ্যাপ</div>
                                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="guardian_phone" class="field-label">Guardian Mobile</label>
                                        <div class="field-note">Required</div>
                                        <div class="field-note bn">অভিভাবকের মোবাইল</div>
                                        <input type="text" name="guardian_phone" id="guardian_phone" value="{{ old('guardian_phone') }}" class="form-control @error('guardian_phone') is-invalid @enderror" required>
                                        @error('guardian_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="address" class="field-label">Address</label>
                                        <div class="field-note bn">ঠিকানা</div>
                                        <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="section-block">
                                <div class="section-title">Face Registration</div>

                                <div class="camera-box">
                                    <div class="row g-4 align-items-start">
                                        <div class="col-lg-6">
                                            <div class="camera-stage">
                                                <video id="faceVideo" class="d-none" autoplay playsinline muted></video>
                                                <img id="facePreview" alt="Face preview" class="d-none">
                                                <div id="facePlaceholder" class="camera-placeholder">
                                                    <div>
                                                        <div class="fw-semibold mb-2">Use camera</div>
                                                        <div class="small text-white-50">মুখ স্পষ্ট করে ক্যামেরায় রাখুন</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="face_capture" id="faceCapture" value="{{ old('face_capture') }}">
                                            @error('face_capture')
                                                <div class="text-danger small mt-2">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="camera-actions d-grid gap-2">
                                                <button type="button" class="btn btn-primary" id="startCameraButton">Open Camera</button>
                                                <button type="button" class="btn btn-outline-primary d-none" id="captureButton">Capture</button>
                                                <button type="button" class="btn btn-outline-secondary d-none" id="retakeButton">Retake</button>
                                            </div>

                                            <div class="field-note mt-3">If camera is not available, upload a clear photo.</div>
                                            <div class="field-note bn">ক্যামেরা না থাকলে পরিষ্কার ছবি আপলোড করুন</div>

                                            <div class="mt-3">
                                                <label for="photo" class="field-label">Photo Upload</label>
                                                <div class="field-note bn">ছবি আপলোড</div>
                                                <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" accept=".jpg,.jpeg,.png">
                                                @error('photo')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg submit-btn">Submit Admission Request</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const startCameraButton = document.getElementById('startCameraButton');
            const captureButton = document.getElementById('captureButton');
            const retakeButton = document.getElementById('retakeButton');
            const faceVideo = document.getElementById('faceVideo');
            const facePreview = document.getElementById('facePreview');
            const facePlaceholder = document.getElementById('facePlaceholder');
            const faceCapture = document.getElementById('faceCapture');
            let mediaStream = null;

            const stopCamera = () => {
                if (mediaStream) {
                    mediaStream.getTracks().forEach((track) => track.stop());
                    mediaStream = null;
                }

                faceVideo.classList.add('d-none');
            };

            const showPreview = (dataUrl) => {
                faceCapture.value = dataUrl;
                facePreview.src = dataUrl;
                facePreview.classList.remove('d-none');
                facePlaceholder.classList.add('d-none');
                captureButton.classList.add('d-none');
                retakeButton.classList.remove('d-none');
                startCameraButton.classList.add('d-none');
            };

            if (faceCapture.value) {
                showPreview(faceCapture.value);
            }

            startCameraButton?.addEventListener('click', async () => {
                try {
                    mediaStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                    faceVideo.srcObject = mediaStream;
                    faceVideo.classList.remove('d-none');
                    facePreview.classList.add('d-none');
                    facePlaceholder.classList.add('d-none');
                    captureButton.classList.remove('d-none');
                    retakeButton.classList.add('d-none');
                } catch (error) {
                    alert('Camera access failed. Please allow camera access or upload a photo.');
                }
            });

            captureButton?.addEventListener('click', () => {
                if (!faceVideo.videoWidth || !faceVideo.videoHeight) {
                    return;
                }

                const canvas = document.createElement('canvas');
                canvas.width = faceVideo.videoWidth;
                canvas.height = faceVideo.videoHeight;
                const context = canvas.getContext('2d');
                context.drawImage(faceVideo, 0, 0, canvas.width, canvas.height);
                const dataUrl = canvas.toDataURL('image/png');
                showPreview(dataUrl);
                stopCamera();
            });

            retakeButton?.addEventListener('click', () => {
                faceCapture.value = '';
                facePreview.src = '';
                facePreview.classList.add('d-none');
                facePlaceholder.classList.remove('d-none');
                retakeButton.classList.add('d-none');
                startCameraButton.classList.remove('d-none');
            });

            window.addEventListener('beforeunload', stopCamera);
        })();
    </script>
</body>
</html>
