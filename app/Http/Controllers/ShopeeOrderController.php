<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatatableRequest;
use App\Http\Requests\Shopee\ShopeeOrderSummaryRequest;
use App\Models\Shopee;
use App\Models\ShopeeOrderPurchase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class ShopeeOrderController extends Controller
{
    /**
     * Shows shopee orders of completed order
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $sellerId = Auth::user()->id;

        $shops = Shopee::query()
            ->where('seller_id', $sellerId)
            ->orderBy('shop_name', 'asc')
            ->get();

        $orderStatus = ShopeeOrderPurchase::getAllOrderStatus();

        return view('report.shopee-order.index', compact('shops', 'orderStatus'));
    }

    /**
     * Handle server side datatable of shopee transaction
     *
     * @param  \App\Http\Requests\DatatableRequest  $request
     * @return mixed
     */
    public function datatable(DatatableRequest $request)
    {
        $sellerId = Auth::user()->id;

        $search = isset($request->get('search')['value'])
            ? $request->get('search')['value']
            : null;

        $orderColumnList = [
            'order_id', 'total'
        ];

        $orderColumnIndex = isset($request->get('order')[0]['column'])
            ? $request->get('order')[0]['column']
            : 0;

        $orderColumnDir = isset($request->get('order')[0]['dir'])
            ? $request->get('order')[0]['dir']
            : 'desc';

        $orderColumnName = $orderColumnList[$orderColumnIndex] ?? 'order_id';

        $shopId = $request->get('shop_id', null);
        $dateFrom = $request->get('date_from', null);
        $dateTo = $request->get('date_to', null);
        $status = $request->get('status', null);

        $orders = ShopeeOrderPurchase::query()
            ->where('seller_id', $sellerId)
            ->byShop($shopId)
            ->byOrderDateRange($dateFrom, $dateTo)
            ->byStatus($status)
            ->where(function ($order) use ($search) {
                $order->where('order_id', 'like', '%' . $search . '%')
                    ->orWhere('billing', 'like', '%' . $search . '%');
            })
            ->with('shopee')
            ->with('shopee_income')
            ->orderBy($orderColumnName, $orderColumnDir);

        return DataTables::of($orders)
                ->addColumn('detail', function ($order) {
                    $buyerName = '';
                    if (isset($order->billing) && !empty($order->billing)) {
                        $billingData = json_decode($order->billing);
                        $buyerName = $billingData->name ?? '';
                    }

                    return '
                        <div class="grid grid-cols-1 gap-4 gap-x-8 py-4 sm:grid-cols-3">
                            <div class="col-span-1">
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Shop Name') .'
                                </span>
                                <span class="font-bold">
                                    '. $order->shopee->shop_name .'
                                </span>
                            </div>
                            <div class="col-span-1">
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Buyer Name') .'
                                </span>
                                <span class="font-bold">
                                    '. $buyerName .'
                                </span>
                            </div>
                            <div class="col-span-1">
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Order ID') .'
                                </span>
                                <span class="font-bold">
                                    '. $order->order_id .'
                                </span>
                            </div>
                            <div>
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Order Date') .'
                                </span>
                                <span class="font-bold">
                                    '. date('d/m/Y h:i a', strtotime($order->order_date)) .'
                                </span>
                            </div>
                            <div>
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Payment Method') .'
                                </span>
                                <span class="font-bold">
                                    '. $order->payment_method_title .'
                                </span>
                            </div>
                            <div class="col-span-1">
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Status') .'
                                </span>
                                <span class="font-bold">
                                    '. $order->str_order_status .'
                                </span>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('col_amount', function ($order) {
                    return '
                        <div class="grid grid-cols-1 gap-4 gap-x-8 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Total Amount') .'
                                </span>
                                <span class="font-bold">
                                    '. currency_symbol('THB') . currency_number($order->total, 2) .'
                                </span>
                            </div>
                            <div>
                                <span class="block whitespace-nowrap text-red-500">
                                    '. __('translation.Buyer Paid') .'
                                </span>
                                <span class="font-bold">
                                    '. currency_symbol('THB') . currency_number($order->shopee_income->buyer_total_amount, 2) .'
                                </span>
                            </div>
                            <div>
                                <span class="block whitespace-nowrap text-red-500">
                                    '. __('translation.Payout Amount') .'
                                </span>
                                <span class="font-bold">
                                    '. currency_symbol('THB') . currency_number($order->shopee_income->escrow_amount, 2) .'
                                </span>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('actions', function ($order) {
                    return '
                        <button class="btn-action--green"
                            data-id="'. $order->id .'"
                            onClick="showOrder(this)">
                            <i class="bi bi-info-circle"></i>
                        </button>
                    ';
                })
                ->rawColumns(['detail', 'col_amount', 'actions'])
                ->make(true);
    }

    /**
     * Shows shopee orders detail
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show(int $id)
    {
        $sellerId = Auth::user()->id;

        $order = ShopeeOrderPurchase::query()
            ->where('seller_id', $sellerId)
            ->where('id', $id)
            ->with('shopee')
            ->with('shopee_income')
            ->firstOrFail();

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'order' => $order
        ]);
    }

    /**
     * Get total summary of shopee orders
     *
     * @param  \App\Http\Requests\Shopee\ShopeeOrderSummaryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function summary(ShopeeOrderSummaryRequest $request)
    {
        $requestData = $request->validated();

        $sellerId = Auth::user()->id;

        $shopId = $requestData['shop_id'];
        $dateFrom = $requestData['date_from'];
        $dateTo = $requestData['date_to'];
        $status = $requestData['status'];

        $orderSummary = ShopeeOrderPurchase::query()
            ->selectRaw('SUM(total) AS amount_total')
            ->where('seller_id', $sellerId)
            ->byShop($shopId)
            ->byOrderDateRange($dateFrom, $dateTo)
            ->byStatus($status)
            ->first();

        $shop = Shopee::query()
            ->where('seller_id', $sellerId)
            ->where('shop_id', $shopId)
            ->first();

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'order_summary' => [
                'amount_total' => $orderSummary->amount_total ?? 0
            ],
            'shop' => $shop
        ]);
    }
}
