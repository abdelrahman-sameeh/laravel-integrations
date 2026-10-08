<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_chat_to_login(): void
    {
        $this->get('/chat')
            ->assertRedirect('/login');
    }

    public function test_user_can_login_with_the_web_form(): void
    {
        $user = User::factory()->create([
            'password' => '123456',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => '123456',
        ])
            ->assertRedirect('/chat')
            ->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_web_credentials_return_to_the_form(): void
    {
        $user = User::factory()->create([
            'password' => '123456',
        ]);

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'invalid-password',
        ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_register_with_the_web_form(): void
    {
        $this->post('/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ])
            ->assertRedirect('/chat')
            ->assertSessionHasNoErrors();

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'ahmed@example.com',
        ]);
    }

    public function test_authenticated_user_can_logout_from_the_web(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
