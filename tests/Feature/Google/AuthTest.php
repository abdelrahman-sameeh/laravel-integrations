<?php

namespace Tests\Feature\Google;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function testRedirectsToGoogle(): void
    {
        Socialite::fake('google');

        $this->get('/api/auth/google')
            ->assertRedirect('https://socialite.fake/google/authorize');
    }

    public function testCallbackCreatesNewUser(): void
    {
        Socialite::fake('google', GoogleUser::fake([
            'id' => 'google-123',
            'name' => 'Google User',
            'email' => 'google@example.com',
        ]));

        $this->getJson('/api/auth/google/callback')
            ->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token' => ['access_token', 'refresh_token'],
                ],
            ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'name' => 'Google User',
            'email' => 'google@example.com',
            'google_id' => 'google-123',
            'password' => null,
        ]);
    }

    public function testCallbackLinksExistingUserByEmail(): void
    {
        $user = User::factory()->create([
            'email' => 'google@example.com',
            'google_id' => null,
        ]);
        Socialite::fake('google', GoogleUser::fake([
            'id' => 'google-123',
            'name' => 'Google User',
            'email' => 'google@example.com',
        ]));

        $this->getJson('/api/auth/google/callback')
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['user', 'token'],
            ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertSame('google-123', $user->fresh()->google_id);
    }
}
