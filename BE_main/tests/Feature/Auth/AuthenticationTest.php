<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['message', 'token', 'user']);
    }

    public function test_unverified_users_cannot_authenticate(): void
    {
        $user = User::factory()->unverified()->create();
        Notification::fake();

        $response = $this->postJson('/api/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response->assertStatus(409);
        $response->assertJson([
            'verification_required' => true,
        ]);
        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
    }

    public function test_unverified_users_only_receive_one_verification_email_within_five_minutes(): void
    {
        $user = User::factory()->unverified()->create();
        Notification::fake();

        $this->postJson('/api/login', [
            'username' => $user->username,
            'password' => 'password',
        ])->assertStatus(409);

        $this->postJson('/api/login', [
            'username' => $user->username,
            'password' => 'password',
        ])->assertStatus(409);

        Notification::assertSentToTimes($user, \Illuminate\Auth\Notifications\VerifyEmail::class, 1);

        $this->travel(6)->minutes();

        $this->postJson('/api/login', [
            'username' => $user->username,
            'password' => 'password',
        ])->assertStatus(409);

        Notification::assertSentToTimes($user, \Illuminate\Auth\Notifications\VerifyEmail::class, 2);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'username' => $user->username,
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertNoContent();
    }
}
