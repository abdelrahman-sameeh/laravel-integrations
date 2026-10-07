<?php

namespace Tests\Feature\Sanctum;

use App\Models\User;
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
  use RefreshDatabase;
  private string $url = '/api/register';

  private $data = [
    'name' => 'ahmed',
    'email' => 'exist@gmail.com',
    'password' => '123456',
    'password_confirmation' => '123456'
  ];


  function testRegisterSuccessfully()
  {
    $response = $this->postJson($this->url, $this->data);
    $response->assertStatus(201)->assertJsonStructure(['data' => ['token', 'user']]);
  }

  function testEmailDuplicated()
  {
    User::factory()->create(['email' => 'exist@gmail.com']);
    $this->data['email'] = 'exist@gmail.com';
    $response = $this->postJson($this->url, $this->data);
    $response->assertStatus(422)->assertJsonStructure(['errors' => ['email']]);
  }


  function testResponseNotHavePassword()
  {
    $response = $this->postJson($this->url, $this->data);
    $response->assertStatus(201)->assertJsonMissingPath('data.user.password');
  }

  function testPasswordIsEncrypted()
  {
    $this->postJson($this->url, $this->data);
    $user = User::where('email', '=', $this->data['email'])->first();
    $this->assertTrue(Hash::check($this->data['password'], $user->password));
  }

  function testConfirmationPasswordNotMatch()
  {
    $this->data['password_confirmation'] = '1234555';
    $response = $this->postJson($this->url, $this->data);
    $response->assertStatus(422)->assertJsonStructure(['errors' => ['password']]);
  } 

  


}
