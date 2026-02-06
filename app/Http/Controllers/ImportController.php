<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Scene;
use App\Models\Tag;
use App\Models\Timeline;
use App\Models\Universe;
use App\Services\RawParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ImportController extends Controller
{
    public function __construct(
        protected RawParser $parser
    ) {}

    /**
     * Show the import page.
     */
    public function index(Universe $universe): Response
    {
        Gate::authorize('update', $universe);

        return Inertia::render('Universe/Import', [
            'universe' => $universe->load(['timelines', 'characters', 'tags']),
        ]);
    }

    /**
     * Preview parsed content without importing.
     */
    public function preview(Request $request, Universe $universe): JsonResponse
    {
        Gate::authorize('update', $universe);

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'timeline_name' => ['nullable', 'string', 'max:255'],
        ]);

        $content = $validated['content'];
        $scenes = $this->parser->parse($content);
        $stats = $this->parser->getStats($content);

        return response()->json([
            'scenes' => $scenes,
            'stats' => $stats,
            'timeline_name' => $validated['timeline_name'] ?? 'Imported Timeline',
        ]);
    }

    /**
     * Import content to universe.
     */
    public function import(Request $request, Universe $universe): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $universe);

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'timeline_name' => ['required', 'string', 'max:255'],
            'timeline_description' => ['nullable', 'string', 'max:1000'],
            'timeline_color' => ['nullable', 'string', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'is_canon' => ['boolean'],
            'create_characters' => ['boolean'],
            'create_tags' => ['boolean'],
        ]);

        $scenes = $this->parser->parse($validated['content']);

        if (empty($scenes)) {
            return $request->wantsJson()
                ? response()->json(['error' => 'No scenes found in content'], 422)
                : back()->withErrors(['content' => 'No scenes found in content']);
        }

        $result = DB::transaction(function () use ($validated, $scenes, $universe) {
            // Create timeline
            $timeline = Timeline::create([
                'universe_id' => $universe->id,
                'name' => $validated['timeline_name'],
                'description' => $validated['timeline_description'] ?? null,
                'color' => $validated['timeline_color'] ?? '#1CB0F6',
                'is_canon' => $validated['is_canon'] ?? false,
            ]);

            // Collect all unique characters and tags
            $allCharacters = [];
            $allTags = [];

            foreach ($scenes as $scene) {
                foreach ($scene['characters'] as $char) {
                    $allCharacters[$char] = true;
                }
                foreach ($scene['tags'] as $tag) {
                    $allTags[$tag] = true;
                }
            }

            // Create characters if requested
            $characterMap = [];
            if ($validated['create_characters'] ?? true) {
                $characterColors = ['#1CB0F6', '#58CC02', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];
                $colorIndex = 0;

                foreach (array_keys($allCharacters) as $charName) {
                    // Check if character already exists
                    $existing = $universe->characters()->where('name', $charName)->first();

                    if ($existing) {
                        $characterMap[$charName] = $existing->id;
                    } else {
                        $character = Character::create([
                            'universe_id' => $universe->id,
                            'name' => $charName,
                            'color' => $characterColors[$colorIndex % count($characterColors)],
                            'type' => $charName, // INFJ or INFP as personality type
                        ]);
                        $characterMap[$charName] = $character->id;
                        $colorIndex++;
                    }
                }
            }

            // Create tags if requested
            $tagMap = [];
            if ($validated['create_tags'] ?? true) {
                $tagColors = ['#1CB0F6', '#58CC02', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];
                $colorIndex = 0;

                foreach (array_keys($allTags) as $tagName) {
                    // Check if tag already exists
                    $existing = $universe->tags()->whereRaw('LOWER(name) = ?', [strtolower($tagName)])->first();

                    if ($existing) {
                        $tagMap[strtolower($tagName)] = $existing->id;
                    } else {
                        $tag = Tag::create([
                            'universe_id' => $universe->id,
                            'name' => $tagName,
                            'color' => $tagColors[$colorIndex % count($tagColors)],
                            'category' => $this->categorizeTag($tagName),
                        ]);
                        $tagMap[strtolower($tagName)] = $tag->id;
                        $colorIndex++;
                    }
                }
            }

            // Create scenes
            $createdScenes = [];
            foreach ($scenes as $sceneData) {
                $scene = Scene::create([
                    'timeline_id' => $timeline->id,
                    'title' => $sceneData['title'],
                    'content' => $sceneData['content'],
                    'summary' => $sceneData['summary'],
                    'location' => $sceneData['location'],
                    'date' => $sceneData['date'],
                    'mood' => $sceneData['mood'],
                    'is_branch_point' => $sceneData['is_branch_point'],
                    'order' => $sceneData['order'],
                ]);

                // Attach characters
                if ($validated['create_characters'] ?? true) {
                    foreach ($sceneData['characters'] as $charName) {
                        if (isset($characterMap[$charName])) {
                            $scene->characters()->attach($characterMap[$charName]);
                        }
                    }
                }

                // Attach tags
                if ($validated['create_tags'] ?? true) {
                    foreach ($sceneData['tags'] as $tagName) {
                        if (isset($tagMap[strtolower($tagName)])) {
                            $scene->tags()->attach($tagMap[strtolower($tagName)]);
                        }
                    }
                }

                $createdScenes[] = $scene;
            }

            return [
                'timeline' => $timeline,
                'scenes_count' => count($createdScenes),
                'characters_count' => count($characterMap),
                'tags_count' => count($tagMap),
            ];
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Imported {$result['scenes_count']} scenes to timeline '{$result['timeline']->name}'",
                'result' => $result,
            ]);
        }

        return redirect()->route('universes.show', $universe)
            ->with('success', "Imported {$result['scenes_count']} scenes to timeline '{$result['timeline']->name}'");
    }

    /**
     * Categorize a tag based on its name.
     */
    protected function categorizeTag(string $name): string
    {
        $name = strtolower($name);

        $locationTags = ['bandung', 'garut', 'jakarta', 'kereta', 'stasiun', 'cafe', 'travel'];
        if (in_array($name, $locationTags)) {
            return 'location';
        }

        $emotionTags = ['romantic', 'happy', 'sad', 'goodbye', 'love'];
        if (in_array($name, $emotionTags)) {
            return 'emotion';
        }

        $eventTags = ['first meeting', 'morning', 'night'];
        if (in_array($name, $eventTags)) {
            return 'event';
        }

        return 'general';
    }

    /**
     * Get import statistics for a file without importing.
     */
    public function stats(Request $request, Universe $universe): JsonResponse
    {
        Gate::authorize('view', $universe);

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $stats = $this->parser->getStats($validated['content']);

        return response()->json($stats);
    }
}
