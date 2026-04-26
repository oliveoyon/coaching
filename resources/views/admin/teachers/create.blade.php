@extends('layouts.admin')

@section('title', 'Create Teacher')
@section('page-title', 'Create Teacher')
@section('page-subtitle', 'Create a teacher record with a linked user account.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.teachers.store') }}">
                @include('admin.teachers._form', ['submitLabel' => 'Create Teacher'])
            </form>
        </div>
    </div>
@endsection
