<?php

namespace Tests\Feature\Shop;

use App\Enums\UserRoleEnum;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ShopIndexTest extends TestCase
{
    use RefreshDatabase;

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
    public function seller_can_see_shop_page()
    {
        $this->actingAs($this->seller);

        $response = $this->get(route('shops'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertViewIs('settings.shop');
    }

    /** @test */
    public function datatable_route_should_respond_correct_json()
    {
        Shop::factory([ 'seller_id' => $this->seller->id ])
            ->count(20)
            ->create();

        $this->actingAs($this->seller);

        $datatableUri = route('shops.data') . '?' . http_build_query($this->datatableQueryParams());
        $response = $this->getJson($datatableUri);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('draw')
                    ->has('recordsTotal')
                    ->has('recordsFiltered')
                    ->has('data', 10)
                    ->has('data.0', function (AssertableJson $json) {
                        $json
                            ->has('id')
                            ->has('image')
                            ->has('str_name_code')
                            ->has('phone')
                            ->has('address_detail')
                            ->has('manage')
                            ->etc();
                    })
                    ->etc();
            });
    }

    /**
     * Datatable query params
     *
     * @return array
     */
    private function datatableQueryParams()
    {
        return [
            'draw' => 1,
            'columns' => [
                [
                    'data' => 'id',
                    'name' => 'id',
                    'searchable' => true,
                    'orderable' => true
                ],
                [
                    'data' => 'image',
                    'name' => 'image',
                    'searchable' => true,
                    'orderable' => true
                ],
                [
                    'data' => 'str_name_code',
                    'name' => 'str_name_code',
                    'searchable' => true,
                    'orderable' => true
                ],
                [
                    'data' => 'phone',
                    'name' => 'phone',
                    'searchable' => true,
                    'orderable' => true
                ],
                [
                    'data' => 'address_detail',
                    'name' => 'address_detail',
                    'searchable' => true,
                    'orderable' => true
                ],
                [
                    'data' => 'manage',
                    'name' => 'manage',
                    'searchable' => false,
                    'orderable' => false
                ]
            ],
            'order' => [
                [
                    'column' => 1,
                    'dir' => 'asc'
                ]
            ],
            'start' => 0,
            'length' => 10,
            'search' => [
                'value' => ''
            ]
        ];
    }
}
