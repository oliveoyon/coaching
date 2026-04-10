@extends('layouts.app')

@section('title', 'Take Attendance')

@section('page_header')
    <div>
        <div class="page-section-title">Academic Operations</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Batch Attendance Entry</h1>
        <p class="text-secondary mb-0">Load one batch and one date, then mark attendance quickly for the active roster.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('status') }}</div>
        @endif

        <div class="admin-card p-4 mb-4">
            <form method="GET" action="{{ route('attendance.create') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="batch_filter" class="form-label fw-semibold">Batch</label>
                    <select id="batch_filter" name="batch_id" class="form-select rounded-4">
                        <option value="">Select batch</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->id }}" @selected((string) request('batch_id', $selectedBatch?->id) === (string) $batch->id)>{{ $batch->name }} ({{ $batch->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="attendance_date_filter" class="form-label fw-semibold">Attendance Date</label>
                    <input id="attendance_date_filter" type="date" name="attendance_date" class="form-control rounded-4" value="{{ request('attendance_date', $attendanceDate->toDateString()) }}">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-outline-dark rounded-4">Load Roster</button>
                </div>
            </form>
        </div>

        @if ($selectedBatch)
            <div class="admin-card p-4 mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="small text-secondary">Batch</div>
                        <div class="fw-semibold">{{ $selectedBatch->name }}</div>
                        <div class="small text-secondary">{{ $selectedBatch->code }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-secondary">Owner Teacher</div>
                        <div class="fw-semibold">{{ $selectedBatch->ownerTeacher?->name ?? 'Not set' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-secondary">Attendance Date</div>
                        <div class="fw-semibold">{{ $attendanceDate->format('d M Y') }}</div>
                        <div class="small text-secondary">{{ $session ? 'Existing session loaded for update.' : 'New session will be created on save.' }}</div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('attendance.store') }}">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $selectedBatch->id }}">
                <input type="hidden" name="attendance_date" value="{{ $attendanceDate->toDateString() }}">

                <div class="admin-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div>
                            <div class="page-section-title text-success-emphasis">Attendance Sheet</div>
                            <div class="small text-secondary">Default status loads as present. Adjust only the students who differ.</div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 js-set-all" data-status="present">Mark All Present</button>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 js-set-all" data-status="absent">Mark All Absent</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle module-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roster as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $row['student']?->name }}</div>
                                            <div class="small text-secondary">{{ $row['student']?->student_code }}</div>
                                        </td>
                                        <td style="min-width: 240px;">
                                            <input type="hidden" name="records[{{ $index }}][student_id]" value="{{ $row['student']?->id }}">
                                            <input type="hidden" name="records[{{ $index }}][student_enrollment_id]" value="{{ $row['enrollment']?->id }}">
                                            <div class="attendance-status-group">
                                                @foreach ($statuses as $status)
                                                    <label class="attendance-status-pill">
                                                        <input type="radio" name="records[{{ $index }}][status]" value="{{ $status }}" class="attendance-status-input" @checked(old("records.$index.status", $row['status']) === $status)>
                                                        <span>{{ str($status)->title() }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error("records.$index.status") <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                                        </td>
                                        <td>
                                            <input type="text" name="records[{{ $index }}][remarks]" class="form-control rounded-4" value="{{ old("records.$index.remarks", $row['remarks']) }}" placeholder="Optional note">
                                            @error("records.$index.remarks") <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-secondary">No active enrollment roster found for this batch.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-card p-4">
                    <label for="notes" class="form-label fw-semibold">Session Notes</label>
                    <textarea id="notes" name="notes" rows="3" class="form-control rounded-4">{{ old('notes', $session?->notes) }}</textarea>
                    @error('notes') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">Save Attendance</button>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .attendance-status-group {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .attendance-status-pill {
            position: relative;
            margin: 0;
        }

        .attendance-status-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .attendance-status-pill span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 74px;
            padding: .45rem .9rem;
            border-radius: 999px;
            border: 1px solid #d7deea;
            background: #fff;
            color: #334155;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s ease;
        }

        .attendance-status-input:checked + span {
            background: #1d4ed8;
            border-color: #1d4ed8;
            color: #fff;
            box-shadow: 0 6px 16px rgba(29, 78, 216, .18);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-set-all').forEach(function (button) {
                button.addEventListener('click', function () {
                    const status = button.dataset.status;

                    document.querySelectorAll(`.attendance-status-input[value="${status}"]`).forEach(function (input) {
                        input.checked = true;
                    });
                });
            });
        });
    </script>
@endpush
