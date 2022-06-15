<?php

namespace Tests\Feature\Product;

use App\Models\User;
use Tests\TestCase;

class InOutHistoryTest extends TestCase
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


    public function test_user_can_view_in_out_history_page()
    {
        $response = $this->get(route('in-out-history'));

        $response->assertStatus(200)
                ->assertViewIs('qrCode.in-out-history');
    }
}
