<?php

namespace App\Models;
use DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class Shipment extends Model
{
    use HasFactory;

    /**
     * Define `shipment status` field value
     *
     * @var mixed
     */
    CONST SHIPMENT_STATUS_PENDING_STOCK = 10;
    CONST SHIPMENT_STATUS_READY_TO_SHIP = 11;
    CONST SHIPMENT_STATUS_SHIPPED = 12;
    CONST SHIPMENT_STATUS_CANCEL = 13;
    CONST SHIPMENT_STATUS_READY_TO_SHIP_PRINTED = 14;

    CONST SHIPMENT_STATUS_WOO_HOLD = 15;
    CONST SHIPMENT_STATUS_WOO_READY_TO_SHIP = 16;
    CONST SHIPMENT_STATUS_WOO_PROCESSING = 17;
    CONST SHIPMENT_STATUS_WOO_PENDING = 18;
    CONST SHIPMENT_STATUS_WOO_COMPLETED = 19;
    CONST SHIPMENT_STATUS_WOO_CANCEL = 20;
 

    /**
     * Define `print_status` field value
     *
     * @var mixed
     */
    CONST PRINT_STATUS_NOT_PRINT = 0;
    CONST PRINT_STATUS_PRINTED = 1;

    /**
     * Define `pack_status` field value
     *
     * @var mixed
     */
    CONST PACK_STATUS_NOT_PACK = 0;
    CONST PACK_STATUS_PACKED = 1;

    /**

     * Define `Shipment For` field value
     *
     * @var mixed
     */

    CONST SHIPMENT_FOR_DODO = 1;
    CONST SHIPMENT_FOR_DROPSIPPER = 2;
    CONST SHIPMENT_FOR_SHOPEE = 3;
    CONST SHIPMENT_FOR_LAZADA = 4;
    CONST SHIPMENT_FOR_WOO = 5;


    /**
     * Relationship to `wooPurchaseOrder`
     *
     * @return mixed
     */
    public function wooPurchaseOrder()
    {
        return $this->hasMany(WooOrderPurchase::class, 'order_id', 'order_id');
    }

    /**
     * Relationship to `shipment_products` with the `products` table
     *
     * @return mixed
     */
    public function shipment_products()
    {
        return $this->hasMany(ShipmentProduct::class, 'shipment_id', 'id')->with('product');
    }


    /**
     * Relationship to `shipment_products` with the `products` table
     *
     * @return mixed
     */
    public function WOOshipment_products()
    {
        return $this->hasMany(ShipmentProduct::class, 'shipment_id', 'id')->with('woo_product');
    }







    /**
     * Relationship to `users` table
     *
     * @return mixed
     */
    public function printer()
    {
        return $this->belongsTo(User::class, 'print_by', 'id')->withDefault();
    }

       /**
     * Relationship to `users` table
     *
     * @return mixed
     */
    public function shipper()
    {
        return $this->belongsTo(User::class, 'mark_as_shipped_by', 'id')->withDefault();
    }


    /**
     * Relationship to `users` table
     *
     * @return mixed
     */
    public function packer()
    {
        return $this->belongsTo(User::class, 'packed_by', 'id')->withDefault();
    }

    /**
     * Get all `shipment_status`
     *
     * @return array
     */
    public static function getAllShipmentStatus()
    {
        return [
            self::SHIPMENT_STATUS_PENDING_STOCK => 'Waiting For Stock',
            self::SHIPMENT_STATUS_READY_TO_SHIP => 'Ready To Ship',
            self::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED => 'Ready To Ship (Printed)',
            self::SHIPMENT_STATUS_SHIPPED => 'Shipped',
            self::SHIPMENT_STATUS_CANCEL => 'Cancelled',
        ];
    }

    /**
     * @param $shipment_status
     * @return string
     */
    public static function getShipmentStatusStr($shipment_status){
        $shipment_status_text = '';
        if($shipment_status == self::SHIPMENT_STATUS_PENDING_STOCK){
            $shipment_status_text = 'WAITING FOR STOCK';
        }
        if($shipment_status == self::SHIPMENT_STATUS_READY_TO_SHIP){
            $shipment_status_text = 'READY TO SHIP';
        }
        if($shipment_status == self::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED){
            $shipment_status_text = 'READY TO SHIP (PRINTED)';
        }
        if($shipment_status == self::SHIPMENT_STATUS_SHIPPED){
            $shipment_status_text = 'SHIPPED';
        }
        if($shipment_status == self::SHIPMENT_STATUS_CANCEL){
            $shipment_status_text = 'CANCELLED';
        }
        if($shipment_status == self::SHIPMENT_STATUS_WOO_HOLD){
            $shipment_status_text = 'HOLD';
        }
        if($shipment_status == self::SHIPMENT_STATUS_WOO_READY_TO_SHIP){
            $shipment_status_text = 'READY TO SHIP';
        }
        if($shipment_status == self::SHIPMENT_STATUS_WOO_PROCESSING){
            $shipment_status_text = 'PROCESSING';
        }
        if($shipment_status == self::SHIPMENT_STATUS_WOO_PENDING){
            $shipment_status_text = 'PENDING';
        }
        if($shipment_status == self::SHIPMENT_STATUS_WOO_COMPLETED){
            $shipment_status_text = 'COMPLETED';
        }
        if($shipment_status == self::SHIPMENT_STATUS_WOO_CANCEL){
            $shipment_status_text = 'CANCELLED';
        }

        return $shipment_status_text;
    }

    public static function getOrderFromTo($order_id){
        $orderAddress = DB::table('order_managements')
                    ->leftJoin('shops', 'order_managements.shop_id', '=', 'shops.id')
                    ->select('order_managements.*', 'shops.name as shop_name', 'shops.phone as shop_phone', 'shops.address as shop_address', 'shops.district as shop_district', 'shops.sub_district as shop_sub_district', 'shops.province as shop_province', 'shops.postcode as shop_postcode')
                    ->where('order_managements.id', '=', $order_id)
                    ->first();

        if(isset($orderAddress->shop_id)){
            $shopDetails = Shop::where('id',$orderAddress->shop_id)->first();
            if(isset($shopDetails->name)){
            $shopName = $shopDetails->name;
        }
            else{
                $shopName = '';
            }
        }

        if(isset($orderAddress->channel_id)){
            $channelDetails = Channel::where('id',$orderAddress->channel_id)->first();
            if(isset($channelDetails->name)){
            $channelName = $channelDetails->name;
        }
            else{
                $channelName = '';
            }
        }

        $order = OrderManagement::findOrFail($orderAddress->id);
        $shippingMethod = '';
        foreach ($order->customer_shipping_methods as $customerShipping){
            if ($customerShipping->is_selected == CustomerShippingMethod::IS_SELECTED_YES){
                $shippingMethod = $customerShipping->shipping_cost->name;
            }
        }

        $data = [
            'orderAddres'=>$orderAddress,
            'shopName'=>$shopName,
            'channelName'=>$channelName,
            'shippingMethod'=>$shippingMethod
        ];

        return $data;

    }

     public static function getOrderDetails($order_id, $shipment_id){

        $editData = OrderManagement::where('id',$order_id)->with('orderProductDetails')->where('seller_id',Auth::user()->id)->first();

        if($shipment_id){
            $getShipmentsProductsDetails = ShipmentProduct::where('shipment_id',$shipment_id)
                            ->leftJoin('products', 'shipment_products.product_id', '=', 'products.id')
                            ->select('products.*', 'shipment_products.quantity as shipped_qty')
                            ->get();         
        }

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

        $data = [
            'editData' => $editData,
            'getShipmentsProductsDetails' => $getShipmentsProductsDetails,
            'shopName'=>$shopName,
            'channelName'=>$channelName,
            'shippingMethod'=>$shippingMethod
        ];

        return $data;
     }

     public function shipmentProductDetails()
     {
        return $this->hasMany(ShipmentProduct::class, 'shipment_id', 'id')->with('product');
     }

     public static function getallShipmentsProducts($shipment_id){
        if(!empty($shipment_id)){
             $allShipmentsProducts = DB::table('shipments')
                    ->leftjoin('shipment_products', 'shipment_products.shipment_id', '=', 'shipments.id')
                    ->leftjoin('products', 'shipment_products.product_id', '=', 'products.id')
                    ->select('shipments.id', 'shipment_products.product_id', 'shipments.shipment_date', 'shipment_products.ordered_qty', 'shipment_products.quantity', 'products.*')
                    ->where('shipments.id', $shipment_id)
                    ->get();
             return $allShipmentsProducts;
        }


     }

     public static function getShipmentDataStatusWise($status, $shipment_for){
        $today = Carbon::today()->toDateString();
        if($status == 'today'){
            
            $data = Shipment::select('shipments.*', 'shops.name', 'shops.logo', 'customers.customer_name','channels.name as channnel_name', 'channels.image as channnel_image')
            ->leftJoin('order_managements', 'order_managements.id', '=', 'shipments.order_id')
            ->leftJoin('shops', 'order_managements.shop_id', '=', 'shops.id')
            ->leftJoin('customers', 'order_managements.customer_id', '=', 'customers.id')
            ->leftJoin('channels', 'order_managements.channel_id', '=', 'channels.id')
            ->whereDate('shipments.shipment_date',$today)
            ->where('shipments.seller_id', '=', Auth::user()->id)
            ->where('shipments.shipment_for', '=', $shipment_for)
            ->get();
        }
        if($status == 'late'){
            $data = Shipment::select('shipments.*', 'shops.name', 'shops.logo', 'customers.customer_name', 'channels.name as channnel_name', 'channels.image as channnel_image')
            ->leftJoin('order_managements', 'order_managements.id', '=', 'shipments.order_id')
            ->leftJoin('shops', 'order_managements.shop_id', '=', 'shops.id')
            ->leftJoin('customers', 'order_managements.customer_id', '=', 'customers.id')
            ->leftJoin('channels', 'order_managements.channel_id', '=', 'channels.id')
            ->whereDate('shipments.shipment_date','<',$today)
            ->where('shipments.seller_id', '=', Auth::user()->id)
            ->where('shipments.shipment_for', '=', $shipment_for)
            ->get();
        }
        return $data;
     }

     public static function getShipmentDataShipStatusWise($shipment_status, $shipment_for){
        $data = Shipment::select('shipments.*', 'shops.name', 'shops.logo', 'customers.customer_name', 'channels.name as channnel_name', 'channels.image as channnel_image')
                ->leftJoin('order_managements', 'shipments.order_id', '=', 'order_managements.id')
                ->leftJoin('shops', 'order_managements.shop_id', '=', 'shops.id')
                ->leftJoin('customers', 'order_managements.customer_id', '=', 'customers.id')
                ->leftJoin('channels', 'order_managements.channel_id', '=', 'channels.id')
                ->where('shipments.seller_id', '=', Auth::user()->id)
                ->where('shipments.shipment_for', '=', $shipment_for)
                ->where('shipments.shipment_status', '=', $shipment_status)
                ->get();
        return $data;
     }

     public static function getWooShipmentDataShipStatusWise($shipment_status, $shipment_for){
        $data = Shipment::select('shipments.*', 'shops.name', 'shops.logo', 'woo_order_purchases.shipping', 'woo_order_purchases.line_items', 'woo_order_purchases.shipping_lines', 'woo_order_purchases.website_id')
                    ->leftJoin('woo_order_purchases', 'woo_order_purchases.order_id', '=', 'shipments.order_id')
                    ->leftJoin('shops', 'woo_order_purchases.website_id', '=', 'shops.id')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.shipment_for', '=', $shipment_for)
                    ->where('shipments.shipment_status', '=', $shipment_status)
                    ->get();

            return $data;
     }

     public static function getShipmentDataShipNoWise($shipment_no, $shipment_for){
        $data = Shipment::select('shipments.*', 'shops.name', 'shops.logo', 'customers.customer_name', 'channels.name as channnel_name', 'channels.image as channnel_image')
                ->leftJoin('order_managements', 'shipments.order_id', '=', 'order_managements.id')
                ->leftJoin('shops', 'order_managements.shop_id', '=', 'shops.id')
                ->leftJoin('customers', 'order_managements.customer_id', '=', 'customers.id')
                ->leftJoin('channels', 'order_managements.channel_id', '=', 'channels.id')
                ->where('shipments.seller_id', '=', Auth::user()->id)
                ->where('shipments.id', '=', $shipment_no)
                ->where('shipments.shipment_for', '=', $shipment_for)
                ->get();
        return $data;
     }

     public static function getWooShipmentDataShipNoWise($shipment_no, $shipment_for){
        $data = Shipment::select('shipments.*', 'shops.name', 'shops.logo', 'woo_order_purchases.shipping', 'woo_order_purchases.line_items', 'woo_order_purchases.shipping_lines', 'woo_order_purchases.website_id')
                    ->leftJoin('woo_order_purchases', 'woo_order_purchases.order_id', '=', 'shipments.order_id')
                    ->leftJoin('shops', 'woo_order_purchases.website_id', '=', 'shops.id')
                ->where('shipments.seller_id', '=', Auth::user()->id)
                ->where('shipments.id', '=', $shipment_no)
                ->where('shipments.shipment_for', '=', $shipment_for)
                ->get();
        return $data;
     }

    
    public static function checkIfExistsShipmentId($value){
        $result =  Shipment::where('id',$value)->first();
        if(isset($result)){
            return $result;
        }
        else{
            return 0;
        }
    }

    public static function getAllShipmentsCount($shipment_for){
        $result =  Shipment::where('shipment_for',$shipment_for)->get();
        return $result;
    }
    
    public static function getActionBy($actionBy){
        $result =  User::where('id',$actionBy)->first();
        return $result->name;
    }





        /**
     * By multiple status ids from datatable
     *
     * @param  Builder  $query
     * @param  string|null  $orderStatuses
     * @return Builder
     */
    public function scopeByShipmentStatus($query, $orderStatuses = null)
    {
        
        $shipmentTable = (new Shipment())->getTable();
        $wooOrderPurchaseTable = (new ShipmentProduct())->getTable();


        if (!empty($orderStatuses)) {
            $splittedStatuses = explode(',', $orderStatuses);
            if(in_array(Shipment::SHIPMENT_STATUS_READY_TO_SHIP,$splittedStatuses) 
            || in_array(Shipment::SHIPMENT_STATUS_SHIPPED,$splittedStatuses)
            || in_array(Shipment::SHIPMENT_STATUS_READY_TO_SHIP_PRINTED,$splittedStatuses)
            || in_array(Shipment::SHIPMENT_STATUS_PENDING_STOCK,$splittedStatuses)
            || in_array(Shipment::SHIPMENT_STATUS_CANCEL,$splittedStatuses)
            ){
                return $query->whereIn('shipment_status', $splittedStatuses);
            }else{
                return $query->whereIn('status', $splittedStatuses);
            }
            
            
        }

        return;
    }

    /**
     * Query to search by from order datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string|null  $keyword
     */
    public function scopeSearchDataTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            //$wooPurchaseOrderTable = $this->getTable();
            $wooPurchaseOrderTable = (new WooOrderPurchase())->getTable();
            $shipmentTable = (new Shipment())->getTable();
            $shopsTable = (new Shop())->getTable();

            return $query->where(function (Builder $order) use ($shipmentTable, $wooPurchaseOrderTable, $shopsTable, $keyword) {
                $order->where("{$shipmentTable}.order_id", 'like', "%$keyword%")
                    ->orWhere("{$wooPurchaseOrderTable}.total", 'like', "%$keyword%")
                    //->orWhere('payment_method_title', 'like', "%$keyword%")
                    ->orwhere("{$shipmentTable}.id", 'like', "%$keyword%")
                    ->orWhere("{$wooPurchaseOrderTable}.billing->first_name", 'like', "%$keyword%")
                    ->orWhere("{$wooPurchaseOrderTable}.billing->last_name", 'like', "%$keyword%")
                    ->orWhere("{$shopsTable}.name", 'like', "%$keyword%");
            });

        }

        return;
    }


   /**
     * Relationship to `woo_order_purchases` table
     *
     * @return mixed
     */
    public function wooOrder()
    {
        return $this->hasOne(WooOrderPurchase::class, 'order_id','order_id')->withDefault([ 'id' => 0, 'order_date' => null ]);
    }



       /**
     * Relationship to `users` table
     *
     * @return mixed
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'seller_id', 'id')->withDefault();
    }

    /**
     * Relationship to `woo order purchase table` table
     *
     * @return mixed
     */
    public function wooOrderPurchaseTable()
    {
        return $this->belongsTo(WooOrderPurchase::class, 'order_id', 'order_id')->withDefault();
    }

    
        /**
     * Join query for the datatable
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeJoinedTable($query)
    {
        $shipmentTable = $this->getTable();
        $wooShopsTable = (new WooShop())->getTable();
        $shopsTable = (new Shop())->getTable();
        $wooPurchaseOrderTable = (new WooOrderPurchase())->getTable(); 

        return $query->join("{$wooShopsTable}", "{$wooShopsTable}.id", '=', "{$shipmentTable}.shop_id")
            ->join("{$shopsTable}", "{$shopsTable}.id", '=', "{$wooShopsTable}.shop_id")
            ->join("{$wooPurchaseOrderTable}", "{$wooPurchaseOrderTable}.order_id", '=', "{$shipmentTable}.order_id");
    }


    public static function getShipmentDataForOrder($order_id, $is_custom){
        $data = Shipment::select('shipments.*', 'shops.name', 'shops.logo', 'customers.customer_name', 'channels.name as channnel_name', 'channels.image as channnel_image')
                    ->leftJoin('order_managements', 'order_managements.id', '=', 'shipments.order_id')
                    ->leftJoin('shops', 'order_managements.shop_id', '=', 'shops.id')
                    ->leftJoin('customers', 'order_managements.customer_id', '=', 'customers.id')
                    ->leftJoin('channels', 'order_managements.channel_id', '=', 'channels.id')
                    ->where('shipments.seller_id', '=', Auth::user()->id)
                    ->where('shipments.order_id', '=', $order_id)
                    ->where('shipments.is_custom', '=', $is_custom)
                    ->get();
        return $data;
     }



}
