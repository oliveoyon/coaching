@extends('layouts.admin')

@section('title', 'Class Management')
@section('page-title', 'Class Management')
@section('page-subtitle', 'Manage academic class master data.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.classes.index') }}" class="row g-2 w-100 w-lg-auto">
                    <div class="col-12 col-md-auto">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search class name">
                    </div>
                    <div class="col-6 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                    </div>
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Class
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
                                <td class="fw-semibold">{{ $class->name }}</td>
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
