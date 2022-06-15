<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class ShipmentProduct extends Model
{
    use HasFactory;
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')
            ->withDefault()
            ->with('getQuantity');
    }


    public function woo_product()
    {
        return $this->belongsTo(WooProduct::class, 'product_id', 'product_id')
            ->withDefault();
    }



    public static function getallShipmentsProductsByShopIdShipmentId($shop_id,$shipment_id){
        $data = DB::select(DB::raw("SELECT 
                shipment_products.*,
                woo_products.website_id,
                woo_products.product_id,
                woo_products.images,
                woo_products.product_name,
                woo_products.product_code,
                woo_products.price
                FROM `shipment_products`
                LEFT JOIN shipments ON shipments.id=shipment_products.shipment_id
                LEFT JOIN woo_products ON woo_products.product_id = shipment_products.product_id
                WHERE shipments.shop_id=$shop_id AND shipments.id=$shipment_id
                GROUP BY shipment_products.`product_id`,`variation_id`
            "));

        return $data;
    }
    

        public static function getArrayShipmentTotalByShopIdShipmentId($shop_id,$shipment_id){

            $allShipmentsProducts = DB::select(DB::raw("SELECT 
            shipment_products.*,
            woo_products.website_id,
            woo_products.product_id,
            woo_products.images,
            woo_products.product_name,
            woo_products.product_code,
            woo_products.price
            FROM `shipment_products`
            LEFT JOIN shipments ON shipments.id=shipment_products.shipment_id
            LEFT JOIN woo_products ON woo_products.product_id = shipment_products.product_id
            WHERE shipments.shop_id=$shop_id AND shipments.id=$shipment_id
            GROUP BY shipment_products.`product_id`,`variation_id`
        "));

            $arr_total = array();
            foreach($allShipmentsProducts as $row){
                $arr_total[] = $row->price * $row->quantity;
            }
            return $arr_total;
    
        }


        public static function getallShipmentsProductsByShopIdOrderID($shop_id,$order_id){

            $allShipmentsProducts = ShipmentProduct::select("shipments.*",
            "shipment_products.*",
            "woo_products.website_id",
            "woo_products.product_id",
            "woo_products.images",
            "woo_products.product_name",
            "woo_products.product_code",
            "woo_products.price"       
            )
            ->join("shipments","shipments.id","=","shipment_products.shipment_id")
                ->join("woo_products",function($join){
                    $join->on("woo_products.website_id","=","shipments.shop_id")
                        ->on("woo_products.product_id","=","shipment_products.product_id");
                })
                ->join("woo_order_purchases",function($join){
                    $join->on("woo_order_purchases.website_id","=","shipments.shop_id")
                        ->on("woo_order_purchases.order_id","=","shipments.order_id");
                })
                ->where('shop_id', $shop_id)
                ->where('shipments.order_id', $order_id)
                ->orderBy('shipment_products.id', 'asc')
                ->get();
    
            return $allShipmentsProducts;
    
            }





}
