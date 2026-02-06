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

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_export_universe_as_json(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id, 'name' => 'Test Universe']);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        Scene::factory()->count(2)->create(['timeline_id' => $timeline->id]);

        $response = $this->actingAs($user)->get(route('universes.export.json', $universe));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename="Test_Universe_backup.json"');

        $data = $response->json();
        $this->assertEquals('1.0', $data['export_version']);
        $this->assertEquals('Test Universe', $data['universe']['name']);
        $this->assertCount(1, $data['timelines']);
        $this->assertCount(2, $data['timelines'][0]['scenes']);
    }

    public function test_user_can_export_universe_as_markdown(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id, 'name' => 'My Story']);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id, 'name' => 'Main Timeline']);
        Scene::factory()->create(['timeline_id' => $timeline->id, 'title' => 'Chapter One']);

        $response = $this->actingAs($user)->get(route('universes.export.markdown', $universe));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('# My Story', $content);
        $this->assertStringContainsString('## Main Timeline', $content);
        $this->assertStringContainsString('### Chapter One', $content);
    }

    public function test_user_cannot_export_others_private_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id, 'is_public' => false]);

        $response = $this->actingAs($user)->get(route('universes.export.json', $universe));

        $response->assertForbidden();
    }

    public function test_user_can_export_timeline_as_json(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id, 'name' => 'Canon']);
        Scene::factory()->count(3)->create(['timeline_id' => $timeline->id]);

        $response = $this->actingAs($user)->get(route('timelines.export.json', $timeline));

        $response->assertOk();

        $data = $response->json();
        $this->assertEquals('Canon', $data['name']);
        $this->assertCount(3, $data['scenes']);
    }

    public function test_user_can_export_timeline_as_markdown(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id, 'name' => 'Alternate', 'is_canon' => true]);
        Scene::factory()->create(['timeline_id' => $timeline->id, 'title' => 'Scene One']);

        $response = $this->actingAs($user)->get(route('timelines.export.markdown', $timeline));

        $response->assertOk();

        $content = $response->streamedContent();
        $this->assertStringContainsString('## Alternate (Canon)', $content);
        $this->assertStringContainsString('### Scene One', $content);
    }

    public function test_user_can_export_scene_as_json(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create([
            'timeline_id' => $timeline->id,
            'title' => 'The Meeting',
            'mood' => 'romantic',
        ]);

        $response = $this->actingAs($user)->get(route('scenes.export.json', $scene));

        $response->assertOk();

        $data = $response->json();
        $this->assertEquals('The Meeting', $data['title']);
        $this->assertEquals('romantic', $data['mood']);
    }

    public function test_user_can_export_scene_as_markdown(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create([
            'timeline_id' => $timeline->id,
            'title' => 'The Goodbye',
            'location' => 'Train Station',
            'mood' => 'sad',
        ]);

        $response = $this->actingAs($user)->get(route('scenes.export.markdown', $scene));

        $response->assertOk();

        $content = $response->streamedContent();
        $this->assertStringContainsString('# The Goodbye', $content);
        $this->assertStringContainsString('**Location:** Train Station', $content);
        $this->assertStringContainsString('**Mood:** sad', $content);
    }

    public function test_export_includes_characters_and_tags(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $character = Character::factory()->create(['universe_id' => $universe->id, 'name' => 'Hero']);
        $tag = Tag::factory()->create(['universe_id' => $universe->id, 'name' => 'action']);
        $scene = Scene::factory()->create(['timeline_id' => $timeline->id]);
        $scene->characters()->attach($character->id);
        $scene->tags()->attach($tag->id);

        $response = $this->actingAs($user)->get(route('universes.export.json', $universe));

        $data = $response->json();
        $this->assertEquals('Hero', $data['characters'][0]['name']);
        $this->assertContains('Hero', $data['timelines'][0]['scenes'][0]['characters']);
        $this->assertContains('action', $data['timelines'][0]['scenes'][0]['tags']);
    }

    public function test_export_sanitizes_filename(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create([
            'user_id' => $user->id,
            'name' => 'My Story: "A Journey" <Part 1>',
        ]);

        $response = $this->actingAs($user)->get(route('universes.export.json', $universe));

        $response->assertOk();
        // Filename should have special characters replaced
        $this->assertStringContainsString('My_Story', $response->headers->get('Content-Disposition'));
    }

    public function test_guest_cannot_export(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->get(route('universes.export.json', $universe));

        $response->assertRedirect(route('login'));
    }

    public function test_markdown_export_includes_branch_points(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create([
            'timeline_id' => $timeline->id,
            'title' => 'Decision Point',
            'is_branch_point' => true,
            'branch_question' => 'What if they chose differently?',
        ]);

        $response = $this->actingAs($user)->get(route('scenes.export.markdown', $scene));

        $content = $response->streamedContent();
        $this->assertStringContainsString('**Branch Point**', $content);
        $this->assertStringContainsString('What if they chose differently?', $content);
    }
}
