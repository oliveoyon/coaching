@extends('layouts.admin')

@section('title', 'Teacher Management')
@section('page-title', 'Teacher Management')
@section('page-subtitle', 'Manage teachers')

@section('content')
    @php
        $activeCount = $teachers->getCollection()->where('status', 'active')->count();
        $inactiveCount = $teachers->getCollection()->where('status', 'inactive')->count();
        $batchCount = $teachers->getCollection()->sum('batches_count');
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                <div class="card-body p-3">
                    <div class="text-success-emphasis small fw-semibold mb-1">Active</div>
                    <div class="fs-4 fw-bold text-success">{{ $activeCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem; background: linear-gradient(135deg, #f5f3ff 0%, #e9d5ff 100%);">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold mb-1">Inactive</div>
                    <div class="fs-4 fw-bold text-secondary">{{ $inactiveCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                <div class="card-body p-3">
                    <div class="text-primary-emphasis small fw-semibold mb-1">Assigned Batches</div>
                    <div class="fs-4 fw-bold text-primary">{{ $batchCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.teachers.index') }}" class="row g-2 flex-grow-1">
                    <div class="col-12 col-lg-6">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search teacher name or email">
                    </div>
                    <div class="col-6 col-lg-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                    </div>
                    <div class="col-6 col-lg-auto">
                        <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> New Teacher
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
                                <td>
                                    <div class="fw-semibold">{{ $teacher->user?->name }}</div>
                                </td>
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
