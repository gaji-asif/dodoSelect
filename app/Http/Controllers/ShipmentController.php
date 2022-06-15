<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\OrderManagement;
use App\Models\OrderManagementDetail;
use App\Models\CustomerShippingMethod;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Category;
use App\Models\Shipper;
use App\Models\Shipment;
use App\Models\ShipmentProduct;
use App\Models\Shop;
use App\Models\User;
use App\Models\WooOrderPurchase;
use App\Models\WooProduct;
use App\Models\WooCountry;
use App\Models\WooState;
use App\Models\WooShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Datatables;
use Illuminate\Http\Response;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use DB;
use Illuminate\Support\Facades\Crypt;
use PDF;
use Carbon\Carbon;
use App\Models\Shopee;
use App\Models\ShopeeOrderPurchase;
use Illuminate\Support\Facades\Http;
use App\Jobs\WooWebhookSync;

class ShipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index($shipment_for)
    {
        $today = Carbon::today()->toDateString();

        $shipmentsTotalAll = Shipment::where('seller_id',Auth::user()->id)
                            ->count();

        $shipmentsTotalToday = Shipment::whereDate('shipment_date',$today)
                             ->count();

        $shipmentsTotalBeforeToday = Shipment::whereDate('shipment_date','<',$today)->count();

        // $shipmentsTotalAfterToday = Shipment::whereDate('shipment_date','>',$today)->count();
        $statusMainSchema = ShopeeOrderPurchase::getMainStatusSchemaForDatatableForShipment();

        $data = [
            'shipmentsTotalAll' => $shipmentsTotalAll,
            'shipmentsTotalToday' => $shipmentsTotalToday,
            'shipmentsTotalBeforeToday' => $shipmentsTotalBeforeToday,
            'shipment_for'=>$shipment_for,
            'statusMainSchema' => $statusMainSchema
        ];
        if($shipment_for == Shipment::SHIPMENT_FOR_SHOPEE){
             $totalProcessingShopeeCommerce = ShopeeOrderPurchase::where('seller_id', Auth::id())
            ->whereIn('status_custom', [ShopeeOrderPurchase::ORDER_STATUS_PROCESSING])
            ->count();

        $statusMainSchema = ShopeeOrderPurchase::getMainStatusSchemaForDatatableForShipment();
        $firstStatusOrderId = array_column($statusMainSchema[0]['sub_status'], 'id')[0];

        $statusSecondarySchema = ShopeeOrderPurchase::getSecondaryStatusSchemaForDatatable();

        $data = [
            'shops' => $this->getShopeeShopsWithProcessingOrdersCount(implode(",", array_column($statusMainSchema[0]['sub_status'], 'id'))),
            'countries' => [],
            'states' => [],
            'totalProcessingShopeeCommerce' => $totalProcessingShopeeCommerce,
            'orderCancelationReasons' => ShopeeOrderPurchase::getAllOrderCancelReasons(),
            'statusMainSchema' => $statusMainSchema,
            'statusSecondarySchema' => $statusSecondarySchema,
            'firstStatusOrderId' => $firstStatusOrderId,
            'shippingMethodForShopee' => ShopeeOrderPurchase::getShippingMethodForShopeeWithOrdersCount(),
            'shipment_for'=>$shipment_for
        ];
            return view('seller.shipments.shopee_orders', $data);
        }
        elseif($shipment_for == Shipment::SHIPMENT_FOR_WOO){
            $shipmentsTotalAll = WooOrderPurchase::where('seller_id',Auth::user()->id)
                            ->count();
            $shipmentsTotalToday = WooOrderPurchase::whereDate('order_date',$today)
                             ->count();
            $shipmentsTotalBeforeToday = WooOrderPurchase::whereDate('order_date','<',$today)->count();
            $countries = WooCountry::all();
        
            $data = [
                'shipmentsTotalAll' => $shipmentsTotalAll,
                'shipmentsTotalToday' => $shipmentsTotalToday,
                'shipmentsTotalBeforeToday' => $shipmentsTotalBeforeToday,
                'shipment_for'=> Shipment::SHIPMENT_FOR_WOO,
                'statusMainSchema' => $statusMainSchema
            ];
            return view('seller.shipments.woo_shipments', $data);
        }
        else{
            return view('seller.shipments.index', $data); 
        }

    }

    public function data(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $shipment_for = $request->shipment_for;
        if ($request->ajax()) {
            if (isset($request->status) && $request->status != null && $request->status != 'all') {
                $status = $request->status;
                $data = Shipment::getShipmentDataStatusWise($status, $shipment_for);
                
            }else{
               $data = Shipment::select('shipments.*', 'shops.name', 'shops.logo', 'customers.customer_name', 'channels.name as channnel_name', 'channels.image as channnel_image')
                    ->leftJoin('order_managements', 'order_managements.id', '=', 'shipments.order_id')
                    ->leftJoin('shops', 'order_managements.shop_id', '=', 'shops.id')
                    ->leftJoin('customers', 'order_managements.customer_id', '=', 'customers.id')
                    ->leftJoin('channels', 'order_managements.channel_id', '=', 'channels.id')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.shipment_for', '=', $shipment_for)
                    ->get();

            }
            if (isset($request->shipment_status) && $request->shipment_status != null) {

               $data = Shipment::getShipmentDataShipStatusWise($request->shipment_status, $request->shipment_for);
               
            }
            if (isset($request->shipment_no) && $request->shipment_no != null) {
               $data = Shipment::getShipmentDataShipNoWise($request->shipment_no, $request->shipment_for);
               
            }

            // for order shipment details
            if (isset($request->order_id) && $request->order_id != null) {
               $data = Shipment::getShipmentDataForOrder($request->order_id, $request->is_custom);
               
            }

            //$data = OrderManagement::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return $row->id;
                })
                ->addColumn('details', function ($row) {
                    $ordersDetails = OrderManagementDetail::select('id')->where('order_management_id',$row->order_id)->get();
                    $getTotalItems = ShipmentProduct::where('shipment_products.shipment_id', '=', $row->id)
                    ->select('shipment_products.*')
                    ->get();
         
                    $shipment_status = '';
                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_PENDING_STOCK){
                        $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_PENDING_STOCK);
                    }
                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_READY_TO_SHIP){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_READY_TO_SHIP);
                    }
                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_SHIPPED){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_SHIPPED);
                    }
                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_CANCEL){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_CANCEL);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_WOO_HOLD){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_HOLD);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_WOO_READY_TO_SHIP){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_READY_TO_SHIP);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_WOO_PROCESSING){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_PROCESSING);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_WOO_PENDING){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_PENDING);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_WOO_COMPLETED){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_COMPLETED);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_WOO_CANCEL){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_CANCEL);
                    }

                    $channelName = $row->channnel_name;
                    $shopName = '';
                    if(isset($row->name)){
                        $shopName = $row->name;
                    }
                    else{
                        $shopName = '';
                    }
                    
                    if (!empty($row->channnel_name) && file_exists(public_path($row->channnel_image))) {
                        $image = asset($row->channnel_image);
                    }
                    else {
                        $image = asset('img/No_Image_Available.jpg');
                    }

                    $order = OrderManagement::findOrFail($row->order_id);
                    $shippingMethod = '';
                    foreach ($order->customer_shipping_methods as $customerShipping){
                        if ($customerShipping->is_selected == CustomerShippingMethod::IS_SELECTED_YES){
                            $shippingMethod = $customerShipping->shipping_cost->name;
                        }
                    }

                    $print_btn_action = '<button onclick="printLevel('.$row->id.','.$row->order_id.')" type="button" class="shipment_btns btn btn-sm btn-warning mb-1 mt-1 action_btns" id="" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-print mr-1" aria-hidden="true"></i>'.__('translation.Print Label').'</button>';

                    $pack_btn_action = '<button onclick="packOrder('.$row->id.','.$row->order_id.')" type="button" class="shipment_btns mb-1 mt-1 btn btn-primary btn-sm action_btns" id="" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-truck-pickup mr-1" aria-hidden="true"></i>'.__('translation.Pick Confirm').'</button>';

                    $pack_btn_action_cancel = '<button onclick="pickOrderCancel('.$row->id.','.$row->order_id.')" type="button" class="shipment_btns mb-1 mt-1 btn btn-danger btn-sm action_btns" id="" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-trash mr-1" aria-hidden="true"></i>'.__('translation.Cancel').'</button>';

                    $mark_as_shipped_btn_action = '<button onclick="markAsShipped('.$row->id.','.$row->order_id.')" type="button" class="shipment_btns mb-1 mt-1 btn btn-success btn-sm action_btns" id="" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-shipping-fast mr-1" aria-hidden="true"></i>'.__('translation.Mark as Shipped').'</button>';

                    $mark_as_shipped_btn_action_update = '<button onclick="markAsShippedUpdate('.$row->id.','.$row->order_id.')" type="button" class="shipment_btns mb-1 mt-1 btn btn-info btn-sm action_btns" id="" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-shipping-fast mr-1" aria-hidden="true"></i>'.__('translation.Update').'</button>';


                    if($row->print_status == 1){
                    $print_time = Carbon::parse($row->print_date_time)->format('d/m/Y h:i a');
                    $userDetails = User::select('name')->where('id',$row->print_by)->first();

                    $print_by_btn =  '<div class="action_butn_style"><button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Printed On :<strong><br>'.$print_time.'<br></strong> (<font class="text-blue-500">'.$userDetails->name.'</font>)</button>
                        <br>'.$print_btn_action.'</div>
                        ';
                    }
                    else{
                     $print_by_btn =  $print_btn_action;
                    }

                    if($row->pack_status == 1){
                        
                        $packed_date_time = Carbon::parse($row->packed_date_time)->format('d/m/Y h:i a');
                        $userDetails = User::select('name')->where('id',$row->packed_by)->first();
                    

                        $pack_status_btn =  '<div class="action_butn_style"><button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Pick Confirm :<strong><br>'.$packed_date_time.'<br></strong> (<font class="text-blue-500">'.$userDetails->name.'</font>)</button>
                            <br>'.$pack_btn_action_cancel.'</div>
                            ';
                    }
                    else{
                        $pack_status_btn = $pack_btn_action;
                    }

                    if($row->mark_as_shipped_status == 1){
                        
                        $mark_as_shipped_date_time = Carbon::parse($row->mark_as_shipped_date_time)->format('d/m/Y h:i a');
                        $userDetails = User::select('name')->where('id',$row->mark_as_shipped_by)->first();
                    

                        $mark_as_shipped_btn =  '<div class="action_butn_style"><button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Mark Shipped On :<strong><br>'.$mark_as_shipped_date_time.'<br></strong> (<font class="text-blue-500">'.$userDetails->name.'</font>)</button>
                            <br>
                            '.$mark_as_shipped_btn_action_update.'</div>
                            ';
                    }
                    else{
                        $mark_as_shipped_btn = $mark_as_shipped_btn_action;
                    }

                   return '<div class="border-div col-lg-12">
                    <div class="row border-bottom-dotted common_padding_5">
                            <div class="col-lg-3 text-center"><a class="shipment_order_id" href="'. route('order_management.edit', [ 'order_management' => $row->order_id ]) .'"><strong>#'.$row->order_id.'</strong></a> (Ship ID: <strong>'.$row->id.')</strong> </div>
                            <div class="col-lg-6 text-center">'.$shipment_status.'</div>
                            <div class="col-lg-3 text-left">
                                <div class="margin_top_mb_10">
                            Shipment Date : <strong>'.Carbon::parse($row->shipment_date)->format('d/m/Y').'</strong>
                            </div>
                            </div>
                        </div>
                        <div class="row common_padding_5">
                            <div class="col-lg-3 text-center">
                              <div class="mb-1 md:mb-3">
                                <img src="'. $image .'" alt="'. $channelName .'" title="'. $channelName .'" class="w-10 h-auto" />
                            </div>
                            <div class="">
                                <span class="badge-status--yellow">
                                    '. $shopName .'
                                </span>
                            </div>
                            </div>
                            <div class="col-lg-9 text-left">
                            <div class="row">
                            <div class="col-lg-12">
                                <div class="width-100 float-left">
                                <font>Customer Name : <strong>'.$row->customer_name.'</strong></font><br>
                                <font class="mb-2 margin_bottom_10">Total Items: <strong class="text-underline cursor" onclick="see_total_items('.$row->id.','.$row->order_id.')">'.count($getTotalItems).'</strong></font><br>
                                <div class="mb-3">
                                    <span class="text-gray-600">
                                        Shipped Method:
                                    </span>
                                    <a data-id="'. $row->id .'" id="BtnAddress" class="modal-open cursor-pointer">' . $shippingMethod .'</a>
                                </div>
                                </div>
                                
                            </div>
                            </div>
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
                        </div>';
                })
                ->addColumn('id', function ($row) {
                    $ordersDetails = OrderManagementDetail::select('id')->where('order_management_id',$row->order_id)->get();
                    return 'Shipment ID : <strong>#'.$row->id.'</strong><br>
                    Shipment Date : '.Carbon::parse($row->shipment_date)->format('d-M-Y h:i:a').'<br>Order ID : <strong>#'.$row->order_id.'</strong><br>Total Item : <strong>'.count($ordersDetails).'</strong>';
                })
               
                 ->addColumn('print_level', function ($row) {

                    if($row->print_status == 1){
                        $print_time = Carbon::parse($row->print_date_time)->format('d-M-Y h:i a');
                        $userDetails = User::select('name')->where('id',$row->print_by)->first();
                        return '<strong>Printed by</strong> : <br>'.$userDetails->name.'<br><strong>'.$print_time.'</strong>';
                    }
                    else{
                     return '<button type="button" class="btn btn-success btn-sm" id="printLevel" order-id="' . $row->order_id . '" data-id="' . $row->id . '">'.__('translation.Print Label').'</button>';
                    }

                 })
                 ->addColumn('pack_order', function ($row) {
                    if($row->pack_status == 0){
                    return '<button type="button" class="btn btn-warning btn-sm" id="packOrder" order-id="' . $row->order_id . '" data-id="' . $row->id . '">Pack</button>';
                    }
                    if($row->pack_status == 1){
                        $packed_time = Carbon::parse($row->packed_date_time)->format('d-M-Y h:i a');
                        $userDetails = User::select('name')->where('id',$row->packed_by)->first();
                        return '<strong>Packed by</strong> : <br> '.$userDetails->name.'<br><strong>'.$packed_time.'</strong>';
                    }
                 })
                 

                 ->addColumn('shipment_status', function ($row) {
                    if($row->shipment_status == 10){
                        return '<button type="button" class="btn btn-danger btn-sm">'.__('translation.Wanting for Stock').'</button>';
                    }
                    if($row->shipment_status == 11){
                        return '<button type="button" class="btn btn-danger btn-sm"> '. __('translation.Ready To ship') .'</button>';
                    }
                    if($row->shipment_status == 12){
                        return '<button type="button" class="btn btn-danger btn-sm"> '.__('translation.Shipped').'</button>';
                    }
                    if($row->shipment_status == 13){
                        return '<button type="button" class="btn btn-danger btn-sm">'. __('translation.Cancelled').'</button>';
                    }
               })
                 ->rawColumns(['checkbox','details','id', 'shipment_status', 'print_level', 'pack_order'])
                ->make(true);
        }
    }


    public function dataWooShipments(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $shipment_for = $request->shipment_for;
        if ($request->ajax()) {
            if (isset($request->status) && $request->status != null && $request->status != 'all') {
                $status = $request->status;
                //$data = Shipment::getShipmentDataStatusWise($status, $shipment_for);
                
            }else{
               $data = Shipment::select('shipments.*', 'shops.name', 'shops.logo', 'woo_order_purchases.shipping', 'woo_order_purchases.line_items', 'woo_order_purchases.shipping_lines', 'woo_order_purchases.website_id')
                    ->leftJoin('woo_order_purchases', 'woo_order_purchases.order_id', '=', 'shipments.order_id')
                    ->leftJoin('shops', 'woo_order_purchases.website_id', '=', 'shops.id')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.shipment_for', '=', $shipment_for)
                    ->get();

            }
            if (isset($request->shipment_status) && $request->shipment_status != null) {

               $data = Shipment::getWooShipmentDataShipStatusWise($request->shipment_status, $request->shipment_for);
               
            }
            if (isset($request->shipment_no) && $request->shipment_no != null) {
               $data = Shipment::getWooShipmentDataShipNoWise($request->shipment_no, $request->shipment_for);
               
            }

            //$data = OrderManagement::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    //return $row->id;
                    return $row->website_id . '*' . $row->id . '*' . $row->order_id;
                })
                ->addColumn('details', function ($row) {
                    $getTotalItems = ShipmentProduct::where('shipment_products.shipment_id', '=', $row->id)
                    ->select('shipment_products.*')
                    ->get();
         
                    $shipment_status = '';
                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_PENDING_STOCK){
                        $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_PENDING_STOCK);
                    }
                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_READY_TO_SHIP){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_READY_TO_SHIP);
                    }
                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_SHIPPED){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_SHIPPED);
                    }
                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_CANCEL){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_CANCEL);
                    }

                    if($row->shipment_status == Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED){
                         $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED);
                    }

                    if(isset($row->name)){
                        $shopName = $row->name;
                    }
                    else{
                        $shopName = '';
                    }

                    $shipping = json_decode($row->shipping);
                    $productDetails = json_decode($row->line_items);

                    if(isset($shipping)){
                        $customerName = $shipping->first_name." ".$shipping->last_name;
                    }
                    else{
                        $customerName = '';
                    }

                    if (!empty($row->logo) && file_exists(public_path($row->logo))) {
                        $image = asset($row->logo);
                    }
                    else {
                        $image = asset('img/No_Image_Available.jpg');
                    }

                    $channelImage = asset('img/No_Image_Available.jpg');
                    $channelName = 'Woocommerce';
                    $shannel = Channel::where('name', 'Woocommerce')->first();
                    if (!empty($shannel) && file_exists(public_path($shannel->image))) {
                        $channelImage = asset($shannel->image);
                    }

                    //$order = OrderManagement::findOrFail($row->order_id);
                    $shipmentMethod = '';
                    $shipping_lines = json_decode($row->shipping_lines);
                    if (!empty($shipping_lines)) {
                        $shipmentMethod = $shipping_lines[0]->method_title;
                    }

                    $print_btn_action = '<button onclick="printLevel('.$row->id.','.$row->order_id.', '.$row->website_id.')" type="button" class="shipment_btns btn btn-sm btn-warning mb-1 mt-1 action_btns" id="" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-print mr-1" aria-hidden="true"></i>'.__('translation.Print Label').'</button>';

                    $pack_btn_action = '<button onclick="packOrder('.$row->id.','.$row->order_id.')" shop_id="' . $row->shop_id . '" type="button" class="shipment_btns mb-1 mt-1 btn btn-primary btn-sm action_btns" id="packOrder_' . $row->id . '" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-truck-pickup mr-1" aria-hidden="true"></i>'.__('translation.Pick Confirm').'</button>';

                    $pack_btn_action_cancel = '<button onclick="pickOrderCancel('.$row->id.','.$row->order_id.')" type="button" class="shipment_btns mb-1 mt-1 btn btn-danger btn-sm action_btns" id="" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-trash mr-1" aria-hidden="true"></i>'.__('translation.Cancel').'</button>';

                    $mark_as_shipped_btn_action = '<button onclick="markAsShipped('.$row->id.','.$row->order_id.')" type="button" class="shipment_btns mb-1 mt-1 btn btn-success btn-sm action_btns" id="" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-shipping-fast mr-1" aria-hidden="true"></i>'.__('translation.Mark as Shipped').'</button>';

                    $mark_as_shipped_btn_action_update = '<button onclick="markAsShippedUpdate('.$row->id.','.$row->order_id.')" type="button" class="shipment_btns mb-1 mt-1 btn btn-info btn-sm action_btns" id="" order-id="' . $row->order_id . '" data-id="' . $row->id . '"><i class="fa fa-shipping-fast mr-1" aria-hidden="true"></i>'.__('translation.Update').'</button>';


                    if($row->print_status == 1){
                    $print_time = Carbon::parse($row->print_date_time)->format('d/m/Y h:i a');
                    $userDetails = User::select('name')->where('id',$row->print_by)->first();

                    $print_by_btn =  '<div class="action_butn_style"><button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Printed On :<strong><br>'.$print_time.'<br></strong> (<font class="text-blue-500">'.$userDetails->name.'</font>)</button>
                        <br>'.$print_btn_action.'</div>
                        ';
                    }
                    else{
                     $print_by_btn =  $print_btn_action;
                    }

                    if($row->pack_status == 1){
                        
                        $packed_date_time = Carbon::parse($row->packed_date_time)->format('d/m/Y h:i a');
                        $userDetails = User::select('name')->where('id',$row->packed_by)->first();
                    

                        $pack_status_btn =  '<div class="action_butn_style"><button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Pick Confirm :<strong><br>'.$packed_date_time.'<br></strong> (<font class="text-blue-500">'.$userDetails->name.'</font>)</button>
                            <br>'.$pack_btn_action_cancel.'</div>
                            ';
                    }
                    else{
                        $pack_status_btn = $pack_btn_action;
                    }

                    if($row->mark_as_shipped_status == 1){
                        
                        $mark_as_shipped_date_time = Carbon::parse($row->mark_as_shipped_date_time)->format('d/m/Y h:i a');
                        $userDetails = User::select('name')->where('id',$row->mark_as_shipped_by)->first();
                    

                        $mark_as_shipped_btn =  '<div class="action_butn_style"><button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Mark Shipped On :<strong><br>'.$mark_as_shipped_date_time.'<br></strong> (<font class="text-blue-500">'.$userDetails->name.'</font>)</button>
                            <br>
                            '.$mark_as_shipped_btn_action_update.'</div>
                            ';
                    }
                    else{
                        $mark_as_shipped_btn = $mark_as_shipped_btn_action;
                    }

                   return '<div class="border-div col-lg-12">
                    <div class="row border-bottom-dotted common_padding_5">
                            <div class="col-lg-3 text-center">
                            <a class="shipment_order_id" href="'. route('wc-order-purchase-details', [ 'id' => $row->order_id ,'shop_id' => $row->shop_id ]) .'"><strong>#'.$row->order_id.'</strong></a> (Ship ID: <strong>'.$row->id.')</strong> </div>
                            <div class="col-lg-6 text-center">'.$shipment_status.'</div>
                            <div class="col-lg-3 text-left">
                                <div class="margin_top_mb_10">
                            Shipment Date : <strong>'.Carbon::parse($row->shipment_date)->format('d/m/Y').'</strong>
                            </div>
                            </div>
                        </div>
                        <div class="row common_padding_5">
                            <div class="col-lg-3 text-center">
                              <div class="mb-1 md:mb-3">
                                <img src="'. $image .'" alt="'. $channelName .'" title="'. $channelName .'" class="w-10 h-auto" />
                            </div>
                            <div class="">
                                <span class="badge-status--yellow">
                                    '. $shopName .'
                                </span>
                            </div>
                            </div>
                            <div class="col-lg-9 text-left">

                            <font>Customer Name : <strong>'.$customerName.'</strong></font><br>
                            <font class="mb-2 margin_bottom_10">Total Items: <strong class="text-underline cursor" onclick="see_total_items('.$row->id.','.$row->order_id.')">'.count($getTotalItems).'</strong></font><br>
                                <div class="mb-3">
                                    <span class="text-gray-600">
                                        Shipped Method:
                                    </span>
                                    <a data-id="'. $row->id .'" id="BtnAddress" class="modal-open cursor-pointer">' . $shipmentMethod .'</a>
                                </div>
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
                        </div>';
                })
                ->addColumn('id', function ($row) {
                    $ordersDetails = OrderManagementDetail::select('id')->where('order_management_id',$row->order_id)->get();
                    return 'Shipment ID : <strong>#'.$row->id.'</strong><br>
                    Shipment Date : '.Carbon::parse($row->shipment_date)->format('d-M-Y h:i:a').'<br>Order ID : <strong>#'.$row->order_id.'</strong><br>Total Item : <strong>'.count($ordersDetails).'</strong>';
                })
               
                 ->rawColumns(['checkbox','details','id'])
                ->make(true);
        }
    }


    public function getCustomerOrderHistory(Request $request){
        $editData = OrderManagement::where('id',$request->order_id)->with('orderProductDetails')->where('seller_id',Auth::user()->id)->first();
        //$userDetails = User::where('id',Auth::user()->id)->first();
        if(isset($editData->shop_id)){
            $shopDetails = Shop::where('id',$editData->shop_id)->first();
        }

        $data = [];
        if(isset($editData->orderProductDetails) && count($editData->orderProductDetails)>0){
            $product_price = [];
            foreach($editData->orderProductDetails as $key=>$row)
            {

                // if(empty($row->discount_price) || $row->discount_price == NULL){
                if($row->discount_price == 0){
                    $product_price[$key]['price'] = $row->product->price;
                }
                else{
                    $product_price[$key]['price'] = $row->discount_price;
                }

                array_push($data,$row->product->product_code);
            }
        }
        Session::put('itemArray',$data);

        // for shipment products details
        if($request->shipment_id){
            $getShipmentsProductsDetails = ShipmentProduct::where('shipment_id',$request->shipment_id)
                            ->leftJoin('products', 'shipment_products.product_id', '=', 'products.id')
                            ->select('products.*', 'shipment_products.quantity as shipped_qty')
                            ->get();         
        }
        return view('seller.shipments.getCustomerOrderHistory', compact('editData', 'product_price', 'shopDetails', 'getShipmentsProductsDetails'));

    }

    public function getCustomerOrderHistoryForPack(Request $request){
        $editData = OrderManagement::where('id',$request->order_id)->with('orderProductDetails')->where('seller_id',Auth::user()->id)->first();
        //$userDetails = User::where('id',Auth::user()->id)->first();

        $data = [];
        if(isset($editData->orderProductDetails) && count($editData->orderProductDetails)>0){
            $product_price = [];
            foreach($editData->orderProductDetails as $key=>$row)
            {

                // if(empty($row->discount_price) || $row->discount_price == NULL){
                if($row->discount_price == 0){
                    $product_price[$key]['price'] = $row->product->price;
                }
                else{
                    $product_price[$key]['price'] = $row->discount_price;
                }

                array_push($data,$row->product->product_code);
            }
        }
        // for shipment products details
        if($request->shipment_id){
            $getShipmentsProductsDetails = ShipmentProduct::where('shipment_id',$request->shipment_id)
                            ->leftJoin('products', 'shipment_products.product_id', '=', 'products.id')
                            ->select('products.*', 'shipment_products.quantity as shipped_qty')
                            ->get();         
        }
        Session::put('itemArray',$data);
        return view('seller.shipments.getCustomerOrderHistoryForPack', compact('editData', 'product_price', 'getShipmentsProductsDetails'));
    }

    public function getCustomerOrderHistoryForDelete(Request $request){
        $editData = OrderManagement::where('id',$request->order_id)->with('orderProductDetails')->where('seller_id',Auth::user()->id)->first();
        //$userDetails = User::where('id',Auth::user()->id)->first();
        $shipmentDetails = Shipment::select('pack_status')->where('id',$request->shipment_id)->first();

        $data = [];
        if(isset($editData->orderProductDetails) && count($editData->orderProductDetails)>0){
            $product_price = [];
            foreach($editData->orderProductDetails as $key=>$row)
            {

                if(empty($row->discount_price) || $row->discount_price == NULL){
                    $product_price[$key]['price'] = $row->product->price;
                }
                else{
                    $product_price[$key]['price'] = $row->discount_price;
                }

                array_push($data,$row->product->product_code);
            }
        }
        Session::put('itemArray',$data);
        return view('seller.shipments.getCustomerOrderHistoryForDelete', compact('editData', 'product_price', 'shipmentDetails'));
    }

    public function printLevelPrint(Request $request){

        $shipment_id = $request->shipment_id_input_val;
        $editData = OrderManagement::where('id',$request->order_id_input_val)->with('orderProductDetails')->where('seller_id',Auth::user()->id)->first();

        if(isset($editData->orderProductDetails) && count($editData->orderProductDetails)>0){
            $product_price = [];
            foreach($editData->orderProductDetails as $key=>$row)
            {

                if(empty($row->discount_price) || $row->discount_price == NULL){
                    $product_price[$key]['price'] = $row->product->price;
                }
                else{
                    $product_price[$key]['price'] = $row->discount_price;
                }
            }
        }

        $userDetails = User::where('id',Auth::user()->id)->first();
        
        if(isset($editData->shop_id)){
            $shopDetails = Shop::where('id',$editData->shop_id)->first();
            if(isset($shopDetails->name)){
            $shopName = $shopDetails->name;
        }
            else{
                $shopName = '';
            }
        }

        
        if(isset($editData->channel_id)){
            $channelDetails = Channel::where('id',$editData->channel_id)->first();
            if(isset($channelDetails->name)){
            $channelName = $channelDetails->name;
        }
            else{
                $channelName = '';
            }
        }

        $order = OrderManagement::findOrFail($editData->id);
        $shippingMethod = '';
        foreach ($order->customer_shipping_methods as $customerShipping){
            if ($customerShipping->is_selected == CustomerShippingMethod::IS_SELECTED_YES){
                $shippingMethod = $customerShipping->shipping_cost->name;
            }
        }

        if($shipment_id){
            $getShipmentsProductsDetails = ShipmentProduct::where('shipment_id',$shipment_id)
                            ->leftJoin('products', 'shipment_products.product_id', '=', 'products.id')
                            ->select('products.*', 'shipment_products.quantity as shipped_qty')
                            ->get();         
        }

        $pdf = PDF::loadView('seller.shipments.order_print_level', compact('editData', 'userDetails', 'shipment_id', 'shopDetails', 'shopName', 'channelName', 'shippingMethod', 'getShipmentsProductsDetails', 'product_price'));

        //$pdf->setPaper('L', 'landscape');


        if($pdf){

            $shipments = Shipment::find($shipment_id);
            $shipments->print_date_time = Carbon::now()->format('Y-m-d H:i:s');
            $shipments->print_by = Auth::user()->id;
            $shipments->print_status = 1;
            $shipments->shipment_status = Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED;
            $result = $shipments->save();
        }

       return $pdf->download('order_print_level.pdf');
    }

    public function printLevelBulk(Request $request){
        $shipment_ids = $request->shipment_ids_input_array;
        $all_shipments = explode(',', $shipment_ids);
        $allShipmentData = Shipment::whereIn('id', $all_shipments)->orderBy('id', 'DESC')->get();

        $userDetails = User::where('id',Auth::user()->id)->first();
        // if(isset($editData->shop_id)){
        //     $shopDetails = Shop::where('id',$editData->shop_id)->first();
        // }

        $pdf = PDF::loadView('seller.shipments.order_print_level_bulk', compact('userDetails', 'allShipmentData'));
        if($pdf){
            foreach($allShipmentData as $allShipmentValue){
                $shipment = Shipment::find($allShipmentValue->id);
                $shipment->print_date_time = date('Y-m-d H:i:s');
                $shipment->print_status = Shipment::PRINT_STATUS_PRINTED;
                $shipment->print_by = Auth::user()->id;
                $shipment->shipment_status = Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED;
                $shipment->update();
            }
        }

        return $pdf->download('order_print_leve_bulk.pdf');
    }

    public function orderPrintLabelBulk(Request $request){
        $order_ids = $request->order_ids_input_array;
        $all_orders = explode(',', $order_ids);

        $allShipmentData = Shipment::whereIn('order_id', $all_orders)->orderBy('id', 'DESC')->get();

        $userDetails = User::where('id',Auth::user()->id)->first();

        $pdf = PDF::loadView('seller.shipments.order_print_level_bulk', compact('userDetails', 'allShipmentData'));

        if ($pdf){
            foreach ($allShipmentData as $shipment){
                if (empty($shipment->print_date_time)) {
                    $shipment->print_date_time = date('Y-m-d H:i:s');
                    $shipment->print_status = Shipment::PRINT_STATUS_PRINTED;
                    $shipment->print_by = Auth::user()->id;
                    $shipment->shipment_status = Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED;
                    $shipment->update();
                }
            }
        }
        return $pdf->download('order_print_leve_bulk.pdf');
    }

    public function updateShipmentStatus(Request $request){

        $shipments = Shipment::find($request->shipment_id);
        $shipments->pack_status = 1;
        //$shipments->shipment_status = Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED;
        $shipments->packed_date_time = Carbon::now()->format('Y-m-d H:i:s');
        $shipments->packed_by = Auth::user()->id;
        $result = $shipments->save();
        echo 'ok';
    }

    /**
     * Update shipment status to 'Shipped'
     *
     */
    public function updateShipmentStatusByUser(Request $request)
    {
        try {
            $shipmentId = $request->id;

            $shipment = Shipment::where('id', $shipmentId)->first();
            $shipment->shipment_status = $request->shipmentStatus;
            $shipment->update();

            return $this->apiResponse(Response::HTTP_OK, 'Data successfully updated.');

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Sorry, something went wrong. ' . $th->getMessage());
        }
    }

    public function deleteShipment(Request $request){

        Shipment::where('id',$request->shipment_id)->delete();
        echo 'ok';
    }


    public function getWOOCustomerOrderHistory(Request $request)
    {
        $orderDetails = WooOrderPurchase::where('order_id', $request->order_id)->where('seller_id',Auth::user()->id)->first();
        
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
        $arrProductImageWithID = $this->getProductDetailsByOrderID($isBulk='0',$orderDetails);
        $orderProductDetails = json_decode($orderDetails->line_items);
        if(isset($request->shop_id)){
            $shopDetails = WooShop::where('woo_shops.id',$request->shop_id)
                            ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
                            ->first();
        }

        $data = [];
        $product_price = [];
        //dd($shipping);    
        Session::put('itemArray',$data);
        return view('seller.shipments.getWCCustomerOrderHistory', compact('orderDetails', 'shipping','product_price', 'shopDetails','orderProductDetails','arrProductImageWithID'));
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
    private function getProductDetailsByOrderID($isBulk,$allOrders){
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
        
 
        $products = WooProduct::whereIn('product_id',$arr_product_id)->get();
        foreach ($products as $item) {
            $product_id = $item->product_id;
            if (!empty($item)) {
                $arr_product[$product_id] = $item;
            }
        }
        return $arr_product;
    }

    public function checkIfExistsShipmentId(Request $request){
        $shipment_id = $request->shipment_id;
        $shipment_for = $request->shipment_for;
        // $result = Shipment::checkIfExistsShipmentId($shipment_id);
        $result = Shipment::select('shipments.*', 'customers.customer_name')
                    ->leftJoin('order_managements', 'order_managements.id', '=', 'shipments.order_id')
                    ->leftJoin('customers', 'order_managements.customer_id', '=', 'customers.id')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.id', '=', $shipment_id)
                    ->where('shipments.shipment_for', '=', $shipment_for)
                    ->first();

        if(isset($result)){
            $getTotalItems = ShipmentProduct::where('shipment_products.shipment_id', '=', $request->shipment_id)
                    ->select('shipment_products.*')
                    ->get();
        
        if(!empty($result->shipment_status)){
        if($result->shipment_status == Shipment::SHIPMENT_STATUS_PENDING_STOCK){
            $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_PENDING_STOCK);
        }
        if($result->shipment_status == Shipment::SHIPMENT_STATUS_READY_TO_SHIP){
             $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_READY_TO_SHIP);
        }
        if($result->shipment_status == Shipment::SHIPMENT_STATUS_SHIPPED){
             $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_SHIPPED);
        }
        if($result->shipment_status == Shipment::SHIPMENT_STATUS_CANCEL){
             $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_CANCEL);
        }

        if($result->shipment_status == Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED){
            $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED);
                    }

        if($result->shipment_status == Shipment::SHIPMENT_STATUS_WOO_HOLD){
             $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_HOLD);
        }

        if($result->shipment_status == Shipment::SHIPMENT_STATUS_WOO_READY_TO_SHIP){
             $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_READY_TO_SHIP);
        }

        if($result->shipment_status == Shipment::SHIPMENT_STATUS_WOO_PROCESSING){
             $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_PROCESSING);
        }

        if($result->shipment_status == Shipment::SHIPMENT_STATUS_WOO_PENDING){
             $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_PENDING);
        }

        if($result->shipment_status == Shipment::SHIPMENT_STATUS_WOO_COMPLETED){
             $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_COMPLETED);
        }

        if($result->shipment_status == Shipment::SHIPMENT_STATUS_WOO_CANCEL){
             $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_WOO_CANCEL);
        }
     }
     else{
        $shipment_status = '';
     }

        $data = [
            'order_id' => $result->order_id,
            'customer_name' => $result->customer_name,
            'getTotalItems' => count($getTotalItems),
            'shipment_status' => $shipment_status
        ];
        return $data;
        }
        else{
            return __('translation.No Shipment Found');
        }
        
    }

    public function shipmentPickOrderCancel(Request $request){
        $shipments = Shipment::find($request->shipment_id);
        $shipments->pack_status = 0;
        $result = $shipments->save();
    }

    public function shipmentStatusUpdate(Request $request){
        if(!empty($request->shipment_status_update)){
            $shipments = Shipment::find($request->shipment_id);
            $shipments->shipment_status = $request->shipment_status_update;
            $result = $shipments->save(); 
        }
       
    }

    public function afterSearchModalContent(Request $request){
        $shipment_id = $request->shipment_id;
        $shipments = Shipment::select('shipments.*')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.id', '=', $shipment_id)
                    ->first();
        if(!empty($shipments->order_id)){
            $getShipmentsProductsDetails = ShipmentProduct::where('shipment_id',$request->shipment_id)
                     ->leftJoin('products', 'shipment_products.product_id', '=', 'products.id')
                     ->select('products.*', 'shipment_products.quantity')
                     ->get();
        }

        //dd($shipments);
        return view('seller.shipments.after_search_modal_content', compact('shipments', 'getShipmentsProductsDetails'));
    }

    public function afterSearchModalContentShipmentsProducts(Request $request){
        $shipment_id = $request->shipment_id;
        $use_for = $request->use_for;
        $shipments = Shipment::select('shipments.*')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.id', '=', $shipment_id)
                    ->first();
        if(!empty($shipments->id)){
         $editData = Shipment::where('id',$shipments->id)->with('WOOshipment_products')->where('seller_id',Auth::user()->id)->first();

        $data = [];
        if(isset($editData->WOOshipment_products) && count($editData->WOOshipment_products)>0){
            $product_shipped_quantity = [];
            foreach($editData->WOOshipment_products as $key=>$row)
            {

                $product_shipped_quantity[$key]['quantity'] = $row->quantity;

            }
        }
       }
        
        if($use_for == 'view'){
         return view('seller.shipments.woo.modal_shipment_products_for_woo', compact('shipments', 'editData', 'product_shipped_quantity'));

        }

        if($use_for == 'afterSearchview'){
        return view('seller.shipments.after_search_modal_content_for_woo', compact('shipments', 'editData', 'product_shipped_quantity'));

        }
    }


    public function testCheck(){
        WooWebhookSync::dispatch();
    }
    

    // for Shopee Orders
    private function getShopeeShopsWithProcessingOrdersCount($processingStatuses) {
        try {
            $shops = Shopee::where('seller_id', Auth::id())
                ->select('id','shop_name','shop_id','code')
                ->orderBy('shop_name', 'asc')
                ->get();  
            foreach($shops as $shop) {
                $shop["processing_orders_count"] = !empty($processingStatuses)?ShopeeOrderPurchase::getMultipleStatusSchemaCount($processingStatuses, $shop["id"]):0;
            }
            return $shops;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [];
    }

     public function checkIfExistsShipmentIdForWoo(Request $request){
        $shipment_id = $request->shipment_id;
        $shipment_for = $request->shipment_for;
        $result =   Shipment::select('shipments.*', 'shops.name', 'shops.logo', 'woo_order_purchases.shipping', 'woo_order_purchases.line_items', 'woo_order_purchases.shipping_lines', 'woo_order_purchases.website_id')
                    ->leftJoin('woo_order_purchases', 'woo_order_purchases.order_id', '=', 'shipments.order_id')
                    ->leftJoin('shops', 'woo_order_purchases.website_id', '=', 'shops.id')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.id', '=', $shipment_id)
                    ->where('shipments.shipment_for', '=', $shipment_for)
                    ->first();

        if(isset($result)){
            $getTotalItems = ShipmentProduct::where('shipment_products.shipment_id', '=', $request->shipment_id)
                    ->select('shipment_products.*')
                    ->get();
        
        if(!empty($result->shipment_status)){
        if($result->shipment_status == Shipment::SHIPMENT_STATUS_PENDING_STOCK){
            $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_PENDING_STOCK);
        }
        if($result->shipment_status == Shipment::SHIPMENT_STATUS_READY_TO_SHIP){
             $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_READY_TO_SHIP);
        }
        if($result->shipment_status == Shipment::SHIPMENT_STATUS_SHIPPED){
             $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_SHIPPED);
        }
        if($result->shipment_status == Shipment::SHIPMENT_STATUS_CANCEL){
             $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_CANCEL);
        }

        if($result->shipment_status == Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED){
            $shipment_status = Shipment::getShipmentStatusStr(Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED);
             }
        }
        else{
            $shipment_status = '';
        }

        $shipping = json_decode($result->shipping);
        
        if(isset($shipping)){
            $customerName = $shipping->first_name." ".$shipping->last_name;
        }
        else{
            $customerName = '';
        }

        $data = [
            'order_id' => $result->order_id,
            'customer_name' => $customerName,
            'getTotalItems' => count($getTotalItems),
            'shipment_status' => $shipment_status
        ];
        return $data;
        }
        else{
            return __('translation.No Shipment Found');
        }
        
    }

    public function getWooShipmentsProducts(Request $request){
        $shipment_id = $request->shipment_id;
        $shipments = Shipment::select('shipments.*')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.id', '=', $shipment_id)
                    ->first();
        if(!empty($shipments->id)){
         $editData = Shipment::where('id',$shipments->id)->with('WOOshipment_products')->where('seller_id',Auth::user()->id)->first();

        $data = [];
        if(isset($editData->WOOshipment_products) && count($editData->WOOshipment_products)>0){
            $product_shipped_quantity = [];
            foreach($editData->WOOshipment_products as $key=>$row)
            {

                $product_shipped_quantity[$key]['quantity'] = $row->quantity;

            }
        }
       }

       return view('seller.shipments.woo.shipment_products_for_woo', compact('shipments', 'editData', 'product_shipped_quantity'));
    }
}
