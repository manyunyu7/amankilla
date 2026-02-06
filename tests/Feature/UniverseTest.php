<?php

namespace Tests\Feature;

use App\Models\Universe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniverseTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_user_universes(): void
    {
        $user = User::factory()->create();
        $universes = Universe::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('universes', 3)
        );
    }

    public function test_user_can_create_universe(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('universes.store'), [
            'name' => 'Test Universe',
            'description' => 'A test description',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('universes', [
            'name' => 'Test Universe',
            'user_id' => $user->id,
        ]);
    }

    public function test_universe_name_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('universes.store'), [
            'description' => 'A test description',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_user_can_view_own_universe(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('universes.show', $universe));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Universe/Index')
            ->has('universe')
        );
    }

    public function test_user_cannot_view_private_universe_of_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create([
            'user_id' => $otherUser->id,
            'is_public' => false,
        ]);

        $response = $this->actingAs($user)->get(route('universes.show', $universe));

        $response->assertStatus(403);
    }

    public function test_user_can_view_public_universe_of_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->public()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('universes.show', $universe));

        $response->assertStatus(200);
    }

    public function test_user_can_update_own_universe(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('universes.update', $universe), [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('universes', [
            'id' => $universe->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_cannot_update_another_users_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->put(route('universes.update', $universe), [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_universe(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('universes.destroy', $universe));

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseMissing('universes', ['id' => $universe->id]);
    }

    public function test_user_cannot_delete_another_users_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete(route('universes.destroy', $universe));

        $response->assertStatus(403);
        $this->assertDatabaseHas('universes', ['id' => $universe->id]);
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_universe_settings_page_loads(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('universes.edit', $universe));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Universe/Settings')
            ->has('universe')
        );
    }
}
