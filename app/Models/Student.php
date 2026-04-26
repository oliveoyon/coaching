<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    protected $fillable = [
        'student_code',
        'name',
        'class_id',
        'phone',
        'guardian_phone',
        'school',
        'address',
        'photo_path',
        'status',
    ];

    /**
     * Generate a readable code after the first insert.
     */
    protected static function booted(): void
    {
        static::created(function (Student $student): void {
            if ($student->student_code) {
                return;
            }

            $student->forceFill([
                'student_code' => sprintf('STD%04d', $student->id),
            ])->saveQuietly();
        });
    }

    /**
     * Get the academic class assigned to the student.
     */
    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    /**
     * Get enrollment history for the student.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get related admission requests.
     */
    public function admissionRequests(): HasMany
    {
        return $this->hasMany(AdmissionRequest::class);
    }

    /**
     * Get payments through enrollment history.
     */
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Enrollment::class);
    }

    /**
     * Get a public photo URL when available.
     */
    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }
}
