@extends('layouts.admin')

@section('title', 'Admit Student')
@section('page-title', 'Admit Student')
@section('page-subtitle', 'Add a new student')

@section('content')
    <div class="card page-card">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
                @include('admin.students._form', ['submitLabel' => 'Save Admission'])
            </form>
        </div>
    </div>
@endsection
