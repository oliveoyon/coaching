@extends('layouts.admin')

@section('title', 'Edit Student')
@section('page-title', 'Edit Student')
@section('page-subtitle', 'Update admission details while keeping the student record intact for future batch enrollments.')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data">
                @method('PUT')
                @include('admin.students._form', ['submitLabel' => 'Update Student'])
            </form>
        </div>
    </div>
@endsection
