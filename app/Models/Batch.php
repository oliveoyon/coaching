<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    protected $fillable = [
        'name',
        'class_id',
        'subject_id',
        'monthly_fee',
        'distribution_type',
        'schedule_days',
        'start_time',
        'end_time',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'monthly_fee' => 'decimal:2',
            'schedule_days' => 'array',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
        ];
    }

    /**
     * Get the class for this batch.
     */
    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    /**
     * Get the subject for this batch.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get assigned teachers.
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'batch_teacher', 'batch_id', 'teacher_id')
            ->with('user')
            ->withTimestamps();
    }

    /**
     * Get student enrollments under this batch.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get admission links created for this batch.
     */
    public function admissionLinks(): HasMany
    {
        return $this->hasMany(BatchAdmissionLink::class);
    }

    /**
     * Get incoming admission requests for this batch.
     */
    public function admissionRequests(): HasMany
    {
        return $this->hasMany(AdmissionRequest::class);
    }

    /**
     * Get configured fee items for this batch.
     */
    public function batchFees(): HasMany
    {
        return $this->hasMany(BatchFee::class);
    }
}
