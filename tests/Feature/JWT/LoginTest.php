<?php

namespace Tests\Feature\JWT;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;



class LoginTest extends TestCase
{
  use RefreshDatabase;
  private $data = [
    'email' => 'ahmed@gmail.com',
    'password' => '123456',
  ];

  private $url = '/api/login';

  function createUser()
  {
    $user = new User([
      'name' => 'test',
      'email' => $this->data['email'],
      'password' => bcrypt($this->data['password'])
    ]);
    $user->save();
    return $user;
  }


  function testLoginReturnCorrectUserData()
  {
    $user = $this->createUser();
    $response = $this->postJson($this->url, $this->data);
    $response->assertOk()->assertJson([
      'user' => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email
      ]
    ])->assertJsonPath('token.token_type', 'bearer');
  }


  function testInvalidEmail()
  {
    $this->createUser();
    $this->data['email'] = 'invalid';
    $response = $this->postJson($this->url, $this->data);
    $response->assertStatus(422)->assertJsonStructure(['message', 'errors' => ['email']]);
  }


  function testInvalidPassword()
  {
    $this->createUser();
    $this->data['password'] = ['test'];
    $response = $this->postJson($this->url, $this->data);
    $response->assertStatus(422)->assertJsonStructure(['message', 'errors' => ['password']]);
  }


  function testNonExistingEmail()
  {
    $response = $this->postJson($this->url, $this->data);
    $response->assertStatus(422)->assertJsonStructure(['message', 'errors' => ['email']]);
  }

  function testMissingEmail()
  {
    $this->createUser();
    unset($this->data['email']);
    $response = $this->postJson($this->url, $this->data);
    $response->assertStatus(422)->assertJsonValidationErrors(['email']);
  }


  function testWrongPassword()
  {
    $this->createUser();
    $this->data['password'] = '123444';
    $response = $this->postJson($this->url, $this->data);
    $response->assertStatus(401)->assertJson(['error' => 'Unauthorized']);
  }

}