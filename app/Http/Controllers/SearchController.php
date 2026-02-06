<?php

namespace App\Http\Controllers;

use App\Models\Scene;
use App\Models\Universe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SearchController extends Controller
{
    /**
     * Search scenes within a universe.
     */
    public function search(Request $request, Universe $universe): JsonResponse
    {
        Gate::authorize('view', $universe);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'character_ids' => ['nullable', 'array'],
            'character_ids.*' => ['integer', 'exists:characters,id'],
            'mood' => ['nullable', 'string', 'max:50'],
            'timeline_id' => ['nullable', 'integer', 'exists:timelines,id'],
            'sort' => ['nullable', 'string', 'in:date,order,title,created_at'],
            'sort_dir' => ['nullable', 'string', 'in:asc,desc'],
        ]);

        $query = Scene::query()
            ->whereHas('timeline', function ($q) use ($universe) {
                $q->where('universe_id', $universe->id);
            })
            ->with(['timeline', 'tags', 'characters']);

        // Full-text search on title, content, and summary
        if (!empty($validated['q'])) {
            $searchTerm = $validated['q'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('content', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('summary', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('location', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by tags
        if (!empty($validated['tag_ids'])) {
            $query->whereHas('tags', function ($q) use ($validated) {
                $q->whereIn('tags.id', $validated['tag_ids']);
            });
        }

        // Filter by characters
        if (!empty($validated['character_ids'])) {
            $query->whereHas('characters', function ($q) use ($validated) {
                $q->whereIn('characters.id', $validated['character_ids']);
            });
        }

        // Filter by mood
        if (!empty($validated['mood'])) {
            $query->where('mood', $validated['mood']);
        }

        // Filter by timeline
        if (!empty($validated['timeline_id'])) {
            $query->where('timeline_id', $validated['timeline_id']);
        }

        // Sorting
        $sortField = $validated['sort'] ?? 'order';
        $sortDir = $validated['sort_dir'] ?? 'asc';

        switch ($sortField) {
            case 'date':
                $query->orderBy('date', $sortDir);
                break;
            case 'title':
                $query->orderBy('title', $sortDir);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortDir);
                break;
            default:
                $query->orderBy('timeline_id', 'asc')->orderBy('order', $sortDir);
        }

        $results = $query->limit(50)->get();

        // Add match context for search queries
        if (!empty($validated['q'])) {
            $searchTerm = strtolower($validated['q']);
            $results = $results->map(function ($scene) use ($searchTerm) {
                $scene->match_context = $this->getMatchContext($scene, $searchTerm);
                return $scene;
            });
        }

        return response()->json([
            'results' => $results,
            'total' => $results->count(),
            'query' => $validated['q'] ?? null,
        ]);
    }

    /**
     * Get context around matching text.
     */
    private function getMatchContext(Scene $scene, string $searchTerm): ?string
    {
        $fields = ['title', 'summary', 'content', 'location'];

        foreach ($fields as $field) {
            $text = strip_tags($scene->$field ?? '');
            $pos = stripos($text, $searchTerm);

            if ($pos !== false) {
                $start = max(0, $pos - 50);
                $length = strlen($searchTerm) + 100;
                $context = substr($text, $start, $length);

                if ($start > 0) {
                    $context = '...' . $context;
                }
                if ($start + $length < strlen($text)) {
                    $context .= '...';
                }

                return $context;
            }
        }

        return null;
    }

    /**
     * Get available filter options for a universe.
     */
    public function filters(Universe $universe): JsonResponse
    {
        Gate::authorize('view', $universe);

        $tags = $universe->tags()
            ->withCount('scenes')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $characters = $universe->characters()
            ->withCount('scenes')
            ->orderBy('name')
            ->get();

        $timelines = $universe->timelines()
            ->withCount('scenes')
            ->orderBy('name')
            ->get();

        $moods = Scene::query()
            ->whereHas('timeline', fn ($q) => $q->where('universe_id', $universe->id))
            ->whereNotNull('mood')
            ->distinct()
            ->pluck('mood');

        return response()->json([
            'tags' => $tags,
            'characters' => $characters,
            'timelines' => $timelines,
            'moods' => $moods,
        ]);
    }
}
