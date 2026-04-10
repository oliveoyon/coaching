<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    public function view(User $user, Teacher $teacher): bool
    {
        return $this->sharesTenant($user, $teacher)
            && ($user->isAdmin() || ($user->isTeacher() && $teacher->isOwnedBy($user)));
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() && $user->can('teachers.create');
    }

    public function update(User $user, Teacher $teacher): bool
    {
        if (! $this->sharesTenant($user, $teacher)) {
            return false;
        }

        if ($user->isAdmin()) {
            return $user->can('teachers.update');
        }

        return $user->isTeacher()
            && $teacher->isOwnedBy($user)
            && $user->can('teachers.update');
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $this->sharesTenant($user, $teacher)
            && $user->isAdmin()
            && $user->can('teachers.delete');
    }

    protected function sharesTenant(User $user, Teacher $teacher): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $teacher->tenant_id;
    }
}
