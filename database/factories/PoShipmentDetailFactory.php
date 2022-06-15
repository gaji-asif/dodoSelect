<?php

namespace Database\Factories;

use App\Models\OrderPurchase;
use App\Models\PoShipment;
use App\Models\PoShipmentDetail;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PoShipmentDetailFactory extends Factory
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected $model = PoShipmentDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'po_shipment_id' => PoShipment::factory(),
            // 'product_id' => Product::factory(),
            'ship_quantity' => $this->faker->randomNumber(5, false),
            'order_purchase_id' => OrderPurchase::factory(),
            'supplier_id' => Supplier::factory(),
            'seller_id' => User::factory([ 'role' => User::ROLE_MEMBER ])
        ];
    }
}
