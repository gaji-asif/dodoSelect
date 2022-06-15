<?php

namespace App\Http\Controllers\WCProduct;

use App\Exports\WooProductLinkedCatalogExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ExportExcelLinkedCatalogController extends Controller
{
    public function __invoke()
    {
        $sellerId = Auth::user()->id;

        $dateTime = date('YmdHis');
        $fileName = "Woocommerce Catalog Linked_{$dateTime}.xlsx";

        return Excel::download(new WooProductLinkedCatalogExport($sellerId), $fileName);
    }
}
