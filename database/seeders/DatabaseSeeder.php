<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        $gender = $faker->randomElement(['male', 'female']);

    	foreach (range(1,2500) as $index) {
            DB::table('order_managements')->insert([
                'contact_name' => $faker->name($gender),
                'channel' => $faker->email,
                
                'shipping_phone' => $faker->phoneNumber
               
            ]);
        }
    }
}