<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class StudentFaceRegistration extends Model
{
    protected $fillable = [
        'student_id',
        'admission_request_id',
        'capture_path',
        'capture_method',
        'status',
        'captured_at',
        'verified_by',
        'verified_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Get the linked student when the registration is approved.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the source admission request.
     */
    public function admissionRequest(): BelongsTo
    {
        return $this->belongsTo(AdmissionRequest::class);
    }

    /**
     * Get the reviewing user.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get a public preview URL when available.
     */
    public function previewUrl(): ?string
    {
        return $this->capture_path ? Storage::url($this->capture_path) : null;
    }
}
