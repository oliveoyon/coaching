<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Determine if the user account is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function teacherProfile(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Get admission links created by the user.
     */
    public function batchAdmissionLinks(): HasMany
    {
        return $this->hasMany(BatchAdmissionLink::class, 'created_by');
    }

    /**
     * Get enrollments created by the user.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'created_by');
    }

    /**
     * Get admission requests reviewed by the user.
     */
    public function reviewedAdmissionRequests(): HasMany
    {
        return $this->hasMany(AdmissionRequest::class, 'reviewed_by');
    }

    /**
     * Get payments collected by the user.
     */
    public function collectedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'collected_by');
    }

    /**
     * Get payments approved by the user.
     */
    public function approvedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'approved_by');
    }

    /**
     * Get expenses entered by this user.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'created_by');
    }

    /**
     * Get teacher settlements recorded by this user.
     */
    public function teacherSettlements(): HasMany
    {
        return $this->hasMany(TeacherSettlement::class, 'paid_by');
    }

}
