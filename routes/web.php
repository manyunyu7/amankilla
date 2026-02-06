<?php

use App\Http\Controllers\CharacterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SceneController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\UniverseController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard (Universe index)
    Route::get('/dashboard', [UniverseController::class, 'index'])->name('dashboard');

    // Universe CRUD
    Route::resource('universes', UniverseController::class)->except(['index']);

    // Timeline CRUD (nested under universe for store, shallow for others)
    Route::resource('universes.timelines', TimelineController::class)->shallow();

    // Scene CRUD (nested under timeline for store, shallow for others)
    Route::resource('timelines.scenes', SceneController::class)->shallow();
    Route::post('/timelines/{timeline}/scenes/reorder', [SceneController::class, 'reorder'])
        ->name('timelines.scenes.reorder');

    // Scene branching
    Route::patch('/scenes/{scene}/branch-point', [SceneController::class, 'toggleBranchPoint'])
        ->name('scenes.toggle-branch-point');
    Route::post('/scenes/{scene}/branch', [SceneController::class, 'createBranch'])
        ->name('scenes.create-branch');
    Route::get('/scenes/{scene}/branches', [SceneController::class, 'branches'])
        ->name('scenes.branches');

    // Character CRUD (nested under universe for store, shallow for others)
    Route::resource('universes.characters', CharacterController::class)->shallow();

    // Tag CRUD (nested under universe for store, shallow for others)
    Route::resource('universes.tags', TagController::class)->shallow()->except(['show']);
    Route::post('/universes/{universe}/tags/quick', [TagController::class, 'quickCreate'])
        ->name('universes.tags.quick');

    // Search
    Route::get('/universes/{universe}/search', [SearchController::class, 'search'])
        ->name('universes.search');
    Route::get('/universes/{universe}/search/filters', [SearchController::class, 'filters'])
        ->name('universes.search.filters');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
