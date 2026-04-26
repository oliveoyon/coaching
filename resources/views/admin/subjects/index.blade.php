@extends('layouts.admin')

@section('title', 'Subject Management')
@section('page-title', 'Subject Management')
@section('page-subtitle', 'Manage subject master data for batches and enrollments.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.subjects.index') }}" class="row g-2 w-100 w-lg-auto">
                    <div class="col-12 col-md-auto">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search subject name">
                    </div>
                    <div class="col-6 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                    </div>
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Subject
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subjects as $subject)
                            <tr>
                                <td class="fw-semibold">{{ $subject->name }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $subject->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($subject->status) }}
                                    </span>
                                </td>
                                <td>{{ $subject->created_at?->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">No subjects found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($subjects->hasPages())
                <div class="mt-4">
                    {{ $subjects->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
