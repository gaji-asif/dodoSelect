<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200)
            ->assertViewIs('auth.register');
    }


    public function test_new_users_can_register()
    {
        $this->get(route('register'));

        $response = $this->post(route('register'), [
            'username' => $this->faker->userName,
            'name' => $this->faker->name(),
            'contactname' => $this->faker->name(),
            'phone' => escape_user_phone($this->faker->e164PhoneNumber),
            'email' => $this->faker->safeEmail,
            'lineid' => (string)$this->faker->numberBetween(0, 100),
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('verify_mobile'));
    }


    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get(route('signin'));

        $response->assertStatus(200)
            ->assertViewIs('auth.signin');
    }


    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create([
            'is_active' => User::ACTIVE,
            'role' => User::ROLE_MEMBER
        ]);

        $response = $this->post(route('signin'), [
            'phone' => $user->phone,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }


    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create([
            'is_active' => User::ACTIVE,
            'role' => User::ROLE_MEMBER
        ]);

        $this->post(route('signin'), [
            'phone' => $user->phone,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
