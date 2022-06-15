<?php

namespace Tests\Feature\Shop;

use App\Enums\UserRoleEnum;
use App\Models\Shop;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class ShopUpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    protected $seller;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->seller = $this->createUser(UserRoleEnum::seller()->value);
    }

    /** @test */
    public function it_can_fetch_edit_form()
    {
        $shop = Shop::factory([ 'seller_id' => $this->seller->id ])->create();

        $this->actingAs($this->seller);
        $response = $this->getJson(route('shops.data', [ 'id' => $shop->id ]));

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('elements.form-update-shop')
            ->assertSee($shop->name)
            ->assertSee($shop->code);
    }

    /** @test */
    public function seller_can_update_existing_shop_with_new_data()
    {
        $shop = Shop::factory([ 'seller_id' => $this->seller->id ])->create();

        $this->actingAs($this->seller);

        $newShopData = [
            'id' => $shop->id,
            'name' => $this->faker->company(),
            'code' => $this->faker->unique()->regexify('[A-Z0-9]{2}'),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber()
        ];

        $this->get('shops');
        $response = $this->post(route('shop.update'), $newShopData);

        $response
            ->assertRedirect(route('shops'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas(Shop::class, array_merge($newShopData, [ 'seller_id' => $this->seller->id ]));
    }

    /** @test */
    public function seller_can_update_shop_with_previous_shop_code()
    {
        $shop = Shop::factory([ 'seller_id' => $this->seller->id ])->create();

        $this->actingAs($this->seller);

        $newShopData = [
            'id' => $shop->id,
            'name' => $this->faker->company(),
            'code' => $shop->code, // SAME CODE
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber()
        ];

        $this->get('shops');
        $response = $this->post(route('shop.update'), $newShopData);

        $response
            ->assertRedirect(route('shops'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas(Shop::class, array_merge($newShopData, [ 'seller_id' => $this->seller->id ]));
    }

    /** @test */
    public function seller_should_get_errors_when_shop_code_already_exists_by_other_shop()
    {
        $shopOne = Shop::factory([ 'seller_id' => $this->seller->id ])->create();
        $shopTwo = Shop::factory([ 'seller_id' => $this->seller->id ])->create();

        $this->actingAs($this->seller);

        $this->get(route('shops'));

        $formData = [
            'id' => $shopOne->id, // ID of shop 1
            'name' => $this->faker->company(),
            'code' => $shopTwo->code, // CODE of shop 2
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber()
        ];

        $response = $this->post(route('shop.update'), $formData);

        $response
            ->assertSessionHasErrors([
                'code'
            ]);
    }

    /** @test */
    public function seller_can_update_their_shop_with_same_code_as_other_seller_shop()
    {
        $mainShop = Shop::factory([ 'seller_id' => $this->seller->id ])->create();

        $otherSeller = $this->createUser(UserRoleEnum::seller()->value);
        $otherShop = Shop::factory([ 'seller_id' => $otherSeller->id ])->create();

        $this->actingAs($this->seller);

        $this->get('shops');

        $formData = [
            'id' => $mainShop->id, // ID of main shop
            'name' => $this->faker->company(),
            'code' => $otherShop->code, // USE OTHER SHOP CODE
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber()
        ];

        $response = $this->post(route('shop.update'), $formData);

        $response
            ->assertRedirect(route('shops'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas(Shop::class, array_merge($formData, [ 'seller_id' => $this->seller->id ]));
    }

    // /** @test */
    public function all_required_fields_are_validated()
    {
        // Create shop
        $shop = Shop::factory([ 'seller_id' => $this->seller->id ])->create();

        // Acting as seller
        $this->actingAs($this->seller);

        // On shops route
        $this->get(route('shops'));

        // Update shop with empty fields
        $response = $this->post(route('shop.update'));

        // Expect error
        $response
            ->assertSessionHasErrors([
                'id', 'name', 'code'
            ]);
    }

    /**
     * @test
     * @dataProvider invalid_fields
     *
     * @param  array  $requestData
     * @param  array|string  $errorFields
     * @return void
     */
    public function update_request_should_fail(array $requestData, $errorFields)
    {
        $shop = Shop::factory([ 'seller_id' => $this->seller->id ])->create();

        $this->actingAs($this->seller);

        $formData = array_merge($requestData, [ 'id' => $shop->id ]);
        $response = $this->post(route('shop.update'), $formData);

        $response
            ->assertSessionHasErrors($errorFields);
    }

    /**
     * Invalid fields test
     *
     * @return array
     */
    public function invalid_fields()
    {
        $faker = Factory::create();

        return [
            '`Name` is not provided' => [
                [
                    'name' => '',
                    'code' => $faker->unique()->regexify('[A-Z0-9]{2}'),
                ],
                [
                    'name'
                ]
            ],
            '`Shop Code` is not provided' => [
                [
                    'name' => $faker->company(),
                    'code' => '',
                ],
                [
                    'code'
                ]
            ],
            '`Shop Code` more than 10 chars' => [
                [
                    'name' => $faker->company(),
                    'code' => $faker->unique()->regexify('[A-Z0-9]{11}'),
                ],
                [
                    'code'
                ]
            ],
        ];
    }
}
