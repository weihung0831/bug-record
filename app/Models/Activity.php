<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    public const TYPES = [
        'created',
        'updated',
        'status_changed',
        'priority_changed',
        'assigned',
        'unassigned',
        'commented',
        'attachment_added',
        'attachment_removed',
        'tag_added',
        'tag_removed',
    ];

    protected $fillable = [
        'bug_id',
        'user_id',
        'type',
        'field',
        'old_value',
        'new_value',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
