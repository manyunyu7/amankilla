<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUniverseRequest;
use App\Http\Requests\UpdateUniverseRequest;
use App\Models\Universe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class UniverseController extends Controller
{
    /**
     * Display a listing of the user's universes.
     */
    public function index(Request $request): Response
    {
        $universes = $request->user()
            ->universes()
            ->withCount(['timelines', 'characters'])
            ->latest()
            ->get();

        return Inertia::render('Dashboard', [
            'universes' => $universes,
        ]);
    }

    /**
     * Store a newly created universe.
     */
    public function store(StoreUniverseRequest $request): RedirectResponse
    {
        $universe = $request->user()->universes()->create($request->validated());

        return redirect()
            ->route('universes.show', $universe)
            ->with('success', 'Universe created successfully!');
    }

    /**
     * Display the specified universe.
     */
    public function show(Universe $universe): Response
    {
        Gate::authorize('view', $universe);

        $universe->load([
            'timelines' => fn ($q) => $q->withCount('scenes')->with('branchFrom.timeline'),
            'characters',
            'tags',
        ]);

        // Load all scenes for graph visualization
        $allScenes = $universe->timelines()
            ->with('scenes.tags', 'scenes.characters')
            ->get()
            ->pluck('scenes')
            ->flatten();

        return Inertia::render('Universe/Index', [
            'universe' => $universe,
            'allScenes' => $allScenes,
        ]);
    }

    /**
     * Show the settings page for the universe.
     */
    public function edit(Universe $universe): Response
    {
        Gate::authorize('update', $universe);

        return Inertia::render('Universe/Settings', [
            'universe' => $universe,
        ]);
    }

    /**
     * Update the specified universe.
     */
    public function update(UpdateUniverseRequest $request, Universe $universe): RedirectResponse
    {
        Gate::authorize('update', $universe);

        $universe->update($request->validated());

        return redirect()
            ->back()
            ->with('success', 'Universe updated successfully!');
    }

    /**
     * Remove the specified universe.
     */
    public function destroy(Universe $universe): RedirectResponse
    {
        Gate::authorize('delete', $universe);

        $universe->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Universe deleted successfully!');
    }
}
