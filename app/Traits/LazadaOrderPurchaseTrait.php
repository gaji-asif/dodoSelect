<?php

namespace App\Traits;

use App\Models\Lazada;
use App\Models\LazadaSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Lazada\LazopClient;
use Lazada\LazopRequest;

trait LazadaOrderPurchaseTrait
{
    /**
     * Get the seller id.
     */
    public function getLazadaSellerId() 
    {
        try {
            return Auth::id();
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return 0;
    }


    /**
     * Get access token to communicate with "Lazada".
     * 
     * @param integer $lazada_shop_id
     */
    public function getAccessTokenForLazada($lazada_shop_id=0)
    {
        try {
            if ($lazada_shop_id > 0) {
                $shop = Lazada::find($lazada_shop_id);
                return (isset($shop->response) and !empty($shop->response) and $this->isJson($shop->response))?json_decode($shop->response)->access_token:"";
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return "";
    }


    /**
     * Get the "Lazada" client to communicate with the api.
     */
    public function getLazadaClient() 
    {
        try {
            $lazada_setting = LazadaSetting::first();
            if (isset($lazada_setting, $lazada_setting->regional_host, $lazada_setting->app_id, $lazada_setting->app_secret) and
                !empty($lazada_setting->regional_host) and !empty($lazada_setting->app_id) and !empty($lazada_setting->app_secret)) {
                return new LazopClient($lazada_setting->regional_host, $lazada_setting->app_id, $lazada_setting->app_secret);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    /**
     * Check if json.
     * 
     * @param string $string
     */
    public function isJson($string) 
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }


    public function allowedShipingMethodsForLazada() 
    {
        return ["dropship"];
    }


    public function putLazadaOrderProcessingRelatedInCacheForTrackingInit($ordersn, $status, $auth_id) 
    {
        try {    
            if (isset($ordersn) and !empty($ordersn)) {
                Cache::put($this->getKeyPrefixForLazadaTrackingInit($auth_id).$ordersn, $status, Carbon::now()->addMinutes(3));
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    public function getLazadaOrderProcessingRelatedInCacheForTrackingInit($ordersn, $auth_id) 
    {
        try {    
            if (isset($ordersn) and !empty($ordersn)) {
                if (Cache::has($this->getKeyPrefixForLazadaTrackingInit($auth_id).$ordersn)) {
                    return Cache::get($this->getKeyPrefixForLazadaTrackingInit($auth_id).$ordersn);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    public function removeLazadaOrderProcessingRelatedInCacheForTrackingInit($ordersn, $auth_id) 
    {
        try {    
            if (isset($ordersn) and !empty($ordersn)) {
                if (Cache::has($this->getKeyPrefixForLazadaTrackingInit($auth_id).$ordersn)) {
                    Cache::forget($this->getKeyPrefixForLazadaTrackingInit($auth_id).$ordersn);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    public function getKeyPrefixForLazadaTrackingInit($auth_id) 
    {
        return "lazada_init_".$auth_id."_";
    }
}