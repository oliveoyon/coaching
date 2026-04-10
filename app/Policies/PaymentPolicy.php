<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return ($user->isAdmin() || $user->isTeacher()) && $user->can('payments.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->sharesTenant($user, $payment)
            && $payment->isManagedBy($user)
            && $user->can('payments.view');
    }

    public function create(User $user): bool
    {
        if ($user->isAdmin()) {
            return $user->can('payments.collect');
        }

        return $user->isTeacher()
            && $user->teacher !== null
            && $user->teacher->can_collect_fees
            && $user->can('payments.collect');
    }

    protected function sharesTenant(User $user, Payment $payment): bool
    {
        return $user->tenant_id !== null && $user->tenant_id === $payment->tenant_id;
    }
}
