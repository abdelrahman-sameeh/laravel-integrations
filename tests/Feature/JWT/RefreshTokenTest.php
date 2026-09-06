<?php

namespace Tests\Feature\JWT;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RefreshTokenTest extends TestCase
{
    use RefreshDatabase;

    private $url = '/api/refresh';

    public function testRefreshTokenValid(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson($this->url);
        $response->assertOk()->assertJsonStructure(['user', 'token']);
    }


    public function testTokenIsBlacklistedAfterInvalidation(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        JWTAuth::setToken($token)->invalidate();
        $this->expectException(\Tymon\JWTAuth\Exceptions\TokenBlacklistedException::class);
        JWTAuth::setToken($token)->authenticate();
    }

    public function testRefreshFailsWithoutToken(): void
    {
        $response = $this->getJson($this->url);
        $response->assertStatus(401);
    }

    public function testNewTokenDiffersFromOld(): void
    {
        $user = User::factory()->create();
        $oldToken = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', "Bearer $oldToken")
            ->getJson($this->url);

        $newToken = $response->json('token.access_token');
        $this->assertNotEquals($oldToken, $newToken);
    }


}
