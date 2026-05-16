<?php

namespace App\Http\Controllers;

use App\Models\AdmissionRequest;
use App\Models\AttendanceSession;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Student;
use App\Models\TeacherSettlement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Redirect authenticated users to their role dashboard.
     */
    public function redirect(Request $request): RedirectResponse
    {
        return redirect()->route($this->dashboardRouteName($request));
    }

    /**
     * Display the Super Admin dashboard.
     */
    public function superAdmin(Request $request): View
    {
        return $this->renderDashboard($request, 'Super Admin', 'Full system overview');
    }

    /**
     * Display the Admin dashboard.
     */
    public function admin(Request $request): View
    {
        return $this->renderDashboard($request, 'Admin', 'Daily operations overview');
    }

    /**
     * Display the Teacher dashboard.
     */
    public function teacher(Request $request): View
    {
        return $this->renderDashboard($request, 'Teacher', 'Own batches and daily work');
    }

    /**
     * Display the Accounts dashboard.
     */
    public function accounts(Request $request): View
    {
        return $this->renderDashboard($request, 'Accounts', 'Collections and finance overview');
    }

    /**
     * Resolve the named dashboard route by role priority.
     */
    public function dashboardRouteName(Request $request): string
    {
        $user = $request->user();

        return match (true) {
            $user->hasRole('Super Admin') => 'dashboard.super-admin',
            $user->hasRole('Admin') => 'dashboard.admin',
            $user->hasRole('Teacher') => 'dashboard.teacher',
            $user->hasRole('Accounts') => 'dashboard.accounts',
            default => 'dashboard.admin',
        };
    }

    /**
     * Render the shared dashboard view.
     */
    protected function renderDashboard(Request $request, string $dashboardRole, string $dashboardSubtitle): View
    {
        $user = $request->user();

        return view('dashboard', [
            'dashboardRole' => $dashboardRole,
            'dashboardSubtitle' => $dashboardSubtitle,
            'user' => $user,
            'summaryCards' => $this->summaryCards($request),
            'quickLinks' => $this->quickLinks($request),
        ]);
    }

    /**
     * Build summary cards by role.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function summaryCards(Request $request): array
    {
        $user = $request->user();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        if ($user->hasRole('Teacher')) {
            $teacher = $user->teacherProfile;
            $batchIds = $teacher?->batches()->pluck('batches.id')->all() ?? [];

            return [
                ['label' => 'My Batches', 'value' => count($batchIds), 'tone' => 'primary'],
                ['label' => 'Active Students', 'value' => Enrollment::query()->whereIn('batch_id', $batchIds)->where('status', 'active')->count(), 'tone' => 'success'],
                ['label' => 'Today Attendance', 'value' => AttendanceSession::query()->whereIn('batch_id', $batchIds)->whereDate('attendance_date', today())->count(), 'tone' => 'warning'],
                ['label' => 'This Month Collection', 'value' => number_format((float) Payment::query()
                    ->where('collected_by', $user->id)
                    ->where('status', 'approved')
                    ->whereBetween('payment_date', [$monthStart, $monthEnd])
                    ->sum('amount'), 2), 'tone' => 'info'],
            ];
        }

        if ($user->hasRole('Accounts')) {
            return [
                ['label' => 'This Month Collection', 'value' => number_format((float) Payment::query()->where('status', 'approved')->whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount'), 2), 'tone' => 'primary'],
                ['label' => 'Pending Payments', 'value' => Payment::query()->where('status', 'pending')->count(), 'tone' => 'warning'],
                ['label' => 'This Month Expenses', 'value' => number_format((float) Expense::query()->whereBetween('expense_date', [$monthStart, $monthEnd])->sum('amount'), 2), 'tone' => 'danger'],
                ['label' => 'Teacher Settlements', 'value' => number_format((float) TeacherSettlement::query()->whereBetween('settlement_date', [$monthStart, $monthEnd])->sum('amount'), 2), 'tone' => 'success'],
            ];
        }

        return [
            ['label' => 'Students', 'value' => Student::query()->count(), 'tone' => 'primary'],
            ['label' => 'Active Enrollments', 'value' => Enrollment::query()->where('status', 'active')->count(), 'tone' => 'success'],
            ['label' => 'Active Batches', 'value' => Batch::query()->where('status', 'active')->count(), 'tone' => 'warning'],
            ['label' => 'Pending Admissions', 'value' => AdmissionRequest::query()->where('status', 'pending')->count(), 'tone' => 'info'],
        ];
    }

    /**
     * Build quick links by role.
     *
     * @return array<int, array<string, string>>
     */
    protected function quickLinks(Request $request): array
    {
        $user = $request->user();
        $links = [];

        if ($user->can('manage users')) {
            $links[] = ['label' => 'Users', 'route' => route('admin.users.index'), 'style' => 'primary'];
        }

        if ($user->can('manage students')) {
            $links[] = ['label' => 'Students', 'route' => route('admin.students.index'), 'style' => 'outline-primary'];
        }

        if ($user->can('manage enrollments')) {
            $links[] = ['label' => 'Enrollments', 'route' => route('admin.enrollments.index'), 'style' => 'outline-primary'];
            $links[] = ['label' => 'Promotion Center', 'route' => route('admin.enrollments.promote'), 'style' => 'outline-secondary'];
            $links[] = ['label' => 'Admission Requests', 'route' => route('admin.admission-requests.index'), 'style' => 'outline-secondary'];
        }

        if ($user->can('manage batches')) {
            $links[] = ['label' => 'Batches', 'route' => route('admin.batches.index'), 'style' => 'outline-primary'];
        }

        if ($user->can('manage fee setup')) {
            $links[] = ['label' => 'Batch Fee Setup', 'route' => route('admin.batch-fees.directory'), 'style' => 'outline-secondary'];
        }

        if ($user->can('collect payments') || $user->can('approve payments')) {
            $links[] = ['label' => 'Payments', 'route' => route('admin.payments.index'), 'style' => 'outline-primary'];
        }

        if ($user->can('manage attendance') || $user->hasRole('Teacher')) {
            $links[] = ['label' => 'Attendance', 'route' => route('admin.attendance.index'), 'style' => 'outline-primary'];
        }

        if ($user->can('view reports') || $user->hasRole('Teacher')) {
            $links[] = ['label' => 'Reports', 'route' => route('reports.index'), 'style' => 'outline-secondary'];
        }

        return $links;
    }

}
