<?php

namespace App\Traits\Inventory;

use App\Models\Lazada;
use App\Models\Shopee;
use App\Models\WooShop;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait ShopTrait
{
    public function getShopDetailsFromDatabase($data=[]) {
        $shopee_shops = [];
        $lazada_shops = [];
        $woo_shops = [];
        try {
            if (isset($data["shopee_key"]) and $data["shopee_key"] == "shop_id") {
                $shopee_shops = Shopee::pluck("shop_name", "shop_id");
            } else {
                $shopee_shops = Shopee::pluck("shop_name", "id");
            }
            $lazada_shops = Lazada::pluck("shop_name", "id");
            $shops = WooShop::where('woo_shops.seller_id', Auth::user()->id)
                ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
                ->select('woo_shops.id','shops.name','shop_id')
                ->get();
            foreach ($shops as $shop) {
                $woo_shops[$shop->id] = $shop->name;
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [    
            "shopee"        => $shopee_shops,
            "lazada"        => $lazada_shops,
            "woo_commerce"  => $woo_shops
        ];
    }


    public function getShopInfoByPlatformAndId($platform, $id, $seller_id)
    {
        try {
            if ($platform == "shopee") {
                return Shopee::find($id);
            } else if ($platform == "lazada") {
                return Lazada::find($id);
            } else if ($platform == "woo_commerce") {
                return WooShop::where('shop_id', '=', $id)
                    ->where('woo_shops.seller_id', $seller_id)
                    ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
                    ->select('woo_shops.id','shops.name AS shop_name','shop_id')
                    ->first();
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * @param $platform
     * @return string
     */
    private function getPlatformDisplayName($platform)
    {
        if ($platform == "shopee") {
            return "Shopee";
        } else if ($platform == "woo_commerce") {
            return "Woo Commerce";
        } else if ($platform == "lazada") {
            return "Lazada";
        }
        return $platform;
    }
}