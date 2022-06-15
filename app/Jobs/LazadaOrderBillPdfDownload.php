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

class LazadaOrderBillPdfDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LazadaOrderPurchaseTrait;
    private $token;
    private $auth_id;
    private $ordersn_list;
    private $percentage;
    private $doc_type;
    private $lazada_limit = 100;
    private $directory_path;
    private $file_type;

    private $access_token_list;
    private $current_order_items_list;
    private $overall_order_items_count;
    private $remaining_order_list;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($token, $auth_id, $ordersn_list=[], $percentage=0, $doc_type="shippingLabel", $file_type="html")
    {
        $this->token = $token;
        $this->auth_id = $auth_id;
        $this->ordersn_list = $ordersn_list;
        $this->doc_type = $doc_type;
        $this->percentage = $percentage;
        $this->directory_path = storage_path("app/lazada/airway_bills");
        $this->overall_order_items_count = 0;
        $this->current_order_items_list = [];
        $this->file_type = $file_type;
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

        if (!in_array($this->doc_type, ["shippingLabel", "invoice"])) {
            return;
        }

        if (!in_array($this->file_type, ["pdf", "html"])) {
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
                            if (sizeof($order_item_ids) > 0) {
                                if ($this->file_type == "pdf") {
                                    /* This generate PDF. */
                                    $shipping_label_html = $this->getPdfDocumentContentFromLazada($this->access_token_list[$lazada_shop->id], $order_item_ids, "shippingLabel");
                                    $invoice_html = $this->getPdfDocumentContentFromLazada($this->access_token_list[$lazada_shop->id], $order_item_ids, "invoice");
                                    
                                    if (empty($shipping_label_html) and empty($invoice_html)) {
                                        array_push($failed_ordersn, $lazada_order_id);
                                        continue;
                                    }

                                    $pdf = SnappyPdf::loadView("lazada.pdf.pdf_template_1", [
                                        "shipping_label_html"   => $shipping_label_html, 
                                        "invoice_html"          => $invoice_html, 
                                        "carrier_manifest_html" => "",
                                    ]);
                                    $pdf->setOrientation('Portrait')->setPaper('a5')->save($this->getSinglePdfFilePath($lazada_order_id));
                                    array_push($pdf_files_generated, $this->getSinglePdfFilePath($lazada_order_id));
                                } else if ($this->file_type == "html") {
                                    /* This generate HTML. */
                                    if ($this->doc_type=="shippingLabel") {
                                        $html = $this->getPdfDocumentContentFromLazada($this->access_token_list[$lazada_shop->id], $order_item_ids, "shippingLabel");
                                    } else if ($this->doc_type=="invoice") {
                                        $html = $this->getPdfDocumentContentFromLazada($this->access_token_list[$lazada_shop->id], $order_item_ids, "invoice");
                                    }
                                    if (File::exists($this->getParentHtmlFilePath())) {
                                        File::append($this->getParentHtmlFilePath(), $html);
                                    } else {
                                        File::put($this->getParentHtmlFilePath(), '
                                        <!DOCTYPE html>
                                        <html>
                                            <head>
                                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                                <style type="text/css">
                                                    #shipping_label .cn-html-body {
                                                        width: 100% !important;
                                                        height: 500px !important;
                                                        margin: 0 auto;
                                                    }
                                                    @media print {
                                                       .delivery-note {
                                                            page-break-after: always;
                                                        }
                                                    }
                                                </style>
                                            </head>
                                            <body>
                                        ');
                                        File::append($this->getParentHtmlFilePath(), $html);
                                    }
                                }

                                $this->overall_order_items_count += sizeof($order_item_ids);
                            } else {
                                array_push($failed_ordersn, $lazada_order_id);
                            }
                        } else {
                            array_push($failed_ordersn, $lazada_order_id);
                        }
                    }
                } 
            }

            $new_percentage = $lazada_airway_bill_pdf->percentage + rand(15,30);
            if ($new_percentage < 80) {
                $lazada_airway_bill_pdf->percentage = $new_percentage;
                $lazada_airway_bill_pdf->save();
            }
        }  
        
        if ($this->file_type == "pdf") {
            if (sizeof($pdf_files_generated) > 0) {
                $this->executeCommnadForGeneratingPdf($pdf_files_generated);
                sleep(10);
                $this->updateLazadaOrderPurchaseDownloadInfo($this->ordersn_list);
                $lazada_airway_bill_pdf->pdf_path = $this->getParentPdfFilePath();
                $lazada_airway_bill_pdf->pdf_name = $this->getParentPdfFileName();
            }
        } else if ($this->file_type == "html") {
            File::append($this->getParentHtmlFilePath(), '</body></html>');
            $this->updateLazadaOrderPurchaseDownloadInfo($this->ordersn_list);
            $lazada_airway_bill_pdf->pdf_path = $this->getParentHtmlFilePath();
            $lazada_airway_bill_pdf->pdf_name = $this->getParentHtmlFileName();
        }

        $lazada_airway_bill_pdf->total_orders = sizeof($this->ordersn_list);
        $lazada_airway_bill_pdf->total_items = $this->overall_order_items_count;
        $lazada_airway_bill_pdf->failed_ordersn = sizeof($failed_ordersn)>0?implode(", ", $failed_ordersn):"";
        $lazada_airway_bill_pdf->percentage = 100;
        $lazada_airway_bill_pdf->status = "complete";
        $lazada_airway_bill_pdf->save();
    }


    /**
     * Get all the access tokens for available Lazada shops.
     */
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


    /**
     * Get the pdf content from Lazada.
     */
    private function getPdfDocumentContentFromLazada($access_token, $order_item_ids, $doc_type="shippingLabel") 
    {
        try {
            $client = $this->getLazadaClient();
            $request = new LazopRequest('/order/document/get', 'GET');
            
            $request->addApiParam('doc_type', $doc_type);
            $request->addApiParam('order_item_ids', json_encode($order_item_ids));
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
     * Check if json.
     */
    private function isJson($string) 
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }


    private function getParentPdfFileName() 
    {
        return 'lazada_bill__'.$this->token.'.pdf';
    }


    private function getParentPdfFilePath() 
    {
        return $this->directory_path.'/'.$this->getParentPdfFileName();
    }


    private function getParentHtmlFileName() 
    {
        return 'lazada_bill__'.$this->token.'.html';
    }


    private function getParentHtmlFilePath() 
    {
        return $this->directory_path.'/'.$this->getParentHtmlFileName();
    }


    private function getSinglePdfFilePath($order_id, $index=0) 
    {
        if ($index > 0) {
            return $this->directory_path.'/single_bill_'.$this->token.'__'.$index.'__'.$order_id.'.pdf';
        }
        return $this->directory_path.'/single_bill_'.$this->token.'__'.$order_id.'.pdf';
    }


    private function getSinglePdfFileName($order_id) 
    {
        return 'single_bill_'.$this->token.'__'.$order_id.'.pdf';
    }


    private function getTmpFilePath($order_id) 
    {
        return $this->directory_path.'/tmp_'.$this->token.'__'.$order_id.'.pdf';
    }


    /**
     * Generate the pdf by combining single pdfs.
     */
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
