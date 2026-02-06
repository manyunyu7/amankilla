<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'universe_id',
        'name',
        'color',
        'category',
    ];

    /**
     * Get the universe that owns the tag.
     */
    public function universe(): BelongsTo
    {
        return $this->belongsTo(Universe::class);
    }

    /**
     * Get the scenes with this tag.
     */
    public function scenes(): BelongsToMany
    {
        return $this->belongsToMany(Scene::class, 'scene_tag');
    }

    /**
     * Scope for tags in a specific category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
