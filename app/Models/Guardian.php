<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'tenant_id',
    'user_id',
    'name',
    'phone',
    'email',
    'occupation',
    'address',
])]
class Guardian extends Model
{
    use BelongsToTenant, HasFactory;

    public const RELATION_FATHER = 'father';
    public const RELATION_MOTHER = 'mother';
    public const RELATION_GUARDIAN = 'guardian';
    public const RELATION_BROTHER = 'brother';
    public const RELATION_SISTER = 'sister';
    public const RELATION_OTHER = 'other';

    /**
     * @return array<int, string>
     */
    public static function relationTypes(): array
    {
        return [
            self::RELATION_FATHER,
            self::RELATION_MOTHER,
            self::RELATION_GUARDIAN,
            self::RELATION_BROTHER,
            self::RELATION_SISTER,
            self::RELATION_OTHER,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->withPivot(['tenant_id', 'relation_type', 'is_primary', 'notes'])
            ->withTimestamps();
    }
}
