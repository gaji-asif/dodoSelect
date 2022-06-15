<?php

namespace Database\Factories;

use App\Enums\SheetNameSyncStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\SheetDoc;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SheetNameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sheet_doc_id' => SheetDoc::factory(),
            'sheet_name' => $this->faker->words(3, true),
            'allow_to_sync' => $this->faker->boolean(),
            'last_sync' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'sync_status' => $this->faker->randomElement(SheetNameSyncStatusEnum::toValues()),
            'seller_id' => User::factory([ 'role' => UserRoleEnum::seller()->value ]),
        ];
    }
}
