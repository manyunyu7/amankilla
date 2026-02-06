<?php

namespace Tests\Feature;

use App\Models\Timeline;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_timeline_for_own_universe(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('universes.timelines.store', $universe), [
            'name' => 'Canon Timeline',
            'description' => 'The main story',
            'color' => '#1CB0F6',
            'is_canon' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('timelines', [
            'name' => 'Canon Timeline',
            'universe_id' => $universe->id,
            'is_canon' => true,
        ]);
    }

    public function test_user_cannot_create_timeline_for_others_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->post(route('universes.timelines.store', $universe), [
            'name' => 'Hacked Timeline',
        ]);

        $response->assertStatus(403);
    }

    public function test_timeline_name_is_required(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('universes.timelines.store', $universe), [
            'description' => 'No name provided',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_marking_timeline_as_canon_unmarks_other_canon_timelines(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $existingCanon = Timeline::factory()->canon()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->post(route('universes.timelines.store', $universe), [
            'name' => 'New Canon',
            'is_canon' => true,
        ]);

        $this->assertFalse($existingCanon->fresh()->is_canon);
        $this->assertTrue(Timeline::where('name', 'New Canon')->first()->is_canon);
    }

    public function test_user_can_update_own_timeline(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->put(route('timelines.update', $timeline), [
            'name' => 'Updated Name',
            'color' => '#58CC02',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('timelines', [
            'id' => $timeline->id,
            'name' => 'Updated Name',
            'color' => '#58CC02',
        ]);
    }

    public function test_user_cannot_update_timeline_in_others_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->put(route('timelines.update', $timeline), [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_timeline(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->delete(route('timelines.destroy', $timeline));

        $response->assertRedirect(route('universes.show', $universe->id));
        $this->assertDatabaseMissing('timelines', ['id' => $timeline->id]);
    }

    public function test_user_cannot_delete_timeline_in_others_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->delete(route('timelines.destroy', $timeline));

        $response->assertStatus(403);
        $this->assertDatabaseHas('timelines', ['id' => $timeline->id]);
    }

    public function test_color_must_be_valid_hex(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('universes.timelines.store', $universe), [
            'name' => 'Timeline',
            'color' => 'invalid-color',
        ]);

        $response->assertSessionHasErrors('color');
    }

    public function test_timeline_index_shows_universe_timelines(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        Timeline::factory()->count(3)->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->get(route('universes.timelines.index', $universe));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Universe/Timelines')
            ->has('timelines', 3)
        );
    }
}
