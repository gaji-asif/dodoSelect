<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatatableRequest;
use App\Models\OrderManagement;
use App\Models\TaxRateSetting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade as PDF;
use Yajra\DataTables\DataTables;

class TaxInvoiceController extends Controller
{
    /**
     * Show datatable of tax_invoices data
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('seller.tax-invoice.index');
    }

    /**
     * Handle datatable server side of tax invoice
     *
     * @param  \App\Http\Requests\DatatableRequest  $request
     * @return mixed
     */
    public function dataTable(DatatableRequest $request)
    {
        $sellerId = Auth::user()->id;

        $search = isset($request->get('search')['value'])
                ? $request->get('search')['value']
                : null;

        $orderColumnIndex = isset($request->get('order')[0]['column'])
                            ? $request->get('order')[0]['column']
                            : 1;

        $orderDir = isset($request->get('order')[0]['dir'])
                    ? $request->get('order')[0]['dir']
                    : 'desc';

        $availableColumnsOrder = [
            'id', 'id', 'company_name', 'created_at', 'in_total'
        ];

        $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
                            ? $availableColumnsOrder[$orderColumnIndex]
                            : $availableColumnsOrder[0];

        $taxInvoices = OrderManagement::query()
            ->selectRaw('id, order_status, company_name, company_address, created_at, sub_total, shipping_cost, tax_rate, in_total')
            ->where('seller_id', $sellerId)
            ->where('tax_enable', OrderManagement::TAX_ENABLE_YES)
            ->searchTaxInvoiceDataTable($search)
            ->orderBy($orderColumnName, $orderDir);

        return DataTables::of($taxInvoices)
            ->addIndexColumn()
            ->addColumn('order_tax_id', function($data) {
                return '
                    <span class="font-bold text-blue-500">#'. $data->id .'</span><br>
                    <span class="badge-status--yellow">
                        '. $data->str_order_status .'
                    </span>
                ';
            })
            ->addColumn('company_info', function($data) {
                return '
                    <div class="mb-2">
                        <span>Company Name: </span><span class="font-bold">'. $data->company_name .'</span>
                    </div>
                    <div>
                        <span>Address: </span><span class="font-bold">'. Str::limit($data->company_address, 40) .'</span>
                    </div>
                ';
            })
            ->addColumn('order_date', function($data) {
                return strftime('%d %b %Y', strtotime($data->created_at));
            })
            ->addColumn('str_in_total', function($data) {
                return currency_symbol('THB') . $data->in_total;
            })
            ->addColumn('action', function ($data) {
                return '
                    <a href="'. route('tax-invoice.pdf-invoice', [ 'order_id' => $data->id ]) .'" class="btn-action--green" title="Print Invoice">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                ';
            })
            ->rawColumns(['order_tax_id', 'company_info', 'order_date', 'action'])
            ->make(true);
    }

    /**
     * Generate pdf of tax invoice
     *
     * @param  int  $orderId
     * @return mixed
     */
    public function generatePdfInvoice($orderId)
    {
        $sellerId = Auth::user()->id;

        $orderManagement = OrderManagement::query()
             ->with(['order_management_details' => function($detail) {
                 $detail->with('product');
             }])
            ->where('seller_id', $sellerId)
            ->where('id', $orderId)
            ->where('tax_enable', OrderManagement::TAX_ENABLE_YES)
            ->with('shop')
            ->first();

        abort_if(!$orderManagement, Response::HTTP_NOT_FOUND, 'Data not found');

        $data = [
            'orderManagement' => $orderManagement,
            'taxRateSetting' => TaxRateSetting::where('seller_id', $sellerId)->first(),
            'taxEnableYes' => OrderManagement::TAX_ENABLE_YES
        ];

        $taxInvoicePdf = PDF::loadView('pdf.tax-invoice', $data);
        $taxInvoicePdf->setPaper('A4', 'portrait');

        $pdfFileName = 'tax_invoice_' . $orderManagement->id . '.pdf';

        return $taxInvoicePdf->download($pdfFileName);
    }
}
