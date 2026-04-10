<?php

namespace App\Policies;

use App\Models\AttendanceSession;
use App\Models\User;

class AttendanceSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return ($user->isAdmin() || $user->isTeacher()) && $user->can('attendance.view');
    }

    public function view(User $user, AttendanceSession $attendanceSession): bool
    {
        return $this->sharesTenant($user, $attendanceSession)
            && $attendanceSession->isManagedBy($user)
            && $user->can('attendance.view');
    }

    public function create(User $user): bool
    {
        return ($user->isAdmin() || ($user->isTeacher() && $user->teacher !== null))
            && $user->can('attendance.mark');
    }

    public function update(User $user, AttendanceSession $attendanceSession): bool
    {
        return $this->sharesTenant($user, $attendanceSession)
            && $attendanceSession->isManagedBy($user)
            && ($user->can('attendance.update') || $user->can('attendance.mark'));
    }

    protected function sharesTenant(User $user, AttendanceSession $attendanceSession): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $attendanceSession->tenant_id;
    }
}
