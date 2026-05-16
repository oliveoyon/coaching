@extends('layouts.admin')

@section('title', 'Create Batch')
@section('page-title', 'Create Batch')
@section('page-subtitle', 'Add a new batch')

@push('styles')
    <style>
        .batch-form-page {
            max-width: 1120px;
            margin: 0 auto;
        }

        .batch-form-wrap {
            border: 0;
            border-radius: 1.35rem;
            box-shadow: 0 18px 44px rgba(15, 23, 42, .05);
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, .06), transparent 26%),
                linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .batch-form-wrap .batch-form-section {
            padding: 1.15rem !important;
            border-radius: 1.15rem !important;
            border-color: #e5e7eb !important;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .03);
        }

        .batch-form-wrap .batch-form-section-title {
            font-size: 1rem;
            margin-bottom: 1rem !important;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .batch-form-wrap .batch-form-section-title::before {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: linear-gradient(135deg, #0ea5e9 0%, #14b8a6 100%);
            box-shadow: 0 0 0 6px rgba(20, 184, 166, .08);
            flex: 0 0 auto;
        }

        .batch-form-wrap .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: .45rem;
        }

        .batch-form-wrap .form-control,
        .batch-form-wrap .form-select {
            border-radius: .95rem;
            border-color: #dbe4f0;
            background: #f8fafc;
            padding-top: .72rem;
            padding-bottom: .72rem;
        }

        .batch-form-wrap .form-control:focus,
        .batch-form-wrap .form-select:focus {
            background: #fff;
            border-color: #93c5fd;
            box-shadow: 0 0 0 .18rem rgba(59, 130, 246, .12);
        }

        .batch-form-wrap .schedule-slot {
            padding: .9rem !important;
            border-radius: 1rem !important;
            border-color: #dbe4f0 !important;
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        }

        .batch-form-wrap .teacher-select-card {
            display: block;
            padding: .95rem 1rem !important;
            border-radius: 1rem !important;
            border: 1px solid #dbe4f0 !important;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%) !important;
            color: #0f172a !important;
            box-shadow: none !important;
            transition: .18s ease;
        }

        .batch-form-wrap .teacher-select-card .small {
            font-size: .78rem !important;
            color: #64748b !important;
        }

        .batch-form-wrap .teacher-select-card:hover {
            border-color: #93c5fd !important;
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(59, 130, 246, .08) !important;
        }

        .batch-form-wrap .btn-check:checked + .teacher-select-card {
            border-color: transparent !important;
            background: linear-gradient(135deg, #0f766e 0%, #0ea5e9 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 16px 30px rgba(14, 165, 233, .18) !important;
        }

        .batch-form-wrap .btn-check:checked + .teacher-select-card .small,
        .batch-form-wrap .btn-check:checked + .teacher-select-card .fw-semibold {
            color: #ffffff !important;
        }

        .batch-form-wrap .batch-form-actions {
            padding-top: .2rem;
        }

        .batch-form-wrap .btn-primary {
            border-radius: .95rem;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .batch-form-wrap .btn-outline-secondary,
        .batch-form-wrap .btn-outline-danger,
        .batch-form-wrap .btn-outline-primary {
            border-radius: .95rem;
        }
    </style>
@endpush

@section('content')
    <div class="batch-form-page">
        <div class="card page-card batch-form-wrap">
            <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.batches.store') }}">
                @include('admin.batches._form', ['submitLabel' => 'Create Batch'])
            </form>
            </div>
        </div>
    </div>
@endsection
