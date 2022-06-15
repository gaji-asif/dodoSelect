<?php

namespace Tests\Feature\Product;

use App\Models\User;
use Tests\TestCase;

class CheckInOutProductTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create([
            'is_active' => User::ACTIVE,
            'role' => User::ROLE_MEMBER
        ]);

        $this->actingAs($user);
    }


    public function test_can_view_check_in_out_product_page()
    {
        $response = $this->get(route('inout qr code'));

        $response->assertStatus(200)
            ->assertViewIs('qrCode.in-out');
    }
}
