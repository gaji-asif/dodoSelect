<?php

namespace Database\Factories;

use App\Enums\UserRoleEnum;
use App\Models\SheetName;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SheetDataTpkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sheet_name_id' => SheetName::factory(),
            'seller_id' => User::factory([ 'role' => UserRoleEnum::seller()->value ]),
            'date' => $this->faker->dateTimeBetween('-1 years', 'now')->format('Y-m-d'),
            'amount' => $this->faker->randomFloat(2, 0, 1000),
            'type' => $this->faker->randomElement([ 'New', 'Old', 'Sample' ]),
            'channel' => $this->faker->randomElement([ 'Facebook', 'Line' ]),
            'order_by' => $this->faker->randomElement([ 'n', 't' ]),
            'shop' => $this->faker->randomElement([ 'TH', 'VC', 'AC', 'PZ' ]),
            'charged_shipping_cost' => $this->faker->randomFloat(2, 0, 100),
            'actual_shipping_cost' => $this->faker->randomFloat(2, 0, 100)
        ];
    }
}
