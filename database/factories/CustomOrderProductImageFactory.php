<?php

namespace Database\Factories;

use App\Models\CustomOrderDetail;
use App\Models\CustomOrderProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomOrderProductImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CustomOrderProductImage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $fullPath = $this->faker->image(public_path('uploads/custom-order/products'));
        $splitFullPath = explode('uploads/', $fullPath);

        $imagePath = 'uploads/' . end($splitFullPath);

        return [
            'custom_order_detail_id' => CustomOrderDetail::factory(),
            'image' => $imagePath
        ];
    }
}
