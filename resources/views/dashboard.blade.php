@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', $dashboardRole . ' Dashboard')
@section('page-subtitle', $dashboardSubtitle)

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card metric-card bg-primary-subtle">
                <div class="card-body">
                    <div class="text-muted small mb-2">Current User</div>
                    <div class="h5 mb-0">{{ $user->name }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card metric-card bg-success-subtle">
                <div class="card-body">
                    <div class="text-muted small mb-2">Dashboard Role</div>
                    <div class="h5 mb-0">{{ $dashboardRole }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card metric-card bg-warning-subtle">
                <div class="card-body">
                    <div class="text-muted small mb-2">Reports Access</div>
                    <div class="h5 mb-0">{{ $user->can('view reports') ? 'Allowed' : 'Restricted' }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card metric-card bg-info-subtle">
                <div class="card-body">
                    <div class="text-muted small mb-2">Primary Access Flow</div>
                    <div class="h5 mb-0">
                        @if ($user->hasRole('Teacher'))
                            Own Work Scope
                        @elseif ($user->hasRole('Accounts'))
                            Finance Scope
                        @else
                            Full Operations
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">Quick Access</h2>
            <div class="d-flex flex-wrap gap-2">
                @can('manage users')
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">Manage Users</a>
                @endcan

                @can('manage batches')
                    <a href="{{ route('admin.batches.index') }}" class="btn btn-outline-primary">Manage Batches</a>
                @endcan

                @if ($user->hasAnyRole(['Super Admin', 'Admin']))
                    <a href="{{ route('admin.rbac-demo') }}" class="btn btn-outline-primary">View RBAC Demo</a>
                @endif

                @can('view reports')
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Open Reports</a>
                @endcan
            </div>
        </div>
    </div>
@endsection
