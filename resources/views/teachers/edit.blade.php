@extends('layouts.app')

@section('title', 'Edit Teacher')

@section('page_header')
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-end gap-3">
        <div>
            <div class="page-section-title">Teacher Module</div>
            <h1 class="h3 fw-bold text-dark mt-2 mb-2">{{ $teacher->name }}</h1>
            <p class="text-secondary mb-0">
                {{ $isSelfManaged ? 'Update the parts of your profile that remain inside your own scope.' : 'Maintain teacher profile, access flags, and linked account details.' }}
            </p>
        </div>
        <span class="badge rounded-pill {{ $teacher->status === \App\Models\Teacher::STATUS_ACTIVE ? 'text-bg-success' : 'text-bg-secondary' }} px-3 py-2">
            {{ ucfirst($teacher->status) }}
        </span>
    </div>
@endsection

@section('content')
    <div class="py-4">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('teachers.update', $teacher) }}">
            @csrf
            @method('PATCH')

            @include('teachers.partials.form', [
                'submitLabel' => 'Update Teacher',
            ])
        </form>
    </div>
@endsection
