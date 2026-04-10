<?php

namespace App\Policies;

use App\Models\FeeStructure;
use App\Models\User;

class FeeStructurePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() && $user->can('fees.view');
    }

    public function view(User $user, FeeStructure $feeStructure): bool
    {
        return $this->sharesTenant($user, $feeStructure) && $user->isAdmin() && $user->can('fees.view');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() && $user->can('fees.structure.manage');
    }

    public function update(User $user, FeeStructure $feeStructure): bool
    {
        return $this->sharesTenant($user, $feeStructure) && $user->isAdmin() && $user->can('fees.structure.manage');
    }

    public function delete(User $user, FeeStructure $feeStructure): bool
    {
        return $this->sharesTenant($user, $feeStructure) && $user->isAdmin() && $user->can('fees.structure.manage');
    }

    protected function sharesTenant(User $user, FeeStructure $feeStructure): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $feeStructure->tenant_id;
    }
}
