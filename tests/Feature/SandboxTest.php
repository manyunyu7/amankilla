<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SandboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_sandbox_page_requires_authentication(): void
    {
        $response = $this->get('/sandbox');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_sandbox(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/sandbox');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Sandbox'));
    }
}
