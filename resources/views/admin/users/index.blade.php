@extends('layouts.admin')

@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Create users and assign roles for system access.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 w-100 w-lg-auto">
                    <div class="col-12 col-md-auto">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by name, email, or role">
                    </div>
                    <div class="col-6 col-md-auto">
                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                    </div>
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>

                <div>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Add User
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $user->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td>
                                    @forelse ($user->roles as $role)
                                        <span class="badge rounded-pill text-bg-primary">{{ $role->name }}</span>
                                    @empty
                                        <span class="badge rounded-pill text-bg-secondary">No role</span>
                                    @endforelse
                                </td>
                                <td>{{ $user->created_at?->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
