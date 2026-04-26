@extends('layouts.admin')

@section('title', 'RBAC Demo')
@section('page-title', 'RBAC Demo')
@section('page-subtitle', 'This page confirms role-protected access is working.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="border rounded-4 p-3 h-100 bg-light">
                        <div class="text-muted small mb-2">Middleware</div>
                        <div class="fw-semibold">role:Super Admin|Admin</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-4 p-3 h-100 bg-light">
                        <div class="text-muted small mb-2">User</div>
                        <div class="fw-semibold">{{ auth()->user()->name }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-4 p-3 h-100 bg-light">
                        <div class="text-muted small mb-2">Role(s)</div>
                        <div class="fw-semibold">{{ auth()->user()->getRoleNames()->implode(', ') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
