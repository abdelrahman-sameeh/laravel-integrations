<?php

namespace Tests\Feature\Sanctum;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MeTest extends TestCase
{
  use RefreshDatabase;
  private $url = '/api/me';

  function testGetMeData()
  {
    $user = User::factory()->create(['password' => '123456']);
    $credential = [
      'email' => $user->email,
      'password' => '123456'
    ];
    $loginResponse = $this->postJson('api/login', $credential);

    $access_token = $loginResponse->json('data.token.access_token');
    $meDataResponse = $this->withHeader('Authorization', "Bearer $access_token")->getJson($this->url);
    $meDataResponse->assertStatus(200)->assertJsonStructure(['data' => ['id', 'name', 'email']]);
  }

}
