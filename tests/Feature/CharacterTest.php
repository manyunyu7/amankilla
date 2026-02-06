<?php

namespace Tests\Feature;

use App\Models\Character;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_character_for_own_universe(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('universes.characters.store', $universe), [
            'name' => 'Test Character',
            'nickname' => 'Testy',
            'type' => 'INFJ',
            'description' => 'A test character',
            'color' => '#FF5733',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('characters', [
            'universe_id' => $universe->id,
            'name' => 'Test Character',
            'nickname' => 'Testy',
            'type' => 'INFJ',
        ]);
    }

    public function test_user_cannot_create_character_for_others_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->post(route('universes.characters.store', $universe), [
            'name' => 'Test Character',
        ]);

        $response->assertForbidden();
    }

    public function test_character_name_is_required(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('universes.characters.store', $universe), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_user_can_view_own_character(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $character = Character::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->get(route('characters.show', $character));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Character/Show')
            ->has('character')
            ->where('character.id', $character->id)
        );
    }

    public function test_user_cannot_view_character_in_others_private_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create([
            'user_id' => $otherUser->id,
            'is_public' => false,
        ]);
        $character = Character::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->get(route('characters.show', $character));

        $response->assertForbidden();
    }

    public function test_user_can_update_own_character(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $character = Character::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->put(route('characters.update', $character), [
            'name' => 'Updated Name',
            'nickname' => 'New Nickname',
            'type' => 'INFP',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('characters', [
            'id' => $character->id,
            'name' => 'Updated Name',
            'nickname' => 'New Nickname',
            'type' => 'INFP',
        ]);
    }

    public function test_user_cannot_update_character_in_others_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);
        $character = Character::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->put(route('characters.update', $character), [
            'name' => 'Updated Name',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_character(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $character = Character::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->delete(route('characters.destroy', $character));

        $response->assertRedirect(route('universes.characters.index', $universe));
        $this->assertDatabaseMissing('characters', ['id' => $character->id]);
    }

    public function test_user_cannot_delete_character_in_others_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);
        $character = Character::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->delete(route('characters.destroy', $character));

        $response->assertForbidden();
    }

    public function test_character_can_have_traits(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('universes.characters.store', $universe), [
            'name' => 'Character with Traits',
            'traits' => ['caring', 'analytical', 'introverted'],
        ]);

        $character = Character::where('name', 'Character with Traits')->first();
        $this->assertEquals(['caring', 'analytical', 'introverted'], $character->traits);
    }

    public function test_color_must_be_valid_hex(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('universes.characters.store', $universe), [
            'name' => 'Test Character',
            'color' => 'not-a-color',
        ]);

        $response->assertSessionHasErrors('color');
    }

    public function test_character_index_shows_universe_characters(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $characters = Character::factory()->count(3)->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->get(route('universes.characters.index', $universe));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Universe/Characters')
            ->has('characters', 3)
        );
    }
}
