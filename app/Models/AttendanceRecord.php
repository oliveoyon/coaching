<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'attendance_session_id',
        'enrollment_id',
        'student_id',
        'status',
        'method',
        'confidence_score',
        'marked_by',
        'marked_at',
        'scan_code',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'marked_at' => 'datetime',
            'confidence_score' => 'decimal:2',
        ];
    }

    /**
     * Get the parent session.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    /**
     * Get the active enrollment this row belongs to.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the student on this row.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who marked this row.
     */
    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
