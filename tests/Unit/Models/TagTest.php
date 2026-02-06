<?php

namespace Tests\Unit\Models;

use App\Models\Scene;
use App\Models\Tag;
use App\Models\Universe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_belongs_to_universe(): void
    {
        $universe = Universe::factory()->create();
        $tag = Tag::factory()->create(['universe_id' => $universe->id]);

        $this->assertTrue($tag->universe->is($universe));
    }

    public function test_tag_has_many_scenes(): void
    {
        $universe = Universe::factory()->create();
        $tag = Tag::factory()->create(['universe_id' => $universe->id]);
        $scenes = Scene::factory()->count(3)->create([
            'timeline_id' => $universe->timelines()->create([
                'name' => 'Canon',
                'is_canon' => true,
            ])->id,
        ]);

        $tag->scenes()->attach($scenes);

        $this->assertCount(3, $tag->scenes);
    }

    public function test_tag_can_be_emotion_category(): void
    {
        $tag = Tag::factory()->emotion()->create();

        $this->assertEquals('emotion', $tag->category);
    }

    public function test_tag_can_be_event_category(): void
    {
        $tag = Tag::factory()->event()->create();

        $this->assertEquals('event', $tag->category);
    }

    public function test_tag_can_be_theme_category(): void
    {
        $tag = Tag::factory()->theme()->create();

        $this->assertEquals('theme', $tag->category);
    }

    public function test_tag_scope_filters_by_category(): void
    {
        $universe = Universe::factory()->create();
        Tag::factory()->create(['universe_id' => $universe->id, 'category' => 'emotion']);
        Tag::factory()->create(['universe_id' => $universe->id, 'category' => 'event']);
        Tag::factory()->create(['universe_id' => $universe->id, 'category' => 'event']);

        $emotionTags = Tag::category('emotion')->get();
        $eventTags = Tag::category('event')->get();

        $this->assertCount(1, $emotionTags);
        $this->assertCount(2, $eventTags);
    }

    public function test_tag_name_is_unique_within_universe(): void
    {
        $universe = Universe::factory()->create();
        Tag::factory()->create(['universe_id' => $universe->id, 'name' => 'unique-tag']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Tag::factory()->create(['universe_id' => $universe->id, 'name' => 'unique-tag']);
    }

    public function test_same_tag_name_can_exist_in_different_universes(): void
    {
        $universe1 = Universe::factory()->create();
        $universe2 = Universe::factory()->create();

        $tag1 = Tag::factory()->create(['universe_id' => $universe1->id, 'name' => 'shared-tag']);
        $tag2 = Tag::factory()->create(['universe_id' => $universe2->id, 'name' => 'shared-tag']);

        $this->assertEquals($tag1->name, $tag2->name);
        $this->assertNotEquals($tag1->id, $tag2->id);
    }
}
