<?php

namespace Database\Factories;

use App\Models\CustomOrder;
use App\Models\CustomOrderDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomOrderDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CustomOrderDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'custom_order_id' => CustomOrder::factory(),
            'shop_id' => User::factory(),
            'product_name' => $this->faker->productName(),
            'product_description' => $this->faker->text(),
            'product_price' => $this->faker->numberBetween(100, 1000),
            'quantity' => $this->faker->numberBetween(0, 100),
            'discount_price' => $this->faker->numberBetween(0, 100),
            'seller_id' => User::factory()
        ];
    }
}
