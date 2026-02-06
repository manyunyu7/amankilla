<?php

namespace Tests\Unit\Models;

use App\Models\Character;
use App\Models\Scene;
use App\Models\Tag;
use App\Models\Timeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SceneTest extends TestCase
{
    use RefreshDatabase;

    public function test_scene_belongs_to_timeline(): void
    {
        $timeline = Timeline::factory()->create();
        $scene = Scene::factory()->create(['timeline_id' => $timeline->id]);

        $this->assertTrue($scene->timeline->is($timeline));
    }

    public function test_scene_has_many_characters(): void
    {
        $scene = Scene::factory()->create();
        $characters = Character::factory()->count(2)->create([
            'universe_id' => $scene->timeline->universe_id,
        ]);

        $scene->characters()->attach($characters);

        $this->assertCount(2, $scene->characters);
    }

    public function test_scene_has_many_tags(): void
    {
        $scene = Scene::factory()->create();
        $tags = Tag::factory()->count(3)->create([
            'universe_id' => $scene->timeline->universe_id,
        ]);

        $scene->tags()->attach($tags);

        $this->assertCount(3, $scene->tags);
    }

    public function test_scene_can_be_branch_point(): void
    {
        $scene = Scene::factory()->branchPoint('What if they never met?')->create();

        $this->assertTrue($scene->is_branch_point);
        $this->assertEquals('What if they never met?', $scene->branch_question);
    }

    public function test_scene_can_have_branched_timelines(): void
    {
        $timeline = Timeline::factory()->create();
        $scene = Scene::factory()->branchPoint()->create(['timeline_id' => $timeline->id]);
        $branchedTimeline = Timeline::factory()->branchedFrom($scene)->create();

        $this->assertCount(1, $scene->branchedTimelines);
        $this->assertTrue($scene->branchedTimelines->first()->is($branchedTimeline));
    }

    public function test_scene_can_get_universe(): void
    {
        $scene = Scene::factory()->create();

        $this->assertTrue($scene->universe->is($scene->timeline->universe));
    }

    public function test_scene_can_get_previous_scene(): void
    {
        $timeline = Timeline::factory()->create();
        $scene1 = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 1]);
        $scene2 = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 2]);

        $this->assertTrue($scene2->previousScene()->is($scene1));
        $this->assertNull($scene1->previousScene());
    }

    public function test_scene_can_get_next_scene(): void
    {
        $timeline = Timeline::factory()->create();
        $scene1 = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 1]);
        $scene2 = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 2]);

        $this->assertTrue($scene1->nextScene()->is($scene2));
        $this->assertNull($scene2->nextScene());
    }

    public function test_scene_can_update_word_count(): void
    {
        $scene = Scene::factory()->create([
            'content' => 'This is a test content with exactly ten words here.',
            'word_count' => 0,
        ]);

        $scene->updateWordCount();

        $this->assertEquals(10, $scene->fresh()->word_count);
    }

    public function test_scene_can_have_specific_mood(): void
    {
        $scene = Scene::factory()->mood('romantic')->create();

        $this->assertEquals('romantic', $scene->mood);
    }

    public function test_scene_casts_attributes_correctly(): void
    {
        $scene = Scene::factory()->create([
            'order' => '5',
            'word_count' => '100',
            'is_branch_point' => 1,
        ]);

        $this->assertIsInt($scene->order);
        $this->assertIsInt($scene->word_count);
        $this->assertIsBool($scene->is_branch_point);
    }
}
