<?php

namespace App\Http\Controllers;

use App\Models\Shopee;
use App\Traits\ShopeeOrderPurchaseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\OrderManagement\DatatableRequest;
use App\Models\ShopeeOrderParamInit;
use App\Models\ShopeeOrderPurchase;
use Carbon\Carbon;
use DB;

class ShopeeOrderPurchaseHistoryController extends Controller
{
    use ShopeeOrderPurchaseTrait;

    public function __construct()
    {
        $this->middleware('auth');
    }

    
    public function index() 
    {
        $shops = Shopee::query()
            ->where('seller_id', $this->getShopeeSellerId())
            ->orderBy('shop_name', 'asc')
            ->get();
            
        $data = [
            'shops' => $shops
        ];

        return view('shopee.order.order_history_analysis', $data);
    }


    public function data(DatatableRequest $request)
    {
        try {
            $shopeeTable = (new Shopee())->getTable();
            $shopeePurchaseOrderTable = (new ShopeeOrderPurchase())->getTable();
            $orderStatuses = $request->get('status', 0);
            $interval = $request->get('interval', 0);
            $shopeeId = $request->get('shopee_id', 0);
            $search = isset($request->get('search')['value']) ? $request->get('search')['value']:null;
    
            $orderColumnIndex = isset($request->get('order')[0]['column']) ? $request->get('order')[0]['column']:1;
    
            $orderDir = isset($request->get('order')[0]['dir']) ? $request->get('order')[0]['dir']:'desc';
    
            $availableColumnsOrder = [
                'id', 'order_date'
            ];
    
            $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
                ? $availableColumnsOrder[$orderColumnIndex]
                : $availableColumnsOrder[1];

            $ShopeeOrderPurchases = DB::table($shopeePurchaseOrderTable)
                ->join("{$shopeeTable}", "{$shopeeTable}.id", '=', "{$shopeePurchaseOrderTable}.website_id")
                ->where("{$shopeePurchaseOrderTable}.seller_id", "=", $this->getShopeeSellerId());

            if (isset($interval)) {
                if ($interval == "per_year") {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->select(DB::raw("count({$shopeePurchaseOrderTable}.id) AS total_count, sum({$shopeePurchaseOrderTable}.total) AS total_amount, DATE_FORMAT({$shopeePurchaseOrderTable}.order_date, '%Y') AS formated_order_date, {$shopeePurchaseOrderTable}.website_id, {$shopeeTable}.shop_name"));
                } else if ($interval == "per_month") {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->select(DB::raw("count({$shopeePurchaseOrderTable}.id) AS total_count, sum({$shopeePurchaseOrderTable}.total) AS total_amount, DATE_FORMAT({$shopeePurchaseOrderTable}.order_date, '%Y-%m') AS formated_order_date, {$shopeePurchaseOrderTable}.website_id, {$shopeeTable}.shop_name"));
                } else if ($interval == "per_week") {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->select(DB::raw("count({$shopeePurchaseOrderTable}.id) AS total_count, sum({$shopeePurchaseOrderTable}.total) AS total_amount, STR_TO_DATE(CONCAT(YEARWEEK({$shopeePurchaseOrderTable}.order_date,3), '1'), '%x%v%w') AS formated_order_date, {$shopeePurchaseOrderTable}.website_id, {$shopeeTable}.shop_name"));
                } else if ($interval == "per_day") {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->select(DB::raw("count({$shopeePurchaseOrderTable}.id) AS total_count, sum({$shopeePurchaseOrderTable}.total) AS total_amount, DATE_FORMAT({$shopeePurchaseOrderTable}.order_date, '%Y-%m-%d') AS formated_order_date, {$shopeePurchaseOrderTable}.website_id, {$shopeeTable}.shop_name"));
                }
            } else {
                $ShopeeOrderPurchases = $ShopeeOrderPurchases->select(DB::raw("count({$shopeePurchaseOrderTable}.id) AS total_count, sum({$shopeePurchaseOrderTable}.total) AS total_amount, DATE_FORMAT({$shopeePurchaseOrderTable}.order_date, '%Y-%m-%d') AS formated_order_date, {$shopeePurchaseOrderTable}.website_id, {$shopeeTable}.shop_name"));
            }

            if (isset($orderStatuses) and !empty($orderStatuses) and in_array($orderStatuses, [
                "ALL", 
                "NOW", 
                ShopeeOrderPurchase::ORDER_STATUS_COMPLETED, 
                ShopeeOrderPurchase::ORDER_STATUS_CANCELLED,
                "NOT_".ShopeeOrderPurchase::ORDER_STATUS_CANCELLED
            ])) {
                /* Get data by "custom_status". */
                if ($orderStatuses == "NOW") {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->whereIn("{$shopeePurchaseOrderTable}.status_custom", [
                        strtolower(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING), 
                        strtolower(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB),
                        // strtolower(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP), 
                    ]);
                } else if ($orderStatuses == ShopeeOrderPurchase::ORDER_STATUS_COMPLETED || 
                    $orderStatuses == ShopeeOrderPurchase::ORDER_STATUS_CANCELLED) {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->where("{$shopeePurchaseOrderTable}.status_custom", strtolower($orderStatuses));
                } else if ($orderStatuses == "NOT_".ShopeeOrderPurchase::ORDER_STATUS_CANCELLED) {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->where("{$shopeePurchaseOrderTable}.status_custom", "!=", strtolower(ShopeeOrderPurchase::ORDER_STATUS_CANCELLED));
                } 
            }

            if ($shopeeId == 0) {
                $ShopeeOrderPurchases = $ShopeeOrderPurchases->groupBy('formated_order_date')    
                    ->groupBy("{$shopeePurchaseOrderTable}.website_id")
                    ->orderByRaw("DATE_FORMAT({$shopeePurchaseOrderTable}.order_date, '%Y-%m-%d') DESC")
                    ->orderBy("{$shopeeTable}.shop_name", "asc");
            } else {
                $ShopeeOrderPurchases = $ShopeeOrderPurchases->where("{$shopeePurchaseOrderTable}.website_id", "=", $shopeeId)
                ->groupBy('formated_order_date')
                ->orderByRaw("DATE_FORMAT({$shopeePurchaseOrderTable}.order_date, '%Y-%m-%d') DESC");
            }

            $ShopeeOrderPurchases = $ShopeeOrderPurchases->get();
                
            return Datatables::of($ShopeeOrderPurchases)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '';
                })
                ->addColumn('shop_name', function ($row) {
                    if (isset($row->shop_name) && !empty($row->shop_name)) {
                        return $row->shop_name;
                    }
                    return "";
                })
                ->addColumn('order_data', function ($row) use ($interval, $shopeeId) {
                    if ($interval == "per_week") {
                        return Carbon::parse($row->formated_order_date)->format("d M")." - ".Carbon::parse($row->formated_order_date)->addDays(6)->format("d M, Y"); 
                    } else if ($interval == "per_month") {
                        return Carbon::parse($row->formated_order_date)->format("M, Y"); 
                    } else if ($interval == "per_year") {
                        return Carbon::parse($row->formated_order_date)->format("Y"); 
                    }
                    return Carbon::parse($row->formated_order_date)->format("d M, Y");
                })
                ->addColumn('total_orders_count', function ($row) {
                    return $row->total_count;
                })
                ->addColumn('total_amount', function ($row) {
                    return $row->total_amount;
                })
                ->addColumn('action', function ($row) {
                    
                })
                ->rawColumns(['checkbox', 'order_data', 'total_amount'])
                ->make(true);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [];
    }
}
