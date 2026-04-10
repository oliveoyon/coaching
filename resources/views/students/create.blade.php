@extends('layouts.app')

@section('title', 'Add Student')

@section('page_header')
    <div>
        <div class="page-section-title">Student Module</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Add Student</h1>
        <p class="text-secondary mb-0">Create a student profile with optional primary guardian support.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <form method="POST" action="{{ route('students.store') }}">
            @csrf
            @include('students.partials.form', ['submitLabel' => 'Create Student'])
        </form>
    </div>
@endsection
