<?php

namespace Tests\Feature\SheetDataTpk;

use App\Enums\UserRoleEnum;
use App\Models\SheetDataTpk;
use App\Models\SheetName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class OrderAnalysisTest extends TestCase
{
    use RefreshDatabase;

    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    protected $seller;

    /** @var \App\Models\SheetName */
    protected $sheetName;

    /**
     * Setup test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->seller = $this->createUser(UserRoleEnum::seller()->value);
        $this->sheetName = SheetName::factory([ 'seller_id' => $this->seller->id ])->create();
    }

    /** @test */
    public function seller_can_see_order_analysis_page()
    {
        $this->actingAs($this->seller);

        $this->get(route('sheet-data-tpks.order-analysis'))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('sheet-data-tpks.order-analysis');
    }

    /** @test */
    public function datatable_route_should_respond_correct_json()
    {
        SheetDataTpk::factory([
                'date' => date('Y-m-01'),
                'sheet_name_id' => $this->sheetName->id,
                'seller_id' => $this->seller->id
            ])
            ->count(20)
            ->create();

        $this->actingAs($this->seller);

        $datatableUri = route('sheet-data-tpks.order-analysis-datatable') . '?' . http_build_query($this->datatableRequestParams());
        $this->getJson($datatableUri)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('draw')
                    ->has('recordsTotal')
                    ->has('recordsFiltered')
                    ->has('data.0', function (AssertableJson $json) {
                        $json
                            ->has('DT_RowIndex')
                            ->has('str_shop_name')
                            ->has('str_date')
                            ->has('total_orders')
                            ->has('total_amount')
                            ->etc();
                    })
                    ->etc();
            });
    }

    /**
     * Make request format for datatable
     *
     * @param  string|null  $searchingKeyword
     * @return array
     */
    private function datatableRequestParams($searchingKeyword = null)
    {
        return [
            'draw' => 1,
            'columns' => [
                [
                    'data' => 'DT_RowIndex',
                    'name' => 'DT_RowIndex',
                    'searchable' => false,
                    'orderable' => false
                ],
                [
                    'data' => 'str_shop_name',
                    'name' => 'str_shop_name',
                    'searchable' => false,
                    'orderable' => false
                ],
                [
                    'data' => 'str_date',
                    'name' => 'str_date',
                    'searchable' => false,
                    'orderable' => false
                ],
                [
                    'data' => 'total_orders',
                    'name' => 'total_orders',
                    'searchable' => false,
                    'orderable' => false
                ],
                [
                    'data' => 'total_amount',
                    'name' => 'total_amount',
                    'searchable' => false,
                    'orderable' => false
                ],
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
                'value' => $searchingKeyword
            ],
            'date_range' => date('Y-01-01') . ' to ' . date('Y-12-31'),
            'interval' => 'per_day'
        ];
    }

    /** @test */
    public function it_should_respond_correct_data_for_chart()
    {
        SheetDataTpk::factory()
            ->sequence(
                [
                    'date' => date('Y-m-01'),
                    'sheet_name_id' => $this->sheetName->id,
                    'seller_id' => $this->seller->id
                ],
                [
                    'date' => date('Y-m-02'),
                    'sheet_name_id' => $this->sheetName->id,
                    'seller_id' => $this->seller->id
                ],
                [
                    'date' => date('Y-m-03'),
                    'sheet_name_id' => $this->sheetName->id,
                    'seller_id' => $this->seller->id
                ],
                [
                    'date' => date('Y-m-04'),
                    'sheet_name_id' => $this->sheetName->id,
                    'seller_id' => $this->seller->id
                ],
                [
                    'date' => date('Y-m-05'),
                    'sheet_name_id' => $this->sheetName->id,
                    'seller_id' => $this->seller->id
                ],
            )
            ->count(30)
            ->create();

        $this->actingAs($this->seller);

        $requestData = [
            'date_range' => date('Y-01-01') . ' to ' . date('Y-12-31'),
            'interval' => 'per_day'
        ];

        $chartUri = route('sheet-data-tpks.order-analysis-chart') . '?' . http_build_query($requestData);
        $this->getJson($chartUri)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->has('data.data')
                    ->has('data.data.0', function (AssertableJson $json) {
                        $json
                            ->has('str_date')
                            ->has('total_orders')
                            ->has('total_amount')
                            ->has('shop');
                    });
            });
    }
}
