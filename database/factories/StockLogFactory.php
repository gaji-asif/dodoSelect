<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StockLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'seller_id' => User::factory([ 'role' => User::ROLE_MEMBER ]),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now', 'Asia/Bangkok'),
            'check_in_out' => $this->faker->randomElement([ StockLog::CHECK_IN_OUT_ADD, StockLog::CHECK_IN_OUT_REMOVE ]),
            'is_defect' => $this->faker->randomElement([ StockLog::IS_DEFECT_YES, StockLog::IS_DEFECT_NO ]),
            'deffect_status' => $this->faker->randomElement([ StockLog::DEFECT_STATUS_OPEN, StockLog::DEFECT_STATUS_CLOSE ]),
            'deffect_note' => null,
            'defect_result' => null
        ];
    }
}
