<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ShopeeSetting;
use App\Jobs\ShopeeOrderDetailSync;
use App\Traits\LineBotTrait;
use App\Traits\ShopeeOrderPurchaseTrait;
use Illuminate\Support\Facades\Log;

class ShopeeOrderSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopeeOrderPurchaseTrait, LineBotTrait;
    private $page;
    private $per_page;
    private $website_id;
    private $auth_id;
    private $shopee_shop_id;
    private $shopee_setting;
    private $number_of_orders;
    private $sync_type;
    private $shopee_limit = 100;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($page, $per_page, $website_id, $shopee_shop_id, $auth_id, $number_of_orders=-1, $sync_type="manual")
    {
        if ($page < 0) {
            $this->page = 0;
        } else {
            $this->page = $page;
        }
        /* Api request limit per_page is 100. */
        if ($per_page > $this->shopee_limit) {
            $this->per_page = $this->shopee_limit;
        } else {
            $this->per_page = $per_page;
        }
        $this->website_id = (int) $website_id;
        $this->shopee_shop_id = (int) $shopee_shop_id;
        $this->auth_id = $auth_id;
        if ($number_of_orders < -1) {
            $this->number_of_orders = -1;
        } else {
            $this->number_of_orders = $number_of_orders;
        }
        $this->sync_type = $sync_type;
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
            $response = $this->getOrderList()->getData();
            if (isset($response['orders'])) {
                $shopee_orders_list = $response['orders'];
                if (sizeof($shopee_orders_list) > 0) {
                    $ordersn_list = [];
                    /* Fetch only the "ordersn". "ordersn" bahaves same as "order_id" for "Shopee". */
                    foreach($shopee_orders_list as $order) {
                        array_push($ordersn_list, $order['ordersn']);
                    }
                    /* Fetch order details in bulk. */
                    ShopeeOrderDetailSync::dispatch($this->shopee_shop_id, $this->auth_id, $this->website_id, $ordersn_list);
                }
                if (isset($response['more'])) {
                    $has_more = $response['more'];
                    if ($has_more) {
                        /* if there are 355 orders first time this job is called $page=0, for second time $page=100 and so on. */
                        $this->page += $this->shopee_limit;
                        /* -1 means to fetch all the orders for a specified date range mentioned in api client object. */
                        if ($this->number_of_orders != -1) {
                            /* if there are 355 orders "number_of_orders" after first job call will be 255, for second call 155 and so on. */
                            $this->number_of_orders -= $this->per_page;
                            if ($this->number_of_orders < $this->per_page) {
                                $this->per_page = $this->number_of_orders;
                            }
                        }
                        /* Again trigger the job for fetching more order list from "Shopee". */
                        ShopeeOrderSync::dispatch($this->page, $this->per_page, $this->website_id, $this->shopee_shop_id, $this->auth_id, $this->number_of_orders, $this->sync_type)->delay(now()->addSeconds(120));
                    }
                }
            } else {
                $this->triggerPushMessage("Failed to start syncing orders from \"Shopee\"");
            }
        } catch (\Exception $exception) {
            $this->triggerPushMessage("Failed to start syncing orders from \"Shopee\".");
        }
    }


    /**
     * Get order list from "Shopee".
     * @return object
     */
    private function getOrderList() {
        $client = $this->getShopeeClient($this->shopee_shop_id);
        if (!isset($client)) {
            return [];
        }

        $timestamp = time();
        if ($this->sync_type == "auto") {
            return $client->order->getOrdersList([
                'update_time_from'  => strtotime('-1 hour, -30 minutes', $timestamp),
                'update_time_to'    => $timestamp,
                'pagination_offset' => $this->page,
                'pagination_entries_per_page' => $this->per_page,
                'partner_id'        => (int) $this->shopee_setting->parent_id,
                'shopid'            => $this->shopee_shop_id,
                'timestamp'         => $timestamp
            ]);
        }
        return $client->order->getOrdersList([
            'create_time_from'      => strtotime('-10 day', $timestamp),
            'create_time_to'        => $timestamp,
            'pagination_offset'     => $this->page,
            'pagination_entries_per_page' => $this->per_page,
            'partner_id'            => (int) $this->shopee_setting->parent_id,
            'shopid'                => $this->shopee_shop_id,
            'timestamp'             => $timestamp
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            "Shop:{$this->website_id}"
        ];
    }
}
