@extends('layouts.app')

@section('title', 'Enrollments')

@section('page_header')
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-end gap-3">
        <div>
            <div class="page-section-title">Enrollment Module</div>
            <h1 class="h3 fw-bold text-dark mt-2 mb-2">Student Enrollments</h1>
            <p class="text-secondary mb-0">
                {{ auth()->user()->isAdmin() ? 'Manage batch-wise student assignments across your coaching center.' : 'Manage enrollments only for your own teaching scope.' }}
            </p>
        </div>
        @can('create', \App\Models\StudentEnrollment::class)
            <a href="{{ route('enrollments.create') }}" class="btn btn-dark rounded-pill px-4 fw-semibold">Add Enrollment</a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="py-4">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('status') }}</div>
        @endif

        <div class="admin-card p-3 p-lg-4 mb-4">
            <form method="GET" action="{{ route('enrollments.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-5">
                    <label for="q" class="form-label fw-semibold">Search</label>
                    <input id="q" type="text" name="q" class="form-control rounded-4" value="{{ request('q') }}" placeholder="Student ID, student name, batch name">
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
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="batch_id" class="form-label fw-semibold">Batch</label>
                    <select id="batch_id" name="batch_id" class="form-select rounded-4">
                        <option value="">All batches</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->id }}" @selected((string) request('batch_id') === (string) $batch->id)>{{ $batch->name }}</option>
                        @endforeach
                    </select>
                </div>
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
                            <th>Batch</th>
                            <th>Inferred Owner</th>
                            <th>Enrolled On</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($enrollments as $enrollment)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $enrollment->student?->name }}</div>
                                    <div class="small text-secondary">{{ $enrollment->student?->student_code }}</div>
                                </td>
                                <td>
                                    <div>{{ $enrollment->batch?->name }}</div>
                                    <div class="small text-secondary">{{ $enrollment->batch?->code }}</div>
                                </td>
                                <td>{{ $enrollment->batch?->ownerTeacher?->name ?? 'Not set' }}</td>
                                <td>{{ optional($enrollment->enrolled_at)->format('Y-m-d') ?: 'Not set' }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $enrollment->status === \App\Models\StudentEnrollment::STATUS_ACTIVE ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($enrollment->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('enrollments.edit', $enrollment) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Manage</a>
                                        @can('delete', $enrollment)
                                            <form method="POST" action="{{ route('enrollments.destroy', $enrollment) }}" onsubmit="return confirm('Remove this enrollment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Remove</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">No enrollments are available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $enrollments->links() }}
        </div>
    </div>
@endsection
