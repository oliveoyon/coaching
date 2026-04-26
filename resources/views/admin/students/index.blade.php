@extends('layouts.admin')

@section('title', 'Student Management')
@section('page-title', 'Student Management')
@section('page-subtitle', 'Manage admissions and keep student records active or inactive without deleting history.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.students.index') }}" class="row g-2 w-100 w-lg-auto">
                    <div class="col-12 col-md-auto">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by code, name, student phone, or guardian phone">
                    </div>
                    <div class="col-6 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                    </div>
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i> Admit Student
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Photo</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Phone</th>
                            <th>Guardian Phone</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr>
                                <td>
                                    @if ($student->photoUrl())
                                        <img src="{{ $student->photoUrl() }}" alt="{{ $student->name }}" class="rounded border" style="width: 48px; height: 48px; object-fit: cover;">
                                    @else
                                        <span class="text-muted small">No photo</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $student->student_code }}</td>
                                <td>{{ $student->name }}</td>
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
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">No students found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($students->hasPages())
                <div class="mt-4">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
