<?php

namespace App\Policies;

use App\Models\BatchSchedule;
use App\Models\User;

class BatchSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return ($user->isAdmin() || $user->isTeacher()) && $user->can('batches.view');
    }

    public function view(User $user, BatchSchedule $batchSchedule): bool
    {
        return $this->sharesTenant($user, $batchSchedule)
            && $batchSchedule->isManagedBy($user)
            && $user->can('batches.view');
    }

    public function create(User $user): bool
    {
        return ($user->isAdmin() || ($user->isTeacher() && $user->teacher !== null))
            && $user->can('batches.update');
    }

    public function update(User $user, BatchSchedule $batchSchedule): bool
    {
        return $this->sharesTenant($user, $batchSchedule)
            && $batchSchedule->isManagedBy($user)
            && $user->can('batches.update');
    }

    public function delete(User $user, BatchSchedule $batchSchedule): bool
    {
        return $this->sharesTenant($user, $batchSchedule)
            && $batchSchedule->isManagedBy($user)
            && $user->can('batches.update');
    }

    protected function sharesTenant(User $user, BatchSchedule $batchSchedule): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $batchSchedule->tenant_id;
    }
}
