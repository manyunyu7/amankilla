<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_tag_for_own_universe(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('universes.tags.store', $universe), [
            'name' => 'cute',
            'color' => '#FF5733',
            'category' => 'emotion',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tags', [
            'universe_id' => $universe->id,
            'name' => 'cute',
            'color' => '#FF5733',
            'category' => 'emotion',
        ]);
    }

    public function test_user_cannot_create_tag_for_others_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->post(route('universes.tags.store', $universe), [
            'name' => 'test',
        ]);

        $response->assertForbidden();
    }

    public function test_tag_name_is_required(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('universes.tags.store', $universe), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_tag_category_must_be_valid(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('universes.tags.store', $universe), [
            'name' => 'test',
            'category' => 'invalid_category',
        ]);

        $response->assertSessionHasErrors('category');
    }

    public function test_user_can_update_own_tag(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $tag = Tag::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->put(route('tags.update', $tag), [
            'name' => 'Updated Tag',
            'color' => '#00FF00',
            'category' => 'event',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Updated Tag',
            'color' => '#00FF00',
            'category' => 'event',
        ]);
    }

    public function test_user_cannot_update_tag_in_others_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);
        $tag = Tag::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->put(route('tags.update', $tag), [
            'name' => 'Updated Tag',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_tag(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $tag = Tag::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->delete(route('tags.destroy', $tag));

        $response->assertRedirect(route('universes.tags.index', $universe));
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_user_cannot_delete_tag_in_others_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);
        $tag = Tag::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->delete(route('tags.destroy', $tag));

        $response->assertForbidden();
    }

    public function test_color_must_be_valid_hex(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('universes.tags.store', $universe), [
            'name' => 'Test Tag',
            'color' => 'not-a-color',
        ]);

        $response->assertSessionHasErrors('color');
    }

    public function test_tag_index_shows_universe_tags(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        Tag::factory()->count(5)->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->get(route('universes.tags.index', $universe));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Universe/Tags')
            ->has('tags', 5)
        );
    }

    public function test_tags_are_ordered_by_category_and_name(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        Tag::factory()->create(['universe_id' => $universe->id, 'name' => 'zebra', 'category' => 'emotion']);
        Tag::factory()->create(['universe_id' => $universe->id, 'name' => 'apple', 'category' => 'emotion']);
        Tag::factory()->create(['universe_id' => $universe->id, 'name' => 'beta', 'category' => 'event']);

        $response = $this->actingAs($user)->get(route('universes.tags.index', $universe));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Universe/Tags')
            ->has('tags', 3)
        );
    }
}
