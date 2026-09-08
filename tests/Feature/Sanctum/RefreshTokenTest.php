<?php

namespace Tests\Feature;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RefreshTokenTest extends TestCase
{
  use RefreshDatabase;
  private $url = '/api/refresh';

  public function testUserCanRefreshAccessTokenWithValidRefreshToken()
  {
    $user = User::factory()->create();

    $refreshToken = Str::random(64);

    RefreshToken::create([
      'user_id' => $user->id,
      'hash_token' => Hash::make($refreshToken),
      'expires_at' => now()->addDays(30),
    ]);

    $response = $this->actingAs($user)
      ->postJson($this->url, [
        'refresh_token' => $refreshToken,
      ]);

    $response
      ->assertStatus(200)
      ->assertJsonStructure([
        'access_token',
        'refresh_token',
      ]);
  }

  public function testUserCannotRefreshWithInvalidRefreshToken()
  {
    $user = User::factory()->create();

    RefreshToken::create([
      'user_id' => $user->id,
      'hash_token' => Hash::make(Str::random(64)),
      'expires_at' => now()->addDays(30),
    ]);

    $response = $this->actingAs($user)
      ->postJson($this->url, [
        'refresh_token' => 'wrong-token',
      ]);

    $response
      ->assertStatus(401)
      ->assertJson([
        'message' => 'Invalid or expired refresh token',
      ]);
  }

  public function testUserCannotRefreshWithExpiredRefreshToken()
  {
    $user = User::factory()->create();

    $refreshToken = Str::random(64);

    RefreshToken::create([
      'user_id' => $user->id,
      'hash_token' => Hash::make($refreshToken),
      'expires_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($user)
      ->postJson($this->url, [
        'refresh_token' => $refreshToken,
      ]);

    $response
      ->assertStatus(401)
      ->assertJson([
        'message' => 'Invalid or expired refresh token',
      ]);
  }

  public function testRefreshTokenIsRequired()
  {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
      ->postJson($this->url, []);

    $response->assertStatus(422);
  }

  public function testRefreshTokenMustBeString()
  {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
      ->postJson($this->url, [
        'refresh_token' => 12345,
      ]);

    $response->assertStatus(422);
  }

  public function testRefreshTokenCannotBeLongerThan64Characters()
  {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
      ->postJson($this->url, [
        'refresh_token' => str_repeat('a', 65),
      ]);

    $response->assertStatus(422);
  }

  public function testOldRefreshTokenBecomesInvalidAfterRefresh()
  {
    $user = User::factory()->create();

    $oldRefreshToken = Str::random(64);

    RefreshToken::create([
      'user_id' => $user->id,
      'hash_token' => Hash::make($oldRefreshToken),
      'expires_at' => now()->addDays(30),
    ]);

    // First refresh
    $response = $this->actingAs($user)
      ->postJson($this->url, [
        'refresh_token' => $oldRefreshToken,
      ]);

    $response->assertStatus(200);

    // Try to use the old token again
    $response = $this->actingAs($user)
      ->postJson($this->url, [
        'refresh_token' => $oldRefreshToken,
      ]);

    $response
      ->assertStatus(401)
      ->assertJson([
        'message' => 'Invalid or expired refresh token',
      ]);
  }
}