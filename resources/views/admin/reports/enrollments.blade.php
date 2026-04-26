@extends('layouts.admin')

@section('title', 'Enrollment Reports')
@section('page-title', 'Enrollment Reports')
@section('page-subtitle', 'Detailed enrollment list with student, batch, teacher, and status filters.')

@section('content')
    <div class="card page-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.enrollments') }}" class="row g-3 align-items-end">
                <div class="col-lg-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" value="{{ $search }}" class="form-control" placeholder="Code, name, phone, guardian phone">
                </div>
                <div class="col-lg-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="withdrawn" @selected($status === 'withdrawn')>Withdrawn</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label for="class_id" class="form-label">Class</label>
                    <select name="class_id" id="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach ($classOptions as $class)
                            <option value="{{ $class->id }}" @selected((string) $classId === (string) $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label for="batch_id" class="form-label">Batch</label>
                    <select name="batch_id" id="batch_id" class="form-select">
                        <option value="">All Batches</option>
                        @foreach ($batchOptions as $batch)
                            <option value="{{ $batch->id }}" @selected((string) $batchId === (string) $batch->id)>{{ $batch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label for="teacher_id" class="form-label">Teacher</label>
                    <select name="teacher_id" id="teacher_id" class="form-select" @disabled($teacherScopeId)>
                        <option value="">All Teachers</option>
                        @foreach ($teacherOptions as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) $selectedTeacherId === (string) $teacher->id)>{{ $teacher->user?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1 d-grid">
                    <button type="submit" class="btn btn-outline-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Contact</th>
                            <th>Class</th>
                            <th>Batch</th>
                            <th>Teachers</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($enrollments as $enrollment)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $enrollment->student?->name }}</div>
                                    <div class="text-muted small">{{ $enrollment->student?->student_code }}</div>
                                </td>
                                <td>
                                    <div>{{ $enrollment->student?->phone ?: '-' }}</div>
                                    <div class="text-muted small">{{ $enrollment->student?->guardian_phone ?: '-' }}</div>
                                </td>
                                <td>{{ $enrollment->batch?->academicClass?->name ?: '-' }}</td>
                                <td>{{ $enrollment->batch?->name }}</td>
                                <td>{{ $enrollment->batch?->teachers?->pluck('user.name')->filter()->implode(', ') ?: '-' }}</td>
                                <td>{{ $enrollment->start_date?->format('d M Y') }}</td>
                                <td>{{ $enrollment->end_date?->format('d M Y') ?: '-' }}</td>
                                <td><span class="badge text-bg-{{ $enrollment->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($enrollment->status) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No enrollments found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($enrollments->hasPages())
            <div class="card-footer bg-white">
                {{ $enrollments->links() }}
            </div>
        @endif
    </div>
@endsection
