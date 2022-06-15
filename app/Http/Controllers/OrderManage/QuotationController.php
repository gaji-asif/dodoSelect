<?php

namespace App\Http\Controllers\OrderManage;

use App\Http\Controllers\Controller;
use App\Models\OrderManagement;
use App\Models\TaxRateSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use PDF;

class QuotationController extends Controller
{
    /**
     * Print the pdf of quotation
     *
     * @param  int  $orderId
     * @return mixed
     */
    public function printPdf($orderId)
    {
        $sellerId = Auth::user()->id;

        $orderManagement = OrderManagement::query()
                            ->with(['order_management_details' => function($detail) {
                                $detail->with('product');
                            }])
                            ->with('shop')
                            ->where('seller_id', $sellerId)
                            ->where('id', $orderId)
                            ->with('shop')
                            ->first();

        abort_if(!$orderManagement, Response::HTTP_NOT_FOUND, 'Data not found');

        $data = [
            'orderManagement' => $orderManagement,
            'taxRateSetting' => TaxRateSetting::where('seller_id', $sellerId)->first(),
            'taxEnableYes' => OrderManagement::TAX_ENABLE_YES
        ];

        $taxInvoicePdf = PDF::loadView('pdf.order-quotation', $data);
        $taxInvoicePdf->setPaper('A4', 'portrait');

        $pdfFileName = 'order_quotation_' . $orderManagement->id . '.pdf';

        return $taxInvoicePdf->download($pdfFileName);
    }
}
