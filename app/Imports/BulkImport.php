<?php

namespace App\Imports;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMainStock;
use Maatwebsite\Excel\Concerns\ToModel;
use Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BulkImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if($row[0] != "product_name")
        {
            // $getCategoryName = strtolower($row[5]);
           $getCategoryId = Category::where('seller_id',Auth::user()->id)->where('parent_category_id',0)->where('cat_name',$row[5])->first();

            $getProduct = Product::where('product_code',$row[2])->where('seller_id',Auth::user()->id)->first();
            if(empty($getProduct))
            {
                $product = new Product();
                $product->seller_id = Auth::user()->id;
               // QrCode::generate($row[2], 'qrcodes/'.$row[2].'.svg');
            }
            else{
                $product = Product::where('product_code',$row[2])->where('seller_id',Auth::user()->id)->first();
            }

            $product->product_name = $row[0];
            $product->image = 'uploads/product/'.$row[1];
            $product->product_code = $row[2];
            $product->price = $row[3];
            $product->weight = $row[4];
            $product->pack = $row[6];
            $product->alert_stock = $row[7];
            $product->specifications = $row[8];
            if(!empty($getCategoryId))
            {
                $product->category_id = $getCategoryId->id;
            }
            $product->save();

            if(empty($getProduct))
            {
                $productMainStock = new ProductMainStock();
                $productMainStock->product_id = $product->id;
                $productMainStock->quantity = 0;
                $productMainStock->save();
            }

            ActivityLog::updateProductActivityLog('Bulk create products', $product->id);
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
