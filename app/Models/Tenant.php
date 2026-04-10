<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'status',
    'billing_model',
    'legal_name',
    'contact_person',
    'phone',
    'email',
    'website',
    'timezone',
    'currency',
    'address',
    'city',
    'state',
    'country',
    'logo_path',
    'max_branches',
    'max_users',
    'max_teachers',
    'max_students',
    'activated_at',
    'suspended_at',
])]
class Tenant extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_TRIAL = 'trial';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_INACTIVE = 'inactive';

    public const BILLING_MODEL_PER_STUDENT = 'per_student';
    public const BILLING_MODEL_PER_COURSE = 'per_course';
    public const BILLING_MODEL_PER_BATCH = 'per_batch';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(TenantSetting::class);
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(Guardian::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function billingConfig()
    {
        return $this->hasOne(TenantBillingConfig::class);
    }

    public function feeHeads(): HasMany
    {
        return $this->hasMany(FeeHead::class);
    }

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function studentFeeOverrides(): HasMany
    {
        return $this->hasMany(StudentFeeOverride::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function isAccessible(): bool
    {
        return in_array($this->status, [
            self::STATUS_ACTIVE,
            self::STATUS_TRIAL,
        ], true);
    }

    protected function displayName(): Attribute
    {
        return Attribute::get(fn () => $this->legal_name ?: $this->name);
    }
}
