@extends('layouts.admin')

@section('title', 'Profile')
@section('page-title', 'Profile')
@section('page-subtitle', 'Manage your account information and security settings.')

@section('content')
    <div class="row g-4">
        <div class="col-12">
            <div class="card page-card">
                <div class="card-body p-4">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card page-card">
                <div class="card-body p-4">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card page-card border-danger-subtle">
                <div class="card-body p-4">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
@endsection
