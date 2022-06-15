<?php

namespace Tests\Feature\Product;

use App\Models\User;
use Tests\TestCase;

class ProductTest extends TestCase
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


    public function test_user_can_view_product_table_page()
    {
        $response = $this->get(route('product'));

        $response->assertStatus(200)
            ->assertViewIs('seller.product');
    }


    public function test_product_datatable_can_render_correctly()
    {
        $response = $this->get(route('data product'), [
            'Accept' => 'application/json, text/javascript, */*',
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data', 'input']);
    }
}
