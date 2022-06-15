<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Shopee;
use App\Models\ShopeeBranch;
use App\Models\ShopeeOrderPurchase;
use App\Models\ShopeeOrderParamInit;
use App\Models\ShopeeProduct;
use App\Jobs\ShopeeOrderDetailSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\OrderManagement\DatatableRequest;
use App\Jobs\ShopeeOrderAirwayBillPrint;
use App\Jobs\ShopeeOrderInitWithAddressId;
use App\Jobs\ShopeeOrderInitWithBranchId;
use App\Jobs\ShopeeOrderInitWithTrackingNumber;
use App\Models\InventoryProductsReservedQuantityLog;
use App\Models\InventoryProductsStockUpdate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use App\Traits\ShopeeOrderPurchaseTrait;
use App\Traits\ShopeeOrderSyncTrait;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Traits\Inventory\LazadaInventoryProductsStockUpdateTrait;
use App\Traits\Inventory\ShopeeInventoryProductsStockUpdateTrait;
use App\Traits\MarketplaceOrderTabNavigationTrait;

use function PHPUnit\Framework\isJson;
use function PHPUnit\Framework\isTrue;

class ShopeeOrderPurchaseController extends Controller
{
    use ShopeeOrderPurchaseTrait, ShopeeOrderSyncTrait, MarketplaceOrderTabNavigationTrait, ShopeeInventoryProductsStockUpdateTrait, LazadaInventoryProductsStockUpdateTrait;

    private $update_order_enabled = false;
    private $delete_order_enabled = false;
    private $arrange_order_shipment_enabled = true;
    private $cancel_order_enabled = true;
    private $orders_with_no_awburl__session_key = "shopee_orders_with_no_awb_url_count";
    private $orders_with_no_tracking_number__session_key = "shopee_orders_with_no_tracking_number_count";

    private $shopee_airway_bill_api_limit = 50;


    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $statusMainSchema = ShopeeOrderPurchase::getMainStatusSchemaForDatatable();
        $firstStatusOrderId = array_column($statusMainSchema[0]['sub_status'], 'id')[0];

        $statusSecondarySchema = ShopeeOrderPurchase::getSecondaryStatusSchemaForDatatable();

        $data = [
            'shops' => $this->getShopeeShopsWithProcessingOrdersCount(implode(",", array_column($statusMainSchema[0]['sub_status'], 'id'))),
            'countries' => [],
            'states' => [],
            'totalProcessingWooCommerce' => $this->getTotalProcessingOrdersForWooCommerce(),
            // 'totalProcessingShopee' => $this->getTotalProcessingOrdersForShopee(),
            'totalProcessingShopee' => array_column($statusMainSchema[0]['sub_status'], 'count')[0],
            'totalProcessingLazada' => $this->getTotalProcessingOrdersForLazada(),
            'orderCancelationReasons' => ShopeeOrderPurchase::getAllOrderCancelReasons(),
            'statusMainSchema' => $statusMainSchema,
            'statusSecondarySchema' => $statusSecondarySchema,
            'firstStatusOrderId' => $firstStatusOrderId,
            'shippingMethodForShopee' => ShopeeOrderPurchase::getShippingMethodForShopeeWithOrdersCount()
        ];

