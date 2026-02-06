<?php

namespace Tests\Unit\Models;

use App\Models\Character;
use App\Models\Scene;
use App\Models\Tag;
use App\Models\Timeline;
use App\Models\Universe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniverseTest extends TestCase
{
    use RefreshDatabase;

    public function test_universe_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($universe->user->is($user));
    }

    public function test_universe_has_many_timelines(): void
    {
        $universe = Universe::factory()->create();
        $timelines = Timeline::factory()->count(3)->create(['universe_id' => $universe->id]);

        $this->assertCount(3, $universe->timelines);
    }

    public function test_universe_has_many_characters(): void
    {
        $universe = Universe::factory()->create();
        $characters = Character::factory()->count(2)->create(['universe_id' => $universe->id]);

        $this->assertCount(2, $universe->characters);
    }

    public function test_universe_has_many_tags(): void
    {
        $universe = Universe::factory()->create();
        $tags = Tag::factory()->count(5)->create(['universe_id' => $universe->id]);

        $this->assertCount(5, $universe->tags);
    }

    public function test_universe_can_get_canon_timeline(): void
    {
        $universe = Universe::factory()->create();
        Timeline::factory()->create(['universe_id' => $universe->id, 'is_canon' => false]);
        $canon = Timeline::factory()->canon()->create(['universe_id' => $universe->id]);

        $this->assertTrue($universe->canonTimeline()->is($canon));
    }

    public function test_universe_can_be_public(): void
    {
        $universe = Universe::factory()->public()->create();

        $this->assertTrue($universe->is_public);
    }

    public function test_universe_can_be_forkable(): void
    {
        $universe = Universe::factory()->forkable()->create();

        $this->assertTrue($universe->is_public);
        $this->assertTrue($universe->allow_fork);
    }

    public function test_universe_casts_booleans(): void
    {
        $universe = Universe::factory()->create([
            'is_public' => 1,
            'allow_fork' => 1,
        ]);

        $this->assertIsBool($universe->is_public);
        $this->assertIsBool($universe->allow_fork);
    }
}
