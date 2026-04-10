<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;

class BatchPolicy
{
    public function viewAny(User $user): bool
    {
        return ($user->isAdmin() || $user->isTeacher()) && $user->can('batches.view');
    }

    public function view(User $user, Batch $batch): bool
    {
        return $this->sharesTenant($user, $batch)
            && (
                ($user->isAdmin() && $user->can('batches.view'))
                || ($user->isTeacher() && $user->can('batches.view') && $batch->isOwnedBy($user))
            );
    }

    public function create(User $user): bool
    {
        return ($user->isAdmin() || ($user->isTeacher() && $user->teacher !== null))
            && $user->can('batches.create');
    }

    public function update(User $user, Batch $batch): bool
    {
        if (! $this->sharesTenant($user, $batch)) {
            return false;
        }

        if ($user->isAdmin()) {
            return $user->can('batches.update');
        }

        return $user->isTeacher()
            && $user->can('batches.update')
            && $batch->isOwnedBy($user);
    }

    public function delete(User $user, Batch $batch): bool
    {
        if (! $this->sharesTenant($user, $batch)) {
            return false;
        }

        if ($user->isAdmin()) {
            return $user->can('batches.delete');
        }

        return $user->isTeacher()
            && $user->can('batches.delete')
            && $batch->isOwnedBy($user);
    }

    protected function sharesTenant(User $user, Batch $batch): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $batch->tenant_id;
    }
}
