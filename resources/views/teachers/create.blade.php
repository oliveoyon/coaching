@extends('layouts.app')

@section('title', 'Create Teacher')

@section('page_header')
    <div>
        <div class="page-section-title">Teacher Module</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-2">Create Teacher</h1>
        <p class="text-secondary mb-0">Add a teacher profile, optionally link the teacher user account, and enable ownership or fee collection rights.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <form method="POST" action="{{ route('teachers.store') }}">
            @csrf
            @include('teachers.partials.form', [
                'submitLabel' => 'Save Teacher',
                'isSelfManaged' => false,
            ])
        </form>
    </div>
@endsection
