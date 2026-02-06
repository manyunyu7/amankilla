<?php

namespace Tests\Unit\Models;

use App\Models\Universe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_universes(): void
    {
        $user = User::factory()->create();
        Universe::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->universes);
    }

    public function test_user_has_username(): void
    {
        $user = User::factory()->create(['username' => 'testuser123']);

        $this->assertEquals('testuser123', $user->username);
    }

    public function test_user_has_avatar_url(): void
    {
        $user = User::factory()->create(['avatar_url' => 'https://example.com/avatar.jpg']);

        $this->assertEquals('https://example.com/avatar.jpg', $user->avatar_url);
    }

    public function test_user_password_is_hidden(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
    }

    public function test_user_remember_token_is_hidden(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => 'plain-password']);

        $this->assertNotEquals('plain-password', $user->password);
    }

    public function test_username_is_unique(): void
    {
        User::factory()->create(['username' => 'uniqueuser']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['username' => 'uniqueuser']);
    }

    public function test_email_is_unique(): void
    {
        User::factory()->create(['email' => 'unique@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => 'unique@example.com']);
    }
}
