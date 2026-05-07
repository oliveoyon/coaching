@extends('layouts.admin')

@section('title', 'Attendance Workspace')
@section('page-title', 'Attendance Workspace')
@section('page-subtitle', 'Mark attendance quickly from one screen.')

@php
    $modeLabels = ['face' => 'Face', 'qr' => 'QR / Barcode', 'manual' => 'Manual'];
    $gridRecords = $records->sortBy([
        fn ($record) => $record->status === 'pending' ? 0 : 1,
        fn ($record) => optional($record->student?->faceRegistrations?->first())->status === 'verified' ? 0 : 1,
        fn ($record) => $record->student?->name,
    ])->values();
@endphp

@section('content')
    <div id="attendanceMessage"></div>
    <div id="attendanceLiveBanner" class="attendance-live-banner d-none" aria-live="polite"></div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <div>
                            <h2 class="h4 mb-1">{{ $attendance->batch?->name }}</h2>
                            <div class="text-muted">
                                {{ $attendance->batch?->academicClass?->name }}
                                @if ($attendance->batch?->subject)
                                    | {{ $attendance->batch->subject->name }}
                                @endif
                                | {{ $attendance->attendance_date?->format('d M Y') }}
                            </div>
                        </div>
                        <div class="text-lg-end">
                            <span class="badge rounded-pill {{ $attendance->status === 'completed' ? 'text-bg-success' : 'text-bg-warning' }}">
                                {{ str_replace('_', ' ', ucfirst($attendance->status)) }}
                            </span>
                            <div class="small text-muted mt-2">
                                Teachers: {{ $attendance->batch?->teachers?->pluck('user.name')->filter()->implode(', ') ?: 'Not assigned' }}
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        @foreach (['total' => 'primary', 'present' => 'success', 'late' => 'info', 'excused' => 'secondary', 'pending' => 'warning', 'absent' => 'danger'] as $key => $tone)
                            <div class="col-md-4 col-xl-2">
                                <div class="card metric-card bg-{{ $tone }}-subtle border-0">
                                    <div class="card-body">
                                        <div class="text-muted small">{{ ucfirst($key) }}</div>
                                        <div class="fs-4 fw-semibold" data-summary-key="{{ $key }}">{{ $summary[$key] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0">Close Day Safely</h2>
                        <span class="small text-muted"><span data-summary-key="pending">{{ $summary['pending'] }}</span> pending</span>
                    </div>
                    <div class="small text-muted mb-3">
                        When you complete the session, any remaining pending students are marked absent automatically so the day finishes cleanly.
                    </div>
                    <form method="POST" action="{{ route('admin.attendance.complete', $attendance) }}" id="completeAttendanceForm">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success w-100" id="completeAttendanceButton" {{ $attendance->status === 'completed' && $summary['pending'] === 0 ? 'disabled' : '' }}>
                            Mark Remaining Pending as Absent and Complete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 align-items-xl-end">
                <div>
                    <div class="text-muted small mb-2">Quick Mode Switch</div>
                    <ul class="nav nav-pills gap-2" id="attendanceModeTabs" role="tablist">
                        @foreach ($modeLabels as $mode => $label)
                            <li class="nav-item" role="presentation">
                                <button
                                    class="btn {{ $selectedMode === $mode ? 'btn-primary' : 'btn-outline-primary' }} attendance-mode-button"
                                    id="attendance-mode-{{ $mode }}-tab"
                                    data-bs-toggle="pill"
                                    data-bs-target="#attendance-mode-{{ $mode }}"
                                    data-mode="{{ $mode }}"
                                    type="button"
                                    role="tab"
                                    aria-controls="attendance-mode-{{ $mode }}"
                                    aria-selected="{{ $selectedMode === $mode ? 'true' : 'false' }}"
                                >
                                    {{ $label }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <form method="GET" action="{{ route('admin.attendance.show', $attendance) }}" class="row g-2 align-items-end">
                    <input type="hidden" name="mode" id="attendanceModeFilter" value="{{ $selectedMode }}">
                    <div class="col-sm-auto">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" value="{{ $search }}" class="form-control" placeholder="Code, name, or phone">
                    </div>
                    <div class="col-sm-auto">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All</option>
                            @foreach (['pending', 'present', 'late', 'excused', 'absent'] as $statusOption)
                                <option value="{{ $statusOption }}" @selected($status === $statusOption)>{{ ucfirst($statusOption) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-auto d-grid">
                        <button type="submit" class="btn btn-outline-secondary">Apply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="tab-content mb-4">
        <div class="tab-pane fade {{ $selectedMode === 'face' ? 'show active' : '' }}" id="attendance-mode-face" role="tabpanel" aria-labelledby="attendance-mode-face-tab">
            <div class="card page-card">
                <div class="card-body p-4">
                    <div class="row g-4 align-items-start">
                        <div class="col-xl-7">
                            <div class="ratio ratio-16x9 rounded-4 overflow-hidden bg-dark position-relative border">
                                <video id="attendanceFaceVideo" class="w-100 h-100 object-fit-cover d-none" autoplay playsinline muted></video>
                                <canvas id="attendanceFaceCanvas" class="w-100 h-100 object-fit-cover d-none"></canvas>
                                <canvas id="attendanceFaceOverlay" class="position-absolute top-0 start-0 w-100 h-100 d-none"></canvas>
                                <div id="attendanceFacePlaceholder" class="d-flex flex-column align-items-center justify-content-center text-center text-white p-4">
                                    <div class="fw-semibold mb-2">Camera is not open yet</div>
                                    <div class="small opacity-75">Tap open camera to start.</div>
                                </div>
                                <div id="attendanceFaceGuide" class="position-absolute top-50 start-50 translate-middle border border-2 border-white rounded-4 d-none" style="width: 36%; height: 54%; box-shadow: 0 0 0 9999px rgba(0, 0, 0, .12);"></div>
                            </div>
                        </div>
                        <div class="col-xl-5">
                            <div class="d-grid gap-2 mb-3">
                                <button type="button" class="btn btn-primary" id="openAttendanceCameraButton">Open Camera</button>
                                <button type="button" class="btn btn-outline-success d-none" id="toggleAttendanceAutoButton">Auto Mark: On</button>
                                <button type="button" class="btn btn-outline-primary d-none" id="captureAttendanceFrameButton">Freeze Current Frame</button>
                                <button type="button" class="btn btn-outline-secondary d-none" id="resumeAttendanceCameraButton">Resume Live Camera</button>
                                <button type="button" class="btn btn-outline-secondary d-none" id="stopAttendanceCameraButton">Stop Camera</button>
                            </div>

                            <div class="border rounded-3 p-3 bg-light-subtle mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Face Status</span>
                                    <span class="badge text-bg-light border" id="faceEngineStatus">Loading...</span>
                                </div>
                                <div class="small text-muted mt-2" id="faceEngineNote">Preparing face matching.</div>
                            </div>

                            <div class="border rounded-3 p-3 bg-light-subtle">
                                <div class="fw-semibold mb-2">Last Match</div>
                                <div class="small text-muted" id="faceRecentActivity">No face match yet.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade {{ $selectedMode === 'qr' ? 'show active' : '' }}" id="attendance-mode-qr" role="tabpanel" aria-labelledby="attendance-mode-qr-tab">
            <div class="card page-card">
                <div class="card-body p-4">
                    <div class="row g-4 align-items-end">
                        <div class="col-xl-8">
                            <h2 class="h5 mb-2">QR / Barcode</h2>
                            <div class="text-muted small">Scan or type student code or phone number.</div>
                        </div>
                        <div class="col-xl-4">
                            <form method="POST" action="{{ route('admin.attendance.scan', $attendance) }}" class="row g-2" id="attendanceScanForm">
                                @csrf
                                <div class="col-12">
                                    <label for="scan_code" class="form-label">Scan or Enter Code</label>
                                    <input type="text" name="scan_code" id="scan_code" class="form-control @error('scan_code') is-invalid @enderror" placeholder="STD0001 or phone">
                                    @error('scan_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-7">
                                    <label for="scan_status" class="form-label">Mark As</label>
                                    <select name="status" id="scan_status" class="form-select">
                                        @foreach ($scanStatusOptions as $optionValue => $optionLabel)
                                            <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-5 d-grid">
                                    <label class="form-label d-none d-sm-block">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary">Mark</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade {{ $selectedMode === 'manual' ? 'show active' : '' }}" id="attendance-mode-manual" role="tabpanel" aria-labelledby="attendance-mode-manual-tab">
            <div class="card page-card">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-xl-8">
                            <h2 class="h5 mb-2">Manual</h2>
                            <div class="text-muted small">Tap a student card below.</div>
                        </div>
                        <div class="col-xl-4">
                            <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                <div class="fw-semibold mb-2">Low data mode</div>
                                <div class="small text-muted">Small updates, no full page reload for each mark.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h5 mb-1">Student Grid</h2>
            <div class="small text-muted">Pending students stay first.</div>
        </div>
    </div>

    <div class="row g-4" id="attendanceRecordGrid">
        @forelse ($gridRecords as $record)
            @php
                $latestFace = $record->student?->faceRegistrations?->first();
                $previewUrl = $latestFace?->previewUrl() ?: $record->student?->photoUrl();
                $badgeClass = match ($record->status) {
                    'present' => 'text-bg-success',
                    'late' => 'text-bg-info',
                    'excused' => 'text-bg-secondary',
                    'absent' => 'text-bg-danger',
                    default => 'text-bg-warning',
                };
            @endphp
            <div
                class="col-sm-6 col-lg-4 col-xxl-3 attendance-record-card"
                data-record-id="{{ $record->id }}"
                data-student-id="{{ $record->student_id }}"
                data-student-name="{{ $record->student?->name }}"
                data-student-code="{{ $record->student?->student_code }}"
                data-face-source="{{ $previewUrl ?: '' }}"
                data-face-ready="{{ $latestFace && $latestFace->status === 'verified' ? '1' : '0' }}"
                data-current-status="{{ $record->status }}"
            >
                <div class="card page-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            @if ($previewUrl)
                                <img src="{{ $previewUrl }}" alt="{{ $record->student?->name }}" class="rounded-circle border" style="width: 68px; height: 68px; object-fit: cover;">
                            @else
                                <div class="rounded-circle border bg-light d-inline-flex align-items-center justify-content-center text-muted" style="width: 68px; height: 68px;">No</div>
                            @endif
                            <div class="min-w-0">
                                <div class="fw-semibold">{{ $record->student?->name }}</div>
                                <div class="small text-muted">{{ $record->student?->student_code }}</div>
                                <div class="small {{ $latestFace && $latestFace->status === 'verified' ? 'text-success' : 'text-warning' }}">
                                    {{ $latestFace && $latestFace->status === 'verified' ? 'Face ready' : 'Face not ready' }}
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center small mb-2">
                            <span>{{ $record->student?->guardian_phone ?: $record->student?->phone ?: '-' }}</span>
                            <span class="badge rounded-pill attendance-status-badge {{ $badgeClass }}">{{ ucfirst($record->status) }}</span>
                        </div>
                        <div class="small text-muted mb-3">
                            Marked by: <span class="attendance-marker-name">{{ $record->marker?->name ?: '-' }}</span>
                            <br>
                            Time: <span class="attendance-marked-at">{{ $record->marked_at?->format('d M h:i A') ?: '-' }}</span>
                        </div>

                        <form method="POST" action="{{ route('admin.attendance.records.mark', ['attendance' => $attendance, 'record' => $record]) }}" class="attendance-mark-form d-grid gap-2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="method" class="attendance-method-input" value="{{ $selectedMode }}">
                            <button type="submit" name="status" value="present" class="btn btn-success btn-sm attendance-mark-button">Present</button>
                            <div class="row g-2">
                                <div class="col-6 d-grid">
                                    <button type="submit" name="status" value="late" class="btn btn-outline-info btn-sm attendance-mark-button">Late</button>
                                </div>
                                <div class="col-6 d-grid">
                                    <button type="submit" name="status" value="excused" class="btn btn-outline-secondary btn-sm attendance-mark-button">Excused</button>
                                </div>
                            </div>
                            <button type="submit" name="status" value="absent" class="btn btn-outline-danger btn-sm attendance-mark-button">Absent</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card page-card">
                    <div class="card-body py-5 text-center text-muted">No student matched the current filters.</div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="attendance-mobile-modebar d-md-none">
        <button type="button" class="btn btn-primary attendance-mobile-mode-button" data-mobile-mode="face">Face</button>
        <button type="button" class="btn btn-outline-primary attendance-mobile-mode-button" data-mobile-mode="qr">QR</button>
        <button type="button" class="btn btn-outline-primary attendance-mobile-mode-button" data-mobile-mode="manual">Manual</button>
    </div>
@endsection

@push('styles')
    <style>
        body {
            padding-bottom: 0;
        }

        .attendance-live-banner {
            position: sticky;
            top: 1rem;
            z-index: 1085;
            margin-bottom: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, #198754, #20c997);
            color: #fff;
            box-shadow: 0 0.85rem 2rem rgba(25, 135, 84, 0.28);
        }

        .attendance-live-banner.is-showing {
            animation: attendanceBannerIn 0.28s ease;
        }

        .attendance-live-banner .banner-name {
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .attendance-live-banner .banner-meta {
            font-size: 0.9rem;
            opacity: 0.92;
        }

        .attendance-record-card {
            transition: transform 0.25s ease, opacity 0.25s ease;
        }

        .attendance-record-card .page-card {
            transition: box-shadow 0.25s ease, border-color 0.25s ease, transform 0.25s ease;
        }

        .attendance-record-card.is-just-marked .page-card {
            border-color: rgba(25, 135, 84, 0.55);
            box-shadow: 0 1rem 2rem rgba(25, 135, 84, 0.22);
            transform: translateY(-3px);
        }

        .attendance-record-card.is-next-focus .page-card {
            border-color: rgba(13, 110, 253, 0.45);
            box-shadow: 0 0 0 0.35rem rgba(13, 110, 253, 0.12);
        }

        .attendance-mobile-modebar {
            position: fixed;
            right: 0.9rem;
            bottom: 0.9rem;
            left: 0.9rem;
            z-index: 1080;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            padding: 0.65rem;
            border: 1px solid rgba(13, 110, 253, 0.12);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 1rem 2rem rgba(15, 23, 42, 0.16);
            backdrop-filter: blur(10px);
        }

        .attendance-mobile-mode-button {
            min-height: 46px;
            font-size: 0.92rem;
            font-weight: 600;
        }

        @keyframes attendanceBannerIn {
            from {
                opacity: 0;
                transform: translateY(-12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 767.98px) {
            body {
                padding-bottom: 5.75rem;
            }

            .attendance-live-banner {
                padding: 0.9rem 1rem;
            }

            .attendance-live-banner .banner-name {
                font-size: 1.05rem;
            }

            .page-card .card-body {
                padding: 1rem;
            }

            .attendance-record-card .card-body {
                padding: 0.9rem;
            }

            .attendance-record-card img,
            .attendance-record-card .rounded-circle.border,
            .attendance-record-card .rounded-circle.bg-light {
                width: 58px !important;
                height: 58px !important;
            }

            .attendance-record-card .btn {
                min-height: 42px;
                font-size: 0.9rem;
                font-weight: 600;
            }

            #scan_code,
            #scan_status,
            #search,
            #status {
                min-height: 46px;
                font-size: 0.98rem;
            }

            .nav#attendanceModeTabs {
                display: none;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (() => {
            const modeButtons = document.querySelectorAll('.attendance-mode-button');
            const mobileModeButtons = document.querySelectorAll('.attendance-mobile-mode-button');
            const modeFilter = document.getElementById('attendanceModeFilter');
            const methodInputs = document.querySelectorAll('.attendance-method-input');
            const qrInput = document.getElementById('scan_code');
            const messageBox = document.getElementById('attendanceMessage');
            const liveBanner = document.getElementById('attendanceLiveBanner');
            const completeForm = document.getElementById('completeAttendanceForm');
            const completeButton = document.getElementById('completeAttendanceButton');
            const scanForm = document.getElementById('attendanceScanForm');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const attendanceGrid = document.getElementById('attendanceRecordGrid');

            const openButton = document.getElementById('openAttendanceCameraButton');
            const autoButton = document.getElementById('toggleAttendanceAutoButton');
            const captureButton = document.getElementById('captureAttendanceFrameButton');
            const resumeButton = document.getElementById('resumeAttendanceCameraButton');
            const stopButton = document.getElementById('stopAttendanceCameraButton');
            const video = document.getElementById('attendanceFaceVideo');
            const canvas = document.getElementById('attendanceFaceCanvas');
            const overlay = document.getElementById('attendanceFaceOverlay');
            const placeholder = document.getElementById('attendanceFacePlaceholder');
            const guide = document.getElementById('attendanceFaceGuide');
            const faceEngineStatus = document.getElementById('faceEngineStatus');
            const faceEngineNote = document.getElementById('faceEngineNote');
            const faceRecentActivity = document.getElementById('faceRecentActivity');
            let mediaStream = null;
            let faceApiLoaded = false;
            let faceMatcher = null;
            let labeledDescriptors = [];
            let detectionInterval = null;
            let autoMarkEnabled = true;
            let faceLoopBusy = false;
            const faceCooldownMap = new Map();
            let liveBannerTimeout = null;
            const FACE_MATCH_THRESHOLD = 0.48;
            const FACE_COOLDOWN_MS = 12000;
            const FACE_MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

            const badgeClassMap = {
                present: 'text-bg-success',
                late: 'text-bg-info',
                excused: 'text-bg-secondary',
                absent: 'text-bg-danger',
                pending: 'text-bg-warning',
            };

            const playSuccessTone = () => {
                try {
                    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContextClass) {
                        return;
                    }

                    const context = new AudioContextClass();
                    const oscillator = context.createOscillator();
                    const gainNode = context.createGain();

                    oscillator.type = 'sine';
                    oscillator.frequency.setValueAtTime(880, context.currentTime);
                    oscillator.frequency.exponentialRampToValueAtTime(1175, context.currentTime + 0.14);

                    gainNode.gain.setValueAtTime(0.001, context.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.08, context.currentTime + 0.02);
                    gainNode.gain.exponentialRampToValueAtTime(0.001, context.currentTime + 0.22);

                    oscillator.connect(gainNode);
                    gainNode.connect(context.destination);
                    oscillator.start();
                    oscillator.stop(context.currentTime + 0.22);
                    oscillator.onended = () => context.close();
                } catch (error) {
                    // Audio may be blocked on some devices.
                }
            };

            const pulseDevice = () => {
                if ('vibrate' in navigator) {
                    navigator.vibrate([90, 40, 90]);
                }
            };

            const showLiveBanner = (studentName, statusLabel, extra = '') => {
                if (!liveBanner) {
                    return;
                }

                if (liveBannerTimeout) {
                    clearTimeout(liveBannerTimeout);
                }

                liveBanner.innerHTML = `
                    <div class="banner-name">${studentName}</div>
                    <div class="banner-meta">${statusLabel}${extra ? ` • ${extra}` : ''}</div>
                `;
                liveBanner.classList.remove('d-none');
                liveBanner.classList.remove('is-showing');
                void liveBanner.offsetWidth;
                liveBanner.classList.add('is-showing');

                liveBannerTimeout = window.setTimeout(() => {
                    liveBanner.classList.add('d-none');
                    liveBanner.classList.remove('is-showing');
                }, 2400);
            };

            const showMessage = (message, type = 'success') => {
                if (!messageBox) {
                    return;
                }

                messageBox.innerHTML = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            };

            const updateSummary = (summary) => {
                if (!summary) {
                    return;
                }

                Object.entries(summary).forEach(([key, value]) => {
                    document.querySelectorAll(`[data-summary-key="${key}"]`).forEach((node) => {
                        node.textContent = value;
                    });
                });

                if (completeButton) {
                    completeButton.disabled = false;
                }
            };

            const sortAttendanceCards = () => {
                if (!attendanceGrid) {
                    return;
                }

                const cards = Array.from(attendanceGrid.querySelectorAll('.attendance-record-card'));
                cards
                    .sort((left, right) => {
                        const leftPending = left.dataset.currentStatus === 'pending' ? 0 : 1;
                        const rightPending = right.dataset.currentStatus === 'pending' ? 0 : 1;

                        if (leftPending !== rightPending) {
                            return leftPending - rightPending;
                        }

                        return (left.dataset.studentName || '').localeCompare(right.dataset.studentName || '');
                    })
                    .forEach((card) => attendanceGrid.appendChild(card));
            };

            const highlightNextPendingCard = () => {
                const cards = Array.from(document.querySelectorAll('.attendance-record-card'));
                cards.forEach((card) => card.classList.remove('is-next-focus'));

                const nextPendingCard = cards.find((card) => card.dataset.currentStatus === 'pending');
                if (!nextPendingCard) {
                    return;
                }

                nextPendingCard.classList.add('is-next-focus');
                nextPendingCard.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'nearest',
                });
            };

            const celebrateAttendanceCard = (card, payload, method = 'manual', extraMeta = '') => {
                if (!card || !payload?.record) {
                    return;
                }

                card.classList.remove('is-just-marked');
                void card.offsetWidth;
                card.classList.add('is-just-marked');
                window.setTimeout(() => card.classList.remove('is-just-marked'), 1800);

                sortAttendanceCards();
                highlightNextPendingCard();
                showLiveBanner(
                    card.dataset.studentName || 'Student marked',
                    `${payload.record.status.charAt(0).toUpperCase()}${payload.record.status.slice(1)} marked`,
                    extraMeta || (method === 'face' ? 'Face' : method === 'qr' ? 'QR / Barcode' : '')
                );
                playSuccessTone();
                pulseDevice();
            };

            const setActiveMode = (mode) => {
                modeFilter.value = mode;
                methodInputs.forEach((input) => {
                    input.value = mode;
                });

                 mobileModeButtons.forEach((button) => {
                    button.classList.toggle('btn-primary', button.dataset.mobileMode === mode);
                    button.classList.toggle('btn-outline-primary', button.dataset.mobileMode !== mode);
                });

                if (mode === 'qr' && qrInput) {
                    setTimeout(() => qrInput.focus(), 150);
                }
            };

            const setFaceStatus = (label, note, tone = 'light') => {
                if (faceEngineStatus) {
                    faceEngineStatus.className = `badge text-bg-${tone}`;
                    if (tone === 'light') {
                        faceEngineStatus.classList.add('border');
                    }
                    faceEngineStatus.textContent = label;
                }

                if (faceEngineNote) {
                    faceEngineNote.textContent = note;
                }
            };

            const setRecentFaceActivity = (message) => {
                if (faceRecentActivity) {
                    faceRecentActivity.textContent = message;
                }
            };

            modeButtons.forEach((button) => {
                button.addEventListener('shown.bs.tab', (event) => {
                    modeButtons.forEach((item) => {
                        item.classList.remove('btn-primary');
                        item.classList.add('btn-outline-primary');
                    });

                    const currentButton = event.target;
                    currentButton.classList.remove('btn-outline-primary');
                    currentButton.classList.add('btn-primary');
                    setActiveMode(currentButton.dataset.mode);
                });
            });

            mobileModeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const mode = button.dataset.mobileMode;
                    const desktopButton = document.querySelector(`.attendance-mode-button[data-mode="${mode}"]`);

                    if (desktopButton) {
                        const tab = bootstrap.Tab.getOrCreateInstance(desktopButton);
                        tab.show();
                    } else {
                        setActiveMode(mode);
                    }
                });
            });

            document.querySelectorAll('.attendance-mark-form').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const submitter = event.submitter;
                    if (!submitter) {
                        return;
                    }

                    const formData = new FormData(form);
                    formData.set('status', submitter.value);

                    const buttons = form.querySelectorAll('.attendance-mark-button');
                    buttons.forEach((button) => button.disabled = true);

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: formData,
                        });

                        const payload = await response.json();

                        if (!response.ok) {
                            throw new Error(payload.message || 'Attendance update failed.');
                        }

                        const card = form.closest('.attendance-record-card');
                        const badge = card?.querySelector('.attendance-status-badge');
                        const markerName = card?.querySelector('.attendance-marker-name');
                        const markedAt = card?.querySelector('.attendance-marked-at');

                        if (card) {
                            card.dataset.currentStatus = payload.record.status;
                        }

                        if (badge) {
                            badge.textContent = payload.record.status.charAt(0).toUpperCase() + payload.record.status.slice(1);
                            badge.className = `badge rounded-pill attendance-status-badge ${badgeClassMap[payload.record.status] || badgeClassMap.pending}`;
                        }

                        if (markerName) {
                            markerName.textContent = payload.record.marked_by || '-';
                        }

                        if (markedAt) {
                            markedAt.textContent = payload.record.marked_at || '-';
                        }

                        updateSummary(payload.summary);
                        showMessage(payload.message, 'success');
                        celebrateAttendanceCard(card, payload, formData.get('method') || 'manual');
                    } catch (error) {
                        showMessage(error.message || 'Attendance update failed.', 'danger');
                    } finally {
                        buttons.forEach((button) => button.disabled = false);
                    }
                });
            });

            scanForm?.addEventListener('submit', async (event) => {
                event.preventDefault();

                const formData = new FormData(scanForm);
                const submitButton = scanForm.querySelector('button[type="submit"]');

                if (submitButton) {
                    submitButton.disabled = true;
                }

                try {
                    const response = await fetch(scanForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload.message || 'Code scan failed.');
                    }

                    const card = document.querySelector(`.attendance-record-card[data-record-id="${payload.record.id}"]`);
                    const badge = card?.querySelector('.attendance-status-badge');
                    const markerName = card?.querySelector('.attendance-marker-name');
                    const markedAt = card?.querySelector('.attendance-marked-at');

                    if (card) {
                        card.dataset.currentStatus = payload.record.status;
                    }

                    if (badge) {
                        badge.textContent = payload.record.status.charAt(0).toUpperCase() + payload.record.status.slice(1);
                        badge.className = `badge rounded-pill attendance-status-badge ${badgeClassMap[payload.record.status] || badgeClassMap.pending}`;
                    }

                    if (markerName) {
                        markerName.textContent = payload.record.marked_by || '-';
                    }

                    if (markedAt) {
                        markedAt.textContent = payload.record.marked_at || '-';
                    }

                    updateSummary(payload.summary);
                    showMessage(payload.message, 'success');
                    celebrateAttendanceCard(card, payload, 'qr');
                    scanForm.reset();
                    qrInput?.focus();
                } catch (error) {
                    showMessage(error.message || 'Code scan failed.', 'danger');
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                }
            });

            completeForm?.addEventListener('submit', async (event) => {
                event.preventDefault();

                if (completeButton) {
                    completeButton.disabled = true;
                }

                const formData = new FormData(completeForm);

                try {
                    const response = await fetch(completeForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload.message || 'Could not complete attendance.');
                    }

                    updateSummary(payload.summary);
                    showMessage(payload.message, 'success');
                    window.location.reload();
                } catch (error) {
                    showMessage(error.message || 'Could not complete attendance.', 'danger');
                    if (completeButton) {
                        completeButton.disabled = false;
                    }
                }
            });

            setActiveMode(modeFilter.value);
            sortAttendanceCards();
            highlightNextPendingCard();

            const stopCamera = () => {
                if (detectionInterval) {
                    clearInterval(detectionInterval);
                    detectionInterval = null;
                }

                if (mediaStream) {
                    mediaStream.getTracks().forEach((track) => track.stop());
                    mediaStream = null;
                }

                if (video) {
                    video.srcObject = null;
                    video.classList.add('d-none');
                }

                if (canvas) {
                    canvas.classList.add('d-none');
                }

                if (overlay) {
                    overlay.classList.add('d-none');
                    const context = overlay.getContext('2d');
                    context?.clearRect(0, 0, overlay.width, overlay.height);
                }

                guide?.classList.add('d-none');
                placeholder?.classList.remove('d-none');
                openButton?.classList.remove('d-none');
                autoButton?.classList.add('d-none');
                captureButton?.classList.add('d-none');
                resumeButton?.classList.add('d-none');
                stopButton?.classList.add('d-none');
            };

            const loadScript = (src) => new Promise((resolve, reject) => {
                if (document.querySelector(`script[data-face-api="${src}"]`)) {
                    resolve();
                    return;
                }

                const script = document.createElement('script');
                script.src = src;
                script.async = true;
                script.dataset.faceApi = src;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });

            const loadFaceApi = async () => {
                if (faceApiLoaded && window.faceapi) {
                    return;
                }

                setFaceStatus('Loading', 'Downloading face engine...', 'warning');
                await loadScript('https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js');
                await Promise.all([
                    window.faceapi.nets.tinyFaceDetector.loadFromUri(FACE_MODEL_URL),
                    window.faceapi.nets.faceLandmark68Net.loadFromUri(FACE_MODEL_URL),
                    window.faceapi.nets.faceRecognitionNet.loadFromUri(FACE_MODEL_URL),
                ]);

                faceApiLoaded = true;
            };

            const buildFaceMatcher = async () => {
                if (!window.faceapi) {
                    return null;
                }

                const cards = Array.from(document.querySelectorAll('.attendance-record-card'))
                    .filter((card) => card.dataset.faceReady === '1' && card.dataset.faceSource);

                const descriptors = [];
                let processed = 0;

                for (const card of cards) {
                    processed += 1;
                    setFaceStatus('Preparing', `Loading registered faces ${processed}/${cards.length}...`, 'warning');

                    try {
                        const image = await window.faceapi.fetchImage(card.dataset.faceSource);
                        const result = await window.faceapi
                            .detectSingleFace(image, new window.faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
                            .withFaceLandmarks()
                            .withFaceDescriptor();

                        if (result?.descriptor) {
                            descriptors.push(new window.faceapi.LabeledFaceDescriptors(
                                String(card.dataset.recordId),
                                [result.descriptor]
                            ));
                        }
                    } catch (error) {
                        // Ignore one bad face source and continue.
                    }
                }

                labeledDescriptors = descriptors;
                faceMatcher = descriptors.length > 0 ? new window.faceapi.FaceMatcher(descriptors, FACE_MATCH_THRESHOLD) : null;

                if (descriptors.length > 0) {
                    setFaceStatus('Ready', `${descriptors.length} face profiles ready.`, 'success');
                    setRecentFaceActivity('Camera ready for automatic face attendance.');
                } else {
                    setFaceStatus('No Faces', 'No verified face profile found in this batch.', 'secondary');
                    setRecentFaceActivity('Use QR or Manual for this batch.');
                }

                return faceMatcher;
            };

            const drawFaceOverlay = (detections) => {
                if (!overlay || !video || !window.faceapi) {
                    return;
                }

                const displaySize = {
                    width: video.videoWidth,
                    height: video.videoHeight,
                };

                overlay.width = displaySize.width;
                overlay.height = displaySize.height;
                overlay.classList.remove('d-none');

                const resized = window.faceapi.resizeResults(detections, displaySize);
                const context = overlay.getContext('2d');
                context.clearRect(0, 0, overlay.width, overlay.height);
                window.faceapi.draw.drawDetections(overlay, resized);
            };

            const markAttendanceByRecordId = async (recordId, status = 'present', method = 'face', confidenceScore = null) => {
                const card = document.querySelector(`.attendance-record-card[data-record-id="${recordId}"]`);
                const form = card?.querySelector('.attendance-mark-form');

                if (!card || !form) {
                    return null;
                }

                const formData = new FormData(form);
                formData.set('status', status);
                formData.set('method', method);
                if (confidenceScore !== null) {
                    formData.set('confidence_score', confidenceScore.toFixed(2));
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'Attendance update failed.');
                }

                const badge = card.querySelector('.attendance-status-badge');
                const markerName = card.querySelector('.attendance-marker-name');
                const markedAt = card.querySelector('.attendance-marked-at');

                card.dataset.currentStatus = payload.record.status;

                if (badge) {
                    badge.textContent = payload.record.status.charAt(0).toUpperCase() + payload.record.status.slice(1);
                    badge.className = `badge rounded-pill attendance-status-badge ${badgeClassMap[payload.record.status] || badgeClassMap.pending}`;
                }

                if (markerName) {
                    markerName.textContent = payload.record.marked_by || '-';
                }

                if (markedAt) {
                    markedAt.textContent = payload.record.marked_at || '-';
                }

                updateSummary(payload.summary);
                showMessage(payload.message, 'success');
                celebrateAttendanceCard(card, payload, method, method === 'face' && confidenceScore !== null ? `${confidenceScore.toFixed(0)}% match` : '');

                return payload;
            };

            const runFaceDetection = async () => {
                if (!autoMarkEnabled || faceLoopBusy || !mediaStream || !faceMatcher || !window.faceapi || !video || video.readyState < 2) {
                    return;
                }

                faceLoopBusy = true;

                try {
                    const detections = await window.faceapi
                        .detectAllFaces(video, new window.faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
                        .withFaceLandmarks()
                        .withFaceDescriptors();

                    drawFaceOverlay(detections);

                    if (!detections.length) {
                        faceLoopBusy = false;
                        return;
                    }

                    for (const detection of detections) {
                        const bestMatch = faceMatcher.findBestMatch(detection.descriptor);

                        if (bestMatch.label === 'unknown' || bestMatch.distance > FACE_MATCH_THRESHOLD) {
                            continue;
                        }

                        const recordId = bestMatch.label;
                        const card = document.querySelector(`.attendance-record-card[data-record-id="${recordId}"]`);
                        if (!card || card.dataset.currentStatus !== 'pending') {
                            continue;
                        }

                        const lastMark = faceCooldownMap.get(recordId);
                        if (lastMark && (Date.now() - lastMark) < FACE_COOLDOWN_MS) {
                            continue;
                        }

                        faceCooldownMap.set(recordId, Date.now());

                        const confidence = Math.max(0, (1 - bestMatch.distance) * 100);
                        await markAttendanceByRecordId(recordId, 'present', 'face', confidence);
                        setRecentFaceActivity(`${card.dataset.studentName} detected and marked present.`);
                    }
                } catch (error) {
                    setRecentFaceActivity('Face detection paused. You can keep using QR or Manual.');
                } finally {
                    faceLoopBusy = false;
                }
            };

            openButton?.addEventListener('click', async () => {
                try {
                    await loadFaceApi();
                    await buildFaceMatcher();

                    mediaStream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user',
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        },
                        audio: false
                    });

                    video.srcObject = mediaStream;
                    video.classList.remove('d-none');
                    canvas?.classList.add('d-none');
                    overlay?.classList.remove('d-none');
                    guide?.classList.remove('d-none');
                    placeholder?.classList.add('d-none');
                    openButton.classList.add('d-none');
                    autoButton?.classList.remove('d-none');
                    captureButton?.classList.remove('d-none');
                    resumeButton?.classList.add('d-none');
                    stopButton?.classList.remove('d-none');

                    if (faceMatcher && !detectionInterval) {
                        detectionInterval = setInterval(runFaceDetection, 1400);
                    }
                } catch (error) {
                    showMessage('Camera access failed. Please allow camera permission in the browser. You can still switch to QR or Manual instantly.', 'danger');
                    setFaceStatus('Unavailable', 'Face recognition could not start on this device.', 'danger');
                }
            });

            autoButton?.addEventListener('click', () => {
                autoMarkEnabled = !autoMarkEnabled;
                autoButton.textContent = `Auto Mark: ${autoMarkEnabled ? 'On' : 'Off'}`;
                autoButton.classList.toggle('btn-outline-success', autoMarkEnabled);
                autoButton.classList.toggle('btn-outline-warning', !autoMarkEnabled);
                setRecentFaceActivity(autoMarkEnabled ? 'Automatic face marking resumed.' : 'Automatic face marking paused.');
            });

            captureButton?.addEventListener('click', () => {
                if (!video || !canvas || !video.videoWidth || !video.videoHeight) {
                    return;
                }

                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                canvas.classList.remove('d-none');
                video.classList.add('d-none');
                guide?.classList.add('d-none');
                overlay?.classList.add('d-none');
                captureButton.classList.add('d-none');
                resumeButton?.classList.remove('d-none');
            });

            resumeButton?.addEventListener('click', () => {
                canvas?.classList.add('d-none');
                video?.classList.remove('d-none');
                guide?.classList.remove('d-none');
                overlay?.classList.remove('d-none');
                captureButton?.classList.remove('d-none');
                resumeButton.classList.add('d-none');
            });

            stopButton?.addEventListener('click', stopCamera);
            window.addEventListener('beforeunload', stopCamera);
        })();
    </script>
@endpush
