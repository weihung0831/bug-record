<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Tag $tag) {
            if (empty($tag->slug)) {
                $slug = Str::slug($tag->name);
                // 中文名稱會產生空 slug，使用隨機字串
                $tag->slug = $slug ?: Str::random(8);
            }
        });
    }

    public function bugs(): BelongsToMany
    {
        return $this->belongsToMany(Bug::class)->withTimestamps();
    }
}
