<?php

namespace App\Jobs;

use App\Models\ShopeeAirwayBillPdf;
use App\Models\ShopeeOrderPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ShopeeOrderAirwayBillDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $token;
    private $ordersn_list;
    private $percentage;
    private $shopee_limit = 50;
    private $directory_path;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($token, $ordersn_list=[], $percentage=0)
    {
        $this->token = $token;
        $this->ordersn_list = $ordersn_list;
        $this->percentage = $percentage;
        $this->directory_path = storage_path("app/shopee/airway_bills");
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $arr_size = sizeof($this->ordersn_list);
        if ($arr_size == 0) {
            return;
        }
        
        /* Check if the target directory exists in "storage". */
        $shopee_airway_bill_pdf = ShopeeAirwayBillPdf::whereToken($this->token)->first();
        if (isset($shopee_airway_bill_pdf)) {
            if(!File::exists($this->directory_path)) {
                Log::error($this->directory_path." doesn't exist.");
                $shopee_airway_bill_pdf->delete();
                return;
            } else {
                /* For frontend. The percentage was decided keeping in mind that we are only processing 50 orders only. */
                if ($arr_size <= 10) {
                    $percentage = rand(70,85);
                } else if ($arr_size <= 25) {
                    $percentage = rand(45,60);
                } else {
                    $percentage = rand(25,40);
                }
                $shopee_airway_bill_pdf->percentage = $percentage;
                $shopee_airway_bill_pdf->save();
            }
        } else {
            return;
        }
        
        $order_list = [];
        if (sizeof($this->ordersn_list) > $this->shopee_limit) {
            $order_list = array_slice($this->ordersn_list, 0, $this->shopee_limit);
            /* Dispatch job if more ordersn is passed then the expected limit. */
            if (isset($this->ordersn_list[$this->shopee_limit])) {
                ShopeeOrderAirwayBillDownload::dispatch($this->token, array_slice($this->ordersn_list, $this->shopee_limit, sizeof($this->ordersn_list)), $this->percentage)->delay(now()->addSeconds(5));
            }
        } else {
            $order_list = $this->ordersn_list;
        }
        $order_purchases = ShopeeOrderPurchase::whereIn('order_id', $order_list)->get();
        if (sizeof($order_purchases) > 0) {
            $airway_bills = $this->getAirwayBillUrlFromDatabase($order_purchases);
            if (sizeof($airway_bills) > 0) {
                $this->generateAirwayBillPdf($airway_bills);
            }
        }
    }

    
    /**
     * Generate the airway bill pdf.
     */
    private function generateAirwayBillPdf($airway_bills) {
        $old_umask = umask();
 
        $new_pdf_file_paths = [];
        foreach ($airway_bills as $order_id => $airway_bill) {
            $pdf_file_content = $this->getAirwayBillPdfContent($airway_bill);
            /**
             * If the pdf is not found then a json response is send back by Shopee.
             * So if a json reponse is send just ignore the particular airway bill content.
             */
            if ($this->isJson($pdf_file_content)) {
                continue;
            }

            try {
                /* Create single pdf. */
                $nf_path = $this->getSinglePdfFilePath($order_id);
                file_put_contents($nf_path, $pdf_file_content);
                chmod($nf_path, 0777);
    
                /* Update the downloaded_at in db. */
                $shopee_order_purchase = ShopeeOrderPurchase::whereOrderId($order_id)->first();
                if (isset($shopee_order_purchase)) {
                    $shopee_order_purchase->downloaded_at = date("Y-m-d H:i:s");
                    $shopee_order_purchase->save();
                }

                array_push($new_pdf_file_paths, $nf_path);
            } catch (\Exception $exception) {
                Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
            }
        }

        $shopee_airway_bill_pdf = ShopeeAirwayBillPdf::whereToken($this->token)->first();
        if (isset($shopee_airway_bill_pdf)) {
            $total_valid_airway_bills = sizeof($new_pdf_file_paths);
            if ($total_valid_airway_bills > 0) {
                $this->executeCommnadForGeneratingPdf($new_pdf_file_paths);
    
                /* Update pdf info in database. */
                if (isset($shopee_airway_bill_pdf)) {
                    if ($shopee_airway_bill_pdf->percentage <= $this->percentage) {
                        $shopee_airway_bill_pdf->percentage = $this->percentage;
                    }
                    $shopee_airway_bill_pdf->total_orders = $total_valid_airway_bills;
                    $shopee_airway_bill_pdf->pdf_name = $this->getParentFileName();
                    $shopee_airway_bill_pdf->pdf_path = $this->getParentPdfFilePath();
                    $shopee_airway_bill_pdf->status = "complete";
                    $shopee_airway_bill_pdf->save();
                }
            } else {
                $shopee_airway_bill_pdf->delete();
            }
        }

        umask($old_umask);
    }


    /**
     * Get the file contents of a airway bill.
     */
    private function getAirwayBillPdfContent($url) {
        return file_get_contents($url);
    }


    /**
     * Get the airway bill urls from database.
     */
    private function getAirwayBillUrlFromDatabase($orders) {
        $airway_bills = [];
        foreach($orders as $order_purchase) {
            if (isset($order_purchase->awb_url) and !empty(($order_purchase->awb_url))) {
                $airway_bills[$order_purchase->order_id] = $order_purchase->awb_url;
            }
        }
        return $airway_bills;
    }


    /**
     * Check if json.
     */
    private function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }


    private function getParentPdfFilePath() {
        return $this->directory_path.'/'.$this->getParentFileName();
    }


    private function getParentFileName() {
        return 'airway_bill__'.$this->token.'.pdf';
    }


    private function getSinglePdfFilePath($order_id) {
        return $this->directory_path.'/single_airway_bill_'.$this->token.'__'.$order_id.'.pdf';
    }


    private function executeCommnadForGeneratingPdf($new_pdf_file_paths) {
        $cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=".$this->getParentPdfFilePath()." ";
        /* Add each pdf file to the end of the command */
        foreach($new_pdf_file_paths as $file) {
            $cmd .= $file." ";
        }
        exec($cmd);
        /* Remove the single pdf files. */
        $cmd = "";
        foreach($new_pdf_file_paths as $file) {
            $cmd .= "rm $file; ";
        }
        exec($cmd);
        chmod($this->getParentPdfFilePath(), 0777);
    }

}
