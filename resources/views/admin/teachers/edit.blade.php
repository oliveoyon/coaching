@extends('layouts.admin')

@section('title', 'Edit Teacher')
@section('page-title', 'Edit Teacher')
@section('page-subtitle', 'Update teacher profile and linked login account details.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.teachers.update', $teacher) }}">
                @method('PUT')
                @include('admin.teachers._form', ['submitLabel' => 'Update Teacher'])
            </form>
        </div>
    </div>
@endsection
