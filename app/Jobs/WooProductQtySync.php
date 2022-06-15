<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\WooProduct;
use App\Models\WooShop;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WooProductQtySync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = true;

    /** @var WooShop */
    private $wooShop;

    /** @var WooProduct */
    private $wooProduct;

    /** @var Product */
    private $dodoProduct;

    /**
     * Create a new job instance.
     *
     * @param  WooShop  $wooShop
     * @param  WooProduct  $wooProduct
     * @param  Product  $dodoProduct
     * @return void
     */
    public function __construct(
        WooShop $wooShop,
        WooProduct $wooProduct,
        Product $dodoProduct
    ) {
        $this->wooShop = $wooShop;
        $this->wooProduct = $wooProduct;
        $this->dodoProduct = $dodoProduct;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $wooCommerce = new Client(
                $this->wooShop->site_url,
                $this->wooShop->rest_api_key,
                $this->wooShop->rest_api_secrete,
                [
                    'version' => 'wc/v3'
                ]
            );

            $apiEndPoint = "/products/{$this->wooProduct->product_id}";
            if (WooProduct::isVariation($this->wooProduct)) {
                $apiEndPoint = "/products/{$this->wooProduct->parent_id}/variations/{$this->wooProduct->product_id}";
            }

            $dodoProductQty = $this->dodoProduct->getQuantity->quantity ?? 0;

            /** @var string */
            $response = $wooCommerce->put($apiEndPoint, [
                'manage_stock' => true,
                'stock_quantity' => $dodoProductQty,
            ]);

            $decodedResponse = json_decode($response);

            if (isset($decodedResponse->id)) {
                $this->wooProduct->quantity = $dodoProductQty;
                $this->wooProduct->update();
            }

        } catch (HttpClientException $e) {
            report($e);
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return [
            "Shop:{$this->wooShop->id}"
        ];
    }
}
