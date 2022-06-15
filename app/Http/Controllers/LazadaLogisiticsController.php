<?php

namespace App\Http\Controllers;

use App\Models\Lazada;
use App\Models\LazadaShipmentProvider;
use App\Traits\LazadaOrderPurchaseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Lazada\LazopRequest;

class LazadaLogisiticsController extends Controller
{
    use LazadaOrderPurchaseTrait;

    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Get shipment providers for Lazada from database.
     */
    public function getShipmentProvidersFromDatabase(Request $request)
    {
        $data = [];
        try {
            if ($request->ajax()) {
                if (isset($request->website_id) and !empty($request->website_id)) {
                    $lazada_providers = LazadaShipmentProvider::whereWebsiteId($request->website_id)->orderBy('name', 'asc')->get();
                } else {
                    $lazada_providers = LazadaShipmentProvider::get();
                }
                if (sizeof($lazada_providers) > 0) {
                    foreach($lazada_providers as $provider) {
                        array_push($data, [
                            "name"  => $provider->name
                        ]);
                    }
                    return response()->json([
                        "success"   => true,
                        "data"      => [
                            "shipment_providers" => $data
                        ],
                        "message"   => __("translation.Succssefully retrieved shipement provider info.")
                    ]);
                } else {
                    $this->getShipmentProviders();
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "data"      => [
                "shipment_providers" => $data
            ],
            "message"   => __("translation.Falied to update shipping providers for lazada shops.")
        ]);
    }


    /**
     * Fetch shipement providers from Lazada and update database.
     */
    public function getShipmentProviders()
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
                                    LazadaShipmentProvider::whereWebsiteId($shop->id)->whereName($shipment_provider->name)->delete();
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


    /**
     * Get the request object for lazada to fetch the shipment providers.
     */
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
