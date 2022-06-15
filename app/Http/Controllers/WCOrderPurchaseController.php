<?php

namespace App\Http\Controllers;
use App\Actions\ArrangeWCShipmentActionForOrder;
use App\Actions\CreateWCShipmentActionForOrder;
use App\Actions\UpdateWCShipmentActionForOrder;
use App\Actions\WCCustomShipmentActionForOrder;
use App\Actions\UpdateWCCustomShipmentActionForOrder;

use App\Http\Requests\OrderManagement\DatatableRequest;
use App\Http\Requests\WCProduct\Shipment\{DeleteRequest, StoreRequest, UpdateRequest};
use App\Models\Channel;
use App\Models\WooCronReport;
use App\Models\WooOrderPurchase;
use App\Models\WooOrderPurchaseDetail;
use App\Models\Supplier;
use App\Models\WooShop;
use App\Models\Shop;
use App\Models\Shipment;
use App\Models\ShipmentProduct;
use App\Models\WooCountry;
use App\Models\WooState;
use App\Models\User;
use App\Models\OrderManagement;
use App\Models\TaxRateSetting;

use App\Http\Resources\WCProductSelectTwoResource;
use App\Jobs\AdjustDisplayReservedQty;
use App\Models\WooProduct;
use App\Traits\Inventory\AdjustDisplayReservedQtyTrait;
use App\Traits\Inventory\WooCommerceInventoryProductsStockUpdateTrait;
use Automattic\WooCommerce\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Yajra\DataTables\Facades\DataTables;
use function Psy\sh;
use Illuminate\Support\Facades\Storage;


class WCOrderPurchaseController extends Controller
{
    use WooCommerceInventoryProductsStockUpdateTrait, AdjustDisplayReservedQtyTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sellerId = Auth::user()->id;
        $roleName = Auth::user()->role;

        $shops = WooShop::where('woo_shops.seller_id',Auth::user()->id)
        ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
        ->select('woo_shops.id','shops.name','shop_id','site_url','rest_api_key','rest_api_secrete')
        ->get();

        $countries = WooCountry::all();
        $states = WooState::all();

        $totalProcessingOrders = OrderManagement::where('seller_id', $sellerId)
            ->where('order_status', OrderManagement::ORDER_STATUS_PROCESSING)
            ->customerAsset($roleName)
            ->count();

        $totalProcessingDropshipperOrders = OrderManagement::where('seller_id', $sellerId)
            ->where('order_status', OrderManagement::ORDER_STATUS_PROCESSING)
            ->customerAsset($roleName, User::ROLE_DROPSHIPPER)
            ->count();

        $totalProcessingWooCommerce = WooOrderPurchase::where('seller_id', $sellerId)
            ->where('status', WooOrderPurchase::ORDER_STATUS_PROCESSING)
            ->count();

        $totalToShip = Shipment::where('shipment_for', Shipment::SHIPMENT_FOR_WOO)
        ->count();

        $statusMainSchema = WooOrderPurchase::getMainStatusSchemaForDatatable();
        $firstStatusOrderId = array_column($statusMainSchema[0]['sub_status'], 'id')[0];

        $statusSecondarySchema = WooOrderPurchase::getSecondaryStatusSchemaForDatatable();

        //dd($statusMainSchema);

        $data = [
            'shops' => $shops,
            'countries' => $countries,
            'states' => $states,
            'totalProcessingOrders' => $totalProcessingOrders,
            'totalProcessingDropshipperOrders' => $totalProcessingDropshipperOrders,
            'totalProcessingWooCommerce' => $totalProcessingWooCommerce,
            'totalToShip' => $totalToShip,
            'statusMainSchema' => $statusMainSchema,
            'statusSecondarySchema' => $statusSecondarySchema,
            'firstStatusOrderId' => $firstStatusOrderId,
        ];

