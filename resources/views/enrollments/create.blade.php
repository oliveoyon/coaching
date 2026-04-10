@extends('layouts.app')

@section('title', 'Add Enrollment')

@section('page_header')
    <div>
        <div class="page-section-title">Enrollment Module</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Add Enrollment</h1>
        <p class="text-secondary mb-0">Assign a student to a batch without mixing enrollment into the student master profile.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <form method="POST" action="{{ route('enrollments.store') }}">
            @csrf
            @include('enrollments.partials.form', ['submitLabel' => 'Create Enrollment'])
        </form>
    </div>
@endsection
