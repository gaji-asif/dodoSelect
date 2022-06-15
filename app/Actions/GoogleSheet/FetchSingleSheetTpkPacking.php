<?php

namespace App\Actions\GoogleSheet;

use App\Enums\SheetNameSyncStatusEnum;
use App\Jobs\SheetTpkPackingSync;
use App\Models\SheetName;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchSingleSheetTpkPacking
{
    use AsAction;

    public function handle(SheetName $sheetName, int $jobDelay = 0)
    {
        $sheetName->sync_status = SheetNameSyncStatusEnum::syncing();
        $sheetName->save();

        SheetTpkPackingSync::dispatch($sheetName)
            ->delay(now()->addSeconds($jobDelay));
    }
}
