@extends('layouts.app')

@section('title', 'Edit Student')

@section('page_header')
    <div>
        <div class="page-section-title">Student Module</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Manage Student</h1>
        <p class="text-secondary mb-0">Update the student profile, academic owner, and optional guardian details.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <form method="POST" action="{{ route('students.update', $student) }}">
            @csrf
            @method('PUT')
            @include('students.partials.form', ['submitLabel' => 'Update Student'])
        </form>
    </div>
@endsection
