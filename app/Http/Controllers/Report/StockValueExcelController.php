<?php

namespace App\Http\Controllers\Report;

use App\Exports\StockValueExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class StockValueExcelController extends Controller
{
    /**
     * Export to excel stock value data
     *
     * @return mixed
     */
    public function __invoke()
    {
        $sellerId = Auth::user()->id;

        $dateTime = date('YmdHis');
        $fileName = "stock_value_{$dateTime}.xlsx";

        return Excel::download(new StockValueExport($sellerId), $fileName);
    }
}
