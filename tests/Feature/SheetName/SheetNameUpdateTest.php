<?php

namespace Tests\Feature\SheetName;

use App\Enums\UserRoleEnum;
use App\Models\SheetDoc;
use App\Models\SheetName;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SheetNameUpdateTest extends TestCase
{
    use WithFaker;

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
    public function seller_can_get_their_sheet_name_by_id()
    {
        $this->actingAs($this->seller);

        $sheetName = SheetName::factory([
            'sheet_doc_id' => $this->sheetDoc->id,
            'seller_id' => $this->seller->id
        ])->create();

        $response = $this->getJson(route('sheet-names.edit', [
            'sheetDoc' => $this->sheetDoc->id,
            'id' => $sheetName->id
        ]));

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->has('data.sheet_name', function (AssertableJson $json) {
                        $json
                            ->has('id')
                            ->has('sheet_name')
                            ->has('allow_to_sync')
                            ->etc();
                    })
                    ->etc();
            });
    }

    /** @test */
    public function it_should_respond_not_found_if_sheet_name_does_not_exist()
    {
        $this->actingAs($this->seller);

        SheetName::factory([
            'sheet_doc_id' => $this->sheetDoc->id,
            'seller_id' => $this->seller->id
        ])->create();

        $response = $this->getJson(route('sheet-names.edit', [
            'sheetDoc' => $this->sheetDoc->id,
            'id' => 999999
        ]));

        $response
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });
    }

    /** @test */
    public function seller_unable_to_get_their_sheet_name_if_not_their_own()
    {
        $this->actingAs($this->seller);

        $otherSeller = $this->createUser(UserRoleEnum::seller()->value);
        $otherSheetDoc = SheetDoc::factory([ 'seller_id' => $otherSeller->id ])->create();
        $otherSheetName = SheetName::factory([
                'sheet_doc_id' => $otherSheetDoc->id,
                'seller_id' => $otherSeller->id
            ])->create();

        $response = $this->getJson(route('sheet-names.edit', [
            'sheetDoc' => $otherSheetDoc->id,
            'id' => $otherSheetName->id
        ]));

        $response
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });
    }

    /** @test */
    public function seller_can_update_their_sheet_name()
    {
        $this->actingAs($this->seller);

        $sheetName = SheetName::factory([
                'sheet_doc_id' => $this->sheetDoc->id,
                'seller_id' => $this->seller->id,
                'allow_to_sync' => false
            ])->create();

        $newData = [
            'sheet_name' => substr($this->faker->words(3, true), 0, 50),
            'allow_to_sync' => true
        ];

        $response = $this->postJson(route('sheet-names.update', [
            'sheetDoc' => $this->sheetDoc->id,
            'id' => $sheetName->id
        ]), $newData);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });

        $this->assertDatabaseHas(SheetName::class, array_merge($newData, [
            'sheet_doc_id' => $this->sheetDoc->id,
            'seller_id' => $this->seller->id,
        ]));
    }

    /** @test */
    public function seller_unable_to_update_other_seller_sheet_name()
    {
        $this->actingAs($this->seller);

        $otherSeller = $this->createUser(UserRoleEnum::seller()->value);
        $otherSheetDoc = SheetDoc::factory([ 'seller_id' => $otherSeller->id ])->create();
        $otherSheetName = SheetName::factory([
                'sheet_doc_id' => $otherSheetDoc->id,
                'seller_id' => $otherSeller->id
            ])->create();

        $response = $this->postJson(route('sheet-names.update', [
            'sheetDoc' => $otherSheetDoc->id,
            'id' => $otherSheetName->id
        ]), [
            'sheet_name' => substr($this->faker->words(3, true), 0, 50)
        ]);

        $response
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });
    }

    /** @test */
    public function seller_unable_to_update_duplicated_sheet_name_in_same_spreadsheet()
    {
        $firstSheetName = SheetName::factory([
                'sheet_doc_id' => $this->sheetDoc->id,
                'seller_id' => $this->seller->id
            ])
            ->create();

        $secondSheetName = SheetName::factory([
                'sheet_doc_id' => $this->sheetDoc->id,
                'seller_id' => $this->seller->id
            ])
            ->create();

        $this->actingAs($this->seller);

        $formData = [
            'sheet_name' => $secondSheetName->sheet_name,
            'allow_to_sync' => true
        ];

        $response = $this->postJson(route('sheet-names.update', [
            'sheetDoc' => $this->sheetDoc->id,
            'id' => $firstSheetName->id
        ]), $formData);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'sheet_name'
            ]);
    }

    /** @test */
    public function seller_can_update_same_sheet_name_with_previous_name()
    {
        $sheetName = SheetName::factory([
            'sheet_doc_id' => $this->sheetDoc->id,
            'seller_id' => $this->seller->id,
        ])->create();

        $this->actingAs($this->seller);

        $formData = [
            'sheet_name' => $sheetName->sheet_name,
            'allow_to_sync' => true
        ];

        $response = $this->postJson(route('sheet-names.update', [
            'sheetDoc' => $this->sheetDoc->id,
            'id' => $sheetName->id
        ]), $formData);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });

        $this->assertDatabaseHas(SheetName::class, array_merge($formData, [
            'sheet_doc_id' => $this->sheetDoc->id,
            'seller_id' => $this->seller->id
        ]));
    }

    /** @test */
    public function seller_can_update_same_sheet_name_in_his_own_other_spreadsheet()
    {
        $firstSheetDoc = SheetDoc::factory([ 'seller_id' => $this->seller->id ])->create();
        $firstSheetName = SheetName::factory([
                'sheet_doc_id' => $firstSheetDoc->id,
                'seller_id' => $this->seller->id
            ])
            ->create();

        $secondSheetDoc = SheetDoc::factory([ 'seller_id' => $this->seller->id ])->create();
        $secondSheetName = SheetName::factory([
                'sheet_doc_id' => $secondSheetDoc->id,
                'seller_id' => $this->seller->id
            ])
            ->create();

        $this->actingAs($this->seller);

        $formData = [
            'sheet_name' => $firstSheetName->sheet_name,
            'allow_to_sync' => true
        ];

        $response = $this->postJson(route('sheet-names.update', [
            'sheetDoc' => $secondSheetDoc->id,
            'id' => $secondSheetName->id
        ]), $formData);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });

        $this->assertDatabaseHas(SheetName::class, array_merge($formData, [
            'sheet_doc_id' => $secondSheetDoc->id,
            'seller_id' => $this->seller->id
        ]));
    }

    /** @test */
    public function a_seller_can_update_same_sheet_name_with_other_seller_spreadsheet()
    {
        $otherSeller = $this->createUser(UserRoleEnum::seller()->value);
        $otherSheetDoc = SheetDoc::factory([ 'seller_id' => $otherSeller->id ])->create();

        // other seller sheet name
        $otherSheetName = SheetName::factory([
                'sheet_doc_id' => $otherSheetDoc->id,
                'seller_id' => $otherSeller->id
            ])
            ->create();

        $mainSheetName = SheetName::factory([
                'sheet_doc_id' => $this->sheetDoc->id,
                'seller_id' => $this->seller->id
            ])
            ->create();

        $this->actingAs($this->seller);

        $formData = [
            'sheet_name' => $otherSheetName->sheet_name,
            'allow_to_sync' => true
        ];

        $response = $this->postJson(route('sheet-names.update', [
            'sheetDoc' => $this->sheetDoc->id,
            'id' => $mainSheetName->id
        ]), $formData);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });

        $this->assertDatabaseHas(SheetName::class, array_merge($formData, [
            'sheet_doc_id' => $this->sheetDoc->id,
            'seller_id' => $this->seller->id
        ]));
    }

    /** @test */
    public function all_required_fields_of_store_form_are_validated()
    {
        $this->actingAs($this->seller);

        $sheetName = SheetName::factory([
            'sheet_doc_id' => $this->sheetDoc->id,
            'seller_id' => $this->seller->id
        ])->create();

        $response = $this->postJson(route('sheet-names.update', [
            'sheetDoc' => $this->sheetDoc->id,
            'id' => $sheetName->id
        ]), []);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'sheet_name', 'allow_to_sync'
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

        $sheetName = SheetName::factory([
            'sheet_doc_id' => $this->sheetDoc->id,
            'seller_id' => $this->seller->id
        ])->create();

        $response = $this->postJson(route('sheet-names.update', [
            'sheetDoc' => $this->sheetDoc->id,
            'id' => $sheetName->id
        ]), $requestData);

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
            '`Sheet Name` not provided' => [
                [
                    'sheet_name' => '',
                    'allow_to_sync' => ''
                ],
                [
                    'sheet_name',
                    'allow_to_sync'
                ]
            ],
            '`Sheet Name` more than 50 characters' => [
                [
                    'sheet_name' => str_repeat('a', 51),
                    'allow_to_sync' => $faker->boolean()
                ],
                [
                    'sheet_name'
                ]
            ],
            '`Allow to Sync` is not provided' => [
                [
                    'sheet_name' => substr($faker->words(3, true), 0, 50),
                    'allow_to_sync' => ''
                ],
                [
                    'allow_to_sync'
                ]
            ],
            '`Allow to Sync` is not boolean value' => [ // boolean values are true/false/0/1
                [
                    'sheet_name' => substr($faker->words(3, true), 0, 50),
                    'allow_to_sync' => '2'
                ],
                [
                    'allow_to_sync'
                ]
            ],
        ];
    }
}
