<?php
namespace App\Http\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Hash;
use Str;

class AuthService
{
  function getUserWithToken(User $user)
  {
    return [
      'user' => $user->only('id', 'name', 'email'),
      'token' => $this->generateToken($user),
    ];
  }

  function generateToken(User $user)
  {
    // check user has refresh token need make it invalid
    $user->refreshTokens()->delete();
    $accessToken = $user->createToken('access_token')->plainTextToken;
    $refreshToken = Str::random(64);
    RefreshToken::create([
      'user_id' => $user->id,
      'hash_token' => Hash::make($refreshToken),
      'expires_at' => now()->addDays(30),
    ]);

    return [
      'access_token' => $accessToken,
      'refresh_token' => $refreshToken,
    ];
  }
}
