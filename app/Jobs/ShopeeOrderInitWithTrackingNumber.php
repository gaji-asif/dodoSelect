<?php

namespace App\Jobs;

use App\Models\OrderPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Shopee\Client;
use App\Models\ShopeeSetting;
use App\Models\ShopeeOrderPurchase;
use App\Traits\ShopeeOrderPurchaseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ShopeeOrderInitWithTrackingNumber implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeOrderPurchaseTrait;
    private $shopee_shop_id;
    private $ordersn;
    private $tracking_no;
    private $auth_id;
    private $shopee_setting;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopee_shop_id, $ordersn, $tracking_no, $auth_id)
    {
        $this->shopee_shop_id = (int) $shopee_shop_id;
        $this->ordersn = $ordersn;
        $this->tracking_no = $tracking_no;
        $this->auth_id = $auth_id;
        $this->shopee_setting = ShopeeSetting::first();
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $orderPurchase = ShopeeOrderPurchase::whereOrderId($this->ordersn)->first();
            if (isset($orderPurchase)) {
                $response = $this->init()->getData();
                /* Check if response is valid. */
                if (isset($response["request_id"])) {
                    /* Check if any sort of error was send by shopee. Then ignore. */
                    if(!isset($response["error_param"]) and !isset($response["error_params"]) and !isset($response["error_not_exist"]) and
                        !isset($response["error_not_found"]) and !isset($response["error_permission"]) and !isset($response["error_server"]) and
                        !isset($response["error_unknown"]) and !isset($response["lack_of_invoice_data"]) and !isset($response["error_auth"])) {
                        if (isset($response["tracking_number"]) and !empty($response["tracking_number"])) {
                            $orderPurchase->tracking_number = $response["tracking_number"];
                        }

                        /* custom_status for filter */
                        $orderPurchase->status_custom = strtolower(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB);

                        /* Selected shipping method and params for shopee */
                        $orderPurchase->shopee_shipping_method = strtolower(ShopeeOrderPurchase::SHIPPING_METHOD_DROPOFF);
                        $orderPurchase->shopee_shipping_method_params = json_encode([
                            'tracking_no' => $this->tracking_no
                        ]);

                        $orderPurchase->shipped_on_date = date("Y-m-d H:i:s", time());
                        $orderPurchase->save();

                        $this->putShopeeOrderProcessingRelatedInCacheForTrackingInit($this->ordersn, "completed");
                        return;
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        $this->putShopeeOrderProcessingRelatedInCacheForTrackingInit($this->ordersn, "failed");

        /* Check if this is the last order for bulk batch init for multiple shops. */
        if ($this->matchLastLastShopeeOrderIdForBulkInitLock($this->ordersn, $this->auth_id)) {
            $this->removetLastShopeeOrderIdForBulkInitLock($this->auth_id);
            $this->removeLockForShopeeOrderBulkInit($this->auth_id);
        }
    }


    /**
     * Get the order details.
     *
     * @return object
     */
    private function init() {
        $client = new Client([
            'baseUrl' => $this->shopee_setting->host,
            'secret' => $this->shopee_setting->parent_key,
            'partner_id' => (int) $this->shopee_setting->parent_id,
            'shopid' => $this->shopee_shop_id
        ]);

        return $client->logistics->init(
            [
                'ordersn' => $this->ordersn,
                'dropoff' => [
                    'tracking_no' => $this->tracking_no
                ]
            ]
        );
    }


    private function putShopeeOrderProcessingRelatedInCacheForTrackingInit($ordersn, $status="processing")
    {
        try {
            if (isset($ordersn) and !empty($ordersn)) {
                Cache::put($this->getKeyPrefixForShopeeTrackingInit().$ordersn, $status, Carbon::now()->addMinutes(30));
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function removeShopeeOrderProcessingRelatedInCacheForTrackingInit($ordersn)
    {
        try {
            if (isset($ordersn) and !empty($ordersn)) {
                if (Cache::has($this->getKeyPrefixForShopeeTrackingInit().$ordersn)) {
                    Cache::forget($this->getKeyPrefixForShopeeTrackingInit().$ordersn);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function getKeyPrefixForShopeeTrackingInit()
    {
        return "shopee_init_".$this->auth_id."_";
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
