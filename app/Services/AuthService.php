<?php

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function register(array $attributes): User
    {
        return User::query()->create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'password' => Hash::make($attributes['password']),
        ]);
    }

    public function authenticate(array $credentials): ?User
    {
        $user = User::query()
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        return $user;
    }

    public function userWithTokens(User $user): array
    {
        return [
            'user' => $user->only('id', 'name', 'email'),
            'token' => $this->issueTokens($user),
        ];
    }

    public function issueTokens(User $user): array
    {
        return DB::transaction(function () use ($user) {
            $user->refreshTokens()->delete();

            $accessToken = $user->createToken('access_token')->plainTextToken;
            $refreshToken = Str::random(64);

            RefreshToken::query()->create([
                'user_id' => $user->id,
                'hash_token' => Hash::make($refreshToken),
                'expires_at' => now()->addDays(30),
            ]);

            return [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ];
        });
    }

    public function refreshTokens(User $user, string $refreshToken): ?array
    {
        $isValid = $user->refreshTokens()
            ->where('expires_at', '>', now())
            ->get()
            ->contains(fn (RefreshToken $token) => Hash::check($refreshToken, $token->hash_token));

        if (! $isValid) {
            return null;
        }

        return $this->issueTokens($user);
    }

    public function revokeTokens(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->refreshTokens()->delete();
            $user->currentAccessToken()?->delete();
        });
    }
}
