<?php

namespace App\Http\Controllers;

use App\Jobs\DeleteFileFromStorage;
use App\Jobs\LazadaOrderBillPdfDownload;
use App\Models\Lazada;
use App\Models\LazadaBillPdf;
use App\Models\LazadaOrderPurchase;
use App\Traits\LazadaOrderPurchaseTrait;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Lazada\LazopRequest;

use function PHPUnit\Framework\isJson;

class LazadaOrderPurchasePdfController extends Controller
{
    use LazadaOrderPurchaseTrait;

    private $pdf_generation_time_limit = 3;
    private $lazada_api_limit = 100;

    public function __construct()
    {
        $this->middleware('auth');
    }

      
    /**
     * Genereate pdf files containing airway bills for multiple orders.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateLazadaAirwayBillPrint(Request $request) 
    {
        try {
            if ($request->ajax()) {
                if(!$this->checkIfDirectoryExistsForPdfGeneration()) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.No directory found.")
                    ]);
                }

                $session_key = $this->getSessionKeyForCheckingAirwayBillPdfGenerationPermission();
                if(!$this->checkIfPdfCanBeGeneratedFromSession($session_key) || $this->checkIfSystemProcessingAirwayBillPdf()) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Already processing airway bill pdfs.")
                    ]);
                }

                if (isset($request->json_data)) {
                    $arr = json_decode($request->json_data);
                    if (sizeof($arr) > 50) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __("translation.You can select atmost 50 orders at a time.")
                        ]);
                    }
                    if (sizeof($arr) > 0) {
                        /* Check if pdf can be generated from session. */
                        Session::put($session_key, date("Y-m-d H:i:s", time()));
                        $ordersn_list = [];

                        $lazada_shops = Lazada::get();
                        foreach ($lazada_shops as $lazada_shop) {
                            $ordersn_list[$lazada_shop->id] = [];
                        }

                        foreach ($arr as $web_order_data) {
                            $order_data = explode("*", $web_order_data);
                            /* $order_data[0] is 'website_id'('id' in 'lazada' table), $order_data[1] is 'id'('lazada_order_purchases' table), $order_data[2] is 'order_id'(ordersn) */
                            array_push($ordersn_list[(int) $order_data[0]], $order_data[2]);
                        }

                        $token = \Illuminate\Support\Str::random(25);
                        $initial_percentage = rand(5,10);

                        /* Save the pdf info. */
                        $lazada_airway_bill_pdf = new LazadaBillPdf();
                        $lazada_airway_bill_pdf->user_id = $this->getLazadaSellerId();
                        $lazada_airway_bill_pdf->token = $token;
                        $lazada_airway_bill_pdf->percentage = $initial_percentage;
                        $lazada_airway_bill_pdf->save();

                        LazadaOrderBillPdfDownload::dispatch($token, $this->getLazadaSellerId(), $ordersn_list);
                        
