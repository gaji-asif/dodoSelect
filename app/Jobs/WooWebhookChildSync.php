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
use App\Jobs\WooWebhookSubChildSync;

class WooWebhookChildSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $webhook_api_url;
    protected $shop;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($webhook_api_url, $shop)
    {
        $this->webhook_api_url = $webhook_api_url;
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = Http::withOptions([
            'verify' => false,
        ])
        ->get($this->webhook_api_url);

        if (!empty($response)) {

            $webhooks = json_decode($response, true);
            foreach ($webhooks as $webhook) {
                if($webhook['status'] != 'active'){

                    $data = [
                        "status" => "active"
                    ];

                    $updateUrl = $this->shop['site_url'] . '/wp-json/wc/v3/webhooks/'.$webhook['id'].'?consumer_key=' . $this->shop['rest_api_key'] . '&consumer_secret=' . $this->shop['rest_api_secrete'];

                    WooWebhookSubChildSync::dispatch($updateUrl, $data);
                    
                }

            }
        }
    }
}