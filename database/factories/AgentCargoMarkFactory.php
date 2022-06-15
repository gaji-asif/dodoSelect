<?php

namespace Database\Factories;

use App\Models\AgentCargoMark;
use App\Models\AgentCargoName;
use App\Models\ShipType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgentCargoMarkFactory extends Factory
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected $model = AgentCargoMark::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'agent_cargo_id' => AgentCargoName::factory(),
            'shipping_mark' => $this->faker->lastName() . ' Mark',
            'ship_type_id' => ShipType::factory()
        ];
    }
}
