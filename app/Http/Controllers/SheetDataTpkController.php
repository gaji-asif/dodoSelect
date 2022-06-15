<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatatableRequest;
use App\Models\SheetDataTpk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class SheetDataTpkController extends Controller
{
    /**
     * Show datatable page of the sheet data tpks data
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sheet-data-tpks.index');
    }

    /**
     * Handle server side datatable of sheet docs data
     *
     * @param  \App\Http\Requests\DatatableRequest  $request
     * @return DataTables
     */
    public function datatable(DatatableRequest $request)
    {
        $sellerId = Auth::user()->id;

        $search = isset($request->get('search')['value'])
            ? $request->get('search')['value']
            : null;

        $orderColumnList = [
            'date', 'sheet_name', 'date', 'charged_shipping_cost'
        ];

        $orderColumnIndex = isset($request->get('order')[0]['column'])
            ? $request->get('order')[0]['column']
            : 0;

        $orderColumnDir = isset($request->get('order')[0]['dir'])
            ? $request->get('order')[0]['dir']
            : 'desc';

        $orderColumnName = $orderColumnList[$orderColumnIndex] ?? 'date';

        $sheetDataTpkTable = (new SheetDataTpk())->getTable();

        $sheetDataTpks = SheetDataTpk::query()
            ->joinedDatatable()
            ->where("{$sheetDataTpkTable}.seller_id", $sellerId)
            ->searchTable($search)
            ->orderBy($orderColumnName, $orderColumnDir);

        return DataTables::of($sheetDataTpks)
                ->addColumn('str_date_amount', function ($sheetData) {
                    $strDate = '-';
                    if (! empty($sheetData->date)) {
                        $strDate = date('d M Y', strtotime($sheetData->date));
                    }

                    return '
                        <div class="grid grid-cols-1 gap-2 gap-x-4">
                            <div>
                                <div class="whitespace-nowrap">
                                    '. __('translation.Date') .'
                                </div>
                                <div>
                                    <span class="font-bold">
                                        '. $strDate .'
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div class="whitespace-nowrap">
                                    '. __('translation.Amount') .'
                                </div>
                                <div>
                                    <span class="font-bold">
                                        '. number_format($sheetData->amount, 2) .'
                                    </span>
                                </div>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('more', function ($sheetData) {
                    $shopName = '<i class="text-red-400">N/A</i>';
                    if ($sheetData->shopData) {
                        $shopName = $sheetData->shopData->name;
                    }

                    return '
                        <div class="grid grid-cols-1 gap-2 gap-x-4 md:grid-cols-2">
                            <div>
                                <div class="whitespace-nowrap">
                                    '. __('translation.Charged Shipping Cost') .'
                                </div>
                                <div>
                                    <span class="font-bold">
                                        '. number_format($sheetData->charged_shipping_cost, 2) .'
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div class="whitespace-nowrap">
                                    '. __('translation.Actual Shipping Cost') .'
                                </div>
                                <div>
                                    <span class="font-bold">
                                        '. number_format($sheetData->actual_shipping_cost, 2) .'
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div class="whitespace-nowrap">
                                    '. __('translation.Type') .'
                                </div>
                                <div>
                                    <span class="font-bold">
                                        '. $sheetData->type .'
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div class="whitespace-nowrap">
                                    '. __('translation.Channel') .'
                                </div>
                                <div>
                                    <span class="font-bold">
                                        '. $sheetData->channel .'
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div class="whitespace-nowrap">
                                    '. __('translation.Order By') .'
                                </div>
                                <div>
                                    <span class="font-bold">
                                        '. $sheetData->order_by .'
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div class="whitespace-nowrap">
                                    '. __('translation.Shop') .'
                                </div>
                                <div>
                                    <span class="font-bold">
                                        '. $shopName .'
                                    </span>
                                </div>
                            </div>
                        </div>
                    ';
                })
                ->addIndexColumn()
                ->rawColumns(['str_date_amount', 'more'])
                ->make(true);
    }
}
