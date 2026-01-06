<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bug extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['open', 'in_progress', 'resolved', 'closed'];

    public const PRIORITIES = ['low', 'medium', 'high', 'critical'];

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'reporter_id',
        'assignee_id',
        'resolved_at',
        'closed_at',
    ];

    protected $attributes = [
        'status' => 'open',
        'priority' => 'medium',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    // Relationships

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->latest();
    }

    // Scopes

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assignee_id', $userId);
    }

    public function scopeReportedBy(Builder $query, int $userId): Builder
    {
        return $query->where('reporter_id', $userId);
    }

    public function scopeWithTag(Builder $query, string $tagSlug): Builder
    {
        return $query->whereHas('tags', fn ($q) => $q->where('slug', $tagSlug));
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
