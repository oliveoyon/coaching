@extends('layouts.app')

@section('title', 'Add Schedule')

@section('page_header')
    <div>
        <div class="page-section-title">Academic Operations</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Add Routine Schedule</h1>
        <p class="text-secondary mb-0">Create a batch schedule row with subject, teacher, and timing details.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <form method="POST" action="{{ route('schedules.store') }}">
            @csrf
            @php($submitLabel = 'Save Schedule')
            @include('schedules.partials.form')
        </form>
    </div>
@endsection
