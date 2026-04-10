<?php

namespace App\Policies;

use App\Models\StudentDue;
use App\Models\User;

class StudentDuePolicy
{
    public function viewAny(User $user): bool
    {
        return ($user->isAdmin() || $user->isTeacher()) && $user->can('fees.view');
    }

    public function view(User $user, StudentDue $studentDue): bool
    {
        return $this->sharesTenant($user, $studentDue)
            && $studentDue->isManagedBy($user)
            && $user->can('fees.view');
    }

    public function generate(User $user): bool
    {
        return $user->isAdmin() && $user->can('fees.structure.manage');
    }

    protected function sharesTenant(User $user, StudentDue $studentDue): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $studentDue->tenant_id;
    }
}
