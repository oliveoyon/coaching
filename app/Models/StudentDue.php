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
    'ledger_key',
    'student_id',
    'student_enrollment_id',
    'batch_id',
    'owner_teacher_id',
    'fee_head_id',
    'fee_structure_id',
    'billing_period_type',
    'billing_period_key',
    'period_start',
    'period_end',
    'charge_amount',
    'paid_amount',
    'due_amount',
    'status',
    'generated_at',
    'last_synced_at',
    'notes',
])]
class StudentDue extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'generated_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'charge_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_PARTIAL,
            self::STATUS_PAID,
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

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function paymentItems(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
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
