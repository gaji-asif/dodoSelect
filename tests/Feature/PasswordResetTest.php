<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    public function test_reset_password_link_screen_can_be_rendered()
    {
        $response = $this->get(route('forget-password'));

        $response->assertStatus(200)
            ->assertViewIs('otp.enterPhoneNumber');
    }


    public function test_reset_password_link_can_be_requested()
    {
        $user = User::factory()->create([
            'is_active' => User::ACTIVE,
            'role' => User::ROLE_MEMBER
        ]);

        $response = $this->post(route('get-phone'), [
            'phone' => $user->phone
        ]);

        $response->assertRedirect(route('verify_mobile'));
    }
}
