@extends('layouts.admin')

@section('title', 'Admit Student')
@section('page-title', 'Admit Student')
@section('page-subtitle', 'Add a new student')

@push('styles')
    <style>
        .student-form-page {
            max-width: 1100px;
            margin: 0 auto;
        }

        .student-form-card {
            border: 0;
            border-radius: 1.35rem;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .05);
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, .06), transparent 26%),
                linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .student-form-card .student-form-section {
            padding: 1.15rem !important;
            border-radius: 1.15rem !important;
            border-color: #e5e7eb !important;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .03);
        }

        .student-form-card .student-form-section-title {
            font-size: 1rem;
            margin-bottom: 1rem !important;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .student-form-card .student-form-section-title::before {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: linear-gradient(135deg, #0ea5e9 0%, #14b8a6 100%);
            box-shadow: 0 0 0 6px rgba(20, 184, 166, .08);
            flex: 0 0 auto;
        }

        .student-form-card .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: .45rem;
        }

        .student-form-card .form-control,
        .student-form-card .form-select {
            border-radius: .95rem;
            border-color: #dbe4f0;
            background: #f8fafc;
            padding-top: .72rem;
            padding-bottom: .72rem;
        }

        .student-form-card .form-control:focus,
        .student-form-card .form-select:focus {
            background: #fff;
            border-color: #93c5fd;
            box-shadow: 0 0 0 .18rem rgba(59, 130, 246, .12);
        }

        .student-form-card textarea.form-control {
            min-height: 110px;
        }

        .student-form-card .face-capture-panel {
            border-radius: 1rem !important;
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%) !important;
            border-color: #dbe4f0 !important;
        }
    </style>
@endpush

@section('content')
    <div class="student-form-page">
        <div class="card page-card student-form-card">
            <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
                @include('admin.students._form', ['submitLabel' => 'Save Admission'])
            </form>
            </div>
        </div>
    </div>
@endsection
