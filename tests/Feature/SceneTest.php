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

class SceneTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_scene_for_own_timeline(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->post(route('timelines.scenes.store', $timeline), [
            'title' => 'First Scene',
            'summary' => 'This is what happens',
            'location' => 'Coffee Shop',
            'mood' => 'peaceful',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('scenes', [
            'title' => 'First Scene',
            'timeline_id' => $timeline->id,
            'order' => 1,
        ]);
    }

    public function test_user_cannot_create_scene_for_others_timeline(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->post(route('timelines.scenes.store', $timeline), [
            'title' => 'Hacked Scene',
        ]);

        $response->assertStatus(403);
    }

    public function test_scene_title_is_required(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->post(route('timelines.scenes.store', $timeline), [
            'summary' => 'No title provided',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_scenes_are_ordered_sequentially(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);

        $this->actingAs($user)->post(route('timelines.scenes.store', $timeline), [
            'title' => 'Scene 1',
        ]);

        $this->actingAs($user)->post(route('timelines.scenes.store', $timeline), [
            'title' => 'Scene 2',
        ]);

        $this->assertDatabaseHas('scenes', ['title' => 'Scene 1', 'order' => 1]);
        $this->assertDatabaseHas('scenes', ['title' => 'Scene 2', 'order' => 2]);
    }

    public function test_user_can_view_own_scene(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create(['timeline_id' => $timeline->id]);

        $response = $this->actingAs($user)->get(route('scenes.show', $scene));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Scene/Show')
            ->has('scene')
            ->has('timeline')
            ->has('universe')
        );
    }

    public function test_user_cannot_view_scene_in_others_private_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create([
            'user_id' => $otherUser->id,
            'is_public' => false,
        ]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create(['timeline_id' => $timeline->id]);

        $response = $this->actingAs($user)->get(route('scenes.show', $scene));

        $response->assertStatus(403);
    }

    public function test_user_can_update_own_scene(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create(['timeline_id' => $timeline->id]);

        $response = $this->actingAs($user)->put(route('scenes.update', $scene), [
            'title' => 'Updated Title',
            'content' => '<p>New content here</p>',
            'mood' => 'happy',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('scenes', [
            'id' => $scene->id,
            'title' => 'Updated Title',
            'mood' => 'happy',
        ]);
    }

    public function test_user_cannot_update_scene_in_others_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create(['timeline_id' => $timeline->id]);

        $response = $this->actingAs($user)->put(route('scenes.update', $scene), [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_scene(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create(['timeline_id' => $timeline->id]);

        $response = $this->actingAs($user)->delete(route('scenes.destroy', $scene));

        $response->assertRedirect(route('timelines.show', $timeline->id));
        $this->assertDatabaseMissing('scenes', ['id' => $scene->id]);
    }

    public function test_user_cannot_delete_scene_in_others_universe(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create(['timeline_id' => $timeline->id]);

        $response = $this->actingAs($user)->delete(route('scenes.destroy', $scene));

        $response->assertStatus(403);
        $this->assertDatabaseHas('scenes', ['id' => $scene->id]);
    }

    public function test_scene_can_sync_characters(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create(['timeline_id' => $timeline->id]);
        $characters = Character::factory()->count(2)->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->put(route('scenes.update', $scene), [
            'title' => $scene->title,
            'character_ids' => $characters->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();
        $this->assertCount(2, $scene->fresh()->characters);
    }

    public function test_scene_can_sync_tags(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create(['timeline_id' => $timeline->id]);
        $tags = Tag::factory()->count(3)->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->put(route('scenes.update', $scene), [
            'title' => $scene->title,
            'tag_ids' => $tags->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();
        $this->assertCount(3, $scene->fresh()->tags);
    }

    public function test_user_can_reorder_scenes(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene1 = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 1]);
        $scene2 = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 2]);
        $scene3 = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 3]);

        $response = $this->actingAs($user)->post(route('timelines.scenes.reorder', $timeline), [
            'scene_ids' => [$scene3->id, $scene1->id, $scene2->id],
        ]);

        $response->assertRedirect();
        $this->assertEquals(1, $scene3->fresh()->order);
        $this->assertEquals(2, $scene1->fresh()->order);
        $this->assertEquals(3, $scene2->fresh()->order);
    }

    public function test_scene_index_shows_timeline_scenes(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        Scene::factory()->count(5)->create(['timeline_id' => $timeline->id]);

        $response = $this->actingAs($user)->get(route('timelines.scenes.index', $timeline));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Timeline/Scenes')
            ->has('scenes', 5)
        );
    }

    public function test_scene_index_provides_all_tags_for_filtering(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        Tag::factory()->count(3)->create(['universe_id' => $universe->id]);

        $response = $this->actingAs($user)->get(route('timelines.scenes.index', $timeline));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Timeline/Scenes')
            ->has('allTags', 3)
        );
    }

    public function test_word_count_is_updated_on_content_change(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create(['timeline_id' => $timeline->id, 'content' => null]);

        $this->actingAs($user)->put(route('scenes.update', $scene), [
            'title' => $scene->title,
            'content' => '<p>One two three four five six seven eight nine ten.</p>',
        ]);

        $this->assertEquals(10, $scene->fresh()->word_count);
    }

    public function test_user_can_toggle_branch_point(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create([
            'timeline_id' => $timeline->id,
            'is_branch_point' => false,
        ]);

        $response = $this->actingAs($user)->patch(route('scenes.toggle-branch-point', $scene), [
            'is_branch_point' => true,
            'branch_question' => 'What if they never met?',
        ]);

        $response->assertRedirect();
        $this->assertTrue($scene->fresh()->is_branch_point);
        $this->assertEquals('What if they never met?', $scene->fresh()->branch_question);
    }

    public function test_user_cannot_toggle_branch_point_on_others_scene(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create(['timeline_id' => $timeline->id]);

        $response = $this->actingAs($user)->patch(route('scenes.toggle-branch-point', $scene), [
            'is_branch_point' => true,
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_create_branch_from_scene(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 1]);

        $response = $this->actingAs($user)->post(route('scenes.create-branch', $scene), [
            'name' => 'Alternate Timeline',
            'description' => 'What if things went differently?',
            'color' => '#FF5733',
        ]);

        $response->assertRedirect();

        // Verify scene is now a branch point
        $this->assertTrue($scene->fresh()->is_branch_point);

        // Verify new timeline was created
        $this->assertDatabaseHas('timelines', [
            'universe_id' => $universe->id,
            'name' => 'Alternate Timeline',
            'branch_from_id' => $scene->id,
            'is_canon' => false,
        ]);
    }

    public function test_user_can_create_branch_with_copied_scenes(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene1 = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 1, 'title' => 'Scene 1']);
        $scene2 = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 2, 'title' => 'Scene 2']);
        $scene3 = Scene::factory()->create(['timeline_id' => $timeline->id, 'order' => 3, 'title' => 'Scene 3']);

        $response = $this->actingAs($user)->post(route('scenes.create-branch', $scene1), [
            'name' => 'Alternate Timeline',
            'copy_subsequent_scenes' => true,
        ]);

        $response->assertRedirect();

        // Verify new timeline has the subsequent scenes copied
        $newTimeline = Timeline::where('branch_from_id', $scene1->id)->first();
        $this->assertNotNull($newTimeline);
        $this->assertEquals(2, $newTimeline->scenes()->count());
        $this->assertEquals(['Scene 2', 'Scene 3'], $newTimeline->scenes()->pluck('title')->toArray());
    }

    public function test_user_cannot_create_branch_on_others_scene(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $otherUser->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create(['timeline_id' => $timeline->id]);

        $response = $this->actingAs($user)->post(route('scenes.create-branch', $scene), [
            'name' => 'Alternate Timeline',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_get_branches_from_scene(): void
    {
        $user = User::factory()->create();
        $universe = Universe::factory()->create(['user_id' => $user->id]);
        $timeline = Timeline::factory()->create(['universe_id' => $universe->id]);
        $scene = Scene::factory()->create([
            'timeline_id' => $timeline->id,
            'is_branch_point' => true,
        ]);

        // Create branch timelines
        Timeline::factory()->create([
            'universe_id' => $universe->id,
            'branch_from_id' => $scene->id,
            'name' => 'Branch 1',
        ]);
        Timeline::factory()->create([
            'universe_id' => $universe->id,
            'branch_from_id' => $scene->id,
            'name' => 'Branch 2',
        ]);

        $response = $this->actingAs($user)->getJson(route('scenes.branches', $scene));

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['name' => 'Branch 1']);
        $response->assertJsonFragment(['name' => 'Branch 2']);
    }
}
