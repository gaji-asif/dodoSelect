<?php

namespace Tests\Feature\SheetName;

use App\Enums\UserRoleEnum;
use App\Models\SheetDataTpk;
use App\Models\SheetDoc;
use App\Models\SheetName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Log;
use Tests\TestCase;

class SheetNameDeleteTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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
    public function seller_can_delete_their_sheet_name()
    {
        $this->actingAs($this->seller);

        $sheetName = SheetName::factory([
            'sheet_doc_id' => $this->sheetDoc->id,
            'seller_id' => $this->seller->id
        ])->create();

        $response = $this->postJson(route('sheet-names.delete', [
            'sheetDoc' => $this->sheetDoc->id,
            'id' => $sheetName->id
        ]));

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });

        $this->assertDeleted(SheetName::class, [
            'id' => $sheetName->id
        ]);
    }

    /** @test */
    public function seller_unable_to_delete_other_seller_sheet_name()
    {
        $this->actingAs($this->seller);

        $otherSeller = $this->createUser(UserRoleEnum::seller()->value);
        $otherSheetDoc = SheetDoc::factory([ 'seller_id' => $otherSeller->id ])->create();
        $otherSheetName = SheetName::factory([
            'sheet_doc_id' => $otherSheetDoc->id,
            'seller_id' => $otherSeller->id
        ])->create();

        $response = $this->postJson(route('sheet-names.delete', [
            'sheetDoc' => $otherSheetDoc->id,
            'id' => $otherSheetName->id
        ]));

        $response
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });
    }

    /** @test */
    public function it_should_delete_sheet_data_tpk_after_corresponding_sheet_was_deleted()
    {
        $this->actingAs($this->seller);

        $sheetName = SheetName::factory([
                'sheet_doc_id' => $this->sheetDoc->id,
                'seller_id' => $this->seller->id
            ])
            ->create();

        $sheetDataTpks = SheetDataTpk::factory([
                'sheet_name_id' => $sheetName->id,
                'seller_id' => $this->seller->id
            ])
            ->count(3)
            ->create();

        $response = $this->postJson(route('sheet-names.delete', [
            'sheetDoc' => $this->sheetDoc->id,
            'id' => $sheetName->id
        ]));

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });

        $this->assertDeleted(SheetName::class, [
            'id' => $sheetName
        ]);

        foreach ($sheetDataTpks as $sheetDataTpk) {
            $this->assertDeleted(SheetDataTpk::class, [
                'id' => $sheetDataTpk->id
            ]);
        }
    }
}
