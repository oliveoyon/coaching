<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Coaching Management System'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --cms-sidebar-bg: #1f2937;
            --cms-sidebar-hover: #374151;
            --cms-accent: #0d6efd;
            --cms-body-bg: #f4f6f9;
            --cms-card-border: #e5e7eb;
        }

        body {
            font-family: "Instrument Sans", sans-serif;
            background: var(--cms-body-bg);
            color: #1f2937;
        }

        .admin-shell {
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 280px;
            min-width: 280px;
            max-width: 280px;
            flex: 0 0 280px;
            background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
        }

        .admin-sidebar .brand-link {
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            letter-spacing: .02em;
        }

        .admin-sidebar .nav-link {
            color: rgba(255, 255, 255, .78);
            border-radius: .75rem;
            padding: .72rem .9rem;
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: .35rem;
            font-size: .92rem;
            line-height: 1.2;
            white-space: nowrap;
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, .12);
        }

        .admin-sidebar .menu-section + .menu-section {
            margin-top: 1rem;
        }

        .admin-sidebar .menu-toggle {
            justify-content: space-between;
            font-weight: 600;
        }

        .admin-sidebar .menu-toggle > span {
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .admin-sidebar .submenu {
            padding-left: .75rem;
            border-left: 1px solid rgba(255, 255, 255, .12);
            margin-left: .5rem;
        }

        .admin-sidebar .submenu .nav-link {
            padding: .58rem .82rem;
            font-size: .88rem;
        }

        .admin-sidebar .submenu .nav-link span {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .admin-sidebar .menu-label {
            font-size: .72rem;
            letter-spacing: .08em;
        }

        .admin-topbar {
            position: sticky;
            top: 0;
            z-index: 1035;
            background: rgba(255, 255, 255, .9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(15, 23, 42, .08);
        }

        .admin-topbar .dropdown {
            position: relative;
            z-index: 1040;
        }

        .admin-topbar .dropdown-menu {
            z-index: 1050;
        }

        .admin-page {
            min-width: 0;
        }

        .topbar-student-search {
            min-width: 280px;
            max-width: 420px;
        }

        .page-card {
            border: 1px solid var(--cms-card-border);
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
        }

        .metric-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        }

        .table > :not(caption) > * > * {
            vertical-align: middle;
        }

        .content-wrapper {
            padding: 1.5rem;
        }

        @media (max-width: 991.98px) {
            .content-wrapper {
                padding: 1rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $isAcademicMenuOpen = request()->routeIs('admin.classes.*')
            || request()->routeIs('admin.subjects.*')
            || request()->routeIs('admin.teachers.*')
            || request()->routeIs('admin.batches.*');
        $isStudentMenuOpen = request()->routeIs('admin.students.*')
            || request()->routeIs('admin.enrollments.*')
            || request()->routeIs('admin.admission-links.*')
            || request()->routeIs('admin.admission-requests.*');
        $isFinanceMenuOpen = request()->routeIs('admin.payments.*')
            || request()->routeIs('admin.fee-types.*')
            || request()->routeIs('admin.batch-fees.*')
            || request()->routeIs('admin.distributions.*')
            || request()->routeIs('admin.teacher-settlements.*')
            || request()->routeIs('admin.expenses.*')
            || request()->routeIs('teacher.earnings.*')
            || request()->routeIs('teacher.settlements.*');
        $isAdminMenuOpen = request()->routeIs('admin.users.*')
            || request()->routeIs('admin.rbac-demo');
        $isReportMenuOpen = request()->routeIs('reports.*');
    @endphp
    <div class="admin-shell d-flex">
        <aside class="admin-sidebar text-white d-none d-lg-flex flex-column p-3 p-xl-4">
            <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom border-secondary-subtle">
                <span class="bg-primary rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                    <i class="bi bi-mortarboard-fill text-white"></i>
                </span>
                <a href="{{ route('dashboard') }}" class="brand-link fs-5">Coaching CMS</a>
            </div>

            <div class="small text-uppercase text-white-50 fw-semibold mb-3">Navigation</div>

            <nav class="nav flex-column">
                <div class="menu-section">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                @if (auth()->user()->hasAnyRole(['Super Admin', 'Admin']) || auth()->user()->can('manage batches') || auth()->user()->hasRole('Teacher'))
                    <div class="menu-section">
                        <div class="menu-label text-uppercase text-white-50 fw-semibold mb-2">Academic</div>
                        <a class="nav-link menu-toggle {{ $isAcademicMenuOpen ? 'active' : '' }}" data-bs-toggle="collapse" href="#desktopAcademicMenu" role="button" aria-expanded="{{ $isAcademicMenuOpen ? 'true' : 'false' }}" aria-controls="desktopAcademicMenu">
                            <span class="d-flex align-items-center gap-2">
                                <i class="bi bi-building"></i>
                                <span>Academic Setup</span>
                            </span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>
                        <div class="collapse submenu {{ $isAcademicMenuOpen ? 'show' : '' }}" id="desktopAcademicMenu">
                            @if (auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
                                <a href="{{ route('admin.classes.index') }}" class="nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                                    <i class="bi bi-easel"></i>
                                    <span>Classes</span>
                                </a>
                                <a href="{{ route('admin.subjects.index') }}" class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                                    <i class="bi bi-journal-text"></i>
                                    <span>Subjects</span>
                                </a>
                                <a href="{{ route('admin.teachers.index') }}" class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                                    <i class="bi bi-person-workspace"></i>
                                    <span>Teachers</span>
                                </a>
                            @endif
                            <a href="{{ route('admin.batches.index') }}" class="nav-link {{ request()->routeIs('admin.batches.*') ? 'active' : '' }}">
                                <i class="bi bi-collection"></i>
                                <span>{{ auth()->user()->hasRole('Teacher') && !auth()->user()->can('manage batches') ? 'My Batches' : 'Batch Management' }}</span>
                            </a>
                        </div>
                    </div>
                @endif

                @if (auth()->user()->can('manage students') || auth()->user()->can('manage enrollments') || auth()->user()->hasRole('Teacher'))
                    <div class="menu-section">
                        <div class="menu-label text-uppercase text-white-50 fw-semibold mb-2">Students</div>
                        <a class="nav-link menu-toggle {{ $isStudentMenuOpen ? 'active' : '' }}" data-bs-toggle="collapse" href="#desktopStudentMenu" role="button" aria-expanded="{{ $isStudentMenuOpen ? 'true' : 'false' }}" aria-controls="desktopStudentMenu">
                            <span class="d-flex align-items-center gap-2">
                                <i class="bi bi-mortarboard"></i>
                                <span>Students & Enrollments</span>
                            </span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>
                        <div class="collapse submenu {{ $isStudentMenuOpen ? 'show' : '' }}" id="desktopStudentMenu">
                            @can('manage students')
                                <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                                    <i class="bi bi-person-vcard"></i>
                                    <span>Student Management</span>
                                </a>
                            @endcan
                            <a href="{{ route('admin.enrollments.index') }}" class="nav-link {{ request()->routeIs('admin.enrollments.*') ? 'active' : '' }}">
                                <i class="bi bi-person-lines-fill"></i>
                                <span>{{ auth()->user()->hasRole('Teacher') && !auth()->user()->can('manage enrollments') ? 'My Students' : 'Enrollment Management' }}</span>
                            </a>
                            @if (auth()->user()->can('manage enrollments'))
                                <a href="{{ route('admin.admission-links.index') }}" class="nav-link {{ request()->routeIs('admin.admission-links.*') || request()->routeIs('admin.admission-requests.*') ? 'active' : '' }}">
                                    <i class="bi bi-link-45deg"></i>
                                    <span>Public Admissions</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                @if (auth()->user()->canany(['collect payments', 'approve payments', 'manage fee setup', 'settle teacher payments']) || auth()->user()->can('manage expenses'))
                    <div class="menu-section">
                        <div class="menu-label text-uppercase text-white-50 fw-semibold mb-2">Finance</div>
                        <a class="nav-link menu-toggle {{ $isFinanceMenuOpen ? 'active' : '' }}" data-bs-toggle="collapse" href="#desktopFinanceMenu" role="button" aria-expanded="{{ $isFinanceMenuOpen ? 'true' : 'false' }}" aria-controls="desktopFinanceMenu">
                            <span class="d-flex align-items-center gap-2">
                                <i class="bi bi-cash-stack"></i>
                                <span>Finance & Collection</span>
                            </span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>
                        <div class="collapse submenu {{ $isFinanceMenuOpen ? 'show' : '' }}" id="desktopFinanceMenu">
                            @canany(['collect payments', 'approve payments'])
                                <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                                    <i class="bi bi-wallet2"></i>
                                    <span>Payment Collection</span>
                                </a>
                            @endcanany
                            @can('manage fee setup')
                                <a href="{{ route('admin.fee-types.index') }}" class="nav-link {{ request()->routeIs('admin.fee-types.*') || request()->routeIs('admin.batch-fees.*') ? 'active' : '' }}">
                                    <i class="bi bi-receipt-cutoff"></i>
                                    <span>Fee Setup</span>
                                </a>
                            @endcan
                            @canany(['approve payments', 'settle teacher payments'])
                                <a href="{{ route('admin.distributions.index') }}" class="nav-link {{ request()->routeIs('admin.distributions.*') ? 'active' : '' }}">
                                    <i class="bi bi-cash-coin"></i>
                                    <span>Distribution History</span>
                                </a>
                            @endcanany
                            @can('settle teacher payments')
                                <a href="{{ route('admin.teacher-settlements.index') }}" class="nav-link {{ request()->routeIs('admin.teacher-settlements.*') ? 'active' : '' }}">
                                    <i class="bi bi-bank"></i>
                                    <span>Teacher Settlements</span>
                                </a>
                            @endcan
                            @can('manage expenses')
                                <a href="{{ route('admin.expenses.index') }}" class="nav-link {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
                                    <i class="bi bi-receipt"></i>
                                    <span>Expense Management</span>
                                </a>
                            @endcan
                        </div>
                    </div>
                @endif

                @if (auth()->user()->hasRole('Teacher'))
                    <div class="menu-section">
                        <div class="menu-label text-uppercase text-white-50 fw-semibold mb-2">Finance</div>
                        <a href="{{ route('teacher.earnings.index') }}" class="nav-link {{ request()->routeIs('teacher.earnings.*') ? 'active' : '' }}">
                            <i class="bi bi-cash-coin"></i>
                            <span>My Earnings</span>
                        </a>
                        <a href="{{ route('teacher.settlements.index') }}" class="nav-link {{ request()->routeIs('teacher.settlements.*') ? 'active' : '' }}">
                            <i class="bi bi-bank"></i>
                            <span>My Settlements</span>
                        </a>
                    </div>
                @endif

                @if (auth()->user()->can('manage users') || auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
                    <div class="menu-section">
                        <div class="menu-label text-uppercase text-white-50 fw-semibold mb-2">Administration</div>
                        <a class="nav-link menu-toggle {{ $isAdminMenuOpen ? 'active' : '' }}" data-bs-toggle="collapse" href="#desktopAdminMenu" role="button" aria-expanded="{{ $isAdminMenuOpen ? 'true' : 'false' }}" aria-controls="desktopAdminMenu">
                            <span class="d-flex align-items-center gap-2">
                                <i class="bi bi-shield-lock"></i>
                                <span>Access & Settings</span>
                            </span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>
                        <div class="collapse submenu {{ $isAdminMenuOpen ? 'show' : '' }}" id="desktopAdminMenu">
                            @can('manage users')
                                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                    <i class="bi bi-people"></i>
                                    <span>User Management</span>
                                </a>
                            @endcan
                            @if (auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
                                <a href="{{ route('admin.rbac-demo') }}" class="nav-link {{ request()->routeIs('admin.rbac-demo') ? 'active' : '' }}">
                                    <i class="bi bi-shield-check"></i>
                                    <span>RBAC Demo</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                @if (auth()->user()->can('view reports') || auth()->user()->hasRole('Teacher'))
                    <div class="menu-section">
                        <div class="menu-label text-uppercase text-white-50 fw-semibold mb-2">Reports</div>
                        <a class="nav-link menu-toggle {{ $isReportMenuOpen ? 'active' : '' }}" data-bs-toggle="collapse" href="#desktopReportMenu" role="button" aria-expanded="{{ $isReportMenuOpen ? 'true' : 'false' }}" aria-controls="desktopReportMenu">
                            <span class="d-flex align-items-center gap-2">
                                <i class="bi bi-bar-chart"></i>
                                <span>Report Center</span>
                            </span>
                            <i class="bi bi-chevron-down small"></i>
                        </a>
                        <div class="collapse submenu {{ $isReportMenuOpen ? 'show' : '' }}" id="desktopReportMenu">
                            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                                <i class="bi bi-grid-1x2"></i>
                                <span>Report Hub</span>
                            </a>
                            @if (auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Teacher']))
                                <a href="{{ route('reports.students') }}" class="nav-link {{ request()->routeIs('reports.students') ? 'active' : '' }}">
                                    <i class="bi bi-people"></i>
                                    <span>Student Reports</span>
                                </a>
                                <a href="{{ route('reports.enrollments') }}" class="nav-link {{ request()->routeIs('reports.enrollments') ? 'active' : '' }}">
                                    <i class="bi bi-person-lines-fill"></i>
                                    <span>Enrollment Reports</span>
                                </a>
                            @endif
                            <a href="{{ route('reports.dues') }}" class="nav-link {{ request()->routeIs('reports.dues') ? 'active' : '' }}">
                                <i class="bi bi-exclamation-circle"></i>
                                <span>Due Reports</span>
                            </a>
                            <a href="{{ route('reports.collections') }}" class="nav-link {{ request()->routeIs('reports.collections') ? 'active' : '' }}">
                                <i class="bi bi-wallet2"></i>
                                <span>Collection Reports</span>
                            </a>
                            <a href="{{ route('reports.teacher-finance') }}" class="nav-link {{ request()->routeIs('reports.teacher-finance') ? 'active' : '' }}">
                                <i class="bi bi-cash-coin"></i>
                                <span>Teacher Finance</span>
                            </a>
                            <a href="{{ route('reports.expenses') }}" class="nav-link {{ request()->routeIs('reports.expenses') ? 'active' : '' }}">
                                <i class="bi bi-receipt"></i>
                                <span>Expense Reports</span>
                            </a>
                        </div>
                    </div>
                @endif
            </nav>

            <div class="mt-auto pt-4 border-top border-secondary-subtle">
                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                <div class="small text-white-50">{{ auth()->user()->getRoleNames()->implode(', ') ?: 'No role assigned' }}</div>
            </div>
        </aside>

        <div class="admin-page flex-grow-1 d-flex flex-column">
            <header class="admin-topbar">
                <div class="content-wrapper py-3 d-flex align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                            <i class="bi bi-list"></i>
                        </button>
                        <div>
                            <h1 class="h4 mb-0">@yield('page-title', 'Dashboard')</h1>
                            @hasSection('page-subtitle')
                                <div class="text-muted small">@yield('page-subtitle')</div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2">
                        @if (auth()->user()->hasRole('Teacher') || auth()->user()->canany(['manage students', 'manage enrollments', 'collect payments']))
                            <form method="GET" action="{{ route('admin.student-lookup.index') }}" class="topbar-student-search">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                    <input type="text" name="student_search" value="{{ request('student_search') }}" class="form-control" placeholder="Find student by code, name, or phone">
                                </div>
                            </form>
                        @endif

                        <div class="dropdown">
                            <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ auth()->user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <main class="content-wrapper flex-grow-1">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title" id="mobileSidebarLabel">Coaching CMS</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <nav class="nav flex-column gap-2">
                <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard*') ? 'active bg-white bg-opacity-10' : '' }}">Dashboard</a>

                @if (auth()->user()->hasAnyRole(['Super Admin', 'Admin']) || auth()->user()->can('manage batches') || auth()->user()->hasRole('Teacher'))
                    <button class="btn btn-link nav-link text-white text-decoration-none text-start d-flex justify-content-between {{ $isAcademicMenuOpen ? 'active bg-white bg-opacity-10' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#mobileAcademicMenu" aria-expanded="{{ $isAcademicMenuOpen ? 'true' : 'false' }}">
                        <span>Academic Setup</span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <div class="collapse {{ $isAcademicMenuOpen ? 'show' : '' }}" id="mobileAcademicMenu">
                        <div class="ps-3 d-flex flex-column gap-2">
                            @if (auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
                                <a href="{{ route('admin.classes.index') }}" class="nav-link text-white {{ request()->routeIs('admin.classes.*') ? 'active bg-white bg-opacity-10' : '' }}">Classes</a>
                                <a href="{{ route('admin.subjects.index') }}" class="nav-link text-white {{ request()->routeIs('admin.subjects.*') ? 'active bg-white bg-opacity-10' : '' }}">Subjects</a>
                                <a href="{{ route('admin.teachers.index') }}" class="nav-link text-white {{ request()->routeIs('admin.teachers.*') ? 'active bg-white bg-opacity-10' : '' }}">Teachers</a>
                            @endif
                            <a href="{{ route('admin.batches.index') }}" class="nav-link text-white {{ request()->routeIs('admin.batches.*') ? 'active bg-white bg-opacity-10' : '' }}">
                                {{ auth()->user()->hasRole('Teacher') && !auth()->user()->can('manage batches') ? 'My Batches' : 'Batch Management' }}
                            </a>
                        </div>
                    </div>
                @endif

                @if (auth()->user()->can('manage students') || auth()->user()->can('manage enrollments') || auth()->user()->hasRole('Teacher'))
                    <button class="btn btn-link nav-link text-white text-decoration-none text-start d-flex justify-content-between {{ $isStudentMenuOpen ? 'active bg-white bg-opacity-10' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#mobileStudentMenu" aria-expanded="{{ $isStudentMenuOpen ? 'true' : 'false' }}">
                        <span>Students & Enrollments</span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <div class="collapse {{ $isStudentMenuOpen ? 'show' : '' }}" id="mobileStudentMenu">
                        <div class="ps-3 d-flex flex-column gap-2">
                            @can('manage students')
                                <a href="{{ route('admin.students.index') }}" class="nav-link text-white {{ request()->routeIs('admin.students.*') ? 'active bg-white bg-opacity-10' : '' }}">Student Management</a>
                            @endcan
                            <a href="{{ route('admin.enrollments.index') }}" class="nav-link text-white {{ request()->routeIs('admin.enrollments.*') ? 'active bg-white bg-opacity-10' : '' }}">
                                {{ auth()->user()->hasRole('Teacher') && !auth()->user()->can('manage enrollments') ? 'My Students' : 'Enrollment Management' }}
                            </a>
                            @if (auth()->user()->can('manage enrollments'))
                                <a href="{{ route('admin.admission-links.index') }}" class="nav-link text-white {{ request()->routeIs('admin.admission-links.*') || request()->routeIs('admin.admission-requests.*') ? 'active bg-white bg-opacity-10' : '' }}">Public Admissions</a>
                            @endif
                        </div>
                    </div>
                @endif

                @if (auth()->user()->canany(['collect payments', 'approve payments', 'manage fee setup', 'settle teacher payments']) || auth()->user()->can('manage expenses'))
                    <button class="btn btn-link nav-link text-white text-decoration-none text-start d-flex justify-content-between {{ $isFinanceMenuOpen ? 'active bg-white bg-opacity-10' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#mobileFinanceMenu" aria-expanded="{{ $isFinanceMenuOpen ? 'true' : 'false' }}">
                        <span>Finance & Collection</span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <div class="collapse {{ $isFinanceMenuOpen ? 'show' : '' }}" id="mobileFinanceMenu">
                        <div class="ps-3 d-flex flex-column gap-2">
                            @canany(['collect payments', 'approve payments'])
                                <a href="{{ route('admin.payments.index') }}" class="nav-link text-white {{ request()->routeIs('admin.payments.*') ? 'active bg-white bg-opacity-10' : '' }}">Payment Collection</a>
                            @endcanany
                            @can('manage fee setup')
                                <a href="{{ route('admin.fee-types.index') }}" class="nav-link text-white {{ request()->routeIs('admin.fee-types.*') || request()->routeIs('admin.batch-fees.*') ? 'active bg-white bg-opacity-10' : '' }}">Fee Setup</a>
                            @endcan
                            @canany(['approve payments', 'settle teacher payments'])
                                <a href="{{ route('admin.distributions.index') }}" class="nav-link text-white {{ request()->routeIs('admin.distributions.*') ? 'active bg-white bg-opacity-10' : '' }}">Distribution History</a>
                            @endcanany
                            @can('settle teacher payments')
                                <a href="{{ route('admin.teacher-settlements.index') }}" class="nav-link text-white {{ request()->routeIs('admin.teacher-settlements.*') ? 'active bg-white bg-opacity-10' : '' }}">Teacher Settlements</a>
                            @endcan
                            @can('manage expenses')
                                <a href="{{ route('admin.expenses.index') }}" class="nav-link text-white {{ request()->routeIs('admin.expenses.*') ? 'active bg-white bg-opacity-10' : '' }}">Expense Management</a>
                            @endcan
                        </div>
                    </div>
                @endif

                @if (auth()->user()->hasRole('Teacher'))
                    <a href="{{ route('teacher.earnings.index') }}" class="nav-link text-white {{ request()->routeIs('teacher.earnings.*') ? 'active bg-white bg-opacity-10' : '' }}">My Earnings</a>
                    <a href="{{ route('teacher.settlements.index') }}" class="nav-link text-white {{ request()->routeIs('teacher.settlements.*') ? 'active bg-white bg-opacity-10' : '' }}">My Settlements</a>
                @endif

                @if (auth()->user()->can('manage users') || auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
                    <button class="btn btn-link nav-link text-white text-decoration-none text-start d-flex justify-content-between {{ $isAdminMenuOpen ? 'active bg-white bg-opacity-10' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#mobileAdminMenu" aria-expanded="{{ $isAdminMenuOpen ? 'true' : 'false' }}">
                        <span>Access & Settings</span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <div class="collapse {{ $isAdminMenuOpen ? 'show' : '' }}" id="mobileAdminMenu">
                        <div class="ps-3 d-flex flex-column gap-2">
                            @can('manage users')
                                <a href="{{ route('admin.users.index') }}" class="nav-link text-white {{ request()->routeIs('admin.users.*') ? 'active bg-white bg-opacity-10' : '' }}">User Management</a>
                            @endcan
                            @if (auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
                                <a href="{{ route('admin.rbac-demo') }}" class="nav-link text-white {{ request()->routeIs('admin.rbac-demo') ? 'active bg-white bg-opacity-10' : '' }}">RBAC Demo</a>
                            @endif
                        </div>
                    </div>
                @endif

                @if (auth()->user()->can('view reports') || auth()->user()->hasRole('Teacher'))
                    <button class="btn btn-link nav-link text-white text-decoration-none text-start d-flex justify-content-between {{ $isReportMenuOpen ? 'active bg-white bg-opacity-10' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#mobileReportMenu" aria-expanded="{{ $isReportMenuOpen ? 'true' : 'false' }}">
                        <span>Report Center</span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <div class="collapse {{ $isReportMenuOpen ? 'show' : '' }}" id="mobileReportMenu">
                        <div class="ps-3 d-flex flex-column gap-2">
                            <a href="{{ route('reports.index') }}" class="nav-link text-white {{ request()->routeIs('reports.index') ? 'active bg-white bg-opacity-10' : '' }}">Report Hub</a>
                            @if (auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Teacher']))
                                <a href="{{ route('reports.students') }}" class="nav-link text-white {{ request()->routeIs('reports.students') ? 'active bg-white bg-opacity-10' : '' }}">Student Reports</a>
                                <a href="{{ route('reports.enrollments') }}" class="nav-link text-white {{ request()->routeIs('reports.enrollments') ? 'active bg-white bg-opacity-10' : '' }}">Enrollment Reports</a>
                            @endif
                            <a href="{{ route('reports.dues') }}" class="nav-link text-white {{ request()->routeIs('reports.dues') ? 'active bg-white bg-opacity-10' : '' }}">Due Reports</a>
                            <a href="{{ route('reports.collections') }}" class="nav-link text-white {{ request()->routeIs('reports.collections') ? 'active bg-white bg-opacity-10' : '' }}">Collection Reports</a>
                            <a href="{{ route('reports.teacher-finance') }}" class="nav-link text-white {{ request()->routeIs('reports.teacher-finance') ? 'active bg-white bg-opacity-10' : '' }}">Teacher Finance</a>
                            <a href="{{ route('reports.expenses') }}" class="nav-link text-white {{ request()->routeIs('reports.expenses') ? 'active bg-white bg-opacity-10' : '' }}">Expense Reports</a>
                        </div>
                    </div>
                @endif
            </nav>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
