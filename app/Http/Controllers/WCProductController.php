<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatatableRequest;
use App\Http\Requests\WCProduct\DeleteRequest;
use App\Http\Requests\WCProduct\SyncProductRequest;
use App\Http\Requests\WCProduct\UpdateRequest;
use App\Http\Requests\WCProduct\StoreWooProductRequest;
use App\Http\Requests\WCProduct\WooProductStoreRequest;
use App\Imports\BulkImport;
use App\Jobs\WooProductChildSync;
use App\Models\WooCronReport;
use App\Models\WooOrderPurchase;
use App\Models\WooOrderPurchaseDetail;
use App\Models\WooProduct;
use App\Models\WooProductMainStock;
use App\Models\WooProductPrice;
use App\Models\WooShop;
use App\Models\WooInventory;
use App\Models\StockLog;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Datatables;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Session;
use App\Jobs\WooProductSync;
use App\Models\Category;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\Shop;
use App\Traits\CalculateDiscountAmountWoo;
use App\Jobs\WooProductVariationsUpdate;


class WCProductController extends Controller
{
    use CalculateDiscountAmountWoo;
    private $source_shop;

    public function __construct()
    {
        $this->source_shop = isset($_SERVER['HTTP_X_WC_WEBHOOK_SOURCE']) ? $_SERVER['HTTP_X_WC_WEBHOOK_SOURCE'] : '';
    }

    /**
     * If not logged in its redirected to sign in page
    */
    public function index()
    {
        $wooShops = WooShop::query()
        ->with('shops')
        ->get();

        return view('seller.wc_products.index', compact('wooShops'));
    }

    /**
     * Show the wc_product data by id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sellerId = Auth::user()->id;

        $wooProduct = WooProduct::query()
        ->where('seller_id', $sellerId)
        ->where('id', $id)
        ->with('parent')
        ->with('catalog')
        ->first();

        abort_if(!$wooProduct, Response::HTTP_NOT_FOUND, ucfirst(__('translation.data_not_found')));

        return $this->apiResponse(Response::HTTP_OK, 'Success', [
            'woo_product' => $wooProduct
        ]);
    }

    /**
     * create woo_product data
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $product_statuss = ['publish', 'draft'];
        $shops = Shop::all();
        $sellerId = Auth::user()->id;

        $products = Product::where('seller_id', $sellerId)
        ->orderBy('id', 'asc')
        ->skip(0)
        ->take(50)
        ->get();

        return view('seller.wc_products.add', compact('shops', 'product_statuss', 'products'));
    }

    /**
     * create the specified resource in storage.
     *
     * @param  Request  $request
     * @return Response
     * @throws \Exception
     */

    public function store(WooProductStoreRequest $request)
    {
      try {
        if ($request) {
            DB::beginTransaction();
            $isProduct = $this->add_product_in_wc($request);

                // echo $isProduct;
                // exit;

            if ($isProduct) {

                $woo_product_images = [];
                $uploaded_images_path = [];
                if (isset($request->cover_images_count) and $request->cover_images_count > 0) {
                    for ($i=0; $i<$request->cover_images_count; $i++) {
                        if ($request->hasFile('cover_image_'.$i)) {
                            $image = $request->file('cover_image_'.$i);
                            $image_name = $request->file('cover_image_'.$i)->getClientOriginalName();
                            $path = Storage::disk('s3')->put('uploads/woo/products', $image, 'public');
                            $path = Storage::disk('s3')->url($path);

                            $image_arr = [
                                'src' => $path,
                                'alt' => $image_name,
                            ];

                            array_push($woo_product_images, $image_arr);
                            array_push($uploaded_images_path, $image_arr);
                        }
                    }
                }

                $product = new WooProduct();
                if(!empty($request->catelog_product_id)){
                    $product->is_linked = 1;
                    $product->dodo_product_id = $request->catelog_product_id;
                }
                $product->website_id = $request->shop_id;
                $product->product_name = $request->product_name;
                $product->product_id = $isProduct['id'];
                $product->product_code = $request->product_code;
                $product->regular_price = $isProduct['regular_price'];
                $product->price = $isProduct['price'];
                $product->sale_price = $isProduct['sale_price'];
                $product->parent_id = 0;
                $product->quantity = $request->quantity;
                $product->description = $request->description;
                $product->short_description = $request->short_description;
                $product->type = $request->product_type;
                $product->status = $request->status;
                $product->seller_id = Auth::user()->id;
                $product->images = json_encode($woo_product_images);
                $result = $product->save();

                    //$isvariationProduct = $this->add_variations_product_in_wc($isProduct, $request->shop_id, $request);
                DB::commit();

                if($result){
                    return $this->apiResponse(Response::HTTP_OK, ucfirst(__('translation.New Product Added Successfully')));
                }
                else{
                    return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Sorry, something went wrong');
                }
            }
        }

    } catch (\Throwable $th) {
        report($th);

        DB::rollBack();

        return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Sorry, something went wrong. ' . $th->getMessage());
    }
}

