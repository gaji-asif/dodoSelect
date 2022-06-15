<?php

namespace App\Jobs;

use App\Models\Shopee;
use App\Models\ShopeeAirwayBillPdf;
use App\Models\ShopeeOrderPurchase;
use App\Models\ShopeeSetting;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Shopee\Client;

class ShopeeOrderAirwayBillDownload2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $token;
    private $ordersn_list;
    private $percentage;
    private $shopee_limit = 50;
    private $directory_path;
    private $process_type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($token, $ordersn_list=[], $percentage=0, $process_type="initiate")
    {
        $this->token = $token;
        $this->ordersn_list = $ordersn_list;
        $this->percentage = $percentage;
        $this->directory_path = storage_path("app/shopee/airway_bills");
        $this->process_type = $process_type;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /* Check if the target directory exists in "storage". */
        $shopee_airway_bill_pdf = ShopeeAirwayBillPdf::whereToken($this->token)->first();
        if (isset($shopee_airway_bill_pdf)) {
            if(!File::exists($this->directory_path)) {
                Log::error($this->directory_path." doesn't exist.");
                $shopee_airway_bill_pdf->delete();
                return;
            }
        } else {
            Log::debug("no pdf info found in db!");
            return;
        }

        if ($this->process_type="initiate") {
            $arr_size = sizeof($this->ordersn_list);
            if ($arr_size == 0) {
                return;
            }
    
            $shopee_shops = Shopee::pluck("shop_id", "id");
            $shop_specific_ordersn_list = [];
            foreach ($shopee_shops as $id => $shop) {
                $shop_specific_ordersn_list[$id] = [];
            }
            
            foreach ($this->ordersn_list as $ordersn) {
                $order_purchase = ShopeeOrderPurchase::whereOrderId($ordersn)->first();
                if (isset($order_purchase)) {
                    array_push($shop_specific_ordersn_list[$order_purchase["website_id"]], $order_purchase["order_id"]);
                }
            }
    
            $per_shop_percentage = round(100/sizeof($shopee_shops));
            $percentage = 0;
            $failed_ordersn = [];
            foreach ($shopee_shops as $id => $shop_id) {
                $percentage += $per_shop_percentage;
                if ($percentage > 100) {
                    $percentage = 100;
                } 
                if(sizeof($shop_specific_ordersn_list[$id]) > 0) {
                    $response = $this->getAirwayBillInfoFromShopee($shop_id, $shop_specific_ordersn_list[$id]);
                    if (isset($response, $response["request_id"], $response["batch_result"], $response["batch_result"]["airway_bills"])) {
                        $airway_bills = $response["batch_result"]["airway_bills"];
                        if (sizeof($airway_bills) > 0) {
                            foreach($airway_bills as $airway_bill) {
                                $this->generateAirwayBillPdf($airway_bill);
                            }
                            $this->updateShopeeOrderPurchaseDownloadInfo($shop_specific_ordersn_list[$id]);
                            $shopee_airway_bill_pdf->percentage = $percentage;
                            $shopee_airway_bill_pdf->save();
                        }
                        if (isset($response["batch_result"]["errors"])) {
                            $errors = $response["batch_result"]["errors"];
                            foreach ($errors as $error) {
                                if (isset($error["ordersn"])) {
                                    array_push($failed_ordersn, "#".$error["ordersn"]);
                                }
                            }
                        }
                    }
                }
            }
            $shopee_airway_bill_pdf->pdf_path = $this->getParentPdfFilePath();
            $shopee_airway_bill_pdf->pdf_name = $this->getParentFileName();
            $shopee_airway_bill_pdf->total_orders = sizeof($this->ordersn_list);
            $shopee_airway_bill_pdf->failed_ordersn = sizeof($failed_ordersn)>0?implode(", ", $failed_ordersn):"";
            $shopee_airway_bill_pdf->percentage = 100;
            $shopee_airway_bill_pdf->status = "complete";
            $shopee_airway_bill_pdf->save();
        }
    }


    /**
     * Update the awb url downloaded info in database.
     */
    private function updateShopeeOrderPurchaseDownloadInfo($ordersn_list) 
    {
        $date = Carbon::now()->format("Y-m-d H:i:s");
        /* Update the downloaded_at in db. */
        foreach ($ordersn_list as $ordersn) {
            $shopee_order_purchase = ShopeeOrderPurchase::whereOrderId($ordersn)->first();
            if (isset($shopee_order_purchase)) {
                $shopee_order_purchase->downloaded_at = $date;
                $shopee_order_purchase->save();
            }
        }
    }

    
    /**
     * Generate the airway bill pdf.
     */
    private function generateAirwayBillPdf($airway_bill) 
    {
        $old_umask = umask();
 
        $pdf_file_content = $this->getAirwayBillPdfContent($airway_bill);
        /**
         * If the pdf is not found then a json response is send back by Shopee.
         * So if a json reponse is send just ignore the particular airway bill content.
         */
        if ($this->isJson($pdf_file_content)) {
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


    /**
     * Get airway bill info from Shopee.
     */
    private function getAirwayBillInfoFromShopee($shopee_shop_id, $order_list) 
    {
        $client = $this->getShopeeClient($shopee_shop_id);
        if (isset($client)) {
            return $client->logistics->getAirwayBill([
                'ordersn_list' => $order_list,
                'is_batch'     => true
            ])->getData();
        }
        return null;
    }


    /**
     * Get the "Shopee" client to communicate with the api.
     */
    private function getShopeeClient($shopee_shop_id) 
    {
        $shopee_setting = ShopeeSetting::first();
        if (isset($shopee_setting)) {
            return new Client([
                'baseUrl' => $shopee_setting->host,
                'secret' => $shopee_setting->parent_key,
                'partner_id' => (int) $shopee_setting->parent_id,
                'shopid' => (int) $shopee_shop_id,
                'timestamp' => time()
            ]);
        }
        return null;
    }
}
