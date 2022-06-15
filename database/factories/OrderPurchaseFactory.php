<?php

namespace Database\Factories;

use App\Models\AgentCargoMark;
use App\Models\AgentCargoName;
use App\Models\OrderPurchase;
use App\Models\Shipper;
use App\Models\ShipType;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderPurchaseFactory extends Factory
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected $model = OrderPurchase::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $userId = User::factory([ 'role' => User::ROLE_MEMBER ]);
        $orderDate = $this->faker->dateTimeBetween('-1 years', 'now');

        return [
            'supplier_id' => Supplier::factory(),
            'seller_id' => $userId,
            'reference' => null,
            'e_d_f' => Carbon::createFromDate($orderDate)->addDays(3)->format('Y-m-d'),
            'e_d_t' => Carbon::createFromDate($orderDate)->addDays(5)->format('Y-m-d'),
            'e_a_d_f' => Carbon::createFromDate($orderDate)->addDays(7)->format('Y-m-d'),
            'e_a_d_t' => Carbon::createFromDate($orderDate)->addDays(7)->format('Y-m-d'),
            'order_date' => $orderDate->format('Y-m-d'),
            'ship_date' => Carbon::createFromDate($orderDate)->addDays(2)->format('Y-m-d'),
            'note' => null,
            'status' => $this->faker->randomElement([ OrderPurchase::STATUS_OPEN, OrderPurchase::STATUS_CLOSE, OrderPurchase::STATUS_ARRIVE, OrderPurchase::STATUS_DRAFT ]),
            'quantity' => $this->faker->randomNumber(3, false),
            'supply_from' => $this->faker->randomElement([ OrderPurchase::SUPPLY_FROM_DOMESTIC, OrderPurchase::SUPPLY_FROM_IMPORT ]), // int,
            'factory_tracking' => $this->faker->isbn13(),
            'shipping_type_id' => ShipType::factory(),
            'shipping_mark_id' => AgentCargoMark::factory(),
            'domestic_shipper_id' => Shipper::factory(),
            'agent_cargo_id' => AgentCargoName::factory(),
            'cargo_ref' => null,
            'number_of_cartons' => $this->faker->randomNumber(2, false),
            'domestic_logistics' => $this->faker->company(), // string
            'number_of_cartons1' => $this->faker->randomNumber(2, false),
            'domestic_logistics1' => $this->faker->company(), // string
            'author_id' => $userId
        ];
    }
}
