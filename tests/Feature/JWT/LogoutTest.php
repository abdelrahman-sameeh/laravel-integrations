<?php

namespace Tests\Feature\JWT;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogoutTest extends TestCase
{
    use RefreshDatabase;
    private $url = '/api/logout';


    public function testLogoutSuccessfully(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeader('Authorization', "Bearer $token")->postJson($this->url);
        $response->assertStatus(200);
    }


    public function testLogoutMustHaveToken(): void
    {
        $response = $this->postJson($this->url);
        $response->assertStatus(401);
    }


    function testWithInvalidToken(){
        $response = $this->withHeader('Authorization', "Bearer invalid")->postJson($this->url);
        $response->assertStatus(401);
    }

}
