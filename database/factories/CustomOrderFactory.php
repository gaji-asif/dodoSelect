<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\Customer;
use App\Models\CustomOrder;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CustomOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $subTotal = $this->faker->numberBetween(1000, 10000);
        $shippingCost = $this->faker->numberBetween(100, 500);
        $totalDiscount = $this->faker->numberBetween(0, 1000);
        $inTotal = $subTotal + $shippingCost - $totalDiscount;

        return [
            'seller_id' => User::factory(),
            'shop_id' => Shop::factory(),
            'customer_id' => Customer::factory(),
            'channel_id' => Channel::factory(),
            'shipping_name' => $this->faker->company(),
            'shipping_phone' => $this->faker->phoneNumber(),
            'shipping_address' => $this->faker->address(),
            'payment_status' => $this->faker->randomElement([ CustomOrder::PAYMENT_STATUS_UNPAID, CustomOrder::PAYMENT_STATUS_PAID ]),
            'order_status' => $this->faker->randomElement([ CustomOrder::ORDER_STATUS_PENDING, CustomOrder::ORDER_STATUS_PROCESSING, CustomOrder::ORDER_STATUS_READY_TO_SHIP, CustomOrder::ORDER_STATUS_SHIPPED ]),
            'sub_total' => $subTotal,
            'shipping_cost' => $shippingCost,
            'total_discount' => $totalDiscount,
            'in_total' => $inTotal
        ];
    }
}
