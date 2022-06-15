<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\ExchangeRate;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $availableCurrencies = ExchangeRate::pluck('name')->toArray();

        $productImageFileName = Str::random(10) . '.jpg';
        $fakeImage = UploadedFile::fake()->image($productImageFileName);

        return [
            'product_name' => ucwords($this->faker->words(4, true) . ' - ' . $this->faker->safeColorName()),
            'specifications' => $this->faker->text(),
            'pack' => $this->faker->randomNumber(2),
            'currency' => $this->faker->randomElement($availableCurrencies),
            'cost_pc' => $this->faker->randomFloat(2, 0, 100),
            'lowest_value' => $this->faker->randomFloat(2, 0, 50),
            'shop_id' => Shop::factory(),
            'category_id' => Category::factory(),
            'product_code' => $this->faker->unique()->regexify('[A-Z]{10}[0-4]{5}'),
            'image' => $fakeImage,
            'product_tag_id' => 0, // int --> moved to product_has_tags table
            'warehouse_id' => 0, // int
            'seller_id' => User::factory([ 'role' => User::ROLE_MEMBER ]),
            'out_of_stock_reorder' => '', // string
            'from_where' => 0,
            'price' => $this->faker->randomFloat(2, 0, 100),
            'ship_cost' => $this->faker->randomFloat(2, 0, 20),
            'weight' => $this->faker->randomNumber(2) . 'kg',
            'alert_stock' => $this->faker->numberBetween(0, 100),
            'cost_price' => $this->faker->randomFloat(2, 0, 100), // decimal
            'cost_currency' => '', // string
            'dropship_price' => $this->faker->numberBetween(0, 100), // int
            'pieces_per_carton' => $this->faker->numberBetween(0, 10), // int
            'pieces_per_pack' => $this->faker->numberBetween(0, 50), // int
            'supplier_id' => Supplier::factory(), // int
            'product_status' => $this->faker->randomElement([ Product::STATUS_AVAILABLE_STOCK, Product::STATUS_LOW_STOCK, Product::STATUS_NOT_AVAILABLE, Product::STATUS_OUT_OF_STOCK ]), // int
            'lowest_is_type' => 0, // int
            'lowest_sell_price' => 0, // decimal
            'profit' => 0, // decimal
            'mark_up' => 0, // decimal
        ];
    }
}
