<?php

namespace App\Actions\GoogleSheet;

use App\Actions\ValidateDateFormat;
use App\Enums\SheetNameSyncStatusEnum;
use App\Models\SheetDataTpk;
use App\Models\SheetName;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreTpkPackingData
{
    use AsAction;

    public function handle(array $sheetData, SheetName $sheetName)
    {
        try {
            DB::beginTransaction();

            $sheetNameTable = (new SheetName())->getTable();
            DB::table($sheetNameTable)
                ->where('id', $sheetName->id)
                ->update([
                    'sync_status' => SheetNameSyncStatusEnum::synced()->value,
                    'last_sync' => new DateTime(),
                    'updated_at' => new DateTime()
                ]);

            $sheetDataTpkTable = (new SheetDataTpk())->getTable();
            DB::table($sheetDataTpkTable)->where('sheet_name_id', $sheetName->id)->delete();

            LazyCollection::make($sheetData)
                ->chunk(5000)
                ->each(function ($rows) use ($sheetName, $sheetDataTpkTable) {
                    $dateColumn = SheetDataTpk::getColumnIndexByHeaderName('Date');
                    $amountColumn = SheetDataTpk::getColumnIndexByHeaderName('Amount');
                    $typeColumn = SheetDataTpk::getColumnIndexByHeaderName('Type');
                    $channelColumn = SheetDataTpk::getColumnIndexByHeaderName('Channel');
                    $orderByColumn = SheetDataTpk::getColumnIndexByHeaderName('Order By');
                    $shopColumn = SheetDataTpk::getColumnIndexByHeaderName('Shop');
                    $chargedShippingColumn = SheetDataTpk::getColumnIndexByHeaderName('Charged Shipping Cost');
                    $actualShippingColumn = SheetDataTpk::getColumnIndexByHeaderName('Actual Shipping Cost');

                    $tpkData = [];
                    foreach ($rows as $row) {
                        $date = isset($row[$dateColumn]) ? $row[$dateColumn] : null;
                        $amount = isset($row[$amountColumn]) ? $row[$amountColumn] : '';
                        $type = isset($row[$typeColumn]) ? $row[$typeColumn] : null;
                        $channel = isset($row[$channelColumn]) ? $row[$channelColumn] : null;
                        $orderBy = isset($row[$orderByColumn]) ? $row[$orderByColumn] : null;
                        $shop = isset($row[$shopColumn]) ? $row[$shopColumn] : null;
                        $chargedShipping = isset($row[$chargedShippingColumn]) ? $row[$chargedShippingColumn] : 0;
                        $actualShipping = isset($row[$actualShippingColumn]) ? $row[$actualShippingColumn] : 0;

                        $expectedDateFormat = 'd/m/Y';
                        $isDateValid = ValidateDateFormat::make()->handle($date, $expectedDateFormat);

                        if ($isDateValid && is_numeric($amount)) {
                            array_push($tpkData, [
                                'sheet_name_id' => $sheetName->id,
                                'seller_id' => $sheetName->seller_id,
                                'created_at' => new DateTime(),
                                'date' => TransformDate::make()->handle($date),
                                'amount' => $amount,
                                'type' => $type,
                                'channel' => $channel,
                                'order_by' => $orderBy,
                                'shop' => $shop,
                                'charged_shipping_cost' => $chargedShipping,
                                'actual_shipping_cost' => $actualShipping
                            ]);
                        } else {
                            continue;
                        }
                    }

                    if (!empty($tpkData)) {
                        DB::table($sheetDataTpkTable)->insert($tpkData);
                    }
                });

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);
        }
    }
}
