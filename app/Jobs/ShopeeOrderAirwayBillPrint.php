<?php

namespace App\Jobs;

use Shopee\Client;
use App\Models\ShopeeOrderPurchase;
use App\Models\ShopeeSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use JsonException;

class ShopeeOrderAirwayBillPrint implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $shopee_shop_id;
    private $ordersn_list;
    private $is_batch;
    private $shopee_limit = 50;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopee_shop_id, $ordersn_list=[], $is_batch=false)
    {
        $this->shopee_shop_id = (int) $shopee_shop_id;
        $this->ordersn_list = $ordersn_list;
        $this->is_batch = $is_batch;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (sizeof($this->ordersn_list) == 0) {
            return;
        }
        $order_list = [];
        if (sizeof($this->ordersn_list) > $this->shopee_limit) {
            $order_list = array_slice($this->ordersn_list, 0, $this->shopee_limit);
            /* Dispatch job if more ordersn is passed then the expected limit. */
            if (isset($this->ordersn_list[$this->shopee_limit])) {
                ShopeeOrderAirwayBillPrint::dispatch($this->shopee_shop_id, array_slice($this->ordersn_list, $this->shopee_limit, sizeof($this->ordersn_list)))->delay(now()->addSeconds(5));
            }
        } else {
            $order_list = $this->ordersn_list;
        }
        $response = $this->getAirwayBillInfoFromShopee($order_list);
        if (isset($response) and isset($response["result"]) and isset($response["result"]["airway_bills"]) and sizeof($response["result"]["airway_bills"]) > 0) {
            $this->insertDataInDatabase($response["result"]["airway_bills"]);
        }
    }


    /**
     * Store the airway bill url in database.
     */
    private function insertDataInDatabase($airway_bills) 
    {
        foreach($airway_bills as $airway_bill) {
            if (isset($airway_bill["ordersn"], $airway_bill["airway_bill"]) and !empty($airway_bill["airway_bill"])) {
                $order_purchase = ShopeeOrderPurchase::whereOrderId($airway_bill["ordersn"])->first();
                if (isset($order_purchase)) {
                    $order_purchase->awb_printed_at = date("Y-m-d H:i:s", time());
                    $order_purchase->awb_url = $airway_bill["airway_bill"];
                    $order_purchase->save();
                }
            }
        }
    }


    /**
     * Get airway bill info from Shopee.
     */
    private function getAirwayBillInfoFromShopee($order_list) 
    {
        $client = $this->getShopeeClient($this->shopee_shop_id);
        if (isset($client)) {
            return $client->logistics->getAirwayBill([
                'ordersn_list' => $order_list,
                'is_batch'     => $this->is_batch
            ])->getData();
        }
        return null;
    }


    /**
     * Get the "Shopee" client to communicate with the api.
     */
    private function getShopeeClient($shopee_shop_id) 
    {
        $shopee_setting = ShopeeSetting::first();
        if (isset($shopee_setting)) {
            return new Client([
                'baseUrl' => $shopee_setting->host,
                'secret' => $shopee_setting->parent_key,
                'partner_id' => (int) $shopee_setting->parent_id,
                'shopid' => (int) $shopee_shop_id,
                'timestamp' => time()
            ]);
        }
        return null;
    }
}
