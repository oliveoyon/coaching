<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $search = trim((string) $request->string('q'));

        return view('dashboard', [
            'dashboardData' => [
                'roleLabel' => $this->roleLabel($user),
                'search' => $search,
                'globalStats' => $this->globalStats($user),
                'tenantStats' => $this->tenantStats($user),
                'teacherProfile' => $user->teacher,
                'quickNotes' => $this->quickNotes($user, $search),
            ],
        ]);
    }

    /**
     * @return array<int, array<string, string|int>>
     */
    protected function globalStats(User $user): array
    {
        if ($user->isSuperAdmin()) {
            return [
                [
                    'label' => 'Active Tenants',
                    'value' => Tenant::query()->where('status', Tenant::STATUS_ACTIVE)->count(),
                    'tone' => 'emerald',
                ],
                [
                    'label' => 'Trial Tenants',
                    'value' => Tenant::query()->where('status', Tenant::STATUS_TRIAL)->count(),
                    'tone' => 'amber',
                ],
                [
                    'label' => 'Teacher Profiles',
                    'value' => Teacher::query()->count(),
                    'tone' => 'sky',
                ],
                [
                    'label' => 'System Users',
                    'value' => User::query()->count(),
                    'tone' => 'rose',
                ],
            ];
        }

        return [
            [
                'label' => 'Tenant Users',
                'value' => User::query()->where('tenant_id', $user->tenant_id)->count(),
                'tone' => 'sky',
            ],
            [
                'label' => 'Teacher Profiles',
                'value' => Teacher::query()->where('tenant_id', $user->tenant_id)->count(),
                'tone' => 'emerald',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string|int>>
     */
    protected function tenantStats(User $user): array
    {
        if ($user->isSuperAdmin() || $user->tenant === null) {
            return [];
        }

        $tenant = $user->tenant;

        return [
            [
                'label' => 'Tenant Status',
                'value' => ucfirst($tenant->status),
                'tone' => $tenant->status === Tenant::STATUS_ACTIVE ? 'emerald' : 'amber',
            ],
            [
                'label' => 'Billing Model',
                'value' => str($tenant->billing_model)->replace('_', ' ')->title()->toString(),
                'tone' => 'sky',
            ],
            [
                'label' => 'Max Teachers',
                'value' => $tenant->max_teachers ?? 'Open',
                'tone' => 'violet',
            ],
            [
                'label' => 'Max Students',
                'value' => $tenant->max_students ?? 'Open',
                'tone' => 'rose',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function quickNotes(User $user, string $search): array
    {
        $notes = [
            'The top search bar is reserved for future student lookup by ID or name.',
            'Teacher ownership is now ready for future `owner_teacher_id` usage in batches and payments.',
        ];

        if ($user->isSuperAdmin()) {
            $notes[] = 'Platform controls can later expand here for tenant billing, lifecycle, and SaaS operations.';
        } elseif ($user->isAdmin()) {
            $notes[] = 'Tenant admins can manage every teacher profile inside their coaching center.';
        } elseif ($user->isTeacher()) {
            $notes[] = 'Teacher accounts are restricted to their own scope and profile management.';
        }

        if ($search !== '') {
            $notes[] = "Search received: \"{$search}\". Student search actions will be connected when the Student module is added.";
        }

        return $notes;
    }

    protected function roleLabel(User $user): string
    {
        return match (true) {
            $user->isSuperAdmin() => 'Platform Owner',
            $user->isAdmin() => 'Tenant Admin',
            $user->isTeacher() => 'Teacher',
            default => 'User',
        };
    }
}
