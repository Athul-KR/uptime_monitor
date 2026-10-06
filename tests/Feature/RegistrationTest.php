<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk()->assertSee('Create your account');
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas(User::class, [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('register'))->assertSessionHasErrors('password');
    }

    public function test_registration_requires_a_unique_email(): void
    {
        $existingUser = User::factory()->create();

        $response = $this->from(route('register'))->post(route('register'), [
            'name' => 'Jane Doe',
            'email' => $existingUser->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