                        return response()->json([
                            "success"   => true,
                            "data"      => [
                                "percentage" => $initial_percentage,
                            ],
                            "message"   => __("translation.Pdf generation started successfully.")
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("translation.Falied to start pdf generation.")
        ]);
    }  


    /**
     * Check if the directory is present in "storage". If not try to create an check again.
     * If fails then return error response.
     */
    private function checkIfDirectoryExistsForPdfGeneration() 
    {
        try {
            $directory_path = storage_path("app/lazada/airway_bills");
            if(!File::exists($directory_path)) {
                /* Remove all data from table if any found */
                LazadaBillPdf::whereUserId($this->getLazadaSellerId())->delete();
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
        return "lazada_airbill_generate_time_".$this->getLazadaSellerId();
    }


    /**
     * Check if the system is busy with generating a file at the moment of requesting to generate new pdf.
     * 
     * @return boolean
     */
    private function checkIfSystemProcessingAirwayBillPdf() 
    {
        try {
            $airway_bills_count = LazadaBillPdf::whereUserId($this->getLazadaSellerId())
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
            $airway_bill_pdf = LazadaBillPdf::whereUserId($this->getLazadaSellerId())
            ->whereStatus("processing")
            ->first();
            return isset($airway_bill_pdf, $airway_bill_pdf->percentage)?$airway_bill_pdf->percentage:0;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return 0;
    }


    /**
     * Get all the downloadable pdf.
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDownloadableLazadaBillPdfPrint(Request $request) 
    {
        try {
            if ($request->ajax()) {
                $lazada_airway_bill_pdfs = LazadaBillPdf::select("pdf_name", "token", "total_orders", "status", "user_id", "failed_ordersn", "created_at")
                    ->whereUserId($this->getLazadaSellerId())
                    ->whereStatus("complete")
                    ->orderBy('created_at', 'desc')
                    ->get();
                $data = [];
                if (sizeof($lazada_airway_bill_pdfs) > 0) {
                    foreach($lazada_airway_bill_pdfs as $lazada_airway_bill_pdf) {
                        array_push($data, [
                            "name"  => $lazada_airway_bill_pdf["pdf_name"],
                            "total" => $lazada_airway_bill_pdf["total_orders"],
                            "date"  => Carbon::parse($lazada_airway_bill_pdf["created_at"])->format("d/m/y h:i A"),
                            "token" => $lazada_airway_bill_pdf["token"],
                            "missing_orders" => $lazada_airway_bill_pdf["failed_ordersn"]
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
    public function downloadLazadaBillPdfPrint($token) 
    {
        try {
            if (isset($token) && !empty($token)) {
                /* Get pdf info in database. */
                $lazada_airway_bill_pdf = LazadaBillPdf::whereToken($token)
                    ->whereUserId($this->getLazadaSellerId())
                    ->whereStatus("complete")
                    ->first();
                if (isset($lazada_airway_bill_pdf, $lazada_airway_bill_pdf->pdf_path) and !empty($lazada_airway_bill_pdf->pdf_path)) {
                    return response()->download($lazada_airway_bill_pdf->pdf_path, $lazada_airway_bill_pdf->pdf_name);
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
    public function deleteSpecificLazadaBillPdf(Request $request) 
    {
        try {
            if ($request->ajax()) {
                if (isset($request->token) && !empty($request->token)) {
                    /* Get pdf info from database. */
                    $airway_bill = LazadaBillPdf::whereToken($request->token)
                        ->whereUserId($this->getLazadaSellerId())
                        ->whereStatus("complete")
                        ->first();
                    if (isset($airway_bill)) {
                        $old_mask = umask();
                        if (file_exists($airway_bill->pdf_path)) {
                            unlink($airway_bill->pdf_path);
                        }
                        umask($old_mask);
                        $airway_bill->delete();
                    } else {
                        Log::debug("No such pdf found");
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
    public function deleteOldLazadaBillPdf(Request $request) 
    {
        try {
            $message = __("translation.Invalid request.");
            if ($request->ajax()) {
                /* Get pdfs from database. */
                $airway_bills = LazadaBillPdf::whereUserId($this->getLazadaSellerId())
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
                    $message = __("translation.Successfully removed old pdfs.");
                } else {
                    $message = __("translation.No old pdfs found.");
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
                $incomplete_pdfs = LazadaBillPdf::whereUserId($this->getLazadaSellerId())->whereStatus("processing")->get();
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
     * @param string $order_id
     */
    public function downloadSpecificOrderBillPdfFromLazada($order_id)
    {
        try {
            if (empty($order_id)) {
                return;
            }
            $order_purchase = LazadaOrderPurchase::whereOrderId($order_id)->first();
            if (!isset($order_purchase, $order_purchase->website_id, $order_purchase->order_item_ids)) {
                return;
            }

            $lazada_shop = Lazada::find($order_purchase->website_id);
            if (!isset($lazada_shop)) {
                return;
            }

            $client = $this->getLazadaClient();
            $access_token = $this->getAccessTokenForLazada($order_purchase->website_id);

            $directory_path = storage_path("app/lazada/airway_bills");
            
            $html = "";
            $obj = new LazopRequest('/order/document/get', 'GET');

            if (isJson($order_purchase->order_item_ids)) {
                $obj->addApiParam('order_item_ids', $order_purchase->order_item_ids);
            } else {
                $obj->addApiParam('order_item_ids', json_encode($order_purchase->order_item_ids));
            }
            $obj->addApiParam('doc_type', 'shippingLabel');
            if (isset($client, $obj) and !empty($access_token)) {
                $response = $client->execute($obj, $access_token);
                $data = json_decode($response);
                if (isset($data->data, $data->data->document, $data->data->document->file)) {
                    $html = base64_decode($data->data->document->file);
                }
            } else {
                return;
            }

            $order_purchase->downloaded_at = Carbon::now()->format("Y-m-d H:i:s");
            $order_purchase->save();

            /* Create a file */
            $mask = umask();
            $file_name = 'shipping_label_'.$order_purchase->order_id.'.html';
            $file_path = $directory_path.'/'.$file_name;
            File::put($file_path, $html);
            umask($mask);

            /* Dispatch a job to delete the file */
            DeleteFileFromStorage::dispatch($file_path)->delay(Carbon::now()->addSeconds(30));

            return response()->download($file_path, $file_name);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }

    }
}