        return view('shopee.order', $data);
    }

    /**
     * @var array
     */
    private $shopee_shops_for_logged_seller = [];
    private function getShopeeShopsForLoggedInSeller()
    {
        return Shopee::where('seller_id', $this->getShopeeSellerId())
        ->select('id','shop_name','shop_id','code')
        ->orderBy('shop_name', 'asc')
        ->get();
    }


    /**
     * Get the total number orders falling unded "To Process". Basically orders having "processing", "retry_ship" and "in_cancel"
     * as a value for "status_custom" falls under "To Process".
     * $processingStatuses may contain one or more statuses seperated by comma.
     *
     * @param string $processingStatuses
     * @return array
     */
    private function getShopeeShopsWithProcessingOrdersCount($processingStatuses)
    {
        try {
            if (sizeof($this->shopee_shops_for_logged_seller) == 0) {
                $this->shopee_shops_for_logged_seller = $this->getShopeeShopsForLoggedInSeller();
            }
            $shops = $this->shopee_shops_for_logged_seller;
            foreach($shops as $shop) {
                // $shop["processing_orders_count"] = !empty($processingStatuses)?ShopeeOrderPurchase::getMultipleStatusSchemaCount($processingStatuses, $shop["id"]):0;
                $shop["processing_orders_count"] = ShopeeOrderPurchase::getVerifiedOrderSchemaCount(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING, $shop["id"]);
                $date = $this->getBulkSyncStartTimeCacheValue($shop->id, $this->getShopeeSellerId());
                $shop["orders_last_synced_at"] = !empty($date)?Carbon::parse($date)->format("d/m/Y H:i A"):Carbon::now()->addMinutes(30)->format("d/m/Y H:i A");
            }
            return $shops;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [];
    }


    /**
     * Handle server-side datatable of order managements.
     *
     * NOTE:
     * Custom status filtering combinations
     * Processing = "Ready To Ship" + no tracking number
     * Ready To Ship = "Ready To Ship" + tracking number + no awb_url
     * Ready To Ship (printed) = "Ready To Ship" + awb_url
     *
     * @param  \App\Http\Requests\OrderManagement\DatatableRequest $request
     * @return \Illuminate\Http\Response
     */
    public function data(DatatableRequest $request)
    {
        try {
            $this->removeMissingAwburlForShopeeOrdersCountFromSession();
            $this->removeMissingTrackingNumberForShopeeOrdersCountFromSession();

            $shopeePurchaseOrderTable = (new ShopeeOrderPurchase())->getTable();
            $shopsTable = (new Shopee())->getTable();
            $orderParamInitTable = (new ShopeeOrderParamInit())->getTable();
            $orderStatuses = $request->get('status', 0);
            $shopeeId = $request->get('shopee_id', 0);
            $shopeeShippingMethod = $request->get('shipping_method', "");
            $search = isset($request->get('search')['value']) ? $request->get('search')['value']:null;

            $orderColumnIndex = isset($request->get('order')[0]['column']) ? $request->get('order')[0]['column']:1;

            $orderDir = isset($request->get('order')[0]['dir']) ? $request->get('order')[0]['dir']:'desc';

            $availableColumnsOrder = [
                'id', 'order_date'
            ];

            $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
                ? $availableColumnsOrder[$orderColumnIndex]
                : $availableColumnsOrder[0];

            $ShopeeOrderPurchases = ShopeeOrderPurchase::selectRaw("{$shopeePurchaseOrderTable}.*, {$shopsTable}.shop_name")
                ->where("{$shopeePurchaseOrderTable}.seller_id", $this->getShopeeSellerId())
                ->withCount('orderProductDetails')
                ->joinedDatatable();

            /* Handle shipping method filter. */
            if (!empty($shopeeShippingMethod)) {
                if (array_key_exists($shopeeShippingMethod, ShopeeOrderPurchase::getShippingMethodForShopee())) {
                    if ($shopeeShippingMethod == "pickup") {
                        $ShopeeOrderPurchases = $ShopeeOrderPurchases->where("{$orderParamInitTable}.pickup", "like", "%address_id%");
                    } else if ($shopeeShippingMethod == "dropoff_branch_id") {
                        $ShopeeOrderPurchases = $ShopeeOrderPurchases->where("{$orderParamInitTable}.dropoff", "like", "%branch_id%");
                    } else if ($shopeeShippingMethod == "dropoff_tracking_no") {
                        $ShopeeOrderPurchases = $ShopeeOrderPurchases->where("{$orderParamInitTable}.dropoff", "like", "%tracking_no%");
                    }
                }
            }

            if (!empty($shopeeId) and $shopeeId > 0) {
                $ShopeeOrderPurchases = $ShopeeOrderPurchases->where("{$shopeePurchaseOrderTable}.website_id", $shopeeId);
            }

            if (in_array($orderStatuses, [
                ShopeeOrderPurchase::AIRWAY_BILL_STATUS_PRINTED,
                ShopeeOrderPurchase::AIRWAY_BILL_STATUS_NOT_PRINTED
            ])) {
                /**
                 * Get data by print status.
                 * If "status" is "PRINTED", then check if "awb_printed_at" is not null.
                 * If "status" is "NOT_PRINTED", then check if "awb_printed_at" is null.
                 * NOTE:
                 * For this we are only using the "state_custom", "READY_TO_SHIP_AWB".
                 */
                $ShopeeOrderPurchases = $ShopeeOrderPurchases->byPrintStatus($orderStatuses)
                    ->byMultipleOrderStatusCustom(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB);
            } else {
                /* Get data by "custom_status". */
                $now = Carbon::now()->subHours(1)->toDateTimeString();
                if ($orderStatuses == ShopeeOrderPurchase::ORDER_STATUS_UNVERIFIED) {
                    /**
                     * For "UNVERIFIED" custom_status we will use "PROCESSING" custom_status.
                     * Here for this specific status we will look for orders which have not passed the 1 hour mark yet.
                     */
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->byMultipleOrderStatusCustom(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING);
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->where('order_date', '>', $now);
                } else if ($orderStatuses == ShopeeOrderPurchase::ORDER_STATUS_PROCESSING) {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->byMultipleOrderStatusCustom($orderStatuses)->where('order_date', '<', $now);
                } else {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->byMultipleOrderStatusCustom($orderStatuses);
                }
            }

            $ShopeeOrderPurchases = $ShopeeOrderPurchases->searchDatatable($search)
                ->orderBy($orderColumnName, $orderDir);

            if (isset($request->length)) {
                $per_page = (int) $request->length;
                if ($per_page === 300) {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->limit(3000);
                } else if ($per_page === 100) {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->limit(1500);
                } else if ($per_page === 50) {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->limit(750);
                } else {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->limit(500);
                }
            } else {
                $ShopeeOrderPurchases = $ShopeeOrderPurchases->limit(1500);
            }

            return Datatables::of($ShopeeOrderPurchases)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return $row->website_id . '*' . $row->id . '*' . $row->order_id;
                })
                ->addColumn('order_data', function ($row) {
                    $shipment_method = 'None';
                    if (isset($row->shipping_lines) and !empty($row->shipping_lines)) {
                        $shipping_line = json_decode($row->shipping_lines);
                        if (isset($shipping_line)) {
                            if (isset($shipping_line->shipping_carrier) and !empty($shipping_line->shipping_carrier)) {
                                $shipment_method = $shipping_line->shipping_carrier;
                            } else if (isset($shipping_line->checkout_shipping_carrier) and !empty($shipping_line->checkout_shipping_carrier)) {
                                $shipment_method = $shipping_line->checkout_shipping_carrier;
                            }
                        }
                    }

                    /* Get customer name from "billing". */
                    $customer_name = '';
                    if (isset($row->billing) and !empty($row->billing)) {
                        $billing_data = json_decode($row->billing);
                        $customer_name = isset($billing_data->name)?$billing_data->name:"";
                    }

                    $shop_logo = asset('img/No_Image_Available.jpg');
                    if (!empty($row->shop_logo) && file_exists(public_path($row->shop_logo))) {
                        $shop_logo = asset($row->shop_logo);
                    }

                    /**
                     * To check if the order has passed one hour mark.
                     * In the first hour order can be canceled by a customer.
                     */
                    $order_can_be_cancelled = false;
                    if (isset($row->order_date)) {
                        if(Carbon::parse($row->order_date)->diffInHours(Carbon::now()) < 1) {
                            $order_can_be_cancelled = true;
                        }
                    }

                    $functionalBtns = '';

                    /* Check if the order is being process for init. */
                    $processing_order_for_init = false;
                    if (isset($row->status_custom) and $row->status_custom==strtolower(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING)) {
                        if (Cache::has($this->getKeyPrefixForShopeeTrackingInit().$row->order_id)) {
                            $processing_order_for_init = true;
                        }
                        if (!$order_can_be_cancelled) {
                            $functionalBtns .= '
                            <button type="button" class="'.($processing_order_for_init?"hide":"btn-action--green").' btn_arrange_shipment btn_arrange_shipment_'.$row->order_id.' modal-open"
                                title="Arrange Shipment"
                                data-id="'. $row->id .'"
                                data-order_id="'. $row->order_id .'"
                                data-tracking_no="'. $row->tracking_number .'"
                                onClick="arrangeOrderShipment(this)" style="cursor: pointer;">
                                <i class="fas fa-pencil-alt"></i>
                                <span class="ml-2 hidden lg:inline">'.__("shopee.order.datatable.btn.arrange_shipment").'</span>
                            </button>';
                        }
                    }

                    /**
                     * For "Shopee" update and delete is disabled. In "Shopee" an order can only be cancelled.
                     * No other status can be changed or an order can't be removed using the Api.
                     */
                    if ($this->update_order_enabled) {
                        $functionalBtns .= '
                        <button type="button" class="modal-open btn-action--green BtnUpdateStatus" title="Update Status" id="BtnUpdateStatus"
                            data-id="'. $row->id .'"
                            data-order_id="'. $row->order_id .'">
                            <i class="fas fa-pencil-alt"></i>
                            <span class="ml-2 hidden lg:inline">'.__("shopee.order.datatable.btn.update").'</span>
                        </button>';
                    }
                    if ($this->delete_order_enabled) {
                        $functionalBtns .= '
                        <button type="button" class="btn-action--red BtnDelete" title="Delete"
                            data-id="'. $row->id .'"
                            data-order_id="'. $row->order_id .'">
                            <i class="fas fa-trash"></i>
                            <span class="ml-2 hidden lg:inline">'.__("shopee.order.datatable.btn.delete").'</span>
                        </button>';
                    }

                    if ($row->status == ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP) {
                        /* Get the "shop_id" for the shopee shop. */
                        $shopee_shop_id = $this->getShopIdOfSpecificShop($row->website_id);
                        /* Get the "awb_url" from Shopee if missing in the system. */
                        if (isset($shopee_shop_id, $row->status_custom)) {
                            if ($row->status_custom == strtolower(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB)) {
                                if (!isset($row->awb_url) || empty($row->awb_url)) {
                                    ShopeeOrderAirwayBillPrint::dispatch((int) $shopee_shop_id, [$row->order_id]);
                                    $this->updateMissingAwburlForShopeeOrdersCountInSession();
                                }
                                if (!isset($row->tracking_number) || empty($row->tracking_number)) {
                                    ShopeeOrderDetailSync::dispatch((int) $shopee_shop_id, $this->getShopeeSellerId(), $row->website_id, [$row->order_id], "tracking_number");
                                    $this->updateMissingTrackingNumberForShopeeOrdersCountInSession();
                                }
                            }
                        }

                        $functionalBtns .= '
                        <button type="button" class="'.((isset($row->tracking_number,$row->awb_url) and !empty($row->tracking_number) and !empty($row->awb_url))?'btn-action--blue':'no_tracking_num no_tracking_num_'.$row->order_id.' hide').' btn_airway_bill" title="Download Airway Bill"
                            data-id="'. $row->id .'"
                            data-order_id="'. $row->order_id .'"
                            data-airway_bill_url=""
                            onClick="getAirwayBillForSpecificOrder(this)" style="cursor: pointer;">
                            <i class="fas fa-arrow-alt-circle-down"></i>
                            <span class="ml-2 hidden lg:inline">Print Label</span>
                        </button>';
                    }

                    /* Arrange shipment for "RETRY_SHIP". */
                    if ($row->status == ShopeeOrderPurchase::ORDER_STATUS_RETRY_SHIP) {
                        if ($this->cancel_order_enabled) {
                            $functionalBtns .= '
                            <button type="button" class="btn-action--red btn_cancel_order" title="Cancel Order"
                                data-id="'. $row->id .'"
                                data-order_id="'. $row->order_id .'"
                                onClick="cancelSpecificOrder(this)" style="cursor: pointer;">
                                <i class="fas fa-trash"></i>
                                <span class="ml-2 hidden lg:inline">'.__("shopee.order.datatable.btn.cancel").'</span>
                            </button>';
                        }
                        if (!$order_can_be_cancelled) {
                            $functionalBtns .= '
                            <button type="button" class="btn-action--green btn_arrange_shipment modal-open"
                                title="Arrange Shipment"
                                data-id="'. $row->id .'"
                                data-order_id="'. $row->order_id .'"
                                data-tracking_no="'. $row->tracking_number .'"
                                onClick="arrangeOrderShipment(this)" style="cursor: pointer;">
                                <i class="fas fa-pencil-alt"></i>
                                <span class="ml-2 hidden lg:inline">'.__("shopee.order.datatable.btn.arrange_shipment").'</span>
                            </button>';
                            if (isset($row->awb_printed_at, $row->awb_url) and !empty($row->awb_url)) {
                                $functionalBtns .= '<br/><a href="'.$row->awb_url.'" target="_blank" class="block pt-2">View failed awb</a>';
                            }
                        }
                    }

                    /**
                     * Custom buttons, "Mark As Shipped" and "Pick Confirm".
                     * NOTE:
                     * This 2 just update 2 dates for display.
                     */
                    if (isset($row->status_custom) and !empty($row->status_custom) and
                            in_array($row->status_custom, [
                                strtolower(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB),
                                strtolower(ShopeeOrderPurchase::ORDER_STATUS_SHIPPED_TO_WEARHOUSE)
                            ])
                        ) {
                        if (!isset($row->pickup_confirmed_at)) {
                            $functionalBtns .= '
                            <button type="button" class="modal-open '.((isset($row->tracking_number) and !empty($row->tracking_number))?'btn-action--yellow':'hide').' btn_update_pickup_confirm_status" title="Pick Confirm"
                                data-id="'. $row->id .'"
                                data-order_id="'. $row->order_id .'">
                                <i class="fas fa-pencil-alt"></i>
                                <span class="ml-2 hidden lg:inline">Pick Confirm</span>
                            </button>';
                        }
                        if (!isset($row->mark_as_shipped_at)) {
                            $functionalBtns .= '
                            <button type="button" class="modal-open '.((isset($row->tracking_number) and !empty($row->tracking_number))?'btn-action--green':'hide').' btn_update_wearhouse_shipped_status" title="Mark As Shipped"
                                data-id="'. $row->id .'"
                                data-order_id="'. $row->order_id .'">
                                <i class="fas fa-pencil-alt"></i>
                                <span class="ml-2 hidden lg:inline">Mark As Shipped</span>
                            </button>';
                        }
                    }

                    /* Geht the currency symbol. */
                    $currency_symbol = '';
                    if(isset($row->currency_symbol) and !empty($row->currency_symbol) and strlen($row->currency_symbol) === 3) {
                        $currency_symbol = currency_symbol($row->currency_symbol);
                    } else {
                        $currency_symbol = currency_symbol('THB');
                    }

                    return '
                        <div class="border border-solid border-gray-400 lg:border-gray-300 rounded-md hover:bg-blue-50">
                            <div class="border border-dashed border-t-0 border-r-0 border-l-0 border-gray-300">
                                <div class="grid grid-cols-3">
                                    <div class="col-span-3 sm:col-span-1">
                                        <div class="text-center px-2 py-1 sm:py-2">
                                            <span class="font-bold text-gray-400">#</span>
                                            <span class="relative -left-1 text-blue-500 font-bold">
                                                '. $row->order_id .'
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-span-2 sm:col-span-1">
                                        <div class="px-2 py-1 sm:py-2">
                                            <span class="text-xs sm:text-sm">
                                                '. (isset($row->status_custom)?$row->str_order_status_custom:$row->str_order_status) .'
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-span-3 sm:col-span-1 '.((isset($row->pickup_shipped_on) and !empty($row->pickup_shipped_on))?"":"hide").'">
                                        <div class="text-center px-2 py-1 sm:py-2">
                                            <span class="font-bold text-gray-400">Ship On : </span>
                                            <span class="relative ">
                                                '. $row->pickup_shipped_on .'
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-span-3 sm:col-span-1">
                                        <div class="text-center px-2 py-1 sm:py-2 '.($order_can_be_cancelled?"":"hide").'">
                                            <span class="font-bold badge-status--red pb-1 pt-1">Unverified Order</span>
                                        </div>
                                        <div class="text-center px-2 py-1 sm:py-2 '.($processing_order_for_init?"processing_init processing_init_".$row->order_id:"hide").'">
                                            <span class="font-bold badge-status--green pb-1 pt-1">Processing Now</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="grid grid-cols-1 lg:grid-cols-3">
                                    <div class="lg:col-span-2">
                                        <div class="grid grid-cols-5">
                                            <div class="col-span-5 sm:col-span-2 px-2 py-2">
                                                <div class="mb-1 md:mb-3">
                                                    <img src="'. $shop_logo .'" alt="'. $row->shop_name .'" title="'. $row->shop_name .'" class="w-16 h-auto"/>
                                                </div>
                                                <div class="">
                                                    <span class="badge-status--yellow">
                                                        '. $row->shop_name .'
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-span-5 sm:col-span-3">
                                                <div class="text-left px-2 py-2">
                                                    <div>
                                                        <span class="text-gray-600">'.__("shopee.order.datatable.customer_name").' : </span><span id="cn_'.$row->order_id.'">'. $customer_name .'</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600">'.__("shopee.order.datatable.total_amount").' :</span> '. $currency_symbol . number_format((float)$row->total, 2, '.', '').'
                                                        ( <a style="cursor: pointer;" data-order-id="' . $row->id . '" class="modal-open" onClick="productsOrder(this)">' . ((isset($row->line_items) and !empty($row->line_items) and $row->line_items!='""')?count(json_decode($row->line_items)):0).' '.__("shopee.order.datatable.total_item_s").'</a> )
                                                    </div>
                                                    <div class="mb-3">
                                                        <span class="text-gray-600">'.__("shopee.order.datatable.shipping_method").' :</span>
                                                        <a style="cursor: pointer;" data-id="'. $row->id .'" id="BtnAddress" class="modal-open">' . $shipment_method .'</a>
                                                    </div>
                                                    <div class="shopee_order_tracking_no_'. $row->order_id .' '.(empty($row->tracking_number)?"hide":"").'">
                                                        <span class="text-gray-600">'.__("shopee.order.datatable.tracking_no").' : '.$row->tracking_number.'</span>
                                                    </div>
                                                    <div class="shopee_order_processing_now_badge_'. $row->order_id .' '.(($row->status_custom==ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB && empty($row->tracking_number))?"py-2":"hide").'">
                                                        <span class="font-bold badge-status--green pb-1 pt-1">Processing Now'.$row->status.'</span>
                                                    </div>
                                                    <div class="'.(!isset($row->downloaded_at)?"hide":"").'">
                                                        <span class="text-gray-600">Printed At : '.(isset($row->downloaded_at)?date("d/m/Y h:i A", strtotime($row->downloaded_at)):'').'</span>
                                                    </div>
                                                    <div class="'.(!isset($row->mark_as_shipped_at)?"hide":"").'">
                                                        <span class="text-gray-600">Mark As Shipped At : '.(isset($row->mark_as_shipped_at)?date("d/m/Y h:i A", strtotime($row->mark_as_shipped_at)):'').'</span>
                                                    </div>
                                                    <div class="'.(!isset($row->pickup_confirmed_at)?"hide":"").'">
                                                        <span class="text-gray-600">Pick Confirmed At : '.(isset($row->mark_as_shipped_at)?date("d/m/Y h:i A", strtotime($row->pickup_confirmed_at)):'').'</span>
                                                    </div>
                                                    <div class="mt-3 text-center sm:text-left">
                                                        '. $functionalBtns .'
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="lg:col-span-1">
                                        <div class="border border-dashed border-r-0 border-b-0 border-l-0 border-gray-300">
                                            <div class="px-2 py-2 lg:text-left">
                                                <div class="text-xs sm:text-sm">
                                                    <span class="text-gray-600">'.__("shopee.order.datatable.order_date").' :</span> '. date('d/m/Y h:i a', strtotime($row->order_date)) .'
                                                </div>
                                                <div class="text-xs sm:text-sm">
                                                    <span class="text-gray-600">'.__("shopee.order.datatable.payment_method").' :</span> '. $row->payment_method_title .'
                                                </div>
                                                <div class="text-xs sm:text-sm '.((isset($row->shopee_shipping_method) and !empty($row->shopee_shipping_method))?"":"hide").'">
                                                    <span class="text-gray-600">Shopee Shipping Method :</span> '.ucwords($row->shopee_shipping_method).'
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ';
                })
                ->rawColumns(['checkbox', 'order_data'])
                ->make(true);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [];
    }


    /**
     * Handle server-side datatable of order managements.
     *
     * NOTE:
     * Custom status filtering combinations
     * Processing = "Ready To Ship" + no tracking number
     * Ready To Ship = "Ready To Ship" + tracking number + no awb_url
     * Ready To Ship (printed) = "Ready To Ship" + awb_url
     *
     * @param  \App\Http\Requests\OrderManagement\DatatableRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function dataAllShopeeShipments(DatatableRequest $request)
    {
        $this->removeMissingAwburlForShopeeOrdersCountFromSession();
        $this->removeMissingTrackingNumberForShopeeOrdersCountFromSession();

        $shopeePurchaseOrderTable = (new ShopeeOrderPurchase())->getTable();
        $shopsTable = (new Shopee())->getTable();
        $orderParamInitTable = (new ShopeeOrderParamInit())->getTable();
        $orderStatuses = $request->get('status', 0);
        $shopeeId = $request->get('shopee_id', 0);
        $shopeeShippingMethod = $request->get('shipping_method', "");
        $search = isset($request->get('search')['value']) ? $request->get('search')['value']:null;

        $orderColumnIndex = isset($request->get('order')[0]['column']) ? $request->get('order')[0]['column']:1;

        $orderDir = isset($request->get('order')[0]['dir']) ? $request->get('order')[0]['dir']:'desc';

        $availableColumnsOrder = [
            'id', 'order_date'
        ];

        $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
            ? $availableColumnsOrder[$orderColumnIndex]
            : $availableColumnsOrder[0];

        $ShopeeOrderPurchases = ShopeeOrderPurchase::selectRaw("{$shopeePurchaseOrderTable}.*, {$shopsTable}.shop_name")
            ->where("{$shopeePurchaseOrderTable}.seller_id", Auth::id())
            ->withCount('orderProductDetails')
            ->joinedDatatable();

        /* Handle shipping method filter. */
        if (!empty($shopeeShippingMethod)) {
            if (array_key_exists($shopeeShippingMethod, ShopeeOrderPurchase::getShippingMethodForShopee())) {
                if ($shopeeShippingMethod == "pickup") {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->where("{$orderParamInitTable}.pickup", "like", "%address_id%");
                } else if ($shopeeShippingMethod == "dropoff_branch_id") {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->where("{$orderParamInitTable}.dropoff", "like", "%branch_id%");
                } else if ($shopeeShippingMethod == "dropoff_tracking_no") {
                    $ShopeeOrderPurchases = $ShopeeOrderPurchases->where("{$orderParamInitTable}.dropoff", "like", "%tracking_no%");
                }
            }
        }

        if (!empty($shopeeId) and $shopeeId > 0) {
            $ShopeeOrderPurchases = $ShopeeOrderPurchases->where("{$shopeePurchaseOrderTable}.website_id", $shopeeId);
        }

        // echo $request->get('shopeeShipmentNo');


        if (!empty($request->get('shopeeShipmentNo')) and $request->get('shopeeShipmentNo') > 0) {
            $ShopeeOrderPurchases = $ShopeeOrderPurchases->where("{$shopeePurchaseOrderTable}.order_id", $request->get('shopeeShipmentNo'));
        }


        if (in_array($orderStatuses, [
            ShopeeOrderPurchase::AIRWAY_BILL_STATUS_PRINTED,
            ShopeeOrderPurchase::AIRWAY_BILL_STATUS_NOT_PRINTED
        ])) {
            /**
             * Get data by print status.
             * If "status" is "PRINTED", then check if "awb_printed_at" is not null.
             * If "status" is "NOT_PRINTED", then check if "awb_printed_at" is null.
             * NOTE:
             * For this we are only using the "state_custom", "READY_TO_SHIP_AWB".
             */
            $ShopeeOrderPurchases = $ShopeeOrderPurchases->byPrintStatus($orderStatuses)
                ->byMultipleOrderStatusCustom(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB);
        } else {
            /* Get data by "custom_status". */
            $now = Carbon::now()->subHours(1)->toDateTimeString();
            if ($orderStatuses == ShopeeOrderPurchase::ORDER_STATUS_UNVERIFIED) {
                /**
                 * For "UNVERIFIED" custom_status we will use "PROCESSING" custom_status.
                 * Here for this specific status we will look for orders which have not passed the 1 hour mark yet.
                 */
                $ShopeeOrderPurchases = $ShopeeOrderPurchases->byMultipleOrderStatusCustom(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING);
                $ShopeeOrderPurchases = $ShopeeOrderPurchases->where('order_date', '>', $now);
            } else if ($orderStatuses == ShopeeOrderPurchase::ORDER_STATUS_PROCESSING) {
                $ShopeeOrderPurchases = $ShopeeOrderPurchases->byMultipleOrderStatusCustom($orderStatuses)->where('order_date', '<', $now);
            } else {
                $ShopeeOrderPurchases = $ShopeeOrderPurchases->byMultipleOrderStatusCustom($orderStatuses);
            }
        }

        $ShopeeOrderPurchases = $ShopeeOrderPurchases->searchDatatable($search)
            ->orderBy($orderColumnName, $orderDir);

        if (isset($request->length)) {
            $per_page = (int) $request->length;
            if ($per_page === 300) {
                $ShopeeOrderPurchases = $ShopeeOrderPurchases->limit(3000);
            } else if ($per_page === 100) {
                $ShopeeOrderPurchases = $ShopeeOrderPurchases->limit(1500);
            } else if ($per_page === 50) {
                $ShopeeOrderPurchases = $ShopeeOrderPurchases->limit(750);
            } else {
                $ShopeeOrderPurchases = $ShopeeOrderPurchases->limit(500);
            }
        } else {
            $ShopeeOrderPurchases = $ShopeeOrderPurchases->limit(1500);
        }

        return Datatables::of($ShopeeOrderPurchases)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return $row->website_id . '*' . $row->id . '*' . $row->order_id;
            })
            ->addColumn('order_data', function ($row) {
                $shipment_method = 'None';
                if (isset($row->shipping_lines) and !empty($row->shipping_lines)) {
                    $shipping_line = json_decode($row->shipping_lines);
                    if (isset($shipping_line)) {
                        if (isset($shipping_line->shipping_carrier) and !empty($shipping_line->shipping_carrier)) {
                            $shipment_method = $shipping_line->shipping_carrier;
                        } else if (isset($shipping_line->checkout_shipping_carrier) and !empty($shipping_line->checkout_shipping_carrier)) {
                            $shipment_method = $shipping_line->checkout_shipping_carrier;
                        }
                    }
                }

                /* Get customer name from "billing". */
                $customer_name = '';
                if (isset($row->billing) and !empty($row->billing)) {
                    $billing_data = json_decode($row->billing);
                    $customer_name = isset($billing_data->name)?$billing_data->name:"";
                }

                $shop_logo = asset('img/No_Image_Available.jpg');
                if (!empty($row->shop_logo) && file_exists(public_path($row->shop_logo))) {
                    $shop_logo = asset($row->shop_logo);
                }

                /**
                 * To check if the order has passed one hour mark.
                 * In the first hour order can be canceled by a customer.
                 */
                $order_can_be_cancelled = false;
                if (isset($row->order_date)) {
                    if(Carbon::parse($row->order_date)->diffInHours(Carbon::now()) < 1) {
                        $order_can_be_cancelled = true;
                    }
                }

                $functionalBtns = '';

                /* Check if the order is being process for init. */
                $processing_order_for_init = false;
                if ($row->status_custom==strtolower(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING)) {
                    if (Cache::has($this->getKeyPrefixForShopeeTrackingInit().$row->order_id)) {
                        $processing_order_for_init = true;
                    }
                    if (!$order_can_be_cancelled) {
                        $functionalBtns .= '
                        <button type="button" class="'.($processing_order_for_init?"hide":"btn-action--green").' btn_arrange_shipment btn_arrange_shipment_'.$row->order_id.' modal-open"
                            title="Arrange Shipment"
                            data-id="'. $row->id .'"
                            data-order_id="'. $row->order_id .'"
                            data-tracking_no="'. $row->tracking_number .'"
                            onClick="arrangeOrderShipment(this)" style="cursor: pointer;">
                            <i class="fas fa-pencil-alt"></i>
                            <span class="ml-2 hidden lg:inline">'.__("shopee.order.datatable.btn.arrange_shipment").'</span>
                        </button>';
                    }
                }

                /**
                 * For "Shopee" update and delete is disabled. In "Shopee" an order can only be cancelled.
                 * No other status can be changed or an order can't be removed using the Api.
                 */
                if ($this->update_order_enabled) {
                    $functionalBtns .= '
                    <button type="button" class="modal-open btn-action--green BtnUpdateStatus" title="Update Status" id="BtnUpdateStatus"
                        data-id="'. $row->id .'"
                        data-order_id="'. $row->order_id .'">
                        <i class="fas fa-pencil-alt"></i>
                        <span class="ml-2 hidden lg:inline">'.__("shopee.order.datatable.btn.update").'</span>
                    </button>';
                }
                if ($this->delete_order_enabled) {
                    $functionalBtns .= '
                    <button type="button" class="btn-action--red BtnDelete" title="Delete"
                        data-id="'. $row->id .'"
                        data-order_id="'. $row->order_id .'">
                        <i class="fas fa-trash"></i>
                        <span class="ml-2 hidden lg:inline">'.__("shopee.order.datatable.btn.delete").'</span>
                    </button>';
                }

                if ($row->status == ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP) {
                    /* Get the "shop_id" for the shopee shop. */
                    $shopee_shop_id = $this->getShopIdOfSpecificShop($row->website_id);
                    /* Get the "awb_url" from Shopee if missing in the system. */
                    if (isset($shopee_shop_id)) {
                        if ($row->status_custom == strtolower(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB)) {
                            if (!isset($row->awb_url) || empty($row->awb_url)) {
                                ShopeeOrderAirwayBillPrint::dispatch((int) $shopee_shop_id, [$row->order_id]);
                                $this->updateMissingAwburlForShopeeOrdersCountInSession();
                            }
                            if (!isset($row->tracking_number) || empty($row->tracking_number)) {
                                ShopeeOrderDetailSync::dispatch((int) $shopee_shop_id, Auth::id(), $row->website_id, [$row->order_id], "tracking_number");
                                $this->updateMissingTrackingNumberForShopeeOrdersCountInSession();
                            }
                        }
                    }


                }

                /* Arrange shipment for "RETRY_SHIP". */
                if ($row->status == ShopeeOrderPurchase::ORDER_STATUS_RETRY_SHIP) {
                    if ($this->cancel_order_enabled) {
                        $functionalBtns .= '
                        <button type="button" class="btn-action--red btn_cancel_order" title="Cancel Order"
                            data-id="'. $row->id .'"
                            data-order_id="'. $row->order_id .'"
                            onClick="cancelSpecificOrder(this)" style="cursor: pointer;">
                            <i class="fas fa-trash"></i>
                            <span class="ml-2 hidden lg:inline">'.__("shopee.order.datatable.btn.cancel").'</span>
                        </button>';
                    }
                    if (!$order_can_be_cancelled) {
                        $functionalBtns .= '
                        <button type="button" class="btn-action--green btn_arrange_shipment modal-open"
                            title="Arrange Shipment"
                            data-id="'. $row->id .'"
                            data-order_id="'. $row->order_id .'"
                            data-tracking_no="'. $row->tracking_number .'"
                            onClick="arrangeOrderShipment(this)" style="cursor: pointer;">
                            <i class="fas fa-pencil-alt"></i>
                            <span class="ml-2 hidden lg:inline">'.__("shopee.order.datatable.btn.arrange_shipment").'</span>
                        </button>';
                        if (isset($row->awb_printed_at, $row->awb_url) and !empty($row->awb_url)) {
                            $functionalBtns .= '<br/><a href="'.$row->awb_url.'" target="_blank" class="block pt-2">View failed awb</a>';
                        }
                    }
                }

                /**
                 * Custom buttons, "Mark As Shipped" and "Pick Confirm".
                 * NOTE:
                 * This 2 just update 2 dates for display.
                 */
                if (isset($row->status_custom) and !empty($row->status_custom) and
                    isset($row->tracking_number) and !empty($row->tracking_number) and
                    $row->status_custom == strtolower(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB)) {


                }

                $print_btn_action = '<button type="button" class="shipment_btns btn btn-sm btn-warning mb-1 mt-1 action_btns" title="Download Airway Bill"
                            data-id="'. $row->id .'"
                            data-order_id="'. $row->order_id .'"
                            data-airway_bill_url=""
                            onClick="getAirwayBillForSpecificOrder(this)" style="cursor: pointer;">
                            <i class="fa fa-print mr-1" aria-hidden="true"></i>
                            <span class="ml-1 hidden lg:inline">Print Label</span>
                        </button>';
                 $pack_btn_action = '
                        <button data-id="'. $row->id .'"
                            data-order_id="'. $row->order_id .'" type="button" onClick="btnUpdatePickupConfirmStatus(this)" class="modal-open shipment_btns mb-1 mt-1 btn btn-primary btn-sm action_btns" title="Pick Confirm">
                            <i class="fa fa-truck-pickup mr-1" aria-hidden="true"></i>
                            <span class="ml-1 hidden lg:inline">Pick Confirm</span>
                        </button>';
                 $pack_btn_action_cancel = '<button onclick="pickOrderCancel(this)" type="button" class="shipment_btns mb-1 mt-1 btn btn-danger btn-sm action_btns"  data-order_id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-trash mr-1" aria-hidden="true"></i>'.__('translation.Cancel').'</button>';
                $mark_as_shipped_btn_action = '
                        <button type="button" class="modal-open shipment_btns mb-1 mt-1 btn btn-success btn-sm action_btns" onClick="btn_update_wearhouse_shipped_status(this)" title="Mark As Shipped"
                            data-id="'. $row->id .'"
                            data-order_id="'. $row->order_id .'">
                            <i class="fa fa-shipping-fast mr-1" aria-hidden="true"></i>
                            <span class="ml-1 hidden lg:inline">Mark As Wirehouse Shipped</span>
                        </button>';
                $mark_as_shipped_btn_action_cancel = '<button onclick="markAsShippedCancel(this)" type="button" class="shipment_btns mb-1 mt-1 btn btn-danger btn-sm action_btns" id="" data-order_id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-trash mr-1" aria-hidden="true"></i>'.__('translation.Cancel').'</button>';

                  if (isset($row->awb_printed_at) and !empty($row->awb_printed_at)) {
                    $print_time = Carbon::parse($row->awb_printed_at)->format('d/m/Y h:i a');
                    $userDetails = User::select('name')->where('id',$row->print_by)->first();
                    if(isset($userDetails->name)){
                        $print_by_name = '(<font class="text-blue-500">'.$userDetails->name.'</font>)';
                    }
                    else{
                        $print_by_name = '';
                    }

                    $print_by_btn =  '<div class="action_butn_style"><button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Printed On :<strong><br>'.$print_time.'<br></strong>'.$print_by_name.'</button>
                        <br>'.$print_btn_action.'</div>
                        ';
                    }
                    else{
                     $print_by_btn =  $print_btn_action;
                    }


                   if (isset($row->pickup_confirmed_at) and !empty($row->pickup_confirmed_at)) {

                        $packed_date_time = Carbon::parse($row->pickup_confirmed_at)->format('d/m/Y h:i a');
                        $userDetails = User::select('name')->where('id',$row->packed_by)->first();

                        if(isset($userDetails->name)){
                            $picked_by_name = '(<font class="text-blue-500">'.$userDetails->name.'</font>)';
                        }
                        else{
                            $picked_by_name = '';
                        }


                        $pack_status_btn =  '<div class="action_butn_style"><button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Pick Confirm :<strong><br>'.$packed_date_time.'<br></strong>'.$picked_by_name.'</button>
                            <br>'.$pack_btn_action_cancel.'</div>
                            ';
                    }
                    else{
                        $pack_status_btn = $pack_btn_action;
                    }


                 if (isset($row->mark_as_shipped_at) and !empty($row->mark_as_shipped_at)) {

                        $mark_as_shipped_date_time = Carbon::parse($row->mark_as_shipped_at)->format('d/m/Y h:i a');
                        $userDetails = User::select('name')->where('id',$row->mark_as_shipped_by)->first();

                        if(isset($userDetails->name)){
                            $mark_as_shipped_name = '(<font class="text-blue-500">'.$userDetails->name.'</font>)';
                        }
                        else{
                            $mark_as_shipped_name = '';
                        }


                        $mark_as_shipped_btn =  '<div class="action_butn_style"><button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Mark Shipped On :<strong><br>'.$mark_as_shipped_date_time.'<br></strong>'.$mark_as_shipped_name.'</button>
                            <br>
                            '.$mark_as_shipped_btn_action_cancel.'</div>
                            ';
                    }
                    else{
                        $mark_as_shipped_btn = $mark_as_shipped_btn_action;
                    }


                /* Geht the currency symbol. */
                $currency_symbol = '';
                if(isset($row->currency_symbol) and !empty($row->currency_symbol) and strlen($row->currency_symbol) === 3) {
                    $currency_symbol = currency_symbol($row->currency_symbol);
                } else {
                    $currency_symbol = currency_symbol('THB');
                }

                return '
                    <div class="border border-solid border-gray-400 lg:border-gray-300 rounded-md hover:bg-blue-50">
                        <div class="border border-dashed border-t-0 border-r-0 border-l-0 border-gray-300">
                            <div class="grid grid-cols-3">
                                <div class="col-span-3 sm:col-span-1">
                                    <div class="text-center px-2 py-1 sm:py-2">
                                        <span class="font-bold text-gray-400">#</span>
                                        <span class="relative -left-1 text-blue-500 font-bold">
                                            '. $row->order_id .'
                                        </span>
                                    </div>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <div class="px-2 py-1 sm:py-2">
                                        <span class="text-xs sm:text-sm">
                                            '. (isset($row->status_custom)?$row->str_order_status_custom:$row->str_order_status) .'
                                        </span>
                                    </div>
                                </div>
                                <div class="col-span-3 sm:col-span-1">
                                    <div class="px-2 py-1 sm:py-2">
                                        <div class="text-xs sm:text-sm">
                                                <span class="text-gray-600">'.__("shopee.order.datatable.order_date").' : </span><strong>'. date('d/m/Y h:i a', strtotime($row->order_date)) .'</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div>

                            <div class="grid grid-cols-4 lg:grid-cols-4">
                               <div class="lg:col-span-4">
                                    <div class="grid grid-cols-5">
                                      <div class="col-span-5 sm:col-span-2 px-2 py-2">
                                            <div class="mb-1 md:mb-3">
                                                <img src="'. $shop_logo .'" alt="'. $row->shop_name .'" title="'. $row->shop_name .'" class="w-16 h-auto"/>
                                            </div>
                                            <div class="">
                                                <span class="badge-status--yellow">
                                                    '. $row->shop_name .'
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-span-5 sm:col-span-3">
                                            <div class="text-left px-2 py-2">
                                                <div>
                                                    <span class="text-gray-600">'.__("shopee.order.datatable.customer_name").' : </span><span id="cn_'.$row->order_id.'">'. $customer_name .'</span>
                                                </div>
                                                <div>
                                                   Total Items:
                                                     <a style="cursor: pointer;" data-order-id="' . $row->id . '" class="modal-open" onClick="productsOrder(this)">' . ((isset($row->line_items) and !empty($row->line_items) and $row->line_items!='""')?count(json_decode($row->line_items)):0).__("shopee.order.datatable.total_item_s").'</a>
                                                </div>
                                                <div class="mb-3">
                                                    <span class="text-gray-600">'.__("shopee.order.datatable.shipping_method").' :</span>
                                                    <a style="cursor: pointer;" data-id="'. $row->id .'" id="BtnAddress" class="modal-open">' . $shipment_method .'</a>
                                                </div>
                                                <div class="'.(empty($row->tracking_number)?"hide":"").'">
                                                    <span class="text-gray-600">'.__("shopee.order.datatable.tracking_no").' : '.$row->tracking_number.'</span>
                                                </div>

                                                <div class="'.(!isset($row->downloaded_at)?"hide":"").'">
                                                    <span class="text-gray-600">Printed At : '.(isset($row->downloaded_at)?date("d/m/Y h:i A", strtotime($row->downloaded_at)):'').'</span>
                                                </div>
                                                <div class="mt-3 text-center sm:text-left">
                                                    '. $functionalBtns .'

                                                <div class="col-lg-12">
                                                <div class="row">
                                                <div class="shipment_action_btn">
                                                 '.$print_by_btn.'<br>

                                                </div>
                                                <div class="shipment_action_btn">
                                                '.$pack_status_btn.'<br>

                                                </div>
                                                <div class="shipment_action_btn">
                                                 '.$mark_as_shipped_btn.'<br>

                                                </div>
                                             </div>
                                             </div>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    ';
            })
            ->rawColumns(['checkbox', 'order_data'])
            ->make(true);
    }


    /**
     * This function keeps track of all the orders having status "READY_TO_SHIP" but "awb_url" is missing.
     * NOTE:
     * This is done to show the count in frontend.
     */
    private function updateMissingAwburlForShopeeOrdersCountInSession() {
        try {
            /* The orders with awb_url */
            if (Session::has($this->orders_with_no_awburl__session_key)) {
                $count = (int) Session::get($this->orders_with_no_awburl__session_key);
                Session::put($this->orders_with_no_awburl__session_key, $count+1);
            } else {
                Session::put($this->orders_with_no_awburl__session_key, 1);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * This function keeps track of all the orders having status "READY_TO_SHIP" but "awb_url" is missing.
     * NOTE:
     * This is done to show the count in frontend.
     */
    private function updateMissingTrackingNumberForShopeeOrdersCountInSession() {
        try {
            /* The orders with awb_url */
            if (Session::has($this->orders_with_no_tracking_number__session_key)) {
                $count = (int) Session::get($this->orders_with_no_tracking_number__session_key);
                Session::put($this->orders_with_no_tracking_number__session_key, $count+1);
            } else {
                Session::put($this->orders_with_no_tracking_number__session_key, 1);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get the total number of orders missing "awb_url" but has status "READY_TO_SHIP".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMissingAwburlForShopeeOrdersCountInSession(Request $request)
    {
        try {
            if ($request->ajax()) {
                /* The orders with awb_url */
                if (Session::has($this->orders_with_no_awburl__session_key)) {
                    return response()->json([
                        "success"   => true,
                        "data"      => [
                            "missing_awb_url_count" => Session::get($this->orders_with_no_awburl__session_key),
                            "missing_tracking_no_count" => Session::get($this->orders_with_no_tracking_number__session_key)
                        ]
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => true,
            "data"      => [
                "count" => 0
            ]
        ]);
    }


    /**
     * Remove particular data from session.
     * This is related to showing the number of failed orders to de diplayed in frontend.
     */
    private function removeMissingAwburlForShopeeOrdersCountFromSession() {
        try {
            if (Session::has($this->orders_with_no_awburl__session_key)) {
                Session::forget($this->orders_with_no_awburl__session_key);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Remove particular data from session.
     * This is related to showing the number of failed orders to de diplayed in frontend.
     */
    private function removeMissingTrackingNumberForShopeeOrdersCountFromSession() {
        try {
            if (Session::has($this->orders_with_no_tracking_number__session_key)) {
                Session::forget($this->orders_with_no_tracking_number__session_key);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get the status of init for each order from cache.
     * NOTE:
     * "processing" means the order is either in queue or is processing now.
     * "completed" means the job for "init" has successfully exectued.
     * "failed" means the "init" job failed.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShopeeOrdersProcessingNowForInit(Request $request)
    {
        try {
            if ($request->ajax()) {
                if (isset($request->json_data)) {
                    $ordersn_arr = json_decode($request->json_data);
                    $completed_init_orders = [];
                    $failed_init_orders = [];
                    foreach ($ordersn_arr as $ordersn) {
                        $cache_val = $this->getShopeeOrderProcessingRelatedInCacheForTrackingInit($ordersn);
                        if (isset($cache_val) and !empty($cache_val)) {
                            if ($cache_val == "processing") {
                                continue;
                            } else if ($cache_val == "completed") {
                                array_push($completed_init_orders, $ordersn);
                            } else if ($cache_val == "falied") {
                                array_push($failed_init_orders, $ordersn);
                            }
                            $this->removeShopeeOrderProcessingRelatedInCacheForTrackingInit($ordersn);
                        }
                    }
                    return response()->json([
                        "success"   => true,
                        "data"      => [
                            "completed" => $completed_init_orders,
                            "failed"    => $failed_init_orders
                        ],
                        "message"   => "Successfully retrieved info for init"
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => "Failed to fetch init info"
        ]);
    }


    /**
     * Check if the supplied shopee orders have a tracking number assigned to them.
     * Return the ones for which one was found.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShopeeOrdersHavingMissingTrackingNumUpdated(Request $request)
    {
        try {
            if ($request->ajax()) {
                if (isset($request->json_data)) {
                    $ordersn_arr = json_decode($request->json_data);
                    $data = [];
                    if (sizeof($ordersn_arr) > 0) {
                        $shopee_orders = ShopeeOrderPurchase::select("order_id")
                        ->whereIn("order_id", $ordersn_arr)
                        ->whereNotNull("tracking_number")
                        ->where("tracking_number", "!=", "")
                        ->get();
                        foreach ($shopee_orders as $order) {
                            array_push($data, $order->order_id);
                        }
                    }

                    return response()->json([
                        "success"   => true,
                        "data"      => [
                            "completed" => $data
                        ],
                        "message"   => "Successfully checked the shopee orders for updated tracking number."
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => "Failed to checked the shopee orders for updated tracking number."
        ]);
    }


    /**
     * Show customer address in modal.
     *
     * @param Illuminate\Http\Request $request
     */
    public function getCustomerAddress(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->id) && $request->id != null) {
                $platform = "shopee";
                $id = $request->id;
                $data = ShopeeOrderPurchase::find($id);
                $countries = \App\Models\WooCountry::all();
                $states = \App\Models\WooState::all();
                return view('elements.form-show-customer-address', compact(['data', 'countries', 'states', 'id', 'platform']));
            }
        }
    }


    /**
     * Get the modal to update the status of an order.
     *
     * @param Illuminate\Http\Request $request
     */
    public function getOrderStatus(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->id) && $request->id != null) {
                $row_index = $request->row_index;
                $data = ShopeeOrderPurchase::where('id', $request->id)->first();
                $statuses = ShopeeOrderPurchase::getAllOrderStatus();
                return view('elements.form-show-order-status', compact(['data', 'row_index', 'statuses']));
            }
        }
    }


    /**
     * Change order status of a selected order.
     * NOTE:
     * Except "CANCELLED" and "IN_CANCEL" no other status can be used to update order status in "Shopee" Api.
     * Only changing order status to "CANCEL" is supported in the current Api.
     * Only the status of the order in db is updated.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeOrderPurchaseStatus(Request $request)
    {
        try {
            if ($request->ajax()) {
                if (!$this->update_order_enabled) {
                    return __("shopee.default_error_msg");
                }
                $new_status = $request->status;
                if (!array_key_exists($new_status, ShopeeOrderPurchase::getAllOrderStatus())) {
                    return __("shopee.order.change_status.invalid_status");
                }

                $order_purchase_id = (int) $request->id;
                $orderPurchase = ShopeeOrderPurchase::find($order_purchase_id);
                if (!isset($orderPurchase, $orderPurchase->website_id)) {
                    return __("shopee.no_such_order");
                }

                $shopee_shop = Shopee::find($orderPurchase->website_id);
                if (!isset($shopee_shop)) {
                    return __("translation.Shop Not Found");
                }

                $update_db = false;
                if ($this->cancel_order_enabled and ($new_status === ShopeeOrderPurchase::ORDER_STATUS_CANCELLED || $new_status === ShopeeOrderPurchase::ORDER_STATUS_IN_CANCEL)) {
                    $update_db = $this->cancelOrderInShopee($orderPurchase->order_id, $shopee_shop->shop_id, '');
                }

                if ($update_db) {
                    $orderPurchase->status = $request->status;
                    $orderPurchase->save();
                    return __("shopee.order.change_status.success");
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return __("shopee.order.change_status.failed");
    }


    /**
     * Cancel an order in shopee.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelSpecificOrderInShopee(Request $request)
    {
        try {
            if ($request->ajax() and $this->cancel_order_enabled and isset($request->id, $request->reason)
            and !empty($request->id) and !empty($request->reason)) {
                $order_purchase_id = (int) $request->id;
                $orderPurchase = ShopeeOrderPurchase::find($order_purchase_id);
                if (!isset($orderPurchase, $orderPurchase->website_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.no_such_order")
                    ]);
                }

                $shopee_shop = Shopee::find($orderPurchase->website_id);
                if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Shop Not Found")
                    ]);
                }

                if (!array_key_exists($request->reason, ShopeeOrderPurchase::getAllOrderCancelReasons())) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.order.cancel_order.invalid_reason")
                    ]);
                }

                $cancelled_shopee_order = false;
                $cancelled_shopee_order = $this->cancelOrderInShopee($orderPurchase->order_id, $shopee_shop->shop_id, $request->reason);
                if ($cancelled_shopee_order) {
                    return response()->json([
                        "success"   => true,
                        "message"   => __("shopee.order.cancel_order.success")
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.cancel_order.failed")
        ]);
    }


    /**
     * Cancel a specific order in Shopee.
     *
     * @param string $ordersn
     * @param string $shopee_shop_id
     * @param string $reason
     * @return boolean
     */
    private function cancelOrderInShopee($ordersn, $shopee_shop_id, $reason="") {
        try {
            if (array_key_exists($reason, ShopeeOrderPurchase::getAllOrderCancelReasons())) {
                $client = $this->getShopeeClient($shopee_shop_id);
                $response = $client->order->cancelOrder(
                    [
                        'ordersn' => $ordersn,
                        'cancel_reason' => $reason,
                        'timestamp' => time()
                    ]
                );
                $errors = $response->getData()["error"];
                if (!isset($errors)) {
                    return true;
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return false;
    }


    /**
     * Get products list from a specific order to display in modal.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderProducts(Request $request)
    {
        $orderId = $request->get('orderId', 0);
        $order = ShopeeOrderPurchase::where(['id' => $orderId])->first();
        if (!isset($order, $order->line_items) || empty($order->line_items) || $order->line_items=='""') {
            return response()->json([
                'data' => [['', '']],
            ]);
        }

        $orderDetails = json_decode($order->line_items);

        $productData = [];
        foreach ($orderDetails as $item) {
            $row = [];

            $image_url = asset('No-Image-Found.png');
            if (isset($item->item_sku) and !empty($item->item_sku)) {
                $product = ShopeeProduct::whereProductCode($item->item_sku)->first();
                if (isset($product) and isset($product->images) and !empty($product->images)) {
                    $images = json_decode($product->images);
                    if (!empty($images[0])) {
                        $image_url = $images[0];
                    }
                }
            }

            $currency_symbol = '';
            if(isset($item->currency_symbol) and !empty($item->currency_symbol) and strlen($item->currency_symbol) === 3) {
                $currency_symbol = currency_symbol($item->currency_symbol);
            } else {
                $currency_symbol = currency_symbol('THB');
            }

            $row[] = '
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <img src="'. $image_url .'" height="90" width="90" class="" />
                    </div>
                    <div>
                        <span class="whitespace-nowrap text-blue-500">
                            '.__("shopee.order.product.id").' : <strong>'. $item->item_id .'</strong>
                        </span>
                    </div>
                </div>
            ';

            $row[] = '
                <div>
                    <div class="mb-1">
                        <strong>'. $item->item_name .'</strong><br/>
                        <strong>'. $item->variation_name .'</strong>
                    </div>
                    <div class="mb-1">
                        Item SKU : <strong class="text-blue-500">'. $item->item_sku .'</strong><br/>
                        Variation SKU : <strong class="text-blue-500">'. $item->variation_sku .'</strong>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700 font-bold">'.__("shopee.price").' : </label>
                            <strong class="">'. $currency_symbol . number_format(floatval($item->variation_original_price), 2) .'</strong>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700 font-bold">'.__("shopee.order.product.quantity").' : </label>
                            <span class="text-gray-900">
                                '. number_format($item->variation_quantity_purchased) .'
                            </span>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700 font-bold">'.__("shopee.total_price").' : </label>
                            <strong class="">'. $currency_symbol . number_format(floatval($item->variation_discounted_price), 2) .'</strong>
                        </div>
                    </div>
                </div>
            ';

            $productData[] = $row;
        }

        return response()->json([
            'data' => $productData,
        ]);
    }


    /**
     * Delete specific shoper order from system.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSpecificOrder(Request $request)
    {
        try {
            if ($request->ajax() and $this->delete_order_enabled) {
                $id = (int) $request->id;
                $order_id = $request->order_id;
                $purchase_order = ShopeeOrderPurchase::whereId($id)->whereSellerId($this->getShopeeSellerId())->whereOrderId($order_id)->first();
                if (!isset($purchase_order)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.no_such_order")
                    ]);
                }
                $shopee_shop = Shopee::find($purchase_order->website_id);
                if (!isset($shopee_shop)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Shop Not Found")
                    ]);
                }
                $purchase_order->delete();

                return response()->json([
                    "success"   => true,
                    "message"   => __("shopee.order.delete.success")
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.delete.failed")
        ]);
    }


    /**
     * Update status for selected orders.
     * NOTE:
     * Have no effect in "Shopee" Api.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkStatusUpdateForSelectedOrders(Request $request)
    {
        try {
            if ($request->ajax() and $this->update_order_enabled) {
                if (isset($request->json_data)) {
                    $total_changed_status = 0;
                    $arr = json_decode($request->json_data);
                    foreach ($arr as $web_order_data) {
                        $order_data = explode("*", $web_order_data);
                        /* $order_data[0] is 'website_id'('id' in 'shopee' table), $order_data[1] is 'id'('shopee_order_purchases' table), $order_data[2] is 'order_id'(ordersn) */
                        $order_purchase_id = (int) $order_data[1];
                        $orderPurchase = ShopeeOrderPurchase::find($order_purchase_id);
                        if (isset($orderPurchase) and array_key_exists($request->status, ShopeeOrderPurchase::getAllOrderStatus())) {
                            $orderPurchase->status = $request->status;
                            $orderPurchase->save();
                            $total_changed_status += 1;
                        }
                    }
                    if ($total_changed_status > 0) {
                        return response()->json([
                            "success"   => true,
                            "message"   => __("shopee.order.bulk_status_update.success")
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.bulk_status_update.failed")
        ]);
    }


    /**
     * Get pickup address info from "Shopee".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPickupAddressIdsFromShopee(Request $request)
    {
        try {
            if ($request->ajax()) {
                if (isset($request->id) and !empty($request->id)) {
                    $order_purchase_id = (int) $request->id;
                    $orderPurchase = ShopeeOrderPurchase::find($order_purchase_id);
                    if (!isset($orderPurchase, $orderPurchase->website_id)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("shopee.no_such_order")
                        ]);
                    }

                    $shopee_shop = Shopee::find($orderPurchase->website_id);
                    if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.Shop Not Found")
                        ]);
                    }
                } else if (isset($request->shop_id) and !empty($request->shop_id)) {
                    $shopee_shop = Shopee::find($request->shop_id);
                    if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.Shop Not Found")
                        ]);
                    }
                } else {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.order.get_pickup_address.failed")
                    ]);
                }

                $client = $this->getShopeeClient($shopee_shop->shop_id);
                if (isset($client)) {
                    $response = $client->logistics->getAddress([
                        'timestamp' => time()
                    ])->getData();

                    if (isset($response["address_list"])) {
                        return response()->json([
                            "success"   => true,
                            "data"      => $response["address_list"],
                            "message"   => __("shopee.order.get_pickup_address.success")
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.get_pickup_address.failed")
        ]);
    }


    /**
     * Get time slot info from "Shopee".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTimeSlotFromShopee(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->address_id) and !empty($request->address_id)) {
                $address_id = (int) $request->address_id;
                if (isset($request->id) and !empty($request->id)) {
                    $order_purchase_id = (int) $request->id;
                    $orderPurchase = ShopeeOrderPurchase::find($order_purchase_id);
                    if (!isset($orderPurchase, $orderPurchase->website_id, $orderPurchase->order_id)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("shopee.no_such_order")
                        ]);
                    }

                    $shopee_shop = Shopee::find($orderPurchase->website_id);
                    if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.Shop Not Found")
                        ]);
                    }
                } else if (isset($request->ordersn, $request->shop_id) and !empty($request->shop_id) and !empty($request->ordersn)) {
                    $orderPurchase = ShopeeOrderPurchase::whereOrderId($request->ordersn)->first();
                    if (!isset($orderPurchase)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("shopee.no_such_order")
                        ]);
                    }

                    $shopee_shop = Shopee::find($request->shop_id);
                    if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.Shop Not Found")
                        ]);
                    }
                } else {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.order.get_pickup_time_slot.failed")
                    ]);
                }

                $client = $this->getShopeeClient($shopee_shop->shop_id);
                if (isset($client)) {
                    $response = $client->logistics->getTimeSlot([
                        'ordersn'    => $orderPurchase->order_id,
                        'address_id' => $address_id,
                        'timestamp'  => time()
                    ])->getData();

                    if (isset($response["pickup_time"])) {
                        $time_slots = [];
                        foreach ($response["pickup_time"] as $time_slot) {
                            array_push($time_slots, [
                                "id" => $time_slot["pickup_time_id"],
                                "date" => date("Y-m-d", $time_slot["date"]),
                                "time_text" => isset($time_slot["time_text"])?$time_slot["time_text"]:""
                            ]);
                        }

                        return response()->json([
                            "success"   => true,
                            "data"      => $time_slots,
                            "message"   => __("shopee.order.get_pickup_time_slot.success")
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.get_pickup_time_slot.failed")
        ]);
    }


    /**
     * Get branch info from "Shopee".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBranchInfoFromShopee(Request $request)
    {
        try {
            if ($request->ajax()) {
                if (isset($request->id) and !empty($request->id)) {
                    $order_purchase_id = (int) $request->id;
                    $orderPurchase = ShopeeOrderPurchase::find($order_purchase_id);
                    if (!isset($orderPurchase, $orderPurchase->website_id, $orderPurchase->order_id)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("shopee.no_such_order")
                        ]);
                    }

                    $shopee_shop = Shopee::find($orderPurchase->website_id);
                    if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.Shop Not Found")
                        ]);
                    }

                    if ($this->shouldUpdateShopeeBranchesInDatabase()) {
                        $this->updateShopeeBranchesInDatabase($orderPurchase->order_id, $shopee_shop->shop_id);
                    }
                }

                $branches_data = ShopeeBranch::select('branch_id', 'address', 'country', 'state', 'city');
                if (isset($request->state) and !empty($request->state)) {
                    $branches_data->where('state', 'like', '%'.$request->state.'%');
                }
                if (isset($request->city) and !empty($request->city)) {
                    $branches_data->where('city', 'like', '%'.$request->city.'%');
                }
                $branches_data = $branches_data->get();

                return response()->json([
                    "success"   => true,
                    "data"      => $branches_data,
                    "message"   => __("shopee.order.get_branch.success")
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.get_branch.failed")
        ]);
    }


    /**
     * Get branch info from "Shopee".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBranchInfoFromDatabseForShopee(Request $request)
    {
        try {
            if ($request->ajax()) {
                $branches_data = ShopeeBranch::select('branch_id', 'address', 'country', 'state', 'city');
                if (isset($request->state) and !empty($request->state)) {
                    $branches_data->where('state', 'like', '%'.$request->state.'%');
                }
                if (isset($request->city) and !empty($request->city)) {
                    $branches_data->where('city', 'like', '%'.$request->city.'%');
                }
                $branches_data = $branches_data->get();

                return response()->json([
                    "success"   => true,
                    "data"      => $branches_data,
                    "message"   => __("shopee.order.get_branch.success")
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.get_branch.failed")
        ]);
    }


    /**
     * Get states info assigned to branches for "Shopee".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBranchStatesInfoForShopee(Request $request)
    {
        try {
            if ($request->ajax()) {
                if (isset($request->shopee_shop_id) and !empty($request->shopee_shop_id)) {
                    $shopee_shop = Shopee::whereShopId($request->shopee_shop_id)->first();
                    $order_param_init = ShopeeOrderParamInit::where("dropoff", "like", "%branch_id%")->latest()->first();
                    if (isset($shopee_shop) and isset($order_param_init) and $this->shouldUpdateShopeeBranchesInDatabase()) {
                        $this->updateShopeeBranchesInDatabase($order_param_init->ordersn, $shopee_shop->shop_id);
                    }
                }

                $states_data = ShopeeBranch::select('state')->distinct()->get();
                return response()->json([
                    "success"   => true,
                    "data"      => $states_data,
                    "message"   => __("shopee.order.get_branch.success")
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.get_branch.failed")
        ]);
    }


    /**
     * Get cities info assigned to branches for "Shopee".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBranchCitiesInfoForShopee(Request $request)
    {
        try {
            if ($request->ajax()) {
                if (!isset($request->state) || empty($request->state)) {
                    return response()->json([
                        "success"   => true,
                        "data"      => [],
                        "message"   => __("shopee.order.get_branch.success")
                    ]);
                }
                $cities_data = ShopeeBranch::select('city', 'state')->where('state', 'like', "%".$request->state."%")->distinct()->get();
                return response()->json([
                    "success"   => true,
                    "data"      => $cities_data,
                    "message"   => __("shopee.order.get_branch.success")
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.get_branch.failed")
        ]);
    }


    /**
     * Check whether the shopee branches information needs to be updated.
     * After 12 hours the database will update.
     */
    private function shouldUpdateShopeeBranchesInDatabase() {
        try {
            $branch = ShopeeBranch::first();
            if (isset($branch)) {
                $last_modified_date = Carbon::parse($branch->created_at);
                $now = Carbon::now();
                if ($last_modified_date->diffInHours($now) < 12) {
                    return false;
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return true;
    }


    /**
     * Update the shopee branches info in database.
     *
     * @param string $ordersn
     * @param integer $shopee_shop_id
     */
    private function updateShopeeBranchesInDatabase($ordersn, $shopee_shop_id) {
        try {
            $client = $this->getShopeeClient($shopee_shop_id);
            $response = $client->logistics->getBranch([
                'ordersn'    => $ordersn,
                'timestamp'  => time()
            ])->getData();

            if (isset($response["branch"])) {
                $shopee_branches = $response["branch"];
                foreach ($shopee_branches as $branch) {
                    /* Remove old data. */
                    ShopeeBranch::whereBranchId($branch["branch_id"])->delete();

                    /* Enter new branch data. */
                    $branch_db = new ShopeeBranch();
                    $branch_db->branch_id = $branch["branch_id"];
                    $branch_db->country = $branch["country"];
                    $branch_db->state = $branch["state"];
                    $branch_db->city = $branch["city"];
                    $branch_db->address = $branch["address"];
                    $branch_db->zipcode = $branch["zipcode"];
                    $branch_db->district = $branch["district"];
                    $branch_db->town = $branch["town"];
                    $branch_db->save();
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get logistic info for a specific order.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogisticInfoFromShopee(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->id) and !empty($request->id)) {
                $order_purchase_id = (int) $request->id;
                $orderPurchase = ShopeeOrderPurchase::find($order_purchase_id);
                if (!isset($orderPurchase, $orderPurchase->website_id, $orderPurchase->order_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.no_such_order")
                    ]);
                }

                $shopee_shop = Shopee::find($orderPurchase->website_id);
                if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Shop Not Found")
                    ]);
                }

                if (!isset($request->shipping_method) || empty($request->shipping_method) || !in_array(strtolower($request->shipping_method), ["pickup", "dropoff"])) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.order.set_logistic_info.invalid_method")
                    ]);
                }
                $method = strtolower($request->shipping_method);

                $client = $this->getShopeeClient((int)$shopee_shop->shop_id);
                $response = $client->logistics->getParameterForInit([
                    'ordersn'    => $orderPurchase->order_id,
                    'timestamp'  => time()
                ])->getData();

                if ($method == "pickup") {
                    if (isset($response["pickup"])) {
                        return response()->json([
                            "success"   => true,
                            "data"      => $response["pickup"],
                            "message"   => __("shopee.order.get_logistic_info.success")." ".__("shopee.order.get_logistic_info.found_pickup")
                        ]);
                    } else if (isset($response["dropoff"])) {
                        return response()->json([
                            "success"   => true,
                            "data"      => $response["dropoff"],
                            "message"   => __("shopee.order.get_logistic_info.success")." ".__("shopee.order.get_logistic_info.found_dropoff")
                        ]);
                    }
                } else {
                    if (isset($response["dropoff"])) {
                        return response()->json([
                            "success"   => true,
                            "data"      => $response["dropoff"],
                            "message"   => __("shopee.order.get_logistic_info.success")." ".__("shopee.order.get_logistic_info.found_dropoff")
                        ]);
                    } else if (isset($response["pickup"])) {
                        return response()->json([
                            "success"   => true,
                            "data"      => $response["pickup"],
                            "message"   => __("shopee.order.get_logistic_info.success")." ".__("shopee.order.get_logistic_info.found_pickup")
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.get_logistic_info.failed")
        ]);
    }


    /**
     * Set logitics info for a specific order in "Shopee".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setLogisticInfoInShopee(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->id, $request->shipping_method) and !empty($request->id) ) {
                $order_purchase_id = (int) $request->id;
                $orderPurchase = ShopeeOrderPurchase::find($order_purchase_id);
                if (!isset($orderPurchase, $orderPurchase->website_id, $orderPurchase->order_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.no_such_order")
                    ]);
                }

                if (!in_array($request->shipping_method, ["pickup", "dropoff"])) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.order.set_logistic_info.invalid_method")
                    ]);
                }

                /**
                 * Check if it has passed one hour since the order was created.
                 * In the first hour of placing an order, a customer can cancel the order.
                 */
                $order_date = Carbon::parse($orderPurchase->order_date);
                $now = Carbon::now();
                if($order_date->diffInHours($now) < 1) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.order.set_logistic_info.wait")
                    ]);
                }

                $shopee_shop = Shopee::find($orderPurchase->website_id);
                if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Shop Not Found")
                    ]);
                }

                $client = $this->getShopeeClient((int)$shopee_shop->shop_id);
                if (isset($client)) {
                    $params["ordersn"] = $orderPurchase->order_id;
                    if ($request->shipping_method == "pickup") {
                        if (!isset($request->address_id, $request->time_id) || empty($request->address_id) || empty($request->time_id)) {
                            return response()->json([
                                "success"   => false,
                                "message"   => __("shopee.order.set_logistic_info.missing_parameters")
                            ]);
                        }
                        /* The selected date and time for shipment selected by the user. */
                        if (isset($request->time_text) and !empty($request->time_text)) {
                            $orderPurchase->pickup_shipped_on = $this->processShippedOnTimeTextForPickUp($request->time_text);
                        }
                        $params["pickup"] = [
                            "address_id" => (int) $request->address_id,
                            "pickup_time_id" => $request->time_id
                        ];
                    } else if ($request->shipping_method == "dropoff") {
                        if (isset($request->branch_id) and !empty($request->branch_id)) {
                            $params["dropoff"] = [
                                "branch_id" => (int) $request->branch_id
                            ];
                        } else if (isset($request->tracking_no) and !empty($request->tracking_no)) {
                            $params["dropoff"] = [
                                "tracking_no" => strtoupper($request->tracking_no)
                            ];
                        } else if (isset($request->sender_real_name) and !empty($request->sender_real_name)) {
                            $params["dropoff"] = [
                                "sender_real_name" => $request->sender_real_name
                            ];
                        } else {
                            return response()->json([
                                "success"   => false,
                                "message"   => __("shopee.order.set_logistic_info.missing_parameters")
                            ]);
                        }
                    } else {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("shopee.order.set_logistic_info.failed")
                        ]);
                    }

                    /* Update the logistics info in Shopee. */
                    $response = $client->logistics->init($params)->getData();

                    /* Check if response is valid. */
                    if (isset($response["request_id"])) {
                        if(!isset($response["error_param"]) and !isset($response["error_params"]) and !isset($response["error_not_exist"]) and
                        !isset($response["error_not_found"]) and !isset($response["error_permission"]) and !isset($response["error_server"]) and
                        !isset($response["error_unknown"]) and !isset($response["lack_of_invoice_data"]) and !isset($response["error_auth"])) {
                            if (isset($response["tracking_number"]) and !empty($response["tracking_number"])) {
                                $orderPurchase->tracking_number = $response["tracking_number"];
                            } else if (isset($response["error"], $response["msg"])) {
                                return response()->json([
                                    "success"   => false,
                                    "message"   => $response["msg"]
                                ]);
                            }

                            /* custom_status for filter */
                            $orderPurchase->status_custom = strtolower(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB);
                            /* The date of launching "init" in Shopee. */
                            $orderPurchase->shipped_on_date = date("Y-m-d H:i:s", time());
                            $orderPurchase->save();
                            return response()->json([
                                "success"   => true,
                                "data"      => [
                                    "order_id"  => $orderPurchase->order_id
                                ],
                                "message"   => __("shopee.order.set_logistic_info.success")
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.set_logistic_info.failed")
        ]);
    }


    /**
     * Validate logitics info of batch orders in "Shopee".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateOrdersLogisticInfoInBatchInShopee(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->shopee_shop_id, $request->shipping_method, $request->json_data)
                and !empty($request->shopee_shop_id) and !empty($request->shipping_method) and !empty($request->json_data)) {
                if (!in_array($request->shipping_method, ["pickup", "dropoff_branch_id", "dropoff_tracking_no"])) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.order.validate_batch_logistic_info.invalid_method")
                    ]);
                }

                $shopee_shop = Shopee::whereShopId($request->shopee_shop_id)->first();
                if (!isset($shopee_shop)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Shop Not Found")
                    ]);
                }

                /* Process and retrieve the ordersn from "json_data". */
                $order_ids_arr = [];
                $order_list_data = json_decode($request->json_data);
                if (sizeof($order_list_data) > 100) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.order.validate_batch_logistic_info.order_limit_exceeded")
                    ]);
                }

                $wrong_shop_order_list = [];
                foreach ($order_list_data as $web_order_data) {
                    $order_data = explode("*", $web_order_data);
                    /* $order_data[0] is 'website_id'('id' in 'shopee' table), $order_data[1] is 'id'('shopee_order_purchases' table), $order_data[2] is 'order_id'(ordersn) */
                    $ordersn = $order_data[2];
                    if (isset($ordersn) and !empty($ordersn)) {
                        $website_id = (int)$order_data[0];
                        if ($website_id !== $shopee_shop->id) {
                            array_push($wrong_shop_order_list, $ordersn);
                            continue;
                        }
                        array_push($order_ids_arr, $ordersn);
                    }
                }

                if (sizeof($order_ids_arr) == 0) {
                    return response()->json([
                        "success"   => true,
                        "data"      => [
                            "wrong_shop_order_list"  => $wrong_shop_order_list
                        ],
                        "message"   => __("shopee.order.validate_batch_logistic_info.empty_list")
                    ]);
                }

                $shopee_order_purchase_table = (new ShopeeOrderPurchase())->getTable();
                $shopee_param_init_table = (new ShopeeOrderParamInit())->getTable();

                /**
                 * The order list which have invalid status.
                 */
                $invalid_status_order_list = [];
                $orderPurchases = ShopeeOrderPurchase::whereIn("{$shopee_order_purchase_table}.order_id", $order_ids_arr)
                    ->whereNotIn("{$shopee_order_purchase_table}.status", [
                        ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP,
                        ShopeeOrderPurchase::ORDER_STATUS_RETRY_SHIP
                    ])->get();

                foreach($orderPurchases as $orderPurchase) {
                    array_push($invalid_status_order_list, $orderPurchase->order_id);
                }

                /**
                 * The order list which have valid status.
                 */
                $orderPurchases = ShopeeOrderPurchase::join("{$shopee_param_init_table}", "{$shopee_param_init_table}.ordersn", "=", "{$shopee_order_purchase_table}.order_id")
                    ->whereIn("{$shopee_order_purchase_table}.order_id", $order_ids_arr)
                    ->whereIn("{$shopee_order_purchase_table}.status", [
                        ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP,
                        ShopeeOrderPurchase::ORDER_STATUS_RETRY_SHIP
                    ]);
                if ($request->shipping_method == "pickup") {
                    $orderPurchases = $orderPurchases->where("{$shopee_param_init_table}.pickup", "like", "%address_id%");
                } else if ($request->shipping_method == "dropoff_branch_id") {
                    $orderPurchases = $orderPurchases->where("{$shopee_param_init_table}.dropoff", "like", "%branch_id%");
                } else if ($request->shipping_method == "dropoff_tracking_no") {
                    $orderPurchases = $orderPurchases->where("{$shopee_param_init_table}.dropoff", "like", "%tracking_no%");
                } else {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.order.validate_batch_logistic_info.failed")
                    ]);
                }
                $orderPurchases = $orderPurchases->get();

                $ordersn_list = [];
                /**
                 * Check if each order has passed one hour since the orders were created.
                 * In the first hour of placing an order, a customer can cancel the order.
                 */
                $not_applicable_order_list = [];
                $now = Carbon::now();
                foreach($orderPurchases as $orderPurchase) {
                    $order_date = Carbon::parse($orderPurchase->order_date);
                    if($order_date->diffInHours($now) > 1) {
                        array_push($ordersn_list, $orderPurchase->order_id);
                    } else {
                        array_push($not_applicable_order_list, $orderPurchase->order_id);
                    }
                }

                return response()->json([
                    "success"   => true,
                    "data"      => [
                        "shipping_method"           => $request->shipping_method,
                        "valid_order_list"          => $ordersn_list,
                        "invalid_status_order_list" => $invalid_status_order_list,
                        "wrong_shop_order_list"     => $wrong_shop_order_list,
                        "not_applicable_order_list" => $not_applicable_order_list
                    ],
                    "message"   => __("shopee.order.validate_batch_logistic_info.success")
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.validate_batch_logistic_info.failed")
        ]);
    }


    /**
     * Set logitics info of batch orders in "Shopee".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setLogisticInfoInBatchInShopee(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->shipping_method, $request->json_data)
                and !empty($request->shipping_method) and !empty($request->json_data)) {
                if (!in_array($request->shipping_method, ["pickup", "dropoff_branch_id", "dropoff_tracking_no"])) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.order.set_batch_logistic_info.invalid_method")
                    ]);
                }

                $ordersn_list = [];
                $order_list_data = json_decode($request->json_data);

                if (isset($request->shopee_shop_id) and !empty($request->shopee_shop_id) and $request->shipping_method == "pickup") {
                    $shopee_shop = Shopee::whereShopId($request->shopee_shop_id)->first();
                    if (!isset($shopee_shop)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.Shop Not Found")
                        ]);
                    }
                }

                /**
                 * Contains shop wise ordersn list.
                 */
                $shop_wise_ordersn_list = [];
                $shopee_shops = [];
                if (isset($shopee_shop)) {
                    array_push($shopee_shops, $shopee_shop);
                    $shop_wise_ordersn_list[$shopee_shop->id] = [];
                    $ordersn_list[$shopee_shop->id] = [];
                } else {
                    $shopee_shops = Shopee::get();
                    foreach ($shopee_shops as $shop) {
                        $shop_wise_ordersn_list[$shop["id"]] = [];
                        $ordersn_list[$shop["id"]] = [];
                    }
                }

                $list_is_empty = true;
                foreach ($order_list_data as $web_order_data) {
                    $order_data = explode("*", $web_order_data);
                    /* $order_data[0] is 'website_id'('id' in 'shopee' table), $order_data[1] is 'id'('shopee_order_purchases' table), $order_data[2] is 'order_id'(ordersn) */
                    $ordersn = $order_data[2];
                    if (isset($ordersn) and !empty($ordersn)) {
                        $website_id = (int)$order_data[0];
                        if (isset($shopee_shop) and $website_id !== $shopee_shop->id) {
                            continue;
                        }
                        if ($list_is_empty) {
                            $list_is_empty = false;
                        }
                        array_push($shop_wise_ordersn_list[$website_id], $ordersn);
                    }
                }

                if ($list_is_empty) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.order.set_batch_logistic_info.empty_list")
                    ]);
                }

                $shopee_order_purchase_table = (new ShopeeOrderPurchase())->getTable();
                $shopee_param_init_table = (new ShopeeOrderParamInit())->getTable();

                $list_is_empty = true;
                $now = Carbon::now();
                foreach ($shopee_shops as $shop) {
                    $website_id = $shop["id"];
                    /**
                     * The order list which have valid status.
                     */
                    $orderPurchases = ShopeeOrderPurchase::join("{$shopee_param_init_table}", "{$shopee_param_init_table}.ordersn", "=", "{$shopee_order_purchase_table}.order_id")
                    ->whereIn("{$shopee_order_purchase_table}.order_id", $shop_wise_ordersn_list[$website_id])
                    ->whereIn("{$shopee_order_purchase_table}.status", [ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP, ShopeeOrderPurchase::ORDER_STATUS_RETRY_SHIP]);

                    if ($request->shipping_method == "pickup") {
                        $orderPurchases = $orderPurchases->where("{$shopee_param_init_table}.pickup", "like", "%address_id%");
                    } else if ($request->shipping_method == "dropoff_branch_id") {
                        $orderPurchases = $orderPurchases->where("{$shopee_param_init_table}.dropoff", "like", "%branch_id%");
                    } else if ($request->shipping_method == "dropoff_tracking_no") {
                        $orderPurchases = $orderPurchases->where("{$shopee_param_init_table}.dropoff", "like", "%tracking_no%");
                    }
                    $orderPurchases = $orderPurchases->get();

                    /**
                     * Check if each order has passed one hour since the orders were created.
                     * In the first hour of placing an order, a customer can cancel the order.
                     */
                    foreach($orderPurchases as $orderPurchase) {
                        $order_date = Carbon::parse($orderPurchase->order_date);
                        if($order_date->diffInHours($now) < 1) {
                            continue;
                        } else {
                            if ($list_is_empty) {
                                $list_is_empty = false;
                            }
                            array_push($ordersn_list[$website_id], $orderPurchase->order_id);
                            /**
                             * Update cache. This will be used to show whether the order is processing currently or not.
                             */
                            $this->putShopeeOrderProcessingRelatedInCacheForTrackingInit($orderPurchase->order_id);
                        }
                    }
                }

                if ($list_is_empty) {
                    return response()->json([
                        "success"   => false,
                        "message"   => "All orders failed to pass the 1 hour mark."
                    ]);
                }

                $processing_now_ordersn_list = [];
                if ($request->shipping_method == "pickup") {
                    if (!isset($request->address_id, $request->time_id) || empty($request->address_id) || empty($request->time_id)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("shopee.order.set_batch_logistic_info.missing_parameters")
                        ]);
                    }
                    $selected_time_slot = "";
                    /* The selected date and time for shipment selected by the user. */
                    if (isset($request->time_text) and !empty($request->time_text)) {
                        $selected_time_slot = $this->processShippedOnTimeTextForPickUp($request->time_text);
                    }
                    foreach ($shopee_shops as $shop) {
                        $website_id = $shop["id"];
                        $shopee_shop_id = (int) $shop["shop_id"];
                        if (sizeof($ordersn_list[$website_id]) > 0) {
                            foreach ($ordersn_list[$website_id] as $i => $ordersn) {
                                ShopeeOrderInitWithAddressId::dispatch($shopee_shop_id, (int) $request->address_id, $request->time_id, $selected_time_slot, $ordersn, $this->getShopeeSellerId())->delay(Carbon::now()->addSeconds($i*2));
                                array_push($processing_now_ordersn_list, $ordersn);
                            }
                        }
                    }
                } else if ($request->shipping_method == "dropoff_branch_id") {
                    if (!isset($request->branch_id) || empty($request->branch_id)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("shopee.order.set_batch_logistic_info.missing_parameters")
                        ]);
                    }
                    $branch = ShopeeBranch::whereBranchId((int)$request->branch_id)->first();
                    if (!isset($branch)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("shopee.order.set_batch_logistic_info.invalid_branch")
                        ]);
                    }
                    foreach ($shopee_shops as $shop) {
                        $website_id = $shop["id"];
                        $shopee_shop_id = (int) $shop["shop_id"];
                        if (sizeof($ordersn_list[$website_id]) > 0) {
                            foreach ($ordersn_list[$website_id] as $i => $ordersn) {
                                ShopeeOrderInitWithBranchId::dispatch($shopee_shop_id, (int) $request->branch_id, $ordersn, $this->getShopeeSellerId())->delay(Carbon::now()->addSeconds($i*1));
                                array_push($processing_now_ordersn_list, $ordersn);
                            }
                        }
                    }
                } else if ($request->shipping_method == "dropoff_tracking_no") {
                    /**
                     * For each order a "tracking_no" is passed. For each order we are initiating a separate "init".
                     * $request->tracking_nums passes an associative array.
                     * Ex: ["ordersn_1"=>"tracking_no_1","ordersn_2"=>"tracking_no_2","ordersn_3"=>"tracking_no_1"]
                     * NOTE:
                     * Same "tracking_no" could be send for a number of orders.
                     */
                    if (!isset($request->tracking_nums)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("shopee.order.set_batch_logistic_info.missing_parameters")
                        ]);
                    }
                    $tracking_nums = (array)json_decode($request->tracking_nums);
                    if (sizeof($tracking_nums) < 0) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("shopee.order.set_batch_logistic_info.missing_parameters")
                        ]);
                    }
                    foreach ($shopee_shops as $shop) {
                        $website_id = $shop["id"];
                        $shopee_shop_id = (int) $shop["shop_id"];
                        if (sizeof($ordersn_list[$website_id]) > 0) {
                            foreach($ordersn_list[$website_id] as $index => $ordersn) {
                                $tracking_no = $tracking_nums[$ordersn];
                                if (!isset($tracking_no) || empty($tracking_no)) {
                                    continue;
                                }
                                ShopeeOrderInitWithTrackingNumber::dispatch($shopee_shop_id, $ordersn, $tracking_no, $this->getShopeeSellerId())->delay(Carbon::now()->addSeconds($index*2));
                                array_push($processing_now_ordersn_list, $ordersn);
                            }
                        }
                    }
                }
                return response()->json([
                    "success"   => true,
                    "data"      => [
                        "ordersn_list" => $processing_now_ordersn_list,
                    ],
                    "message"   => __("shopee.order.set_batch_logistic_info.success")
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.set_batch_logistic_info.failed")
        ]);
    }


    /**
     * Get status counters for shopee orders.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShopeeStatusList(Request $request)
    {
        $parentStatusId = $request->get('parentStatusId', 0);
        $shopId = $request->get('shopId', 0);

        $statusMainSchema = ShopeeOrderPurchase::getMainStatusSchemaForDatatable($shopId);
        $statusSecondarySchema = ShopeeOrderPurchase::getSecondaryStatusSchemaForDatatable($shopId);

        $statusCounts = '';
        $tabCounts = [];
        foreach ($statusMainSchema as $schema){
            if ($schema['id'] == $parentStatusId){
                $statusCounts = $schema['sub_status'];
            }
            $tabCounts[$schema['id']] = $schema['count'];
        }

        foreach ($statusSecondarySchema as $schema){
            if ($schema['id'] == $parentStatusId){
                $statusCounts = $schema['sub_status'];
            }
            $tabCounts[$schema['id']] = $schema['count'];
        }

        $data = [
            'orderStatusCounts' => $statusCounts,
            'tabCounts' => $tabCounts,
            // 'shopsToProcessCounts' => $this->getShopeeShopsWithProcessingOrdersCount(implode(",", array_column($statusMainSchema[0]['sub_status'], 'id'))),
            'shopsToProcessCounts' => $this->getShopeeShopsWithProcessingOrdersCount(array_column($statusMainSchema[0]['sub_status'], 'id')[0]),
        ];

        return response()->json($data);
    }


    /**
     * Update "custom_status" to "shipped_to_warehouse".
     * This is done by clicking "Mark As Shipped". This is an intermediary custom status between "READY_TO_SHIP" & "SHIPPED".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markShopeeOrderAsShippedToWarehouse(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->id) and !empty($request->id)) {
                $order_purchase_id = (int) $request->id;
                $orderPurchase = ShopeeOrderPurchase::find($order_purchase_id);
                if (!isset($orderPurchase, $orderPurchase->order_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.no_such_order")
                    ]);
                }

                if ($orderPurchase->status_custom === strtolower(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB)
                    and !isset($orderPurchase->mark_as_shipped_at)) {
                    $orderPurchase->status_custom = strtolower(ShopeeOrderPurchase::ORDER_STATUS_SHIPPED_TO_WEARHOUSE);
                    $orderPurchase->mark_as_shipped_at = Carbon::now()->format("Y-m-d H:i:s");
                    $orderPurchase->mark_as_shipped_by = Auth::user()->id;
                    $orderPurchase->save();
                    return response()->json([
                        "success"   => true,
                        "message"   => __("shopee.order.mark_as_shipped_to_wearhouse.success")
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.mark_as_shipped_to_wearhouse.failed")
        ]);
    }


    /**
     * This is done by clicking "Pick Confirm". This is an intermediary custom status between "READY_TO_SHIP" & "SHIPPED".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markShopeeOrderAsPickupConfirmed(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->id) and !empty($request->id)) {
                $order_purchase_id = (int) $request->id;
                $orderPurchase = ShopeeOrderPurchase::find($order_purchase_id);
                if (!isset($orderPurchase, $orderPurchase->order_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.no_such_order")
                    ]);
                }

                if ($orderPurchase->status_custom === strtolower(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB)
                    and !isset($orderPurchase->pickup_confirmed_at)) {
                    $orderPurchase->pickup_confirmed_at = Carbon::now()->format("Y-m-d H:i:s");
                    $orderPurchase->packed_by = Auth::user()->id;
                    $orderPurchase->save();
                    return response()->json([
                        "success"   => true,
                        "message"   => __("shopee.order.pickup_confirmed.success")
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.pickup_confirmed.failed")
        ]);
    }


    /**
     * Get the total number of orders missing "awb_url" but has status "READY_TO_SHIP".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShopeeShippingMethodsWithOrderCount(Request $request)
    {
        try {
            if ($request->ajax()) {
                $shopee_id = ($request->id == -1)?null:$request->id;
                return response()->json([
                    "success"   => true,
                    "data"      => [
                        "shipping_methods" => ShopeeOrderPurchase::getShippingMethodForShopeeWithOrdersCount($shopee_id)
                    ]
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "data"      => []
        ]);
    }


    /**
     * @param Request $request
     * @return array
     */
    public function checkIfExistsShopeeOrder(Request $request){
        $shipment_id = $request->shipment_id;
        $result = ShopeeOrderPurchase::select('shopee_order_purchases.*')
                    ->where('shopee_order_purchases.order_id', '=', $shipment_id)
                    ->first();

        /* Get customer name from "billing". */
        $customer_name = '';
        if (isset($result->billing) and !empty($result->billing)) {
            $billing_data = json_decode($result->billing);
            $customer_name = isset($billing_data->name)?$billing_data->name:"";
        }
        $getTotalItems = '';
        if(isset($result)){
            if(isset($result->line_items) and !empty($result->line_items) and $result->line_items!='""'){
                $getTotalItems = count(json_decode($result->line_items));
            } else {
                $getTotalItems = 0;
            }

            // $getStatus = ShopeeOrderPurchase::determineStatusCustom($result->status, $result->tracking_number);

            if($result->status_custom){
                $getStatus =  $result->str_order_status_custom;
            }
            else{
                $getStatus =  $result->str_order_status;
            }

            $data = [
                'shipment_no' => $result->id,
                'order_id' => $result->order_id,
                'customer_name' => $customer_name,
                'getTotalItems' => $getTotalItems,
                'getStatus' => $getStatus

            ];
            return $data;
        } else {
            return __('translation.No Order Found');
        }
    }

    public function afterSearchModalContentForShopee(Request $request){
        $shipment_id = $request->shipment_id;
        $shipments = ShopeeOrderPurchase::select('shopee_order_purchases.*')
                    ->where('shopee_order_purchases.order_id', '=', $shipment_id)
                    ->first();

        $orderId = $shipments->id;
        $order = ShopeeOrderPurchase::where(['id' => $orderId])->first();
        if (!isset($order, $order->line_items) || empty($order->line_items) || $order->line_items=='""') {
           $orderDetails = '';
        }

        $orderDetails = json_decode($order->line_items);
        return view('seller.shipments.after_search_modal_content_for_shopee', compact('shipments', 'order', 'orderDetails'));
    }

    public function shipmentPickOrderCancelForShopee(Request $request){
        $results = ShopeeOrderPurchase::find($request->shipment_id);
        $results->pickup_confirmed_at = NULL;
        $result = $results->save();
    }

    /**
     * @param Request $request
     */
    public function ShopeeMarkAsShippedCancel(Request $request){
        $results = ShopeeOrderPurchase::find($request->shipment_id);
        $results->mark_as_shipped_at = NULL;
        $result = $results->save();
    }

    public function getShopeeOrderedProducts(Request $request){
        $shipment_id = $request->shipment_id;
        $order = ShopeeOrderPurchase::where(['id' => $shipment_id])->first();
        if (!isset($order, $order->line_items) || empty($order->line_items) || $order->line_items=='""') {
           $orderDetails = '';
        }

        $orderDetails = json_decode($order->line_items);
        return view('seller.shipments.shopeeOrderedProducts', compact('shipment_id', 'order', 'orderDetails'));
    }



    /**
     * Validate logitics info of batch orders in "Shopee".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setLogisticInfoInBatchForMultipleShopsInShopee(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->shipping_method, $request->json_data)
                and !empty($request->shipping_method) and !empty($request->json_data)) {
                if ($this->isLockedShopeeOrderBulkInit($this->getShopeeSellerId())) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Still processing orders")
                    ]);
                }

                if (!in_array($request->shipping_method, ["pickup", "dropoff_branch_id", "dropoff_tracking_no"])) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Invalid shipping method")
                    ]);
                }

                $arr = json_decode($request->json_data);
                if (sizeof($arr) > 150) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.At a time 150 orders can be arranged.")
                    ]);
                }


                $shopee_shops = Shopee::pluck("shop_id", "id");

                $ordersn_list = [];
                foreach ($shopee_shops as $key => $shopee_shop_id) {
                    $ordersn_list[$key] = [];
                }
                foreach ($arr as $web_order_data) {
                    $order_data = explode("*", $web_order_data);
                    /* $order_data[0] is 'website_id'('id' in 'shopee' table), $order_data[1] is 'id'('shopee_order_purchases' table), $order_data[2] is 'order_id'(ordersn) */
                    $order_purchase_id = (int) $order_data[1];
                    $orderPurchase = ShopeeOrderPurchase::find($order_purchase_id);
                    if (isset($orderPurchase)) {
                        array_push($ordersn_list[$orderPurchase["website_id"]], $orderPurchase["order_id"]);
                    }
                }

                /* For "pickup", "address id" related data. */
                $address_id_data = [];
                if (isset($request->address_data) and !empty($request->address_data) and isJson($request->address_data)) {
                    $address_json_data = json_decode($request->address_data);
                    foreach ($address_json_data as $address_info) {
                        $address_id_data[$address_info->shop_id] = $address_info->address_id;
                    }
                }
                /* For "pickup", "time slot" related data. */
                $time_id_data = [];
                $time_text_data = [];
                if (isset($request->time_data) and !empty($request->time_data) and isJson($request->time_data)) {
                    $time_json_data = json_decode($request->time_data);
                    foreach ($time_json_data as $time_info) {
                        $time_id_data[$time_info->shop_id] = $time_info->time_id;
                        $time_text_data[$time_info->shop_id] = $time_info->time_text;
                    }
                }

                $seller_id = $this->getShopeeSellerId();
                $extra_time = 0;
                $processing_now_ordersn_list = [];
                foreach ($shopee_shops as $key => $shopee_shop_id) {
                    if (isset($ordersn_list[$key]) and sizeof($ordersn_list[$key]) > 0) {
                        if ($request->shipping_method == "pickup") {
                            if (!isset($request->address_data, $request->time_data) || empty($request->address_data) || empty($request->time_data)) {
                                return response()->json([
                                    "success"   => false,
                                    "message"   => __("shopee.order.set_batch_logistic_info.missing_parameters")
                                ]);
                            }

                            $selected_time_slot = "";
                            /* The selected date and time for shipment selected by the user. */
                            if (isset($time_text_data[$key])) {
                                $selected_time_slot = $this->processShippedOnTimeTextForPickUp($time_text_data[$key]);
                            }
                            if (sizeof($ordersn_list[$key]) > 0) {
                                foreach ($ordersn_list[$key] as $i => $ordersn) {
                                    if (isset($address_id_data[$key])) {
                                        ShopeeOrderInitWithAddressId::dispatch($shopee_shop_id, (int) $address_id_data[$key], $time_id_data[$key], $selected_time_slot, $ordersn, $seller_id)->delay(now()->addSeconds($extra_time+($i*1.5)));
                                        array_push($processing_now_ordersn_list, $ordersn);
                                        /* Update cache. This will be used to show whether the order is processing currently or not. */
                                        $this->putShopeeOrderProcessingRelatedInCacheForTrackingInit($ordersn);
                                    }
                                }
                                $extra_time += 1.5*sizeof($ordersn_list[$key]);
                            }
                        } else if ($request->shipping_method == "dropoff_branch_id") {
                            if (!isset($request->branch_id) || empty($request->branch_id)) {
                                return response()->json([
                                    "success"   => false,
                                    "message"   => __("shopee.order.set_batch_logistic_info.missing_parameters")
                                ]);
                            }
                            $branch = ShopeeBranch::whereBranchId((int)$request->branch_id)->first();
                            if (!isset($branch)) {
                                return response()->json([
                                    "success"   => false,
                                    "message"   => __("shopee.order.set_batch_logistic_info.invalid_branch")
                                ]);
                            }
                            if (sizeof($ordersn_list[$key]) > 0) {
                                foreach ($ordersn_list[$key] as $i => $ordersn) {
                                    ShopeeOrderInitWithBranchId::dispatch($shopee_shop_id, (int) $request->branch_id, $ordersn, $seller_id)->delay(now()->addSeconds($extra_time+($i*1.5)));
                                    array_push($processing_now_ordersn_list, $ordersn);
                                    /* Update cache. This will be used to show whether the order is processing currently or not. */
                                    $this->putShopeeOrderProcessingRelatedInCacheForTrackingInit($ordersn);
                                }
                                $extra_time += 1.5*sizeof($ordersn_list[$key]);
                            }
                        } else if ($request->shipping_method == "dropoff_tracking_no") {
                            /**
                             * For each order a "tracking_no" is passed. For each order we are initiating a separate "init".
                             * $request->tracking_nums passes an associative array.
                             * Ex: ["ordersn_1"=>"tracking_no_1","ordersn_2"=>"tracking_no_2","ordersn_3"=>"tracking_no_1"]
                             * NOTE:
                             * Same "tracking_no" could be send for a number of orders.
                             */
                            if (!isset($request->tracking_nums)) {
                                return response()->json([
                                    "success"   => false,
                                    "message"   => __("shopee.order.set_batch_logistic_info.missing_parameters")
                                ]);
                            }
                            $tracking_nums = (array)json_decode($request->tracking_nums);
                            if (sizeof($tracking_nums) < 0) {
                                return response()->json([
                                    "success"   => false,
                                    "message"   => __("shopee.order.set_batch_logistic_info.missing_parameters")
                                ]);
                            }
                            if (sizeof($ordersn_list[$key]) > 0) {
                                foreach($ordersn_list[$key] as $i => $ordersn) {
                                    $tracking_no = $tracking_nums[$ordersn];
                                    if (!isset($tracking_no) || empty($tracking_no)) {
                                        continue;
                                    }
                                    ShopeeOrderInitWithTrackingNumber::dispatch($shopee_shop_id, $ordersn, $tracking_no, $seller_id)->delay(now()->addSeconds($extra_time+($i*1.5)));
                                    array_push($processing_now_ordersn_list, $ordersn);
                                    /* Update cache. This will be used to show whether the order is processing currently or not. */
                                    $this->putShopeeOrderProcessingRelatedInCacheForTrackingInit($ordersn);
                                }
                                $extra_time += 1.5*sizeof($ordersn_list[$key]);
                            }
                        }
                    }
                }

                if ($extra_time > 0) {
                    $this->setLockForShopeeOrderBulkInit($extra_time, $seller_id);
                    $this->setLastShopeeOrderIdForBulkInitLock(end($processing_now_ordersn_list), $seller_id);
                }

                return response()->json([
                    "success"   => true,
                    "data"      => [
                        "shipping_method"   => $request->shipping_method,
                        "ordersn_list"      => $processing_now_ordersn_list
                    ],
                    "message"   => __("translation.Successfully arranged shipment for the orders")
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("translation.Failed to arrange shipment for the orders")
        ]);
    }


    /**
     * Check if shopee orders are being processed now for batch init for multiple shopee shops.
     */
    public function checkIfShopeeOrderBulkInitIsStillProcessingOrders(Request $request)
    {
        try {
            if ($request->ajax()) {
                if ($this->isLockedShopeeOrderBulkInit($this->getShopeeSellerId())) {
                    return response()->json([
                        "success"   => true,
                        "data"      => [
                            "processing" => true
                        ],
                        "message"   => __("translation.Still processing shopee orders for init.")
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => true,
            "data"      => [
                "processing" => false
            ],
            "message"   => __("translation.Not processing shopee orders for init.")
        ]);
    }
}

