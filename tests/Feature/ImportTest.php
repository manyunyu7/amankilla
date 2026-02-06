<?php

namespace Tests\Feature;

use App\Models\Universe;
use App\Models\User;
use App\Services\RawParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    protected string $sampleContent = <<<'EOT'
Feb 2
🚂 Bandung → Garut: An INFJ × INFP Story
MALAM SEBELUMNYA
21.47 — INFJ di kos, Bandung

Duduk di kasur, bantal dipangku. HP di tangan.

Inner monologue INFJ: "Kereta pertama jam 7 pagi... kalau berangkat jam segitu, kita sampai jam setengah 9an."

22.13 — Chat

INFJ: Udah di mana btw?

INFP: Baru lewat Cileunyi kayaknya

INFJ: Haha iya jam segini mah emang gelap

Inner monologue INFP: scrolling Spotify, earphone satu telinga copot. Senyum sendiri.

🚂 Pagi Berangkat
06.34 — INFJ di depan penginapan INFP

INFJ: "Ayo, keburu."

INFP: "Hehe, sori sori."

Inner monologue INFJ: "...berantakan tapi lucu juga."
EOT;

    public function test_raw_parser_can_parse_scenes(): void
    {
        $parser = new RawParser();
        $scenes = $parser->parse($this->sampleContent);

        $this->assertCount(2, $scenes);
        $this->assertEquals('Bandung → Garut: An INFJ × INFP Story', $scenes[0]['title']);
        $this->assertEquals('Pagi Berangkat', $scenes[1]['title']);
    }

    public function test_raw_parser_extracts_characters(): void
    {
        $parser = new RawParser();
        $scenes = $parser->parse($this->sampleContent);

        $this->assertContains('INFJ', $scenes[0]['characters']);
        $this->assertContains('INFP', $scenes[0]['characters']);
    }

    public function test_raw_parser_extracts_location(): void
    {
        $parser = new RawParser();
        $scenes = $parser->parse($this->sampleContent);

        // Location can be extracted from title - Bandung is detected
        $this->assertNotNull($scenes[0]['location']);
        $this->assertStringContainsString('Bandung', $scenes[0]['location']);
    }

    public function test_raw_parser_generates_tags(): void
    {
        $parser = new RawParser();
        $scenes = $parser->parse($this->sampleContent);

        // Should have location tags
        $this->assertContains('Bandung', $scenes[0]['tags']);
        $this->assertContains('Garut', $scenes[0]['tags']);
    }

    public function test_raw_parser_detects_mood(): void
    {
        $parser = new RawParser();
        $scenes = $parser->parse($this->sampleContent);

        // Based on content keywords
        $this->assertNotNull($scenes[0]['mood']);
    }

    public function test_raw_parser_generates_stats(): void
    {
        $parser = new RawParser();
        $stats = $parser->getStats($this->sampleContent);

        $this->assertArrayHasKey('total_scenes', $stats);
        $this->assertArrayHasKey('characters', $stats);
        $this->assertArrayHasKey('locations', $stats);
        $this->assertEquals(2, $stats['total_scenes']);
    }

    public function test_user_can_access_import_page(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('universes.import.index', $universe));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Universe/Import')
            ->has('universe')
        );
    }

    public function test_user_cannot_access_others_import_page(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('universes.import.index', $universe));

        $response->assertForbidden();
    }

    public function test_user_can_preview_import(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson(route('universes.import.preview', $universe), [
            'content' => $this->sampleContent,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'scenes',
            'stats',
        ]);
        $this->assertCount(2, $response->json('scenes'));
    }

    public function test_user_can_import_content(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson(route('universes.import', $universe), [
            'content' => $this->sampleContent,
            'timeline_name' => 'Test Timeline',
            'timeline_description' => 'Imported from raw.md',
            'is_canon' => true,
            'create_characters' => true,
            'create_tags' => true,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'result' => ['timeline', 'scenes_count', 'characters_count', 'tags_count'],
        ]);

        // Verify data was created
        $this->assertDatabaseHas('timelines', [
            'universe_id' => $universe->id,
            'name' => 'Test Timeline',
            'is_canon' => true,
        ]);

        $this->assertEquals(2, $universe->timelines()->first()->scenes()->count());
    }

    public function test_import_creates_characters(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->postJson(route('universes.import', $universe), [
            'content' => $this->sampleContent,
            'timeline_name' => 'Test Timeline',
            'create_characters' => true,
            'create_tags' => false,
        ]);

        $this->assertDatabaseHas('characters', [
            'universe_id' => $universe->id,
            'name' => 'INFJ',
        ]);
        $this->assertDatabaseHas('characters', [
            'universe_id' => $universe->id,
            'name' => 'INFP',
        ]);
    }

    public function test_import_creates_tags(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->postJson(route('universes.import', $universe), [
            'content' => $this->sampleContent,
            'timeline_name' => 'Test Timeline',
            'create_characters' => false,
            'create_tags' => true,
        ]);

        $this->assertDatabaseHas('tags', [
            'universe_id' => $universe->id,
            'name' => 'Bandung',
        ]);
    }

    public function test_import_fails_with_empty_content(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson(route('universes.import', $universe), [
            'content' => 'No scenes here, just plain text without any markers.',
            'timeline_name' => 'Test Timeline',
        ]);

        $response->assertStatus(422);
    }

    public function test_import_reuses_existing_characters(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        // Create existing character
        $universe->characters()->create([
            'name' => 'INFJ',
            'color' => '#FF0000',
        ]);

        $this->actingAs($user)->postJson(route('universes.import', $universe), [
            'content' => $this->sampleContent,
            'timeline_name' => 'Test Timeline',
            'create_characters' => true,
            'create_tags' => false,
        ]);

        // Should still only have one INFJ
        $this->assertEquals(1, $universe->characters()->where('name', 'INFJ')->count());
        // But should have 2 total (INFJ + INFP)
        $this->assertEquals(2, $universe->characters()->count());
    }

    public function test_user_can_get_import_stats(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson(route('universes.import.stats', $universe), [
            'content' => $this->sampleContent,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'total_scenes',
            'characters',
            'locations',
            'tags',
        ]);
    }
}
