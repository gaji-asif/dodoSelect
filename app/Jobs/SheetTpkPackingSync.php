<?php

namespace App\Jobs;

use App\Actions\GoogleSheet\StoreTpkPackingData;
use App\Models\SheetDataTpk;
use App\Models\SheetName;
use GoogleSheet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SheetTpkPackingSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var \App\Models\SheetName */
    protected $sheetName;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\SheetName  $sheetName
     * @return void
     */
    public function __construct(SheetName $sheetName)
    {
        $this->sheetName = $sheetName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $spreadSheetId = $this->sheetName->sheetDoc->spreadsheet_id;
            $sheetName = $this->sheetName->sheet_name;

            $lastColumn = last(array_keys(SheetDataTpk::getAllActualColumns()));

            $cellRange = 'A2:' . $lastColumn;

            $tpkData = GoogleSheet::useDocument($spreadSheetId)
                ->fetchData($sheetName, $cellRange);

            StoreTpkPackingData::make()->handle($tpkData, $this->sheetName);

        } catch (\Throwable $th) {
            report($th);
        }
    }
}
