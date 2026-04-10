<?php

namespace App\Policies;

use App\Models\FeeHead;
use App\Models\User;

class FeeHeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() && $user->can('fees.view');
    }

    public function view(User $user, FeeHead $feeHead): bool
    {
        return $this->sharesTenant($user, $feeHead) && $user->isAdmin() && $user->can('fees.view');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() && $user->can('fees.structure.manage');
    }

    public function update(User $user, FeeHead $feeHead): bool
    {
        return $this->sharesTenant($user, $feeHead) && $user->isAdmin() && $user->can('fees.structure.manage');
    }

    public function delete(User $user, FeeHead $feeHead): bool
    {
        return $this->sharesTenant($user, $feeHead) && $user->isAdmin() && $user->can('fees.structure.manage');
    }

    protected function sharesTenant(User $user, FeeHead $feeHead): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $feeHead->tenant_id;
    }
}
