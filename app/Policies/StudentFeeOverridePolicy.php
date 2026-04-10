<?php

namespace App\Policies;

use App\Models\StudentFeeOverride;
use App\Models\User;

class StudentFeeOverridePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() && $user->can('fees.view');
    }

    public function view(User $user, StudentFeeOverride $override): bool
    {
        return $this->sharesTenant($user, $override) && $user->isAdmin() && $user->can('fees.view');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() && $user->can('fees.structure.manage');
    }

    public function update(User $user, StudentFeeOverride $override): bool
    {
        return $this->sharesTenant($user, $override) && $user->isAdmin() && $user->can('fees.structure.manage');
    }

    public function delete(User $user, StudentFeeOverride $override): bool
    {
        return $this->sharesTenant($user, $override) && $user->isAdmin() && $user->can('fees.structure.manage');
    }

    protected function sharesTenant(User $user, StudentFeeOverride $override): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $override->tenant_id;
    }
}
