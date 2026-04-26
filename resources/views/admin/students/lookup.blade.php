@extends('layouts.admin')

@section('title', 'Student Lookup')
@section('page-title', 'Student Lookup')
@section('page-subtitle', 'Search by code, name, phone, or guardian phone and jump straight into the student profile.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.student-lookup.index') }}" class="row g-3 align-items-end mb-4">
                <div class="col-lg-10">
                    <label for="student_search" class="form-label">Search</label>
                    <input type="text" name="student_search" id="student_search" value="{{ $search }}" class="form-control" placeholder="Type student code, name, phone, or guardian phone">
                </div>
                <div class="col-lg-2 d-grid">
                    <button type="submit" class="btn btn-primary">Find Student</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
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
                                <td class="fw-semibold">{{ $student->student_code }}</td>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->academicClass?->name ?: '-' }}</td>
                                <td>{{ $student->phone ?: '-' }}</td>
                                <td>{{ $student->guardian_phone ?: '-' }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $student->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($student->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.student-profiles.show', $student) }}" class="btn btn-sm btn-outline-primary">Open Profile</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    {{ $search !== '' ? 'No student matched your search.' : 'Start typing in the search box to find a student quickly.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
