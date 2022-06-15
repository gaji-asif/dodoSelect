<?php

namespace App\Http\Controllers;

use App\Models\Shopee;
use App\Traits\ShopeeOrderPurchaseTrait;
use App\Traits\ShopeeProductTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopeeLogisticsController extends Controller
{
    use ShopeeOrderPurchaseTrait, ShopeeProductTrait;

    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Get pickup address info from "Shopee".
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogisticsFromShopee(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->shopee_shop_id) and !empty($request->shopee_shop_id)) {
                $shopee_shop = Shopee::whereShopId($request->shopee_shop_id)->first();
                if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Shop Not Found")
                    ]);
                }

                $client = $this->getShopeeClient($shopee_shop->shop_id);
                if (isset($client)) {
                    $response = $client->logistics->getLogistics([
                        'timestamp' => time()
                    ])->getData();
    
                    if (isset($response["logistics"])) {
                        return response()->json([
                            "success"   => true,
                            "data"      => $response["logistics"]
                        ]);
                    } else if (isset($response["msg"])) {
                        return response()->json([
                            "success"   => true,
                            "message"   => $response["msg"],
                            "data"      => []
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => "Failed to fetch logistics info"
        ]);
    }
}
