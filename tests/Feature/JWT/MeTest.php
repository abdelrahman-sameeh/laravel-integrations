<?php

namespace Tests\Feature\JWT;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class MeTest extends TestCase
{
    use RefreshDatabase;
    private string $url = "/api/me";

    public function testAuthenticatedUserCanGetProfile(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson($this->url);
        $response->assertOk()
            ->assertJsonStructure(['user' => ['id', 'name']]);
    }

    function testAuthenticatedUserCanGetProfileWithoutPassword()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeader('Authorization', "Bearer $token")->getJson($this->url);
        $response->assertOk()
            ->assertJsonMissingPath('user.password');
    }


    function testGuestCannotGetProfile(){
        $response = $this->getJson($this->url);
        $response->assertStatus(401)->assertJson(["message"=>"Unauthenticated."]);
    }


    function testInvalidTokenIsRejected(){
        $response = $this->withHeader('Authorization', 'Bearer Invalid token')->getJson($this->url);
        $response->assertStatus(401)->assertJson(["message"=>"Unauthenticated."]);
    }

}
