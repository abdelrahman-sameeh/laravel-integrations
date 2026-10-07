<?php

namespace Tests\Feature\Sanctum;

use App\Models\RefreshToken;
use App\Models\User;
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Str;
use Tests\TestCase;

class LogoutTest extends TestCase
{
  use RefreshDatabase;
  private $url = '/api/logout';


  public function testUserCanLogout()
  {
    $user = User::factory()->create();

    $refreshToken = Str::random(64);

    RefreshToken::create([
      'user_id' => $user->id,
      'hash_token' => Hash::make($refreshToken),
      'expires_at' => now()->addDays(30),
    ]);

    $accessToken = $user->createToken('access_token');

    $response = $this->withToken($accessToken->plainTextToken)
      ->postJson($this->url);

    $response
      ->assertStatus(200)
      ->assertJson([
        'message' => 'Logged out successfully',
      ]);

    $this->assertDatabaseMissing('refresh_tokens', [
      'user_id' => $user->id,
    ]);

    $this->assertDatabaseMissing('personal_access_tokens', [
      'id' => $accessToken->accessToken->id,
    ]);
  }



  public function testLogoutDeletesAllUserRefreshTokens()
  {
    $user = User::factory()->create();

    RefreshToken::create([
      'user_id' => $user->id,
      'hash_token' => Hash::make(Str::random(64)),
      'expires_at' => now()->addDays(30),
    ]);

    RefreshToken::create([
      'user_id' => $user->id,
      'hash_token' => Hash::make(Str::random(64)),
      'expires_at' => now()->addDays(30),
    ]);


    $accessToken = $user->createToken('access_token');

    $this->assertDatabaseCount('refresh_tokens', 2);

    $this->withHeader('Authorization', "Bearer {$accessToken->plainTextToken}")
      ->postJson($this->url)
      ->assertStatus(200);

    $this->assertDatabaseCount('refresh_tokens', 0);
  }

}
