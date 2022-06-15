<?php

namespace App\Traits;

use App\Models\ShopeeOrderPurchase;
use App\Models\LazadaOrderPurchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait MarketplaceOrderTabNavigationTrait
{
    private function getSellerId() {
        return Auth::id();
    }

    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function getTotalProcessingOrdersForWooCommerce()
    {
        return 0;
    }

    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function getTotalProcessingOrdersForShopee()
    {
        try {
            return ShopeeOrderPurchase::getVerifiedOrderSchemaCount(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return 0;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function getTotalProcessingOrdersForLazada()
    {
        try {
            return LazadaOrderPurchase::getVerifiedOrderSchemaCount(LazadaOrderPurchase::ORDER_STATUS_PROCESSING);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return 0;
    }
}
