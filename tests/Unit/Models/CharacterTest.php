<?php

namespace Tests\Unit\Models;

use App\Models\Character;
use App\Models\Scene;
use App\Models\Universe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterTest extends TestCase
{
    use RefreshDatabase;

    public function test_character_belongs_to_universe(): void
    {
        $universe = Universe::factory()->create();
        $character = Character::factory()->create(['universe_id' => $universe->id]);

        $this->assertTrue($character->universe->is($universe));
    }

    public function test_character_has_many_scenes(): void
    {
        $universe = Universe::factory()->create();
        $character = Character::factory()->create(['universe_id' => $universe->id]);
        $scenes = Scene::factory()->count(2)->create([
            'timeline_id' => $universe->timelines()->create([
                'name' => 'Canon',
                'is_canon' => true,
            ])->id,
        ]);

        $character->scenes()->attach($scenes);

        $this->assertCount(2, $character->scenes);
    }

    public function test_character_can_be_infj(): void
    {
        $character = Character::factory()->infj()->create();

        $this->assertEquals('INFJ', $character->type);
        $this->assertContains('empathetic', $character->traits);
    }

    public function test_character_can_be_infp(): void
    {
        $character = Character::factory()->infp()->create();

        $this->assertEquals('INFP', $character->type);
        $this->assertContains('creative', $character->traits);
    }

    public function test_character_display_name_returns_nickname_when_set(): void
    {
        $character = Character::factory()->create([
            'name' => 'John Doe',
            'nickname' => 'Johnny',
        ]);

        $this->assertEquals('Johnny', $character->display_name);
    }

    public function test_character_display_name_returns_name_when_no_nickname(): void
    {
        $character = Character::factory()->create([
            'name' => 'John Doe',
            'nickname' => null,
        ]);

        $this->assertEquals('John Doe', $character->display_name);
    }

    public function test_character_traits_is_cast_to_array(): void
    {
        $traits = ['caring', 'analytical', 'creative'];
        $character = Character::factory()->create(['traits' => $traits]);

        $this->assertIsArray($character->traits);
        $this->assertEquals($traits, $character->traits);
    }
}
