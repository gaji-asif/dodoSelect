<?php

namespace App\Http\Controllers;

use App\Jobs\ShopeeGetParameterForInitForOrder;
use App\Jobs\ShopeeOrderAirwayBillPrint;
use App\Jobs\ShopeeOrderDetailSync;
use App\Jobs\ShopeeOrderPurchaseUpdateViaWebhook;
use App\Models\Shopee;
use App\Models\ShopeeOrderPurchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShopeeWebhookController extends Controller
{
    /**
     * Handle shopee webhook for order status update.
     *
     * @param Request $request
     */
    public function handleShopeeOrderWebhook(Request $request) {
        try {
            if (isset($request->data, $request->shop_id, $request->data["ordersn"]) and !empty($request->shop_id) and !empty($request->data["ordersn"])) {
                ShopeeOrderPurchaseUpdateViaWebhook::dispatch((int)$request->shop_id, $request->data);
                return;
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        Log::error(__("shopee.order.handle_order_webhook.failed"));
    }
}
