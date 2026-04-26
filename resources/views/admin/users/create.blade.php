@extends('layouts.admin')

@section('title', 'Create User')
@section('page-title', 'Create User')
@section('page-subtitle', 'Add a system user and assign the appropriate role.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @include('admin.users._form', ['submitLabel' => 'Create User'])
            </form>
        </div>
    </div>
@endsection
