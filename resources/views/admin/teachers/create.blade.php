@extends('layouts.admin')

@section('title', 'Create Teacher')
@section('page-title', 'Create Teacher')
@section('page-subtitle', 'Add a new teacher')

@push('styles')
    <style>
        .setup-form-page {
            max-width: 980px;
            margin: 0 auto;
        }

        .setup-form-card {
            border: 0;
            border-radius: 1.35rem;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .05);
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, .06), transparent 26%),
                linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .setup-form-card .setup-form-section {
            padding: 1.15rem !important;
            border-radius: 1.15rem !important;
            border-color: #e5e7eb !important;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .03);
        }

        .setup-form-card .setup-form-section-title {
            font-size: 1rem;
            margin-bottom: 1rem !important;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .setup-form-card .setup-form-section-title::before {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: linear-gradient(135deg, #0ea5e9 0%, #14b8a6 100%);
            box-shadow: 0 0 0 6px rgba(20, 184, 166, .08);
            flex: 0 0 auto;
        }

        .setup-form-card .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: .45rem;
        }

        .setup-form-card .form-control,
        .setup-form-card .form-select {
            border-radius: .95rem;
            border-color: #dbe4f0;
            background: #f8fafc;
            padding-top: .72rem;
            padding-bottom: .72rem;
        }

        .setup-form-card .form-control:focus,
        .setup-form-card .form-select:focus {
            background: #fff;
            border-color: #93c5fd;
            box-shadow: 0 0 0 .18rem rgba(59, 130, 246, .12);
        }
    </style>
@endpush

@section('content')
    <div class="setup-form-page">
        <div class="card page-card setup-form-card">
            <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.teachers.store') }}">
                @include('admin.teachers._form', ['submitLabel' => 'Create Teacher'])
            </form>
            </div>
        </div>
    </div>
@endsection
