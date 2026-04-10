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
    'receipt_no',
    'student_id',
    'student_enrollment_id',
    'batch_id',
    'owner_teacher_id',
    'collector_id',
    'collector_role',
    'payment_method',
    'collected_on',
    'total_amount',
    'status',
    'notes',
])]
class Payment extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_RECEIVED = 'received';
    public const STATUS_VOID = 'void';

    public const METHOD_CASH = 'cash';
    public const METHOD_BANK = 'bank';
    public const METHOD_MOBILE_BANKING = 'mobile_banking';
    public const METHOD_CARD = 'card';
    public const METHOD_OTHER = 'other';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'collected_on' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function methods(): array
    {
        return [
            self::METHOD_CASH,
            self::METHOD_BANK,
            self::METHOD_MOBILE_BANKING,
            self::METHOD_CARD,
            self::METHOD_OTHER,
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function ownerTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'owner_teacher_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function postActions(): HasMany
    {
        return $this->hasMany(PaymentPostAction::class);
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
            return $query->where($this->qualifyColumn('student_id'), $user->student?->getKey());
        }

        if ($user->isGuardian()) {
            return $query->whereHas('student.guardians', function ($guardianQuery) use ($user): void {
                $guardianQuery->where('guardians.user_id', $user->getKey());
            });
        }

        return $query->whereRaw('1 = 0');
    }

    public function isManagedBy(User $user): bool
    {
        return $user->isAdmin()
            || ($user->teacher !== null && $this->owner_teacher_id === $user->teacher->getKey());
    }
}
