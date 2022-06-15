<?php

namespace App\Http\Controllers;

use App\Enums\ShopeeTransactionTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\DatatableRequest;
use App\Http\Requests\Shopee\SyncTransactionRequest;
use App\Http\Requests\Shopee\TransactionSummaryRequest;
use App\Jobs\ShopeeTransactionSync;
use App\Models\Shopee;
use App\Models\ShopeeSetting;
use App\Models\ShopeeTransaction;
use App\Utilities\QueueJobStatusUtil;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class ShopeeTransactionController extends Controller
{
    /**
     * Shows shopee transaction datatable page
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

        return view('report.shopee-transaction.index', compact('shops'));
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
            'create_time', 'amount'
        ];

        $orderColumnIndex = isset($request->get('order')[0]['column'])
            ? $request->get('order')[0]['column']
            : 0;

        $orderColumnDir = isset($request->get('order')[0]['dir'])
            ? $request->get('order')[0]['dir']
            : 'desc';

        $orderColumnName = $orderColumnList[$orderColumnIndex] ?? 'create_time';

        $shopId = $request->get('shop_id', null);
        $dateFrom = $request->get('date_from', null);
        $dateTo = $request->get('date_to', null);

        $transactions = ShopeeTransaction::query()
            ->where('seller_id', $sellerId)
            ->where('transaction_type', '<>', ShopeeTransactionTypeEnum::withdrawal_created()->value)
            ->searchTable($search)
            ->byShop($shopId)
            ->byCreateTimeDateRange($dateFrom, $dateTo)
            ->with('shopee')
            ->orderBy($orderColumnName, $orderColumnDir);

        return DataTables::of($transactions)
                ->addColumn('detail', function ($transaction) {
                    return '
                        <div class="grid grid-cols-1 gap-4 gap-x-8 py-4 sm:grid-cols-3">
                            <div>
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Shop Name') .'
                                </span>
                                <span class="font-bold">
                                    '. $transaction->shopee->shop_name .'
                                </span>
                            </div>
                            <div>
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Buyer Name') .'
                                </span>
                                <span class="font-bold">
                                    '. $transaction->buyer_name .'
                                </span>
                            </div>
                            <div>
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Order ID') .'
                                </span>
                                <span class="font-bold">
                                    '. $transaction->ordersn .'
                                </span>
                            </div>
                            <div>
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Wallet Time') .'
                                </span>
                                <span class="font-bold">
                                    '. date('d/m/Y h:i a', $transaction->create_time) .'
                                </span>
                            </div>
                            <div>
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Status') .'
                                </span>
                                <span class="font-bold">
                                    '. $transaction->status .'
                                </span>
                            </div>
                            <div>
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Wallet Type') .'
                                </span>
                                <span class="font-bold">
                                    '. $transaction->wallet_type .'
                                </span>
                            </div>
                            <div>
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Transaction ID') .'
                                </span>
                                <span class="font-bold">
                                    '. $transaction->transaction_id .'
                                </span>
                            </div>
                            <div>
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Transaction Type') .'
                                </span>
                                <span class="font-bold">
                                    '. $transaction->transaction_type .'
                                </span>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('col_amount', function ($transaction) {
                    return '
                        <div class="grid grid-cols-1 gap-4 gap-x-8 -mt-14">
                            <div>
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Amount') .'
                                </span>
                                <span class="font-bold">
                                    '. currency_symbol('THB') . currency_number($transaction->amount, 2) .'
                                </span>
                            </div>
                            <div>
                                <span class="block whitespace-nowrap text-gray-500">
                                    '. __('translation.Transaction Fee') .'
                                </span>
                                <span class="font-bold">
                                    '. currency_symbol('THB') . currency_number($transaction->transaction_fee, 2) .'
                                </span>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('actions', function ($transaction) {
                    return '
                        <button class="btn-action--green"
                            data-id="'. $transaction->id .'"
                            onClick="showTransaction(this)">
                            <i class="bi bi-info-circle"></i>
                        </button>
                    ';
                })
                ->rawColumns(['detail', 'col_amount', 'actions'])
                ->make(true);
    }

    /**
     * Sync shopee transaction data
     *
     * @param  \App\Http\Requests\Shopee\SyncTransactionRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(SyncTransactionRequest $request)
    {
        $requestData = $request->validated();

        $splitDateRange = explode(' to ', $requestData['date_range']);
        $fromDate = $splitDateRange[0];
        $toDate = $splitDateRange[1];

        $datePeriods = date_period_interval($fromDate, $toDate, 15);

        $shopeeSetting = ShopeeSetting::first();

        $shopId = $requestData['shop_id'];
        $seller = Auth::user();

        $delaySeconds = 0;
        foreach ($datePeriods as $period) {
            $requestParams = [
                'page' => 0,
                'create_time_from' => strtotime($period['date_from'] . ' 00:00:00'),
                'create_time_to' => strtotime($period['date_to'] . ' 23:59:59')
            ];

            ShopeeTransactionSync::dispatch($shopeeSetting, $shopId, $seller, $requestParams)
                ->delay(now()->addSeconds($delaySeconds));

            $delaySeconds += 5;
        }

        return $this->apiResponse(Response::HTTP_OK, __('translation.We are syncing the data'));
    }

    /**
     * Get detail of shopee transaction
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $sellerId = Auth::user()->id;

        $transaction = ShopeeTransaction::query()
            ->where('seller_id', $sellerId)
            ->where('id', $id)
            ->with('shopee')
            ->firstOrFail();

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'transaction' => $transaction
        ]);
    }

    /**
     * Get total summary of shopee transaction
     *
     * @param  \App\Http\Requests\Shopee\TransactionSummaryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function summary(TransactionSummaryRequest $request)
    {
        $requestData = $request->validated();

        $sellerId = Auth::user()->id;

        $shopId = $requestData['shop_id'];
        $dateFrom = $requestData['date_from'];
        $dateTo = $requestData['date_to'];

        $transactionSummary = ShopeeTransaction::query()
            ->selectRaw('SUM(amount) AS amount_total, SUM(transaction_fee) AS transaction_fee_total')
            ->where('seller_id', $sellerId)
            ->where('transaction_type', '<>', ShopeeTransactionTypeEnum::withdrawal_created()->value)
            ->byShop($shopId)
            ->byCreateTimeDateRange($dateFrom, $dateTo)
            ->first();

        $shop = Shopee::query()
            ->where('seller_id', $sellerId)
            ->where('shop_id', $shopId)
            ->first();

        $transactionBalance = ShopeeTransaction::query()
            ->where('seller_id', $sellerId)
            ->where('transaction_type', '<>', ShopeeTransactionTypeEnum::withdrawal_created()->value)
            ->byShop($shopId)
            ->byCreateTimeDateRange($dateFrom, $dateTo)
            ->orderBy('create_time', 'desc')
            ->first();

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'transaction_summary' => [
                'amount_total' => $transactionSummary->amount_total ?? 0,
                'transaction_fee_total' => $transactionSummary->transaction_fee_total ?? 0,
                'wallet_balance' => [
                    'amount' => $transactionBalance->current_balance ?? 0,
                    'datetime' => date('Y-m-d H:i:s', strtotime($dateTo))
                ]
            ],
            'shop' => $shop
        ]);
    }

    /**
     * Get sync status of ShopeeTransactionJob queue
     *
     * @return  \Illuminate\Http\Response
     */
    public function syncStatus()
    {
        $jobStatus = new QueueJobStatusUtil(ShopeeTransactionSync::class);

        return $this->apiResponse(Response::HTTP_OK, __('translation.success'), [
            'sync' => $jobStatus->getStatus()
        ]);
    }
}
