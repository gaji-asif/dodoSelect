<?php
namespace App\Traits;

use App\Models\Shopee;
use App\Models\ShopeeSetting;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Shopee\Client;

trait ShopeeTrait
{
    /**
     * Get the seller id.
     */
    public function getShopeeSellerId() 
    {
        try {
            return Auth::id();
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return 0;
    }


    /**
     * Get the "Shopee" client to communicate with the api.
     */
    public function getShopeeClient($shopee_shop_id) 
    {
        try {
            $shopee_setting = ShopeeSetting::first();
            if (isset($shopee_setting)) {
                return new Client([
                    'baseUrl'       => $shopee_setting->host,
                    'secret'        => $shopee_setting->parent_key,
                    'partner_id'    => (int) $shopee_setting->parent_id,
                    'shopid'        => (int) $shopee_shop_id,
                    'timestamp'     => Carbon::now()->valueOf(), //time(),
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    /**
     * This will be used for dispatching job to fetch "awb_url" for orders which have a "tracking_id" but no "awb_url". 
     */
    public function getShopIdOfSpecificShop($id) 
    {
        try {
            $shopee_shops = Shopee::pluck("shop_id", "id");
            if (isset($id, $shopee_shops[$id])) {
                return $shopee_shops[$id];
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    /**
     * Get shopee shop using "website_id".
     * NOTE:
     * Here "website_id" refers to "id" in "shopees" table.
     */
    public function getShopeeShopBasedOnId($id)
    {
        try {
            if (!empty($id)) {
                $shopee_shop = Shopee::find($id);
                if (isset($shopee_shop)) {
                    return $shopee_shop;
                }   
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    /**
     * Get shopee shop using "website_id".
     * NOTE:
     * Here "website_id" refers to "id" in "shopees" table.
     */
    public function getShopeeShopBasedOnShopId($website_id)
    {
        try {
            if (!empty($website_id)) {
                $shopee_shop = Shopee::whereShopId($website_id)->first();
                if (isset($shopee_shop)) {
                    return $shopee_shop;
                }   
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }
}