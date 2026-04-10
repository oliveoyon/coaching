@extends('layouts.app')

@section('title', 'Edit Enrollment')

@section('page_header')
    <div>
        <div class="page-section-title">Enrollment Module</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Manage Enrollment</h1>
        <p class="text-secondary mb-0">Update enrollment status or move the student to another allowed batch.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <form method="POST" action="{{ route('enrollments.update', $enrollment) }}">
            @csrf
            @method('PUT')
            @include('enrollments.partials.form', ['submitLabel' => 'Update Enrollment'])
        </form>
    </div>
@endsection
