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

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_search_scenes_by_text(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);

        Scene::factory()->create([
            'timeline_id' => $timeline->id,
            'title' => 'The First Meeting',
            'content' => 'They met at a coffee shop.',
        ]);
        Scene::factory()->create([
            'timeline_id' => $timeline->id,
            'title' => 'The Farewell',
            'content' => 'They said goodbye at the station.',
        ]);

        $response = $this->actingAs($user)->getJson(route('universes.search', [
            'universe' => $universe->id,
            'q' => 'coffee',
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'results');
        $response->assertJsonFragment(['title' => 'The First Meeting']);
    }

    public function test_user_can_filter_scenes_by_tag(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $tag = Tag::factory()->create(['universe_id' => $universe->id, 'name' => 'romantic']);

        $scene1 = Scene::factory()->create(['timeline_id' => $timeline->id, 'title' => 'Tagged Scene']);
        $scene1->tags()->attach($tag->id);

        Scene::factory()->create(['timeline_id' => $timeline->id, 'title' => 'Untagged Scene']);

        $response = $this->actingAs($user)->getJson(route('universes.search', [
            'universe' => $universe->id,
            'tag_ids' => [$tag->id],
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'results');
        $response->assertJsonFragment(['title' => 'Tagged Scene']);
    }

    public function test_user_can_filter_scenes_by_character(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $character = Character::factory()->create(['universe_id' => $universe->id, 'name' => 'Sarah']);

        $scene1 = Scene::factory()->create(['timeline_id' => $timeline->id, 'title' => 'With Sarah']);
        $scene1->characters()->attach($character->id);

        Scene::factory()->create(['timeline_id' => $timeline->id, 'title' => 'Without Sarah']);

        $response = $this->actingAs($user)->getJson(route('universes.search', [
            'universe' => $universe->id,
            'character_ids' => [$character->id],
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'results');
        $response->assertJsonFragment(['title' => 'With Sarah']);
    }

    public function test_user_can_filter_scenes_by_mood(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);

        Scene::factory()->create(['timeline_id' => $timeline->id, 'title' => 'Happy Scene', 'mood' => 'happy']);
        Scene::factory()->create(['timeline_id' => $timeline->id, 'title' => 'Sad Scene', 'mood' => 'sad']);

        $response = $this->actingAs($user)->getJson(route('universes.search', [
            'universe' => $universe->id,
            'mood' => 'happy',
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'results');
        $response->assertJsonFragment(['title' => 'Happy Scene']);
    }

    public function test_user_can_filter_scenes_by_timeline(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline1 = Timeline::factory()->create(['universe_id' => $universe->id, 'name' => 'Canon']);
        $timeline2 = Timeline::factory()->create(['universe_id' => $universe->id, 'name' => 'Alternate']);

        Scene::factory()->create(['timeline_id' => $timeline1->id, 'title' => 'Canon Scene']);
        Scene::factory()->create(['timeline_id' => $timeline2->id, 'title' => 'Alternate Scene']);

        $response = $this->actingAs($user)->getJson(route('universes.search', [
            'universe' => $universe->id,
            'timeline_id' => $timeline1->id,
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'results');
        $response->assertJsonFragment(['title' => 'Canon Scene']);
    }

    public function test_user_can_combine_filters(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $tag = Tag::factory()->create(['universe_id' => $universe->id]);

        $scene1 = Scene::factory()->create([
            'timeline_id' => $timeline->id,
            'title' => 'Happy Tagged',
            'mood' => 'happy',
        ]);
        $scene1->tags()->attach($tag->id);

        $scene2 = Scene::factory()->create([
            'timeline_id' => $timeline->id,
            'title' => 'Sad Tagged',
            'mood' => 'sad',
        ]);
        $scene2->tags()->attach($tag->id);

        Scene::factory()->create([
            'timeline_id' => $timeline->id,
            'title' => 'Happy Untagged',
            'mood' => 'happy',
        ]);

        $response = $this->actingAs($user)->getJson(route('universes.search', [
            'universe' => $universe->id,
            'tag_ids' => [$tag->id],
            'mood' => 'happy',
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'results');
        $response->assertJsonFragment(['title' => 'Happy Tagged']);
    }

    public function test_user_cannot_search_others_private_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id, 'is_public' => false]);

        $response = $this->actingAs($user)->getJson(route('universes.search', [
            'universe' => $universe->id,
            'q' => 'test',
        ]));

        $response->assertForbidden();
    }

    public function test_user_can_get_filter_options(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);

        Tag::factory()->count(3)->create(['universe_id' => $universe->id]);
        Character::factory()->count(2)->create(['universe_id' => $universe->id]);

        Scene::factory()->create(['timeline_id' => $timeline->id, 'mood' => 'happy']);
        Scene::factory()->create(['timeline_id' => $timeline->id, 'mood' => 'sad']);

        $response = $this->actingAs($user)->getJson(route('universes.search.filters', $universe));

        $response->assertOk();
        $response->assertJsonCount(3, 'tags');
        $response->assertJsonCount(2, 'characters');
        $response->assertJsonCount(1, 'timelines');
        $response->assertJsonCount(2, 'moods');
    }

    public function test_search_sorts_results(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);

        Scene::factory()->create(['timeline_id' => $timeline->id, 'title' => 'Zebra', 'order' => 1]);
        Scene::factory()->create(['timeline_id' => $timeline->id, 'title' => 'Apple', 'order' => 2]);

        $response = $this->actingAs($user)->getJson(route('universes.search', [
            'universe' => $universe->id,
            'sort' => 'title',
            'sort_dir' => 'asc',
        ]));

        $response->assertOk();
        $results = $response->json('results');
        $this->assertEquals('Apple', $results[0]['title']);
        $this->assertEquals('Zebra', $results[1]['title']);
    }

    public function test_user_can_access_search_page(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('universes.search.index', $universe));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Universe/Search')
            ->has('universe')
        );
    }
}
