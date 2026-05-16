@extends('layouts.admin')

@section('title', 'Student Management')
@section('page-title', 'Student Management')
@section('page-subtitle', 'Find students quickly')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                <div class="card-body p-3">
                    <div class="text-primary-emphasis small fw-semibold mb-1">Total Students</div>
                    <div class="fs-4 fw-bold text-primary">{{ $studentStats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                <div class="card-body p-3">
                    <div class="text-success-emphasis small fw-semibold mb-1">Active</div>
                    <div class="fs-4 fw-bold text-success">{{ $studentStats['active'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem; background: linear-gradient(135deg, #f5f3ff 0%, #e9d5ff 100%);">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold mb-1">Inactive</div>
                    <div class="fs-4 fw-bold text-secondary">{{ $studentStats['inactive'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <h2 class="h5 mb-1">Student Filters</h2>
                    <div class="small text-muted">Search and open.</div>
                </div>

                <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i> Admit Student
                </a>
            </div>

            <form method="GET" action="{{ route('admin.students.index') }}" class="row g-3">
                <div class="col-lg-5">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" value="{{ $search }}" class="form-control" placeholder="Student code, name, phone, or guardian phone">
                </div>
                <div class="col-lg-3">
                    <label for="class_id" class="form-label">Class</label>
                    <select name="class_id" id="class_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected($classId === $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-sm-6 col-lg-1 d-grid">
                    <label class="form-label d-none d-lg-block">&nbsp;</label>
                    <button type="submit" class="btn btn-outline-primary">Find</button>
                </div>
                <div class="col-sm-6 col-lg-1 d-grid">
                    <label class="form-label d-none d-lg-block">&nbsp;</label>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            @if (! $hasFilters)
                <div class="py-5 text-center">
                    <div class="fw-semibold mb-2">Start with a filter</div>
                    <div class="text-muted">Search by student, class, or status.</div>
                </div>
            @elseif ($students && $students->count() > 0)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="small text-muted">{{ $students->total() }} student(s) found</div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Phone</th>
                                <th>Guardian</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $student)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if ($student->photoUrl())
                                                <img src="{{ $student->photoUrl() }}" alt="{{ $student->name }}" class="rounded border" style="width: 48px; height: 48px; object-fit: cover;">
                                            @else
                                                <div class="rounded border bg-light d-inline-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">No</div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold">{{ $student->name }}</div>
                                                <div class="small text-muted">{{ $student->student_code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $student->academicClass?->name }}</td>
                                    <td>{{ $student->phone ?: '-' }}</td>
                                    <td>{{ $student->guardian_phone }}</td>
                                    <td>
                                        <span class="badge rounded-pill {{ $student->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ ucfirst($student->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.student-profiles.show', $student) }}" class="btn btn-sm btn-outline-secondary">Profile</a>
                                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($students->hasPages())
                    <div class="mt-4">
                        {{ $students->links() }}
                    </div>
                @endif
            @else
                <div class="py-5 text-center text-muted">No students found.</div>
            @endif
        </div>
    </div>
@endsection
