<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id',
    'batch_id',
    'subject_id',
    'teacher_id',
    'day_of_week',
    'start_time',
    'end_time',
    'session_type',
    'is_extra',
    'room_name',
    'sort_order',
    'notes',
])]
class BatchSchedule extends Model
{
    use BelongsToTenant, HasFactory;

    public const SESSION_TYPE_REGULAR = 'regular';
    public const SESSION_TYPE_EXTRA = 'extra';

    public const DAYS = [
        'saturday',
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_extra' => 'boolean',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function sessionTypes(): array
    {
        return [
            self::SESSION_TYPE_REGULAR,
            self::SESSION_TYPE_EXTRA,
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
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
            return $query->where($this->qualifyColumn('teacher_id'), $user->teacher->getKey());
        }

        return $query->whereRaw('1 = 0');
    }

    public function isManagedBy(User $user): bool
    {
        return $user->isAdmin()
            || ($user->teacher !== null && $this->teacher_id === $user->teacher->getKey());
    }
}
