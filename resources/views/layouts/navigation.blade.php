@php
    $user = auth()->user();
    $tenant = $user?->tenant;
    $searchValue = request('q', '');
    $roleLabel = $user?->isSuperAdmin() ? 'Super Admin' : ($user?->isAdmin() ? 'Admin' : ($user?->isTeacher() ? 'Teacher' : 'User'));
@endphp

<div class="d-flex">
    <aside class="admin-sidebar d-none d-lg-flex flex-column position-fixed top-0 start-0 vh-100">
        <div class="sidebar-brand p-4">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                <div class="d-flex align-items-center gap-3">
                    <div class="brand-logo">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <div>
                        <p class="brand-title">{{ $tenant?->name ?? 'Platform Console' }}</p>
                        <p class="brand-subtitle">Coaching Admin Panel</p>
                    </div>
                </div>
            </a>

            <div class="tenant-mini">
                <div>
                    <div class="small">Signed in as</div>
                    <strong>{{ $roleLabel }}</strong>
                </div>
                @if ($tenant)
                    <span class="badge rounded-pill text-bg-light text-dark">{{ ucfirst($tenant->status) }}</span>
                @endif
            </div>
        </div>

        <div class="px-3 py-3">
            <div class="sidebar-section-title px-3 mb-2">Navigation</div>
            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                @if ($user->isAdmin() || $user->isTeacher())
                    <li class="nav-item">
                        <a href="{{ route('teachers.index') }}" class="nav-link {{ request()->routeIs('teachers.*') ? 'active' : '' }}">
                            <i class="bi bi-person-workspace"></i>
                            <span>Teachers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('students.index') }}" class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>
                            <span>Students</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('enrollments.index') }}" class="nav-link {{ request()->routeIs('enrollments.*') ? 'active' : '' }}">
                            <i class="bi bi-person-check"></i>
                            <span>Enrollments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('batches.index') }}" class="nav-link {{ request()->routeIs('batches.*') ? 'active' : '' }}">
                            <i class="bi bi-collection"></i>
                            <span>Batches</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('schedules.index') }}" class="nav-link {{ request()->routeIs('schedules.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar3-week"></i>
                            <span>Routine</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                            <i class="bi bi-cash-stack"></i>
                            <span>Payments</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check"></i>
                            <span>Attendance</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('dues.index') }}" class="nav-link {{ request()->routeIs('dues.*') ? 'active' : '' }}">
                            <i class="bi bi-journal-check"></i>
                            <span>Dues</span>
                        </a>
                    </li>
                    @if ($user->isAdmin())
                        <li class="nav-item">
                            <a href="{{ route('settings.edit') }}" class="nav-link {{ request()->routeIs('settings.*') || request()->routeIs('billing-settings.*') ? 'active' : '' }}">
                                <i class="bi bi-sliders"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('fee-heads.index') }}" class="nav-link {{ request()->routeIs('fee-heads.*') ? 'active' : '' }}">
                                <i class="bi bi-tag"></i>
                                <span>Fee Heads</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('fee-structures.index') }}" class="nav-link {{ request()->routeIs('fee-structures.*') ? 'active' : '' }}">
                                <i class="bi bi-diagram-3"></i>
                                <span>Fee Structures</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('student-fee-overrides.index') }}" class="nav-link {{ request()->routeIs('student-fee-overrides.*') ? 'active' : '' }}">
                                <i class="bi bi-person-gear"></i>
                                <span>Fee Overrides</span>
                            </a>
                        </li>
                    @endif
                @endif
            </ul>

            <div class="sidebar-section-title px-3 mt-4 mb-2">Upcoming</div>
            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <span class="nav-link disabled">
                        <i class="bi bi-journal-text"></i>
                        <span>Exams</span>
                    </span>
                </li>
            </ul>
        </div>

        <div class="mt-auto px-3 pb-4">
            <div class="px-3 small text-secondary mb-2">{{ $user->name }}</div>
            <a href="{{ route('profile.edit') }}" class="nav-link">
                <i class="bi bi-gear"></i>
                <span>Profile Settings</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="px-2 mt-2">
                @csrf
                <button type="submit" class="btn btn-outline-light w-100 rounded-4 py-2 fw-semibold">Log Out</button>
            </form>
        </div>
    </aside>

    <div class="flex-grow-1 admin-main-shell" style="min-width:0;">
        <nav class="px-3 px-lg-4 py-3">
            <div class="admin-topbar d-flex align-items-center justify-content-between gap-3 flex-wrap rounded-4 px-3 px-lg-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light d-lg-none rounded-4 shadow-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileAdminSidebar" aria-controls="mobileAdminSidebar">
                        <i class="bi bi-list fs-5"></i>
                    </button>

                    <div class="d-none d-lg-block position-relative" style="min-width: 360px;">
                        <form action="{{ route('dashboard') }}" method="GET">
                            <span class="quick-search-icon"><i class="bi bi-search"></i></span>
                            <input type="text" name="q" value="{{ $searchValue }}" class="form-control quick-search-input" placeholder="Search student by ID or name">
                        </form>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 ms-auto">
                    <button class="icon-btn d-none d-md-inline-flex align-items-center justify-content-center">
                        <i class="bi bi-bell"></i>
                        <span class="dot"></span>
                    </button>

                    <div class="profile-chip">
                        <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                        <div>
                            <p class="name">{{ $user->name }}</p>
                            <p class="role">{{ $roleLabel }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</div>

<div class="offcanvas offcanvas-start text-bg-dark border-0" tabindex="-1" id="mobileAdminSidebar" aria-labelledby="mobileAdminSidebarLabel">
    <div class="offcanvas-header border-bottom border-light border-opacity-10">
        <div>
            <div id="mobileAdminSidebarLabel" class="fw-bold">{{ $tenant?->name ?? 'Platform Console' }}</div>
            <div class="small text-light-emphasis">Coaching Admin Panel</div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <form action="{{ route('dashboard') }}" method="GET" class="mb-4">
            <input type="text" name="q" value="{{ $searchValue }}" class="form-control rounded-4" placeholder="Search student by ID or name">
        </form>

        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : 'text-light' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            @if ($user->isAdmin() || $user->isTeacher())
                <li class="nav-item">
                    <a href="{{ route('teachers.index') }}" class="nav-link {{ request()->routeIs('teachers.*') ? 'active' : 'text-light' }}">
                        <i class="bi bi-person-workspace me-2"></i> Teachers
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('students.index') }}" class="nav-link {{ request()->routeIs('students.*') ? 'active' : 'text-light' }}">
                        <i class="bi bi-people me-2"></i> Students
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('enrollments.index') }}" class="nav-link {{ request()->routeIs('enrollments.*') ? 'active' : 'text-light' }}">
                        <i class="bi bi-person-check me-2"></i> Enrollments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('batches.index') }}" class="nav-link {{ request()->routeIs('batches.*') ? 'active' : 'text-light' }}">
                        <i class="bi bi-collection me-2"></i> Batches
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('schedules.index') }}" class="nav-link {{ request()->routeIs('schedules.*') ? 'active' : 'text-light' }}">
                        <i class="bi bi-calendar3-week me-2"></i> Routine
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : 'text-light' }}">
                        <i class="bi bi-cash-stack me-2"></i> Payments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : 'text-light' }}">
                        <i class="bi bi-calendar-check me-2"></i> Attendance
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('dues.index') }}" class="nav-link {{ request()->routeIs('dues.*') ? 'active' : 'text-light' }}">
                        <i class="bi bi-journal-check me-2"></i> Dues
                    </a>
                </li>
                @if ($user->isAdmin())
                    <li class="nav-item">
                        <a href="{{ route('settings.edit') }}" class="nav-link {{ request()->routeIs('settings.*') || request()->routeIs('billing-settings.*') ? 'active' : 'text-light' }}">
                            <i class="bi bi-sliders me-2"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('fee-heads.index') }}" class="nav-link {{ request()->routeIs('fee-heads.*') ? 'active' : 'text-light' }}">
                            <i class="bi bi-tag me-2"></i> Fee Heads
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('fee-structures.index') }}" class="nav-link {{ request()->routeIs('fee-structures.*') ? 'active' : 'text-light' }}">
                            <i class="bi bi-diagram-3 me-2"></i> Fee Structures
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('student-fee-overrides.index') }}" class="nav-link {{ request()->routeIs('student-fee-overrides.*') ? 'active' : 'text-light' }}">
                            <i class="bi bi-person-gear me-2"></i> Fee Overrides
                        </a>
                    </li>
                @endif
            @endif
            <li class="nav-item">
                <a href="{{ route('profile.edit') }}" class="nav-link text-light">
                    <i class="bi bi-gear me-2"></i> Profile Settings
                </a>
            </li>
        </ul>

        <form method="POST" action="{{ route('logout') }}" class="mt-auto">
            @csrf
            <button type="submit" class="btn btn-outline-light w-100 rounded-4 py-2 fw-semibold">Log Out</button>
        </form>
    </div>
</div>
