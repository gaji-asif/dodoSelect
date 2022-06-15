<?php

namespace App\Jobs;

use Shopee\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Shopee;
use App\Models\ShopeeOrderPurchase;
use App\Models\ShopeeOrderParamInit;
use App\Models\ShopeeSetting;

class ShopeeGetParameterForInitForOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $ordersn, $shopee_shop_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ordersn, $shopee_shop_id)
    {
        $this->ordersn = $ordersn;
        $this->shopee_shop_id = (int) $shopee_shop_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->getLogisticInfoFromShopee();
    }

    /**
     * Get logistic info for a specific order.
     */
    public function getLogisticInfoFromShopee()
    {
        try {
            $orderPurchase = ShopeeOrderPurchase::whereOrderId($this->ordersn)->first();
            if (!isset($orderPurchase)) {
                Log::error(__("shopee.no_such_order"));
                return;
            }

            $shopee_shop = Shopee::whereShopId($this->shopee_shop_id)->first();
            if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                Log::error(__("translation.Shop Not Found"));
                return;
            }

            if ($shopee_shop->id !== $orderPurchase->website_id) {
                Log::error(__("shopee.shopee_id_not_match"));
                return;
            }

            $client = $this->getShopeeClient();
            if (!isset($client)) {
                Log::error(__("shopee.shopee_client_failed"));
                return;
            }

            $response = $client->logistics->getParameterForInit([
                'ordersn'    => $this->ordersn
            ])->getData();

            if (isset($response["request_id"])) {
                $pickup_json_data = "";
                $dropoff_json_data = "";
                if (isset($response["error"], $response["msg"])) {
                    if (strpos($response["msg"], "cancelled") !== false || strpos($response["msg"], "allocated") !== false) {
                        $pickup_json_data = json_encode(["address_id","pickup_time_id"]);
                    }
                } else {
                    if (isset($response["pickup"])) {
                        $pickup_json_data = json_encode($response["pickup"]);
                    }
                    if (isset($response["dropoff"])) {
                        $dropoff_json_data = json_encode($response["dropoff"]);
                    }
                }

                if (!empty($pickup_json_data) || !empty($dropoff_json_data)) {
                    /* Remove old data. */
                    ShopeeOrderParamInit::whereOrdersn($this->ordersn)->delete();

                    /* Store the data in database. */
                    $init_param = new ShopeeOrderParamInit();
                    $init_param->ordersn = $this->ordersn;
                    $init_param->pickup = $pickup_json_data;
                    $init_param->dropoff = $dropoff_json_data;
                    $init_param->save();
                }
            }
        } catch (\Exception $exception) {
            Log::error("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get the "Shopee" client to communicate with the api.
     */
    private function getShopeeClient()
    {
        $shopee_setting = ShopeeSetting::first();
        if (isset($shopee_setting)) {
            return new Client([
                'baseUrl' => $shopee_setting->host,
                'secret' => $shopee_setting->parent_key,
                'partner_id' => (int) $shopee_setting->parent_id,
                'shopid' => $this->shopee_shop_id,
                'timestamp'  => time()
            ]);
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
