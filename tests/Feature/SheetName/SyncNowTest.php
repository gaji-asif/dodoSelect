<?php

namespace Tests\Feature\SheetName;

use App\Enums\SheetNameSyncStatusEnum;
use App\Enums\UserRoleEnum;
use App\Jobs\SheetTpkPackingSync;
use App\Models\SheetDoc;
use App\Models\SheetName;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SyncNowTest extends TestCase
{
    /** @var \Illuminate\Contracts\Auth\Authenticatable */
    protected $seller;

    /** @var \App\Models\SheetDoc */
    protected $sheetDoc;

    /** @var \App\Models\SheetName */
    protected $sheetName;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->seller = $this->createUser(UserRoleEnum::seller()->value);
        $this->sheetDoc = SheetDoc::factory([ 'seller_id' => $this->seller->id ])->create();

        $this->sheetName = SheetName::factory([
            'sheet_doc_id' => $this->sheetDoc->id,
            'seller_id' => $this->seller->id
        ])->create();
    }

    /** @test */
    public function it_can_sync_now_the_sheet()
    {
        $this->actingAs($this->seller);

        Bus::fake();

        $response = $this->postJson(route('sheet-names.sync-now', [
            'sheetDoc' => $this->sheetDoc->id,
            'id' => $this->sheetName->id
        ]));

        Bus::assertDispatched(SheetTpkPackingSync::class);

        $this->assertDatabaseHas(SheetName::class, [
            'id' => $this->sheetName->id,
            'sync_status' => SheetNameSyncStatusEnum::syncing()->value
        ]);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });
    }

    /** @test */
    public function seller_unable_to_sync_now_sheet_if_not_their_own()
    {
        $otherSeller = $this->createUser(UserRoleEnum::seller()->value);
        $this->actingAs($otherSeller);

        $response = $this->postJson(route('sheet-names.sync-now', [
            'sheetDoc' => $this->sheetDoc->id,
            'id' => $this->sheetName->id
        ]));

        $response
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->has('message')
                    ->etc();
            });
    }
}
