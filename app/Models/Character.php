<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Character extends Model
{
    use HasFactory;

    protected $fillable = [
        'universe_id',
        'name',
        'nickname',
        'type',
        'description',
        'traits',
        'avatar_url',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'traits' => 'array',
        ];
    }

    /**
     * Get the universe that owns the character.
     */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /**
     * Get the scenes that feature this character.
     */
    public function scenes(): BelongsToMany
    {
        return $this->belongsToMany(Scene::class, 'scene_character');
    }

    /**
     * Get the display name (nickname or name).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->nickname ?? $this->name;
    }
}
