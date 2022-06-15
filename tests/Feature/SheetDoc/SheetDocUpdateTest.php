<?php

namespace Tests\Feature\SheetDoc;

use App\Enums\UserRoleEnum;
use App\Models\SheetDoc;
use Faker\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SheetDocUpdateTest extends TestCase
{
    use WithFaker;

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

    /** @test */
    public function seller_can_get_sheet_doc_detail_by_id()
    {
        $this->actingAs($this->seller);

        $sheetDoc = SheetDoc::factory([ 'seller_id' => $this->seller->id ])->create();

        $response = $this->getJson(route('sheet-docs.edit', [ 'id' => $sheetDoc->id ]));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->has('data.sheet_doc', function (AssertableJson $json) {
                        $json
                            ->has('id')
                            ->has('file_name')
                            ->has('spreadsheet_id')
                            ->etc();
                    })
                    ->etc();
            });
    }

    /** @test */
    public function it_should_respond_not_found_if_sheet_doc_not_exists()
    {
        $this->actingAs($this->seller);

        $response = $this->getJson(route('sheet-docs.edit', [ 'id' => 999999 ]));
        $response
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });
    }

    /** @test */
    public function seller_can_update_their_sheet_doc()
    {
        $this->actingAs($this->seller);
        $sheetDoc = SheetDoc::factory([ 'seller_id' => $this->seller->id ])->create();

        $newData = [
            'file_name' => substr($this->faker->sentence(3), 0, 50),
            'spreadsheet_id' => Str::random(44)
        ];

        $response = $this->postJson(route('sheet-docs.update', [ 'id' => $sheetDoc->id ]), $newData);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });

        $this->assertDatabaseHas(SheetDoc::class, $newData);
    }

    /** @test */
    public function seller_can_not_update_sheet_doc_if_not_their_own()
    {
        $anotherSeller = $this->createUser(UserRoleEnum::seller()->value);
        $this->actingAs($anotherSeller);

        $sheetDoc = SheetDoc::factory([ 'seller_id' => $this->seller->id ])->create();

        $newData = [
            'file_name' => substr($this->faker->sentence(3), 0, 50),
            'spreadsheet_id' => Str::random(44)
        ];

        $response = $this->postJson(route('sheet-docs.update', [ 'id' => $sheetDoc->id ]), $newData);

        $response
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });
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
        $this->actingAs($this->seller);
        $sheetDoc = SheetDoc::factory([ 'seller_id' => $this->seller->id ])->create();

        $response = $this->postJson(route('sheet-docs.update', [ 'id' => $sheetDoc->id ]), $requestData);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors($errorFields);
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
            'No data provided' => [
                [
                    'file_name' => '',
                    'spreadsheet_id' => ''
                ],
                [
                    'file_name',
                    'spreadsheet_id'
                ]
            ],
            'File Name not provided' => [
                [
                    'file_name' => '',
                    'spreadsheet_id' => Str::random(44)
                ],
                'file_name'
            ],
            'File Name more than 50 characters' => [
                [
                    'file_name' => str_repeat('a', 51),
                    'spreadsheet_id' => Str::random(44)
                ],
                'file_name'
            ],
            'Spreadsheet ID less than 44 characters' => [
                [
                    'file_name' => substr($faker->sentence(3), 0, 50),
                    'spreadsheet_id' => Str::random(43)
                ],
                'spreadsheet_id'
            ],
            'Spreadsheet ID less than 44 characters' => [
                [
                    'file_name' => substr($faker->sentence(3), 0, 50),
                    'spreadsheet_id' => Str::random(43)
                ],
                'spreadsheet_id'
            ],
        ];
    }
}
