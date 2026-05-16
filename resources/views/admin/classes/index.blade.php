@extends('layouts.admin')

@section('title', 'Class Management')
@section('page-title', 'Class Management')
@section('page-subtitle', 'Manage classes')

@section('content')
    @php
        $activeCount = $classes->getCollection()->where('status', 'active')->count();
        $inactiveCount = $classes->getCollection()->where('status', 'inactive')->count();
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                <div class="card-body p-3">
                    <div class="text-success-emphasis small fw-semibold mb-1">Active</div>
                    <div class="fs-4 fw-bold text-success">{{ $activeCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem; background: linear-gradient(135deg, #f5f3ff 0%, #e9d5ff 100%);">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold mb-1">Inactive</div>
                    <div class="fs-4 fw-bold text-secondary">{{ $inactiveCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.classes.index') }}" class="row g-2 flex-grow-1">
                    <div class="col-12 col-lg-6">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search class name">
                    </div>
                    <div class="col-6 col-lg-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                    </div>
                    <div class="col-6 col-lg-auto">
                        <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> New Class
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
                        @forelse ($classes as $class)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $class->name }}</div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill {{ $class->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($class->status) }}
                                    </span>
                                </td>
                                <td>{{ $class->created_at?->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">No classes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($classes->hasPages())
                <div class="mt-4">
                    {{ $classes->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
