<?php

namespace Tests\Feature\SheetDoc;

use App\Enums\UserRoleEnum;
use App\Models\SheetDoc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SheetDocDeleteTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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
    public function seller_can_delete_their_sheet_doc()
    {
        $this->actingAs($this->seller);
        $sheetDoc = SheetDoc::factory([ 'seller_id' => $this->seller->id ])->create();

        $response = $this->postJson(route('sheet-docs.delete', [ 'id' => $sheetDoc->id ]));

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });

        $this->assertSoftDeleted(SheetDoc::class, [ 'id' => $sheetDoc->id ]);
    }

    /** @test */
    public function seller_can_not_update_sheet_doc_if_not_their_own()
    {
        $anotherSeller = $this->createUser(UserRoleEnum::seller()->value);
        $this->actingAs($anotherSeller);

        $sheetDoc = SheetDoc::factory([ 'seller_id' => $this->seller->id ])->create();

        $response = $this->postJson(route('sheet-docs.delete', [ 'id' => $sheetDoc->id ]));

        $response
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });
    }
}
