<?php

namespace App\Support;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DataScope
{
    public function forTenant(Builder $query, User $user, string $tenantColumn = 'tenant_id'): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where($query->qualifyColumn($tenantColumn), $user->tenant_id);
    }

    public function forTeacherOwned(
        Builder $query,
        User $user,
        int|string $teacherOwnerId,
        string $ownerColumn = 'owner_teacher_id',
        string $tenantColumn = 'tenant_id',
    ): Builder {
        $query = $this->forTenant($query, $user, $tenantColumn);

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $query;
        }

        if ($user->isTeacher()) {
            return $query->where($query->qualifyColumn($ownerColumn), $teacherOwnerId);
        }

        return $query->whereRaw('1 = 0');
    }

    public function forOwnedTeacher(
        Builder $query,
        User $user,
        string $ownerColumn = 'owner_teacher_id',
        string $tenantColumn = 'tenant_id',
    ): Builder {
        $query = $this->forTenant($query, $user, $tenantColumn);

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $query;
        }

        if ($user->isTeacher()) {
            $teacherId = $user->teacher?->getKey();

            return $teacherId === null
                ? $query->whereRaw('1 = 0')
                : $query->where($query->qualifyColumn($ownerColumn), $teacherId);
        }

        return $query->whereRaw('1 = 0');
    }
}
