<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tenant_id',
    'name',
    'code',
    'type',
    'frequency',
    'is_active',
    'description',
])]
class FeeHead extends Model
{
    use BelongsToTenant, HasFactory;

    public const TYPE_ADMISSION = 'admission';
    public const TYPE_MONTHLY_TUITION = 'monthly_tuition';
    public const TYPE_EXAM = 'exam';
    public const TYPE_CUSTOM = 'custom';

    public const FREQUENCY_ONE_TIME = 'one_time';
    public const FREQUENCY_MONTHLY = 'monthly';
    public const FREQUENCY_CUSTOM = 'custom';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function types(): array
    {
        return [
            self::TYPE_ADMISSION,
            self::TYPE_MONTHLY_TUITION,
            self::TYPE_EXAM,
            self::TYPE_CUSTOM,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function frequencies(): array
    {
        return [
            self::FREQUENCY_ONE_TIME,
            self::FREQUENCY_MONTHLY,
            self::FREQUENCY_CUSTOM,
        ];
    }

    public function structures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }
}
