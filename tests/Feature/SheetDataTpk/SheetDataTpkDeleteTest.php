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

class SheetDataTpkDeleteTest extends TestCase
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
    public function seller_can_batch_delete_the_data_tpk()
    {
        $sheetDataTpks = SheetDataTpk::factory([
                'sheet_name_id' => $this->sheetName->id,
                'seller_id' => $this->seller->id,
            ])
            ->count(5)
            ->create();

        $this->actingAs($this->seller);

        $response = $this->postJson(route('sheet-data-tpks.batch-delete'), [
                'ids' => $sheetDataTpks->pluck('id')->toArray(),
            ]);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });

        foreach ($sheetDataTpks as $sheetDataTpk) {
            $this->assertDeleted(SheetDataTpk::class, [
                'id' => $sheetDataTpk->id
            ]);
        }
    }

    /** @test */
    public function seller_unable_to_batch_delete_the_data_tpk_if_not_their_own()
    {
        $sheetDataTpks = SheetDataTpk::factory([
            'sheet_name_id' => $this->sheetName->id,
            'seller_id' => $this->seller->id,
        ])
        ->count(5)
        ->create();

        $otherSeller = $this->createUser(UserRoleEnum::seller()->value);
        $this->actingAs($otherSeller);

        $response = $this->postJson(route('sheet-data-tpks.batch-delete'), [
                'ids' => $sheetDataTpks->pluck('id')->toArray(),
            ]);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });

        foreach ($sheetDataTpks as $sheetDataTpk) {
            $this->assertDatabaseHas(SheetDataTpk::class, [
                'id' => $sheetDataTpk->id
            ]);
        }
    }

    /** @test */
    public function it_should_fail_if_no_ids_was_submitted()
    {
        $this->actingAs($this->seller);

        $response = $this->postJson(route('sheet-data-tpks.batch-delete'), []);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'ids'
            ]);
    }

    /** @test */
    public function it_should_fail_if_some_ids_contains_not_integer_values()
    {
        $this->actingAs($this->seller);

        $response = $this->postJson(route('sheet-data-tpks.batch-delete'), [
                'ids' => [
                    'not-integer',
                    1,
                    2,
                    3,
                ]
            ]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors([
                'ids.0'
            ]);
    }

    /** @test */
    public function it_should_fail_if_submitted_ids_more_than_100_items()
    {
        $sheetDataTpks = SheetDataTpk::factory([
                'sheet_name_id' => $this->sheetName->id,
                'seller_id' => $this->seller->id,
            ])
            ->count(101)
            ->create();

        $this->actingAs($this->seller);

        $response = $this->postJson(route('sheet-data-tpks.batch-delete'), [
                'ids' => $sheetDataTpks->pluck('id')->toArray(),
            ]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });
    }
}
