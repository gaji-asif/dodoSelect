<?php

namespace App\Actions\GoogleSheet;

use App\Models\SheetName;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchTpkPackingData
{
    use AsAction;

    public function handle()
    {
        $sheetToFetch = SheetName::availableToSync()
            ->with('sheetDoc')
            ->get();

        $jobDelay = 0;
        foreach ($sheetToFetch as $sheetName) {
            FetchSingleSheetTpkPacking::make()->handle($sheetName, $jobDelay);
            $jobDelay += 2;
        }
    }
}
