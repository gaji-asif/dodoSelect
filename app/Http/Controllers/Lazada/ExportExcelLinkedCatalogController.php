<?php

namespace App\Http\Controllers\Lazada;

use App\Exports\LazadaLinkedCatalogExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        $fileName = "Lazada Catalog Linked - {$dateTime}.xlsx";

        return Excel::download(new LazadaLinkedCatalogExport($sellerId), $fileName);
    }
}
