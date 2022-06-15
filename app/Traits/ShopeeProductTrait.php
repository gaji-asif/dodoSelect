<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait ShopeeProductTrait
{
    public function getShopeePortalLinkForProduct($country="th")
    {
        if ($country == "th") {
            return "https://seller.shopee.co.th/portal/product";
        }
        return "https://seller.shopee.co.th/portal/product";
    }
}