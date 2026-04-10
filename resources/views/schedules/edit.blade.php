@extends('layouts.app')

@section('title', 'Edit Schedule')

@section('page_header')
    <div>
        <div class="page-section-title">Academic Operations</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Edit Routine Schedule</h1>
        <p class="text-secondary mb-0">Update timing, teacher, room, or class type for this routine row.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <form method="POST" action="{{ route('schedules.update', $schedule) }}">
            @csrf
            @method('PUT')
            @php($submitLabel = 'Update Schedule')
            @include('schedules.partials.form')
        </form>
    </div>
@endsection
