<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use App\Models\Universe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function store(StoreTagRequest $request, Universe $universe): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $universe);

        $tag = $universe->tags()->create($request->validated());

        // Return JSON for AJAX requests (quick tag creation from TagSelector)
        if ($request->wantsJson()) {
            return response()->json($tag, 201);
        }

        return redirect()
            ->back()
            ->with('success', 'Tag created successfully!');
    }

    /**
     * Quick create a tag (JSON only, for inline creation).
     */
    public function quickCreate(Request $request, Universe $universe): JsonResponse
    {
        Gate::authorize('update', $universe);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        // Check if tag already exists in this universe
        $existingTag = $universe->tags()->where('name', $validated['name'])->first();
        if ($existingTag) {
            return response()->json($existingTag);
        }

        $tag = $universe->tags()->create($validated);

        return response()->json($tag, 201);
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
