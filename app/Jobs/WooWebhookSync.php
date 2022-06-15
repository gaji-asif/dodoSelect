<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WooShop;
use Illuminate\Support\Facades\Http;
use App\Jobs\WooWebhookChildSync;


class WooWebhookSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $shops = WooShop::orderBy('id', 'desc')->get();
         if(!empty($shops)){
            foreach ($shops as $key=>$shop) {
               
                // call shop API for fetch the webhooks
                $url = $shop->site_url . '/wp-json/wc/v3/webhooks?consumer_key=' . $shop->rest_api_key . '&consumer_secret=' . $shop->rest_api_secrete;

                WooWebhookChildSync::dispatch($url, $shop);
               
            }
        }
    }
}