<?php

namespace App\Http\Controllers;


use App\Deal;
use App\Imports\BulkImport;
use App\Models\Category;
use App\Models\WooInventory;
use App\Models\WooProduct;
use App\Models\WooProductMainStock;
use App\Models\WooProductPrice;
use App\Models\WooShop;
use App\Models\StockLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Datatables;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Session;

class InventoryController extends Controller
{

    public function deleteSessionAddLinkedProduct()
    {
        //initialize liked product on reload

        Session::forget('add_linked_product');
    }

    public function inventoryProduct(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->website_id) && $request->website_id != null && $request->website_id != '-1') {
                $website_id = $request->website_id;
                $data = WooProduct::where('inventory_id', $request->inv_id)->where('website_id', $website_id)->where('status', 'publish');
            } else {
                $data = WooProduct::where('inventory_id', $request->inv_id)->where('status', 'publish');
            }


            $table = Datatables::of($data)
                ->addColumn('expand', function ($row) {
                })
                ->addColumn('checkbox', function ($row) {
                    return "<input type='checkbox' name='inv_id' class='inv_id' data-id='" . $row->id . "'>";
                })
                ->addColumn('product_id', function ($row) {
                    return $row->product_id;
                })
                ->addColumn('website_id', function ($row) {
                    $shop = WooShop::where('id', $row->website_id)->get();
                    $site_name = "";
                    foreach ($shop as $details) {
                        $site_name = $details->name;
                        $site_url = $details->site_url;
                        $rest_api_key = $details->rest_api_key;
                        $rest_api_secrete = $details->rest_api_secrete;
                    }
                    return '<strong>' . $site_name . '</strong>';
                })
                ->addColumn('image', function ($row) {
                    $image = '';
                    if (($row->images != '[null]') || ($row->images != '[]')) {

                        $images = json_decode($row->images);

                        if (!empty($images[0]))
                            return '<img width="50px" src="' . $images[0]->src . '">';
                    } else {
                        return $image;
                    }
                })




//              ->editColumn('inventory_link', function ($row) {
//                  if($row->type=='variable'){
//
//                  }else{
//                      return '<div class="inventory_link" data-product_code="'.$row->product_code.'">Inventory Link</div>';
//                  }
//
//              })


