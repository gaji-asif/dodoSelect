<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'supplier_name' => $this->faker->firstName() . ' ' . $this->faker->lastName(),
            'seller_id' => User::factory([ 'role' => User::ROLE_MEMBER ]),
            'address' => $this->faker->address(),
            'note' => null
        ];
    }
}
