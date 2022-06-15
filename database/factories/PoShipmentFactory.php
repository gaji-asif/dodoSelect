<?php

namespace Database\Factories;

use App\Models\AgentCargoMark;
use App\Models\AgentCargoName;
use App\Models\OrderPurchase;
use App\Models\PoShipment;
use App\Models\Shipper;
use App\Models\ShipType;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PoShipmentFactory extends Factory
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected $model = PoShipment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $orderDate = $this->faker->dateTimeBetween('-1 years', 'now');

        return [
            'order_purchase_id' => OrderPurchase::factory(),
            'supplier_id' => Supplier::factory(),
            'seller_id' => User::factory([ 'role' => User::ROLE_MEMBER ]),
            'e_d_f' => Carbon::createFromDate($orderDate)->addDays(3)->format('Y-m-d'),
            'e_d_t' => Carbon::createFromDate($orderDate)->addDays(5)->format('Y-m-d'),
            'e_a_d_f' => Carbon::createFromDate($orderDate)->addDays(7)->format('Y-m-d'),
            'e_a_d_t' => Carbon::createFromDate($orderDate)->addDays(7)->format('Y-m-d'),
            'quantity' => $this->faker->randomNumber(3, false),
            'supply_from' => $this->faker->randomElement([ OrderPurchase::SUPPLY_FROM_DOMESTIC, OrderPurchase::SUPPLY_FROM_IMPORT ]), // int,
            'factory_tracking' => $this->faker->isbn13(),
            'shipping_type_id' => ShipType::factory(),
            'shipping_mark_id' => AgentCargoMark::factory(),
            'domestic_shipper_id' => Shipper::factory(),
            'agent_cargo_id' => AgentCargoName::factory(),
            'cargo_ref' => null, // string
            'number_of_cartons' => $this->faker->randomNumber(2, false),
            'domestic_logistics' => $this->faker->company(), // string
            'number_of_cartons1' => $this->faker->randomNumber(2, false),
            'domestic_logistics1' => $this->faker->company(), // string
            'order_date' => $orderDate->format('Y-m-d'),
            'ship_date' => Carbon::createFromDate($orderDate)->addDays(2)->format('Y-m-d'),
            'status' => $this->faker->randomElement([ PoShipment::STATUS_OPEN, PoShipment::STATUS_CLOSE, PoShipment::STATUS_ARRIVE, PoShipment::STATUS_DRAFT ])
        ];
    }
}
