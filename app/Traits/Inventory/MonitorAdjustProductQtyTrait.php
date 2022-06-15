<?php

namespace App\Traits\Inventory;

use App\Models\InventoryProductsStockUpdateErrorLog;
use App\Models\Lazada;
use App\Models\Shopee;
use App\Models\WooShop;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\isJson;

/**
 * Trait MonitorAdjustProductQtyTrait is to check whether inventory qty for a product should be update or not.
 * Also to log failed tries from marketplaces
 * @package App\Traits\Inventory
 */
trait MonitorAdjustProductQtyTrait
{
    use PlatformTrait;

    private $error_message_mapqt = "";

    public function shouldUpdateProductQty($response, $tag, $data=[])
    {
        try {
            if (!in_array($tag, [
                $this->getTagForShopeePlatform(),
                $this->getTagForLazadaPlatform(),
                $this->getTagForWooCommercePlatform()
            ])) {
                return false;
            }

            if (is_string($response)) {
                if (empty($response)) {
                    return false;
                }
                if (isJson($response)) {
                    $response = json_decode($response, true);
                }
            } else if (isJson($response)) {
                if (!is_array($response)) {
                    $response = json_decode($response, true);
                }
            }

            if ($tag == $this->getTagForLazadaPlatform()) {
                if (isset($response["code"])) {
                    if ($response["code"] == 0) {
                        return true;
                    }
                    if (!empty($response["code"]) ) {
                        $this->setErrorMessageForMonitoringInventoryProductQtyUpdate($response["code"]);
                    }
                }

                if (empty($this->getErrorMessageForMonitoringInventoryProductQtyUpdate())) {
                    if (isset($response["message"])) {
                        $this->setErrorMessageForMonitoringInventoryProductQtyUpdate($response["message"]);
                    }
                }
            } else if ($tag == $this->getTagForShopeePlatform()) {
                if (isset($response["item"])) {
                    return true;
                }

                $this->setErrorMessageForLogForInventoryQtyShopee($response);
            } else if ($tag == $this->getTagForWooCommercePlatform()) {
                if (isset($response["id"])) {
                    return true;
                }

                $this->setErrorMessageForLogForInventoryQtyWooCommerce($response);
            }

            if (empty($this->getErrorMessageForMonitoringInventoryProductQtyUpdate())) {
                $this->setErrorMessageForMonitoringInventoryProductQtyUpdate("Failed to process json response");
            }
        } catch (\Exception $exception) {
            $this->setErrorMessageForMonitoringInventoryProductQtyUpdate("Failed (error in ".$exception->getLine().") to process json response");
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return false;
    }


    /**
     *
     * Set error message to be stored in database for failure of updating product quantity in Shopee.
     */
    private function setErrorMessageForLogForInventoryQtyShopee($response)
    {
        if (isset($response["error"])) {
            $this->setErrorMessageForMonitoringInventoryProductQtyUpdate($response["error"]);
        }
        if (isset($response["error_param"])) {
            $this->setErrorMessageForMonitoringInventoryProductQtyUpdate($response["error_param"]);
        }
        if (isset($response["error_auth"])) {
            $this->setErrorMessageForMonitoringInventoryProductQtyUpdate($response["error_auth"]);
        }
        if (isset($response["error_item_uneditable"])) {
            $this->setErrorMessageForMonitoringInventoryProductQtyUpdate($response["error_item_uneditable"]);
        }
        if (isset($response["msg"])) {
            $this->setErrorMessageForMonitoringInventoryProductQtyUpdate($response["msg"]);
        }
        if (isset($response["message"])) {
            $this->setErrorMessageForMonitoringInventoryProductQtyUpdate($response["message"]);
        }
    }


    /**
     * Set error message to be stored in database for failure of updating product quantity in Woo Commerce.
     */
    private function setErrorMessageForLogForInventoryQtyWooCommerce($response)
    {
        if (isset($response["code"]) and !empty($response["code"])) {
            $this->setErrorMessageForMonitoringInventoryProductQtyUpdate($response["code"]);
        }
        if (isset($response["message"]) and !empty($response["message"])) {
            $this->setErrorMessageForMonitoringInventoryProductQtyUpdate($response["message"]);
        }
    }


    /**
     * @return string
     */
    public function getErrorMessageForMonitoringInventoryProductQtyUpdate()
    {
        return $this->error_message_mapqt;
    }


    /**
     * @param string $message
     */
    public function setErrorMessageForMonitoringInventoryProductQtyUpdate($message)
    {
        $this->error_message_mapqt .= empty($this->error_message_mapqt)?$message:"\n".$message;
    }


    public function resetErrorMessageForMonitoringInventoryProductQtyUpdate()
    {
        $this->error_message_mapqt = "";
    }


    /**
     * Enter new error log.
     *
     * @param object $product // Read the NOTE section
     * @param integer $quantity
     * @param string $tag
     *
     * NOTE:
     * Here "dodo_product_id" refers to "product_id" in either
     * "shopee_products" or "lazada_products" or "woo_products" table.
     */
    public function createNewErrorLogForMonitoringAdjustInventoryProductQty($product, $quantity, $tag)
    {
        try {
            if (!in_array($tag, [
                $this->getTagForShopeePlatform(),
                $this->getTagForLazadaPlatform(),
                $this->getTagForWooCommercePlatform()
            ])) {
                return;
            }

            $platform_no = $this->getPlatformNo($tag);

            /* Remove old error log */
            $this->deleteOldErrorLogForMonitoringAdjustInventoryProductQty($product->id, $platform_no, $product->website_id);

            $obj = new InventoryProductsStockUpdateErrorLog();
            $obj->product_name = $product->product_name;
            $obj->product_code = $product->product_code;
            $obj->quantity = $quantity;
            $type = $product->type;
            if (isset($type)) {
                $obj->type = $type;
                if ($type == "simple") {
                    $obj->product_id = isset($product->product_id)?$product->product_id:0;
                } else {
                    $obj->product_id = isset($product->parent_id)?$product->parent_id:0;
                    $obj->variation_id = isset($product->product_id)?$product->product_id:0;
                }
            }
            $obj->platform = $platform_no;
            $obj->platform_sid = isset($product->website_id)?$product->website_id:null;
            $obj->platform_name = $tag;
            $obj->shop_name = $this->getShopName($product->website_id, $tag, $product->seller_id);

            $obj->message = $this->getErrorMessageForMonitoringInventoryProductQtyUpdate();
            /**
             * Here "dodo_product_id" refers to "product_id" in either
             * "shopee_products" or "lazada_products" or "woo_products" table.
             */
            $obj->dodo_product_id = isset($product->dodo_product_id)?$product->dodo_product_id:0;
            $obj->save();

            $this->resetErrorMessageForMonitoringInventoryProductQtyUpdate();
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Remove old error log.
     *
     * @param integer $dodo_product_id
     * @param integer $platform_no
     * @param string $website_id
     * @param string $type
     */
    private function deleteOldErrorLogForMonitoringAdjustInventoryProductQty($dodo_product_id, $platform_no, $website_id, $type=null)
    {
        try {
            $query = InventoryProductsStockUpdateErrorLog::whereDodoProductId($dodo_product_id)
                ->wherePlatform($platform_no)
                ->wherePlatformSid($website_id);
            if (isset($type) and !empty($type)) {
                $query = $query->whereType($type);
            }
            $query->delete();
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get shop name
     *
     * @param integer $website_id
     * @param string $tag
     * @param integer $seller_id
     * @return string
     */
    private function getShopName($website_id, $tag, $seller_id)
    {
        try {
            $shop = null;
            if ($tag == $this->getTagForShopeePlatform()) {
                $shop = Shopee::whereId($website_id)->orWhere('shop_id', $website_id)->first();
            } else if ($tag == $this->getTagForLazadaPlatform()) {
                $shop = Lazada::find($website_id);
            } else if ($tag == $this->getTagForWooCommercePlatform()) {
                $shop = WooShop::where('shop_id', '=', $website_id)
                ->where('woo_shops.seller_id', $seller_id)
                ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
                ->select('woo_shops.id','shops.name AS shop_name','shop_id')
                ->first();
            }
            if (isset($shop)) {
                return $shop->shop_name;
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return "";
    }
}
