<?php

namespace App\Http\Controllers;

use App\Models\Universe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExploreController extends Controller
{
    /**
     * Show the explore page with public universes.
     */
    public function index(Request $request): Response
    {
        $query = Universe::query()
            ->where('is_public', true)
            ->with(['user:id,name,username', 'timelines'])
            ->withCount(['timelines', 'characters', 'tags']);

        // Search by name or description
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Sort options
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'popular':
                $query->orderByDesc('fork_count');
                break;
            case 'name':
                $query->orderBy('name');
                break;
            default:
                $query->orderByDesc('created_at');
        }

        $universes = $query->paginate(12)->withQueryString();

        return Inertia::render('Explore/Index', [
            'universes' => $universes,
            'filters' => [
                'search' => $request->get('search'),
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * Show a public universe detail.
     */
    public function show(Universe $universe): Response
    {
        // Must be public to view via explore
        if (!$universe->is_public) {
            abort(404);
        }

        $universe->load([
            'user:id,name,username',
            'timelines' => function ($q) {
                $q->withCount('scenes');
            },
            'characters',
            'tags',
            'forkedFrom:id,name,user_id',
            'forkedFrom.user:id,name,username',
        ]);

        // Get all scenes for graph
        $allScenes = $universe->scenes()
            ->with(['timeline:id,name,color', 'tags', 'characters'])
            ->orderBy('timeline_id')
            ->orderBy('order')
            ->get();

        return Inertia::render('Explore/Show', [
            'universe' => $universe,
            'allScenes' => $allScenes,
            'canFork' => $universe->canBeForkedBy(auth()->user()),
        ]);
    }

    /**
     * Fork a universe.
     */
    public function fork(Request $request, Universe $universe)
    {
        $user = $request->user();

        if (!$universe->canBeForkedBy($user)) {
            abort(403, 'You cannot fork this universe.');
        }

        // Create forked universe
        $forked = Universe::create([
            'user_id' => $user->id,
            'name' => $universe->name . ' (Fork)',
            'description' => $universe->description,
            'cover_image' => $universe->cover_image,
            'is_public' => false, // Forked universes are private by default
            'allow_fork' => false,
            'forked_from_id' => $universe->id,
        ]);

        // Clone timelines
        $timelineMap = [];
        foreach ($universe->timelines as $timeline) {
            $newTimeline = $forked->timelines()->create([
                'name' => $timeline->name,
                'description' => $timeline->description,
                'color' => $timeline->color,
                'is_canon' => $timeline->is_canon,
            ]);
            $timelineMap[$timeline->id] = $newTimeline->id;
        }

        // Clone characters
        $characterMap = [];
        foreach ($universe->characters as $character) {
            $newCharacter = $forked->characters()->create([
                'name' => $character->name,
                'description' => $character->description,
                'color' => $character->color,
                'type' => $character->type,
                'traits' => $character->traits,
            ]);
            $characterMap[$character->id] = $newCharacter->id;
        }

        // Clone tags
        $tagMap = [];
        foreach ($universe->tags as $tag) {
            $newTag = $forked->tags()->create([
                'name' => $tag->name,
                'color' => $tag->color,
                'category' => $tag->category,
            ]);
            $tagMap[$tag->id] = $newTag->id;
        }

        // Clone scenes
        foreach ($universe->timelines as $timeline) {
            foreach ($timeline->scenes as $scene) {
                $newScene = $forked->timelines()
                    ->find($timelineMap[$timeline->id])
                    ->scenes()
                    ->create([
                        'title' => $scene->title,
                        'content' => $scene->content,
                        'summary' => $scene->summary,
                        'order' => $scene->order,
                        'date' => $scene->date,
                        'location' => $scene->location,
                        'mood' => $scene->mood,
                        'pov' => $scene->pov,
                        'is_branch_point' => $scene->is_branch_point,
                        'branch_question' => $scene->branch_question,
                    ]);

                // Attach characters
                foreach ($scene->characters as $character) {
                    if (isset($characterMap[$character->id])) {
                        $newScene->characters()->attach($characterMap[$character->id]);
                    }
                }

                // Attach tags
                foreach ($scene->tags as $tag) {
                    if (isset($tagMap[$tag->id])) {
                        $newScene->tags()->attach($tagMap[$tag->id]);
                    }
                }
            }
        }

        // Increment fork count on original
        $universe->increment('fork_count');

        return redirect()->route('universes.show', $forked)
            ->with('success', "Successfully forked '{$universe->name}'");
    }
}
