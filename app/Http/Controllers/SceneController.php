<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSceneRequest;
use App\Http\Requests\UpdateSceneRequest;
use App\Models\Scene;
use App\Models\Timeline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SceneController extends Controller
{
    /**
     * Display a listing of scenes for a timeline.
     */
    public function index(Timeline $timeline): Response
    {
        Gate::authorize('view', $timeline->universe);

        $scenes = $timeline->scenes()
            ->with(['characters', 'tags'])
            ->orderBy('order')
            ->get();

        // Get all tags for the universe (for filtering)
        $allTags = $timeline->universe->tags()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return Inertia::render('Timeline/Scenes', [
            'timeline' => $timeline->load('universe'),
            'scenes' => $scenes,
            'allTags' => $allTags,
        ]);
    }

    /**
     * Store a newly created scene.
     */
    public function store(StoreSceneRequest $request, Timeline $timeline): RedirectResponse
    {
        Gate::authorize('update', $timeline->universe);

        $data = $request->validated();

        // Set order to be after the last scene
        $data['order'] = $timeline->scenes()->max('order') + 1;

        $scene = $timeline->scenes()->create($data);

        // Sync characters if provided
        if (isset($data['character_ids'])) {
            $scene->characters()->sync($data['character_ids']);
        }

        // Sync tags if provided
        if (isset($data['tag_ids'])) {
            $scene->tags()->sync($data['tag_ids']);
        }

        return redirect()
            ->route('scenes.show', $scene)
            ->with('success', 'Scene created successfully!');
    }

    /**
     * Display the specified scene.
     */
    public function show(Scene $scene): Response
    {
        Gate::authorize('view', $scene->timeline->universe);

        $scene->load(['timeline.universe', 'characters', 'tags']);

        // Get all characters and tags for the universe (for selectors)
        $universe = $scene->timeline->universe;
        $characters = $universe->characters()->orderBy('name')->get();
        $tags = $universe->tags()->orderBy('name')->get();

        return Inertia::render('Scene/Show', [
            'scene' => $scene,
            'timeline' => $scene->timeline,
            'universe' => $universe,
            'characters' => $characters,
            'tags' => $tags,
            'previousScene' => $scene->previousScene(),
            'nextScene' => $scene->nextScene(),
        ]);
    }

    /**
     * Update the specified scene.
     */
    public function update(UpdateSceneRequest $request, Scene $scene): RedirectResponse
    {
        Gate::authorize('update', $scene->timeline->universe);

        $data = $request->validated();

        $scene->update($data);

        // Update word count if content changed
        if (isset($data['content'])) {
            $scene->updateWordCount();
        }

        // Sync characters if provided
        if (isset($data['character_ids'])) {
            $scene->characters()->sync($data['character_ids']);
        }

        // Sync tags if provided
        if (isset($data['tag_ids'])) {
            $scene->tags()->sync($data['tag_ids']);
        }

        return redirect()
            ->back()
            ->with('success', 'Scene updated successfully!');
    }

    /**
     * Remove the specified scene.
     */
    public function destroy(Scene $scene): RedirectResponse
    {
        Gate::authorize('update', $scene->timeline->universe);

        $timelineId = $scene->timeline_id;
        $scene->delete();

        // Reorder remaining scenes
        Timeline::find($timelineId)
            ->scenes()
            ->orderBy('order')
            ->get()
            ->each(function ($s, $index) {
                $s->update(['order' => $index + 1]);
            });

        return redirect()
            ->route('timelines.show', $timelineId)
            ->with('success', 'Scene deleted successfully!');
    }

    /**
     * Reorder scenes in a timeline.
     */
    public function reorder(Request $request, Timeline $timeline): RedirectResponse
    {
        Gate::authorize('update', $timeline->universe);

        $request->validate([
            'scene_ids' => ['required', 'array'],
            'scene_ids.*' => ['required', 'exists:scenes,id'],
        ]);

        foreach ($request->scene_ids as $index => $sceneId) {
            Scene::where('id', $sceneId)
                ->where('timeline_id', $timeline->id)
                ->update(['order' => $index + 1]);
        }

        return redirect()
            ->back()
            ->with('success', 'Scenes reordered successfully!');
    }
}
