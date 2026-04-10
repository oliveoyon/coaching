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
    'fee_head_id',
    'title',
    'billing_model',
    'applicable_type',
    'applicable_id',
    'amount',
    'is_active',
    'starts_on',
    'ends_on',
    'notes',
])]
class FeeStructure extends Model
{
    use BelongsToTenant, HasFactory;

    public const APPLICABLE_TENANT = 'tenant';
    public const APPLICABLE_PROGRAM = 'program';
    public const APPLICABLE_BATCH = 'batch';
    public const APPLICABLE_COURSE = 'course';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_active' => 'boolean',
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function applicableTypes(): array
    {
        return [
            self::APPLICABLE_TENANT,
            self::APPLICABLE_PROGRAM,
            self::APPLICABLE_BATCH,
            self::APPLICABLE_COURSE,
        ];
    }

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class);
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(StudentFeeOverride::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'applicable_id')
            ->where($this->qualifyColumn('applicable_type'), self::APPLICABLE_PROGRAM);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'applicable_id')
            ->where($this->qualifyColumn('applicable_type'), self::APPLICABLE_BATCH);
    }
}
