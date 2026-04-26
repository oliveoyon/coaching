<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchAdmissionLink extends Model
{
    protected $fillable = [
        'batch_id',
        'title',
        'token',
        'status',
        'expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the target batch for the link.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Get the creator of the link.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get admission requests submitted through the link.
     */
    public function admissionRequests(): HasMany
    {
        return $this->hasMany(AdmissionRequest::class);
    }

    /**
     * Determine if the link is currently available for submissions.
     */
    public function isOpen(): bool
    {
        return $this->status === 'active' && (! $this->expires_at || $this->expires_at->isFuture());
    }
}
