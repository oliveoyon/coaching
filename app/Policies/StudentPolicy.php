<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return ($user->isAdmin() || $user->isTeacher()) && $user->can('students.view');
    }

    public function view(User $user, Student $student): bool
    {
        if (! $this->sharesTenant($user, $student)) {
            return false;
        }

        if ($user->isAdmin()) {
            return $user->can('students.view');
        }

        if ($user->isTeacher()) {
            return $user->can('students.view') && $student->isOwnedBy($user);
        }

        if ($user->isStudent()) {
            return $student->user_id === $user->getKey();
        }

        if ($user->isGuardian()) {
            return $student->guardians()
                ->where('guardians.user_id', $user->getKey())
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return ($user->isAdmin() || ($user->isTeacher() && $user->teacher !== null))
            && $user->can('students.create');
    }

    public function update(User $user, Student $student): bool
    {
        if (! $this->sharesTenant($user, $student)) {
            return false;
        }

        if ($user->isAdmin()) {
            return $user->can('students.update');
        }

        return $user->isTeacher()
            && $user->can('students.update')
            && $student->isOwnedBy($user);
    }

    public function delete(User $user, Student $student): bool
    {
        if (! $this->sharesTenant($user, $student)) {
            return false;
        }

        if ($user->isAdmin()) {
            return $user->can('students.delete');
        }

        return $user->isTeacher()
            && $user->can('students.delete')
            && $student->isOwnedBy($user);
    }

    protected function sharesTenant(User $user, Student $student): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $student->tenant_id;
    }
}
