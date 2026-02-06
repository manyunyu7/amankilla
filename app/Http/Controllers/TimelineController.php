<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimelineRequest;
use App\Http\Requests\UpdateTimelineRequest;
use App\Models\Timeline;
use App\Models\Universe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TimelineController extends Controller
{
    /**
     * Display a listing of timelines for a universe.
     */
    public function index(Universe $universe): Response
    {
        Gate::authorize('view', $universe);

        $timelines = $universe->timelines()
            ->withCount('scenes')
            ->get();

        return Inertia::render('Universe/Timelines', [
            'universe' => $universe,
            'timelines' => $timelines,
        ]);
    }

    /**
     * Store a newly created timeline.
     */
    public function store(StoreTimelineRequest $request, Universe $universe): RedirectResponse
    {
        Gate::authorize('update', $universe);

        $data = $request->validated();

        // If this is marked as canon, unmark other canon timelines
        if ($data['is_canon'] ?? false) {
            $universe->timelines()->where('is_canon', true)->update(['is_canon' => false]);
        }

        $universe->timelines()->create($data);

        return redirect()
            ->back()
            ->with('success', 'Timeline created successfully!');
    }

    /**
     * Display the specified timeline.
     */
    public function show(Timeline $timeline): Response
    {
        Gate::authorize('view', $timeline->universe);

        $timeline->load(['scenes' => fn ($q) => $q->orderBy('order'), 'branchFrom']);

        return Inertia::render('Timeline/Show', [
            'timeline' => $timeline,
            'universe' => $timeline->universe,
        ]);
    }

    /**
     * Update the specified timeline.
     */
    public function update(UpdateTimelineRequest $request, Timeline $timeline): RedirectResponse
    {
        Gate::authorize('update', $timeline->universe);

        $data = $request->validated();

        // If this is being marked as canon, unmark other canon timelines
        if (($data['is_canon'] ?? false) && !$timeline->is_canon) {
            $timeline->universe->timelines()
                ->where('is_canon', true)
                ->update(['is_canon' => false]);
        }

        $timeline->update($data);

        return redirect()
            ->back()
            ->with('success', 'Timeline updated successfully!');
    }

    /**
     * Remove the specified timeline.
     */
    public function destroy(Timeline $timeline): RedirectResponse
    {
        Gate::authorize('update', $timeline->universe);

        $universeId = $timeline->universe_id;
        $timeline->delete();

        return redirect()
            ->route('universes.show', $universeId)
            ->with('success', 'Timeline deleted successfully!');
    }
}