        return view('seller.wc_purchase_order.index', $data);
    }


    public function getWCCustomerOrderHistory(Request $request)
    {
        $orderDetails = WooOrderPurchase::where('website_id', $request->shop_id)->where('order_id', $request->order_id)->where('seller_id',Auth::user()->id)->first();



        $shipmentWithProducts = ShipmentProduct::getallShipmentsProductsByShopIdShipmentId($request->shop_id,$request->shipment_id);

       //dd($shipmentWithProducts);


        $countries = WooCountry::all();
        $states = WooState::all();

        $arr_country = array();
        if(!empty($countries)){
            foreach ($countries as $country){
                $arr_country[$country->code] = $country->name;
            }
        }

        $arr_state = array();
        if(!empty($states)){
                foreach ($states as $state){
                $arr_state[$state->code] = $state->name;
            }
        }

        $shipping = $this->formatCustomerAddress($isBulk='0',$orderDetails,$arr_country,$arr_state);
        $shopDetails = '';
         if(isset($request->shop_id)){
            $shopDetails = WooShop::where('woo_shops.id',$request->shop_id)
                            ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
                            ->first();
        }

        $data = [];
        $product_price = [];

        // for shipment products details
        if(!empty($request->shipment_id)){
         $editData = Shipment::where('id',$request->shipment_id)->with('WOOshipment_products')->where('seller_id',Auth::user()->id)->first();

        $data = [];
        if(isset($editData->WOOshipment_products) && count($editData->WOOshipment_products)>0){
            $product_shipped_quantity = [];
            foreach($editData->WOOshipment_products as $key=>$row)
            {

                $product_shipped_quantity[$key]['quantity'] = $row->quantity;

            }
        }
       }

        Session::put('itemArray',$data);
        return view('seller.wc_purchase_order.getCustomerOrderHistory', compact('shipmentWithProducts','orderDetails', 'shipping','product_price', 'shopDetails'));
    }


    public function getWCCustomerOrderHistoryForPack(Request $request)
    {

        $orderDetails = WooOrderPurchase::where('website_id', $request->shop_id)->where('order_id', $request->order_id)->where('seller_id',Auth::user()->id)->first();

        $countries = WooCountry::all();
        $states = WooState::all();

        $arr_country = array();
        if(!empty($countries)){
            foreach ($countries as $country){
                $arr_country[$country->code] = $country->name;
            }
        }

        $arr_state = array();
        if(!empty($states)){
                foreach ($states as $state){
                $arr_state[$state->code] = $state->name;
            }
        }

        $shipping = $this->formatCustomerAddress($isBulk='0',$orderDetails,$arr_country,$arr_state);
        $arrProductImageWithID = $this->getProductDetailsByOrderID($isBulk='0',$orderDetails,$request->shop_id);
        $orderProductDetails = json_decode($orderDetails->line_items);
         if(isset($request->shop_id)){
            $shopDetails = WooShop::where('woo_shops.id',$request->shop_id)
                            ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
                            ->first();
        }

        $data = [];
        $product_price = [];

        Session::put('itemArray',$data);
        return view('seller.wc_purchase_order.getCustomerOrderHistoryForPack', compact('orderDetails', 'shipping','product_price', 'shopDetails','orderProductDetails','arrProductImageWithID'));
    }


    public function getWCCustomerOrderHistoryForCustomShipment(Request $request)
    {

        $orderDetails = WooOrderPurchase::where('website_id', $request->shop_id)->where('order_id', $request->order_id)->where('seller_id',Auth::user()->id)->first();
        $countries = WooCountry::all();
        $states = WooState::all();

        $arr_country = array();
        if(!empty($countries)){
            foreach ($countries as $country){
                $arr_country[$country->code] = $country->name;
            }
        }

        $arr_state = array();
        if(!empty($states)){
                foreach ($states as $state){
                $arr_state[$state->code] = $state->name;
            }
        }

        $shipping = $this->formatCustomerAddress($isBulk='0',$orderDetails,$arr_country,$arr_state);

        $getShipmentsProductsDetails = ShipmentProduct::where('shipment_id',$request->shipment_id)
                                     ->leftJoin('woo_products', 'shipment_products.product_id', '=', 'woo_products.product_id')
                                     ->select('woo_products.*', 'shipment_products.quantity')
                                     ->get();



         if(isset($request->shop_id)){
            $shopDetails = WooShop::where('woo_shops.id',$request->shop_id)
                            ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
                            ->first();
        }

        $data = [];
        Session::put('itemArray',$data);
        return view('seller.wc_purchase_order.getWCCustomerOrderHistoryForCustomShipment', compact('orderDetails', 'shipping', 'shopDetails','getShipmentsProductsDetails'));
    }

    public function getWCCustomerOrderHistoryForPackAndCustomShipment(Request $request)
    {
        $orderDetails = WooOrderPurchase::where('website_id', $request->shop_id)->where('order_id', $request->order_id)->where('seller_id',Auth::user()->id)->first();

        $countries = WooCountry::all();
        $states = WooState::all();

        $arr_country = array();
        if(!empty($countries)){
            foreach ($countries as $country){
                $arr_country[$country->code] = $country->name;
            }
        }

        $arr_state = array();
        if(!empty($states)){
                foreach ($states as $state){
                $arr_state[$state->code] = $state->name;
            }
        }

        $shipping = $this->formatCustomerAddress($isBulk='0',$orderDetails,$arr_country,$arr_state);
        $arrProductImageWithID = $this->getProductDetailsByOrderID($isBulk='0',$orderDetails,$request->shop_id);
        $orderProductDetails = json_decode($orderDetails->line_items);
         if(isset($request->shop_id)){
            $shopDetails = WooShop::where('woo_shops.id',$request->shop_id)
                            ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
                            ->first();
        }

        $data = [];
        $product_price = [];

        Session::put('itemArray',$data);
        return view('seller.wc_purchase_order.getCustomerOrderHistoryForPack', compact('orderDetails', 'shipping','product_price', 'shopDetails','orderProductDetails','arrProductImageWithID'));
    }

    /**
     * Handle server-side datatable of order managements
     *
     * @param \App\Http\Requests\OrderManagement\DatatableRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function data(DatatableRequest $request)
    {
        $sellerId = Auth::user()->id;

        $wooPurchaseOrderTable = (new WooOrderPurchase())->getTable();
        $shipmentTable = (new Shipment())->getTable();
        $shopsTable = (new Shop())->getTable();

        $orderStatuses = $request->get('status', 0);
        $shopId = $request->get('shopId', 0);

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
            'id', 'id'
        ];

        $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
            ? $availableColumnsOrder[$orderColumnIndex]
            : $availableColumnsOrder[0];


            if($orderStatuses==Shipment::SHIPMENT_STATUS_READY_TO_SHIP
            || $orderStatuses==Shipment::SHIPMENT_STATUS_SHIPPED
            || $orderStatuses==Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED
            || $orderStatuses==Shipment::SHIPMENT_STATUS_SHIPPED
            || $orderStatuses==Shipment::SHIPMENT_STATUS_PENDING_STOCK
            || $orderStatuses==Shipment::SHIPMENT_STATUS_CANCEL ){

        $WooOrderPurchases = Shipment::selectRaw("{$shipmentTable}.*,
                                {$shopsTable}.name AS shop_name,
                                {$shopsTable}.logo AS shop_logo, {$wooPurchaseOrderTable}.order_date")
            ->where("{$shipmentTable}.seller_id", $sellerId)
            ->where("{$shipmentTable}.shipment_for", Shipment::SHIPMENT_FOR_WOO)
            ->where(function ($query) use ($shopId) {
                return $shopId ? $query->from("shipments}")->where('shipments.shop_id', $shopId) : '';
            })
            //->with('wooOrder')
            ->with('creator')
            ->with('packer')
            ->with('wooOrderPurchaseTable')
            ->byShipmentStatus($orderStatuses)
            ->joinedTable()
            ->searchDatatable($search)
            ->orderBy($orderColumnName, $orderDir)
            ->groupBy('shipments.id');

            return $this->shipmentsWithorderDataTable($WooOrderPurchases);


        }else{
            $WooOrderPurchases = WooOrderPurchase::selectRaw("{$wooPurchaseOrderTable}.*,
                                {$shopsTable}.name AS shop_name,
                                {$shopsTable}.logo AS shop_logo
                                ")
            ->where("{$wooPurchaseOrderTable}.seller_id", $sellerId)
            ->where(function ($query) use ($shopId) {
                return $shopId ? $query->from('woo_order_purchases')->where('website_id', $shopId) : '';
            })
            ->with('creator')
            ->with('shipment')
            ->byMultipleOrderStatus($orderStatuses)
            //->byOrderShipmentStatus($orderStatuses)
            ->joinedDatatable()
            ->searchDatatable($search)
            ->groupBy('woo_order_purchases.id')
            ->orderBy($orderColumnName, $orderDir);

            return $this->orderDataTable($WooOrderPurchases);

        }



    }


    private function shipmentsWithorderDataTable($WooOrderPurchases){

        return Datatables::of($WooOrderPurchases)
        ->addIndexColumn()
        ->addColumn('checkbox', function ($row) {
            $shop_id = $row->shop_id;
            return $shop_id . '*' . $row->id . '*' . $row->order_id;
        })
        ->addColumn('order_data', function ($row) {
            $currentShipment = Shipment::where('order_id', $row->order_id)->first();

            if(!empty($currentShipment->shipment_date)){
                $shipment_date = 'Shipment Date: <strong>'.date('d/m/Y',strtotime($currentShipment->shipment_date)).'</strong>';
                }
                else{
                    $shipment_date='';
                }

            $shipmentMethod = '';

            $currentOrder = WooOrderPurchase::where('order_id', $row->order_id)->where('website_id', $row->shop_id)->first();

            $shipping_lines = array();
            if (!empty($currentOrder->shipping_lines)) {
                $shipping_lines = json_decode($currentOrder->shipping_lines);
                $shipmentMethod = $shipping_lines[0]->method_title;
            }



            $customerName = '';
            if (!empty($currentOrder->billing)) {
            $customer = json_decode($currentOrder->billing);
            if ($customer)
                $customerName = $customer->first_name . " " . $customer->last_name;
            }

            //$channelImage = asset('img/No_Image_Available.jpg');
            $channelName = 'Woocommerce';
            $shannel = Channel::where('name', 'Woocommerce')->first();
            // if (!empty($shannel) && file_exists(public_path($shannel->image))) {
            //     $channelImage = asset($shannel->image);
            // }

            if (Storage::disk('s3')->exists($shannel->image) && !empty($shannel->image)) {
                $channelImage = Storage::disk('s3')->url($shannel->image);
            }
            else {
                $channelImage = Storage::disk('s3')->url('uploads/No-Image-Found.png');
            }

            $functionalBtns = '';


            $rowId = $row->order_id;

            $shop_id = $row->shop_id;
            if($row->is_custom==1){ $custom = " (Custom) ";}else{$custom='';}
            $rowId = $row->order_id . ' (Ship ID #' . $row->id . ')'.$custom;
            $shipmentStatus = Shipment::getShipmentStatusStr($row->shipment_status) ;


        $breakLine = '<span class="sm:hidden"><br></span>';

            $arrangeShipmentBtn = '';
            $confirmBtn = '';
            $updateShipmentStatusBtn = '';
            $UpdateOrderStatustBtn='';
            $markAsShippedBtn = '';
            $shipmentStatusBtns = '';
            $packageContent = '';
            $packageBtn = '';
            $printDate = '';
            $printLabelBtn = '';



                $website_id = $row->shop_id;
                $printLabelBtn = '<button type="button" class="btn-action--green mr-2" id="printLevel" shop-id="' . $website_id . '" order-id="' . $row->order_id . '" data-shipment_id="' . $row->id . '"><i class="fa fa-print mr-1" aria-hidden="true"></i> Print Label</button>';

                $pack_status = isset($row->pack_status) ? $row->pack_status : 0;
                $print_status = isset($row->print_status) ? $row->print_status : 0;

                if ($row->shipment_status == Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED && $print_status == 1){
                    $printDate = '<div class="mb-1 sm:mb-2">
                                            Printed At : ' . date('d-m-Y h:i A', strtotime($row->print_date_time)) . ' ( ' . $row->printer->name . ' )
                                    </div>
                                    ';
                }

                if ($pack_status == 1){
                    $packageContent = '<div class="mb-1 sm:mb-2">
                                                Pick Confirm At : ' . date('d-m-Y h:i A', strtotime($row->packed_date_time)) . ' ( ' . $row->packer->name . ' )
                                        </div>
                                        ' . $breakLine;
                }else{
                    $packageBtn = '<button type="button" class="btn-action--blue mr-2" title="Pack Order"
                                            data-order-id="' . $row->order_id . '"
                                            data-shop-id="' . $row->shop_id . '"
                                            data-shipment-id="' .$row->id . '"
                                            onClick="packOrder(this)">
                                            PICK CONFIRM
                                        </button>
                                        ' . $breakLine;
                }

                $updateShipmentStatusBtn = '<button type="button" class="btn-action--yellow mr-1" title="Update Status"
                                        data-order-id="' . $row->order_id . '"
                                        data-shop-id="' . $row->shop_id . '"
                                        data-shipment-id="' . $row->id . '"
                                        onClick="updateShipmentStatus(this)">
                                        Update status
                                    </button>
                                    ';

                if(isset($row->mark_as_shipped_status)>0){
                    $markAsShippedBtn = '<button type="button" class="shipment_btns mb-1 mt-1 btn btn-info btn-sm action_btns" id="markAsShipped" shop-id="'.$row->website_id.'" data-order-id="'.$row->order_id.'" data-id="'.$row->id.'">
                    <i class="fa fa-shipping-fast mr-1" aria-hidden="true"></i>Update</button>';
                }else{
                    $markAsShippedBtn = '<button type="button" class="btn btn-outline-success btn-sm" id="markAsShipped" shop-id="'.$row->website_id.'" data-order-id="'.$row->order_id.'" data-id="'.$row->shipment_row_id.'">
                                    <i class="fa fa-shipping-fast mr-1"></i>'.__("translation.Mark as Shipped").'</button>';
                }
                $shipmentStatusBtns = $packageBtn . $updateShipmentStatusBtn;



            $functionalBtns .=  $shipmentStatusBtns.$arrangeShipmentBtn;

            $order_status = isset($currentOrder->status) ? $currentOrder->status : '';

            $total = 0;
            $count = 0;
            $arrayTotal = ShipmentProduct::getArrayShipmentTotalByShopIdShipmentId($row->shop_id,$row->id);
            if(!empty($arrayTotal)){
                $total = array_sum($arrayTotal);
                $count = count($arrayTotal);
            }
            return '
                <div class="border border-solid border-gray-400 lg:border-gray-300 rounded-md hover:bg-blue-50">
                    <div class="border border-dashed border-t-0 border-r-0 border-l-0 border-gray-300">
                        <div class="grid grid-cols-3">
                            <div class="col-span-3 sm:col-span-1">
                                <div class="text-center px-2 py-1 sm:py-2">
                               <a href="'. route('wc-order-purchase-details', [ 'id' => $row->order_id,'shop_id' => $row->shop_id  ]) .'" data-id="'.$row->id.'" order-status-id="'.$order_status.'" class="cursor-pointer underline" title="Edit">
                                    <span class="font-bold text-gray-400">#</span>
                                    <span class="relative -left-1 text-blue-500 font-bold">
                                        '. $rowId .'
                                    </span>
                                </a>
                                </div>
                            </div>
                            <div class="col-span-3 sm:col-span-1">
                                <div class="px-2 py-1 sm:py-2">
                                    <span class="text-xs sm:text-sm" id="order_status_'. $row->order_id.'">
                                        '.strtoupper($shipmentStatus) .'
                                    </span>
                                </div>
                            </div>

                            <div class="col-span-3 sm:col-span-1">
                                        <div class="px-2 py-1 sm:py-2">
                                            '. $shipment_date .'

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
                                            <img src="'. $channelImage .'" alt="'. $channelName .'" title="'. $channelName .'" class="w-16 h-auto"/>
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
                                                <span class="text-gray-600">
                                                    Cust. Name :
                                                </span>'. $customerName .'
                                            </div>
                                            <div>
                                                <span class="text-gray-600">
                                                    Total Amount :
                                                </span> '. currency_symbol('THB') . number_format((float)$total, 2, '.', '').'
                                                ( <a style="cursor: pointer;" data-shipment-id="' .$row->id . '" data-shop-id = "' .$website_id . '" data-order-id="' . $row->order_id . '" class="modal-open" onClick="productsShipped(this)">' . $count .' Item/s</a> )
                                            </div>
                                            <div class="mb-3">
                                                <span class="text-gray-600">
                                                    Shipping Method :
                                                </span>
                                                <a style="cursor: pointer;" data-shop-id="'. $row->shop_id .'"  data-order-id="'. $row->order_id .'" id="BtnAddress" class="modal-open">' . $shipmentMethod .'</a><br />
                                                <span class="text-gray-600">
                                                    Payment Method : '. ucfirst($currentOrder->payment_method) .'
                                                </span>
                                            </div>

                                            <div class="text-left sm:text-left sm:block">
                                            '.$printDate .'
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:col-span-1">
                                <div class="border border-dashed border-r-0 border-b-0 border-l-0 border-gray-300">
                                    <div class="px-2 py-2 lg:text-left">
                                        <div class="text-xs sm:text-sm">
                                            <span class="text-gray-600">Order Date :</span> '. date('d/m/Y h:i a', strtotime($row->order_date)) .'
                                        </div>
                                        <div class="text-xs sm:text-sm">
                                            <span class="text-gray-600">Created By :</span> '. $row->creator->name .'
                                        </div>

                                        <div class="text-left sm:text-left sm:block">
                                        '. $packageContent .'
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-1">
                                <div class="border border-dashed border-r-0 border-b-0 border-l-0 border-gray-300">
                                    <div class="px-2 py-2 lg:text-left">
                                        <div class="text-left sm:text-right sm:block">
                                        '. $functionalBtns . $UpdateOrderStatustBtn . $printLabelBtn .'
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


    private function orderDataTable($WooOrderPurchases){

    return Datatables::of($WooOrderPurchases)
    ->addIndexColumn()
    ->addColumn('checkbox', function ($row) {
        $website_id = isset($row->shop_id) ? $row->shop_id : $row->website_id;
        $shipment_row_id = isset($row->shipment_row_id) ? $row->shipment_row_id:$row->id ;
        return $website_id . '*' . $row->shipment_row_id . '*' . $row->order_id;
    })
    ->addColumn('order_data', function ($row) {
        $shipmentMethod = '';
        $shipping_lines = json_decode($row->shipping_lines);
        if (!empty($shipping_lines)) {
            $shipmentMethod = $shipping_lines[0]->method_title;
        }
       // dd($row);
        $customerName = '';
        $customer = json_decode($row->billing);
        if ($customer)
            $customerName = $customer->first_name . " " . $customer->last_name;

        //$channelImage = asset('img/No_Image_Available.jpg');
        $channelName = 'Woocommerce';
        $shannel = Channel::where('name', 'Woocommerce')->first();
        // if (!empty($shannel) && file_exists(public_path($shannel->image))) {
        //     $channelImage = asset($shannel->image);
        // }

        if (Storage::disk('s3')->exists($shannel->image) && !empty($shannel->image)) {
                $channelImage = Storage::disk('s3')->url($shannel->image);
            }
            else {
                $channelImage = Storage::disk('s3')->url('uploads/No-Image-Found.png');
            }

        $functionalBtns = '';


        $rowId = $row->order_id;
        $orderStatus = $row->str_order_status;

        $website_id = $row->website_id;

            if($row->status==WooOrderPurchase::ORDER_STATUS_READY_TO_SHIP){
                $orderStatus = WooOrderPurchase::ORDER_STATUS_PROCESSED;
            }


 $breakLine = '<span class="sm:hidden"><br></span>';

        $arrangeShipmentBtn = '';
        $confirmBtn = '';
        $updateShipmentStatusBtn = '';
        $UpdateOrderStatustBtn='';
        $markAsShippedBtn = '';
        $shipmentStatusBtns = '';
        $packageContent = '';
        $packageBtn = '';
        $printDate = '';
        $printLabelBtn = '';





            if($row->status == WooOrderPurchase::ORDER_STATUS_PROCESSING) {
                $arrangeShipmentBtn = '  <button id="btn_arrange_shipment_'. $row->order_id .'" type="button" class="btn-action--green"
                                        data-order_id="' . $row->order_id . '"
                                        data-shipment_id="' . $row->id . '"
                                        data-website_id="' . $website_id . '"
                                        onClick="arrangeShipment(this)">
                                        '.__('translation.Arrange Shipment').'
                                    </button>';
            }


            $UpdateOrderStatustBtn = '
            <button type="button" class="modal-open btn-action--green BtnUpdateStatus" title="Update Status" id="BtnUpdateStatus"
                data-id="'. $row->id .'"
                data-order_id="'. $row->id .'">
                <i class="fas fa-pencil-alt"></i>
                <span class="ml-2 hidden lg:inline">Update</span>
            </button>
            ';

         /*   $UpdateOrderStatustBtn = ' <button type="button" class="btn-action--red BtnDelete" title="Delete"
            data-id="'. $row->id .'"
            data-order_id="'. $row->id .'">
            <i class="fas fa-trash"></i>
            <span class="ml-2 hidden lg:inline">Delete</span>
        </button>

        <button id="btn_arrange_shipment_'. $row->order_id .'" type="button" class="btn-action--green mr-2"
            data-order-id="' . $row->order_id . '"
            onClick="updateOrderStatus(this)">
            <i class="fas fa-pencil-alt"></i>
            <span class="ml-2 hidden lg:inline">Update Status</span>
        </button>';
        */



        $functionalBtns .=  $shipmentStatusBtns.$arrangeShipmentBtn;



        return '
            <div class="border border-solid border-gray-400 lg:border-gray-300 rounded-md hover:bg-blue-50">
                <div class="border border-dashed border-t-0 border-r-0 border-l-0 border-gray-300">
                    <div class="grid grid-cols-3">
                        <div class="col-span-3 sm:col-span-1">
                            <div class="text-center px-2 py-1 sm:py-2">
                           <a href="'. route('wc-order-purchase-details', [ 'id' => $row->order_id,'shop_id' => $row->website_id  ]) .'" data-id="'.$row->id.'" order-status-id="'.$row->status.'" class="cursor-pointer underline" title="Edit">
                                <span class="font-bold text-gray-400">#</span>
                                <span class="relative -left-1 text-blue-500 font-bold">
                                    '. $rowId .'
                                </span>
                            </a>
                            </div>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <div class="px-2 py-1 sm:py-2">
                                <span class="text-xs sm:text-sm" id="order_status_'. $row->order_id.'">
                                    '.strtoupper($orderStatus) .'
                                </span>
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
                                        <img src="'. $channelImage .'" alt="'. $channelName .'" title="'. $channelName .'" class="w-16 h-auto"/>
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
                                            <span class="text-gray-600">
                                                Cust. Name :
                                            </span>'. $customerName .'
                                        </div>
                                        <div>
                                            <span class="text-gray-600">
                                                Total Amount :
                                            </span> '. currency_symbol('THB') . number_format((float)$row->total, 2, '.', '').'
                                            ( <a style="cursor: pointer;" data-shop-id = "' .$website_id . '" data-order-id="' . $row->order_id . '" class="modal-open" onClick="productsOrder(this)">' . count(json_decode($row->line_items)) .' Item/s</a> )
                                        </div>
                                        <div class="mb-3">
                                            <span class="text-gray-600">
                                                Shipping Method :
                                            </span>
                                            <a style="cursor: pointer;" data-shop-id="'. $website_id .'"  data-order-id="'. $row->order_id .'"  id="BtnAddress" class="modal-open">' . $shipmentMethod .'</a><br />
                                            <span class="text-gray-600">
                                                    Payment Method : '. ucfirst($row->payment_method) .'
                                            </span>
                                        </div>

                                        <div class="text-left sm:text-left sm:block">
                                        '.$printDate .'
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-1">
                            <div class="border border-dashed border-r-0 border-b-0 border-l-0 border-gray-300">
                                <div class="px-2 py-2 lg:text-left">
                                    <div class="text-xs sm:text-sm">
                                        <span class="text-gray-600">Order Date :</span> '. date('d/m/Y h:i a', strtotime($row->order_date)) .'
                                    </div>
                                    <div class="text-xs sm:text-sm">
                                        <span class="text-gray-600">Created By :</span> '. $row->creator->name .'
                                    </div>

                                    <div class="text-left sm:text-left sm:block">
                                    '. $packageContent .'
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-1">
                            <div class="border border-dashed border-r-0 border-b-0 border-l-0 border-gray-300">
                                <div class="px-2 py-2 lg:text-left">
                                    <div class="text-left sm:text-right sm:block">
                                    '. $functionalBtns . $UpdateOrderStatustBtn . $printLabelBtn .'
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
     * Update Order status
     *
     */
    public function updateWCOrderStatus(Request $request)
    {
        try {
            $order_id = $request->orderId;

            $WooOrder = WooOrderPurchase::where('order_id', $order_id)->first();
            $WooOrder->status = $request->orderStatus;
            $WooOrder->update();

            if (in_array(strtolower($request->status), [
                WooOrderPurchase::ORDER_STATUS_PROCESSING,
                // WooOrderPurchase::ORDER_STATUS_ON_HOLD,
                // WooOrderPurchase::ORDER_STATUS_PENDING,
            ])) {
                /* Update inventory quantity */
                if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($order_id, $WooOrder->website_id, $this->getTagForWooCommercePlatform())) {
                    $this->initInventoryQtyUpdateForWooCommerce($WooOrder);
                }
            } else {
                /**
                 * Update "display_reserved_qty" for the dodo products in this order.
                 * NOTE:
                 * This will be triggered for any other status/status_custom other than "processing".
                 */
                if ($this->checkIfDisplayReservedQtyShouldBeUpdated($order_id, $WooOrder->website_id, $this->getTagForWooCommercePlatform())) {
                    AdjustDisplayReservedQty::dispatch($order_id, $WooOrder->website_id, $this->getTagForWooCommercePlatform())->delay(Carbon::now()->addSeconds(2));
                }
            }

            return $this->apiResponse(Response::HTTP_OK, 'Data successfully updated.');
        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Sorry, something went wrong. ' . $th->getMessage());
        }
    }



    public function orderPurchaseDetails($id,$shop_id){

        $sellerId = Auth::user()->id;
        $orderManagement = WooOrderPurchase::where('order_id',$id)->where('website_id',$shop_id)->where('seller_id',Auth::user()->id)->first();
        $orderProductDetails = json_decode($orderManagement->line_items);
        $shipping_lines = json_decode($orderManagement->shipping_lines);
        $userDetails = User::where('id',Auth::user()->id)->first();
        if(isset($shop_id)){
            $shopDetails =  WooShop::where('woo_shops.id',$shop_id)
            ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
            ->first();
        }
        $data = [];



        Session::put('itemArray',$data);

        $countries = WooCountry::all();
        $states = WooState::all();

        $arr_country = array();
        if(!empty($countries)){
            foreach ($countries as $country){
                $arr_country[$country->code] = $country->name;
            }
        }

        $arr_state = array();
        if(!empty($states)){
                foreach ($states as $state){
                $arr_state[$state->code] = $state->name;
            }
        }

        $shipping = $this->formatCustomerAddress($isBulk='0',$orderManagement,$arr_country,$arr_state);
        $arrProductImageWithID = $this->getProductDetailsByOrderID($isBulk='0',$orderManagement,$shop_id);
        //dd($arrProductImageWithID);
        $data = [
            'orderManagement' => $orderManagement,
            'orderProductDetails' => $orderProductDetails,
            'arrProductImageWithID' => $arrProductImageWithID,
            'shipping' => $shipping,
            'shipping_lines' => $shipping_lines,
            'shopDetails' => $shopDetails,
            'shop_id' => $shop_id

        ];
        return view('seller.wc_purchase_order.show_order_datails', $data);
    }


    /**
     * @WOO Go through the statements, remove unused or commented codebase
     * There duplicate calls of OrderManagement
     * @WOO collect OrderManagement with common query and query on the collection
     */
    public function getWCShipmentDetailsData(Request $request){
        $order_id = $request->order_id;
        $website_id = $request->website_id;
        $getAllOredredDetails = WooOrderPurchase::where('website_id',$website_id)->where('order_id',$order_id)->where('seller_id',Auth::user()->id)->first();
        $allShipments = DB::table('shipments')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.is_custom', '=', 0)
                    ->where('shipments.shop_id', '=', $website_id)
                    ->where('shipments.shipment_for', '=', Shipment::SHIPMENT_FOR_WOO)
                    ->where('shipments.order_id', '=', $request->order_id)
                    ->get();


            $ordered_products = 0;
            $orderProductDetails = json_decode($getAllOredredDetails['line_items']);
            if(!empty($orderProductDetails)){
                foreach ($orderProductDetails as $product) {
                    $ordered_products += $product->quantity;
                }
            }

        $total_shipped_products = ShipmentProduct::where('shipment_products.order_id',$order_id)
        ->where('shipments.shipment_for',Shipment::SHIPMENT_FOR_WOO)
        ->where('shipments.is_custom',0)
        ->where('shipments.shop_id', '=', $website_id)
        ->leftJoin('shipments', 'shipments.id', '=', 'shipment_products.shipment_id')
        ->sum('shipment_products.quantity');

        $remaining_ship_quantity =  $ordered_products - $total_shipped_products;



        if (!empty($getAllOredredDetails)){
            $getCustomerDetails = json_decode($getAllOredredDetails['shipping']);
        }

        if (!empty($getAllOredredDetails)){
            $getProductDetails = json_decode($getAllOredredDetails['line_items']);
        }

        if (!empty($getAllOredredDetails)){
            $shipping_lines = json_decode($getAllOredredDetails['shipping_lines']);
        }

        if (!empty($shipping_lines)){
            $shippingMethod = $shipping_lines[0]->method_title;
        }

        return view('seller.wc_purchase_order.shipmentDetails', compact('getAllOredredDetails','website_id','order_id', 'getCustomerDetails','getProductDetails', 'allShipments', 'shippingMethod','remaining_ship_quantity'));
    }


        /**
     * @WOO Go through the statements, remove unused or commented codebase
     * There duplicate calls of OrderManagement
     * @WOO collect OrderManagement with common query and query on the collection
     */
    public function getWCCustomShipmentDetailsData(Request $request){
        $website_id = $request->website_id;
        $order_id = $request->order_id;


        $getAllOredredDetails = WooOrderPurchase::where('website_id',$website_id)->where('order_id',$order_id)->where('seller_id',Auth::user()->id)->first();
        $allShipments = Shipment::with('printer')
                    ->with('shipper')
                    ->with('packer')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.is_custom', '=', 1)
                    ->where('shipments.shipment_for', '=', Shipment::SHIPMENT_FOR_WOO)
                    ->where('shipments.order_id', '=', $request->order_id)
                    ->where('shipments.shop_id', '=', $request->website_id)
                    ->get();
        //dd($allShipments);

       // if($row->print_status == 1){
          //  $print_time = Carbon::parse($row->print_date_time)->format('d-M-Y h:i a');
//$userDetails = User::select('name')->where('id',$row->print_by)->first();
            //return '<strong>Printed by</strong> : <br>'.$userDetails->name.'<br><strong>'.$print_time.'</strong>';
       // }

        if (!empty($getAllOredredDetails)){
            $getCustomerDetails = json_decode($getAllOredredDetails['shipping']);
        }

        if (!empty($getAllOredredDetails)){
            $getProductDetails = json_decode($getAllOredredDetails['line_items']);
        }
        //dd($allShipments);
        if (!empty($getAllOredredDetails)){
            $shipping_lines = json_decode($getAllOredredDetails['shipping_lines']);
        }

        if (!empty($shipping_lines)){
            $shippingMethod = $shipping_lines[0]->method_title;
        }

        return view('seller.wc_purchase_order.customShipmentDetails', compact('website_id','getAllOredredDetails','order_id', 'getCustomerDetails','getProductDetails', 'allShipments', 'shippingMethod'));
    }

    public function getModalContentForWCCustomShipment(Request $request){
        $order_id = $request->orderId;
        $website_id = $request->website_id;


        $products = WooProduct::where('website_id',$website_id)->where('seller_id',Auth::user()->id)->get();
        return view('seller.wc_purchase_order.getModalContentForCustomShipment', compact('website_id','order_id', 'products'));
    }

    public function getWOOProductDetails(Request $request){
        $order_id = $request->orderId;
        $product_id = $request->product_id;
        $website_id = $request->website_id;

        $product = WooProduct::where('website_id',$request->website_id)
        ->where('product_id',$request->product_id)
        ->where('seller_id',Auth::user()->id)->first();
        $shipped_qty = '';
        $product_price = '';
        if(isset($product)){

            $getShippedQty = ShipmentProduct::where('order_id', $request->orderId)
            ->where('product_id', $request->product_id)
            ->sum('quantity');

            if(!empty($getShippedQty)){
                $shipped_qty = $getShippedQty;
            }
            else{
                $shipped_qty = 0;
            }

        }

        return view('seller.wc_purchase_order.orderedProductedDetails', compact('order_id', 'product', 'shipped_qty', 'product_price'));
    }


    /**
     * Print the pdf of quotation For WOO Orders
     *
     * @param  int  $orderId
     * @return mixed
     */
    public function printWCQuotationPdf($orderId,$website_id)
    {
        $sellerId = Auth::user()->id;

        $orderManagement = WooOrderPurchase::where('website_id',$website_id)->where('order_id',$orderId)->where('seller_id',Auth::user()->id)->first();
        if(isset($website_id)){
            $shopDetails =  WooShop::where('woo_shops.id',$website_id)
            ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
            ->first();
        }

        $allShipments = Shipment::with('printer')
                    ->with('shipper')
                    ->with('packer')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.is_custom', '=', 1)
                    ->where('shipments.shipment_for', '=', Shipment::SHIPMENT_FOR_WOO)
                    ->where('shipments.order_id', '=', $orderId)
                    ->get();

        $countries = WooCountry::all();
        $states = WooState::all();

        $arr_country = array();
        if(!empty($countries)){
            foreach ($countries as $country){
                $arr_country[$country->code] = $country->name;
            }
        }

        $arr_state = array();
        if(!empty($states)){
                foreach ($states as $state){
                $arr_state[$state->code] = $state->name;
            }
        }
        $orderProductDetails = json_decode($orderManagement->line_items);
        $shipping_lines = json_decode($orderManagement->shipping_lines);

        $shipping = $this->formatCustomerAddress($isBulk='0',$orderManagement,$arr_country,$arr_state);
        $arrProductImageWithID = $this->getProductDetailsByOrderID($isBulk='0',$orderManagement,$website_id);


       // dd($orderProductDetails);
        abort_if(!$orderManagement, Response::HTTP_NOT_FOUND, 'Data not found');

        $data = [
            'orderManagement' => $orderManagement,
            'shopDetails' => $shopDetails,
            'orderProductDetails' => $orderProductDetails,
            'arrProductImageWithID' => $arrProductImageWithID,
            'shipping' => $shipping,
            'taxRateSetting' => TaxRateSetting::where('seller_id', $sellerId)->first(),
            'taxEnableYes' => OrderManagement::TAX_ENABLE_YES
        ];

        $quotationPdf = PDF::loadView('pdf.wc-order-quotation', $data);
        $quotationPdf->setPaper('A4', 'portrait');

        $pdfFileName = 'wc_order_quotation_' . $orderManagement->order_id . '.pdf';

        return $quotationPdf->download($pdfFileName);
    }


        /**
     * Print the pdf of quotation For WOO Orders
     *
     * @param  int  $orderId
     * @return mixed
     */
    public function printWCinvoicePdf($orderId,$website_id)
    {
        $sellerId = Auth::user()->id;

        $orderManagement = WooOrderPurchase::where('website_id',$website_id)->where('order_id',$orderId)->where('seller_id',Auth::user()->id)->first();
        if(isset($website_id)){
            $shopDetails =  WooShop::where('woo_shops.id',$website_id)
            ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
            ->first();
        }

        $allShipments = Shipment::with('printer')
                    ->with('shipper')
                    ->with('packer')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.is_custom', '=', 1)
                    ->where('shipments.shipment_for', '=', Shipment::SHIPMENT_FOR_WOO)
                    ->where('shipments.order_id', '=', $orderId)
                    ->get();

        $countries = WooCountry::all();
        $states = WooState::all();

        $arr_country = array();
        if(!empty($countries)){
            foreach ($countries as $country){
                $arr_country[$country->code] = $country->name;
            }
        }

        $arr_state = array();
        if(!empty($states)){
                foreach ($states as $state){
                $arr_state[$state->code] = $state->name;
            }
        }
        $orderProductDetails = json_decode($orderManagement->line_items);
        $shipping_lines = json_decode($orderManagement->shipping_lines);

        $billing = json_decode($orderManagement->billing);

        /* This is for "WooCommerce" */
        $arr_billing_details = array();
        $arr_billing_details['billing_name'] = $billing->first_name." ".$billing->last_name;
        $arr_billing_details['billing_email'] = isset($shipping->email) ? $billing->email : $billing->email;
        $arr_billing_details['billing_phone'] = isset($billing->phone) ? $billing->phone : $billing->phone;
        $arr_billing_details['billing_company'] = $billing->company;
        $arr_billing_details['billing_address_1'] = $billing->address_1;
        $arr_billing_details['billing_address_2'] = $billing->address_2;
        $arr_billing_details['billing_city'] = $billing->city;
        $arr_billing_details['billing_state'] = $arr_state[$billing->state];
        $arr_billing_details['billing_postcode'] = $billing->postcode;
        $arr_billing_details['billing_country'] = $arr_country[$billing->country];

        $arrProductImageWithID = $this->getProductDetailsByOrderID($isBulk='0',$orderManagement,$website_id);

        abort_if(!$orderManagement, Response::HTTP_NOT_FOUND, 'Data not found');

        $data = [
            'orderManagement' => $orderManagement,
            'shopDetails' => $shopDetails,
            'orderProductDetails' => $orderProductDetails,
            'arrProductImageWithID' => $arrProductImageWithID,
            'billing_details' => $arr_billing_details,
            'taxRateSetting' => TaxRateSetting::where('seller_id', $sellerId)->first(),
            'taxEnableYes' => OrderManagement::TAX_ENABLE_YES
        ];

        $quotationPdf = PDF::loadView('pdf.wc-order-invoice', $data);
        $quotationPdf->setPaper('A4', 'portrait');

        $pdfFileName = 'wc_order_invoice_' . $orderManagement->order_id . '.pdf';

        return $quotationPdf->download($pdfFileName);
    }


    public function getProducts(Request $request)
    {
        $sellerId = Auth::user()->id;

        $website_id = $request->get('website_id', '');
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $limit = 20;

        $offset = ($page - 1) * $limit;

        $products = WooProduct::where('seller_id', $sellerId)
                        ->where('website_id', $website_id)
                        ->take($limit)
                        ->skip($offset)
                        ->orderBy('id', 'asc')
                        ->get();

        $productsCount = WooProduct::where('seller_id', $sellerId)
        ->where('website_id',$website_id)
        ->count();

        if ($page == 1) {
            $allProductObject = new WooProduct();
            $allProductObject->id = 0;
            $allProductObject->product_name = '- All Products - ';
            //$products->prepend($allProductObject);
        }

        return response()->json([
            'results' => WCProductSelectTwoResource::collection($products),
            'pagination' => [
                'more' => ($page * $limit ) < $productsCount
            ]
        ]);
    }

    public function getModalContentForEditWCCustomShipment(Request $request){
        $order_id = $request->orderId;
        $website_id = $request->website_id;

        $editData = WooOrderPurchase::where('website_id',$website_id)->where('order_id',$order_id)->where('seller_id',Auth::user()->id)->first();
        $shipmentDetails = Shipment::where('id',$request->shipment_id)->first();
        $getShipmentsProductsDetails = ShipmentProduct::where('shipment_id',$request->shipment_id)
                                     ->leftJoin('woo_products', 'shipment_products.product_id', '=', 'woo_products.product_id')
                                     ->select('woo_products.*', 'shipment_products.quantity')
                                     ->where('woo_products.website_id', $website_id)
                                     ->get();
        //dd($getShipmentsProductsDetails);
        if($request->use_for == 'edit'){
         return view('seller.wc_purchase_order.getModalContentForEditCustomShipment', compact('website_id','order_id', 'editData', 'shipmentDetails', 'getShipmentsProductsDetails'));
        }

        if($request->use_for == 'view'){
        return view('seller.wc_purchase_order.getModalContentForViewCustomShipment', compact('website_id','order_id', 'editData', 'shipmentDetails', 'getShipmentsProductsDetails'));
        }


    }


    public function storeForWCCustomShipment(StoreRequest $request, WCCustomShipmentActionForOrder $WCcustomShipmentActionForOrder)
    {

        $sellerId = Auth::user()->id;
        if(isset($request->shipment_id)){
            $shipment_id = $request->shipment_id;
        }
        else{
            $shipment_id = '';
        }



        $shipmentData = [
            'shipment_date' => $request->shipment_date,
            'order_id' => $request->order_id,
            'seller_id' => $sellerId,
            'shipment_status' => $request->shipment_status,
            'product_id' => $request->product_id,
            'shipment_qty' => $request->shipment_qty,
            'is_custom' => 1,
            'shipment_for' => Shipment::SHIPMENT_FOR_WOO,
            'shop_id' => $request->shop_id
        ];

        $WCcustomShipmentActionForOrder->handle($shipmentData);

        return $this->apiResponse(Response::HTTP_CREATED, __('translation.New Custom Shipment successfully updated'));
    }


     public function updateForWCCustomShipment(StoreRequest $request, UpdateWCCustomShipmentActionForOrder $updateWCcustomShipmentActionForOrder)
    {

        $sellerId = Auth::user()->id;
        $shipmentData = [
            'shipment_id'=>$request->shipment_id,
            'shipment_date' => $request->shipment_date,
            'order_id' => $request->order_id,
            'seller_id' => $sellerId,
            'shipment_status' => $request->shipment_status,
            'product_id' => $request->product_id,
            'shipment_qty' => $request->shipment_qty,
        ];

        $updateWCcustomShipmentActionForOrder->handle($shipmentData);

        return $this->apiResponse(Response::HTTP_CREATED, __('translation.New Custom Shipment successfully updated'));
    }



    public function arrangeShipment(Request $request){


        $website_id = $request->website_id;
        $order_id = $request->order_id;
        $sellerId = Auth::user()->id;
        $orderManagement = WooOrderPurchase::where('website_id',$website_id)->where('order_id',$order_id)->where('seller_id',Auth::user()->id)->first();
        $orderProductDetails = json_decode($orderManagement->line_items);

        $shipping_lines = json_decode($orderManagement->shipping_lines);
        $userDetails = User::where('id',Auth::user()->id)->first();
        if(isset($website_id)){
            $shopDetails =  WooShop::where('woo_shops.id',$website_id)
            ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
            ->first();
        }
        $data = [];

        Session::put('itemArray',$data);

        $countries = WooCountry::all();
        $states = WooState::all();

        $arr_country = array();
        if(!empty($countries)){
            foreach ($countries as $country){
                $arr_country[$country->code] = $country->name;
            }
        }

        $arr_state = array();
        if(!empty($states)){
                foreach ($states as $state){
                $arr_state[$state->code] = $state->name;
            }
        }

        $shipping = $this->formatCustomerAddress($isBulk='0',$orderManagement,$arr_country,$arr_state);
        $arrProductImageWithID = $this->getProductDetailsByOrderID($isBulk='0',$orderManagement,$website_id);
        $arrProductWiseTotalShippedQty = $this->getProductWiseTotalShippedQty($website_id,$order_id);

        $statusMainSchema = WooOrderPurchase::getMainStatusSchemaForDatatable();

        $firstStatusOrderId = array_column($statusMainSchema[0]['sub_status'], 'id')[0];
        $data = [
            'firstStatusOrderId' =>$firstStatusOrderId,
            'disable_edit_quantity' =>$request->disable_edit_quantity,
            'orderManagement' => $orderManagement,
            'orderProductDetails' => $orderProductDetails,
            'arrProductImageWithID' => $arrProductImageWithID,
            'shipping' => $shipping,
            'shipping_lines' => $shipping_lines,
            'shopDetails' => $shopDetails,
            'arrProductWiseTotalShippedQty' => $arrProductWiseTotalShippedQty
        ];
        return view('seller.wc_purchase_order.arrangeShipment', $data);
    }



    public function getAllWCOrderedProForOrder(Request $request){



        $id = $request->orderId;
        $website_id = $request->website_id;

        $sellerId = Auth::user()->id;
        $orderManagement = WooOrderPurchase::where('website_id',$website_id)->where('order_id',$id)->where('seller_id',Auth::user()->id)->first();
        $orderProductDetails = json_decode($orderManagement->line_items);

        $shipping_lines = json_decode($orderManagement->shipping_lines);
        $userDetails = User::where('id',Auth::user()->id)->first();
        if(isset($website_id)){
            $shopDetails =  WooShop::where('woo_shops.id',$website_id)
            ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
            ->first();
        }
        $data = [];

        Session::put('itemArray',$data);

        $countries = WooCountry::all();
        $states = WooState::all();

        $arr_country = array();
        if(!empty($countries)){
            foreach ($countries as $country){
                $arr_country[$country->code] = $country->name;
            }
        }

        $arr_state = array();
        if(!empty($states)){
                foreach ($states as $state){
                $arr_state[$state->code] = $state->name;
            }
        }

        $shipping = $this->formatCustomerAddress($isBulk='0',$orderManagement,$arr_country,$arr_state);
        $arrProductImageWithID = $this->getProductDetailsByOrderID($isBulk='0',$orderManagement,$website_id);
        $arrProductWiseTotalShippedQty = $this->getProductWiseTotalShippedQty($website_id,$request->orderId);
        $statusMainSchema = WooOrderPurchase::getMainStatusSchemaForDatatable();

        $firstStatusOrderId = array_column($statusMainSchema[0]['sub_status'], 'id')[0];
        $data = [
            'firstStatusOrderId' =>$firstStatusOrderId,
            'firstStatusOrderId' =>$firstStatusOrderId,
            'disable_edit_quantity' =>$request->disable_edit_quantity,
            'orderManagement' => $orderManagement,
            'orderProductDetails' => $orderProductDetails,
            'arrProductImageWithID' => $arrProductImageWithID,
            'shipping' => $shipping,
            'shipping_lines' => $shipping_lines,
            'shopDetails' => $shopDetails,
            'arrProductWiseTotalShippedQty' => $arrProductWiseTotalShippedQty,
            'website_id'=>$website_id
        ];
        return view('seller.wc_purchase_order.getAllOrderedProForOrder', $data);
    }



    public function getAllWCOrderedProForOrderEdit(Request $request){

        $orderManagement = WooOrderPurchase::where('website_id',$request->shop_id)->where('order_id',$request->orderId)->where('seller_id',Auth::user()->id)->first();

        $orderProductDetails = json_decode($orderManagement->line_items);
        $shipping_lines = json_decode($orderManagement->shipping_lines);
        $arrTotalOrderedQty = array();
        if(isset($orderProductDetails) && count($orderProductDetails)>0){
            foreach($orderProductDetails as $product){
                $arrTotalOrderedQty[] = $product->quantity;
            }
        }

        $totalOrderedQty = array_sum($arrTotalOrderedQty);

        $arrProductImageWithID = $this->getProductDetailsByOrderID($isBulk='0',$orderManagement,$request->shop_id);
        $arrProductWiseTotalShippedQty = $this->getProductWiseTotalShippedQty($request->shop_id,$request->orderId);
        //dd($orderProductDetails);


        $shipmentDetails = Shipment::where('id',$request->shipment_id)->first();

        $data = [];
        $product_price = [];
        $shipmentQtyPerPro = [];

        if(isset($orderProductDetails) && count($orderProductDetails)>0){

            foreach($orderProductDetails as $key=>$row)
            {

                if(empty($row->discount_price) || $row->discount_price == NULL){
                    $product_price[$key]['price'] = $row->price;
                }
                else{
                    $product_price[$key]['price'] = $row->discount_price;
                }

                $getShippedQty = ShipmentProduct::where('order_id', $request->orderId)
                ->where('product_id', $row->product_id)
                ->sum('quantity');

                if(!empty($getShippedQty)){
                    $product_price[$key]['shipped_qty'] = $getShippedQty;
                }
                else{
                    $product_price[$key]['shipped_qty'] = 0;
                }

                $shipmentQtyPerProduct = ShipmentProduct::where('order_id', $request->orderId)
                ->where('product_id', $row->product_id)
                ->where('shipment_id', $request->shipment_id)
                ->first();

                if(!empty($shipmentQtyPerProduct->quantity)){
                    $shipmentQtyPerPro[$key]['shipment_qty'] = $shipmentQtyPerProduct->quantity;
                }
                else{
                    $shipmentQtyPerPro[$key]['shipment_qty'] = 0;
                }
             }
        }

        $data = [
            'orderManagement' => $orderManagement,
            'orderProductDetails' => $orderProductDetails,
            'arrProductImageWithID' => $arrProductImageWithID,
            'product_price' => $product_price,
            'totalOrderedQty' => $totalOrderedQty,
            'shipmentQtyPerPro' => $shipmentQtyPerPro,
            'shipmentDetails' => $shipmentDetails,
            'arrProductWiseTotalShippedQty' => $arrProductWiseTotalShippedQty
        ];

        if($request->use_for=='view'){
            return view('seller.wc_purchase_order.getAllOrderedProForOrderView', $data);
        }else{
            return view('seller.wc_purchase_order.getAllOrderedProForOrderEdit', $data);
        }

    }






    public function getAllWCOrderedProductForOrShipment(Request $request){



        $id = $request->orderId;
        $website_id = $request->website_id;

        $sellerId = Auth::user()->id;
        $orderManagement = WooOrderPurchase::where('website_id',$website_id)->where('order_id',$id)->where('seller_id',Auth::user()->id)->first();
        $orderProductDetails = json_decode($orderManagement->line_items);

        $shipping_lines = json_decode($orderManagement->shipping_lines);
        $userDetails = User::where('id',Auth::user()->id)->first();
        if(isset($website_id)){
            $shopDetails =  WooShop::where('woo_shops.id',$website_id)
            ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
            ->first();
        }
        $data = [];

        Session::put('itemArray',$data);

        $countries = WooCountry::all();
        $states = WooState::all();

        $arr_country = array();
        if(!empty($countries)){
            foreach ($countries as $country){
                $arr_country[$country->code] = $country->name;
            }
        }

        $arr_state = array();
        if(!empty($states)){
                foreach ($states as $state){
                $arr_state[$state->code] = $state->name;
            }
        }

        $shipping = $this->formatCustomerAddress($isBulk='0',$orderManagement,$arr_country,$arr_state);
        $arrProductImageWithID = $this->getProductDetailsByOrderID($isBulk='0',$orderManagement,$website_id);
        $arrProductWiseTotalShippedQty = $this->getProductWiseTotalShippedQty($website_id,$request->orderId);
        $statusMainSchema = WooOrderPurchase::getMainStatusSchemaForDatatable();

        $firstStatusOrderId = array_column($statusMainSchema[0]['sub_status'], 'id')[0];
        $data = [
            'firstStatusOrderId' =>$firstStatusOrderId,
            'disable_edit_quantity' =>$request->disable_edit_quantity,
            'orderManagement' => $orderManagement,
            'orderProductDetails' => $orderProductDetails,
            'arrProductImageWithID' => $arrProductImageWithID,
            'shipping' => $shipping,
            'shipping_lines' => $shipping_lines,
            'shopDetails' => $shopDetails,
            'arrProductWiseTotalShippedQty' => $arrProductWiseTotalShippedQty,
            'website_id'=>$website_id
        ];
        return view('seller.wc_purchase_order.getAllOrderedProForOrder', $data);
    }

    public function deleteWCShipmentForOrder(Request $request){
        $shipment = Shipment::where('id', $request->shipment_id)->where('seller_id', Auth::user()->id)->first();

        if($shipment) {
            $order = WooOrderPurchase::where('website_id',$shipment->shop_id)->where('order_id',$shipment->order_id)->where('seller_id',Auth::user()->id)->first();
            $order->status = WooOrderPurchase::ORDER_STATUS_PROCESSING;
            $order->update();

            $result = $shipment->delete();
            if ($result) {
                ShipmentProduct::where('shipment_id', $request->shipment_id)->delete();
            }

            return $shipment->shop_id;
        }
    }

    public function WCprintLabelPrint(Request $request){
        $shipment_id = $request->shipment_id_input_val;
        $shop_id = $request->shop_id_input_val;
        $orderDetails = WooOrderPurchase::where('website_id',$request->shop_id_input_val)->where('order_id',$request->order_id_input_val)->where('seller_id',Auth::user()->id)->first();
        $orderProductDetails = json_decode($orderDetails->line_items);
        $userDetails = User::where('id',Auth::user()->id)->first();
        if(isset($request->shop_id_input_val)){
            $shopDetails =  WooShop::where('woo_shops.id',$request->shop_id_input_val)
            ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
            ->first();
        }


        $channelName = '';
        if(isset($orderDetails->channel_id)){
            $channelDetails = Channel::where('id',$orderDetails->channel_id)->first();
            if(isset($channelDetails->name)){
            $channelName = $channelDetails->name;
        }
            else{
                $channelName = '';
            }
        }

        $shipmentMethod = '';
        $shipping_lines = json_decode($orderDetails->shipping_lines);
        if (!empty($shipping_lines)) {
            $shipmentMethod = $shipping_lines[0]->method_title;
        }
        $data = [];


        Session::put('itemArray',$data);

        $countries = WooCountry::all();
        $states = WooState::all();

        $arr_country = array();
        if(!empty($countries)){
            foreach ($countries as $country){
                $arr_country[$country->code] = $country->name;
            }
        }

        $arr_state = array();
        if(!empty($states)){
                foreach ($states as $state){
                $arr_state[$state->code] = $state->name;
            }
        }

        $shipping = $this->formatCustomerAddress($isBulk='0',$orderDetails,$arr_country,$arr_state);
        $arrProductImageWithID = $this->getProductDetailsByOrderID($isBulk='0',$orderDetails,$request->shop_id_input_val);

        if($shipment_id){
            $getShipmentsProductsDetails = ShipmentProduct::where('shipment_id',$shipment_id)
                            ->leftJoin('woo_products', 'shipment_products.product_id', '=', 'woo_products.product_id')
                            ->select('woo_products.*', 'shipment_products.quantity as shipped_qty')
                            ->get();
        }

    //return view('seller.wc_purchase_order.order_print_level', compact('shop_id','orderDetails','shipping', 'orderProductDetails','userDetails', 'shipment_id', 'shopDetails','arrProductImageWithID', 'channelName', 'shipmentMethod', 'getShipmentsProductsDetails'));
    $pdf = PDF::loadView('seller.wc_purchase_order.order_print_level', compact('shop_id','orderDetails','shipping', 'orderProductDetails','userDetails', 'shipment_id', 'shopDetails','arrProductImageWithID', 'channelName', 'shipmentMethod', 'getShipmentsProductsDetails'));

        //$pdf->setPaper('A4', 'portrait');
        if($pdf){
            $shipments = Shipment::where('id',$shipment_id)->first();
            if($shipments){
                $shipments->shipment_status = Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED;
                $shipments->print_status = 1;
                $shipments->print_date_time = Carbon::now()->format('Y-m-d H:i:s');
                $shipments->print_by = Auth::user()->id;
                $result = $shipments->save();
            }

        }

        return $pdf->download('order_print_label_'.$request->shipment_id_input_val.'.pdf');
    }

    public function WCprintLevelBulk(Request $request){
        $shopIDShipmentID = explode(',', $request->website_shipment_ids_input_array);

        if(!empty($shopIDShipmentID)):
            $all_orders = array();
            $all_shop_ids = array();
            $all_shipment_ids = array();
            foreach($shopIDShipmentID as $item):
                $arr_shopIDShipmentID = explode("*", $item);

                array_push($all_shop_ids,$arr_shopIDShipmentID[0]);
                array_push($all_shipment_ids,$arr_shopIDShipmentID[1]);
                array_push($all_orders,$arr_shopIDShipmentID[2]);
            endforeach;
        endif;


        $allOrdersData = WooOrderPurchase::whereIn('order_id',$all_orders)->orderBy('id', 'DESC')->get();


        $data = [];
        $countries = WooCountry::all();
        $states = WooState::all();

        $arr_country = array();
        if(!empty($countries)){
            foreach ($countries as $country){
                $arr_country[$country->code] = $country->name;
            }
        }

        $arr_state = array();
        if(!empty($states)){
                foreach ($states as $state){
                $arr_state[$state->code] = $state->name;
            }
        }


        // This is array
        $allShippings = $this->formatCustomerAddress($isBulk='1',$allOrdersData,$arr_country,$arr_state);
        $arrProductImageWithID = $this->getProductDetailsByOrderID($isBulk='1',$allOrdersData,$all_shop_ids);

        //return view('seller.wc_purchase_order.order_print_level_bulk', compact('pages', 'allOrdersData','allShippings','all_shipment_ids'));

        $pdf = PDF::loadView('seller.wc_purchase_order.order_print_level_bulk', compact('allOrdersData','allShippings','all_shipment_ids'));



        //$pdf->setPaper('L', 'landscape');

        if($pdf){
            if($all_shipment_ids){
                foreach($all_shipment_ids as $shipment_id):
                    $shipments = Shipment::where('id',$shipment_id)->first();
                    $shipments->shipment_status = Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED;
                    $shipments->print_status = 1;
                    $shipments->print_date_time = Carbon::now()->format('Y-m-d H:i:s');
                    $shipments->print_by = Auth::user()->id;
                    $result = $shipments->save();
                endforeach;
            }
        }


        return $pdf->download('order_print_label_bulk.pdf');
    }



    // This function return array
    private function formatCustomerAddress($isBulk,$orderDetails,$arr_country,$arr_state){

        if($isBulk=='0'){
            $billing = json_decode($orderDetails->billing);
            $shipping = json_decode($orderDetails->shipping);

            /* This is for "WooCommerce" */
            $arr_customer_address = array();
            $arr_customer_address['shipping_name'] = $shipping->first_name." ".$shipping->last_name;
            $arr_customer_address['shipping_email'] = isset($shipping->email) ? $shipping->email : $billing->email;
            $arr_customer_address['shipping_phone'] = isset($shipping->phone) ? $shipping->phone : $billing->phone;
            $arr_customer_address['shipping_company'] = $shipping->company;
            $arr_customer_address['shipping_address_1'] = $shipping->address_1;
            $arr_customer_address['shipping_address_2'] = $shipping->address_2;
            $arr_customer_address['shipping_city'] = $shipping->city;
            $arr_customer_address['shipping_state'] = $arr_state[$shipping->state];
            $arr_customer_address['shipping_postcode'] = $shipping->postcode;
            $arr_customer_address['shipping_country'] = $arr_country[$shipping->country];
        }else{
            if(!empty($orderDetails)){
                foreach($orderDetails as $details){
                    $billing = json_decode($details->billing);
                    $shipping = json_decode($details->shipping);
                    /* This is for "WooCommerce" */
                    $arr_customer_address[$details->id]['shipping_name'] = $shipping->first_name." ".$shipping->last_name;
                    $arr_customer_address[$details->id]['shipping_email'] = isset($shipping->email) ? $shipping->email : $billing->email;
                    $arr_customer_address[$details->id]['shipping_phone'] = isset($shipping->phone) ? $shipping->phone : $billing->phone;
                    $arr_customer_address[$details->id]['shipping_company'] = $shipping->company;
                    $arr_customer_address[$details->id]['shipping_address_1'] = $shipping->address_1;
                    $arr_customer_address[$details->id]['shipping_address_2'] = $shipping->address_2;
                    $arr_customer_address[$details->id]['shipping_city'] = $shipping->city;
                    $arr_customer_address[$details->id]['shipping_state'] = $arr_state[$shipping->state];
                    $arr_customer_address[$details->id]['shipping_postcode'] = $shipping->postcode;
                    $arr_customer_address[$details->id]['shipping_country'] = $arr_country[$shipping->country];
                }
            }

        }
        return $arr_customer_address;
    }


    // This function return array
    private function getProductDetailsByOrderID($isBulk,$allOrders,$shop_id){
        $arr_product_id = [];
        $arr_product = array();
        if($isBulk==0){
            $orderDetails = json_decode($allOrders->line_items);
            if(!empty($orderDetails)){
                foreach ($orderDetails as $item) {
                    array_push($arr_product_id,$item->product_id);
                }
            }
        }else{
            foreach ($allOrders as $order) {
                $orderDetails = json_decode($order->line_items);
                if(!empty($orderDetails)){
                    foreach ($orderDetails as $item) {
                        array_push($arr_product_id,$item->product_id);
                    }
                }
            }
        }


        if($isBulk==0){
        $products = WooProduct::whereIn('product_id',$arr_product_id)->where('website_id',$shop_id)->get();
        }else{
            $products = WooProduct::whereIn('product_id',$arr_product_id)->whereIn('website_id',$shop_id)->get();
        }
        if(!empty($products)){
            foreach ($products as $item) {
                $product_id = $item->product_id;
                if (!empty($item)) {
                    $arr_product[$product_id] = $item;
                }
            }
        }
        return $arr_product;
    }



    private function getProductWiseTotalShippedQty($website_id,$orderId){

        $total_shipped_products = WooOrderPurchase::getProductWiseShipmentTotal($website_id,$orderId);
        $arr_product_id_shippedQty = array();
        if(!empty($total_shipped_products)){
            foreach ($total_shipped_products as $item) {
                $arr_product_id_shippedQty[$item->product_id] = $item->total_shipped;
            }
        }
        return $arr_product_id_shippedQty;
    }


    public function getCustomerAddress(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->order_id) && $request->order_id != null) {

                $data = WooOrderPurchase::where([
                    'order_id' => $request->order_id,
                    'website_id' => $request->shop_id
                ])->first();

                //dd($data);

                $id = $request->order_id;

                $countries = WooCountry::all();
                $states = WooState::all();

                return view('elements.form-show-customer-address', compact(['data', 'countries', 'states', 'id']));
            }
        }
    }


    public function getOrderProducts(Request $request)
    {
        $orderId = $request->get('orderId', 0);
        $shopId = $request->get('shopId', 0);
        $shipmentId = $request->get('shipmentId', 0);
        $productData = [];
        $productData = $this->OrderProducts($shopId,$orderId);
       // dd($shopId);
        return response()->json([
            'data' => $productData,
        ]);
    }


    private function OrderProducts($shopId,$orderId){
        $order = WooOrderPurchase::where(['website_id' => $shopId,'order_id' => $orderId])->first();
        $orderDetails = json_decode($order->line_items);

        $productData = [];

        $arr_images = array();
        foreach ($orderDetails as $item) {
            $product_id = $item->variation_id ?? $item->product_id;
            $arr[] = $product_id;

            $product = WooProduct::where(['product_id' => $product_id,'website_id' => $shopId])->first();
            if (!empty($product)) {
                $images = json_decode($product->images);
                if (!empty($images)) {
                    $arr_images[$product_id] = $product->images;
                } else {
                    $arr_images[$product_id] = '';
                }
            }
        }

        foreach ($orderDetails as $item) {
            $row = [];

            $image_url = asset('No-Image-Found.png');

            $product = WooProduct::where(['product_id' => $item->product_id,'website_id' => $shopId])->first();
            if (!empty($product)) {
                $images = json_decode($product->images);
                $image_url = $images[0]->src;
            }

            $attribites_1 = '';
            $attribites_2 = '';
            $woo_product_details = WooProduct::where('product_id', $item->product_id)
                                    ->first();

            if(isset($woo_product_details->attributes)){

            $attributes_data = json_decode($woo_product_details->attributes);
            if(isset($attributes_data[0])){
                $attribute_1 = $attributes_data[0]->name;
                $attribute_1_option = $attributes_data[0]->option;

                $attribites_1 = '<div class="mb-1">
                    <div class="whitespace-nowrap">
                        <label class="text-gray-700">
                             '.$attribute_1.'
                        </label>
                        <br>
                        <strong class="">'. $attribute_1_option .'</strong>
                    </div>
                </div>';
            }
            else{
                $attribites_1 = '';
            }

            if(isset($attributes_data[1])){
                $attribute_2 = $attributes_data[1]->name;
                $attribute_2_option = $attributes_data[1]->option;

                 $attribites_2 = '<div class="mb-1">
                    <div class="whitespace-nowrap">
                        <label class="text-gray-700">
                             '.$attribute_2.'
                        </label>
                        <br>
                        <strong class="">'. $attribute_2_option .'</strong>
                    </div>
                </div>';
            }
            else{
                $attribites_2 = '';
            }
        }

        $row[] = '
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <img src="'. $image_url .'" height="90" width="90" class="" />
                    </div>
                    <div>
                        <span class="whitespace-nowrap text-blue-500">
                            ID : <strong>'. $item->product_id .'</strong>
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
                    '.$attribites_1.'
                    '.$attribites_2.'
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700 font-bold">
                                Price :
                            </label>
                            <strong class="">'. currency_symbol('THB') . number_format(floatval($item->price), 2) .'</strong>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700 font-bold">
                                Ordered Qty :
                            </label>
                            <span class="text-gray-900">
                                '. number_format($item->quantity) .'
                            </span>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700 font-bold">
                                Total Price :
                            </label>
                            <strong class="">'. currency_symbol('THB') . number_format(floatval($item->subtotal), 2) .'</strong>
                        </div>
                    </div>
                </div>
            ';

            $productData[] = $row;
        }

        return $productData;
    }



    public function getShipmentProducts(Request $request)
    {
        $orderId = $request->get('orderId', 0);
        $shopId = $request->get('shopId', 0);
        $shipmentId = $request->get('shipmentId', 0);
        $productData = [];
        $productData = $this->fnShipmentProducts($shopId,$shipmentId);
       // dd($shopId);
        return response()->json([
            'data' => $productData,
        ]);
    }



    private function fnShipmentProducts($shopId,$shipmentId){
        $shipmentProducts = ShipmentProduct::getallShipmentsProductsByShopIdShipmentId($shopId,$shipmentId);

        $productData = [];

        $arr_images = array();

        foreach ($shipmentProducts as $item) {
            $row = [];

            $image_url = asset('No-Image-Found.png');

            if (!empty($item->images)) {
                $images = json_decode($item->images);
                $image_url = $images[0]->src;
            }

            $row[] = '
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <img src="'. $image_url .'" height="90" width="90" class="" />
                    </div>
                    <div>
                        <span class="whitespace-nowrap text-blue-500">
                            ID : <strong>'. $item->product_id .'</strong>
                        </span>
                    </div>
                </div>
            ';

            $row[] = '
                <div>
                    <div class="mb-1">
                        <strong>'. $item->product_name .'</strong>
                    </div>
                    <div class="mb-1">
                        <strong class="text-blue-500">'. $item->product_code .'</strong>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700 font-bold">
                                Price :
                            </label>
                            <strong class="">'. currency_symbol('THB') . number_format(floatval($item->price), 2) .'</strong>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700 font-bold">
                                Shipped Qty :
                            </label>
                            <span class="text-gray-900">
                                '. number_format($item->quantity) .'
                            </span>
                        </div>
                    </div>
                    <div class="mb-1">
                        <div class="whitespace-nowrap">
                            <label class="text-gray-700 font-bold">
                                Total Price :
                            </label>
                            <strong class="">'. currency_symbol('THB') . number_format(floatval($item->price * $item->quantity), 2) .'</strong>
                        </div>
                    </div>
                </div>
            ';

            $productData[] = $row;
        }

        return $productData;
    }



    public function bulkStatus(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->jSonData) && $request->jSonData != null) {
                $arr = json_decode($request->jSonData);
                foreach ($arr as $webIDOrderID) {
                    $arrWebOrder = explode("*", $webIDOrderID);
                    $website_id = $arrWebOrder[0];
                    $id = $arrWebOrder[1];
                    $order_id = $arrWebOrder[2];

                    //SAVE TO LOCAL DB
                    $orderPurchase = WooOrderPurchase::find($id);
                    $orderPurchase->status = $request->status;
                    $orderPurchase->save();
                    $flag = 1;

                    if (in_array(strtolower($request->status), [
                        WooOrderPurchase::ORDER_STATUS_PROCESSING,
                        // WooOrderPurchase::ORDER_STATUS_ON_HOLD,
                        // WooOrderPurchase::ORDER_STATUS_PENDING,
                    ])) {
                        /* Update inventory quantity */
                        if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($order_id, $website_id, $this->getTagForWooCommercePlatform())) {
                            $this->initInventoryQtyUpdateForWooCommerce($orderPurchase);
                        }
                    } else {
                        /**
                         * Update "display_reserved_qty" for the dodo products in this order.
                         * NOTE:
                         * This will be triggered for any other status/status_custom other than "processing".
                         */
                        if ($this->checkIfDisplayReservedQtyShouldBeUpdated($order_id, $website_id, $this->getTagForWooCommercePlatform())) {
                            AdjustDisplayReservedQty::dispatch($order_id, $website_id, $this->getTagForWooCommercePlatform())->delay(Carbon::now()->addSeconds(2));
                        }
                    }

                    // UPDATE TO WEBSITE DB
                    /*$shop = WooShop::where('id', $website_id)->get();
                    foreach ($shop as $details) {
                        $site_url = $details->site_url;
                        $rest_api_key = $details->rest_api_key;
                        $rest_api_secrete = $details->rest_api_secrete;
                    }*/

                    /*$url = $site_url . '/wp-json/wc/v3/orders/' . $order_id . '?consumer_key=' . $rest_api_key . '&consumer_secret=' . $rest_api_secrete;

                    $data = array(
                        'status' => $request->status,
                    );
                    $options = array(
                        'http' => array(
                            'header' => "Content-type: application/json\r\n",
                            'method' => 'POST',
                            'content' => json_encode($data),
                        )
                    );

                    $context = stream_context_create($options);
                    $result = file_get_contents($url, false, $context);
                    $response = json_decode($result);
                    */

                }

                if ($flag == 1) {
                    return 'OK';
                }

            }
        }
    }


    public function bulkSync(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->jSonData) && $request->jSonData != null) {
                $arr = json_decode($request->jSonData);
                foreach ($arr as $webIDOrderID) {
                    $arrWebOrder = explode("*", $webIDOrderID);
                    $website_id = $arrWebOrder[0];
                    $id = $arrWebOrder[1];
                    $order_id = $arrWebOrder[2];


                    $flag = 1;


                    // UPDATE TO WEBSITE DB


                    $shop = WooShop::where('id', $website_id)->get();
                    foreach ($shop as $details) {
                        $site_url = $details->site_url;
                        $rest_api_key = $details->rest_api_key;
                        $rest_api_secrete = $details->rest_api_secrete;
                    }

                    $url = $site_url . '/wp-json/wc/v3/orders/?include=' . $order_id . '&consumer_key=' . $rest_api_key . '&consumer_secret=' . $rest_api_secrete;

                    $json = file_get_contents($url);
                    $orders = json_decode($json, true);


                    foreach ($orders as $item) {

                        $order_id = $item['id'];
                        $status = $item['status'];
                        if($status==WooOrderPurchase::ORDER_STATUS_PROCESSED){
                            $status = WooOrderPurchase::ORDER_STATUS_READY_TO_SHIP;
                        }
                        $billing = $item['billing'];
                        $shipping = $item['shipping'];

                        $payment_method = $item['payment_method'];
                        $payment_method_title = $item['payment_method_title'];
                        $total = $item['total'];
                        $currency = $item['currency'];
                        $currency_symbol = $item['currency_symbol'];
                        $line_items = $item['line_items'];
                        if ($item['shipping_lines'] == '[]') {
                            $shipping_lines = '';
                        } else {
                            $shipping_lines = json_encode($item['shipping_lines']);
                        }

                        $created_at = $item['date_created'];
                        $updated_at = $item['date_modified'];

                        //delete if exist
                        $orderPurchase = WooOrderPurchase::where('order_id', $order_id)->where('website_id', $website_id)->delete();

                        $oderPurchase = new WooOrderPurchase();
                        // $oderPurchase->product_id = $request->product_id;
                        $oderPurchase->seller_id = Auth::user()->id;

                        $oderPurchase->website_id = $website_id;
                        $oderPurchase->order_id = $order_id;
                        $oderPurchase->status = $status;
                        $oderPurchase->billing = json_encode($billing);
                        $oderPurchase->shipping = json_encode($shipping);
                        $oderPurchase->line_items = json_encode($line_items);
                        $oderPurchase->shipping_lines = $shipping_lines;
                        $oderPurchase->payment_method = $payment_method;
                        $oderPurchase->payment_method_title = $payment_method_title;
                        $oderPurchase->total = $total;
                        $oderPurchase->currency_symbol = $currency_symbol;
                        $oderPurchase->order_date = $created_at;
                        $oderPurchase->save();

                        if (in_array(strtolower($request->status), [
                            WooOrderPurchase::ORDER_STATUS_PROCESSING,
                            // WooOrderPurchase::ORDER_STATUS_ON_HOLD,
                            // WooOrderPurchase::ORDER_STATUS_PENDING,
                        ])) {
                            /* Update inventory quantity */
                            if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($order_id, $website_id, $this->getTagForWooCommercePlatform())) {
                                $this->initInventoryQtyUpdateForWooCommerce($oderPurchase);
                            }
                        } else {
                            /**
                             * Update "display_reserved_qty" for the dodo products in this order.
                             * NOTE:
                             * This will be triggered for any other status/status_custom other than "processing".
                             */
                            if ($this->checkIfDisplayReservedQtyShouldBeUpdated($order_id, $website_id, $this->getTagForWooCommercePlatform())) {
                                AdjustDisplayReservedQty::dispatch($order_id, $website_id, $this->getTagForWooCommercePlatform())->delay(Carbon::now()->addSeconds(2));
                            }
                        }
                    }


                }

            }

            if ($flag == 1) {
                echo 'Order Synchronized Successfully';
            }

        }
    }


    public function getOrderStatus(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->id) && $request->id != null) {

                $row_index = $request->row_index;
                $data = WooOrderPurchase::where('id', $request->id)->first();
                $statuses = WooOrderPurchase::getAllOrderStatus();
                return view('elements.form-show-order-status', compact(['data', 'row_index', 'statuses']));
            }
        }
    }


    public function getOrderSyncData(Request $request)
    {

        try {
            $result = "Success";
            $website_id = $request->website_id;
            $site_url = $request->site_url;
            $rest_api_key = $request->rest_api_key;
            $rest_api_secret = $request->rest_api_secret;
            $total_orders = 0;

            if ($request->number_of_orders < 1) {

                $url = "$site_url/wp-json/wc/v3/reports/orders/totals?consumer_key=$rest_api_key&consumer_secret=$rest_api_secret";

                // 1. initialize
                $ch = curl_init();

                // 2. set the options, including the url
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);

                set_time_limit(12000); // THIS IS REQUIRED TO PLAY WITH LARGE DATA

                // 3. execute and fetch the resulting HTML output
                $output = curl_exec($ch);

                // 4. free up the curl handle
                curl_close($ch);



               // $json_product_qty = file_get_contents($url);

                $allRecrods = 0;
                if (!empty($output)) {
                    $orders = json_decode($output, true);
                    if (isset($orders) and !array_key_exists("code", $orders)) {
                        foreach ($orders as $group) {
                            if (isset($group["total"])) {
                                $allRecrods += $group["total"];
                            }
                        }
                    }

                }
                $total_orders = $allRecrods;


            } else {
                $total_orders = $request->number_of_orders;
            }



            $limit = 0;
            if ($total_orders>0) {
                if ($total_orders > 100) {
                    $count = ($total_orders / 100); // REST API ALLOW ONLY 100 RECORDS AT A TIME
                    $mod = $total_orders % 100;
                    for ($i = 1; $i <= $count; $i++) {
                        $arr_records[$i] = 100;
                    }
                    if ($mod > 0) $arr_records[$i] = $mod;
                } else {
                    $arr_records[1] = $total_orders;
                }



                foreach ($arr_records as $page => $per_page) {

                    $limit = $per_page;
                    $url_record = "$site_url/wp-json/wc/v2/orders?consumer_key=$rest_api_key&consumer_secret=$rest_api_secret&pagination_type=page&page=$page&limit=$limit&per_page=$per_page";
                    // 1. initialize
                    $ch = curl_init();

                    // 2. set the options, including the url
                    curl_setopt($ch, CURLOPT_URL, $url_record);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HEADER, 0);

                    set_time_limit(12000); // THIS IS REQUIRED TO PLAY WITH LARGE DATA

                    // 3. execute and fetch the resulting HTML output
                    $output = curl_exec($ch);

                    // 4. free up the curl handle
                    curl_close($ch);



                    if (!empty($output)) {
                        $orders = json_decode($output, true);
                        $i = 0;
                        foreach ($orders as $item) {

                            $order_id = $item['id'];
                            $status = $item['status'];
                            $billing = $item['billing'];
                            $shipping = $item['shipping'];
                            $payment_method = $item['payment_method'];
                            $payment_method_title = $item['payment_method_title'];
                            $total = $item['total'];
                            //$currency_symbol = $item['currency_symbol'];
                            $line_items = $item['line_items'];
                            if ($item['shipping_lines'] == '[]') {
                                $shipping_lines = '';
                            } else {
                                $shipping_lines = json_encode($item['shipping_lines']);
                            }

                            $created_at = $item['date_created'];
                            $updated_at = $item['date_modified'];

                            //delete if exist
                            $orderPurchase = WooOrderPurchase::where('order_id', $order_id)->where('website_id', $website_id)->delete();

                            $oderPurchase = new WooOrderPurchase();
                            $oderPurchase->website_id = $website_id;
                            // $oderPurchase->product_id = $request->product_id;
                            $oderPurchase->seller_id = Auth::user()->id;

                            if($status == WooOrderPurchase::ORDER_STATUS_READY_TO_SHIP){
                                $status = WooOrderPurchase::ORDER_STATUS_PROCESSED;
                            }

                            $oderPurchase->website_id = $website_id;
                            $oderPurchase->order_id = $order_id;
                            $oderPurchase->status = $status;
                            $oderPurchase->billing = json_encode($billing);
                            $oderPurchase->shipping = json_encode($shipping);
                            $oderPurchase->line_items = json_encode($line_items);
                            $oderPurchase->shipping_lines = $shipping_lines;
                            $oderPurchase->payment_method = $payment_method;
                            $oderPurchase->payment_method_title = $payment_method_title;
                            $oderPurchase->total = $total;
                            //$oderPurchase->currency_symbol = $currency_symbol;
                            $oderPurchase->order_date = $created_at;
                            $oderPurchase->save();

                            if (in_array(strtolower($status), [
                                WooOrderPurchase::ORDER_STATUS_PROCESSING,
                                // WooOrderPurchase::ORDER_STATUS_ON_HOLD,
                                // WooOrderPurchase::ORDER_STATUS_PENDING,
                            ])) {
                                /* Update inventory quantity */
                                if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($order_id, $website_id, $this->getTagForWooCommercePlatform())) {
                                    $this->initInventoryQtyUpdateForWooCommerce($oderPurchase);
                                }
                            } else {
                                /**
                                 * Update "display_reserved_qty" for the dodo products in this order.
                                 * NOTE:
                                 * This will be triggered for any other status/status_custom other than "processing".
                                 */
                                if ($this->checkIfDisplayReservedQtyShouldBeUpdated($order_id, $website_id, $this->getTagForWooCommercePlatform())) {
                                    AdjustDisplayReservedQty::dispatch($order_id, $website_id, $this->getTagForWooCommercePlatform())->delay(Carbon::now()->addSeconds(2));
                                }
                            }

                            if ($i % 100 == 0) {
                                usleep(5000000);
                            }

                            $i++;

                        }

                    }


                }

            }
        } catch (\Exception $exception) {
            $result = "Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine();
        }

        $cron = new WooCronReport();
        $cron->type = "order";
        $cron->shop_id = "$request->website_id";
        $cron->number_of_record_updated = $request->number_of_orders;
        $cron->result = $result;
        $cron->save();
    }


    public function orderSyncAll(Request $request)
    {
        set_time_limit(-1);
        $shops = WooShop::all();
        foreach ($shops as $shop) {
            try {
                $result = "Success";
                $website_id = $shop->id;
                $site_url = $shop->site_url;
                $consumer_key = $shop->rest_api_key;
                $consumer_secret = $shop->rest_api_secrete;
                $total_orders = 0;

                if ($request->number_of_orders < 1) {

                    $url = "$site_url/wp-json/wc/v3/reports/orders/totals?consumer_key=$rest_api_key&consumer_secret=$rest_api_secret";

                    // 1. initialize
                    $ch = curl_init();

                    // 2. set the options, including the url
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HEADER, 0);

                    set_time_limit(12000); // THIS IS REQUIRED TO PLAY WITH LARGE DATA

                    // 3. execute and fetch the resulting HTML output
                    $output = curl_exec($ch);

                    // 4. free up the curl handle
                    curl_close($ch);



                    // $json_product_qty = file_get_contents($url);

                    $allRecrods = 0;
                    if (!empty($output)) {
                        $orders = json_decode($output, true);

                        foreach ($orders as $group) {
                            $allRecrods += $group["total"];
                        }

                    }
                    $total_orders = $allRecrods;


                } else {
                    $total_orders = $request->number_of_orders;
                }



                $limit = 0;
                if ($total_orders>0) {
                    if ($total_orders > 100) {
                        $count = ($total_orders / 100); // REST API ALLOW ONLY 100 RECORDS AT A TIME
                        $mod = $total_orders % 100;
                        for ($i = 1; $i <= $count; $i++) {
                            $arr_records[$i] = 100;
                        }
                        if ($mod > 0) $arr_records[$i] = $mod;
                    } else {
                        $arr_records[1] = $total_orders;
                    }



                    foreach ($arr_records as $page => $per_page) {

                        $limit = $per_page;
                        $url_record = "$site_url/wp-json/wc/v2/orders?consumer_key=$rest_api_key&consumer_secret=$rest_api_secret&pagination_type=page&page=$page&limit=$limit&per_page=$per_page";
                        // 1. initialize
                        $ch = curl_init();

                        // 2. set the options, including the url
                        curl_setopt($ch, CURLOPT_URL, $url_record);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HEADER, 0);

                        set_time_limit(12000); // THIS IS REQUIRED TO PLAY WITH LARGE DATA

                        // 3. execute and fetch the resulting HTML output
                        $output = curl_exec($ch);

                        // 4. free up the curl handle
                        curl_close($ch);



                        if (!empty($output)) {
                            $orders = json_decode($output, true);
                            $i = 0;
                            foreach ($orders as $item) {

                                $order_id = $item['id'];
                                $status = $item['status'];
                                $billing = $item['billing'];
                                $shipping = $item['shipping'];
                                $payment_method = $item['payment_method'];
                                $payment_method_title = $item['payment_method_title'];
                                $total = $item['total'];
                                //$currency_symbol = $item['currency_symbol'];
                                $line_items = $item['line_items'];
                                if ($item['shipping_lines'] == '[]') {
                                    $shipping_lines = '';
                                } else {
                                    $shipping_lines = json_encode($item['shipping_lines']);
                                }

                                $created_at = $item['date_created'];
                                $updated_at = $item['date_modified'];

                                //delete if exist
                                $orderPurchase = WooOrderPurchase::where('order_id', $order_id)->where('website_id', $website_id)->delete();

                                $oderPurchase = new WooOrderPurchase();
                                $oderPurchase->website_id = $website_id;
                                // $oderPurchase->product_id = $request->product_id;
                                $oderPurchase->seller_id = Auth::user()->id;

                                if($status == WooOrderPurchase::ORDER_STATUS_READY_TO_SHIP){
                                    $status = WooOrderPurchase::ORDER_STATUS_PROCESSED;
                                }

                                $oderPurchase->website_id = $website_id;
                                $oderPurchase->order_id = $order_id;
                                $oderPurchase->status = $status;
                                $oderPurchase->billing = json_encode($billing);
                                $oderPurchase->shipping = json_encode($shipping);
                                $oderPurchase->line_items = json_encode($line_items);
                                $oderPurchase->shipping_lines = $shipping_lines;
                                $oderPurchase->payment_method = $payment_method;
                                $oderPurchase->payment_method_title = $payment_method_title;
                                $oderPurchase->total = $total;
                                //$oderPurchase->currency_symbol = $currency_symbol;
                                $oderPurchase->order_date = $created_at;
                                $oderPurchase->save();

                                if (in_array(strtolower($status), [
                                    WooOrderPurchase::ORDER_STATUS_PROCESSING,
                                    // WooOrderPurchase::ORDER_STATUS_ON_HOLD,
                                    // WooOrderPurchase::ORDER_STATUS_PENDING,
                                ])) {
                                    /* Update inventory quantity */
                                    if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($order_id, $website_id, $this->getTagForWooCommercePlatform())) {
                                        $this->initInventoryQtyUpdateForWooCommerce($oderPurchase);
                                    }
                                } else {
                                    /**
                                     * Update "display_reserved_qty" for the dodo products in this order.
                                     * NOTE:
                                     * This will be triggered for any other status/status_custom other than "processing".
                                     */
                                    if ($this->checkIfDisplayReservedQtyShouldBeUpdated($order_id, $website_id, $this->getTagForWooCommercePlatform())) {
                                        AdjustDisplayReservedQty::dispatch($order_id, $website_id, $this->getTagForWooCommercePlatform())->delay(Carbon::now()->addSeconds(2));
                                    }
                                }

                                if ($i % 100 == 0) {
                                    usleep(5000000);
                                }

                                $i++;

                            }

                        }


                    }

                }
            } catch (\Exception $exception) {

            }

            $cron = new WooCronReport();
            $cron->type = "order";
            $cron->shop_id = "$shop->id";
            $cron->number_of_record_updated = $number_of_orders;
            $cron->result = $result;
            $cron->save();
        }


    }


    public function getCountryStateSyncData(Request $request)
    {

        $website_id = $request->website_id;
        $site_url = $request->site_url;
        $consumer_key = $request->consumer_key;
        $consumer_secret = $request->consumer_secret;

        $allRecrods = 0;


        //$loop = ($allRecrods/100)+1;

        $loop = 2;


        $url2 = "$site_url/wp-json/wc-analytics/data/countries?consumer_key=$consumer_key&consumer_secret=$consumer_secret";
        $json = file_get_contents($url2);
        $orders = json_decode($json, true);

        foreach ($orders as $item) {

            $code = $item['code'];
            $name = $item['name'];
            $states = $item['states'];

            $countries = WooCountry::where('code', $code)->delete();
            $countries = new WooCountry();

            $countries->code = $code;
            $countries->name = $name;
            $countries->save();


            if (!empty($states)) {
                foreach ($states as $st) {
                    $states = WooState::where('code', $st['code'])->delete();
                    $states = new WooState();
                    $states->code = $st['code'];
                    $states->name = $st['name'];
                    $states->save();
                }
            }
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [];
        Session::put('itemArray', $data);
        $products = WooProduct::where('seller_id', Auth::user()->id)->get();
        $suppliers = Supplier::where('seller_id', Auth::user()->id)->get();
        return view('seller.wc_purchase_order.create', compact('products', 'suppliers'));
    }

    public function sync()
    {
        $data = [];
        Session::put('itemArray', $data);
        $shops = WooShop::where('seller_id', Auth::user()->id)->get();
        $products = WooProduct::where('seller_id', Auth::user()->id)->get();
        $suppliers = Supplier::where('seller_id', Auth::user()->id)->get();

        return view('seller.wc_purchase_order.sync', compact('shops', 'products', 'suppliers'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        // dd($request);
        $oderPurchase = new WooOrderPurchase();
        // $oderPurchase->product_id = $request->product_id;
        $oderPurchase->seller_id = Auth::user()->id;
        // $oderPurchase->quantity = $request->quantity;
        // $oderPurchase->tracking_number = $request->tracking_number;
        // $oderPurchase->reference = $request->reference;
        // $oderPurchase->e_d_f = date('Y-m-d',strtotime($request->e_d_f));
        // $oderPurchase->e_d_t = date('Y-m-d',strtotime($request->e_d_t));
        if (!empty($request->e_a_d_f)) {
            $oderPurchase->e_a_d_f = date('Y-m-d', strtotime($request->e_a_d_f));
        } else {
            $oderPurchase->e_a_d_f = null;
        }

        if (!empty($request->e_a_d_t)) {
            $oderPurchase->e_a_d_t = date('Y-m-d', strtotime($request->e_a_d_t));
        } else {
            $oderPurchase->e_a_d_t = null;
        }

        $oderPurchase->supplier_id = $request->supplier_id;
        if (!isset($request->check)) {
            $oderPurchase->supply_from = 0;
        } else {
            $oderPurchase->supply_from = $request->check;
        }
        $oderPurchase->note = $request->note;
        $oderPurchase->factory_tracking = $request->factory_tracking;
        $oderPurchase->cargo_ref = $request->cargo_ref;
        $oderPurchase->order_date = date('Y-m-d', strtotime($request->order_date));
        $oderPurchase->number_of_cartons = $request->number_of_cartons;
        $oderPurchase->domestic_logistics = $request->domestic_logistics;
        $oderPurchase->number_of_cartons1 = $request->number_of_cartons1;
        $oderPurchase->domestic_logistics1 = $request->domestic_logistics1;
        $oderPurchase->save();

        if (count($request->product_id) > 0) {
            foreach ($request->product_id as $key => $row) {
                $orderPurchaseDetails = new WooOrderPurchaseDetail();
                $orderPurchaseDetails->product_id = $row;
                $orderPurchaseDetails->quantity = $request->product_quantity[$key];
                $orderPurchaseDetails->seller_id = Auth::user()->id;
                $orderPurchaseDetails->order_purchase_id = $oderPurchase->id;
                $orderPurchaseDetails->po_status = 'open';
                $orderPurchaseDetails->save();
            }
        }


        if ($oderPurchase) {
            return redirect('order_purchase')->with('success', 'Order Purchase added Successfully');
        } else {
            return redirect('order_purchase')->with('danger', 'Something happened wrong');
        }
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $editData = WooOrderPurchase::where('id', $id)->with('supplier', 'orderProductDetails')->where('seller_id', Auth::user()->id)->first();
        // dd($editData);
        $data = [];
        if (isset($editData->orderProductDetails) && count($editData->orderProductDetails) > 0) {
            foreach ($editData->orderProductDetails as $row) {
                array_push($data, $row->product->product_code);
            }
        }
        Session::put('itemArray', $data);
        $products = WooProduct::where('seller_id', Auth::user()->id)->get();
        $suppliers = Supplier::where('seller_id', Auth::user()->id)->get();
        return view('seller.purchase_order.create', compact('products', 'suppliers', 'editData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }

    public function orderPurchaseDelete($id)
    {
        $orderPurchase = WooOrderPurchase::where('id', $id)->where('seller_id', Auth::user()->id)->delete();
        WooOrderPurchaseDetail::where('order_purchase_id', $id)->where('seller_id', Auth::user()->id)->delete();
        if ($orderPurchase) {
            return redirect()->back()->with('success', 'Order Purchase Deleted Successfully');
        } else {
            return redirect()->back()->with('danger', 'Something happened wrong');
        }
    }

    public function changeOrderPurchaseStatus(Request $request)
    {
        // UPDATE TO WEBSITE DB
        $website_id = $request->website_id;
        $order_id = $request->order_id;

        $shop = WooShop::where('id', $website_id)->get();

        foreach ($shop as $details) {
            $site_url = $details->site_url;
            $rest_api_key = $details->rest_api_key;
            $rest_api_secrete = $details->rest_api_secrete;
        }

        $url = $site_url . '/wp-json/wc/v3/orders/' . $order_id . '?consumer_key=' . $rest_api_key . '&consumer_secret=' . $rest_api_secrete;


        $woo_status = $request->status;
        if($request->status==WooOrderPurchase::ORDER_STATUS_PROCESSED){
            $woo_status = WooOrderPurchase::ORDER_STATUS_READY_TO_SHIP;
        }


        //SAVE TO LOCAL DB
        $status = $request->status;
        if($request->status==WooOrderPurchase::ORDER_STATUS_READY_TO_SHIP){
            $status = WooOrderPurchase::ORDER_STATUS_PROCESSED;
        }

        $orderPurchase = WooOrderPurchase::where('website_id',$request->website_id)->where('order_id',$request->order_id)->first();
        $orderPurchase->status = $status;
        $orderPurchase->save();

        if (in_array(strtolower($status), [
            WooOrderPurchase::ORDER_STATUS_PROCESSING,
            // WooOrderPurchase::ORDER_STATUS_ON_HOLD,
            // WooOrderPurchase::ORDER_STATUS_PENDING,
        ])) {
            /* Update inventory quantity */
            if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($order_id, $website_id, $this->getTagForWooCommercePlatform())) {
                $this->initInventoryQtyUpdateForWooCommerce($orderPurchase);
            }
        } else {
            /**
             * Update "display_reserved_qty" for the dodo products in this order.
             * NOTE:
             * This will be triggered for any other status/status_custom other than "processing".
             */
            if ($this->checkIfDisplayReservedQtyShouldBeUpdated($order_id, $website_id, $this->getTagForWooCommercePlatform())) {
                AdjustDisplayReservedQty::dispatch($order_id, $website_id, $this->getTagForWooCommercePlatform())->delay(Carbon::now()->addSeconds(2));
            }
        }

         // UPDATE WOO SITE STATUS
         $data = array(
            'status' => $woo_status,
        );


        $headers = array(
            'Authorization' => 'Basic ' . base64_encode($rest_api_key.':'.$rest_api_secrete )
        );


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERPWD, "$rest_api_key:$rest_api_secrete");
        $resp = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        //print_r(json_decode($resp));


        if ($orderPurchase) {
            return 'Order Status Updated Successfully';
        } else {
            return 'Something happened wrong';
        }
    }

    public function changeOrderPurchaseAddress(Request $request)
    {

        $order_id = $request->id;
        $url = $site_url . '/wp-json/wc/v3/orders/' . $order_id . '?consumer_key=' . $rest_api_key . '&consumer_secret=' . $rest_api_secrete;
        $data['billing'] = array(
            'first_name' => $request->billing_first_name,
            'last_name' => $request->billing_last_name,
            'company' => $request->billing_company,
            'address_1' => $request->billing_address_1,
            'address_2' => $request->billing_address_2,
            'city' => $request->billing_city,
            //'state' => $request->billing_state,
            'postcode' => $request->billing_postcode,
            'country' => $request->billing_country,
            'email' => $request->billing_email,
            'phone' => $request->billing_phone,
        );

        $data['shipping'] = array(
            'first_name' => $request->shipping_first_name,
            'last_name' => $request->shipping_last_name,
            'company' => $request->shipping_company,
            'address_1' => $request->shipping_address_1,
            'address_2' => $request->shipping_address_2,
            'city' => $request->shipping_city,
            //'state' => $request->shipping_state,
            'postcode' => $request->shipping_postcode,
            'country' => $request->shipping_country,
        );
        //print_r($data);
        //  die;
        $options = array(
            'http' => array(
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data),
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result);


        if ($result) {
            return redirect('wc-order-purchase')->with('success', 'Order Purchase Status Updated Successfully333');
        } else {
            return redirect('wc-order-purchase')->with('danger', 'Something happened wrong');
        }

    }


    public function wc_order_delete(Request $request)
    {
        $id = $request->id;
        $order_id = $request->order_id;
        $purchase_order = WooOrderPurchase::where('id', $id)->where('seller_id', Auth::user()->id)->first();
        $shop = WooShop::find($purchase_order->website_id);

        $WC_Client = new Client(
            $shop->site_url,
            $shop->rest_api_key,
            $shop->rest_api_secrete,
            [
                'version' => 'wc/v3',
            ]
        );

        $WC_Client->delete('orders/'. $order_id);
        $purchase_order->delete();

        return response()->json([
            'status' => 1,
        ]);
    }



    public function WCStorArrangeShipmentForOrder(StoreRequest $request, ArrangeWCShipmentActionForOrder $arrangeWCShipmentActionForOrder)
    {

        $sellerId = Auth::user()->id;

        $shipmentData = [
            'website_id' => $request->website_id,
            'shipment_date' => $request->shipment_date,
            'order_id' => $request->order_id,
            'seller_id' => $sellerId,
            'shipment_status' => $request->shipment_status,
            'product_id' => $request->product_id,
            'is_custom' => 0,
            'shipment_qty' => $request->shipment_qty,
            'shipment_for' => Shipment::SHIPMENT_FOR_WOO
        ];


        $arrangeWCShipmentActionForOrder->handle($shipmentData);
        $ordered_details = WooOrderPurchase::where('website_id',$request->website_id)->where('order_id',$request->order_id)->where('seller_id',Auth::user()->id)->first();
        return $this->apiResponse(Response::HTTP_CREATED, 'Shipment successfully created.',$ordered_details);
    }



    public function WCshipmentForOrder(StoreRequest $request, CreateWCShipmentActionForOrder $createWCShipmentActionForOrder)
    {

        $sellerId = Auth::user()->id;

        $shipmentData = [
            'shipment_date' => $request->shipment_date,
            'order_id' => $request->order_id,
            'seller_id' => $sellerId,
            'shipment_status' => $request->shipment_status,
            'product_id' => $request->product_id,
            'variation_id' => $request->variation_id,
            'is_custom' => 0,
            'shipment_qty' => $request->shipment_qty,
            'shipment_for' => Shipment::SHIPMENT_FOR_WOO,
            'shop_id' => $request->shop_id
        ];

        $createWCShipmentActionForOrder->handle($shipmentData);
        $ordered_details = WooOrderPurchase::where('website_id',$request->shop_id)->where('order_id',$request->order_id)->where('seller_id',Auth::user()->id)->first();
        return $this->apiResponse(Response::HTTP_CREATED, 'Shipment successfully created.',$ordered_details);
    }

    public function WCshipmentUpdateForOrder(UpdateRequest $request, UpdateWCShipmentActionForOrder $UpdateWCShipmentActionForOrder)
    {

        $sellerId = Auth::user()->id;
        $shipmentData = [
            'shipment_date' => $request->shipment_date,
            'order_id' => $request->order_id,
            'shipment_id' => $request->shipment_id,
            'seller_id' => $sellerId,
            'shipment_status' => $request->shipment_status,
            'product_id' => $request->product_id,
            'shipment_qty' => $request->shipment_qty,
            'shipment_for' => Shipment::SHIPMENT_FOR_WOO,
        ];



        $UpdateWCShipmentActionForOrder->handle($shipmentData);

        return $this->apiResponse(Response::HTTP_CREATED, 'Shipment successfully updated.');
    }


    public function WCmarkAsShipped(Request $request){
        $shipments = Shipment::find($request->shipment_id);
        $shipments->shipment_status = Shipment::SHIPMENT_STATUS_SHIPPED;
        $shipments->mark_as_shipped_status = Shipment::SHIPMENT_STATUS_SHIPPED;
        $shipments->mark_as_shipped_date_time = Carbon::now()->format('Y-m-d H:i:s');
        $shipments->mark_as_shipped_by = Auth::user()->id;
        $result = $shipments->save();
     }

    public function getWooOrderStatus(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->id) && $request->id != null) {
                $data = WooOrderPurchase::where('id', $request->id)->first();
                $statuses = WooOrderPurchase::getAllOrderStatus();
                return view('elements.form-show-woo-order-status', compact(['data', 'statuses']));
            }
        }
    }


    public function updateWCShipmentStatus(Request $request)
    {


    }


}
