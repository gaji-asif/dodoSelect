<?php

namespace App\Traits\Inventory;

use App\Jobs\InventoryQtySync;
use App\Models\OrderPurchaseDetail;
use App\Models\Product;
use App\Models\StockLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use URL;
use Phattarachai\LineNotify\Facade\Line;

trait AdjustInventoryStockTrait
{
    /**
     *
     * NOTE:
     * $check 0 means remove from stock and $check 1 means add to stock.
     * Add =>
     * {"check":"1","product_id":["231"],"adjust_stock":["3"]}
     * Remove =>
     * {"check":"0","product_id":["231"],"adjust_stock":["5"]}
     */
    public function adjustInventoryStockQty($product_id, $adjust_stock, $check, $seller_id = null, $staff_id = null)
    {
        try {
            if(isset($product_id) && isset($adjust_stock)) {
                /* $product_id contains "dodo_product_id". */
                foreach($product_id as $key => $item) {
                    $product[$key][0] = $item;
                }

                /* $adjust_stock contains quantity to be adjusted for specific dodo_product. */
                foreach($adjust_stock as $key => $item) {
                    $product[$key][1] = $item;
                }

                if ($check == 1) {
                    foreach($product as $item) {
                        $stockLog = new StockLog();
                        $stockLog->product_id = $item[0];
                        $stockLog->quantity = $item[1];
                        // if (Auth::user()->role == 'staff') {
                        //     $stockLog->staff_id = Auth::user()->staff_id;
                        // }
                        // $stockLog->seller_id = Auth::user()->id;
                        if (isset($seller_id)) {
                            $stockLog->seller_id = $seller_id;
                        }
                        $stockLog->date = Carbon::now(config('app.timezone'))->format('Y-m-d H:i');
                        $stockLog->check_in_out = 1;
                        $stockLog->is_defect = 0;
                        if($stockLog->save()) {
                            $dodoProduct = Product::find($item[0]);
                            InventoryQtySync::dispatch($dodoProduct);

                            if ($dodoProduct->child_products) {
                                $child_sku = explode(",", $dodoProduct->child_products);
                                foreach ($child_sku as $child) {
                                    $dodoChildProduct = Product::query()
                                        ->where('product_code', trim($child))
                                        ->with('getQuantity')
                                        ->firstOrFail();
                                    InventoryQtySync::dispatch($dodoChildProduct, $dodoProduct->getQuantity->quantity)->delay(Carbon::now()->addSeconds(1));
                                }
                            }
                        }
                    }
                }
                else {
                    foreach($product as $item) {
                        $stockLog = new StockLog();
                        $stockLog->product_id = $item[0];
                        $stockLog->quantity = $item[1];
                        // if (Auth::user()->role == 'staff') {
                        //     $stockLog->staff_id = Auth::user()->staff_id;
                        // }
                        // $stockLog->seller_id = Auth::user()->id;
                        if (isset($seller_id)) {
                            $stockLog->seller_id = $seller_id;
                        }
                        $stockLog->date = Carbon::now(config('app.timezone'))->format('Y-m-d H:i');
                        $stockLog->check_in_out = 0;
                        $stockLog->is_defect = 0;
                        if($stockLog->save()){
                            $dodoProduct = Product::find($item[0]);
                            InventoryQtySync::dispatch($dodoProduct);

                            if ($dodoProduct->child_products) {
                                $child_sku = explode(",", $dodoProduct->child_products);
                                foreach ($child_sku as $child) {
                                    $dodoChildProduct = Product::query()
                                        ->where('product_code', trim($child))
                                        ->with('getQuantity')
                                        ->firstOrFail();
                                    InventoryQtySync::dispatch($dodoChildProduct, $dodoProduct->getQuantity->quantity)
                                        ->delay(Carbon::now()->addSeconds(1));
                                }
                            }
                        }
                    }
                }

                $data = Product::with('getQuantity')->where('id',$product_id)->orderBy('id', 'desc')->get();

                $adjust_stock = $adjust_stock[0];
                $row = $data[0];
                if (isset($row->image)) {
                    $image = Storage::disk('s3')->url($row->image);
                } else {
                    $image = Storage::disk('s3')->url($row->image_url);
                }

                $image_url = URL::asset($image);
                $current_stock = $row->getQuantity->quantity;
                $previous_stock = $row->getQuantity->quantity - $adjust_stock;
                $alert_stock = $row->alert_stock;
                $previous_status = $this->statusFinder($previous_stock, $alert_stock);
                $current_status = $this->statusFinder($current_stock, $alert_stock);

                if (($previous_status != $current_status) || $current_stock=='0') {
                    $product_name = $row->product_name;
                    $product_code = $row->product_code;
                    $message = $product_name."(".$product_code.")";

                    $orderPurchaseDetailTable = (new OrderPurchaseDetail())->getTable();

                    $incoimg_products = DB::table('order_purchase_details')
                        ->join('order_purchases', 'order_purchases.id', '=', 'order_purchase_details.order_purchase_id')
                        ->where('order_purchase_details.product_id', '=', $product_id)
                        ->where('order_purchases.seller_id', $row->seller_id) // ->where('order_purchases.seller_id',Auth::user()->id)
                        ->where('order_purchases.status', '=', 'open')
                        ->select('order_purchases.*', 'order_purchase_details.quantity', 'order_purchase_details.order_purchase_id')
                        ->get();

                    $message = view('qrCode.line_notify', compact('incoimg_products','current_status','current_stock','product_name','product_code'));
                    Line::imageUrl($image_url)->send($message);
                }
                return true;
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Initate the inventory sync job.
     *
     * @param array $products
     * @param string $type
     */
    public function initInventoryStockAdjustmentJobs($products, $type="remove")
    {
        try {
            $product_id = [];
            $adjust_stock = [];
            $seller_id = null;

            foreach ($products as $product) {  
                array_push($product_id, $product["dodo_product_id"]);
                array_push($adjust_stock, $product["qty"]);
                if (!isset($seller_id) and isset($product["seller_id"])) {
                    $seller_id = $product["seller_id"];
                }
            }

            if (sizeof($product_id) > 0) {
                $this->adjustInventoryStockQty($product_id, $adjust_stock, $this->getCheckValue($type), $seller_id);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    /**
     * Get the value for "check"
     * If $type is "remove" then return 0 else 1 (for "add")
     *
     * @param string $type
     * @return boolean
     */
    private function getCheckValue($type="remove")
    {
        return $type=="remove" ? 0:1;
    }


    public function statusFinder( $quantity, $alert_stock){
        if($alert_stock != '' && $quantity <= '0')
        {
            return 'OUT OF STOCK';
        }
        elseif($quantity > 0 && $quantity <= $alert_stock )
        {
            return 'LOW STOCK';
        }
        elseif($alert_stock != '' && $quantity > $alert_stock )
        {
            return 'OVERSTOCK';
        }
        elseif ($alert_stock == '')
        {
            return 'N/A';
        }
    }
}
