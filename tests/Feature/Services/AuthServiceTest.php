<?php

namespace Tests\Feature\Services;

use App\Http\Services\AuthService;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;


class AuthServiceTest extends TestCase
{
  use RefreshDatabase;

  public function testGenerateTokens(): void
  {
    $user = User::factory()->create();
    $authService = new AuthService();
    $tokens = $authService->generateToken($user);

    $this->assertArrayHasKey('access_token', $tokens);
    $this->assertArrayHasKey('refresh_token', $tokens);
    $this->assertDatabaseHas('personal_access_tokens', [
      'tokenable_id' => $user->id,
      'name' => 'access_token',
    ]);
    $this->assertDatabaseHas('refresh_tokens', [
      'user_id' => $user->id,
    ]);
  }

  public function testRefreshTokenIsStoredHashed(): void
  {
    $user = User::factory()->create();
    $authService = new AuthService();

    $tokens = $authService->generateToken($user);
    $storedToken = $user->refreshTokens()->firstOrFail();

    $this->assertNotSame($tokens['refresh_token'], $storedToken->hash_token);
    $this->assertTrue(Hash::check(
      $tokens['refresh_token'],
      $storedToken->hash_token
    ));
  }

  public function testGeneratingTokensDeletesOldRefreshToken(): void
  {
    $user = User::factory()->create();
    $oldToken = RefreshToken::create([
      'user_id' => $user->id,
      'hash_token' => Hash::make(Str::random(64)),
      'expires_at' => now()->addDays(30),
    ]);
    $authService = new AuthService();

    $tokens = $authService->generateToken($user);

    $this->assertDatabaseMissing('refresh_tokens', [
      'id' => $oldToken->id,
    ]);
    $this->assertCount(1, $user->refreshTokens()->get());
    $this->assertTrue(Hash::check(
      $tokens['refresh_token'],
      $user->refreshTokens()->firstOrFail()->hash_token
    ));
  }

  public function testGetUserWithTokenReturnsUserAndTokens(): void
  {
    $user = User::factory()->create();
    $authService = new AuthService();

    $result = $authService->getUserWithToken($user);

    $this->assertSame(
      $user->only('id', 'name', 'email'),
      $result['user']
    );
    $this->assertArrayHasKey('access_token', $result['token']);
    $this->assertArrayHasKey('refresh_token', $result['token']);
  }
}
