<?php

namespace App\Http\Controllers\Shopee;

use App\Exports\ShopeeLinkedCatalogExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ExportExcelLinkedCatalogController extends Controller
{
    /**
     * Download excel file of linked catalog
     *
     * @return mixed
     */
    public function __invoke()
    {
        $sellerId = Auth::user()->id;

        $dateTime = date('YmdHis');
        $fileName = "Shopee Catalog Linked - {$dateTime}.xlsx";

        return Excel::download(new ShopeeLinkedCatalogExport($sellerId), $fileName);
    }
}
