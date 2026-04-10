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
    'status',
    'description',
])]
class Subject extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(BatchSchedule::class);
    }
}
