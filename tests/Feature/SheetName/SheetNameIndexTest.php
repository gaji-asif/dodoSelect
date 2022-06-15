<?php

namespace Tests\Feature\SheetName;

use App\Enums\UserRoleEnum;
use App\Models\SheetDoc;
use App\Models\SheetName;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SheetNameIndexTest extends TestCase
{
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    protected $seller;

    /** @var \App\Models\SheetDoc */
    protected $sheetDoc;

    /**
     * Setup the test
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->seller = $this->createUser(UserRoleEnum::seller()->value);
        $this->sheetDoc = SheetDoc::factory([ 'seller_id' => $this->seller->id ])->create();
    }

    /** @test */
    public function seller_can_see_their_sheet_names_datatable_page()
    {
        $this->actingAs($this->seller);
        $sheetDoc = SheetDoc::factory([ 'seller_id' => $this->seller->id ])->create();

        $this->get(route('sheet-names.index', [ 'sheetDoc' => $sheetDoc->id ]))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('sheet-names.index');
    }

    /** @test */
    public function seller_can_not_see_other_sheet_names_page()
    {
        $this->actingAs($this->seller);

        $otherSeller = $this->createUser(UserRoleEnum::seller()->value);
        $sheetDoc = SheetDoc::factory([ 'seller_id' => $otherSeller->id ])->create();

        $this->get(route('sheet-names.index', [ 'sheetDoc' => $sheetDoc->id ]))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function datatable_route_should_respond_correct_json()
    {
        $this->actingAs($this->seller);

        SheetName::factory([
                'sheet_doc_id' => $this->sheetDoc->id,
                'seller_id' => $this->seller->id
            ])
            ->count(20)
            ->create();

        $sheetDocDatatableUri = route('sheet-names.datatable', [ 'sheetDoc' => $this->sheetDoc->id ]) . '?' . http_build_query($this->datatableRequestParams());

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
                            ->has('DT_RowIndex')
                            ->has('sheet_name')
                            ->has('str_allow_to_sync')
                            ->has('str_last_sync')
                            ->has('str_sync_status')
                            ->has('actions')
                            ->etc();
                    })
                    ->etc();
            });
    }

    /** @test */
    public function datatable_searching_should_working_properly()
    {
        $this->actingAs($this->seller);

        SheetName::factory([
                'sheet_doc_id' => $this->sheetDoc->id,
                'seller_id' => $this->seller->id
            ])
            ->sequence(
                [ 'sheet_name' => 'First Sheetname' ],
                [ 'sheet_name' => 'Second Sheetname' ],
                [ 'sheet_name' => 'Another name' ],
            )
            ->count(3)
            ->create();

        $keyword = 'sheetname';

        $sheetDocDatatableUri = route('sheet-names.datatable', [ 'sheetDoc' => $this->sheetDoc->id ]) . '?' . http_build_query($this->datatableRequestParams($keyword));

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
                            ->has('DT_RowIndex')
                            ->has('sheet_name')
                            ->has('str_allow_to_sync')
                            ->has('str_last_sync')
                            ->has('str_sync_status')
                            ->has('actions')
                            ->etc();
                    })
                    ->etc();
            });
    }

    /**
     * @dataProvider columns_to_order
     * @test
     *
     * @param  array  $orderRequest
     */
    public function datatable_can_order_by_possible_columns(array $orderRequest)
    {
        $this->actingAs($this->seller);

        SheetName::factory([
                'sheet_doc_id' => $this->sheetDoc->id,
                'seller_id' => $this->seller->id
            ])
            ->count(20)
            ->create();

        $sheetDocDatatableUri = route('sheet-names.datatable', [ 'sheetDoc' => $this->sheetDoc->id ]) . '?' . http_build_query($this->datatableRequestParams(null, $orderRequest));

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
                            ->has('DT_RowIndex')
                            ->has('sheet_name')
                            ->has('str_allow_to_sync')
                            ->has('str_last_sync')
                            ->has('str_sync_status')
                            ->has('actions')
                            ->etc();
                    })
                    ->etc();
            });
    }

    /**
     * Make request format for datatable
     *
     * @param  string|null  $searchingKeyword
     * @param  array|null  $orderRequest
     * @return array
     */
    private function datatableRequestParams($searchingKeyword = null, array $orderRequest = [])
    {
        if (empty($orderRequest)) {
            $orderRequest = [
                'column' => 1,
                'dir' => 'asc'
            ];
        }

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
                    'data' => 'sheet_name',
                    'name' => 'sheet_name',
                    'searchable' => true,
                    'orderable' => true
                ],
                [
                    'data' => 'str_allow_to_sync',
                    'name' => 'str_allow_to_sync',
                    'searchable' => false,
                    'orderable' => true
                ],
                [
                    'data' => 'str_last_sync',
                    'name' => 'str_last_sync',
                    'searchable' => false,
                    'orderable' => true
                ],
                [
                    'data' => 'str_status',
                    'name' => 'str_status',
                    'searchable' => false,
                    'orderable' => true
                ],
                [
                    'data' => 'actions',
                    'name' => 'actions',
                    'searchable' => false,
                    'orderable' => false
                ]
            ],
            'order' => [
                $orderRequest
            ],
            'start' => 0,
            'length' => 10,
            'search' => [
                'value' => $searchingKeyword
            ]
        ];
    }

    /**
     * The data provider for columns_to_order
     *
     * @return array
     */
    public function columns_to_order()
    {
        return [
            'Order by sheet_name ASC' => [
                [
                    'column' => 1,
                    'dir' => 'asc'
                ]
            ],
            'Order by sheet_name DESC' => [
                [
                    'column' => 1,
                    'dir' => 'desc'
                ]
            ],
            'Order by allow_to_sync ASC' => [
                [
                    'column' => 2,
                    'dir' => 'asc'
                ]
            ],
            'Order by allow_to_sync DESC' => [
                [
                    'column' => 2,
                    'dir' => 'desc'
                ]
            ],
            'Order by last_sync ASC' => [
                [
                    'column' => 3,
                    'dir' => 'asc'
                ]
            ],
            'Order by last_sync DESC' => [
                [
                    'column' => 3,
                    'dir' => 'desc'
                ]
            ],
            'Order by sync_status ASC' => [
                [
                    'column' => 4,
                    'dir' => 'asc'
                ]
            ],
            'Order by sync_status DESC' => [
                [
                    'column' => 4,
                    'dir' => 'desc'
                ]
            ],
        ];
    }
}
