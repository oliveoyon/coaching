<?php

namespace App\Http\Controllers;

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
        return $this->renderDashboard($request, 'Super Admin', 'Full system control and configuration access.');
    }

    /**
     * Display the Admin dashboard.
     */
    public function admin(Request $request): View
    {
        return $this->renderDashboard($request, 'Admin', 'Daily operations, batches, enrollments, and reporting.');
    }

    /**
     * Display the Teacher dashboard.
     */
    public function teacher(Request $request): View
    {
        return $this->renderDashboard($request, 'Teacher', 'Own batches, own students, and own collection visibility.');
    }

    /**
     * Display the Accounts dashboard.
     */
    public function accounts(Request $request): View
    {
        return $this->renderDashboard($request, 'Accounts', 'Payments, expenses, and finance-focused reporting.');
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
        return view('dashboard', [
            'dashboardRole' => $dashboardRole,
            'dashboardSubtitle' => $dashboardSubtitle,
            'user' => $request->user(),
        ]);
    }
}
