<?php

namespace Tests\Unit\Product;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductStoreTest extends TestCase
{
    private $fakeImage;
    private $fakeGifImage;


    public function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create([
            'is_active' => User::ACTIVE,
            'role' => User::ROLE_MEMBER
        ]);

        $this->actingAs($user);

        $productImageFileName = Str::random(10) . '.jpg';
        $productGifImageFileName = Str::random(10) . '.gif';
        $this->fakeImage = UploadedFile::fake()->image($productImageFileName);
        $this->fakeGifImage = UploadedFile::fake()->image($productGifImageFileName);
    }


    public function test_data_stored_successfully_when_provide_valid_data()
    {
        $this->get(route('product'));

        $response = $this->post(route('insert product'), [
            'category_id' => $this->faker->randomDigit(),
            'image' => $this->fakeImage,
            'product_name' => $this->faker->company,
            'product_code' => Str::random(10),
            'specifications' => $this->faker->text,
            'price' => $this->faker->randomNumber(),
            'weight' => $this->faker->randomNumber() . 'kg',
            'pack' => $this->faker->randomNumber(),
            'shop_id' => [
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            ],
            'shop_price' => [
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            ],
            'cost_pc' => $this->faker->randomNumber(),
            'currency' => $this->faker->randomNumber(),
            'alert_stock' => $this->faker->randomNumber(),
        ]);

        $response->assertRedirect(route('product'))
                ->assertSessionHas('success');
    }


    public function test_should_success_when_product_category_not_provided()
    {
        $this->get(route('product'));

        $response = $this->post(route('insert product'), [
            'category_id' => null,
            'image' => $this->fakeImage,
            'product_name' => $this->faker->company,
            'product_code' => Str::random(10),
            'specifications' => $this->faker->text,
            'price' => $this->faker->randomNumber(),
            'weight' => $this->faker->randomNumber() . 'kg',
            'pack' => $this->faker->randomNumber(),
            'shop_id' => [
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            ],
            'shop_price' => [
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            ],
            'cost_pc' => $this->faker->randomNumber(),
            'currency' => $this->faker->randomNumber(),
            'alert_stock' => $this->faker->randomNumber(),
        ]);

        $response->assertRedirect(route('product'))
                ->assertSessionHas('success');
    }


    public function test_should_error_when_image_not_provided()
    {
        $this->get(route('product'));

        $response = $this->post(route('insert product'), [
            'category_id' => $this->faker->randomDigit(),
            'image' => null,
            'product_name' => $this->faker->company,
            'product_code' => Str::random(10),
            'specifications' => $this->faker->text,
            'price' => $this->faker->randomNumber(),
            'weight' => $this->faker->randomNumber() . 'kg',
            'pack' => $this->faker->randomNumber(),
            'shop_id' => [
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            ],
            'shop_price' => [
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            ],
            'cost_pc' => $this->faker->randomNumber(),
            'currency' => $this->faker->randomNumber(),
            'alert_stock' => $this->faker->randomNumber(),
        ]);

        $response->assertRedirect(route('product'))
                ->assertSessionHasErrors('image');
    }


    public function test_should_error_when_image_invalid_mimes()
    {
        $this->get(route('product'));

        $response = $this->post(route('insert product'), [
            'category_id' => $this->faker->randomDigit(),
            'image' => $this->fakeGifImage,
            'product_name' => $this->faker->company,
            'product_code' => Str::random(10),
            'specifications' => $this->faker->text,
            'price' => $this->faker->randomNumber(),
            'weight' => $this->faker->randomNumber() . 'kg',
            'pack' => $this->faker->randomNumber(),
            'shop_id' => [
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            ],
            'shop_price' => [
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            ],
            'cost_pc' => $this->faker->randomNumber(),
            'currency' => $this->faker->randomNumber(),
            'alert_stock' => $this->faker->randomNumber(),
        ]);

        $response->assertRedirect(route('product'))
                ->assertSessionHasErrors('image');
    }


    public function test_should_error_when_product_name_not_provided()
    {
        $this->get(route('product'));

        $response = $this->post(route('insert product'), [
            'category_id' => $this->faker->randomDigit(),
            'image' => $this->fakeImage,
            'product_name' => null,
            'product_code' => Str::random(10),
            'specifications' => $this->faker->text,
            'price' => $this->faker->randomNumber(),
            'weight' => $this->faker->randomNumber() . 'kg',
            'pack' => $this->faker->randomNumber(),
            'shop_id' => [
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            ],
            'shop_price' => [
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            ],
            'cost_pc' => $this->faker->randomNumber(),
            'currency' => $this->faker->randomNumber(),
            'alert_stock' => $this->faker->randomNumber(),
        ]);

        $response->assertRedirect(route('product'))
                ->assertSessionHasErrors('product_name');
    }


    public function test_should_error_when_product_name_more_than_255_chars()
    {
        $this->get(route('product'));

        $response = $this->post(route('insert product'), [
            'category_id' => $this->faker->randomDigit(),
            'image' => $this->fakeImage,
            'product_name' => Str::random(256),
            'product_code' => Str::random(10),
            'specifications' => $this->faker->text,
            'price' => $this->faker->randomNumber(),
            'weight' => $this->faker->randomNumber() . 'kg',
            'pack' => $this->faker->randomNumber(),
            'shop_id' => [
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            ],
            'shop_price' => [
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            ],
            'cost_pc' => $this->faker->randomNumber(),
            'currency' => $this->faker->randomNumber(),
            'alert_stock' => $this->faker->randomNumber(),
        ]);

        $response->assertRedirect(route('product'))
                ->assertSessionHasErrors('product_name');
    }


    public function test_should_error_when_product_code_not_provided()
    {
        $this->get(route('product'));

        $response = $this->post(route('insert product'), [
            'category_id' => $this->faker->randomDigit(),
            'image' => $this->fakeImage,
            'product_name' => Str::random(256),
            'product_code' => null,
            'specifications' => $this->faker->text,
            'price' => $this->faker->randomNumber(),
            'weight' => $this->faker->randomNumber() . 'kg',
            'pack' => $this->faker->randomNumber(),
            'shop_id' => [
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            ],
            'shop_price' => [
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->randomNumber()
            ],
            'cost_pc' => $this->faker->randomNumber(),
            'currency' => $this->faker->randomNumber(),
            'alert_stock' => $this->faker->randomNumber(),
        ]);

        $response->assertRedirect(route('product'))
                ->assertSessionHasErrors('product_name');
    }
}
