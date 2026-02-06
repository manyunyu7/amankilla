<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use App\Models\Universe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    /**
     * Display a listing of tags for a universe.
     */
    public function index(Universe $universe): Response
    {
        Gate::authorize('view', $universe);

        $tags = $universe->tags()
            ->withCount('scenes')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return Inertia::render('Universe/Tags', [
            'universe' => $universe,
            'tags' => $tags,
        ]);
    }

    /**
     * Store a newly created tag.
     */
    public function store(StoreTagRequest $request, Universe $universe): RedirectResponse
    {
        Gate::authorize('update', $universe);

        $universe->tags()->create($request->validated());

        return redirect()
            ->back()
            ->with('success', 'Tag created successfully!');
    }

    /**
     * Update the specified tag.
     */
    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        Gate::authorize('update', $tag->universe);

        $tag->update($request->validated());

        return redirect()
            ->back()
            ->with('success', 'Tag updated successfully!');
    }

    /**
     * Remove the specified tag.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        Gate::authorize('update', $tag->universe);

        $universeId = $tag->universe_id;
        $tag->delete();

        return redirect()
            ->route('universes.tags.index', $universeId)
            ->with('success', 'Tag deleted successfully!');
    }
}
