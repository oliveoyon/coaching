<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id',
    'student_id',
    'batch_id',
    'enrolled_at',
    'status',
    'notes',
])]
class StudentEnrollment extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enrolled_at' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
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
            return $query->whereHas('batch', function ($batchQuery) use ($user): void {
                $batchQuery->where('owner_teacher_id', $user->teacher->getKey());
            });
        }

        if ($user->isStudent()) {
            return $query->whereHas('student', function ($studentQuery) use ($user): void {
                $studentQuery->where('user_id', $user->getKey());
            });
        }

        if ($user->isGuardian()) {
            return $query->whereHas('student.guardians', function ($guardianQuery) use ($user): void {
                $guardianQuery->where('guardians.user_id', $user->getKey());
            });
        }

        return $query->whereRaw('1 = 0');
    }

    public function inferredOwnerTeacher(): ?Teacher
    {
        return $this->batch?->ownerTeacher;
    }

    public function isManagedBy(User $user): bool
    {
        return $user->isAdmin()
            || ($user->teacher !== null && $this->batch?->owner_teacher_id === $user->teacher->getKey());
    }
}
