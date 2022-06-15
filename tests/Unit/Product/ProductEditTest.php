<?php

namespace Tests\Unit\Product;

use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class ProductEditTest extends TestCase
{
    private $user;


    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'is_active' => User::ACTIVE,
            'role' => User::ROLE_MEMBER
        ]);

        $this->actingAs($this->user);
    }


    public function test_can_fetch_product_data()
    {
        $product = Product::factory(1)->create([
            'category_id' => 0,
            'seller_id' => $this->user->id
        ]);

        $response = $this->get(route('data product', [ 'id' => $product->id ]));
        $response->assertStatus(200)
                ->assertViewIs('elements.form-update-product')
                ->assertViewHasAll([ 'product', 'categories', 'shops', 'productPrices' ]);
    }
}
