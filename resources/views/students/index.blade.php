@extends('layouts.app')

@section('title', 'Students')

@section('page_header')
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-end gap-3">
        <div>
            <div class="page-section-title">Student Module</div>
            <h1 class="h3 fw-bold text-dark mt-2 mb-2">Students</h1>
            <p class="text-secondary mb-0">
                {{ auth()->user()->isAdmin() ? 'Manage all student profiles inside your coaching center.' : 'Manage only the students owned within your teacher scope.' }}
            </p>
        </div>
        @can('create', \App\Models\Student::class)
            <a href="{{ route('students.create') }}" class="btn btn-dark rounded-pill px-4 fw-semibold">Add Student</a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="py-4">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('status') }}</div>
        @endif

        <div class="admin-card p-3 p-lg-4 mb-4">
            <form method="GET" action="{{ route('students.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-5">
                    <label for="q" class="form-label fw-semibold">Search</label>
                    <input id="q" type="text" name="q" class="form-control rounded-4" value="{{ request('q') }}" placeholder="Student ID, name, phone, guardian">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <select id="status" name="status" class="form-select rounded-4">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if (auth()->user()->isAdmin())
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="owner_teacher_id" class="form-label fw-semibold">Teacher Owner</label>
                        <select id="owner_teacher_id" name="owner_teacher_id" class="form-select rounded-4">
                            <option value="">All teachers</option>
                            @foreach ($teacherOwners as $teacherOwner)
                                <option value="{{ $teacherOwner->id }}" @selected((string) request('owner_teacher_id') === (string) $teacherOwner->id)>{{ $teacherOwner->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-12 col-md-4 col-lg-1 d-grid">
                    <button type="submit" class="btn btn-primary rounded-4">Filter</button>
                </div>
            </form>
        </div>

        <div class="admin-card p-3 p-lg-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle module-table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Teacher Owner</th>
                            <th>Institution</th>
                            <th>Primary Guardian</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            @php($guardian = $student->guardians->firstWhere('pivot.is_primary', true))
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $student->name }}</div>
                                    <div class="small text-secondary">{{ $student->student_code }}{{ $student->phone ? ' • '.$student->phone : '' }}</div>
                                </td>
                                <td>{{ $student->ownerTeacher?->name ?? 'Not assigned' }}</td>
                                <td>
                                    <div>{{ $student->institution_name ?: 'Not set' }}</div>
                                    <div class="small text-secondary">{{ $student->institution_class ?: 'No class/group yet' }}</div>
                                </td>
                                <td>
                                    <div>{{ $guardian?->name ?? 'Not added' }}</div>
                                    <div class="small text-secondary">{{ $guardian?->phone ?? 'No phone added' }}</div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill text-bg-{{ $student->status === \App\Models\Student::STATUS_ACTIVE ? 'success' : ($student->status === \App\Models\Student::STATUS_COMPLETED ? 'primary' : ($student->status === \App\Models\Student::STATUS_DROPOUT ? 'danger' : 'secondary')) }}">
                                        {{ ucfirst($student->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Manage</a>
                                        @can('delete', $student)
                                            <form method="POST" action="{{ route('students.destroy', $student) }}" onsubmit="return confirm('Delete this student profile?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Delete</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">No student profiles are available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $students->links() }}
        </div>
    </div>
@endsection
