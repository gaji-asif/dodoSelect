<?php

namespace App\Jobs;

use App\Models\Lazada;
use App\Models\LazadaShipmentProvider;
use App\Traits\LazadaOrderPurchaseTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Lazada\LazopRequest;

class LazadaSyncShipmentProviders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use LazadaOrderPurchaseTrait;

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
        try {
            $lazada_shops = Lazada::get();
            foreach($lazada_shops as $shop) {
                $access_token = $this->getAccessTokenForLazada($shop->id);
                if (!empty($access_token)) {
                    $client = $this->getLazadaClient();
                    $obj = $this->getRequestObjectToGetShipmentProviders();
                    if (isset($client, $obj)) {
                        $response = $client->execute($obj, $access_token);
                        if (isset($response) and $this->isJson($response)) {
                            $data = json_decode($response);
                            if (isset($data->data, $data->data->shipment_providers)) {
                                foreach ($data->data->shipment_providers as $shipment_provider) {
                                    /* Remove old data. */
                                    LazadaShipmentProvider::whereWebsiteId($shop->id)->whereName($shipment_provider->name)->delete();
                                    /* Insert new data. */
                                    $new_shipment_provider = new LazadaShipmentProvider();
                                    $new_shipment_provider->name = $shipment_provider->name;
                                    $new_shipment_provider->is_default = $shipment_provider->is_default;
                                    $new_shipment_provider->tracking_code_example = isset($shipment_provider->tracking_code_example)?$shipment_provider->tracking_code_example:null;
                                    $new_shipment_provider->enabled_delivery_options = isset($shipment_provider->enabled_delivery_options)?$shipment_provider->enabled_delivery_options:null;
                                    $new_shipment_provider->cod = isset($shipment_provider->cod)?$shipment_provider->cod:null;
                                    $new_shipment_provider->tracking_code_validation_regex = isset($shipment_provider->tracking_code_validation_regex)?$shipment_provider->tracking_code_validation_regex:null;
                                    $new_shipment_provider->tracking_url = isset($shipment_provider->tracking_url)?$shipment_provider->tracking_url:null;
                                    $new_shipment_provider->api_integration = isset($shipment_provider->api_integration)?$shipment_provider->api_integration:0;
                                    $new_shipment_provider->website_id = $shop->id;
                                    $new_shipment_provider->save();
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function getRequestObjectToGetShipmentProviders($params=[]) 
    {
        try {
            $request = new LazopRequest('/shipment/providers/get', 'GET');
            return $request;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }
}
