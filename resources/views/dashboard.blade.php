@extends('layouts.app')

@section('title', 'Dashboard')

@section('page_header')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ $dashboardData['roleLabel'] }} Dashboard</h1>
            <p class="text-secondary mb-0">
                {{ auth()->user()->isSuperAdmin()
                    ? 'Monitor tenants, users, and the current SaaS foundation from one place.'
                    : 'Review coaching operations, teacher readiness, and the next modules waiting to be connected.' }}
            </p>
        </div>
        @if (! empty($dashboardData['tenantStats']))
            <div class="d-flex flex-wrap gap-2">
                @foreach ($dashboardData['tenantStats'] as $stat)
                    <span class="soft-badge soft-primary">{{ $stat['label'] }}: {{ $stat['value'] }}</span>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@section('content')
    <div class="py-4">
        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="hero-panel h-100">
                    <h3>Coaching Center Management Backend</h3>
                    <p class="mb-4">
                        The backend shell is now aligned toward a practical admin dashboard. Teacher management is active, tenant-aware roles are working, and the search area is reserved for future student lookup by ID or name.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        @if (auth()->user()->isAdmin() || auth()->user()->isTeacher())
                            <a href="{{ route('teachers.index') }}" class="btn btn-light">
                                <i class="bi bi-person-workspace me-2"></i>Open Teacher Module
                            </a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-light">
                            <i class="bi bi-gear me-2"></i>Profile Settings
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="admin-card p-4 h-100">
                    <div class="page-section-title">System Notes</div>
                    <div class="list-group list-group-flush mt-3">
                        @foreach ($dashboardData['quickNotes'] as $note)
                            <div class="list-group-item px-0 bg-transparent text-secondary">
                                {{ $note }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            @foreach ($dashboardData['globalStats'] as $stat)
                @php
                    $iconMap = [
                        'emerald' => ['class' => 'bg-success-soft', 'icon' => 'bi bi-building-check'],
                        'amber' => ['class' => 'bg-warning-soft', 'icon' => 'bi bi-hourglass-split'],
                        'sky' => ['class' => 'bg-info-soft', 'icon' => 'bi bi-person-badge'],
                        'rose' => ['class' => 'bg-danger-soft', 'icon' => 'bi bi-people-fill'],
                        'violet' => ['class' => 'bg-primary-soft', 'icon' => 'bi bi-bar-chart-line'],
                    ];
                    $meta = $iconMap[$stat['tone']] ?? ['class' => 'bg-primary-soft', 'icon' => 'bi bi-grid'];
                @endphp
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="stat-card card">
                        <div class="card-body">
                            <div class="stat-icon {{ $meta['class'] }}">
                                <i class="{{ $meta['icon'] }}"></i>
                            </div>
                            <div class="stat-kicker">{{ $stat['label'] }}</div>
                            <div class="display-6 fw-bold text-dark">{{ $stat['value'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="admin-card h-100">
                    <div class="p-4 border-bottom">
                        <h5 class="fw-bold mb-1">Current Backend Readiness</h5>
                        <div class="text-secondary small">Modules and architecture prepared so far</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table module-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Area</th>
                                    <th>Status</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold">Tenant Foundation</td>
                                    <td><span class="soft-badge soft-success">Ready</span></td>
                                    <td>Shared-table multi-tenancy, tenant settings, and tenant onboarding are in place.</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Spatie Roles</td>
                                    <td><span class="soft-badge soft-success">Ready</span></td>
                                    <td>Tenant-aware role and permission structure is working for super admin, admin, and teacher.</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Teacher Module</td>
                                    <td><span class="soft-badge soft-success">Ready</span></td>
                                    <td>Teacher profiles, user linking, scope rules, and seeded sample records are available.</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Student Search</td>
                                    <td><span class="soft-badge soft-warning">Planned</span></td>
                                    <td>The topbar search is intentionally reserved for future student lookup by ID or by name.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="admin-card p-4 h-100">
                    <h5 class="fw-bold mb-1">Quick Actions</h5>
                    <div class="text-secondary small mb-4">Most relevant entry points based on current modules</div>

                    <div class="d-grid gap-3">
                        @if (auth()->user()->isAdmin() || auth()->user()->isTeacher())
                            <a href="{{ route('teachers.index') }}" class="quick-action">
                                <div class="quick-icon bg-info-soft">
                                    <i class="bi bi-person-workspace"></i>
                                </div>
                                <div>
                                    <p class="quick-title">Manage Teachers</p>
                                    <p class="quick-text">Open teacher profiles, update scope, and review linked user accounts.</p>
                                </div>
                            </a>
                        @endif

                        <a href="{{ route('profile.edit') }}" class="quick-action">
                            <div class="quick-icon bg-primary-soft">
                                <i class="bi bi-gear-fill"></i>
                            </div>
                            <div>
                                <p class="quick-title">Profile Settings</p>
                                <p class="quick-text">Update account details, password, and current user profile settings.</p>
                            </div>
                        </a>

                        @if ($dashboardData['teacherProfile'])
                            <div class="quick-action">
                                <div class="quick-icon bg-success-soft">
                                    <i class="bi bi-person-check-fill"></i>
                                </div>
                                <div>
                                    <p class="quick-title">Teacher Scope Active</p>
                                    <p class="quick-text">
                                        {{ $dashboardData['teacherProfile']->name }} can {{ $dashboardData['teacherProfile']->can_collect_fees ? 'collect fees' : 'not collect fees' }} and
                                        {{ $dashboardData['teacherProfile']->can_own_batches ? 'own batches' : 'not own batches' }}.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
