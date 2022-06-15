<?php

namespace App\Actions;

use App\Models\WooOrderPurchase;
use App\Models\Shipment;
use App\Models\ShipmentProduct;
use App\Models\WooShop;

use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ArrangeWCShipmentActionForOrder
{
    public function handle(array $data) : Shipment
    {
        try {
            DB::beginTransaction();

            $shipmentTable = (new Shipment())->getTable();
            $shipmentProductsTable = (new ShipmentProduct())->getTable();

            $shipment_date = null;
            if (!empty($data['shipment_date'])){
                $shipment_date = date('Y-m-d', strtotime($data['shipment_date']));
            }

         
           // dd($data['shipment_qty']);
            $shipmentId = 0;
            // if any array value not zero
            if(!(int)implode($data['shipment_qty'])==false){

                $shipmentId = DB::table($shipmentTable)
                    ->insertGetId([
                        'shipment_date' => $shipment_date,
                        'order_id' => $data['order_id'],
                        'shipment_status' => $data['shipment_status'],
                        'seller_id' => $data['seller_id'],
                        'is_custom' => $data['is_custom'],
                        'shipment_for' => $data['shipment_for'],
                        'shop_id' => $data['website_id'],
                        'created_at' => new DateTime()
                    ]);

                if(isset($data['product_id'])){
                    foreach ($data['product_id'] as $idx => $productId) {
                        $shipmentDetailsData = [
                            'shipment_id' => $shipmentId,
                            'order_id' => $data['order_id'],
                            'product_id' => $data['product_id'][$idx],
                            'quantity' => $data['shipment_qty'][$idx] ?? 0,
                            'created_at' => new DateTime()
                        ];

                        DB::table($shipmentProductsTable)->insert($shipmentDetailsData);
                    }
                }
            

            $ordered_details = WooOrderPurchase::where('website_id',$data['website_id'])->where('order_id',$data['order_id'])->where('seller_id',Auth::user()->id)->first();
            
            $ordered_details->status = WooOrderPurchase::ORDER_STATUS_PROCESSED;
            $ordered_details->updated_at = new DateTime();
            $ordered_details->save();
        }

            // UPDATE STATUS ON WOO SITE TOO
            // UPDATE STATUS ON WOO SITE TOO
            $website_id = $data['website_id'];
            $order_id = $data['order_id'];
            
            $shop = WooShop::where('id', $website_id)->get();
            
            foreach ($shop as $details) {
                $site_url = $details->site_url;
                $rest_api_key = $details->rest_api_key;
                $rest_api_secrete = $details->rest_api_secrete;
            }
            $url = $site_url . '/wp-json/wc/v3/orders/' . $order_id . '?consumer_key=' . $rest_api_key . '&consumer_secret=' . $rest_api_secrete;
            $headers = array(
                'Authorization' => 'Basic ' . base64_encode($rest_api_key.':'.$rest_api_secrete )
            );

            $data = array(
                'status' => WooOrderPurchase::ORDER_STATUS_READY_TO_SHIP,
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
            if($shipmentId){
                $shipment = Shipment::where('id', $shipmentId)->first();

                DB::commit();
    
                return $shipment;  
            }
            return Shipment::first();  
        } catch (\Throwable $th) {
            report($th);

            DB::rollBack();

            throw $th;
        }
    }
}
