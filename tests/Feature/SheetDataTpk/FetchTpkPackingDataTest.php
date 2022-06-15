<?php

namespace Tests\Feature\SheetDataTpk;

use App\Actions\GoogleSheet\FetchTpkPackingData;
use App\Actions\GoogleSheet\StoreTpkPackingData;
use App\Actions\GoogleSheet\TransformDate;
use App\Enums\SheetNameSyncStatusEnum;
use App\Jobs\SheetTpkPackingSync;
use App\Models\SheetDataTpk;
use App\Models\SheetName;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class FetchTpkPackingDataTest extends TestCase
{
    /** @var \Illuminate\Support\Collection */
    protected $sheetNames;

    /**
     * Setup the test.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->sheetNames = SheetName::factory([
                'allow_to_sync' => true,
                'last_sync' => null,
            ])
            ->count(3)
            ->create();
    }

    /** @test */
    public function it_should_dispatch_sheet_tpk_packing_sync_job()
    {
        Bus::fake();

        FetchTpkPackingData::make()->handle();

        Bus::assertDispatched(SheetTpkPackingSync::class);
    }

    /** @test */
    public function it_should_store_tpk_packing_data_with_valid_dates_and_amount_only()
    {
        $firstSheetName = $this->sheetNames->first();
        $firstSheetName->sync_status = SheetNameSyncStatusEnum::syncing();
        $firstSheetName->save();

        DB::table((new SheetDataTpk())->getTable())->truncate();

        StoreTpkPackingData::make()->handle($this->dummy_sheet_data(), $firstSheetName);

        $validDummyData = $this->dummy_sheet_data()[0];

        $this->assertDatabaseHas(SheetDataTpk::class, [
            'date' => TransformDate::make()->handle($validDummyData[0]),
            'amount' => $validDummyData[6]
        ]);

        $this->assertDatabaseCount(SheetDataTpk::class, 1);
    }

    /** @test */
    public function it_should_execute_sheet_data_tpk_sync_command_successfully()
    {
        Bus::fake();

        $this->artisan('dodo:sheet-data-tpk-sync')
            ->expectsOutput('We are syncing data in the background...')
            ->assertSuccessful();

        Bus::assertDispatched(SheetTpkPackingSync::class);
    }

    /** @test */
    public function it_should_only_get_available_to_sync_sheet_names_data()
    {
        SheetName::where('id', '>', 0)->delete();

        TestTime::freeze();

        SheetName::factory()
            ->sequence(
                [
                    'sheet_name' => 'available_1',
                    'allow_to_sync' => true,
                    'last_sync' => null,
                    'sync_status' => SheetNameSyncStatusEnum::none()->value
                ],
                [
                    'sheet_name' => 'available_2',
                    'allow_to_sync' => true,
                    'last_sync' => Carbon::now()->subMinutes(11)->format('Y-m-d H:i:s'),
                    'sync_status' => SheetNameSyncStatusEnum::none()->value
                ],
                [
                    'sheet_name' => 'available_3',
                    'allow_to_sync' => true,
                    'last_sync' => null,
                    'sync_status' => SheetNameSyncStatusEnum::syncing()->value
                ],
                [
                    'sheet_name' => 'available_4',
                    'allow_to_sync' => true,
                    'last_sync' => Carbon::now()->subMinutes(11)->format('Y-m-d H:i:s'),
                    'sync_status' => SheetNameSyncStatusEnum::syncing()->value
                ],
                [
                    'sheet_name' => 'NOT_available_1',
                    'allow_to_sync' => true,
                    'last_sync' => Carbon::now()->subMinutes(9)->format('Y-m-d H:i:s'),
                    'sync_status' => SheetNameSyncStatusEnum::none()->value
                ],
                [
                    'sheet_name' => 'NOT_available_2',
                    'allow_to_sync' => false,
                    'last_sync' => null,
                    'sync_status' => SheetNameSyncStatusEnum::none()->value
                ],
                [
                    'sheet_name' => 'NOT_available_2',
                    'allow_to_sync' => false,
                    'last_sync' => Carbon::now()->subMinutes(11)->format('Y-m-d H:i:s'),
                    'sync_status' => SheetNameSyncStatusEnum::none()->value
                ],
                [
                    'sheet_name' => 'NOT_available_3',
                    'allow_to_sync' => false,
                    'last_sync' => Carbon::now()->subMinutes(9)->format('Y-m-d H:i:s'),
                    'sync_status' => SheetNameSyncStatusEnum::none()->value
                ]
            )
            ->count(8)
            ->create();

        $totalAvailableToSync = SheetName::availableToSync()->count();

        $this->assertEquals(4, $totalAvailableToSync);
    }

    /**
     * Dummy data
     *
     * @return array
     */
    public function dummy_sheet_data()
    {
        return [
            [
              "01/02/2020",
              "วินิจ",
              "ดรุกาญจน์พฤฒิ",
              "15.5 x 23.5cm ถุงซิปล็อค ถุงใส่ขนม มีลาย ตั้งได้ (TASTE สีขาว)",
              "6",
              "j&t",
              "860",
              "New",
              "Line",
              "กมลวรรณ (นราลัย",
              "815935102",
              "820139393953",
              "",
              "t",
            ],
            [
              "01/02/2020",
              "วินิจ",
              "ดรุกาญจน์พฤฒิ",
              "15.5 x 23.5cm ถุงซิปล็อค ถุงใส่ขนม มีลาย ตั้งได้ (TASTE สีขาว)",
              "6",
              "j&t",
              "", // blank amount
              "New",
              "Line",
              "กมลวรรณ (นราลัย",
              "815935102",
              "820139393953",
              "",
              "t",
            ],
            [
              "",
              "เฟื่องกมล",
              "ตาทอง",
              "16 x 24cm ถุงโพลี ใสล้วน มีซิปล็อก ตั้งได้",
              "1",
              "flash",
              "147",
              "New",
              "Facebook",
              "Fueangkamol Juljul",
              "833449902",
              "th20055Gnj96a",
              "th20055Gnj96a",
              "s",
            ],
            [
              "01/02/2020 -- WRONG", // wrong date
              "สุนิรัตน์",
              "แก้วเกิด",
              "16 x 24cm ถุงฟอยด์ ถุงซิปล็อค ด้านหน้าใส่ ด้านหลังทึบ ตั้งได้",
              "4",
              "j&t",
              "690",
              "Old",
              "Facebook",
              "Sunirat Kaewkerd Sangkaew",
              "956697234",
              "820139372942",
              "",
              "s",
            ],
        ];
    }
}
