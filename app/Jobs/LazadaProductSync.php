<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Lazada\LazopClient;
use Lazada\LazopRequest;

class LazadaProductSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $shop_settings;
    private $access_token;
    private $website_id;
    private $number_of_products;
    private $auth_id;

    /**
     * Create a new job instance.
     * @param $shop_settings
     * @param $access_token
     * @param $website_id
     * @param $number_of_products
     * @param $auth_id
     */
    public function __construct($shop_settings, $access_token, $website_id, $number_of_products, $auth_id)
    {
        $this->shop_settings = $shop_settings;
        $this->access_token = $access_token;
        $this->website_id = $website_id;
        $this->number_of_products = $number_of_products;
        $this->auth_id = $auth_id;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        $lazadaSetting = $this->shop_settings;
        $client = new LazopClient(
            $lazadaSetting->regional_host,
            $lazadaSetting->app_id,
            $lazadaSetting->app_secret
        );

        $offset = Cache::pull('lazada_last_index_'.$this->auth_id);
        $total_products = Cache::get('lazada_nop_'.$this->auth_id);

        if($offset < $total_products):
            $lazadaRequest = new LazopRequest('/products/get','GET');
            $lazadaRequest->addApiParam('filter','live');
            $lazadaRequest->addApiParam('offset',$offset);
            $lazadaRequest->addApiParam('limit','50');
            $lazadaRequest->addApiParam('options','1');
            $response = $client->execute($lazadaRequest, $this->access_token);
            LazadaProductBatchSync::dispatch($response, $this->website_id, $this->auth_id);
            Cache::add('lazada_last_index_'.$this->auth_id, ((int) $offset)+50);

            $new_total = $this->number_of_products - 50;
            LazadaProductSync::dispatch($this->shop_settings, $this->access_token, $this->website_id,
                $new_total, $this->auth_id)->delay(now()->addSeconds(5));
        endif;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            "Shop:{$this->website_id}"
        ];
    }
}
