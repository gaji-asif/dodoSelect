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

class SheetDocStoreTest extends TestCase
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
    public function seller_can_store_new_sheet_doc()
    {
        $this->actingAs($this->seller);

        $formData = [
            'file_name' => substr($this->faker->sentence(3), 0, 50),
            'spreadsheet_id' => Str::random(44)
        ];

        $response = $this->postJson(route('sheet-docs.store'), $formData);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });

        $this->assertDatabaseHas(SheetDoc::class, array_merge($formData, [ 'seller_id' => $this->seller->id ]));
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

        $response = $this->postJson(route('sheet-docs.store'), $requestData);

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
