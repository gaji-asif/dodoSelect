<?php

namespace Database\Factories;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SheetDocFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'seller_id' => User::factory([ 'role' => UserRoleEnum::seller()->value ])->create(),
            'file_name' => substr($this->faker->sentence(3), 0, 50),
            'spreadsheet_id' => Str::random(44)
        ];
    }
}
