<?php

namespace Database\Factories;

use App\Enums\UserRoleEnum;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShopFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Shop::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'code' => $this->faker->unique()->regexify('[A-Z0-9]{2}'),
            'seller_id' => User::factory([ 'role' => UserRoleEnum::seller()->value ])
        ];
    }
}
