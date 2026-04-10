@extends('layouts.app')

@section('title', 'Collect Fee')

@section('page_header')
    <div>
        <div class="page-section-title">Fee Collection</div>
        <h1 class="h3 fw-bold text-dark mt-2 mb-1">Collect Fee</h1>
        <p class="text-secondary mb-0">Store both the academic owner and the actual collector while keeping due math accurate.</p>
    </div>
@endsection

@section('content')
    <div class="py-4">
        <div class="admin-card p-3 p-lg-4 mb-4">
            <form method="GET" action="{{ route('payments.create') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-7">
                    <label for="student_lookup" class="form-label fw-semibold">Student Lookup</label>
                    <input id="student_lookup" type="text" name="student_lookup" class="form-control rounded-4" value="{{ $lookupValue }}" placeholder="Enter student ID or student code like STU-1001">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="billing_period_lookup" class="form-label fw-semibold">Billing Period</label>
                    <input id="billing_period_lookup" type="text" name="billing_period_key" class="form-control rounded-4" value="{{ request('billing_period_key', now()->format('Y-m')) }}" placeholder="2026-04">
                </div>
                <div class="col-12 col-md-4 col-lg-2 d-grid">
                    <button type="submit" class="btn btn-primary rounded-4">Load Dues</button>
                </div>
            </form>

            @if ($lookupMissed)
                <div class="alert alert-warning rounded-4 border-0 mt-3 mb-0">No student matched that lookup inside your accessible scope.</div>
            @endif
        </div>

        <form method="POST" action="{{ route('payments.store') }}">
            @csrf
            @include('payments.partials.form')
        </form>
    </div>
@endsection
