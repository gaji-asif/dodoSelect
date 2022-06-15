<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductMainStock;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BulkImportPCost implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if($row[0] != 'product_name')
        {
            try {
                $this->saveData($row);
            } catch (Exception $e) {
                if(session::has('bulk_cost_analysis'))
                {
                    $bulk_cost_analysis = session::get('bulk_cost_analysis');
                    session::forget('bulk_cost_analysis');
                    array_push($bulk_cost_analysis,$row[1]);
                    session::put('bulk_cost_analysis',$bulk_cost_analysis);
                }else
                {
                    $bulk_cost_analysis = [];
                    array_push($bulk_cost_analysis,$row[1]);
                    session::put('bulk_cost_analysis',$bulk_cost_analysis);
                }
            }
        }
    }

    public function saveData($row)
    {
        $getProduct = Product::where('product_code',$row[0])->where('seller_id',Auth::user()->id)->first();
        if(empty($getProduct))
        {
            $product = new Product();
            $product->seller_id = Auth::user()->id;
            if(!empty($row[1]) && is_string($row[1]))
            {
                QrCode::generate($row[0], 'qrcodes/'.$row[1].'.svg');
            }
        }
        else{
            $product = Product::where('product_code',$row[1])->where('seller_id',Auth::user()->id)->first();
        }
        $product->product_name = $row[0];
        $product->product_code = $row[1];
        $product->cost_price = $row[2];
        $product->cost_currency = $row[3];
        $product->save();

        if(empty($getProduct))
        {
            $productMainStock = new ProductMainStock();
            $productMainStock->product_id = $product->id;
            $productMainStock->quantity = 0;
            $productMainStock->save();
        }
    }
    public function dateFormate($date)
    {
        if(is_numeric($date))
        {
            $UNIX_DATE = ($date - 25569) * 86400;
            $date_column = gmdate("Y-m-d H:i:s", $UNIX_DATE);
            return $date_column;
        }
    }
}
