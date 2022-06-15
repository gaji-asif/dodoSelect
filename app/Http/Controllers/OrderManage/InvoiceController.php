<?php

namespace App\Http\Controllers\OrderManage;

use App\Http\Controllers\Controller;
use App\Models\OrderManagement;
use App\Models\TaxRateSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PDF;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Generate pdf of invoice
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
                            ->where('seller_id', $sellerId)
                            ->where('id', $orderId)
                            ->with('shop')
                            ->first();

        abort_if(!$orderManagement, Response::HTTP_NOT_FOUND, __('translation.global.data_not_found'));

        $data = [
            'orderManagement' => $orderManagement,
            'taxRateSetting' => TaxRateSetting::where('seller_id', $sellerId)->first()
        ];

        $taxInvoicePdf = PDF::loadView('pdf.order-invoice', $data);
        $taxInvoicePdf->setPaper('A4', 'portrait');

        $pdfFileName = 'order_invoice_' . $orderManagement->id . '.pdf';

        return $taxInvoicePdf->download($pdfFileName);
    }
}
