<?php

namespace Database\Factories;

use App\Models\ShipType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShipTypeFactory extends Factory
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected $model = ShipType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => strtoupper(substr($this->faker->unique()->word(), 0, 3))
        ];
    }
}
