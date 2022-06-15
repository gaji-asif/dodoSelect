<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Shopee\Client;
use App\Models\ShopeeSetting;
use App\Models\ShopeeOrderParamInit;
use App\Models\ShopeeOrderPurchase;

class ShopeeOrderInitWithAddressIdInBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $shopee_shop_id;
    private $address_id;
    private $time_slot_id;
    private $ordersn_list;
    private $shopee_setting;
    private $shopee_limit = 150;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopee_shop_id, $address_id, $time_slot_id, $ordersn_list)
    {
        $this->shopee_shop_id = (int)$shopee_shop_id;
        $this->address_id = $address_id;
        $this->time_slot_id = $time_slot_id;
        $this->ordersn_list = $ordersn_list;
        $this->shopee_setting = ShopeeSetting::first();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = $this->batchInit()->getData();
        if (isset($response["success_list"])) {
            $this->updateDatabase($response["success_list"]);
        }
    }


    private function updateDatabase($success_order_list) {
        foreach($success_order_list as $success_order) {
            if (isset($success_order["ordersn"])) {
                if (isset($success_order["package_number"]) and !empty($success_order["package_number"])) {
                    $shopeeParamInt = ShopeeOrderParamInit::whereOrdersn($success_order["ordersn"])->first();
                    if (isset($shopeeParamInt)) {
                        $shopeeParamInt->package_number = $success_order["package_number"];
                        $shopeeParamInt->save();
                    }
                }
                $shopeeOrderPurchase = ShopeeOrderPurchase::whereOrderId($success_order["ordersn"])->first();
                if (isset($shopeeOrderPurchase)) {

                    /* custom_status for filter */
                    $shopeeOrderPurchase->status_custom = strtolower(ShopeeOrderPurchase::ORDER_STATUS_READY_TO_SHIP_AWB);

                    $shopeeOrderPurchase->shipped_on_date = date("Y-m-d H:i:s", time());
                    $shopeeOrderPurchase->save();
                }
            }
        }
    }


    /**
     *
     * @return object
     */
    private function batchInit() {
        $client = new Client([
            'baseUrl' => $this->shopee_setting->host,
            'secret' => $this->shopee_setting->parent_key,
            'partner_id' => (int) $this->shopee_setting->parent_id,
            'shopid' => $this->shopee_shop_id,
            'timestamp' => time()
        ]);

        return $client->logistics->batchInit(
            [
                'order_list' => $this->getGFormatedOrdersnListForParam(),
                'pickup' => [
                    'address_id' => $this->address_id,
                    'pickup_time_id' => $this->time_slot_id
                ]
            ]
        );
    }


    private function getGFormatedOrdersnListForParam() {
        $list = [];
        foreach($this->ordersn_list as $ordersn) {
            array_push($list, [
                "ordersn" => $ordersn
            ]);
        }
        return $list;
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
