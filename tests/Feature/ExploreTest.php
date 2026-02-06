<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Scene;
use App\Models\Tag;
use App\Models\Timeline;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExploreTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_explore_page(): void
    {
        $response = $this->get(route('explore.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Explore/Index'));
    }

    public function test_explore_shows_only_public_universes(): void
    {
        $user = User::factory()->create();
        Universe::factory()->create(['user_id' => $user->id, 'is_public' => true, 'name' => 'Public One']);
        Universe::factory()->create(['user_id' => $user->id, 'is_public' => true, 'name' => 'Public Two']);
        Universe::factory()->create(['user_id' => $user->id, 'is_public' => false, 'name' => 'Private']);

        $response = $this->get(route('explore.index'));

        $response->assertOk();
        $universes = $response->original->getData()['page']['props']['universes']['data'];
        $this->assertCount(2, $universes);
        $this->assertContains('Public One', array_column($universes, 'name'));
        $this->assertContains('Public Two', array_column($universes, 'name'));
        $this->assertNotContains('Private', array_column($universes, 'name'));
    }

    public function test_explore_can_search_universes(): void
    {
        $user = User::factory()->create();
        Universe::factory()->create(['user_id' => $user->id, 'is_public' => true, 'name' => 'Space Adventure']);
        Universe::factory()->create(['user_id' => $user->id, 'is_public' => true, 'name' => 'Fantasy World']);

        $response = $this->get(route('explore.index', ['search' => 'Space']));

        $response->assertOk();
        $universes = $response->original->getData()['page']['props']['universes']['data'];
        $this->assertCount(1, $universes);
        $this->assertEquals('Space Adventure', $universes[0]['name']);
    }

    public function test_explore_can_sort_by_popularity(): void
    {
        $user = User::factory()->create();
        Universe::factory()->create(['user_id' => $user->id, 'is_public' => true, 'name' => 'Less Popular', 'fork_count' => 5]);
        Universe::factory()->create(['user_id' => $user->id, 'is_public' => true, 'name' => 'Most Popular', 'fork_count' => 20]);

        $response = $this->get(route('explore.index', ['sort' => 'popular']));

        $response->assertOk();
        $universes = $response->original->getData()['page']['props']['universes']['data'];
        $this->assertEquals('Most Popular', $universes[0]['name']);
    }

    public function test_guest_can_view_public_universe_detail(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id, 'is_public' => true]);

        $response = $this->get(route('explore.show', $universe));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Explore/Show')
            ->has('universe')
        );
    }

    public function test_guest_cannot_view_private_universe_via_explore(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id, 'is_public' => false]);

        $response = $this->get(route('explore.show', $universe));

        $response->assertNotFound();
    }

    public function test_user_can_fork_public_universe(): void
    {
        $owner = User::factory()->create();
        $universe = Universe::factory()->create([
            'user_id' => $owner->id,
            'is_public' => true,
            'allow_fork' => true,
            'name' => 'Original Story',
        ]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        Scene::factory()->count(3)->create(['timeline_id' => $timeline->id]);

        $forker = User::factory()->create();

        $response = $this->actingAs($forker)->post(route('explore.fork', $universe));

        $response->assertRedirect();

        // Check forked universe was created
        $this->assertDatabaseHas('universes', [
            'user_id' => $forker->id,
            'name' => 'Original Story (Fork)',
            'forked_from_id' => $universe->id,
        ]);

        // Check fork count incremented
        $this->assertEquals(1, $universe->fresh()->fork_count);

        // Check scenes were copied
        $forkedUniverse = Universe::where('forked_from_id', $universe->id)->first();
        $this->assertEquals(3, $forkedUniverse->timelines->first()->scenes->count());
    }

    public function test_user_cannot_fork_private_universe(): void
    {
        $owner = User::factory()->create();
        $universe = Universe::factory()->create([
            'user_id' => $owner->id,
            'is_public' => false,
            'allow_fork' => true,
        ]);

        $forker = User::factory()->create();

        $response = $this->actingAs($forker)->post(route('explore.fork', $universe));

        $response->assertForbidden();
    }

    public function test_user_cannot_fork_universe_with_fork_disabled(): void
    {
        $owner = User::factory()->create();
        $universe = Universe::factory()->create([
            'user_id' => $owner->id,
            'is_public' => true,
            'allow_fork' => false,
        ]);

        $forker = User::factory()->create();

        $response = $this->actingAs($forker)->post(route('explore.fork', $universe));

        $response->assertForbidden();
    }

    public function test_user_cannot_fork_own_universe(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'allow_fork' => true,
        ]);

        $response = $this->actingAs($user)->post(route('explore.fork', $universe));

        $response->assertForbidden();
    }

    public function test_guest_cannot_fork_universe(): void
    {
        $owner = User::factory()->create();
        $universe = Universe::factory()->create([
            'user_id' => $owner->id,
            'is_public' => true,
            'allow_fork' => true,
        ]);

        $response = $this->post(route('explore.fork', $universe));

        $response->assertRedirect(route('login'));
    }

    public function test_forked_universe_copies_characters(): void
    {
        $owner = User::factory()->create();
        $universe = Universe::factory()->create([
            'user_id' => $owner->id,
            'is_public' => true,
            'allow_fork' => true,
        ]);
        Character::factory()->count(2)->create(['universe_id' => $universe->id]);

        $forker = User::factory()->create();
        $this->actingAs($forker)->post(route('explore.fork', $universe));

        $forkedUniverse = Universe::where('forked_from_id', $universe->id)->first();
        $this->assertEquals(2, $forkedUniverse->characters->count());
    }

    public function test_forked_universe_copies_tags(): void
    {
        $owner = User::factory()->create();
        $universe = Universe::factory()->create([
            'user_id' => $owner->id,
            'is_public' => true,
            'allow_fork' => true,
        ]);
        Tag::factory()->count(3)->create(['universe_id' => $universe->id]);

        $forker = User::factory()->create();
        $this->actingAs($forker)->post(route('explore.fork', $universe));

        $forkedUniverse = Universe::where('forked_from_id', $universe->id)->first();
        $this->assertEquals(3, $forkedUniverse->tags->count());
    }

    public function test_forked_universe_is_private_by_default(): void
    {
        $owner = User::factory()->create();
        $universe = Universe::factory()->create([
            'user_id' => $owner->id,
            'is_public' => true,
            'allow_fork' => true,
        ]);

        $forker = User::factory()->create();
        $this->actingAs($forker)->post(route('explore.fork', $universe));

        $forkedUniverse = Universe::where('forked_from_id', $universe->id)->first();
        $this->assertFalse($forkedUniverse->is_public);
        $this->assertFalse($forkedUniverse->allow_fork);
    }

    public function test_universe_shows_forked_from_attribution(): void
    {
        $owner = User::factory()->create();
        $original = Universe::factory()->create([
            'user_id' => $owner->id,
            'is_public' => true,
            'allow_fork' => true,
        ]);

        $forker = User::factory()->create();
        $this->actingAs($forker)->post(route('explore.fork', $original));

        $forked = Universe::where('forked_from_id', $original->id)->first();
        $forked->update(['is_public' => true]); // Make it public to view

        $response = $this->get(route('explore.show', $forked));

        $response->assertOk();
        $universeData = $response->original->getData()['page']['props']['universe'];
        $this->assertEquals($original->id, $universeData['forked_from_id']);
    }
}
