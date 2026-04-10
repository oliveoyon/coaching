<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tenant_id',
    'batch_id',
    'owner_teacher_id',
    'taken_by',
    'attendance_date',
    'notes',
])]
class AttendanceSession extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function ownerTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'owner_teacher_id');
    }

    public function takenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function records(): HasMany
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

        return $query->whereRaw('1 = 0');
    }

    public function isManagedBy(User $user): bool
    {
        return $user->isAdmin()
            || ($user->teacher !== null && $this->owner_teacher_id === $user->teacher->getKey());
    }
}
