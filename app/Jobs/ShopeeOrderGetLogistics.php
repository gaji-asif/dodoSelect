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

class ShopeeOrderGetLogistics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $shopee_shop_id;
    private $ordersn;
    private $param;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopee_shop_id, $ordersn, $param="tracking_number")
    {
        $this->shopee_shop_id = (int) $shopee_shop_id;
        $this->ordersn = $ordersn;
        $this->param = $param;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = $this->getOrderLogisticInfoFromShopee($this->ordersn);
        if (isset($response["request_id"])) {
            if (!isset($response["error"]) and !isset($response["error_param"]) and !isset($response["error_status"]) and !isset($response["error_not_exist"]) and
                !isset($response["error_not_found"]) and !isset($response["error_permission"]) and !isset($response["error_server"]) and
                !isset($response["error_unknown"]) and !isset($response["lack_of_invoice_data"]) and !isset($response["error_auth"])) {
                if (isset($response["logistics"])) {
                    $this->insertDataInDatabase($response["logistics"]);
                }
            }
        }
    }


    /**
     * Store the airway bill url in database.
     */
    private function insertDataInDatabase($logistics)
    {
        try {
            if (isset($logistics)) {
                $order_purchase = ShopeeOrderPurchase::whereOrderId($this->ordersn)->first();
                if (isset($order_purchase)) {
                    if ($this->param == "tracking_number") {
                        if (isset($logistics["tracking_no"]) and !empty($logistics["tracking_no"])) {
                            $order_purchase->tracking_number = $logistics["tracking_no"];
                        }
                    }
                    $order_purchase->save();
                }
            }
        } catch (\Exception $exception) {
            Log::error("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get airway bill info from Shopee.
     */
    private function getOrderLogisticInfoFromShopee($order_list)
    {
        try {
            $client = $this->getShopeeClient($this->shopee_shop_id);
            if (isset($client)) {
                return $client->logistics->getOrderLogistics([
                    'ordersn' => $order_list
                ])->getData();
            }
        } catch (\Exception $exception) {
            Log::error("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    /**
     * Get the "Shopee" client to communicate with the api.
     */
    private function getShopeeClient($shopee_shop_id)
    {
        try {
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
        } catch (\Exception $exception) {
            Log::error("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            "Shop:{$this->shopee_shop_id}"
        ];
    }
}
