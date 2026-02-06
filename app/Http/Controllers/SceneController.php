<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSceneRequest;
use App\Http\Requests\UpdateSceneRequest;
use App\Models\Scene;
use App\Models\Timeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    /**
     * Toggle the branch point status of a scene.
     */
    public function toggleBranchPoint(Request $request, Scene $scene): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $scene->timeline->universe);

        $request->validate([
            'is_branch_point' => ['required', 'boolean'],
            'branch_question' => ['nullable', 'string', 'max:500'],
        ]);

        $scene->update([
            'is_branch_point' => $request->is_branch_point,
            'branch_question' => $request->branch_question,
        ]);

        if ($request->wantsJson()) {
            return response()->json($scene);
        }

        return redirect()
            ->back()
            ->with('success', $request->is_branch_point
                ? 'Scene marked as branch point!'
                : 'Branch point removed.');
    }

    /**
     * Create a new timeline branching from this scene.
     */
    public function createBranch(Request $request, Scene $scene): RedirectResponse
    {
        Gate::authorize('update', $scene->timeline->universe);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'copy_subsequent_scenes' => ['boolean'],
        ]);

        return DB::transaction(function () use ($scene, $validated) {
            // Mark the scene as a branch point if not already
            if (!$scene->is_branch_point) {
                $scene->update(['is_branch_point' => true]);
            }

            // Create the new timeline
            $newTimeline = $scene->timeline->universe->timelines()->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'] ?? '#F59E0B',
                'is_canon' => false,
                'branch_from_id' => $scene->id,
            ]);

            // Optionally copy subsequent scenes from the original timeline
            if (!empty($validated['copy_subsequent_scenes'])) {
                $subsequentScenes = $scene->timeline->scenes()
                    ->where('order', '>', $scene->order)
                    ->orderBy('order')
                    ->get();

                $newOrder = 1;
                foreach ($subsequentScenes as $originalScene) {
                    $newScene = $newTimeline->scenes()->create([
                        'title' => $originalScene->title,
                        'content' => $originalScene->content,
                        'summary' => $originalScene->summary,
                        'order' => $newOrder++,
                        'date' => $originalScene->date,
                        'time' => $originalScene->time,
                        'location' => $originalScene->location,
                        'mood' => $originalScene->mood,
                        'pov' => $originalScene->pov,
                        'word_count' => $originalScene->word_count,
                    ]);

                    // Copy character and tag associations
                    $newScene->characters()->sync($originalScene->characters->pluck('id'));
                    $newScene->tags()->sync($originalScene->tags->pluck('id'));
                }
            }

            return redirect()
                ->route('timelines.show', $newTimeline)
                ->with('success', 'Alternate timeline created successfully!');
        });
    }

    /**
     * Get branches from a scene.
     */
    public function branches(Scene $scene): JsonResponse
    {
        Gate::authorize('view', $scene->timeline->universe);

        $branches = Timeline::where('branch_from_id', $scene->id)
            ->withCount('scenes')
            ->get();

        return response()->json($branches);
    }
}
