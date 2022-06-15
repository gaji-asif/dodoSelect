<?php

namespace App\Jobs;

use App\Models\Lazada;
use App\Models\LazadaBillPdf;
use App\Models\LazadaOrderPurchase;
use App\Traits\LazadaOrderPurchaseTrait;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Lazada\LazopRequest;
use Barryvdh\Snappy\Facades\SnappyPdf;

class LazadaOrderBillPdfDownload2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LazadaOrderPurchaseTrait;
    private $token;
    private $auth_id;
    private $ordersn_list;
    private $percentage;
    private $doc_type;
    private $lazada_limit = 100;
    private $directory_path;

    private $access_token_list;
    private $current_order_items_list;
    private $current_order_items_count;
    private $overall_order_items_count;
    private $remaining_order_list;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($token, $auth_id, $ordersn_list=[], $percentage=0, $doc_type="shippingLabel")
    {
        $this->token = $token;
        $this->auth_id = $auth_id;
        $this->ordersn_list = $ordersn_list;
        $this->doc_type = $doc_type;
        $this->percentage = $percentage;
        $this->directory_path = storage_path("app/lazada/airway_bills");
        $this->overall_order_items_count = 0;
        $this->current_order_items_list = [];
    }
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /* Check if the target directory exists in "storage". */
        $lazada_airway_bill_pdf = LazadaBillPdf::whereToken($this->token)->whereUserId($this->auth_id)->whereStatus('processing')->first();
        if (isset($lazada_airway_bill_pdf)) {
            if(!File::exists($this->directory_path)) {
                Log::error($this->directory_path." doesn't exist.");
                $lazada_airway_bill_pdf->delete();
                return;
            }
        } else {
            Log::debug("no pdf info found in db!");
            return;
        }

        $arr_size = sizeof($this->ordersn_list);
        if ($arr_size == 0) {
            return;
        }

        $this->getAccessTokenForLazadaShopsFromDatabase();
        
        $lazada_shops = Lazada::get();
        $pdf_files_generated = [];
        foreach ($lazada_shops as $lazada_shop) {
            if (!isset($this->ordersn_list[$lazada_shop->id]) || sizeof($this->ordersn_list[$lazada_shop->id]) == 0) {
                continue;
            }
            $this->remaining_order_list = $this->ordersn_list[$lazada_shop->id];
            $this->current_order_items_count = 0;
            $current_orders_list = [];

            $loop_counter = 0;
            $failed_ordersn = [];
            while (!sizeof($this->remaining_order_list) !== 0) {
                $loop_counter += 1;
                /* Handling 500 items in order. */
                if ($loop_counter > 5) {
                    break;
                }
                $lazada_order_id = array_shift($this->remaining_order_list);
                if (isset($lazada_order_id) and !empty($lazada_order_id)) {
                    $lazada_order_purchase = LazadaOrderPurchase::whereOrderId($lazada_order_id)->first();
                    if (isset($lazada_order_purchase)) {
                        $order_item_ids = $lazada_order_purchase->order_item_ids;
                        if (isset($order_item_ids) and !empty($order_item_ids) and $this->isJson($order_item_ids)) {
                            $order_item_ids = json_decode($order_item_ids);
                            if (($this->current_order_items_count + sizeof($order_item_ids)) <= $this->lazada_limit) {
                                $this->current_order_items_count += sizeof($order_item_ids);
                                $this->overall_order_items_count += sizeof($order_item_ids);
                                $this->current_order_items_list = array_merge($this->current_order_items_list, $order_item_ids);
                                array_push($current_orders_list, $lazada_order_id);
                                if (sizeof($this->remaining_order_list) == 0) {
                                    /**
                                     * Get PDF content from Lazada
                                     */
                                    $shipping_label_html = $this->getPdfDocumentContentFromLazada($this->access_token_list[$lazada_shop->id], "shippingLabel");
                                    $invoice_html = $this->getPdfDocumentContentFromLazada($this->access_token_list[$lazada_shop->id], "invoice");

                                    $pdf = SnappyPdf::loadView("lazada.pdf.pdf_template_1", [
                                        "shipping_label_html"   => $shipping_label_html, 
                                        "invoice_html"          => $invoice_html, 
                                        "carrier_manifest_html" => "",
                                    ]);
                                    $pdf->save($this->getSinglePdfFilePath($lazada_order_id));
                                    array_push($pdf_files_generated, $this->getSinglePdfFilePath($lazada_order_id));

                                    $this->updateLazadaOrderPurchaseDownloadInfo($current_orders_list);
                                    $current_orders_list = [];

                                    $this->current_order_items_list = [];
                                    $this->current_order_items_count = 0;
                                    
                                    break;
                                }
                            } else {
                                /**
                                 * Get PDF content from Lazada
                                 */
                                $shipping_label_html = $this->getPdfDocumentContentFromLazada($this->access_token_list[$lazada_shop->id], "shippingLabel");
                                $invoice_html = $this->getPdfDocumentContentFromLazada($this->access_token_list[$lazada_shop->id], "invoice");
                                $pdf = SnappyPdf::loadView("lazada.pdf.pdf_template_1", [
                                    "shipping_label_html"   => $shipping_label_html, 
                                    "invoice_html"          => $invoice_html, 
                                    "carrier_manifest_html" => "",
                                ]);
                                $pdf->save($this->getSinglePdfFilePath($lazada_order_id));
                                array_push($pdf_files_generated, $this->getSinglePdfFilePath($lazada_order_id));

                                $this->updateLazadaOrderPurchaseDownloadInfo($current_orders_list);
                                $current_orders_list = [];
                                
                                $this->current_order_items_list = [];
                                $this->current_order_items_count = 0;

                                array_unshift($this->remaining_order_list, $lazada_order_id);
                            }
                        }
                    }
                } 
            }

            // $lazada_airway_bill_pdf->pdf_path = $this->getParentPdfFilePath();
            // $lazada_airway_bill_pdf->pdf_name = $this->getParentFileName();
            // $lazada_airway_bill_pdf->total_orders = sizeof($this->ordersn_list);
            // $lazada_airway_bill_pdf->total_items = $this->overall_order_items_count;
            // $lazada_airway_bill_pdf->failed_ordersn = sizeof($failed_ordersn)>0?implode(", ", $failed_ordersn):"";
            // $lazada_airway_bill_pdf->percentage = 100;
            // $lazada_airway_bill_pdf->status = "complete";
            // $lazada_airway_bill_pdf->save();
        }  
        

        if (sizeof($pdf_files_generated) > 0) {
            $this->executeCommnadForGeneratingPdf($pdf_files_generated);
            sleep(10);
        }

        $lazada_airway_bill_pdf->pdf_path = $this->getParentPdfFilePath();
        $lazada_airway_bill_pdf->pdf_name = $this->getParentFileName();
        $lazada_airway_bill_pdf->total_orders = sizeof($this->ordersn_list);
        $lazada_airway_bill_pdf->total_items = $this->overall_order_items_count;
        $lazada_airway_bill_pdf->failed_ordersn = sizeof($failed_ordersn)>0?implode(", ", $failed_ordersn):"";
        $lazada_airway_bill_pdf->percentage = 100;
        $lazada_airway_bill_pdf->status = "complete";
        $lazada_airway_bill_pdf->save();
    }

    private function getAccessTokenForLazadaShopsFromDatabase() 
    {
        try {
            $lazada_shops = Lazada::get();
            foreach ($lazada_shops as $lazada_shop) {
                $this->access_token_list[$lazada_shop->id] = $this->getAccessTokenForLazada($lazada_shop->id);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function getPdfDocumentContentFromLazada($access_token, $doc_type="shippingLabel") 
    {
        try {
            $client = $this->getLazadaClient();
            $url1 = '/order/document/get';
            $request = new LazopRequest($url1, 'GET');
            $request->addApiParam('doc_type', $doc_type);
            $request->addApiParam('order_item_ids', json_encode($this->current_order_items_list));
            $obj = $request;
            if (isset($client, $obj) and !empty($access_token)) {
                $response = $client->execute($obj, $access_token);
                $data = json_decode($response);
                if (isset($data->data,$data->data->document,$data->data->document->file)) {
                    return base64_decode($data->data->document->file);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return "";
    }


    /**
     * Update the awb url downloaded info in database.
     */
    private function updateLazadaOrderPurchaseDownloadInfo($order_id_list) 
    {
        $date = Carbon::now()->format("Y-m-d H:i:s");
        /* Update the downloaded_at in db. */
        foreach ($order_id_list as $order_id) {
            $lazada_order_purchase = LazadaOrderPurchase::whereOrderId($order_id)->first();
            if (isset($lazada_order_purchase)) {
                $lazada_order_purchase->downloaded_at = $date;
                $lazada_order_purchase->save();
            }
        }
    }

    
    /**
     * Generate the airway bill pdf.
     */
    private function generateAirwayBillPdf($pdf_file_content) 
    {
        $old_umask = umask();
        /**
         * If the pdf is not found then a json response is send back by Shopee.
         * So if a json reponse is send just ignore the particular airway bill content.
         */
        if (empty($pdf_file_content)) {
            return;
        }

        try {
            /* Create single pdf. */
            $nf_path = $this->getSinglePdfFilePath(rand(10000000, 999999999));
            file_put_contents($nf_path, $pdf_file_content);
            chmod($nf_path, 0777);

            $this->executeCommnadForGeneratingPdf([$nf_path]);
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }

        umask($old_umask);
    }


    /**
     * Get the file contents of a airway bill.
     */
    private function getAirwayBillPdfContent($url) 
    {
        return file_get_contents($url);
    }


    /**
     * Check if json.
     */
    private function isJson($string) 
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }


    private function getParentPdfFilePath() 
    {
        return $this->directory_path.'/'.$this->getParentFileName();
    }


    private function getParentFileName() 
    {
        return 'airway_bill__'.$this->token.'.pdf';
    }


    private function getSinglePdfFilePath($order_id) 
    {
        return $this->directory_path.'/single_airway_bill_'.$this->token.'__'.$order_id.'.pdf';
    }


    private function getSinglePdfFileName($order_id) 
    {
        return 'single_airway_bill_'.$this->token.'__'.$order_id.'.pdf';
    }


    private function getTmpFilePath($order_id) 
    {
        return $this->directory_path.'/tmp_'.$this->token.'__'.$order_id.'.pdf';
    }


    private function executeCommnadForGeneratingPdf($new_pdf_file_paths) 
    {
        $cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=".$this->getParentPdfFilePath()." ";
        $tmp_file = "";
        if (File::exists($this->getParentPdfFilePath())) {
            $tmp_file = $this->getTmpFilePath(\Illuminate\Support\Str::random(25));
            exec("gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=".$tmp_file." ".$this->getParentPdfFilePath());
        }
        /* Add each pdf file to the end of the command */
        foreach($new_pdf_file_paths as $file) {
            $cmd .= $file." ";
        }
        if (!empty($tmp_file) and File::exists($tmp_file)) {
            $cmd .= $tmp_file." ";
        }
        exec($cmd);
        /* Remove the single pdf files. */
        $cmd = "";
        foreach($new_pdf_file_paths as $file) {
            $cmd .= "rm $file; ";
        }
        if (!empty($tmp_file) and File::exists($tmp_file)) {
            $cmd .= "rm $tmp_file; ";
        }
        if (!empty($cmd)) {
            exec($cmd);
        }
        chmod($this->getParentPdfFilePath(), 0777);
    }
}
