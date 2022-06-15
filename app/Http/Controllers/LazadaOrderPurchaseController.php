<?php

namespace App\Http\Controllers;

use App\Models\Lazada;
use App\Models\LazadaOrderPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\LazadaOrderPurchaseTrait;
use Carbon\Carbon;
use App\Http\Requests\OrderManagement\DatatableRequest;
use App\Models\LazadaOrderPurchaseItem;
use App\Traits\LazadaOrderSyncTrait;
use App\Traits\MarketplaceOrderTabNavigationTrait;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class LazadaOrderPurchaseController extends Controller
{
    use LazadaOrderPurchaseTrait, LazadaOrderSyncTrait, MarketplaceOrderTabNavigationTrait;
    
    private $update_order_enabled = false;
    private $delete_order_enabled = false;
    private $cancel_order_enabled = false;
    private $arrange_order_shipment_enabled = true;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $statusMainSchema = LazadaOrderPurchase::getMainStatusSchemaForDatatable();
        $firstStatusOrderId = array_column($statusMainSchema[0]['sub_status'], 'id')[0];

        $statusSecondarySchema = LazadaOrderPurchase::getSecondaryStatusSchemaForDatatable();

        $data = [
            'shops' => $this->getLazadaShopsWithProcessingOrdersCount(implode(",", array_column($statusMainSchema[0]['sub_status'], 'id'))),
            'countries' => [],
            'states' => [],
            'totalProcessingWooCommerce' => $this->getTotalProcessingOrdersForWooCommerce(),
            'totalProcessingShopee' => $this->getTotalProcessingOrdersForShopee(),
            // 'totalProcessingLazada' => $this->getTotalProcessingOrdersForLazada(),
            'totalProcessingLazada' => array_column($statusMainSchema[0]['sub_status'], 'count')[0],
            'orderCancelationReasons' => [],
            'statusMainSchema' => $statusMainSchema,
            'statusSecondarySchema' => $statusSecondarySchema,
            'firstStatusOrderId' => $firstStatusOrderId,
            'shippingMethodForLazada' => LazadaOrderPurchase::getShippingMethodForShopeeWithOrdersCount()
        ];

        return view('lazada.order', $data);
    }


    /**
     * Get the total number orders falling unded "To Process". Basically orders having "processing", "retry_ship" and "in_cancel" 
     * as a value for "status_custom" falls under "To Process".
     * $processingStatuses may contain one or more statuses seperated by comma.
     * 
     * @param string $processingStatuses
     * @return array
     */
    private function getLazadaShopsWithProcessingOrdersCount($processingStatuses) {
        try {
            $shops = Lazada::where('seller_id', $this->getLazadaSellerId())
                ->select('id','shop_name','shop_id','code')
                ->orderBy('shop_name', 'asc')
                ->get();
            foreach($shops as $shop) {
                $shop["processing_orders_count"] = !empty($processingStatuses)?LazadaOrderPurchase::getMultipleStatusSchemaCount($processingStatuses, $shop["id"]):0;
                $date = $this->getBulkSyncStartTimeCacheValue($shop->id, $this->getLazadaSellerId());
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
     *
     * @param  \App\Http\Requests\OrderManagement\DatatableRequest $request
     * @return \Illuminate\Http\Response
     */
    public function data(DatatableRequest $request)
    {   
        $lazadaPurchaseOrderTable = (new LazadaOrderPurchase())->getTable();
        $shopsTable = (new Lazada())->getTable();
        $orderStatuses = $request->get('status', 0);
        $lazadaId = $request->get('lazada_id', 0);
        $derivedStatus = $request->get('derived_status', "");
        $search = isset($request->get('search')['value']) ? $request->get('search')['value']:null;

        $orderColumnIndex = isset($request->get('order')[0]['column']) ? $request->get('order')[0]['column']:1;

        $orderDir = isset($request->get('order')[0]['dir']) ? $request->get('order')[0]['dir']:'desc';

        $availableColumnsOrder = [
            'id', 'order_date'
        ];

        $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
            ? $availableColumnsOrder[$orderColumnIndex]
            : $availableColumnsOrder[0];

        $LazadaOrderPurchases = LazadaOrderPurchase::selectRaw("{$lazadaPurchaseOrderTable}.*, {$shopsTable}.shop_name")
            ->where("{$lazadaPurchaseOrderTable}.seller_id", $this->getLazadaSellerId())
            ->withCount('orderProductDetails')
            ->joinedDatatable();

        if (!empty($lazadaId) and $lazadaId > 0) {
            $LazadaOrderPurchases = $LazadaOrderPurchases->where("{$lazadaPurchaseOrderTable}.website_id", $lazadaId);
        }

        if (in_array($orderStatuses, [
            LazadaOrderPurchase::AIRWAY_BILL_STATUS_PRINTED,
            LazadaOrderPurchase::AIRWAY_BILL_STATUS_NOT_PRINTED
        ])) {
            /** 
             * Get data by print status.
             * If "status" is "PRINTED", then check if "awb_printed_at" is not null.
             * If "status" is "NOT_PRINTED", then check if "awb_printed_at" is null.
             */
            $LazadaOrderPurchases = $LazadaOrderPurchases->byPrintStatus($orderStatuses)
                ->byMultipleOrderStatusCustom(LazadaOrderPurchase::ORDER_STATUS_READY_TO_SHIP);
                // ->byMultipleOrderStatusCustom(LazadaOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB);
        } else {
            /* Get data by "custom_status". */
            $now = Carbon::now()->subHours(1)->toDateTimeString();
            if ($orderStatuses == LazadaOrderPurchase::ORDER_STATUS_UNVERIFIED) {
                /**
                 * For "UNVERIFIED" custom_status we will use "PROCESSING" custom_status.
                 * Here for this specific status we will look for orders which have not passed the 1 hour mark yet.
                 */
                $LazadaOrderPurchases = $LazadaOrderPurchases->byMultipleOrderStatusCustom(LazadaOrderPurchase::ORDER_STATUS_PROCESSING);
                $LazadaOrderPurchases = $LazadaOrderPurchases->where('order_date', '>', $now);
            } else if ($orderStatuses == LazadaOrderPurchase::ORDER_STATUS_PROCESSING) {
                if (!empty($derivedStatus)) {
                    $LazadaOrderPurchases = $LazadaOrderPurchases->whereDerivedStatus(strtoupper($derivedStatus));
                }
                $LazadaOrderPurchases = $LazadaOrderPurchases->byMultipleOrderStatusCustom($orderStatuses)->where('order_date', '<', $now);
            } else {
                $LazadaOrderPurchases = $LazadaOrderPurchases->byMultipleOrderStatusCustom($orderStatuses);
            }
        }

        $LazadaOrderPurchases = $LazadaOrderPurchases->searchDatatable($search)
            ->orderBy($orderColumnName, $orderDir);

        if (isset($request->length)) {
            $per_page = (int) $request->length;
            if ($per_page === 300) {
                $LazadaOrderPurchases = $LazadaOrderPurchases->limit(3000);
            } else if ($per_page === 100) {
                $LazadaOrderPurchases = $LazadaOrderPurchases->limit(1500);
            } else if ($per_page === 50) {
                $LazadaOrderPurchases = $LazadaOrderPurchases->limit(750);
            } else {
                $LazadaOrderPurchases = $LazadaOrderPurchases->limit(500);
            }
        } else {
            $LazadaOrderPurchases = $LazadaOrderPurchases->limit(1500);
        }
            
        return DataTables::of($LazadaOrderPurchases)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return $row->website_id . '*' . $row->id . '*' . $row->order_id;
            })
            ->addColumn('order_data', function ($row) {
                $shipment_method = 'None';
                if (isset($row->shipment_provider) and !empty($row->shipment_provider)) {
                    $shipment_method = $row->shipment_provider;
                }

                /* Get customer name from "billing". */
                $customer_name = '';
                if (isset($row->customer_first_name) and !empty($row->customer_first_name)) {
                    $customer_name .= $row->customer_first_name;
                }
                if (isset($row->customer_last_name) and !empty($row->customer_last_name)) {
                    $customer_name .= $row->customer_last_name;
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
                /* DONT DELETE THE FOLLOWING CODES */
                // if (isset($row->order_date)) {
                //     if(Carbon::parse($row->order_date)->diffInHours(Carbon::now()) < 1) {
                //         $order_can_be_cancelled = true;
                //     }
                // }

                $functionalBtns = '';

                /* Check if the order is being process for init. This is for status_custom, "Processing". */
                $processing_order_for_init = false;

                /**
                 * Custom buttons, "Mark As Shipped" and "Pick Confirm".
                 * NOTE:
                 * This 2 just update 2 dates for display.
                 */
                if (isset($row->status_custom) and !empty($row->status_custom)) { 
                    if ($row->status_custom==strtolower(LazadaOrderPurchase::ORDER_STATUS_PROCESSING)) {
                        /* Check if any processing now related data is store in session. */
                        $cache_val = Cache::has($this->getKeyPrefixForLazadaTrackingInit($this->getLazadaSellerId()).$row->order_id);
                        if (isset($cache_val) and !empty($cache_val)) {
                            if ($cache_val == "processing") {
                                $processing_order_for_init = true;
                            } else {
                                $this->removeLazadaOrderProcessingRelatedInCacheForTrackingInit($row->order_id, $this->getLazadaSellerId());
                            }
                        }
                        if ($row->derived_status==LazadaOrderPurchase::ORDER_STATUS_PENDING) {
                            $functionalBtns .= '
                            <button type="button" class="'.($processing_order_for_init?"hide":"btn-action--blue").' btn_create_package btn_create_package_'.$row->order_id.' modal-open" 
                                title="Create Package"
                                data-id="'. $row->id .'"
                                data-order_id="'. $row->order_id .'"
                                data-website_id="'. $row->website_id .'"
                                onClick="setStatusToPackedByMarketplace(this)" style="cursor: pointer;">
                                <i class="fas fa-pencil-alt"></i>
                                <span class="ml-2 hidden lg:inline">Create Package</span>
                            </button>';
                        } else if ($row->derived_status==LazadaOrderPurchase::ORDER_STATUS_PACKED) {
                            $functionalBtns .= '
                            <button type="button" class="'.($processing_order_for_init?"hide":"btn-action--green").' btn_arrange_shipment btn_arrange_shipment_'.$row->order_id.' modal-open" 
                                title="Create Package"
                                data-id="'. $row->id .'"
                                data-order_id="'. $row->order_id .'"
                                data-tracking_no="'. $row->tracking_number .'"
                                onClick="setStatusToReadyToShip(this)" style="cursor: pointer;">
                                <i class="fas fa-pencil-alt"></i>
                                <span class="ml-2 hidden lg:inline">Ready To Ship</span>
                            </button>';
                        }
                    } else if ($row->status_custom == strtolower(LazadaOrderPurchase::ORDER_STATUS_READY_TO_SHIP)) {
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
                        $functionalBtns .= '
                        <a href="'.(route('lazada.order.get_specific_order_airway_bill', ['order_id' => $row->order_id])).'" class="btn-action--blue" title="Pdf Download" target="_blank"
                            data-order_id="'. $row->order_id .'">
                            <i class="fas fa-arrow-circle-right"></i>
                            <span class="ml-2 hidden lg:inline">Download</span>
                        </a>';
                    } else if ($row->status_custom == strtolower(LazadaOrderPurchase::ORDER_STATUS_SHIPPED)) {
                        $functionalBtns .= '
                        <a href="'.(route('lazada.order.get_specific_order_airway_bill', ['order_id' => $row->order_id])).'" class="btn-action--blue" title="Pdf Download" target="_blank"
                            data-order_id="'. $row->order_id .'">
                            <i class="fas fa-arrow-circle-right"></i>
                            <span class="ml-2 hidden lg:inline">Download</span>
                        </a>';
                    }
                }

                /**
                 * For "Lazada" update and delete is disabled. In "Lazada" an order can only be cancelled.
                 * No other status can be changed or an order can't be removed using the Api.
                 */
                if ($this->update_order_enabled) {
                    $functionalBtns .= '
                    <button type="button" class="modal-open btn-action--green BtnUpdateStatus" title="Update Status" id="BtnUpdateStatus"
                        data-id="'. $row->id .'"
                        data-order_id="'. $row->order_id .'">
                        <i class="fas fa-pencil-alt"></i>
                        <span class="ml-2 hidden lg:inline">'.__("lazada.order.datatable.btn.update").'</span>
                    </button>';
                }
                if ($this->delete_order_enabled) {
                    $functionalBtns .= '
                    <button type="button" class="btn-action--red BtnDelete" title="Delete"
                        data-id="'. $row->id .'"
                        data-order_id="'. $row->order_id .'">
                        <i class="fas fa-trash"></i>
                        <span class="ml-2 hidden lg:inline">'.__("lazada.order.datatable.btn.delete").'</span>
                    </button>';
                }

                /* Geht the currency symbol. */
                $currency_symbol = '';
                if(isset($row->currency_symbol) and !empty($row->currency_symbol) and strlen($row->currency_symbol) === 3) {
                    $currency_symbol = currency_symbol($row->currency_symbol);
                } else {
                    $currency_symbol = currency_symbol('THB');
                }

                return '<div class="border border-solid border-gray-400 lg:border-gray-300 rounded-md hover:bg-blue-50">
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
                                                <span class="text-gray-600">'.__("lazada.order.datatable.customer_name").' : </span><span id="cn_'.$row->order_id.'">'. $customer_name .'</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">'.__("lazada.order.datatable.total_amount").' :</span> '. $currency_symbol . number_format((float)$row->price, 2, '.', '').'
                                                ( <a style="cursor: pointer;" data-order-id="' . $row->id . '" class="modal-open" onClick="productsOrder(this)">' . ((isset($row->items_count) and !empty($row->items_count))?$row->items_count:0).' '.__("lazada.order.datatable.total_item_s").'</a> )
                                            </div>
                                            <div class="mb-3">
                                                <span class="text-gray-600">'.__("lazada.order.datatable.shipping_method").' :</span>
                                                <a style="cursor: pointer;" data-id="'. $row->id .'" id="BtnAddress" class="modal-open">' . $shipment_method .'</a>
                                            </div>
                                            <div class="'.(empty($row->tracking_number)?"hide":"").'">
                                                <span class="text-gray-600">'.__("lazada.order.datatable.tracking_no").' : '.$row->tracking_number.'</span>
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
                                            <span class="text-gray-600">'.__("lazada.order.datatable.order_date").' :</span> '. date('d/m/Y h:i a', strtotime($row->order_date)) .'
                                        </div>
                                        <div class="text-xs sm:text-sm">
                                            <span class="text-gray-600">'.__("lazada.order.datatable.payment_method").' :</span> '. $row->payment_method_title .'
                                        </div>
                                        <div class="text-xs sm:text-sm '.((isset($row->delivery_type) and !empty($row->delivery_type))?"":"hide").'">
                                            <span class="text-gray-600">Lazada Shipping Method :</span> '.ucwords($row->delivery_type).'
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
            })
            ->rawColumns(['checkbox', 'order_data'])
            ->make(true);
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
        $order = LazadaOrderPurchase::whereId($orderId)->first();
        if (!isset($order, $order->order_item_ids) || empty($order->order_item_ids) || $order->order_item_ids=='""') {
            return response()->json([
                'data' => [['', '']],
            ]);
        }

        $orderDetails = LazadaOrderPurchaseItem::whereIn("order_item_id", json_decode($order->order_item_ids))->get();

        $productData = [];
        foreach ($orderDetails as $item) {
            $row = [];

            $image_url = asset('No-Image-Found.png');
            if (isset($item->product_main_image) and !empty($item->product_main_image)) {
                $image_url = $item->product_main_image;
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
                            '.__("lazada.order.product.id").' : <strong>'. $item->order_item_id .'</strong>
                        </span>
                    </div>
                </div>
            ';

            $row[] = '
                <div>
                    <div class="mb-1">
                        <strong>'. $item->name .'</strong>
                    </div>
                    <div class="mb-1">
                        <strong class="text-blue-500">'. $item->sku .'</strong>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700 font-bold">'.__("lazada.price").' : </label>
                            <strong class="">'. $currency_symbol . number_format(floatval($item->item_price), 2) .'</strong>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap '.(!isset($item->quantity)?"hide":"").'">
                            <label class="text-gray-700 font-bold">'.__("lazada.order.product.quantity").' : </label>
                            <span class="text-gray-900">
                                '. number_format($item->quantity) .'
                            </span>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700 font-bold">'.__("lazada.total_price").' : </label>
                            <strong class="">'. $currency_symbol . number_format(floatval($item->paid_price), 2) .'</strong>
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
     * Show customer address in modal.
     * 
     * @param Illuminate\Http\Request $request
     */
    public function getCustomerAddress(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->id) && $request->id != null) {
                $platform = "lazada";
                $id = $request->id;
                $data = LazadaOrderPurchase::find($id);
                $countries = \App\Models\WooCountry::all();
                $states = \App\Models\WooState::all();
                return view('elements.form-show-customer-address', compact(['data', 'countries', 'states', 'id', 'platform']));
            }
        }
    }
}
