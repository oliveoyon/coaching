@extends('layouts.admin')

@section('title', 'Report Hub')
@section('page-title', 'Report Hub')
@section('page-subtitle', 'Open the report you need.')

@section('content')
    <style>
        .report-hub .hero-card,
        .report-hub .mini-stat,
        .report-hub .report-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .report-hub .hero-card .card-body {
            padding: 1.15rem 1.2rem;
        }

        .report-hub .mini-stat .card-body {
            padding: 0.95rem 1rem;
        }

        .report-hub .mini-stat .label {
            font-size: 0.76rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
        }

        .report-hub .mini-stat .value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.1;
        }

        .report-hub .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .report-hub .report-card .card-body {
            padding: 1.05rem 1.1rem;
        }

        .report-hub .report-icon {
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .report-hub .report-points {
            margin: 0.8rem 0 1rem;
            padding-left: 1rem;
            color: #64748b;
            font-size: 0.86rem;
        }

        .report-hub .report-points li + li {
            margin-top: 0.2rem;
        }
    </style>

    <div class="report-hub">
        <div class="card hero-card mb-4" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 45%, #f8fafc 100%);">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                        <div class="section-title mb-1">Reports Center</div>
                        <div class="text-muted small">Choose a focused report instead of working inside one long mixed page.</div>
                    </div>
                    <div class="small text-muted">Filters are inside each report screen.</div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @if ($canViewAcademicReports)
                <div class="col-sm-6 col-xl-3">
                    <div class="card mini-stat" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                        <div class="card-body">
                            <div class="label text-primary">Students</div>
                            <div class="value">{{ $reportStats['students'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card mini-stat" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                        <div class="card-body">
                            <div class="label text-success">Active Enrollments</div>
                            <div class="value">{{ $reportStats['active_enrollments'] }}</div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-sm-6 col-xl-3">
                <div class="card mini-stat" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);">
                    <div class="card-body">
                        <div class="label text-warning">Active Batches</div>
                        <div class="value">{{ $reportStats['batches'] }}</div>
                    </div>
                </div>
            </div>
            @if ($canViewAcademicReports)
                <div class="col-sm-6 col-xl-3">
                    <div class="card mini-stat" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);">
                        <div class="card-body">
                            <div class="label text-danger">Pending Requests</div>
                            <div class="value">{{ $reportStats['pending_requests'] }}</div>
                        </div>
                    </div>
                </div>
            @elseif ($canViewFinanceReports)
                <div class="col-sm-6 col-xl-3">
                    <div class="card mini-stat" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);">
                        <div class="card-body">
                            <div class="label" style="color:#7c3aed;">Pending Payments</div>
                            <div class="value">{{ $reportStats['pending_payments'] }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if ($canViewAcademicReports)
            <div class="mb-3">
                <div class="section-title">Academic Reports</div>
            </div>
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-4">
                    <div class="card report-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h2 class="h5 mb-1">Student Reports</h2>
                                    <div class="small text-muted">Find students quickly.</div>
                                </div>
                                <span class="report-icon" style="background:#dbeafe; color:#2563eb;">
                                    <i class="bi bi-people"></i>
                                </span>
                            </div>
                            <ul class="report-points">
                                <li>Class wise</li>
                                <li>Batch wise</li>
                                <li>Teacher wise</li>
                            </ul>
                            <a href="{{ route('reports.students') }}" class="btn btn-outline-primary">Open</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="card report-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h2 class="h5 mb-1">Enrollment Reports</h2>
                                    <div class="small text-muted">Track batch movement.</div>
                                </div>
                                <span class="report-icon" style="background:#dcfce7; color:#16a34a;">
                                    <i class="bi bi-person-lines-fill"></i>
                                </span>
                            </div>
                            <ul class="report-points">
                                <li>Active students</li>
                                <li>Completed or withdrawn</li>
                                <li>Batch history</li>
                            </ul>
                            <a href="{{ route('reports.enrollments') }}" class="btn btn-outline-success">Open</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($canViewFinanceReports)
            <div class="mb-3">
                <div class="section-title">Finance Reports</div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-xl-4">
                    <div class="card report-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h2 class="h5 mb-1">Due Reports</h2>
                                    <div class="small text-muted">Follow up unpaid fees.</div>
                                </div>
                                <span class="report-icon" style="background:#fee2e2; color:#dc2626;">
                                    <i class="bi bi-exclamation-circle"></i>
                                </span>
                            </div>
                            <ul class="report-points">
                                <li>Month wise due</li>
                                <li>Student contact</li>
                                <li>Batch wise follow-up</li>
                            </ul>
                            <a href="{{ route('reports.dues') }}" class="btn btn-outline-danger">Open</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="card report-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h2 class="h5 mb-1">Collection Reports</h2>
                                    <div class="small text-muted">Check approved collections.</div>
                                </div>
                                <span class="report-icon" style="background:#ede9fe; color:#7c3aed;">
                                    <i class="bi bi-wallet2"></i>
                                </span>
                            </div>
                            <ul class="report-points">
                                <li>Daily totals</li>
                                <li>Batch wise</li>
                                <li>Collection history</li>
                            </ul>
                            <a href="{{ route('reports.collections') }}" class="btn btn-outline-secondary">Open</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="card report-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h2 class="h5 mb-1">Teacher Finance</h2>
                                    <div class="small text-muted">Review earnings and dues.</div>
                                </div>
                                <span class="report-icon" style="background:#cffafe; color:#0891b2;">
                                    <i class="bi bi-cash-coin"></i>
                                </span>
                            </div>
                            <ul class="report-points">
                                <li>Earnings</li>
                                <li>Settlements</li>
                                <li>Outstanding</li>
                            </ul>
                            <a href="{{ route('reports.teacher-finance') }}" class="btn btn-outline-info">Open</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="card report-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h2 class="h5 mb-1">Expense Reports</h2>
                                    <div class="small text-muted">See monthly expense records.</div>
                                </div>
                                <span class="report-icon" style="background:#ffedd5; color:#ea580c;">
                                    <i class="bi bi-receipt"></i>
                                </span>
                            </div>
                            <ul class="report-points">
                                <li>Common expenses</li>
                                <li>Teacher expenses</li>
                                <li>Monthly details</li>
                            </ul>
                            <a href="{{ route('reports.expenses') }}" class="btn btn-outline-warning">Open</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
