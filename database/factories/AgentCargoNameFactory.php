<?php

namespace Database\Factories;

use App\Models\AgentCargoName;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgentCargoNameFactory extends Factory
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected $model = AgentCargoName::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->lastName() . ' Cargo'
        ];
    }
}
