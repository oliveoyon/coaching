<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'tenant_id',
    'user_id',
    'owner_teacher_id',
    'student_code',
    'name',
    'phone',
    'email',
    'admission_date',
    'status',
    'institution_name',
    'institution_class',
    'address',
    'notes',
])]
class Student extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_DROPOUT = 'dropout';
    public const STATUS_COMPLETED = 'completed';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'admission_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ownerTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'owner_teacher_id');
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class)
            ->withPivot(['tenant_id', 'relation_type', 'is_primary', 'notes'])
            ->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function batches(): BelongsToMany
    {
        return $this->belongsToMany(Batch::class, 'student_enrollments')
            ->withPivot(['tenant_id', 'enrolled_at', 'status', 'notes'])
            ->withTimestamps();
    }

    public function feeOverrides(): HasMany
    {
        return $this->hasMany(StudentFeeOverride::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function dues(): HasMany
    {
        return $this->hasMany(StudentDue::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        $query->where($this->qualifyColumn('tenant_id'), $user->tenant_id);

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isTeacher() && $user->teacher) {
            return $query->where($this->qualifyColumn('owner_teacher_id'), $user->teacher->getKey());
        }

        if ($user->isStudent()) {
            return $query->where($this->qualifyColumn('user_id'), $user->getKey());
        }

        if ($user->isGuardian()) {
            return $query->whereHas('guardians', function ($guardianQuery) use ($user): void {
                $guardianQuery->where('guardians.user_id', $user->getKey());
            });
        }

        return $query->whereRaw('1 = 0');
    }

    public function primaryGuardian(): ?Guardian
    {
        return $this->guardians()
            ->wherePivot('is_primary', true)
            ->first();
    }

    public function isOwnedBy(User $user): bool
    {
        return $user->teacher !== null && $this->owner_teacher_id === $user->teacher->getKey();
    }
}
