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

class SheetDataTpkIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    protected $seller;

    /** @var \App\Models\SheetName */
    protected $sheetName;

    /**
     * Setup the test
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
    public function seller_can_see_sheet_data_tpk_datatable_page()
    {
        $this->actingAs($this->seller);

        $this->get(route('sheet-data-tpks.index'))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('sheet-data-tpks.index');
    }

    /** @test */
    public function datatable_route_should_respond_correct_json()
    {
        $this->actingAs($this->seller);

        $sheetName = SheetName::factory([ 'seller_id' => $this->seller->id ])->create();

        SheetDataTpk::factory([
            'sheet_name_id' => $sheetName->id,
            'seller_id' => $this->seller->id
        ])->count(20)->create();

        $sheetDocDatatableUri = route('sheet-data-tpks.datatable') . '?' . http_build_query($this->datatableRequestParams());

        $response = $this->getJson($sheetDocDatatableUri);

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
                            ->has('sheet_name')
                            ->has('str_date_amount')
                            ->has('more')
                            ->etc();
                    })
                    ->etc();
            });
    }

    /** @test */
    public function datatable_searching_should_working_properly()
    {
        $this->actingAs($this->seller);

        $sheetName = SheetName::factory([ 'seller_id' => $this->seller->id ])->create();

        SheetDataTpk::where('seller_id', $this->seller->id)->delete();

        SheetDataTpk::factory([
            'sheet_name_id' => $sheetName->id,
            'seller_id' => $this->seller->id
        ])
        ->sequence(
            [ 'amount' => 9911, 'charged_shipping_cost' => 0, 'actual_shipping_cost' => 0 ],
            [ 'amount' => 9912, 'charged_shipping_cost' => 0, 'actual_shipping_cost' => 0 ],
            [ 'amount' => 9999, 'charged_shipping_cost' => 0, 'actual_shipping_cost' => 0 ]
        )
        ->count(3)
        ->create();

        $keyword = '991';

        $sheetDocDatatableUri = route('sheet-data-tpks.datatable') . '?' . http_build_query($this->datatableRequestParams($keyword));

        $response = $this->getJson($sheetDocDatatableUri);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('draw')
                    ->has('recordsTotal')
                    ->has('recordsFiltered')
                    ->has('data', 2)
                    ->has('data.0', function (AssertableJson $json) {
                        $json
                            ->has('id')
                            ->has('sheet_name')
                            ->has('str_date_amount')
                            ->has('more')
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
                    'data' => 'id',
                    'name' => 'id',
                    'searchable' => false,
                    'orderable' => false
                ],
                [
                    'data' => 'sheet_name',
                    'name' => 'sheet_name',
                    'searchable' => true,
                    'orderable' => true
                ],
                [
                    'data' => 'str_date_amount',
                    'name' => 'str_date_amount',
                    'searchable' => false,
                    'orderable' => true
                ],
                [
                    'data' => 'more',
                    'name' => 'more',
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
                'value' => $searchingKeyword
            ]
        ];
    }
}
