@extends('layouts.admin')

@section('title', 'Promotion')
@section('page-title', 'Promotion Center')
@section('page-subtitle', 'Move selected students to a new batch')

@push('styles')
    <style>
        .promotion-page {
            max-width: 1120px;
            margin: 0 auto;
        }

        .promotion-shell {
            border: 0;
            border-radius: 1.35rem;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .05);
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, .06), transparent 26%),
                linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .promotion-panel {
            border: 1px solid #e5e7eb;
            border-radius: 1.15rem;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .03);
        }

        .promotion-card {
            border: 1px solid #dbe4f0;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .promotion-batch-mini {
            border: 1px solid #dbe4f0;
            border-radius: 1rem;
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
            padding: .95rem 1rem;
            box-shadow: 0 10px 20px rgba(15, 23, 42, .03);
        }

        .promotion-student-row {
            border: 1px solid #dbe4f0;
            border-radius: 1rem;
            background: #fff;
            padding: .9rem 1rem;
            transition: .18s ease;
        }

        .promotion-student-row + .promotion-student-row {
            margin-top: .65rem;
        }

        .promotion-student-row:hover {
            border-color: #93c5fd;
            box-shadow: 0 10px 24px rgba(59, 130, 246, .06);
        }

        .promotion-check {
            width: 1.1rem;
            height: 1.1rem;
        }

        .promotion-section-title {
            font-size: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .promotion-section-title::before {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: linear-gradient(135deg, #0ea5e9 0%, #14b8a6 100%);
            box-shadow: 0 0 0 6px rgba(20, 184, 166, .08);
            flex: 0 0 auto;
        }

        .promotion-shell .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: .45rem;
        }

        .promotion-shell .form-control,
        .promotion-shell .form-select {
            border-radius: .95rem;
            border-color: #dbe4f0;
            background: #f8fafc;
            padding-top: .72rem;
            padding-bottom: .72rem;
        }

        .promotion-shell .form-control:focus,
        .promotion-shell .form-select:focus {
            background: #fff;
            border-color: #93c5fd;
            box-shadow: 0 0 0 .18rem rgba(59, 130, 246, .12);
        }
    </style>
@endpush

@section('content')
    <div class="promotion-page">
        <div class="card page-card promotion-shell">
            <div class="card-body p-4">
                <div class="row g-3">
        <div class="col-xl-4">
            <div class="promotion-panel p-3">
                <div class="promotion-section-title fw-semibold">Source</div>
                    <form method="GET" action="{{ route('admin.enrollments.promote') }}" class="row g-3">
                        <div class="col-12">
                            <label for="source_batch_id" class="form-label">Current Batch</label>
                            <select name="source_batch_id" id="source_batch_id" class="form-select" required>
                                <option value="">Select batch</option>
                                @foreach ($sourceBatches as $batch)
                                    <option value="{{ $batch->id }}" @selected((string) $sourceBatchId === (string) $batch->id)>
                                        {{ $batch->name }} ({{ $batch->academicClass?->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="student_search" class="form-label">Find Student</label>
                            <input type="text" name="student_search" id="student_search" value="{{ $studentSearch }}" class="form-control" placeholder="Code, name, phone">
                        </div>
                        <div class="col-6 d-grid">
                            <button type="submit" class="btn btn-outline-primary">Load Students</button>
                        </div>
                        <div class="col-6 d-grid">
                            <a href="{{ route('admin.enrollments.promote') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
            </div>

            @if ($sourceBatch)
                <div class="promotion-batch-mini mt-3">
                    <div class="small text-muted mb-1">Current Batch</div>
                    <div class="fw-semibold">{{ $sourceBatch->name }}</div>
                    <div class="small text-muted">
                        {{ $sourceBatch->academicClass?->name }}
                        @if ($sourceBatch->subject)
                            | {{ $sourceBatch->subject?->name }}
                        @endif
                    </div>
                    <div class="small text-muted mt-1">{{ $sourceBatch->teachers->pluck('user.name')->filter()->implode(', ') ?: '-' }}</div>
                </div>
            @endif

            @if ($targetBatch)
                <div class="promotion-batch-mini mt-3">
                    <div class="small text-muted mb-1">Target Batch</div>
                    <div class="fw-semibold">{{ $targetBatch->name }}</div>
                    <div class="small text-muted">
                        {{ $targetBatch->academicClass?->name }}
                        @if ($targetBatch->subject)
                            | {{ $targetBatch->subject?->name }}
                        @endif
                    </div>
                    <div class="small mt-1 {{ $targetBatch->batch_fees_count > 0 ? 'text-success' : 'text-danger' }}">
                        {{ $targetBatch->batch_fees_count > 0 ? 'Fee setup ready' : 'Set up fees first' }}
                    </div>
                </div>
            @endif
        </div>

        <div class="col-xl-8">
            <div class="promotion-panel p-3">
                    <form method="POST" action="{{ route('admin.enrollments.promote.store') }}">
                        @csrf

                        <div class="promotion-section-title fw-semibold">Promotion Details</div>

                        <div class="row g-3 mb-3">
                            <div class="col-lg-6">
                                <label for="target_batch_id" class="form-label">New Batch</label>
                                <select name="target_batch_id" id="target_batch_id" class="form-select @error('target_batch_id') is-invalid @enderror" required>
                                    <option value="">Select target batch</option>
                                    @foreach ($targetBatches as $batch)
                                        <option value="{{ $batch->id }}" data-fee-count="{{ $batch->batch_fees_count }}" @selected((string) old('target_batch_id', $targetBatchId) === (string) $batch->id)>
                                            {{ $batch->name }} ({{ $batch->academicClass?->name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('target_batch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="promotionFeeNotice" class="small text-danger mt-2 d-none">Set up target batch fees first. If this is a free batch, add the fee items with amount 0.</div>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="start_date" class="form-label">New Start Date</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="form-control @error('start_date') is-invalid @enderror" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="source_end_date" class="form-label">Current End Date</label>
                                <input type="date" name="source_end_date" id="source_end_date" value="{{ old('source_end_date') }}" class="form-control @error('source_end_date') is-invalid @enderror">
                                @error('source_end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <input type="hidden" name="source_batch_id" value="{{ old('source_batch_id', $sourceBatchId) }}">

                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
                            <div>
                                <div class="fw-semibold">Students</div>
                                <div class="small text-muted">{{ $sourceBatch ? $sourceEnrollments->count().' active student'.($sourceEnrollments->count() === 1 ? '' : 's') : 'Choose a current batch first.' }}</div>
                            </div>
                            @if ($sourceEnrollments->isNotEmpty())
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllPromotionStudents">Select All</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearPromotionStudents">Clear</button>
                                </div>
                            @endif
                        </div>

                        @error('enrollment_ids')
                            <div class="alert alert-danger py-2">{{ $message }}</div>
                        @enderror

                        @if ($sourceEnrollments->isNotEmpty())
                            <div class="promotion-card p-3 mb-3">
                                @foreach ($sourceEnrollments as $enrollment)
                                    <label class="promotion-student-row d-flex align-items-start gap-3">
                                        <input type="checkbox" class="form-check-input mt-1 promotion-check" name="enrollment_ids[]" value="{{ $enrollment->id }}" @checked(collect(old('enrollment_ids', []))->contains($enrollment->id))>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                                <div>
                                                    <div class="fw-semibold">{{ $enrollment->student?->name }}</div>
                                                    <div class="small text-muted">{{ $enrollment->student?->student_code }} | {{ $enrollment->student?->academicClass?->name }}</div>
                                                </div>
                                                <div class="small text-muted text-md-end">
                                                    <div>{{ $enrollment->student?->phone ?: '-' }}</div>
                                                    <div>{{ $enrollment->student?->guardian_phone ?: '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @elseif ($sourceBatch)
                            <div class="border rounded-4 p-4 text-center text-muted mb-3">No active student found in this batch.</div>
                        @else
                            <div class="border rounded-4 p-4 text-center text-muted mb-3">Choose a current batch to load students.</div>
                        @endif

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-secondary">Back</a>
                            <button type="submit" class="btn btn-primary" {{ $sourceEnrollments->isNotEmpty() ? '' : 'disabled' }}>Promote Selected</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const checkboxes = () => Array.from(document.querySelectorAll('input[name="enrollment_ids[]"]'));
            const selectAllButton = document.getElementById('selectAllPromotionStudents');
            const clearButton = document.getElementById('clearPromotionStudents');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('source_end_date');
            const targetBatch = document.getElementById('target_batch_id');
            const feeNotice = document.getElementById('promotionFeeNotice');

            selectAllButton?.addEventListener('click', () => {
                checkboxes().forEach((checkbox) => checkbox.checked = true);
            });

            clearButton?.addEventListener('click', () => {
                checkboxes().forEach((checkbox) => checkbox.checked = false);
            });

            const syncEndDate = () => {
                if (!startDate || !endDate || endDate.value !== '') {
                    return;
                }

                const value = startDate.value;

                if (!value) {
                    return;
                }

                const nextDate = new Date(value + 'T00:00:00');
                nextDate.setDate(nextDate.getDate() - 1);
                endDate.value = nextDate.toISOString().slice(0, 10);
            };

            const syncFeeState = () => {
                if (!targetBatch || !feeNotice) {
                    return;
                }

                const option = targetBatch.options[targetBatch.selectedIndex];
                const feeCount = Number(option?.dataset.feeCount || 0);
                const hasTarget = targetBatch.value !== '';

                feeNotice.classList.toggle('d-none', !hasTarget || feeCount > 0);
            };

            startDate?.addEventListener('change', syncEndDate);
            targetBatch?.addEventListener('change', syncFeeState);
            syncEndDate();
            syncFeeState();
        })();
    </script>
@endpush
