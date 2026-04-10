@extends('layouts.app')

@section('title', 'Add Fee Override')

@section('page_header')
    <div>
        <div class="page-section-title">Fee Foundation</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Add Student Fee Override</h1>
        <p class="text-secondary mb-0">Override the resolved base fee for a specific student when needed.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <form method="POST" action="{{ route('student-fee-overrides.store') }}">
            @csrf
            @include('student-fee-overrides.partials.form', ['submitLabel' => 'Save Override'])
        </form>
    </div>
@endsection
