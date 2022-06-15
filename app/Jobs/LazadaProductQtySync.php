<?php

namespace App\Jobs;

use App\Exceptions\LazadaApiException;
use App\Models\Lazada;
use App\Models\LazadaProduct;
use App\Models\LazadaSetting;
use App\Models\Product;
use Bmatovu\LaravelXml\LaravelXml;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Lazada\LazopClient;
use Lazada\LazopRequest;

class LazadaProductQtySync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = true;

    /** @var LazadaSetting */
    private $lazadaSetting;

    /** @var Lazada */
    private $lazadaShop;

    /** @var LazadaProduct */
    private $lazadaProduct;

    /** @var Product */
    private $dodoProduct;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        LazadaSetting $lazadaSetting,
        Lazada $lazadaShop,
        LazadaProduct $lazadaProduct,
        Product $dodoProduct
    ) {
        $this->lazadaSetting = $lazadaSetting;
        $this->lazadaShop = $lazadaShop;
        $this->lazadaProduct = $lazadaProduct;
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
            $lazadaClient = new LazopClient(
                $this->lazadaSetting->regional_host,
                $this->lazadaSetting->app_id,
                $this->lazadaSetting->app_secret
            );

            $dodoProductQty = $this->dodoProduct->getQuantity->quantity ?? 0;

            $payload = LaravelXml::encode([
                'Product' => [
                    'Skus' => [
                        [
                            'ItemId' => $this->lazadaProduct->parent_id,
                            'SkuId' => $this->lazadaProduct->product_id,
                            'SellerSku' => $this->lazadaProduct->product_code,
                            'Quantity' => $dodoProductQty
                        ]
                    ]
                ]
            ], 'Request');

            $lazadaRequest = new LazopRequest('/product/price_quantity/update', 'POST');
            $lazadaRequest->addApiParam('payload', $payload);

            $accessToken = json_decode($this->lazadaShop->response)->access_token ?? '';

            $response = $lazadaClient->execute($lazadaRequest, $accessToken);
            $decodedResponse = json_decode($response);

            if ($decodedResponse->code == '0') {
                $this->lazadaProduct->quantity = $dodoProductQty;
                $this->lazadaProduct->update();
            } else {
                throw new LazadaApiException($decodedResponse->message, $decodedResponse->code);
            }

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
            "Shop:{$this->lazadaShop->id}"
        ];
    }
}
