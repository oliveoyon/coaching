<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicClass extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * Get batches under this class.
     */
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class, 'class_id');
    }

    /**
     * Get students admitted under this class.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }
}