    /**
     * Update woo_product data
     *
     * @param $website_id
     * @param $product_id
     * @return \Illuminate\Http\Response
     */
    public function edit($website_id, $product_id)
    {

        $is_parent = WooProduct::isParent($website_id, $product_id);
        $product_idd = '';

        if($is_parent === 0){
            $product_idd = $product_id;
        }
        if($is_parent > 0){
            $product_idd = $is_parent;
        }

        $sellerId = Auth::user()->id;
        $wooProduct = WooProduct::query()
        ->where('product_id', $product_idd)
        ->where('website_id', $website_id)
        ->with(['woo_shop' => function ($wooShop) {
            $wooShop->with('shops');
        }])
        ->with('catalog')
        ->firstOrFail();
        $products = Product::where('seller_id', $sellerId)
        ->orderBy('id', 'asc')
        ->skip(0)
        ->take(10)
        ->get();

        $imageResources = [];
        if (isset($wooProduct->images)) {
            if (($wooProduct->images != '[null]') || ($wooProduct->images != '[]')) {
                $imageResources = json_decode($wooProduct->images);
            }
        }

        $wooProductsVariations = collect([]);
        if(isset($wooProduct)){
            $wooProductsVariations = WooProduct::where('parent_id', $wooProduct->product_id)->get();
        }

        $getProductAttributes = WooProduct::getProductAttr($wooProduct->attributes);

        //getrice
        // $price = '';
        // $regular_price = '';
        // $sale_price = '';
        // if(empty($wooProduct->sale_price) || $wooProduct->sale_price == 0){
        //     $price = $wooProduct->price;
        //     $regular_price = $wooProduct->regular_price;
        //     $sale_price = $wooProduct->sale_price;
        // }
        // if(isset($wooProduct->sale_price) && $wooProduct->sale_price > 0){
        //     $price = $wooProduct->sale_price;
        //     $regular_price = $wooProduct->regular_price;
        //     $sale_price = $wooProduct->sale_price;
        // }

        return view('seller.wc_products.edit', compact('wooProduct', 'imageResources', 'wooProductsVariations', 'getProductAttributes', 'products'));
    }

