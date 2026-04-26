@extends('layouts.admin')

@section('title', 'Report Hub')
@section('page-title', 'Report Hub')
@section('page-subtitle', 'Open focused reports instead of one crowded page. Use the filters inside each report screen for detailed operational data.')

@section('content')
    <div class="row g-4">
        @if ($canViewAcademicReports)
            <div class="col-md-6 col-xl-4">
                <div class="card page-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h2 class="h5 mb-1">Student Reports</h2>
                                <p class="text-muted small mb-0">Batch, class, teacher, and contact-based student lists.</p>
                            </div>
                            <i class="bi bi-people fs-3 text-primary"></i>
                        </div>
                        <a href="{{ route('reports.students') }}" class="btn btn-outline-primary">Open Report</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card page-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h2 class="h5 mb-1">Enrollment Reports</h2>
                                <p class="text-muted small mb-0">Active and withdrawn batch-wise enrollment details.</p>
                            </div>
                            <i class="bi bi-person-lines-fill fs-3 text-primary"></i>
                        </div>
                        <a href="{{ route('reports.enrollments') }}" class="btn btn-outline-primary">Open Report</a>
                    </div>
                </div>
            </div>
        @endif

        @if ($canViewFinanceReports)
            <div class="col-md-6 col-xl-4">
                <div class="card page-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h2 class="h5 mb-1">Due Reports</h2>
                                <p class="text-muted small mb-0">Month-wise unpaid fee rows with student and guardian contact.</p>
                            </div>
                            <i class="bi bi-exclamation-circle fs-3 text-primary"></i>
                        </div>
                        <a href="{{ route('reports.dues') }}" class="btn btn-outline-primary">Open Report</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card page-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h2 class="h5 mb-1">Collection Reports</h2>
                                <p class="text-muted small mb-0">Daily, batch-wise, teacher-wise, and detailed collection history.</p>
                            </div>
                            <i class="bi bi-wallet2 fs-3 text-primary"></i>
                        </div>
                        <a href="{{ route('reports.collections') }}" class="btn btn-outline-primary">Open Report</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card page-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h2 class="h5 mb-1">Teacher Finance</h2>
                                <p class="text-muted small mb-0">Teacher earnings, settlements, and outstanding payables.</p>
                            </div>
                            <i class="bi bi-cash-coin fs-3 text-primary"></i>
                        </div>
                        <a href="{{ route('reports.teacher-finance') }}" class="btn btn-outline-primary">Open Report</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card page-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h2 class="h5 mb-1">Expense Reports</h2>
                                <p class="text-muted small mb-0">Monthly summary plus detailed common and teacher expenses.</p>
                            </div>
                            <i class="bi bi-receipt fs-3 text-primary"></i>
                        </div>
                        <a href="{{ route('reports.expenses') }}" class="btn btn-outline-primary">Open Report</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
