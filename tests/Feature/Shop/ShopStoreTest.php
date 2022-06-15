<?php

namespace Tests\Feature\Shop;

use App\Enums\UserRoleEnum;
use App\Models\Shop;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class ShopStoreTest extends TestCase
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
        Shop::factory([ 'seller_id' => $this->seller->id, 'code' => 'AB' ])->create();
    }

    /** @test */
    public function it_can_fetch_create_form()
    {
        $this->actingAs($this->seller);

        $response = $this->get(route('shop.create'));

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('elements.form-update-shop');
    }

    /** @test */
    public function seller_can_store_new_shop()
    {
        $this->actingAs($this->seller);

        $formData = [
            'name' => $this->faker->company(),
            'code' => $this->faker->unique()->regexify('[A-Z0-9]{2}'),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
        ];

        $this->get(route('shops'));
        $response = $this->post(route('shop.store'), $formData);

        $response
            ->assertRedirect(route('shops'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas(Shop::class, array_merge($formData, [ 'seller_id' => $this->seller->id ]));
    }

    /** @test */
    public function all_required_fields_of_store_form_are_validated()
    {
        $this->actingAs($this->seller);

        $this->get(route('shops'));
        $response = $this->post(route('shop.store'));

        $response
            ->assertSessionHasErrors([
                'name', 'code'
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
    public function store_request_should_fail(array $requestData, $errorFields)
    {
        $this->actingAs($this->seller);

        $response = $this->post(route('shop.store'));

        $response
            ->assertSessionHasErrors($errorFields);
    }

    /** @test */
    public function it_can_store_new_shop_with_same_code_for_different_seller()
    {
        $otherSeller = $this->createUser(UserRoleEnum::seller()->value);

        $shopCode = $this->faker->regexify('[A-Z0-9]{2}');
        Shop::factory([ 'seller_id' => $this->seller->id, 'code' => $shopCode ])->create();

        $this->actingAs($otherSeller);

        $formData = [
            'name' => $this->faker->company(),
            'code' => $shopCode,
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
        ];

        $this->get(route('shops'));
        $response = $this->post(route('shop.store'), $formData);

        $response
            ->assertRedirect(route('shops'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas(Shop::class, array_merge($formData, [ 'seller_id' => $otherSeller->id ]));
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
            '`Shop Code` already exists' => [
                [
                    'name' => $faker->company(),
                    'code' => 'AB',
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
