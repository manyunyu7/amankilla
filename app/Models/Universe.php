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
        'forked_from_id',
        'fork_count',
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

    /**
     * Get the universe this was forked from.
     */
    public function forkedFrom(): BelongsTo
    {
        return $this->belongsTo(Universe::class, 'forked_from_id');
    }

    /**
     * Get universes forked from this one.
     */
    public function forks(): HasMany
    {
        return $this->hasMany(Universe::class, 'forked_from_id');
    }

    /**
     * Check if universe can be viewed by a user.
     */
    public function canBeViewedBy(?User $user): bool
    {
        // Owner can always view
        if ($user && $this->user_id === $user->id) {
            return true;
        }

        // Public universes can be viewed by anyone
        return $this->is_public;
    }

    /**
     * Check if universe can be forked by a user.
     */
    public function canBeForkedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Must be public and allow fork
        if (!$this->is_public || !$this->allow_fork) {
            return false;
        }

        // Can't fork your own universe
        if ($this->user_id === $user->id) {
            return false;
        }

        return true;
    }
}
