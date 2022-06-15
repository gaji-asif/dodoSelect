<?php

namespace Tests\Feature\Shop;

use App\Enums\UserRoleEnum;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ShopSelectTest extends TestCase
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
    public function it_can_fetch_select_two_server_side()
    {
        Shop::factory([ 'seller_id' => $this->seller->id ])
            ->count(25)
            ->create();

        $this->actingAs($this->seller);

        $response = $this->getJson(route('shop.select', [
            'page' => 1,
            'search' => null
        ]));

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('pagination.more')
                    ->has('results', 10)
                    ->has('results.0', function (AssertableJson $json) {
                        $json
                            ->has('id')
                            ->has('text');
                    });
            });
    }

    /** @test */
    public function it_can_fetch_select_two_server_side_with_extra_options()
    {
        $shops = Shop::factory([ 'seller_id' => $this->seller->id ])
            ->count(25)
            ->create();

        $shopsArray = $shops->sortBy('name', SORT_NATURAL)
            ->take(10)
            ->map(function ($shop) {
                return [
                    'id' => $shop->id,
                    'text' => $shop->name
                ];
            })->toArray();

        $this->actingAs($this->seller);

        $arrayOfOptions = [
            [
                'id' => '-1',
                'text' => 'All Shops'
            ],
        ];

        $response = $this->getJson(route('shop.select', [
            'page' => 1,
            'search' => null,
            'extends' => [
                'options' => $arrayOfOptions
            ]
        ]));

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('pagination.more')
                    ->has('results', 11)
                    ->has('results.0', function (AssertableJson $json) {
                        $json
                            ->has('id')
                            ->has('text');
                    });
            })
            ->assertSimilarJson([
                'pagination' => [
                    'more' => true,
                ],
                'results' => array_merge($arrayOfOptions, $shopsArray)
            ]);
    }
}
