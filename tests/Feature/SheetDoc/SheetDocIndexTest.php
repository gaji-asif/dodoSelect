<?php

namespace Tests\Feature\SheetDoc;

use App\Enums\UserRoleEnum;
use App\Models\SheetDoc;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SheetDocIndexTest extends TestCase
{
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    protected $seller;

    /**
     * Setup the test
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->seller = $this->createUser(UserRoleEnum::seller()->value);
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
                    'data' => 'file_name',
                    'name' => 'file_name',
                    'searchable' => true,
                    'orderable' => true
                ],
                [
                    'data' => 'spreadsheet_id',
                    'name' => 'spreadsheet_id',
                    'searchable' => true,
                    'orderable' => true
                ],
                [
                    'data' => 'actions',
                    'name' => null,
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

    /** @test */
    public function seller_can_see_sheet_docs_datatable_page()
    {
        $this->actingAs($this->seller);

        $this->get(route('sheet-docs.index'))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('sheet-docs.index');
    }

    /** @test */
    public function datatable_route_should_respond_correct_json()
    {
        $this->actingAs($this->seller);

        SheetDoc::factory([ 'seller_id' => $this->seller->id ])
            ->count(20)
            ->create();

        $sheetDocDatatableUri = route('sheet-docs.datatable') . '?' . http_build_query($this->datatableRequestParams());

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
                            ->has('file_name')
                            ->has('spreadsheet_id')
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

        SheetDoc::factory([ 'seller_id' => $this->seller->id ])
            ->sequence(
                [ 'file_name' => 'First Document' ],
                [ 'file_name' => 'Second Document' ],
                [ 'file_name' => 'Another one' ],
            )
            ->count(3)
            ->create();

        $keyword = 'document';
        $sheetDocDatatableUri = route('sheet-docs.datatable') . '?' . http_build_query($this->datatableRequestParams($keyword));

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
                            ->has('file_name')
                            ->has('spreadsheet_id')
                            ->has('actions')
                            ->etc();
                    })
                    ->etc();
            });
    }
}