    public function update(Request $request)
    {
      try {

        $product = WooProduct::where('id', $request->id)->first();
        if ($product) {
            $uploaded_images_path = [];
            if ($request->hasFile('product_image')) {

                $upload = $request->file('product_image');
                $upload_name =  time() . '_' . Str::random(10) . '_' . $upload->getClientOriginalName();
                $destinationPath = public_path('uploads/payment-receipt');
                $upload->move($destinationPath, $upload_name);
                $product->image = 'uploads/payment-receipt/'.$upload_name;
                return $product->image;
            }

            $is_updated = $this->update_product_in_wc($product, $request);

            if ($is_updated) {
                $discountAmount = $this->getDiscountAmount($is_updated['regular_price'], $is_updated['sale_price']);

                $product->product_name = WooProduct::isVariation($product) ? $product->product_name : $request->product_name;

                if(!empty($request->catelog_product_id)){
                    $product->is_linked = 1;
                    $product->dodo_product_id = $request->catelog_product_id;
                }

                $product->product_code = $request->product_code;
                $product->regular_price = $is_updated['regular_price'];
                $product->price = $is_updated['price'];
                $product->sale_price = $is_updated['sale_price'];
                //$product->quantity = $request->quantity;
                $product->description = $request->description;
                $product->short_description = $request->short_description;
                $product->status = $request->status;
                $product->type = $request->product_type;
                $product->discount = $discountAmount;
                $product->save();


                // for product variations
                $product_images_all = [];
                
                if(!empty($request->product_code_variation)){
                    if(count($request->product_code_variation)>0){
                        $shop = WooShop::find($product->website_id);
                        for($x=0; $x<count($request->product_code_variation); $x++){
                            $image_file = $request->file('woo_product_img_variation');
                            if($request->file('woo_product_img_variation')){

                                $data = [];
                                $destination_path = "";
                                $image = $image_file[$x];
                                $image_name = $image->getClientOriginalName();
                                $path = Storage::disk('s3')->put('uploads/woo/products', $image, 'public');

                                $path = Storage::disk('s3')->url($path);
                                $image_arr = [
                                    'src' => $path,
                                    'alt' => $image_name,
                                ];

                                $product_images = $product->images;
                                if(isset($product_images) && !empty($product_images)){
                                    $product_images_all = json_decode($product_images);
                                    if(sizeof($product_images_all)>0){
                                        array_push($product_images_all, $image_arr);
                                    }
                                    else{
                                        $product_images_all = [$image_arr];
                                    }
                                }
                                else{
                                    $product_images_all = [$image_arr];
                                }
                            }

                            $data = [
                                'sku' => $request->product_code_variation[$x],
                                'regular_price' => $request->regular_price_variation[$x],
                                'sale_price' => $request->sale_price_variation[$x],
                                'images' => json_encode($product_images_all)
                            ];


                            $url = $shop->site_url . '/wp-json/wc/v3/products/' . $product->product_id .'/variations/'.$request->product_id_variation[$x]
                            . '?consumer_key=' . $shop->rest_api_key . '&consumer_secret=' . $shop->rest_api_secrete;


                            $response = Http::put($url, $data);
                            $result =  $response->json();

                            $product_var = WooProduct::where('product_id', $request->product_id_variation[$x])->first();
                            $product_var->product_code = $result['sku'];
                            $product_var->regular_price = $result['regular_price'];
                            $product_var->price = $result['price'];
                            $product_var->sale_price = $result['sale_price'];
                            $product_var->images = json_encode($product_images_all);
                            $product_var->save();
                    }
                        
                }
            }
                return $this->apiResponse(Response::HTTP_OK, ucfirst(__('translation.data_updated_successfully')));
            }

                return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ('translation.Couldn\'t reach Product\'s Shop website, please try again'));
            }

            return $this->apiResponse(Response::HTTP_NOT_FOUND, ucfirst(__('translation.data_not_found')));

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ucfirst(__('translation.something_went_wrong')) . $th->getMessage());
        }
    }

    /**
     * Delete woo product data
     *
     * @param  \App\Http\Requests\WCProduct\DeleteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(DeleteRequest $request)
    {
        try {
            $wooProduct = WooProduct::where('id', $request->id)->first();

            abort_if(!$wooProduct, Response::HTTP_NOT_FOUND, ucfirst(__('translation.data_not_found')));

            $shop = WooShop::find($wooProduct->website_id);
            $url = $shop->site_url . '/wp-json/wc/v3/products/' . $wooProduct->product_id
            . '?consumer_key=' . $shop->rest_api_key . '&consumer_secret=' . $shop->rest_api_secrete;

            $response = Http::delete($url);

            if ($response->successful()) {
                $wooProduct->delete();

                return $this->apiResponse(Response::HTTP_OK, ucfirst(__('translation.product_deleted_successfully')));
            }

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $response->body());

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ucfirst(__('translation.something_went_wrong')));
        }
    }

    /**
     * Handle server side datatable of woo_products
     *
     * @param  \App\Http\Requests\DatatableRequest  $request
     * @return mixed
     */
    public function data(DatatableRequest $request)
    {
        if ($request->ajax()) {
            $sellerId = Auth::user()->id;
            $websiteId = $request->get('website_id');
            $inventoryLinkStatus = $request->get('inventory_status');
            $type = $request->get('type');
            $discountRange = $request->get('discount_range');

            $orderColumnIndex = $request->get('order')[0]['column'] ?? 1;
            $orderDir = $request->get('order')[0]['dir'] ?? 'asc';
            $search = $request->get('search')['value'] ?? '';

            $availableOrderColumn = [
                'id'
            ];

            $orderColumn = $availableOrderColumn[$orderColumnIndex] ?? 'id';

            $wooProducts = WooProduct::query()
            ->where('seller_id', $sellerId)
            ->byWebsite($websiteId)
            ->byType($type)
            ->byDiscountRange($discountRange)
            ->isLinked($inventoryLinkStatus)
            ->published()
            ->searchTable($search)
            ->with(['woo_shop' => function ($wooShop) {
                $wooShop->with('shops');
            }])
            ->with('woo_inventory')
            ->with('catalog')
            ->orderBy($orderColumn, $orderDir);

            return Datatables::of($wooProducts)
            ->addColumn('checkbox', function ($wooProduct) {
                        return $wooProduct->website_id . '*' . $wooProduct->id . '*' . $wooProduct->product_id; //pass website_id and product_id to avoid conflict
                    })
            ->addColumn('woo_product_detail', function ($wooProduct) {
                $strType = $wooProduct->type;
                if (in_array($wooProduct->type, ['variable', 'variation'])) {
                    $strType = 'variable';
                }

                $shopName = $wooProduct->woo_shop->shops->name ?? '-';

                $image = '<img src="'. asset('No-Image-Found.png') .'" alt="'. $wooProduct->product_name .'" class="w-full h-auto" />';
                if (($wooProduct->images != '[null]') || ($wooProduct->images != '[]')) {
                    $imageResources = json_decode($wooProduct->images);

                    $image = '<img src="'. asset('No-Image-Found.png') .'" alt="'. $wooProduct->product_name .'" class="w-full h-auto" />';
                    if (!empty($imageResources[0])) {
                        $image = '<img src="'. $imageResources[0]->src .'" alt="'. $wooProduct->product_name .'" class="w-full h-auto">';
                    }
                }

                $editCatalogButton = '
                   <button type="button"
                   class="btn-action--yellow-outline"
                   data-id="'. $wooProduct->id .'"
                   data-detail-url="'. route('wc-products-show', [ 'product_id' => $wooProduct->id ] ) .'"
                   onClick="editLinkedCatalog(this)">
                   <i class="bi bi-bezier2"></i>
                   <span class="ml-2">
                   '. ucwords(__('translation.edit_link')) .'
                   </span>
                   </button>
                   ';
                if($strType != 'variable'){
                    $editCatalogButn = $editCatalogButton;
                }
                else{
                    $editCatalogButn = '';
                }

                $catalogName = '-';
                $regularPrice = '-';
                $salePrice = '-';
                if($strType == 'simple'){
                   $linkedCatelogText = ucwords(__('translation.linked_catalog'));
                   
                   if ($wooProduct->is_linked == WooProduct::IS_LINKED_YES) {
                    $catalogName = $wooProduct->catalog->product_code;
                }

                $regularPrice = ($wooProduct->regular_price > 0) ?currency_symbol('THB').$wooProduct->regular_price : '-';

                $salePrice = ($wooProduct->sale_price > 0) ? currency_symbol('THB').$wooProduct->sale_price : '-';

            }
            else{
               $linkedCatelogText = '';
               $catalogName = '';
           }

           $strStatus = ucfirst(str_replace('-', ' ', $wooProduct->status));
           $strPrice = $wooProduct->price;

           $strProductCode = '-';
           if (!empty($wooProduct->product_code)) {
            $strProductCode = $wooProduct->product_code;
        }

        $getProductAttributes = WooProduct::getProductAttr($wooProduct->attributes);

        return '
        <div class="flex flex-col sm:flex-row justify-between gap-4">
        <div class="sm:w-1/4">
        <div class="flex flex-col gap-2">
        <div class="w-full lg:w-24">
        '. $image .'
        </div>
        <div>
        <span class="text-blue-500 font-bold">
        ID : '. $wooProduct->id .'
        </span>
        </div>
        </div>
        </div>
        <div class="sm:w-full">
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-8 gap-2 sm:gap-x-8">
        <div class="col-span-1 lg:col-span-2">
        <div class="grid grid-cols-1 gap-2">
        <div>
        <span class="block whitepace-nowrap text-gray-500">
        '. ucwords(__('translation.type')) .'
        </span>
        <span class="font-bold">
        '. $strType .'
        </span>
        </div>
        <div>
        <span class="block whitepace-nowrap text-gray-500">
        '. ucwords(__('translation.site_name')) .'
        </span>
        <span class="font-bold">
        '. $shopName .'
        </span>
        </div>
        <div>
        <span class="block whitepace-nowrap text-gray-500">
        '. ucwords(__('translation.status')) .'
        </span>
        <span class="font-bold">
        '. $strStatus .'
        </span>
        </div>
        </div>
        </div>
        <div class="col-span-1 lg:col-span-3">
        <div class="grid grid-cols-1 gap-2">
        <div>
        <span class="block whitepace-nowrap text-gray-500">
        '. ucwords(__('translation.product_name')) .'
        </span>
        <span class="font-bold">
        '. $wooProduct->product_name .'
        </span>
        </div>
        <div>
        <span class="block whitepace-nowrap text-gray-500">
        '. strtoupper(__('translation.SKU')) .'
        </span>
        <span class="font-bold">
        '. $strProductCode .'
        </span>
        </div>

        <div>
        <span class="block whitepace-nowrap text-gray-500">
        '.$getProductAttributes['attribute_1'] .'
        </span>
        <span class="font-bold">
        '. $getProductAttributes['attribute_1_option'] .'
        </span>
        </div>
        <div>
        <span class="block whitepace-nowrap text-gray-500">
        '.$getProductAttributes['attribute_2'].'
        </span>
        <span class="font-bold">
        '. $getProductAttributes['attribute_2_option'] .'
        </span>
        </div>
        <div>
        <span class="block whitepace-nowrap text-gray-500">
        '. ucwords(__('translation.quantity')) .'
        </span>
        <span class="font-bold">
        '. $wooProduct->quantity .'
        </span>
        </div>
        <div>
        <span class="block whitepace-nowrap text-gray-500">
        '. ucwords(__('translation.Discount')) .'
        </span>
        <span class="font-bold">
        '. $wooProduct->discount .'%
        </span>
        </div>
        </div>
        </div>
        <div class="col-span-1 lg:col-span-3">
        <div class="grid grid-cols-1 gap-2">
        <div>
        <span class="block whitepace-nowrap text-gray-500">
        '. ucwords(__('translation.price')) .'
        </span>
        <span class="font-bold">
        '. currency_symbol('THB') . $strPrice .'
        </span>
        </div>
        <div>
        <span class="block whitepace-nowrap text-gray-500">
        '. ucwords(__('translation.Regular Price')) .'
        </span>
        <span class="font-bold">
        '. $regularPrice .'
        </span>
        </div>
        <div>
        <span class="block whitepace-nowrap text-gray-500">
        '. ucwords(__('translation.Sale Price')) .'
        </span>
        <span class="font-bold">
        '. $salePrice .'
        </span>
        </div>
        <div>
        <span class="block whitepace-nowrap text-gray-500">
        '. $linkedCatelogText .'
        </span>
        <span class="block mb-2 font-bold">
        '. $catalogName .'
        </span>
        '. $editCatalogButn .'
        </div>
        <div>&nbsp;</div>
        </div>
        </div>
        </div>
        </div>
        </div>
        ';
    })

->addColumn('action', function ($wooProduct) {
    return '
    <a target="_blank" href="'. route('wc-products-edit', [ 'website_id' => $wooProduct->website_id, 'product_id' => $wooProduct->product_id ]) .'"><button type="button" class="btn-action--green"
    data-detail-url="' . route('wc_products.show', [ 'wc_product' => $wooProduct ]) . '"
    >
    <i class="bi bi-pencil"></i>
    </button>
    </a>
    <button type="button" class="btn-action--red"
    data-id="' . $wooProduct->id . '"
    onClick="deleteProduct(this)">
    <i class="bi bi-trash"></i>
    </button>
    ';
})
->rawColumns(['checkbox', 'woo_product_detail', 'action'])
->make(true);
}

$shops = WooShop::all();
$categories = Category::where('seller_id', Auth::user()->id)->get();
$products = WooProduct::all();

return view('wc.product', compact('products', 'shops'));
}

    /**
     * Sync woocommerce product by website / shop
     *
     * @param  \App\Http\Requests\WCProduct\SyncProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function syncProduct(SyncProductRequest $request)
    {
        try {
            $wooShopId = $request->shop_id;
            $number_of_products = $request->number_of_products;

            $shop = WooShop::where('id', $wooShopId)->first();

            $shopBaseUrl = $shop->site_url ?? '';
            $consumerKey = $shop->rest_api_key ?? '';
            $consumerSecret = $shop->rest_api_secrete ?? '';

            $url = $shopBaseUrl . "/wp-json/wc/v3/reports/products/totals?consumer_key={$consumerKey}&consumer_secret={$consumerSecret}";
            $json_product_qty = file_get_contents($url);
            $arr_product_groups = json_decode($json_product_qty, true);


            if ($number_of_products == '-1') {

                $allRecrods = 0;
                foreach ($arr_product_groups as $group) {
                    $allRecrods += $group["total"];
                }

                $number_of_products = $allRecrods;

            } else {
                $number_of_products = $request->number_of_products;

            }

            if (!empty($number_of_products)) {
                $this->insertData($number_of_products, $wooShopId, $shopBaseUrl, $consumerKey, $consumerSecret);
            }

            return $this->apiResponse(Response::HTTP_OK, ucfirst(__('translation.products_are_syncing')));

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ucfirst(__('translation.something_went_wrong')));
        }
    }

    public function productSyncAll(Request $request)
    {
        set_time_limit(-1);
        $shops = WooShop::all();
        foreach ($shops as $shop) {
            $result = "success";
            try {
                $number_of_products = 10;
                $website_id = $shop->id;
                $site_url = $shop->site_url;
                $consumer_key = $shop->rest_api_key;
                $consumer_secret = $shop->rest_api_secrete;

//                if($request->number_of_products=='-1'){
//                    $url = "$site_url/wp-json/wc/v3/reports/products/totals?consumer_key=$consumer_key&consumer_secret=$consumer_secret";
//                    $json_product_qty = file_get_contents($url);
//                    $arr_product_groups = json_decode($json_product_qty, true);
//                    $allRecrods = 0;
//                    foreach($arr_product_groups as $group){
//                        $allRecrods += $group["total"];
//                    }
//                    $number_of_products = $allRecrods;
//                }else{
                $number_of_products = 10;
//                }

                //echo $number_of_products.">>>>>>"; die;
                if (!empty($number_of_products)) {

                    $this->insertDataForCron($number_of_products, $website_id, $site_url, $consumer_key, $consumer_secret, $shop->seller_id);
                }
            } catch (\Exception $exception) {


            }


            $cron = new WooCronReport();
            $cron->type = "Product";
            $cron->shop_id = $shop->id;
            $cron->number_of_record_updated = $number_of_products;
            $cron->result = $result;
            $cron->save();
        }


    }

    public function insertDataForCron($number_of_products, $website_id, $site_url, $consumer_key, $consumer_secret, $seller_id)
    {

        if ($number_of_products >= 100) {
            $count = ($number_of_products / 100); // REST API ALLOW ONLY 100 RECORDS AT A TIME
            $mod = $number_of_products % 100;
            for ($i = 1; $i < $count; $i++) {
                $arr_records[$i] = 100;
            }
            if ($mod > 0) $arr_records[$i] = $mod;
        } else {
            $arr_records[1] = $number_of_products;
        }

        //echo "<pre>"; print_r($arr_records); die;
        foreach ($arr_records as $page => $per_page) {
            $limit = $per_page;
            $url_record = "$site_url/wp-json/wc/v3/products?consumer_key=$consumer_key&consumer_secret=$consumer_secret&pagination_type=page&page=$page&limit=$limit&per_page=$per_page";

            // 1. initialize
            $ch = curl_init();

            // 2. set the options, including the url
            curl_setopt($ch, CURLOPT_URL, $url_record);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            set_time_limit(12000); // THIS IS REQUIRED TO PLAY WITH LARGE DATA

            // 3. execute and fetch the resulting HTML output
            $output = curl_exec($ch);

            // 4. free up the curl handle
            curl_close($ch);


            if (!empty($output)) {
                $products = json_decode($output, true);
                //echo "<pre>"; print_r($products); echo "</pre>"; die;
                $i = 0;
                foreach ($products as $item) {
                    $product_id = $item['id'];
                    $status = $item['status'];
                    //delete if exist
                    WooProduct::where('product_id', $product_id)->where('website_id', $website_id)->delete();

                    $product = new WooProduct();
                    $product->seller_id = Auth::user()->id;

                    $product->website_id = $website_id;
                    $product->product_id = $item['id'];
                    $product->type = $item['type'];
                    if (!empty($item['variations'])) {
                        $product->variations = json_encode($item['variations']);
                        // Insert Variable product

                        $parent_id = $item['id'];
                        $number_of_products = count($item['variations']);
                        foreach ($item['variations'] as $child_id) {
                            $this->insertChildOrVairableProduct($item['name'], $number_of_products, $parent_id, $child_id, $website_id, $site_url, $consumer_key, $consumer_secret);
                        }

                    }

                    $product->images = json_encode($item['images']);

                    $product->product_name = $item['name'];
                    $product->product_code = $item['sku'];
                    //$product->meta_data = json_encode($item['meta_data']);
                    $product->created_at = $item['date_created'];
                    $product->updated_at = $item['date_modified'];
                    $product->status = $item['status'];
                    $product->quantity = $item['stock_quantity'];
                    $product->price = $item['price'];
//       $product->regular_price = $item['regular_price'];
//       $product->sale_price = $item['sale_price'];
                    $product->weight = $item['weight'];
                    if ($item['type'] == 'simple') {
                        $product->save();
                    }


                    if ($i % 100 == 0) {
                        usleep(5000);
                    }

                    $i++;

                }
            }

        }
    }

    /**
     * This method pulls out 100 records at a time
     * @param $number_of_products
     * @param $website_id
     * @param $site_url
     * @param $consumer_key
     * @param $consumer_secret
     */
    public function insertData($number_of_products, $website_id, $site_url, $consumer_key, $consumer_secret)
    {
        $arr_records = [];
        if ($number_of_products >= 100) {
            $count = ceil($number_of_products / 100)+1;
            $mod = $number_of_products % 100;
            for ($i = 1; $i < $count; $i++) {
                $arr_records[$i] = 100;
            }
            if ($mod > 0) $arr_records[$i] = ceil($mod)+1;
        } else {
            $arr_records[1] = $number_of_products;
        }

        foreach ($arr_records as $page => $per_page) {
            $auth_id = Auth::user()->id;
            WooProductSync::dispatch($per_page, $page, $site_url, $website_id, $consumer_key, $consumer_secret, $auth_id);
        }
    }


    public function insertChildOrVairableProduct($child_name, $number_of_products, $parent_id, $child_id, $website_id, $site_url, $consumer_key, $consumer_secret)
    {

        if ($number_of_products >= 100) {
            $count = ($number_of_products / 100);
            $mod = $number_of_products % 100;
            for ($i = 1; $i < $count; $i++) {
                $arr_records[$i] = 100;
            }
            if ($mod > 0) $arr_records[$i] = $mod;
        } else {
            $arr_records[1] = $number_of_products;
        }

        foreach ($arr_records as $page => $per_page) {
            $limit = $per_page;
            $url_record = "$site_url/wp-json/wc/v3/products/$parent_id/variations/$child_id?consumer_key=$consumer_key&consumer_secret=$consumer_secret&pagination_type=page&page=$page&limit=$limit&per_page=$per_page";
            $output = Http::get($url_record);
            if (!empty($output)) {
                $child = json_decode($output, true);
                $i = 0;
                //foreach($child_products as $child){
                $product_id = $child['id'];
                $status = $child['status'];
                //delete if exist
                WooProduct::where('product_id', $child_id)->where('website_id', $website_id)->delete();

                $child_product = new WooProduct();
                $child_product->seller_id = Auth::user()->id;

                $child_product->parent_id = $parent_id;
                $child_product->website_id = $website_id;
                $child_product->product_id = $child['id'];
                $child_product->type = "V-" . $parent_id;
                $child_images[] = $child['image'];
                $child_product->images = json_encode($child_images);
                $child_product->product_name = $child_name;
                $child_product->product_code = $child['sku'];
                $child_product->created_at = $child['date_created'];
                $child_product->updated_at = $child['date_modified'];
                $child_product->status = $child['status'];

                $child_product->quantity = $child['stock_quantity'];

                $child_product->price = $child['price'];
                $child_product->weight = $child['weight'];

                $child_product->save();

                if ($i % 100 == 0) {
                    usleep(500);
                }
                $i++;
            }
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
                return view('elements.form-add-child-product', compact(['shops', 'data', 'website_id']));
            }
        }
    }

    private function update_product_in_wc(WooProduct $product, Request $request)
    {
        $shop = WooShop::find($product->website_id);

        if (!$shop) return false;

        if (($product->images != '[null]') || ($product->images != '[]')) {
            $imageResources = json_decode($product->images);
        }

        if(empty($request->regular_price) && empty($request->sale_price)){
            $data = [
                'sku' => $request->product_code,
                //'stock_quantity' => $request->quantity,
                'description' => $request->description,
                'short_description' => $request->short_description,
                'type' => $request->product_type,
                'status' => $request->status
            ];
        }
        else{
            $data = [
                'sku' => $request->product_code,
                'regular_price' => $request->regular_price,
                'sale_price' => $request->sale_price,
                //'stock_quantity' => $request->quantity,
                'description' => $request->description,
                'short_description' => $request->short_description,
                'type' => $request->product_type,
                'status' => $request->status
            ];
        }

        //dd($data);
        $url = $shop->site_url . '/wp-json/wc/v3/products/' . $product->product_id
        . '?consumer_key=' . $shop->rest_api_key . '&consumer_secret=' . $shop->rest_api_secrete;
        //echo $url; exit;
        $response = Http::put($url, $data);

        if ($response->successful()) {
            //dd($response->json());
            return $response->json();
        }
        return false;
    }

    private function add_product_in_wc(Request $request)
    {

        $shop = WooShop::where('shop_id', $request->shop_id)->first();
        if (!$shop) return false;

        if(empty($request->regular_price) && empty($request->sale_price)){
            $data = [
                'name' => $request->product_name,
                'sku' => $request->product_code,
                'stock_quantity' => $request->quantity,
                'description' => $request->description,
                'short_description' => $request->short_description,
                'type' => $request->product_type,
                'status' => $request->status

            ];
        }
        else{
            $data = [
                'name' => $request->product_name,
                'sku' => $request->product_code,
                'regular_price' => $request->regular_price,
                'sale_price' => $request->sale_price,
                'stock_quantity' => $request->quantity,
                'description' => $request->description,
                'short_description' => $request->short_description,
                'type' => $request->product_type,
                'status' => $request->status

            ];
        }

        $url = $shop->site_url . '/wp-json/wc/v3/products/' .
        '?consumer_key=' . $shop->rest_api_key . '&consumer_secret=' . $shop->rest_api_secrete;

        $response = Http::post($url, $data);
        $responseJson = $response->json();

        if ($response->successful()) {
            return $responseJson;
        }
        return false;
    }

    private function add_variations_product_in_wc($isProduct, $shop_id, Request $request){

         // if product type will be variable and their is variation product isset
        $shop = WooShop::where('shop_id', $shop_id)->first();
        if (!$shop) return false;

        $products_variation=[];

        if($request->product_type == 'variable'){
            if(count($request->variation_code)>0){
                for($x=0; $x<count($request->variation_code); $x++){
                    $products_variation = [
                        'create' => [
                            'sku' => $request->variation_code[$x],
                            'regular_price' => $request->variation_price[$x],
                            'stock_quantity' => $request->variation_quantity[$x]
                        ]
                    ];
                }

                // print_r($products_variation);
                // exit;

                $url = $shop->site_url . '/wp-json/wc/v3/products/'.$isProduct['id'].'/variations/batch?consumer_key=' . $shop->rest_api_key . '&consumer_secret=' . $shop->rest_api_secrete;
                $response_v = Http::post($url, $products_variation);
                if ($response_v->successful()) {
                    dd($response_v->json());
                    return true;
                }

                // dd($response_v->json());



            }

        }
        return false;
    }

    private function add_product_images(Request $request)
    {
        // code will goes here
    }


    public function productUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'check' => 'required',
            'quantity' => 'required|numeric'
        ],);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        if ($request->quantity > 0) {

            $data = WooProductMainStock::where('product_id', $request->id)->first();
            if ($request->check == 1) {
                $quantity = $data->quantity + $request->quantity;
                $data->quantity = $quantity;
                $result = $data->save();

                $stockLog = new StockLog();
                $stockLog->product_id = $request->id;
                $stockLog->quantity = $request->quantity;
                $stockLog->seller_id = Auth::user()->id;
                $stockLog->date = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i A');
                $stockLog->check_in_out = 1;
                $stockLog->save();

                if ($result) {
                    return redirect()->back()->with('success', 'Product quantity successfully checked in');
                } else {
                    return redirect()->back()->with('error', 'Wrong Password');
                }
            } else {
                if ($data->quantity < $request->quantity) {
                    return redirect()->back()->with('danger', 'After CheckOut this quantity. It become less then zero. Please Insert valid quantity value');
                } else {
                    $quantity = $data->quantity - $request->quantity;
                    $data->quantity = $quantity;
                    $result = $data->save();

                    $stockLog = new StockLog();
                    $stockLog->product_id = $request->id;
                    $stockLog->quantity = $request->quantity;
                    $stockLog->seller_id = Auth::user()->id;
                    $stockLog->date = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i A');
                    $stockLog->check_in_out = 0;
                    $stockLog->save();
                    if ($result) {
                        return redirect()->back()->with('success', 'Product quantity successfully checked out');
                    } else {
                        return redirect()->back()->with('error', 'Wrong Password');
                    }
                }

            }


        }



        return redirect()->back()->with('danger', 'Number must be gretter then zero');

    }


    public function showInventoryLink(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->product_code) && $request->product_code != null) {
                $product_code = $request->product_code;
                $data = WooInventory::where('product_code', $product_code)->get();
                $shops = WooShop::all();
                return view('elements.form-product-page-link-product', compact(['data', 'product_code', 'shops']));
            }
        }
    }

    public function dataPurchaseOrder(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->id) && $request->id != null) {
                $product = Product::where([
                    'id' => $request->id
                ])->first();

                $id = $request->id;
                // $data = OrderPurchaseDetail::where('product_id',$id)->with('orderPurchase')->groupBy('order_purchase_id')->get();
                $data = WooOrderPurchaseDetail::where('product_id', $id)->with('orderPurchase')->get();
                // dd($data);

                return view('elements.woo-form-update-purchase-order', compact(['product', 'id', 'data']));
            }
        }
    }

    public function productData(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->id) && $request->id != null) {
                $data = WooProduct::where([
                    'id' => $request->id
                ])->first();

                $id = $request->id;

                return view('elements.woo-form-update-quantity', compact(['data', 'id']));
            }
        }
    }

    public function seeDetails($id)
    {
        $user = Auth::user();
        $quantityLogs = StockLog::where('product_id', $id)->with('seller')->get();
        $product = WooProduct::find($id);
        return view('seller.woo-qunatity-log-details', compact('quantityLogs', 'product'));
    }

    public function deleteQuantityLog(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Field id is required'
            ]);
        } else {

            $stockLog = StockLog::find($request->id);
            $productMainStock = WooProductMainStock::where('product_id', $stockLog->product_id)->first();
            if ($stockLog->check_in_out == 1) {
                $quantity = $productMainStock->quantity - $stockLog->quantity;
                if ($quantity >= 0) {
                    $productMainStock->quantity = $quantity;
                    $productMainStock->save();
                } else {
                    session::flash('danger', 'After Delete this quantity. It become less then zero. Please Insert valide quantity value');
                    return [
                        'status' => 1
                    ];
                }
            } else {
                $quantity = $productMainStock->quantity + $stockLog->quantity;
                $productMainStock->quantity = $quantity;
                $productMainStock->save();
            }
            DB::table('stock_logs')->where([
                'id' => $request->id
            ])->delete();

            session::flash('success', 'Stock Log item deleted successfully');
            return [
                'status' => 1
            ];
        }
    }

    public function updateQuantityLog(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'quantity' => 'required|numeric'
        ],);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        if ($request->quantity > 0) {
            $quantityLog = StockLog::find($request->id);
            // dump( $quantityLog);
            $productMainStock = WooProductMainStock::where('product_id', $quantityLog->product_id)->first();
            // dump( $productMainStock);
            // exit;
            if ($quantityLog->check_in_out == 1) {

                $quantity = $productMainStock->quantity - $quantityLog->quantity;
                $quantity = $quantity + $request->quantity;
                $productMainStock->quantity = $quantity;
                $result = $productMainStock->save();

                $quantityLog->quantity = $request->quantity;
                $quantityLog->save();


                if ($result) {
                    return redirect()->back()->with('success', 'Product quantity successfully Updated');
                } else {
                    return redirect()->back()->with('error', 'Wrong Password');
                }
            } else {
                if ($productMainStock->quantity < $request->quantity) {
                    return redirect()->back()->with('danger', 'After CheckOut this quantity. It become less then zero. Please Insert valide quantity value');
                } else {
                    $quantity = $productMainStock->quantity + $quantityLog->quantity;
                    $quantity = $quantity - $request->quantity;
                    $productMainStock->quantity = $quantity;
                    $result = $productMainStock->save();

                    $quantityLog->quantity = $request->quantity;
                    $quantityLog->save();

                    if ($result) {
                        return redirect()->back()->with('success', 'Product quantity successfully Updated');
                    } else {
                        return redirect()->back()->with('error', 'Wrong Password');
                    }
                }

            }


        }

        return redirect()->back()->with('danger', 'Number must be gretter then zero');

    }

    public function dataQuantityLog(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->id) && $request->id != null) {
                $data = StockLog::where([
                    'id' => $request->id
                ])->first();

                $id = $request->id;

                return view('elements.woo-form-update-quantity-log', compact(['data', 'id']));
            }
        }
    }

    public function bulkImport(Request $request)
    {
        if ($request->hasFile('file')) {
            $result = Excel::import(new BulkImport, request()->file('file'));
            if ($result) {
                return redirect()->back()->with('success', 'Bulk import Insert Successfully');
            } else {
                return redirect()->back()->with('danger', 'Something happened wrong');

            }
        }
    }

    public function bulkSync(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->jSonData) && $request->jSonData != null) {
                $arr = json_decode($request->jSonData);
                foreach ($arr as $webIDOrderID) {
                    $arrWebOrder = explode("*", $webIDOrderID);
                    $website_id = $arrWebOrder[0];
                    $id = $arrWebOrder[1];
                    $product_id = $arrWebOrder[2];


                    $product = WooProduct::find($id);

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
                        'stock_quantity' => $product->inventory_id,
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
                        WooProduct::where('id', $product->id)->update(
                            [
                                'quantity' => json_decode($result)->stock_quantity,
                                'price' => json_decode($result)->price,
                                'product_code' => json_decode($result)->sku,
                            ]
                        );
                    }

                }

            }

            return response()->json(['succes' => true, "message" => __("Products synced successfully")]);
        }
    }


    /**
     * Delete specific Woo product cover image.
     */
    public function deleteWooProductImage(Request $request)
    {
        try {

            if ($request->ajax()) {
                $product = WooProduct::where('id', $request->id)->first();

                if (!isset($product)) {
                    return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ucfirst(__('translation.No such variation product found.')));
                }
                $file_name = "";
                /* To check if the selected image file is missing in the system. */
                $image_file_is_missing = false;
                $destination_path = "";
                if (isset($request->image)) {
                    $file_name = $request->image;

                    if (!empty($file_name)) {
                        $data_images = [];
                        $product_images = $product->images;
                        if (isset($product_images) and !empty($product_images)) {
                            $product_images = json_decode($product_images);
                            // dd($product_images);

                            foreach ($product_images as $image) {
                                if(!empty($image->src)){
                                    if ($image->src == $file_name) {
                                        continue;
                                    } else {
                                        array_push($data_images, $image);
                                    }
                                }
                            }

                            $shop = WooShop::find($product->website_id);

                            if (!$shop){
                                return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ucfirst(__('translation.No Shop Found.')));
                            }

                            $data = [
                                'images' => json_encode($data_images),
                            ];

                            $url = $shop->site_url . '/wp-json/wc/v3/products/' . $product->parent_id .'/variations/'.$product->product_id
                            . '?consumer_key=' . $shop->rest_api_key . '&consumer_secret=' . $shop->rest_api_secrete;

                            $response = Http::put($url, $data);

                            if ($response->successful()) {
                                $product = WooProduct::find($product->id);
                                $product->images = json_encode($data_images);
                                $product->save();
                            }

                            return $this->apiResponse(Response::HTTP_OK, ucfirst(__('translation.Image Deleted Successfully')));
                        }

                    }
                }
            }
        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ucfirst(__('translation.something_went_wrong')));
        }
    }

    /**
     * Update shopee product image.
     */

    public function uploadWooProductImage(StoreWooProductRequest $request)
    {
        try {
            if ($request->ajax()) {
                $product = WooProduct::where('id', $request->id)->first();
                if (!isset($product)) {
                    return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ucfirst(__('translation.No such variation product found.')));
                }
                if($request->hasFile('image')){
                    $data = [];
                    $destination_path = "";
                    $image = $request->file('image');
                    $image_name = $request->file('image')->getClientOriginalName();
                    $path = Storage::disk('s3')->put('uploads/woo/products', $image, 'public');

                    $path = Storage::disk('s3')->url($path);
                    $image_arr = [
                        'src' => $path,
                        'alt' => $image_name,
                    ];

                    $product_images = $product->images;
                    $product_images_all = [];
                    if(isset($product_images) && !empty($product_images)){
                        $product_images_all = json_decode($product_images);
                        if(sizeof($product_images_all)>0){
                            array_push($product_images_all, $image_arr);
                        }
                        else{
                            $product_images_all = [$image_arr];
                        }
                    }
                    else{
                        $product_images_all = [$image_arr];
                    }

                    $shop = WooShop::find($product->website_id);

                    if (!$shop){
                        return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ucfirst(__('translation.No Shop Found.')));
                    }

                    $data = [
                        'images' => json_encode($product_images_all),
                    ];

                    $url = $shop->site_url . '/wp-json/wc/v3/products/' . $product->parent_id .'/variations/'.$product->product_id
                    . '?consumer_key=' . $shop->rest_api_key . '&consumer_secret=' . $shop->rest_api_secrete;

                    $response = Http::put($url, $data);

                    if ($response->successful()) {
                        $product = WooProduct::find($product->id);
                        $product->images = json_encode($product_images_all);
                        $product->save();
                    }
                    return $this->apiResponse(Response::HTTP_OK, ucfirst(__('translation.New Image Uploaded Successfully')));
                }

            }
        }
        catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ucfirst(__('translation.something_went_wrong')));
        }
    }

}
