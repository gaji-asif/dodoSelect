<?php

namespace Database\Factories;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChannelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Channel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'display_channel' => $this->faker->randomElement([1, 0]),
            'image' => 'uploads/channel_image/' . $this->faker->randomElement([ 'fb.png', 'line.png', 'phone.png' ])
        ];
    }
}
