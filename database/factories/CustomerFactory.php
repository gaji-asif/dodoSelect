<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'customer_name' => $this->faker->firstName() . ' ' . $this->faker->lastName(),
            'contact_phone' => substr(escape_user_phone($this->faker->e164PhoneNumber()), 0, 15),
            'seller_id' => User::factory(),
            'order_type' => $this->faker->randomElement([1, 2])
        ];
    }
}