//              ->addColumn('price', function ($row) {
//                  if($row->type=='variable'){
//                      return $row->price_html;
//                  }else{
//                      return $row->price;
//                  }
//              })


                ->addColumn('status', function ($row) {
                    $status = ucfirst(str_replace("-", " ", $row->status));
                    return $status;
                })
                ->addColumn('manage', function ($row) {
                    return '<span class="bg-red-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer BtnDelete3" data-id="' . $row->id . '"><i class="fas fa-trash-alt"></i></span>';
                })
                ->rawColumns(['checkbox', 'product_id', 'website_id', 'image', 'price', 'inventory_link', 'manage'])
                ->make(true);
            return $table;
        } else {
            $shops = WooShop::all();
            $categories = Category::where('seller_id', Auth::user()->id)->get();
            $products = WooProduct::all();
            return view('wc.product', compact('products', 'shops'));
        }
    }


    public function index()
    {
        $data = WooProduct::latest()->get();
        $shops = WooShop::all();
        //initialize liked product on reload
        Session::forget('add_linked_product');

        Session::put('itemArray', $data);
        //$data = OrderPurchase::with('supplier','orderProductDetails')->where('seller_id',Auth::user()->id)->get();

        $total_records = DB::table('woo_products')
            ->select(DB::raw('count(*) as total'))
            ->get();
        $group_status = DB::table('woo_products')
            ->select('website_id', DB::raw('count(*) as total'))
            ->groupBy('website_id')
            ->get();

        // dd($data);
        return view('seller.wc_stocks.index', compact('total_records', 'group_status', 'shops'));
    }

    public function inventoryProductAdd(Request $request)
    {
        $product = WooProduct::find($request->product_id);
        $product->inventory_id = $request->inventory_id;
        $product->save();
        return response()->json([
            'message' => 'Product linked successfully'
        ]);
    }

    public function SyncProduct(Request $request)
    {
        set_time_limit(-1);
        try {
            $productIdes = explode(',', $request->productIdes);
            $inv = WooInventory::find($request->inv_id);

            foreach ($productIdes as $x => $productIde) {

                $product = WooProduct::find($productIde);


                $shop = WooShop::where('id', $product->website_id)->get();
                foreach ($shop as $details) {
                    $site_url = $details->site_url;
                    $rest_api_key = $details->rest_api_key;
                    $rest_api_secrete = $details->rest_api_secrete;
                }
                $product_id = (!empty($product->parent_id)) ? $product->parent_id : $product->product_id;


                //    $url = $site_url.'/wp-json/wc/v3/products/'.$product_id.'?consumer_key='.$rest_api_key.'&consumer_secret='.$rest_api_secrete;
                if ($product->type == "simple") {
                    $url = $site_url . '/wp-json/wc/v3/products/' . $product_id . '?consumer_key=' . $rest_api_key . '&consumer_secret=' . $rest_api_secrete;
                } else {

                    $url = $site_url . '/wp-json/wc/v3/products/' . $product->parent_id . '/variations/' . $product->product_id . '?consumer_key=' . $rest_api_key . '&consumer_secret=' . $rest_api_secrete;
                }


                $data = array(
                    'manage_stock' => true,
                    'stock_quantity' => $inv->quantity,
                );
                $options = array(
                    'http' => array(
                        'header' => "Content-type: application/json\r\n",
                        'method' => 'POST',
                        'content' => json_encode($data),
                    )
                );

                $context = stream_context_create($options);
                $result = file_get_contents($url, false, $context);
                if (!empty($result)) {
                    WooProduct::where('id', $product->id)->update(['quantity' => $inv->quantity]);
                }


            }


            return response()->json(['succes' => true, "message" => __("Products synced successfully")]);

        } catch (\Exception $exception) {
            return response()->json(['succes' => true, "message" => __("Woo-commerce product not updated for api error")]);
        }
    }

    public function SyncAllInv(Request $request)
    {
        set_time_limit(-1);
        try {

            $productIdes = WooProduct::where("inventory_id", $request->id)->pluck('id')->toArray();
            $inv = WooInventory::find($request->id);

            foreach ($productIdes as $x => $productIde) {

                $product = WooProduct::find($productIde);

                $varients = WooProduct::where("parent_id", $product->product_id)->get();

                $shop = WooShop::where('id', $product->website_id)->get();
                foreach ($shop as $details) {
                    $site_url = $details->site_url;
                    $rest_api_key = $details->rest_api_key;
                    $rest_api_secrete = $details->rest_api_secrete;
                }
                $product_id = (!empty($product->parent_id)) ? $product->parent_id : $product->product_id;


                //    $url = $site_url.'/wp-json/wc/v3/products/'.$product_id.'?consumer_key='.$rest_api_key.'&consumer_secret='.$rest_api_secrete;
                if ($product->type == "simple") {
                    $url = $site_url . '/wp-json/wc/v3/products/' . $product_id . '?consumer_key=' . $rest_api_key . '&consumer_secret=' . $rest_api_secrete;
                } else {
                    $url = $site_url . '/wp-json/wc/v3/products/' . $product->parent_id . '/variations/' . $product->product_id . '?consumer_key=' . $rest_api_key . '&consumer_secret=' . $rest_api_secrete;
                }


                $data = array(
                    'manage_stock' => true,
                    'stock_quantity' => $inv->quantity,
                );
                $options = array(
                    'http' => array(
                        'header' => "Content-type: application/json\r\n",
                        'method' => 'POST',
                        'content' => json_encode($data),
                    )
                );

                $context = stream_context_create($options);
                $result = file_get_contents($url, false, $context);
                if (!empty($result)) {
                    Product::where('id', $product->id)->update(['quantity' => $inv->quantity]);
                }


            }


            return response()->json(['succes' => true, "message" => __("Products synced successfully")]);

        } catch (\Exception $exception) {

            return response()->json(['succes' => true, "message" => __("Woo-commerce product not updated for api error")]);
        }
    }


    public function data(Request $request)
    {
        if ($request->ajax()) {

            $data = WooProduct::where('seller_id', Auth::user()->id)->groupby('product_code')->distinct();
            $table = Datatables::of($data)
                ->addColumn('checkbox', function ($row) {
                    return $row->website_id . '*' . $row->id . '*' . $row->product_id; //pass website_id and product_id to avoid conflict
                })
                /*
                      ->addColumn('image', function ($row) {
                          $image = '';

                            $images = json_decode($row->images);
                          if(!empty($images)){
                            $image = $images[0]->src;
                            return '<img width="50px" src="'.$image.'">';
                          }else{
                            return $image;
                          }
                      })
                      */


                ->addColumn('price', function ($row) {
                    return $row->price;
                })
                ->editColumn('updated_at', function ($row) {
                    return Date($row->updated_at);
                })
                ->addColumn('status', function ($row) {
                    $status = ucfirst(str_replace("-", " ", $row->status));
                    return $status;
                })
                ->addColumn('manage', function ($row) {
                    return '
          <span x-on:click="showEditModal=true" class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer BtnUpdate" data-id="' . $row->id . '" ><i class="fas fa-pencil-alt"></i></span>
          <span x-on:click="showEditModal=true" class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer BtnEditQty" data-product_code="' . $row->product_code . '" ><i class="fas fa-bars"></i></span>
          <span class="bg-red-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer BtnDelete" data-id="' . $row->id . '"><i class="fas fa-trash-alt"></i></span>
          ';
                })
                ->rawColumns(['checkbox', 'website_id', 'image', 'manage'])
                ->make(true);
            return $table;
        }

    }

    public function inventories(Request $request)
    {
        if ($request->ajax()) {

            $data = WooInventory::where('seller_id', Auth::user()->id)->get();

            $table = Datatables::of($data)
                ->addColumn('manage', function ($row) {
                    return '
          <span x-on:click="showEditModal=true" class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer editInv" data-id="' . $row->id . '" data-inv_code="' . $row->inventory_code . '" data-inv_name="' . $row->inventory_name . '" data-inv_quantity="' . $row->quantity . '" ><i class="fas fa-edit"></i></span>
          <span x-on:click="showEditModal=true" class=" bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer syncAll" data-id="' . $row->id . '" id="">sync  <i class="fas fa-spinner fa-spin d-none class_' . $row->id . '" id="spin"></i></span>
          <span x-on:click="showEditModal=true" class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer addProduct" data-id="' . $row->id . '" data-product_code="' . $row->product_code . '" ><i class="fas fa-bezier-curve"></i></span>
          <span class="bg-red-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer BtnDelete2" data-id="' . $row->id . '"><i class="fas fa-trash-alt"></i></span>
          ';
                })
                ->rawColumns(['manage'])
                ->make(true);
            return $table;
        }

    }


    public function getVariationByID(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->id_details) && $request->id_details != null) {
                $id_details = $request->id_details;
                $arr_details = explode("*", $id_details);
                $website_id = $arr_details[0];
                $id = $arr_details[1];
                $product_id = $arr_details[2];
                $data = WooProduct::where('parent_id', $product_id)->where('website_id', $website_id)->get();
                $shops = WooShop::all();
                return view('elements.table-view-child-product', compact(['shops', 'data', 'website_id']));
            }
        }
    }

    function searchProductBySKU(Request $request)
    {
        if ($request->get('q')) {
            $products = WooProduct::join("shops", 'shops.id', "woo_products.website_id")->select('woo_products.id', 'woo_products.product_name as full_name', 'woo_products.images', 'woo_products.product_code', 'woo_shops.name as shop_name')
                ->where('woo_products.product_code', 'LIKE', "%$request->q%")
                ->where('woo_products.inventory_id', "=", 0)
                ->where('woo_products.type', "!=", "variable")
                ->where('woo_products.seller_id', "=", Auth::id())
                ->take(20)->get();
            foreach ($products as $product) {

                $img = json_decode($product->images);

                if (!empty($img) && isset($img[0])) {
                    $product->image = $img[0]->src;
                    $product->image = $img[0]->src;
                    $product->full_name = $product->full_name . "(" . $product->product_code . ")";
                }

            }
            return response()->json(['items' => $products]);
            return response()->json(['items' => Product::select('id', 'product_name as full_name', 'image')->where('product_code', 'LIKE', "%$request->q%")->get()]);
        }
    }

    function autocompleteInventrory(Request $request)
    {
        if ($request->get('q')) {
            $products = WooInventory::where("seller_id", Auth::id())
                ->where("inventory_code", "LIKE", "%" . $request->get('q') . "%")
                ->select("inventory_name as full_name", 'id', 'inventory_code as shop_name')
                ->take(20)->get();

            return response()->json(['items' => $products]);
            return response()->json(['items' => WooProduct::select('id', 'product_name as full_name', 'image')->where('product_code', 'LIKE', "%$request->q%")->get()]);
        }
    }


    public function getLinkedProductBySKU(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->product_code) && $request->product_code != null) {

                $product_code = $request->product_code;
                $data = WooProduct::where('product_code', $product_code)->get();
                //dd($data);die;
                $id = $request->id;

                $arr_images = array();
                foreach ($data as $product) {
                    $product_id = $product->product_id;
                    $arr[] = $product_id;


                    // dd($product);
                    if (!empty($product)) {
                        $images = json_decode($product->images);
                        if (!empty($images)) {
                            $arr_images[$product_id] = $product->images;
                        } else {
                            $arr_images[$product_id] = '';
                        }

                    }
                }
                $shops = WooShop::all();
                return view('elements.form-show-link-product', compact(['data', 'shops', 'arr_images', 'product_code']));
            }
        }
    }


    public function addProductBySKU(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->product_code) && $request->product_code != null) {

                $product_code = $request->product_code;
                $data = WooProduct::where('product_code', $product_code)->get();

                $id = $request->id;

                $arr_images = array();
                foreach ($data as $product) {
                    $product_id = $product->product_id;

                    $arr[] = $product_id;

                    $arr_id[] = $product->id;
                    Session::push('add_linked_product', $product->id);


                    if (!empty($product)) {
                        $images = json_decode($product->images);
                        if (!empty($images)) {
                            $arr_images[$product_id] = $product->images;
                        } else {
                            $arr_images[$product_id] = '';
                        }

                    }
                }


                $shops = WooShop::all();
                return view('elements.form-add-link-product', compact(['shops', 'data', 'arr_images', 'product_code']));
            }
        }
    }

    public function addToInventory(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->jSonData) && $request->jSonData != null) {
                $data = json_decode($request->jSonData);

                foreach ($data as $item) {

                    $inventory = new WooInventory();
                    $inventory->product_id = $item->product_id;
                    $inventory->website_id = $item->website_id;
                    $inventory->quantity = $item->quantity;
                    $inventory->product_code = $item->product_code;
                    $inventory->save();
                }


            }
        }
    }

    ////////////create inventory///////////////
    public function createInventory(Request $request)
    {
        $result = ['success' => true, "message" => "Inventory added done"];
        $code = $request->code;
        if (WooInventory::where("inventory_code", $code)->exists()) {
            $result = ['success' => false, "message" => "Inventory code already exists"];
            return response()->json($result);
        }
        try {

            $invt = new WooInventory();
            $invt->quantity = $request->quantity;
            $invt->inventory_name = $request->name;
            $invt->inventory_code = $code;
            $invt->seller_id = Auth::id();
            $invt->inventory_id = random_int(111111, 999999);
            $invt->save();
        } catch (\Exception $exception) {
            $result = ['success' => false, "message" => $exception->getMessage()];
        }
        return response()->json($result);
    }

    public function editInventory(Request $request)
    {

        $result = ['success' => true, "message" => "Inventory updated done"];
        $code = $request->code;

        try {

            $invt = WooInventory::find($request->id);

            $invt->quantity = $request->quantity;
            $invt->inventory_name = $request->inventory_name;
            $invt->inventory_code = $request->inventory_code;
            $invt->seller_id = Auth::id();
            $invt->save();
        } catch (\Exception $exception) {


        }
        return redirect()->to('wc_stocks')->with("success", "Inventory updated done");
    }

    public function inventoryToProduct(Request $request)
    {
        $result = ['success' => true, "message" => "Product added done"];

        if (WooProduct::where("inventory_id", $request->inventory_id)->where('id', $request->product_id)->exists()) {
            $result = ['success' => false, "message" => "Inventory link already exists"];
            return response()->json($result);
        }
        try {
            $invt = WooProduct::find($request->product_id);
            $invt->inventory_id = $request->inventory_id;
            $invt->save();
        } catch (\Exception $exception) {
            $result = ['success' => false, "message" => "Error inventory add"];
        }
        return response()->json($result);
    }

    public function inventoriesDelete(Request $request)
    {
        $result = ['succes' => true, "message" => __("Inventory deleted successfully")];
        WooProduct::where('inventory_id', $request->inventory_id)->update(['inventory_id' => 0]);
        WooInventory::where('id', $request->id)->delete();
        return response()->json($result);
    }

    public function inventoryProductDelete(Request $request)
    {
        $result = ['succes' => true, "message" => __("Product removed successfully")];
        WooProduct::where('id', $request->id)->update(['inventory_id' => 0]);
        return response()->json($result);
    }

}
