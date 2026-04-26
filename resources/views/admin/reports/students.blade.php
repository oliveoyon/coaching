@extends('layouts.admin')

@section('title', 'Student Reports')
@section('page-title', 'Student Reports')
@section('page-subtitle', 'Detailed student list with class, active batches, and contact information.')

@section('content')
    <div class="card page-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('reports.students') }}" class="row g-3 align-items-end">
                <div class="col-lg-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" value="{{ $search }}" class="form-control" placeholder="Code, name, phone, guardian phone">
                </div>
                <div class="col-lg-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
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
                            <th>Code</th>
                            <th>Class</th>
                            <th>Phone</th>
                            <th>Guardian Phone</th>
                            <th>Active Batches</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $student->name }}</div>
                                    <div class="text-muted small">{{ $student->school ?: 'School not set' }}</div>
                                </td>
                                <td>{{ $student->student_code }}</td>
                                <td>{{ $student->academicClass?->name ?: '-' }}</td>
                                <td>{{ $student->phone ?: '-' }}</td>
                                <td>{{ $student->guardian_phone ?: '-' }}</td>
                                <td>
                                    @forelse ($student->enrollments as $enrollment)
                                        <div>{{ $enrollment->batch?->name }}</div>
                                    @empty
                                        <span class="text-muted">No active batch</span>
                                    @endforelse
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $student->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($student->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No students found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($students->hasPages())
            <div class="card-footer bg-white">
                {{ $students->links() }}
            </div>
        @endif
    </div>
@endsection
