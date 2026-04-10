@extends('layouts.app')

@section('title', 'Edit Fee Override')

@section('page_header')
    <div>
        <div class="page-section-title">Fee Foundation</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Manage Student Fee Override</h1>
        <p class="text-secondary mb-0">Update a student-specific fee exception without altering the underlying structure.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <form method="POST" action="{{ route('student-fee-overrides.update', $override) }}">
            @csrf
            @method('PUT')
            @include('student-fee-overrides.partials.form', ['submitLabel' => 'Update Override'])
        </form>
    </div>
@endsection
