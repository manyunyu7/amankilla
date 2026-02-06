<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCharacterRequest;
use App\Http\Requests\UpdateCharacterRequest;
use App\Models\Character;
use App\Models\Universe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CharacterController extends Controller
{
    /**
     * Display a listing of characters for a universe.
     */
    public function index(Universe $universe): Response
    {
        Gate::authorize('view', $universe);

        $characters = $universe->characters()
            ->withCount('scenes')
            ->get();

        return Inertia::render('Universe/Characters', [
            'universe' => $universe,
            'characters' => $characters,
        ]);
    }

    /**
     * Store a newly created character.
     */
    public function store(StoreCharacterRequest $request, Universe $universe): RedirectResponse
    {
        Gate::authorize('update', $universe);

        $universe->characters()->create($request->validated());

        return redirect()
            ->back()
            ->with('success', 'Character created successfully!');
    }

    /**
     * Display the specified character.
     */
    public function show(Character $character): Response
    {
        Gate::authorize('view', $character->universe);

        $character->load(['scenes' => fn ($q) => $q->orderBy('order')]);
        $character->loadCount('scenes');

        return Inertia::render('Character/Show', [
            'character' => $character,
            'universe' => $character->universe,
        ]);
    }

    /**
     * Update the specified character.
     */
    public function update(UpdateCharacterRequest $request, Character $character): RedirectResponse
    {
        Gate::authorize('update', $character->universe);

        $character->update($request->validated());

        return redirect()
            ->back()
            ->with('success', 'Character updated successfully!');
    }

    /**
     * Remove the specified character.
     */
    public function destroy(Character $character): RedirectResponse
    {
        Gate::authorize('update', $character->universe);

        $universeId = $character->universe_id;
        $character->delete();

        return redirect()
            ->route('universes.characters.index', $universeId)
            ->with('success', 'Character deleted successfully!');
    }
}
