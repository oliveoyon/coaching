<?php

namespace App\Policies;

use App\Models\StudentEnrollment;
use App\Models\User;

class StudentEnrollmentPolicy
{
    public function viewAny(User $user): bool
    {
        return ($user->isAdmin() || $user->isTeacher()) && $user->can('enrollments.view');
    }

    public function view(User $user, StudentEnrollment $enrollment): bool
    {
        return $this->sharesTenant($user, $enrollment)
            && $enrollment->isManagedBy($user)
            && $user->can('enrollments.view');
    }

    public function create(User $user): bool
    {
        return ($user->isAdmin() || ($user->isTeacher() && $user->teacher !== null))
            && $user->can('enrollments.create');
    }

    public function update(User $user, StudentEnrollment $enrollment): bool
    {
        return $this->sharesTenant($user, $enrollment)
            && $enrollment->isManagedBy($user)
            && $user->can('enrollments.update');
    }

    public function delete(User $user, StudentEnrollment $enrollment): bool
    {
        return $this->sharesTenant($user, $enrollment)
            && $enrollment->isManagedBy($user)
            && $user->can('enrollments.delete');
    }

    protected function sharesTenant(User $user, StudentEnrollment $enrollment): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $enrollment->tenant_id;
    }
}
