<?php

namespace Tests\Feature\Sanctum;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    private $url = '/api/login';

    function testLoginSuccessfully()
    {
        $user = User::factory()->create(['password' => '123456']);
        $credential = [
            'email' => $user->email,
            'password' => '123456'
        ];
        $response = $this->postJson($this->url, $credential);
        $response->assertStatus(200)->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user' => ['id'],
                'token' => ['access_token']
            ]
        ]);

    }
    function testEmailIsInvalid()
    {
        $user = User::factory()->create(['email' => 'invalidEmail']);
        $response = $this->postJson($this->url, $user->only('email', 'password'));
        $response->assertStatus(422)->assertJsonPath('errors.email.0', 'The email field must be a valid email address.');
    }

    function testInvalidCredential()
    {
        $user = User::factory()->create();
        $invalidCredential = [
            'email' => $user->email,
            'password' => 'wrong password'
        ];
        $response = $this->postJson($this->url, $invalidCredential);
        $response->assertStatus(401)->assertJsonStructure(['status', 'message']);
    }

    function testNotFoundEmail()
    {
        User::factory()->create(['password' => '123456']);
        $invalidCredential = [
            'email' => 'notfound@gmail.com',
            'password' => '123456'
        ];
        $response = $this->postJson($this->url, $invalidCredential);
        $response->assertStatus(422)->assertJsonStructure(['errors' => ['email'], 'message']);
    }

    function testAccessTokenIsValid(){
        $user = User::factory()->create(['password' => '123456']);
        $credential = [
            'email' => $user->email,
            'password' => '123456'
        ];
        $response = $this->postJson($this->url, $credential);

        $access_token = $response->json('data.token.access_token');
        $meDataResponse = $this->withHeader('Authorization', "Bearer $access_token")->getJson('/api/me');
        $meDataResponse->assertStatus(200)->assertJsonStructure(['data'=>['id', 'name', 'email']]);
    }

}
