<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Universe extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'cover_image',
        'is_public',
        'allow_fork',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'allow_fork' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the universe.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the timelines in this universe.
     */
    public function timelines(): HasMany
    {
        return $this->hasMany(Timeline::class);
    }

    /**
     * Get the characters in this universe.
     */
    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    /**
     * Get the tags in this universe.
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * Get the canon timeline.
     */
    public function canonTimeline(): ?Timeline
    {
        return $this->timelines()->where('is_canon', true)->first();
    }

    /**
     * Get all scenes in this universe through timelines.
     */
    public function scenes()
    {
        return Scene::whereIn('timeline_id', $this->timelines()->pluck('id'));
    }
}
