<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionRequest extends Model
{
    protected $fillable = [
        'batch_admission_link_id',
        'batch_id',
        'student_id',
        'name',
        'phone',
        'guardian_phone',
        'school',
        'address',
        'photo_path',
        'status',
        'review_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Get the source admission link.
     */
    public function batchAdmissionLink(): BelongsTo
    {
        return $this->belongsTo(BatchAdmissionLink::class);
    }

    /**
     * Get the target batch.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the linked student after approval.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the admin who reviewed the request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
