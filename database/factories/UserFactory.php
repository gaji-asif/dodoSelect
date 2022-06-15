<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->firstName() . ' ' . $this->faker->lastName(),
            'username' => $this->faker->unique()->userName(),
            'contactname' => $this->faker->firstName(),
            'phone' => escape_user_phone($this->faker->unique()->e164PhoneNumber()),
            'lineid' => $this->faker->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'role' => $this->faker->randomElement(['member', 'admin', 'staff']),
            'remember_token' => Str::random(10),
            'is_active' => $this->faker->randomElement([1, 0]),
            'max_limit' => $this->faker->numberBetween(0, 1000),
            'seller_id' => $this->faker->randomDigit(),
            'address' => $this->faker->streetAddress()
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
