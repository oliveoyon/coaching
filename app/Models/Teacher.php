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
    'user_id',
    'name',
    'phone',
    'email',
    'status',
    'subject_specializations',
    'address',
    'bio',
    'can_own_batches',
    'can_collect_fees',
    'joined_at',
])]
class Teacher extends Model
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
            'subject_specializations' => 'array',
            'can_own_batches' => 'boolean',
            'can_collect_fees' => 'boolean',
            'joined_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ownedBatches(): HasMany
    {
        return $this->hasMany(Batch::class, 'owner_teacher_id');
    }

    public function ownedStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'owner_teacher_id');
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

        if ($user->isTeacher()) {
            return $query->where($this->qualifyColumn('user_id'), $user->getKey());
        }

        return $query->whereRaw('1 = 0');
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id !== null && $this->user_id === $user->getKey();
    }
}
