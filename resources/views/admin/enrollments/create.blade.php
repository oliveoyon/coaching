@extends('layouts.admin')

@section('title', 'Enroll Student')
@section('page-title', 'Enroll Student')
@section('page-subtitle', 'Add student to batch')

@push('styles')
    <style>
        .enrollment-form-page {
            max-width: 1040px;
            margin: 0 auto;
        }

        .enrollment-form-card {
            border: 0;
            border-radius: 1.35rem;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .05);
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, .06), transparent 26%),
                linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .enrollment-form-card .enrollment-section {
            padding: 1.05rem !important;
            border-radius: 1.1rem !important;
            border-color: #e5e7eb !important;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .03);
        }

        .enrollment-form-card .enrollment-work-section {
            border-color: #bfdbfe !important;
            background: linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%) !important;
            box-shadow: 0 14px 30px rgba(59, 130, 246, .08);
        }

        .enrollment-form-card .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: .45rem;
        }

        .enrollment-form-card .form-control,
        .enrollment-form-card .form-select {
            border-radius: .95rem;
            border-color: #dbe4f0;
            background: #f8fafc;
            padding-top: .72rem;
            padding-bottom: .72rem;
        }

        .enrollment-form-card .form-control:focus,
        .enrollment-form-card .form-select:focus {
            background: #fff;
            border-color: #93c5fd;
            box-shadow: 0 0 0 .18rem rgba(59, 130, 246, .12);
        }

        .enrollment-student-choice {
            text-decoration: none;
        }
    </style>
@endpush

@section('content')
    <div class="enrollment-form-page">
        <div class="card page-card enrollment-form-card">
            <div class="card-body p-4">
            <div class="border rounded-4 p-4 mb-4 enrollment-section">
                <form method="GET" action="{{ route('admin.enrollments.create') }}" class="row g-3">
                    <div class="col-lg-8">
                        <label for="student_search" class="form-label">{{ $selectedStudent && ! $changeStudent ? 'Student' : 'Find Student' }}</label>
                        <input type="text" name="student_search" id="student_search" value="{{ $studentSearch }}" class="form-control" placeholder="Code, name, or phone">
                    </div>
                    @if ($selectedStudent && ! $changeStudent)
                        <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">
                        <input type="hidden" name="change_student" value="1">
                    @endif
                    <div class="col-sm-6 col-lg-auto d-grid">
                        <label class="form-label d-none d-lg-block">&nbsp;</label>
                        <button type="submit" class="btn btn-outline-primary">{{ $selectedStudent && ! $changeStudent ? 'Find Another' : 'Find' }}</button>
                    </div>
                    <div class="col-sm-6 col-lg-auto d-grid">
                        <label class="form-label d-none d-lg-block">&nbsp;</label>
                        <a href="{{ route('admin.enrollments.create') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>

                @if ($selectedStudent && ! $changeStudent)
                    <div class="border rounded-4 p-3 mt-3 bg-light-subtle">
                        <div class="small text-muted mb-1">Selected</div>
                        <div class="fw-semibold">{{ $selectedStudent->name }}</div>
                        <div class="small text-muted">{{ $selectedStudent->student_code }} | {{ $selectedStudent->academicClass?->name }} | {{ $selectedStudent->phone ?: $selectedStudent->guardian_phone ?: '-' }}</div>
                    </div>
                @endif
            </div>

            @if ($studentSearch !== '' && (! $selectedStudent || $changeStudent))
                <div class="border rounded-4 p-4 mb-4 enrollment-section">
                    <div class="fw-semibold mb-3">Search Results</div>

                    @if ($students->isNotEmpty())
                        <div class="list-group list-group-flush">
                            @foreach ($students as $student)
                                <div class="list-group-item rounded-4 mb-2 border border-light-subtle">
                                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                                        <div>
                                            <div class="fw-semibold">{{ $student->name }}</div>
                                            <div class="small text-muted">{{ $student->student_code }} | {{ $student->academicClass?->name }}</div>
                                            <div class="small text-muted">{{ $student->phone ?: $student->guardian_phone ?: '-' }}</div>
                                        </div>
                                        <a href="{{ route('admin.enrollments.create', ['student_search' => $studentSearch, 'student_id' => $student->id]) }}" class="btn btn-sm btn-outline-primary enrollment-student-choice">Select</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted">No student found.</div>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('admin.enrollments.store') }}">
                @csrf

                <div class="border rounded-4 p-4 mb-4 enrollment-section enrollment-work-section">
                    <h2 class="h5 mb-3">Enrollment Details</h2>

                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label">Student</label>
                            @if ($selectedStudent)
                                <div class="border rounded-4 px-3 py-2 bg-body-tertiary">
                                    <div class="fw-semibold">{{ $selectedStudent->name }}</div>
                                    <div class="small text-muted">{{ $selectedStudent->student_code }} | {{ $selectedStudent->academicClass?->name }}</div>
                                </div>
                                <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">
                                <input type="hidden" name="student_search" value="{{ $studentSearch }}">
                                <input type="hidden" name="change_student" value="0">
                            @else
                                <div class="border rounded-4 p-3 text-muted">Find and select a student first.</div>
                            @endif
                            @error('student_id')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8">
                            <label for="batch_id" class="form-label">Batch</label>
                            <select name="batch_id" id="batch_id" class="form-select @error('batch_id') is-invalid @enderror" required>
                                <option value="">Select batch</option>
                                @foreach ($batches as $batch)
                                    <option value="{{ $batch->id }}" data-fee-count="{{ $batch->batch_fees_count }}" @selected((string) old('batch_id') === (string) $batch->id)>
                                        {{ $batch->name }} ({{ $batch->academicClass?->name }}@if($batch->subject) - {{ $batch->subject?->name }}@endif)
                                    </option>
                                @endforeach
                            </select>
                            @error('batch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="batchFeeNotice" class="small mt-2 d-none text-danger">Set batch fees first. Free batch? add fees with 0 amount.</div>
                            <div id="batchFeeReady" class="small mt-2 d-none text-success">Fee setup ready.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="form-control @error('start_date') is-invalid @enderror" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" {{ $selectedStudent ? '' : 'disabled' }}>Save Enrollment</button>
                </div>
            </form>
        </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const batchField = document.getElementById('batch_id');
            const feeNotice = document.getElementById('batchFeeNotice');
            const feeReady = document.getElementById('batchFeeReady');

            const syncBatchFeeState = () => {
                if (! batchField || ! feeNotice || ! feeReady) {
                    return;
                }

                const selectedOption = batchField.options[batchField.selectedIndex];
                const feeCount = Number(selectedOption?.dataset.feeCount || 0);
                const hasBatch = batchField.value !== '';

                feeNotice.classList.toggle('d-none', !hasBatch || feeCount > 0);
                feeReady.classList.toggle('d-none', !hasBatch || feeCount === 0);
            };

            batchField?.addEventListener('change', syncBatchFeeState);
            syncBatchFeeState();
        })();
    </script>
@endpush
