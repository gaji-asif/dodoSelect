<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Shopee;
use App\Models\ShopeeAirwayBillPdf;
use App\Models\ShopeeOrderPurchase;
use App\Jobs\ShopeeOrderAirwayBillPrint;
use App\Jobs\ShopeeOrderAirwayBillDownload;
use App\Jobs\ShopeeOrderAirwayBillDownload2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use App\Traits\ShopeeOrderPurchaseTrait;

class ShopeeOrderPurchaseAirwayBillController extends Controller
{
    use ShopeeOrderPurchaseTrait;

    private $pdf_generation_time_limit = 3;
    private $shopee_airway_bill_api_limit = 50;

    public function __construct()
    {
        $this->middleware('auth');
    }

    
    /**
     * Bulk Shopee airway bill print. Update database with the url of the bill.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkShopeeAirwayBillPrint(Request $request) 
    {
        try {
            if ($request->ajax()) {
                if (isset($request->json_data)) {
                    $arr = json_decode($request->json_data);
                    if (sizeof($arr) > 0) {
                        $shopee_shops = Shopee::pluck("shop_id", "id");

                        $ordersn_list = [];
                        foreach ($shopee_shops as $key => $shopee_shop) {
                            $ordersn_list[$key] = [];
                        }
                        foreach ($arr as $web_order_data) {
                            $order_data = explode("*", $web_order_data);
                            /* $order_data[0] is 'website_id'('id' in 'shopee' table), $order_data[1] is 'id'('shopee_order_purchases' table), $order_data[2] is 'order_id'(ordersn) */
                            $order_purchase_id = (int) $order_data[1];
                            $orderPurchase = ShopeeOrderPurchase::find($order_purchase_id);
                            if (isset($orderPurchase)) {
                                array_push($ordersn_list[$orderPurchase["website_id"]], $orderPurchase["order_id"]);
                            }
                        }

                        foreach ($shopee_shops as $key => $shopee_shop) {
                            if (isset($ordersn_list[$key]) and sizeof($ordersn_list[$key]) > 0) {
                                $loop_count = floor(sizeof($ordersn_list[$key]) / $this->shopee_airway_bill_api_limit);
                                if ($ordersn_list[$key] % $this->shopee_airway_bill_api_limit != 0) {
                                    $loop_count += 1;
                                }
                                for ($i=0; $i < $loop_count; $i++) {
                                    ShopeeOrderAirwayBillPrint::dispatch($shopee_shop, array_slice($ordersn_list[$key], $i*$this->shopee_airway_bill_api_limit, $this->shopee_airway_bill_api_limit))->delay(now()->addSeconds($i*5));
                                }
                            }
                        }

                        return response()->json([
                            "success"   => true,
                            "message"   => "Airway bill pdf generation started."
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false
        ]);
    }    


    /**
     * Genereate pdf files containing airway bills for multiple orders.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateShopeeAirwayBillPrint(Request $request) 
    {
        try {
            if ($request->ajax()) {
                if(!$this->checkIfDirectoryExistsForPdfGeneration()) {
                    return response()->json([
                        "success"   => false,
                        "message"   => "No directory found."
                    ]);
                }

                $session_key = $this->getSessionKeyForCheckingAirwayBillPdfGenerationPermission();
                if(!$this->checkIfPdfCanBeGeneratedFromSession($session_key) || $this->checkIfSystemProcessingAirwayBillPdf()) {
                    return response()->json([
                        "success"   => false,
                        "message"   => "Already processing airway bill pdfs."
                    ]);
                }

                if (isset($request->json_data)) {
                    $arr = json_decode($request->json_data);
                    if (sizeof($arr) > 100) {
                        return response()->json([
                            "success"   => false,
                            "message"   => "You can select atmost 100 orders at a time."
                        ]);
                    }
                    if (sizeof($arr) > 0) {
                        /* Check if pdf can be generated from session. */
                        Session::put($session_key, date("Y-m-d H:i:s", time()));
                        $ordersn_list = [];

                        foreach ($arr as $web_order_data) {
                            $order_data = explode("*", $web_order_data);
                            /* $order_data[0] is 'website_id'('id' in 'shopee' table), $order_data[1] is 'id'('shopee_order_purchases' table), $order_data[2] is 'order_id'(ordersn) */
                            array_push($ordersn_list, $order_data[2]);
                        }

                        $token = \Illuminate\Support\Str::random(25);
                        $initial_percentage = rand(5,10);

                        /* Save the pdf info. */
                        $shopee_airway_bill_pdf = new ShopeeAirwayBillPdf();
                        $shopee_airway_bill_pdf->user_id = $this->getShopeeSellerId();
                        $shopee_airway_bill_pdf->token = $token;
                        $shopee_airway_bill_pdf->percentage = $initial_percentage;
                        $shopee_airway_bill_pdf->save();

                        /* This only supports 50 orders. */
                        // \App\Jobs\ShopeeOrderAirwayBillDownload2::dispatch($token, $ordersn_list);
                        /* This job can support more than 100. */
                        \App\Jobs\ShopeeOrderAirwayBillDownload3::dispatch($token, $ordersn_list);
                        
                        return response()->json([
                            "success"   => true,
                            "data"      => [
                                "percentage" => $initial_percentage,
                            ],
                            "message"   => "Pdf generation started successfully."
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => "Falied to start pdf generation."
        ]);
    } 


    /**
     * Check if the directory is present in "storage". If not try to create an check again.
     * If fails then return error response.
     */
    private function checkIfDirectoryExistsForPdfGeneration() 
    {
        try {
            $directory_path = storage_path("app/shopee/airway_bills");
            if(!File::exists($directory_path)) {
                /* Remove all data from table if any found */
                ShopeeAirwayBillPdf::whereUserId($this->getShopeeSellerId())->delete();
                /* Create directory in storage */
                $old_mask = umask();
                File::makeDirectory($directory_path, 0777);
                chmod($directory_path, 0777);
                umask($old_mask);
                /* Check if the file was created */
                if(!File::exists($directory_path)) {
                    return false;
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return true;
    }


    /**
     * Get all the downloadable pdf.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDownloadableShopeeAirwayBillPrint(Request $request) 
    {
        try {
            if ($request->ajax()) {
                $shopee_airway_bill_pdfs = ShopeeAirwayBillPdf::select("pdf_name", "token", "total_orders", "status", "user_id", "failed_ordersn", "created_at")
                    ->whereUserId($this->getShopeeSellerId())
                    ->whereStatus("complete")
                    ->orderBy('created_at', 'desc')
                    ->get();
                $data = [];
                if (sizeof($shopee_airway_bill_pdfs) > 0) {
                    foreach($shopee_airway_bill_pdfs as $shopee_airway_bill_pdf) {
                        array_push($data, [
                            "name"  => $shopee_airway_bill_pdf["pdf_name"],
                            "total" => $shopee_airway_bill_pdf["total_orders"],
                            "date"  => Carbon::parse($shopee_airway_bill_pdf["created_at"])->format("d/m/y h:i A"),
                            "token" => $shopee_airway_bill_pdf["token"],
                            "missing_orders" => $shopee_airway_bill_pdf["failed_ordersn"]
                        ]);
                    }
                }
                return response()->json([
                    "success"   => true,
                    "data"      => $data
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false
        ]);
    }  


    /**
     * Download a pdf file containing airway bills for multiple orders.
     * 
     * @param string $token
     */
    public function downloadShopeeAirwayBillPrint($token) 
    {
        try {
            if (isset($token) && !empty($token)) {
                /* Get pdf info in database. */
                $shopee_airway_bill_pdf = ShopeeAirwayBillPdf::whereToken($token)
                    ->whereUserId($this->getShopeeSellerId())
                    ->whereStatus("complete")
                    ->first();
                if (isset($shopee_airway_bill_pdf, $shopee_airway_bill_pdf->pdf_path) and !empty($shopee_airway_bill_pdf->pdf_path)) {
                    return response()->download($shopee_airway_bill_pdf->pdf_path, $shopee_airway_bill_pdf->pdf_name);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return;
    }  


    /**
     * Delete a pdf file.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSpecificShopeeAirwayBill(Request $request) 
    {
        try {
            if ($request->ajax()) {
                if (isset($request->token) && !empty($request->token)) {
                    /* Get pdf info from database. */
                    $airway_bill = ShopeeAirwayBillPdf::whereToken($request->token)
                        ->whereUserId($this->getShopeeSellerId())
                        ->whereStatus("complete")
                        ->first();
                    if (isset($airway_bill)) {
                        $old_mask = umask();
                        if (file_exists($airway_bill->pdf_path)) {
                            chmod($airway_bill->pdf_path, 755);
                            unlink($airway_bill->pdf_path);
                        }
                        umask($old_mask);
                        $airway_bill->delete();
                    }
                    return response()->json([
                        "success"   => true
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false
        ]);
    }  


    /**
     * Download a pdf file containing airway bills for multiple orders.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteOldShopeeAirwayBill(Request $request) 
    {
        try {
            $message = "Invalid request.";
            if ($request->ajax()) {
                /* Get pdfs from database. */
                $airway_bills = ShopeeAirwayBillPdf::whereUserId($this->getShopeeSellerId())
                    // ->where("created_at", "not like", date("Y-m-d", time())."%")
                    ->where("created_at", "<", Carbon::now()->subHours(24)->format("Y-m-d H:i:s"))
                    ->get();
                if (sizeof($airway_bills) > 0) {
                    $old_mask = umask();
                    foreach($airway_bills as $airway_bill) {
                        if (isset($airway_bill->pdf_path) and !empty($airway_bill->pdf_path) and file_exists($airway_bill->pdf_path)) {
                            unlink($airway_bill->pdf_path);
                        }
                        $airway_bill->delete();
                    }
                    umask($old_mask);
                    $message = "Successfully removed old pdfs.";
                } else {
                    $message = "No old pdfs found.";
                }
                return response()->json([
                    "success"   => true,
                    "message"   => $message
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
            $message = $exception->getMessage();
        }
        return response()->json([
            "success"   => false,
            "message"   => $message
        ]);
    }  


    /**
     * Check if pdf can be generated.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkIfPdfCanBeGenerated(Request $request) 
    {
        try {
            if ($request->ajax()) {
                $session_key = $this->getSessionKeyForCheckingAirwayBillPdfGenerationPermission();
                if (!$this->checkIfPdfCanBeGeneratedFromSession($session_key)) {
                    $can_generate = false;
                    if (!$this->checkIfSystemProcessingAirwayBillPdf()) {
                        $can_generate = true;
                        if (Session::has($session_key)) {
                            Session::forget($session_key);
                        }
                    }
                    return response()->json([
                        "success"   => true,
                        "data"      => [
                            "can_generate" => $can_generate,
                            "percentage"   => $this->getProcessingAirwayBillPdfCompletionPercentage()
                        ]
                    ]);
                }

                /**
                 * Remove data if the 3 minutes mark has already passed.
                 */
                $incomplete_pdfs = ShopeeAirwayBillPdf::whereUserId($this->getShopeeSellerId())->whereStatus("processing")->get();
                foreach($incomplete_pdfs as $incomplete_pdf) {
                    if (File::exists($incomplete_pdf->pdf_path)) {
                        unlink($incomplete_pdf->pdf_path);
                    }
                    $incomplete_pdf->delete();
                }

                if (Session::has($session_key)) {
                    Session::forget($session_key);
                }
                
                return response()->json([
                    "success"   => true,
                    "data"      => [
                        "can_generate" => !$this->checkIfSystemProcessingAirwayBillPdf(),
                        "percentage"   => $this->getProcessingAirwayBillPdfCompletionPercentage()
                    ]
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false
        ]);
    }


    /**
     * Get airway bill info for a specific order.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpecificOrderAirwayBillInfoFromShopee(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->id) and !empty($request->id)) {
                $order_purchase_id = (int) $request->id;
                $orderPurchase = ShopeeOrderPurchase::find($order_purchase_id);
                if (!isset($orderPurchase, $orderPurchase->website_id, $orderPurchase->order_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("shopee.no_such_order")
                    ]);
                }

                $shopee_shop = Shopee::find($orderPurchase->website_id);
                if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Shop Not Found")
                    ]);
                }

                $client = $this->getShopeeClient((int)$shopee_shop->shop_id);
                if (isset($client)) {
                    $response = $client->logistics->getAirwayBill([
                        'ordersn_list'  => [$orderPurchase->order_id],
                        'timestamp'     => time()
                    ])->getData();
                }

                if (isset($response["result"])) {
                    if (isset($response["result"], $response["result"]["airway_bills"], $response["result"]["airway_bills"][0], $response["result"]["airway_bills"][0]["airway_bill"])) {
                        $orderPurchase->awb_printed_at = date("Y-m-d H:i:s", time());
                        $orderPurchase->awb_url = $response["result"]["airway_bills"][0]["airway_bill"];
                        $orderPurchase->downloaded_at = date("Y-m-d H:i:s", time());
                        $orderPurchase->save();
                        return response()->json([
                            "success"   => true,
                            "data"      => [
                                "url"   => $response["result"]["airway_bills"][0]["airway_bill"]
                            ],
                            "message"   => __("shopee.order.get_specific_order_airway_bill.success")
                        ]);
                    } else if (isset($response["result"]["errors"], $response["result"]["errors"][0], $response["result"]["errors"][0]["error_description"])) {
                        return response()->json([
                            "success"   => false,
                            "message"   => $response["result"]["errors"][0]["error_description"]
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("shopee.order.get_specific_order_airway_bill.failed")
        ]);
    }


    /**
     * Check if pdf can be generated from checking session.
     * 
     * @param string $session_key
     * @return boolean
     */
    public function checkIfPdfCanBeGeneratedFromSession($session_key) 
    {
        try {
            if (Session::has($session_key)) {
                if (Carbon::now()->diffInMinutes(Carbon::parse(Session::get($session_key))) < $this->pdf_generation_time_limit) {
                    return false;
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return true;
    }


    private function getSessionKeyForCheckingAirwayBillPdfGenerationPermission() 
    {
        return "shopee_airbill_generate_time_".$this->getShopeeSellerId();
    }


    /**
     * Check if the system is busy with generating a file at the moment of requesting to generate new pdf.
     * 
     * @return boolean
     */
    private function checkIfSystemProcessingAirwayBillPdf() 
    {
        try {
            $airway_bills_count = ShopeeAirwayBillPdf::whereUserId($this->getShopeeSellerId())
            ->whereStatus("processing")
            ->count();
            return $airway_bills_count > 0?true:false;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return false;
    }


    /**
     * Check how much of the currently generating pdf has been processed.
     */
    private function getProcessingAirwayBillPdfCompletionPercentage() 
    {
        try {
            $airway_bill_pdf = ShopeeAirwayBillPdf::whereUserId($this->getShopeeSellerId())
            ->whereStatus("processing")
            ->first();
            return isset($airway_bill_pdf, $airway_bill_pdf->percentage)?$airway_bill_pdf->percentage:0;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return 0;
    }
    

    /**
     * Genereate pdf files containing airway bills for multiple orders.
     * NOTE:
     * Used previously.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateShopeeAirwayBillPrintOld(Request $request) 
    {
        try {
            if ($request->ajax()) {
                if(!$this->checkIfDirectoryExistsForPdfGeneration()) {
                    return response()->json([
                        "success"   => false,
                        "message"   => "No directory found."
                    ]);
                }

                $session_key = $this->getSessionKeyForCheckingAirwayBillPdfGenerationPermission();
                if(!$this->checkIfPdfCanBeGeneratedFromSession($session_key) || $this->checkIfSystemProcessingAirwayBillPdf()) {
                    return response()->json([
                        "success"   => false,
                        "message"   => "Already processing airway bill pdfs."
                    ]);
                }

                if (isset($request->json_data)) {
                    $arr = json_decode($request->json_data);
                    if (sizeof($arr) > 50) {
                        return response()->json([
                            "success"   => false,
                            "message"   => "You can select atmost 50 orders at a time."
                        ]);
                    }
                    if (sizeof($arr) > 0) {
                        /* Check if pdf can be generated from session. */
                        Session::put($session_key, date("Y-m-d H:i:s", time()));
                        $shopee_shops = Shopee::pluck("shop_id", "id");
        
                        $ordersn_list = [];
                        foreach ($arr as $web_order_data) {
                            $order_data = explode("*", $web_order_data);
                            /* $order_data[0] is 'website_id'('id' in 'shopee' table), $order_data[1] is 'id'('shopee_order_purchases' table), $order_data[2] is 'order_id'(ordersn) */
                            $order_purchase_id = (int) $order_data[1];
                            $order_purchase = ShopeeOrderPurchase::find($order_purchase_id);
                            if (isset($order_purchase, $order_purchase["awb_url"])) {
                                if (!empty($order_purchase["awb_url"])) {
                                    array_push($ordersn_list, $order_purchase["order_id"]);
                                } else if(isset($order_purchase["tracking_no"]) and !empty($order_purchase["tracking_no"]) and isset($shopee_shops[$order_purchase["website_id"]])) {
                                    /* Get the "awb_url" from Shopee. */
                                    ShopeeOrderAirwayBillPrint::dispatch($shopee_shops[$order_purchase["website_id"]], [$order_purchase["order_id"]])->delay(now()->addSeconds(1));
                                }
                            }
                        }

                        $loop_count = floor(sizeof($ordersn_list) / $this->shopee_airway_bill_api_limit);
                        if (sizeof($ordersn_list) % $this->shopee_airway_bill_api_limit != 0) {
                            $loop_count += 1;
                        }

                        $percentage = rand(10,20);
                        for ($i=1; $i <= $loop_count; $i++) {
                            /* Save the pdf info. */
                            $token = \Illuminate\Support\Str::random(25)."_".$i;
                            $shopee_airway_bill_pdf = new ShopeeAirwayBillPdf();
                            $shopee_airway_bill_pdf->user_id = $this->getShopeeSellerId();
                            $shopee_airway_bill_pdf->token = $token;
                            $shopee_airway_bill_pdf->percentage = $percentage;
                            $shopee_airway_bill_pdf->save();
                            /* Start generating pdf. */
                            ShopeeOrderAirwayBillDownload::dispatch($token, array_slice($ordersn_list, ($i-1)*$this->shopee_airway_bill_api_limit, $this->shopee_airway_bill_api_limit), round(($i/$loop_count)*100))->delay(now()->addSeconds(($i-1)*5));
                        }

                        return response()->json([
                            "success"   => true,
                            "data"      => [
                                "percentage" => $percentage,
                            ],
                            "message"   => "Pdf generation started successfully."
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => "Falied to start pdf generation."
        ]);
    } 
}
