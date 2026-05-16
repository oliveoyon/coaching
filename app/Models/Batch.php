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
        'schedule_slots',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'monthly_fee' => 'decimal:2',
            'schedule_days' => 'array',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'schedule_slots' => 'array',
        ];
    }

    /**
     * Get normalized schedule entries for this batch.
     *
     * @return array<int, array<string, string>>
     */
    public function getScheduleEntriesAttribute(): array
    {
        $slots = collect($this->schedule_slots ?? [])
            ->filter(fn ($slot) => filled($slot['day'] ?? null) && filled($slot['start_time'] ?? null) && filled($slot['end_time'] ?? null))
            ->map(fn ($slot) => [
                'day' => (string) $slot['day'],
                'start_time' => (string) $slot['start_time'],
                'end_time' => (string) $slot['end_time'],
            ])
            ->values()
            ->all();

        if ($slots !== []) {
            return $slots;
        }

        $days = collect($this->schedule_days ?? [])->filter()->values();

        if ($days->isEmpty() || ! $this->start_time || ! $this->end_time) {
            return [];
        }

        return $days->map(fn ($day) => [
            'day' => (string) $day,
            'start_time' => $this->start_time?->format('H:i') ?? '',
            'end_time' => $this->end_time?->format('H:i') ?? '',
        ])->all();
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

    /**
     * Get paused billing months for this batch.
     */
    public function billingBreaks(): HasMany
    {
        return $this->hasMany(BatchBillingBreak::class);
    }

    /**
     * Determine whether monthly billing is paused for the given month.
     */
    public function isBillingPausedForMonth(string $month): bool
    {
        $breaks = $this->relationLoaded('billingBreaks')
            ? $this->billingBreaks
            : $this->billingBreaks()->get();

        return $breaks
            ->where('status', 'active')
            ->contains(fn (BatchBillingBreak $break) => $break->month === $month);
    }

    /**
     * Get attendance sessions opened for this batch.
     */
    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }
}
