<?php

namespace App\Jobs;

use App\Models\Shopee;
use App\Models\User;
use App\Traits\LineBotTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckAuthorizationExpirationForShops implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LineBotTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /* Check authorization valid for "Shopee" shops. */
        /* Get sellers from "shopees" table. */
        $shopee_sellers = $this->getUniqueUsersFromShopeeTable();
        foreach ($shopee_sellers as $id => $seller_id) {
            /* Get shopee shops. */
            $shopee_shops = Shopee::whereSellerId($seller_id)->get();
            $message = "";
            foreach ($shopee_shops as $shop) {
                $token_updated_at = $shop["token_updated_at"];
                if (!isset($token_updated_at)) {
                    $token_updated_at = $shop["updated_at"];
                    $shop->token_updated_at = $shop["updated_at"];
                    $shop->save();
                }

                $diff = Carbon::now()->diffInDays(Carbon::parse($token_updated_at));
                if ($diff == 0) {
                    $message .= "Token already expired. Please authorize token for \"".$shop->shop_name."\".\n";
                } else if ($diff <= 10) {
                    $message .= "Token will expire in $diff days. Please update authorization for \"".$shop->shop_name."\" before token expires.\n";
                }
            }

            /**
             * Send notification.
             */
            if (!empty($message)) {
                $user = User::find($seller_id);
                $message_prefix = "Authorization token expiration notification for \"".$user->email."\".";
                $message = $message_prefix."\n".$message;

                $this->sendNotification($message);
            }
        }
    }


    /**
     * Get "seller_id" from "shopees" table.
     */
    private function getUniqueUsersFromShopeeTable()
    {
        try {
            return Shopee::pluck("seller_id", "id")->unique();
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [];
    }


    private function sendNotification($message) 
    {
        try {
            $this->triggerPushMessage($message);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }
}
