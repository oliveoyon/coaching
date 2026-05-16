@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', $dashboardRole . ' Dashboard')
@section('page-subtitle', $dashboardSubtitle)

@push('styles')
    <style>
        .dashboard-stat-card {
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            background: #fff;
        }

        .dashboard-stat-label {
            font-size: .76rem;
            color: #64748b;
            margin-bottom: .32rem;
        }

        .dashboard-stat-value {
            font-size: 1.28rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .dashboard-quick-link {
            min-width: 124px;
        }
    </style>
@endpush

@section('content')
    <div class="row g-3 mb-3">
        @foreach ($summaryCards as $card)
            <div class="col-sm-6 col-xl-3">
                <div class="card dashboard-stat-card h-100">
                    <div class="card-body">
                        <div class="dashboard-stat-label">{{ $card['label'] }}</div>
                        <div class="dashboard-stat-value text-{{ $card['tone'] }}">{{ $card['value'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card page-card mb-3">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">Quick Access</h2>
                    <div class="text-muted small">Open the sections you use most.</div>
                </div>
                <div class="small text-muted">
                    {{ $user->name }} | {{ $user->getRoleNames()->implode(', ') }}
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @foreach ($quickLinks as $link)
                    <a href="{{ $link['route'] }}" class="btn btn-{{ $link['style'] }} dashboard-quick-link">{{ $link['label'] }}</a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
