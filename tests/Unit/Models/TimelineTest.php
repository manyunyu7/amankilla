<?php

namespace Tests\Unit\Models;

use App\Models\Scene;
use App\Models\Timeline;
use App\Models\Universe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_belongs_to_universe(): void
    {
        $universe = Universe::factory()->create();
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);

        $this->assertTrue($timeline->universe->is($universe));
    }

    public function test_timeline_has_many_scenes_ordered(): void
    {
        $timeline = Timeline::factory()->create();
        $scene3 = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 3]);
        $scene1 = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 1]);
        $scene2 = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 2]);

        $scenes = $timeline->scenes;

        $this->assertCount(3, $scenes);
        $this->assertEquals(1, $scenes->first()->order);
        $this->assertEquals(3, $scenes->last()->order);
    }

    public function test_timeline_can_be_canon(): void
    {
        $timeline = Timeline::factory()->canon()->create();

        $this->assertTrue($timeline->is_canon);
        $this->assertEquals('Canon', $timeline->name);
    }

    public function test_timeline_can_branch_from_scene(): void
    {
        $originalTimeline = Timeline::factory()->create();
        $branchPoint = Scene::factory()->branchPoint()->create(['timeline_id' => $originalTimeline->id]);

        $branchedTimeline = Timeline::factory()->branchedFrom($branchPoint)->create();

        $this->assertTrue($branchedTimeline->branchFrom->is($branchPoint));
        $this->assertEquals($originalTimeline->universe_id, $branchedTimeline->universe_id);
    }

    public function test_timeline_can_get_first_scene(): void
    {
        $timeline = Timeline::factory()->create();
        $firstScene = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 1]);
        Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 2]);

        $this->assertTrue($timeline->firstScene()->is($firstScene));
    }

    public function test_timeline_can_get_last_scene(): void
    {
        $timeline = Timeline::factory()->create();
        Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 1]);
        $lastScene = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 2]);

        $this->assertTrue($timeline->lastScene()->is($lastScene));
    }

    public function test_timeline_casts_is_canon_as_boolean(): void
    {
        $timeline = Timeline::factory()->create(['is_canon' => 1]);

        $this->assertIsBool($timeline->is_canon);
    }
}
