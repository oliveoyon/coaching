@extends('layouts.admin')

@section('title', 'Teacher Management')
@section('page-title', 'Teacher Management')
@section('page-subtitle', 'Manage teacher master records linked with login accounts.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.teachers.index') }}" class="row g-2 w-100 w-lg-auto">
                    <div class="col-12 col-md-auto">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search teacher name or email">
                    </div>
                    <div class="col-6 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                    </div>
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Teacher
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Batches</th>
                            <th>Created</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($teachers as $teacher)
                            <tr>
                                <td class="fw-semibold">{{ $teacher->user?->name }}</td>
                                <td>{{ $teacher->user?->email }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $teacher->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($teacher->status) }}
                                    </span>
                                </td>
                                <td>{{ $teacher->batches_count }}</td>
                                <td>{{ $teacher->created_at?->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No teachers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($teachers->hasPages())
                <div class="mt-4">
                    {{ $teachers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
