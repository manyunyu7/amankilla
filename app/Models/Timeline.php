<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'universe_id',
        'name',
        'description',
        'is_canon',
        'color',
        'branch_from_id',
    ];

    protected function casts(): array
    {
        return [
            'is_canon' => 'boolean',
        ];
    }

    /**
     * Get the universe that owns the timeline.
     */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /**
     * Get the scenes in this timeline.
     */
    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class)->orderBy('order');
    }

    /**
     * Get the scene this timeline branches from.
     */
    public function branchFrom(): BelongsTo
    {
        return $this->belongsTo(Scene::class, 'branch_from_id');
    }

    /**
     * Get timelines that branch from scenes in this timeline.
     */
    public function branchedTimelines()
    {
        return Timeline::whereIn('branch_from_id', $this->scenes()->pluck('id'));
    }

    /**
     * Get the first scene in this timeline.
     */
    public function firstScene(): ?Scene
    {
        return $this->scenes()->orderBy('order')->first();
    }

    /**
     * Get the last scene in this timeline.
     */
    public function lastScene(): ?Scene
    {
        return $this->scenes()->orderByDesc('order')->first();
    }
}
