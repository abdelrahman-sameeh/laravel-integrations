<?php

namespace Tests\Feature\JWT;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;
    private $data = [
        'name' => 'ahmed',
        'email' => 'ahmed@gmail.com',
        'password' => '123456',
        'password_confirmation' => '123456',
    ];

    private $url = '/api/register';

    /**
     * A basic feature test example.
     */
    public function testCreateNewUser()
    {
        $response = $this->postJson($this->url, $this->data);
        $response->assertStatus(201)->assertJsonStructure(['user', 'token']);
    }


    public function testInvalidName()
    {
        $this->data['name'] = str_repeat("ahmed", 50);
        $response = $this->postJson($this->url, $this->data);
        $response->assertStatus(422)->assertJsonStructure(['errors' => ['name']]);
    }


    public function testInvalidEmail()
    {
        $this->data['email'] = 'invalidEmail';
        $response = $this->postJson($this->url, $this->data);
        $response->assertStatus(422)->assertJsonStructure(['errors' => ['email']]);
    }

    public function testInvalidPassword()
    {
        $this->data['password'] = str_repeat("test", 50);
        $response = $this->postJson($this->url, $this->data);
        $response->assertStatus(422)->assertJsonStructure(['errors' => ['password']]);
    }


    public function testInvalidPasswordConfirmation()
    {
        $this->data['password_confirmation'] = '12345';
        $response = $this->postJson($this->url, $this->data);
        $response->assertStatus(422)->assertJsonStructure(['errors' => ['password']]);
    }

    public function testPasswordIsEncrypted()
    {
        $response = $this->postJson($this->url, $this->data);
        $response->assertStatus(201);
        $user = User::find($response->json()['user']['id']);
        $this->assertEquals(true, password_verify($this->data['password'], $user['password']));
    }

    function testDuplicateEmail()
    {
        User::create($this->data);
        $response = $this->postJson($this->url, $this->data);
        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }


    public function testUserIsActuallyPersistedInDatabase()
    {
        $response = $this->postJson($this->url, $this->data);
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => $this->data['email']
        ]);
    }

    function testPasswordNotExposedInResponse()
    {
        $response = $this->postJson($this->url, $this->data);
        $response->assertStatus(201)->assertJsonMissing(['password'])->assertJsonMissingPath('user.password');
    }

}
