<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scene extends Model
{
    use HasFactory;

    protected $fillable = [
        'timeline_id',
        'title',
        'content',
        'summary',
        'order',
        'date',
        'time',
        'location',
        'mood',
        'pov',
        'word_count',
        'is_branch_point',
        'branch_question',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'word_count' => 'integer',
            'is_branch_point' => 'boolean',
        ];
    }

    /**
     * Get the timeline that owns the scene.
     */
    public function timeline(): BelongsTo
    {
        return $this->belongsTo(Timeline::class);
    }

    /**
     * Get the characters in this scene.
     */
    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'scene_character');
    }

    /**
     * Get the tags for this scene.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'scene_tag');
    }

    /**
     * Get timelines that branch from this scene.
     */
    public function branchedTimelines(): HasMany
    {
        return $this->hasMany(Timeline::class, 'branch_from_id');
    }

    /**
     * Get the universe through the timeline.
     */
    public function getUniverseAttribute(): ?Universe
    {
        return $this->timeline?->universe;
    }

    /**
     * Calculate and update word count from content.
     */
    public function updateWordCount(): void
    {
        $plainText = strip_tags($this->content);
        $this->word_count = str_word_count($plainText);
        $this->saveQuietly();
    }

    /**
     * Get the previous scene in the timeline.
     */
    public function previousScene(): ?Scene
    {
        return $this->timeline
            ->scenes()
            ->where('order', '<', $this->order)
            ->orderByDesc('order')
            ->first();
    }

    /**
     * Get the next scene in the timeline.
     */
    public function nextScene(): ?Scene
    {
        return $this->timeline
            ->scenes()
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }
}
