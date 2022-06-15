<?php

namespace App\Http\Controllers;

use App\Actions\Product\AutoLinkToLazadaProduct;
use App\Actions\Product\AutoLinkToShopeeProduct;
use App\Actions\Product\AutoLinkToWooCommerceProduct;
use App\Actions\Product\SyncQtyLazadaProduct;
use App\Actions\Product\SyncQtyShopeeProduct;
use App\Actions\Product\SyncQtyWooCommerceProduct;
use App\Enums\MarketPlaceProductLinkedEnum;
use App\Enums\MarketPlaceProductStatusEnum;
use App\Enums\ProductLinkActionEnum;
use App\Enums\SalesChannelEnum;
use App\Http\Requests\InOutProductHistory\BulkDeleteRequest;
use App\Http\Requests\Product\AutoLinkProductRequest;
use App\Jobs\BulkAutoLink;
use App\Jobs\BulkAutoSync;
use App\Jobs\InventoryQtySync;
use App\Jobs\LazadaProductSync;
use App\Models\ActivityLog;
use App\Models\Lazada;
use App\Models\LazadaProduct;
use App\Models\LazadaSetting;
use App\Models\Permission;
use App\Models\ProductTag;
use App\Models\Shopee;
use App\Models\ShopeeProduct;
use App\Models\ShopeeSetting;
use App\Models\User;
use App\Models\WooProduct;
use App\Models\ShipmentProduct;
use App\Models\WooShop;
use App\Models\PoShipment;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Lazada\LazopClient;
use Lazada\LazopRequest;
use App\Http\Requests\Product\InventorySyncQtyRequest;
use App\Http\Requests\Product\ProductSaveLinkRequest;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Imports\BulkImport;
use App\Jobs\InventoryQtySyncLazada;
use App\Jobs\ShopeeProductQtySync;
use App\Jobs\WooProductQtySync;
use App\Models\Product;
use App\Models\Category;
use App\Models\LazadaOrderPurchase;
use App\Models\LazadaOrderPurchaseItem;
use App\Models\OrderPurchase;
use App\Models\OrderPurchaseDetail;
use App\Models\OrderManagement;
use App\Models\ProductMainStock;
use App\Models\ProductPrice;
use App\Models\Shop;
use App\Models\ShopeeOrderPurchase;
use App\Models\StockLog;
use App\Models\WooOrderPurchase;
use Bmatovu\LaravelXml\LaravelXml;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Datatables;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Shopee\Client;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * @return array
     */
    public function product()
    {
        $categories = Category::where('seller_id',Auth::user()->id)->where('parent_category_id', 0)->get();
        $sub_categories = Category::where('seller_id',Auth::user()->id)->where('parent_category_id', '!=', 0)->get();
        $shops = Shop::where('seller_id',Auth::user()->id)->get();

        if (Auth::user()->role == 'dropshipper'){
            $userPermissions = Permission::dropshipperProductPermissions(Auth::user()->dropshipper_id);
            $productCount = Product::whereIn('product_code', $userPermissions)->where('seller_id', Auth::user()->id)->count();

        } else
            $productCount = Product::where('seller_id', Auth::user()->id)->count();

        $product_tags = ProductTag::all();

        return view('seller.product', compact('categories', 'sub_categories', 'shops', 'productCount', 'product_tags'));
    }

    /**
     * Show details of `product` data
     *
     * @param  int  $productId
     * @return \Illuminate\Http\Response
     */
    public function show($productId)
    {
        $sellerId = Auth::user()->id;

        $product = Product::where('id', $productId)
                    ->where('seller_id', $sellerId)
                    ->with('category')
                    ->with('supplier')
                    ->with('getQuantity')
                    ->totalIncoming()
                    ->first();

        abort_if(!$product, 404);

        $data = [
            'product' => $product
        ];

        return view('seller.product.show', $data);
    }

    public function deleteChildSku(Request $request,$product_code)
    {
        $product = Product::where("product_code", $request->product_sku)->first();
        if($product){
            $extract_sku = explode(",", $product->child_products);
            $key_to_remove = array_search($product_code, $extract_sku);
            unset($extract_sku[$key_to_remove]);
            $product->child_products = implode(", ", $extract_sku);

            $child_product = Product::where("product_code", $product_code)->first();
            $child_product->child_of = NULL;
            $child_product->save();
        }
        $product->save();

        if($product->child_products == ""){
            $product->child_products = NULL;
            $product->save();
        }
        return $this->apiResponse(200, "Successfully deleted");
    }

    /**
     * @param Request $request
     * @param $product_code
     * @return mixed
     */
    public function addChildSku(Request $request, $product_code)
    {
        $product = Product::where("product_code", $product_code)->first();
        $product_sku = trim($request->product_sku);
        if($product){
            $is_valid_sku = Product::where("product_code", $product_sku)->first();
            if($is_valid_sku){
                if($is_valid_sku->child_of == NULL):
                    if($product->child_products != NULL){
                        $sku_s = $product->child_products.", ".$product_sku;
                        $sku_s = array_unique(array_map('trim', explode(",", $sku_s)));
                        $product->child_products = implode(", ",$sku_s);
                    } else {
                        $product->child_products = $product_sku;
                    }
                    $is_valid_sku->child_products = NULL;
                    $is_valid_sku->child_of = $product->product_code;
                    if($product_sku != $product_code):
                        $is_valid_sku->save();
                        $product->save();
                    else:
                        return redirect()->back()->with([
                            'msg' => 'Own product code cannot be added'
                        ]);
                    endif;
                else:
                    return redirect()->back()->with([
                        'msg' => 'Already linked to <a href="'.route('product.show',['id'=>product_id_by_code($is_valid_sku->child_of)]).'" target="_blank">'.$is_valid_sku->child_of.'</a>'
                    ]);
                endif;
            }
        }
        session()->flash('msg', 'Not a valid SKU');
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function searchChildSku(Request $request)
    {
        $sku_s = [];
        if($request->has('q')){
            $search = $request->q;
            $sku_s = Product::select("id", "product_code")
                ->where('product_code', 'LIKE', "%$search%")
                ->get();
        }

        return response()->json($sku_s->each->setAppends([]));
    }


    /**
     * Get reserved products quantity.
     *
     * @param string $sku
     * @return integer
     */
    private function getReservedQuantityForDodoProduct($sku)
    {
        try {
            /* Shopee */
            $shopee_count = 0;
            $shopee_order_purchases = ShopeeOrderPurchase::whereStatusCustom(strtolower(ShopeeOrderPurchase::ORDER_STATUS_PROCESSING))
                ->where('line_items', 'like', '%'.$sku.'%')
                ->get();
            foreach ($shopee_order_purchases as $order) {
                $items = json_decode($order->line_items);
                foreach ($items as $item) {
                    if ($item->item_sku == $sku || $item->variation_sku == $sku) {
                        $shopee_count += $item->variation_quantity_purchased;
                        break;
                    }
                }
            }

            /* Woo Commerce */
            $woo_commerce_count = 0;
            $woo_commerce_order_purchases = WooOrderPurchase::whereIn('status', [
                    strtolower(WooOrderPurchase::ORDER_STATUS_PENDING),
                    strtolower(WooOrderPurchase::ORDER_STATUS_ON_HOLD),
                    strtolower(WooOrderPurchase::ORDER_STATUS_PROCESSING)
                ])
                ->where('line_items', 'like', '%'.$sku.'%')
                ->get();
            foreach ($woo_commerce_order_purchases as $order) {
                $items = json_decode($order->line_items);
                foreach ($items as $item) {
                    if ($item->sku == $sku) {
                        $woo_commerce_count += $item->quantity;
                        break;
                    }
                }
            }

            /* Lazada */
            $lazada_count = 0;
            $lazada_order_purchase_items = LazadaOrderPurchaseItem::whereIn('status', [
                    strtolower(LazadaOrderPurchase::ORDER_STATUS_PENDING),
                    strtolower(LazadaOrderPurchase::ORDER_STATUS_PACKED),
                    strtolower(LazadaOrderPurchase::ORDER_STATUS_READY_TO_SHIP_PENDING)
                ])
                ->where('sku', 'like', '%'.$sku.'%')
                ->get();
            foreach ($lazada_order_purchase_items as $item) {
                $lazada_count += 1;
            }

            return $shopee_count + $woo_commerce_count + $lazada_count;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return 0;
    }


    public function data(Request $request)
    {
        if ($request->ajax()){
            if (isset($request->id) && $request->id != null) {
                $product = Product::findOrFail($request->id);
                $categories = Category::where('seller_id', Auth::user()->id)
                                    ->where('parent_category_id', '==', 0)
                                    ->get();
                $shops = Shop::where('seller_id', Auth::user()->id)->get();
                $productPrices = ProductPrice::where('seller_id', Auth::user()->id)
                                        ->where('product_id', $product->id)->get();
                $product_tags = ProductTag::all();

                $data = [
                    'product' => $product,
                    'categories' => $categories,
                    'shops' => $shops,
                    'productPrices' => $productPrices,
                    'product_tags' => $product_tags
                ];

                return view('elements.form-update-product', $data);
            }

            $productTag = $request->get('productTag', 0);
            $categoryId = $request->get('categoryId', '');

            $start = $request->get('start', 0);
            $limit = $request->get('length', 10);
            if ($limit < 1 OR $limit > 100) {
                $limit = 100;
            }

            $search = isset($request->get('search')['value'])
                    ? $request->get('search')['value']
                    : null;

            $orderColumnList = [
                'updated_at',
                'product_name',
                'price',
            ];

            $orderColumnIndex = isset($request->get('order')[0]['column'])
                                ? $request->get('order')[0]['column']
                                : 0;
            $orderColumnDir = isset($request->get('order')[0]['dir'])
                                ? $request->get('order')[0]['dir']
                                : 'desc';
            $orderColumnDir = 'desc';

            $orderColumn = $orderColumnList[$orderColumnIndex] ?? 'updated_at';

            if (Auth::user()->role == 'dropshipper') {
                $userRole = 'dropshipper';
                $userId = Auth::user()->dropshipper_id;
            } else {
                $userRole = 'member';
                $userId = Auth::id();
            }

            $data = Product::where('seller_id', Auth::user()->id)
                ->filterByProductTag($productTag)
                ->filterByCategory($categoryId)
                ->filterByAuthUser($userRole, $userId)
                ->searchTable($search)
                ->quantity()
                ->warehouseQuantity()
                ->reservedQuantity()
                ->displayReservedQty()
                ->totalIncoming()
                ->orderBy($orderColumn, $orderColumnDir)
                ->take($limit)
                ->skip($start)
                ->get();

            $totalData = Product::where('seller_id', Auth::user()->id)
                ->filterByProductTag($productTag)
                ->filterByCategory($categoryId)
                ->filterByAuthUser($userRole, $userId)
                ->searchTable($search)->count();


            $table = Datatables::of($data)
                ->addColumn('details', function($row) {
                    if (Storage::disk('s3')->exists($row->image) && !empty($row->image)){
                        $imageContent = '<img src="'.Storage::disk('s3')->url($row->image).'" class="w-full h-auto">';
                    }
                    else{
                        $imageContent = '<img src="'. asset('No-Image-Found.png') .'" class="w-full h-auto">';
                    }

                    $tagContent = '';
                    if (Auth::user()->role != 'dropshipper') {
                        foreach ($row->productTags as $tag) {
                            $tagContent .= ' <span class="badge-status--yellow my-2">' . ucwords($tag->name) . '</span> ';
                        }
                        $tagContent .= '<br>';
                    }

                    $cat_name = '';
                    $sub_cat_name = '';
                    $divider = '';

                    if(!empty($row->category_id)){
                        $sub_cat_details = Category::select('cat_name', 'parent_category_id')->where('seller_id', Auth::user()->id)->where("id", $row->category_id)->first();
                        if(!empty($sub_cat_details->parent_category_id)){
                            $cat_details = Category::select('cat_name')->where("parent_category_id", $sub_cat_details->parent_category_id)->first();
                        $cat_name = $cat_details->cat_name;
                        $sub_cat_name = $sub_cat_details->cat_name;
                        $divider = '>';
                        }
                    }

                    $wooProductsCount = WooProduct::where('seller_id', Auth::user()->id)->where('dodo_product_id', $row->id)->where('status', 'publish')->count();
                    $shopeeProductsCount = ShopeeProduct::where('seller_id', Auth::user()->id)->where('dodo_product_id', $row->id)->where('status', 'publish')->count();
                    $lazadaProductsCount = LazadaProduct::where('seller_id', Auth::user()->id)->where('dodo_product_id', $row->id)->where('status', 'publish')->count();
                    $totalProductsLinkedCount = $wooProductsCount + $shopeeProductsCount + $lazadaProductsCount;

                    $poOrderQtyTotal =  DB::select(DB::raw("
                    SELECT SUM(order_purchase_details.quantity) as total_order FROM `order_purchase_details`
                    LEFT JOIN order_purchases ON order_purchases.id = order_purchase_details.order_purchase_id
                    WHERE `order_purchases`.`status` <> 'close' AND order_purchase_details.product_id=$row->id
                    "));

                    $poShipmentDetails =  DB::select(DB::raw("
                    SELECT SUM(po_shipment_details.ship_quantity) as total_ship FROM `po_shipment_details`
                    LEFT JOIN po_shipments ON po_shipments.id = po_shipment_details.po_shipment_id
                    WHERE `po_shipments`.`status` <> 'close' AND po_shipment_details.product_id=$row->id
                    "));

                    $order_status_1 = OrderManagement::ORDER_STATUS_PROCESSING;

                    $totalReservedAmount = DB::select(DB::raw("
                    SELECT SUM(quantity) AS totalReserved
                    FROM `order_management_details`
                    LEFT JOIN order_managements ON order_management_details.order_management_id = order_managements.id
                    WHERE order_management_details.product_id = $row->id
                    AND order_managements.order_status IN($order_status_1)
                    "));

                    if($totalReservedAmount[0]->totalReserved == NULL || empty($totalReservedAmount[0]->totalReserved)){
                        $totalReservedQty = 0;
                    }
                    else{
                        $totalReservedQty = $totalReservedAmount[0]->totalReserved;
                    }
                    $reservedNotPaid = Product::getReservedNotPaid($row->id);
                    $pendingStockAmount = Product::getpendingStockAmount($row->id);

                    $reserveQtyContent = '';
                    $totalLinkedProductsCountContent = '';
                    $reservedNotPaidContent = '';
                    $pendingStockQtyContent = '';

                    if (Auth::user()->role == 'dropshipper'){
                        $incomingContent = '<label class="text-gray-700">Dropship Price:</label>'. currency_symbol('THB') . ($row->dropship_price > 0 ? number_format($row->dropship_price) : number_format($row->price));
                        $quantityContent = number_format($row->quantity);
                    }
                    else{
                        $total_order = isset($poOrderQtyTotal[0]->total_order) ? $poOrderQtyTotal[0]->total_order : 0;
                        $total_shipped = isset($poShipmentDetails[0]->total_ship) ? $poShipmentDetails[0]->total_ship : 0;
                        $incomingContent = '<label class="text-gray-700">Incoming:</label>
                                            <button type="button" class="bg-transparent border-0 underline outline-none focus:outline-none" id="BtnProduct" data-id="'. $row->id .'">
                                                '. number_format($total_shipped) .'
                                            </button>';

                        $quantityContent = '<label class="text-gray-700">Warehouse Qty: </label><span class="text-gray-900"> '.number_format($row->warehouse_quantity).' </span> |
                                            <label class="text-gray-700">Reserved: </label>
                                            <a href="'. route('seller_reserved_quantity_log_details', [ 'id' => $row->id ]) .'" class="underline text-gray-900">
                                                '. number_format($row->reserved_quantity) .'
                                            </a> |
                                            <label class="text-gray-700">Quantity:</label>
                                            <a href="'. route('seller quantity details', [ 'id' => $row->id ]) .'" class="underline text-gray-900">
                                                '. number_format($row->quantity) .'
                                            </a>';

                        $reserveQtyContent = '';
                        $reservedNotPaidContent = '';
                        $pendingStockQtyContent = '';

                        $totalLinkedProductsCountContent = '<button style="font-size:11px !important;" type="button" class="btn-action--gray">
                                                       '. $totalProductsLinkedCount .' Product(s) Linked
                                                   </button>';
                    }

                    if($tagContent != '<br>'):
                        $tagContentHTML = '<span id="product_tag_div_'. $row->id . '">'. $tagContent . '</span>';
                    else:
                        $tagContentHTML = "";
                    endif;

                    $detailsContent =
                        '<div class="col-lg-12 row text-blue-500 font-bold mb-2">
                         '. $cat_name .' '.$divider.' '.$sub_cat_name .'
                        </div>
                        <div class="flex flex-row gap-4" id="__productDiv_'. $row->id . '">
                            <div class="w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5">
                                <div class="mb-3">
                                    '. $imageContent .'
                                </div>
                                <div class="mb-3">
                                    <span class="text-blue-500 font-bold">
                                        ID: '. $row->id .'
                                    </span>
                                </div>
                                '. $totalLinkedProductsCountContent .'
                            </div>
                            <div class="w-1/2 sm:w-2/3 md:w-3/4 lg:w-4/5">
                                <div class="grid grid-col-1 lg:gap-y-1">
                                    <div>
                                        '. $tagContentHTML . '
                                        <span class="font-bold">
                                            '. $row->product_name .'
                                        </span><br>
                                        <span class="text-blue-500 font-bold">
                                            '. $row->product_code .'
                                        </span>
                                    </div>
                                    <div class="whitespace-nowrap">
                                        <span class="font-bold text-lg">
                                            '. currency_symbol('THB') . number_format((float)$row->price, 2, '.', '') .'
                                        </span>
                                    </div>
                                    <div class="whitespace-nowrap">
                                        '. $quantityContent .'
                                    </div>
                                    '. $reserveQtyContent .'
                                    '.$reservedNotPaidContent.'
                                    '.$pendingStockQtyContent.'
                                    <div class="whitespace-nowrap">
                                        <label class="text-gray-700">Pieces/Pack:</label> '. number_format($row->pack) .'
                                    </div>
                                    <div class="whitespace-nowrap">
                                        '. $incomingContent .'
                                    </div>
                                    <div class="whitespace-nowrap">
                                        <label class="text-gray-700">Last Updated:</label> '. $row->updated_at->format('d-m-Y') .'
                                    </div>
                                </div>
                            </div>
                        </div>
                    ';
                    return $detailsContent;
                })
                ->addColumn('actions', function ($row) {
                    $actionContent = '';
                    if (Auth::user()->role != 'dropshipper'){
                        if($row->child_of != NULL):
                            $linkedQty = '<span class="py-1 px-4 my-2 hidden lg:block xl:block"  title="'. __('translation.Edit Product Tag') .'">
                                <strong>Linked qty:</strong><br /><a href="'.route('product.show', ['id'=>product_id_by_code($row->child_of)]).'">'.$row->child_of.'</a>
                            </span>';
                        else:
                            $linkedQty = "";
                        endif;
                        $actionContent = '
                        <div class="w-full text-center">
                            '.$linkedQty.'
                            <button class="modal-open badge-status--yellow btn-outline-dark border-0 py-1 px-4 my-2 hidden lg:block xl:block"  title="'. __('translation.Edit Product Tag') .'" x-on:click="showEditTagModal=true" data-id="' . $row->id . '" id="BtnProductTag">
                                Add Product Tag
                            </button>
                            <a href="'. route('product.show', [ 'id' => $row->id ]) .'" class="btn-action--green" title="'. __('translation.Detail') .'">
                                <i class="fas fa-info"></i>
                            </a>
                            <button type="button" class="modal-open btn-action--yellow" title="'. __('translation.Edit') .'" x-on:click="showEditModal=true" data-id="' . $row->id . '" id="BtnUpdate">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button type="button" class="btn-action--red" title="'. __('translation.Delete') .'" data-id="' . $row->id . '" id="BtnDelete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <button type="button" class="modal-open btn-action--blue" title="'. __('translation.Stock Adjust') .'" title="" x-on:click="showQuantityModal=true" data-id="' . $row->id . '" id="BtnQuantity">
                                <i class="fas fa-warehouse"></i>
                            </button>
                            <a href="'.route('product.inventory_sync', [ 'id' => $row->id ]) .'" class="btn-action--gray" title="'. __('translation.Inventory Sync') .'">
                                <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    ';
                    }
                    return $actionContent;
                })
                ->rawColumns(['details', 'actions'])
                ->skipPaging()
                ->setTotalRecords($totalData)
                ->make(true);

            return $table;
        }
        return false;
    }

    /**
     * Store new product
     *
     * @param ProductStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function insert(ProductStoreRequest $request)
    {
        try {
            if(Session::has('product_data')){
                $product = Session::forget('product_data');
            }

            $product = new Product();
            $product->category_id = $request->category_id;
            $product->product_name = $request->product_name;
            $product->product_code = $request->product_code;
            $product->supplier_id = $request->supplier_id;
            $product->specifications = $request->specifications;
            // $product->shop_id = $request->shop_id;
            // $product->warehouse_id = $request->warehouse_id;
            $product->price = $request->price;
            $product->dropship_price = $request->dropship_price ?? 0;
            $product->weight = $request->weight;
            $product->pack = $request->pack;
            $product->currency = $request->currency;
            $product->alert_stock = $request->alert_stock;
            $product->product_status = $request->product_status;
            $product->seller_id = Auth::User()->id;
            $product->from_where = 1;

            if ($request->hasFile('image')) {
                $upload = $request->file('image');
                $path =  Storage::disk('s3')->put('uploads/product', $upload,'public');
                $product->image = $path;
            }

            $product->save();

            $productMainStock = new ProductMainStock();
            $productMainStock->product_id = $product->id;
            $productMainStock->quantity = 0;
            $productMainStock->save();

            $given_tag = [];
            if(isset($request->product_tag) && count($request->product_tag) > 0){
                foreach ($request->product_tag as $item) {
                    $given_tag[] = $item;
                }
            }
            $product->tags()->attach($given_tag);

            Permission::create([
                'name' => $product->product_code,
                'product_id' => $product->id,
                'user_type' => 1
            ]);
            ActivityLog::updateProductActivityLog('Create new product ', $product->id);

            QrCode::generate($product->product_code, public_path('qrcodes/' . $product->product_code . '.svg'));
            session::flash('success', __('translation.products_msg.product.added'));
            return redirect()->back();

        } catch (\Throwable $th) {
            report($th);

            return redirect()->back()
                    ->with('error', $th->getMessage())
                    ->withInput();
        }
    }

    /**
     * Update the product data
     *
     * @param ProductUpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ProductUpdateRequest $request)
    {
        try {
            if(Session::has('product_data')){
                Session::forget('product_data');
            }

            $product = Product::find($request->id);
            $product->category_id = $request->category_id;
            $product->product_name = $request->product_name;
            $product->supplier_id = $request->supplier_id;
            $product->product_code = $request->product_code;
            $product->specifications = $request->specifications;
            $product->price =  $request->price;
            $product->dropship_price = $request->dropship_price;
            $product->weight =  $request->weight;
            $product->pack = $request->pack;
            $product->currency = $request->currency;
            $product->alert_stock = $request->alert_stock;
            $product->product_status = $request->product_status;
            if ($request->hasFile('image')) {
                if(Storage::disk('s3')->exists($product->image)) {
                    Storage::disk('s3')->delete($product->image);
                }

                $upload = $request->file('image');
                $path =  Storage::disk('s3')->put('uploads/product', $upload,'public');
                $product->image = $path;

            }

            $product->save();

            $given_tag = [];
            $tagContent = '';

            if(isset($request->product_tag) && count($request->product_tag) > 0){
                foreach ($request->product_tag as $item) {
                    $given_tag[] = $item;

                    $product_tag = ProductTag::find($item);
                    $tagContent .= ' <span class="badge-status--yellow my-2">'. ucwords($product_tag->name) .'</span> ';
                }
            }
            $product->tags()->detach();
            $product->tags()->attach($given_tag);

            $permission = Permission::where('product_id', '=', $product->id)->first();
            if ($permission){
                $permission->name = $product->product_code;
                $permission->update();
            }

            QrCode::generate($request->product_code, public_path('qrcodes/' . $request->product_code . '.svg'));

            return response()->json([
                'product_id' => $product->id,
                'tagContent' => $tagContent,
                'message' => 'Product updated successfully.'
            ]);


        } catch (\Exception $th) {
            report($th);

            return $this->apiResponse(500, "Something went wrong. {$th->getMessage()}");
        }
    }

    public function editProductTag(Request $request)
    {
        if (isset($request->id) && $request->id != null) {
            $product = Product::findOrFail($request->id);

            $product_tags = ProductTag::all();

            $data = [
                'product' => $product,
                'product_tags' => $product_tags
            ];

            return view('elements.form-update-product-tag', $data);
        }
    }

    public function updateProductTag(Request $request)
    {
        try {
            $product = Product::find($request->id);

            $given_tag = [];
            $tagContent = '';

            if(isset($request->product_tag) && count($request->product_tag) > 0){
                foreach ($request->product_tag as $item) {
                    $given_tag[] = $item;

                    $product_tag = ProductTag::find($item);
                    $tagContent .= ' <span class="badge-status--yellow my-2">'. ucwords($product_tag->name) .'</span> ';
                }
            }
            $product->tags()->detach();
            $product->tags()->attach($given_tag);

            return response()->json([
                'product_id' => $product->id,
                'tagContent' => $tagContent,
                'message' => 'Product tags updated successfully.'
            ]);

        } catch (\Throwable $th) {
            report($th);
            return $this->apiResponse(500, "Something went wrong. {$th->getMessage()}");
        }
    }

    public function delete(Request $request)
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

            if(session::has('product_data')){
                session::forget('product_data');
            }
            $product = Product::find($request->id);
            $path = "qrcodes/".$product->product_code.".svg";

            if(file_exists($path)){
                unlink($path);
            }
            if(file_exists($product->image)){
                unlink($product->image);
            }

            $permission = Permission::where('product_id', '=', $product->id)->first();
            if ($permission){
                $permission->delete();
            }
            ActivityLog::updateProductActivityLog('Delete product', $request->id, $product->product_name, $product->product_code);

            Product::destroy($request->id);

            return [
                'status' => 1
            ];
        }
    }

    public function productUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'check' => 'required',
            'quantity' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if(session::has('product_data')){
            session::forget('product_data');
        }

        if($request->quantity > 0)
        {
            $data = ProductMainStock::where('product_id',$request->id)->first();
            if($request->check == 1)
            {
                $values = array(
                    'product_id' => $request->id,
                    'quantity' => $request->quantity,
                    'seller_id' => Auth::user()->id,
                    'date' => Carbon::now(),
                    'check_in_out' => 1
                );
                $stockLog = StockLog::create($values);

                if ($stockLog) {
                    $dodoProduct = Product::find($request->id);
                    InventoryQtySync::dispatch($dodoProduct);

                    if($dodoProduct->child_products):
                        $child_sku = explode(",", $dodoProduct->child_products);
                        foreach ($child_sku as $child):
                            $dodoChildProduct = Product::query()
                                ->where('product_code', trim($child))
                                ->with('getQuantity')
                                ->firstOrFail();
                            InventoryQtySync::dispatch($dodoChildProduct, $dodoProduct->getQuantity->quantity)
                                ->delay(Carbon::now()->addSeconds(1));
                        endforeach;
                    endif;

                    session::flash('success', __('translation.products_msg.quantity.checkIn'));
                }
                else {
                    session::flash('error', __('translation.global.internal_server_error'));
                }
                return redirect()->back();
            }
            else{
                if($data->quantity < $request->quantity){
                    session::flash('danger', __('translation.products_msg.quantity.quantityAlert'));
                    return redirect()->back();
                }
                else
                {
                    $values = array(
                        'product_id' => $request->id,
                        'quantity' => $request->quantity,
                        'seller_id' => Auth::user()->id,
                        'date' => Carbon::now(),
                        'check_in_out' => 0
                    );
                    $stockLog = StockLog::create($values);

                    if ($stockLog) {
                        $dodoProduct = Product::find($request->id);
                        InventoryQtySync::dispatch($dodoProduct);

                        if($dodoProduct->child_products):
                            $child_sku = explode(",", $dodoProduct->child_products);
                            foreach ($child_sku as $child):
                                $dodoChildProduct = Product::query()
                                    ->where('product_code', trim($child))
                                    ->with('getQuantity')
                                    ->firstOrFail();
                                InventoryQtySync::dispatch($dodoChildProduct, $dodoProduct->getQuantity->quantity)
                                    ->delay(Carbon::now()->addSeconds(1));
                            endforeach;
                        endif;

                        session()->flash('success', __('translation.products_msg.quantity.checkOut'));
                    }
                    else {
                        session::flash('error', __('translation.global.internal_server_error'));
                    }
                    return redirect()->back();
                }
            }
        }
        session::flash('danger', __('translation.products_msg.quantity.quantityError'));
        return redirect()->back();
    }

    public function dataPurchaseOrder(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->id) && $request->id != null) {
                $productId = $request->id;

                $orderPurchaseTable = (new OrderPurchase())->getTable();
                $orderPurchaseDetailTable = (new OrderPurchaseDetail())->getTable();

                $product = Product::findOrFail($productId);
                /*$orderPurchaseDetails22 = OrderPurchaseDetail::with('orderPurchase')
                                    ->incomingQuantity()
                                    ->where("{$orderPurchaseDetailTable}.product_id", $productId)
                                    ->get();
                */
                $orderPurchaseCloseStatus = OrderPurchase::STATUS_CLOSE;
                $orderPurchaseDetails = DB::select(DB::raw("
                        select
                                *
                            from (
                                SELECT
                                PO.id as order_purchase_id,
                                POD.product_id as product_id,
                                ifnull(POD.total_qty, 0) AS tQty,
                                ifnull(PSD.total_ship, 0) AS sQty,
                                PO.order_date,
                                PO.ship_date,
                                PO.e_a_d_f,
                                PO.e_a_d_t,
                                PO.status
                                FROM {$orderPurchaseTable} PO
                                    LEFT JOIN (
                                        SELECT product_id,order_purchase_id, SUM(quantity) AS total_qty
                                        FROM `{$orderPurchaseDetailTable}`
                                        where product_id ={$productId}
                                        GROUP BY product_id,order_purchase_id
                                    ) POD ON POD.order_purchase_id = PO.id
                                    LEFT JOIN (
                                        SELECT order_purchase_id,product_id, SUM(ship_quantity) AS total_ship
                                        FROM po_shipment_details
                                        where product_id ={$productId}
                                        GROUP BY product_id,order_purchase_id
                                    ) PSD ON PSD.order_purchase_id = PO.id
                                where POD.product_id ={$productId}
                                AND PO.status = 'open'
                            ) t
                            group by t.product_id,t.order_purchase_id
                            "));


                $poShipmentDetails =  PoShipment::select(
                    'po_shipments.order_date',
                    'po_shipments.e_a_d_t',
                    'po_shipments.e_a_d_f',
                    'po_shipments.order_purchase_id',
                    'po_shipment_details.po_shipment_id',
                    'po_shipment_details.product_id',
                    'po_shipment_details.ship_quantity',
                    )
                ->join('po_shipment_details', 'po_shipments.id', '=', 'po_shipment_details.po_shipment_id')
                ->where( 'po_shipment_details.product_id',$productId)
                ->where( 'po_shipments.status' , PoShipment::STATUS_OPEN)
                ->get();
                //dd($orderPurchaseDetails);

                $totalOrderPurchaseDetails = 0;
                foreach($orderPurchaseDetails as $value){
                    $totalOrderPurchaseDetails += $value->tQty;
                }

                $totalPoShipmentDetails = 0;
                foreach($poShipmentDetails as $value){
                    $totalPoShipmentDetails += $value->ship_quantity;
                }

                $show_incoming_qty_not_shipped = '';
                if($totalOrderPurchaseDetails > 0 AND $totalPoShipmentDetails > 0){
                    $afterShipmentCount = $totalPoShipmentDetails - $totalOrderPurchaseDetails;
                    if($afterShipmentCount == 0){
                        $show_incoming_qty_not_shipped = 'y';
                    }
                    else{
                        $show_incoming_qty_not_shipped = 'n';
                    }
                    //echo $show_incoming_qty_not_shipped;exit;
                }


                return view('elements.form-update-purchase-order', compact(['product', 'productId', 'orderPurchaseDetails','poShipmentDetails', 'show_incoming_qty_not_shipped']));
            }
        }
    }

    public function productData(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->id) && $request->id != null) {
                $data = Product::where([
                    'id' => $request->id
                ])->first();

                $id = $request->id;

                return view('elements.form-update-quantity', compact(['data', 'id']));
            }
        }
    }

    /**
     * Show quantity logs table page.
     *
     * @param  int  $productId
     * @return \Illuminate\View\View
     */
    public function seeDetails($productId)
    {
        $product = Product::findOrFail($productId);

        $quantityLogCount = StockLog::where('product_id', $productId)
                                    ->where('is_defect', 0)
                                    ->count();

        $data = [
            'product' => $product,
            'quantityLogCount' => $quantityLogCount
        ];

        return view('seller.qunatity-log-details', $data);
    }

    /**
     * Handler the quantity-logs datatable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function seeDetailsDataTable(Request $request)
    {
        $data = [];

        $productId = $request->get('productId', 0);

        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $search = isset($request->get('search')['value'])
                ? $request->get('search')['value']
                : null;

        $orderColumnIndex = isset($request->get('order')[0]['column'])
                            ? $request->get('order')[0]['column']
                            : 2;
        $orderDir = isset($request->get('order')[0]['dir'])
                    ? $request->get('order')[0]['dir']
                    : 'desc';

        $availableColumnsOrder = [
            'id', 'id', 'check_in_out', 'quantity', 'date', 'seller_name'
        ];

        $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
                            ? $availableColumnsOrder[$orderColumnIndex]
                            : $availableColumnsOrder[6];

        $fields = StockLog::where('is_defect', 0)
                    ->where('product_id', $productId)
//                    ->with('seller', 'staff')
                    ->sellerNameAsColumn()
                    ->searchQuantityLogTable($search)
                    ->orderBy($orderColumnName, $orderDir)
                    ->take($limit)
                    ->skip($start)
                    ->get();

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $row = [];

                $row[] = $field->id;
                $row[] = $field->id;
                $row[] = $field->str_in_out;
                $row[] = number_format($field->quantity);

                $dateStr = '';
                if ($field->date != null) {
                    $dateStr = $field->date->format('Y-m-d H:i');
                }
                $row[] = $dateStr;

                $row[] = !empty($field->staff) ? $field->staff->name : $field->seller->name;

                $row[] = '
                    <button
                        type="button"
                        class="btn-action--green"
                        title="Edit Quantity"
                        data-id="'. $field->id .'"
                        onClick="editHistory(this)">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button
                        type="button"
                        class="btn-action--red"
                        title="Delete"
                        data-id="'. $field->id .'"
                        onClick="deleteHistory(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                ';

                $data[] = $row;
            }
        }

        $count_total = StockLog::where('is_defect', 0)->where('product_id', $productId)->count();
        $count_total_search = StockLog::where('is_defect', 0)->where('product_id', $productId)->searchQuantityLogTable($search)->count();

        $response = [
            'draw' => $request->get('draw'),
            'recordsTotal' => $count_total,
            'recordsFiltered' => $count_total_search,
            'data' => $data
        ];

        return response()->json($response);
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
            if(session::has('product_data')){
                session::forget('product_data');
            }
            $stockLog = StockLog::find($request->id);
            $productMainStock = ProductMainStock::where('product_id',$stockLog->product_id)->first();
            if($stockLog->check_in_out == 1)
            {
                $quantity = $productMainStock->quantity - $stockLog->quantity;
                if($quantity < 0)
                {
                    session::flash('danger', __('translation.products_msg.quantity.quantityAlert'));
                    return [
                        'status' => 1
                    ];
                }
            }

            StockLog::destroy($request->id);

            session::flash('success', __('translation.products_msg.stock.delete'));
            return [
                'status' => 1
            ];
        }
    }

    /**
     * Get shops as per sales channel
     * for inventory sync page
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShopsList(Request $request)
    {
        $salesChannel = $request->get('salesChannel', 0);
        $sellerId = Auth::user()->id;
        $data = [];

        if (!empty($salesChannel)) {
            if ($salesChannel == 'woo') {
                $data['shopList'] = WooShop::with('shops')->where('seller_id', $sellerId)->orderBy('site_url')->get();
            }
            elseif ($salesChannel == 'shopee') {
                $data['shops'] = Shopee::select('shop_name as name', 'shop_id as id')->where('seller_id', $sellerId)->get();
            }
            elseif ($salesChannel == 'lazada') {
                $data['shops'] = Lazada::select('shop_name as name', 'shop_id as id')->where('seller_id', $sellerId)->get();
            }
            return response()->json($data);
        }
    }

    /**
     * Get filter quantities as per sales channel
     * for inventory sync page
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilterQuantities(Request $request)
    {
        $productSyncId = $request->get('productSyncId', 0);
        $salesChannel = $request->get('salesChannel', 0);
        $sellerId = Auth::user()->id;

        if (!empty($salesChannel)) {
            if ($salesChannel == 'woo') {
                $productsCount = WooProduct::where('woo_products.seller_id', $sellerId)->where('status', 'publish')->where('type', '!=', 'variable')->joinedWooShopDataTable()->count();
                $productSelectedCount = WooProduct::where('woo_products.seller_id', $sellerId)->where('dodo_product_id', $productSyncId)->where('status', 'publish')->joinedWooShopDataTable()->count();
                $productAvailableCount = WooProduct::where('woo_products.seller_id', $sellerId)->where('is_linked', 0)->where('status', 'publish')->where('type', '!=', 'variable')->joinedWooShopDataTable()->count();
                $productUnavailableCount = WooProduct::where('woo_products.seller_id', $sellerId)->where('dodo_product_id', '!=', $productSyncId)->where('is_linked', 1)->where('status', 'publish')->joinedWooShopDataTable()->count();
            }
            elseif ($salesChannel == 'shopee') {
                $productsCount = ShopeeProduct::where('shopee_products.seller_id', $sellerId)->where('status', 'publish')->joinedShopeeShopDataTable()->count();
                $productSelectedCount = ShopeeProduct::where('shopee_products.seller_id', $sellerId)->where('dodo_product_id', $productSyncId)->where('status', 'publish')->joinedShopeeShopDataTable()->count();
                $productAvailableCount = ShopeeProduct::where('shopee_products.seller_id', $sellerId)->where('is_linked', 0)->where('status', 'publish')->joinedShopeeShopDataTable()->count();
                $productUnavailableCount = ShopeeProduct::where('shopee_products.seller_id', $sellerId)->where('dodo_product_id', '!=', $productSyncId)->where('is_linked', 1)->where('status', 'publish')->joinedShopeeShopDataTable()->count();
            }
            elseif ($salesChannel == 'lazada') {
                $productsCount = LazadaProduct::where('lazada_products.seller_id', $sellerId)->where('status', 'publish')->joinedLazadaShopDataTable()->count();
                $productSelectedCount = LazadaProduct::where('lazada_products.seller_id', $sellerId)->where('dodo_product_id', $productSyncId)->where('status', 'publish')->joinedLazadaShopDataTable()->count();
                $productAvailableCount = LazadaProduct::where('lazada_products.seller_id', $sellerId)->where('is_linked', 0)->where('status', 'publish')->joinedLazadaShopDataTable()->count();
                $productUnavailableCount = LazadaProduct::where('lazada_products.seller_id', $sellerId)->where('dodo_product_id', '!=', $productSyncId)->where('is_linked', 1)->where('status', 'publish')->joinedLazadaShopDataTable()->count();
            }

            return response()->json([
                'productsCount' => $productsCount,
                'productSelectedCount' => $productSelectedCount,
                'productAvailableCount' => $productAvailableCount,
                'productUnavailableCount' => $productUnavailableCount
            ]);
        }
    }

    /**
     * Show inventory sync table page.
     *
     * @param  int  $productId
     * @return \Illuminate\View\View
     */
    public function inventorySync($productId)
    {
        $sellerId = Auth::user()->id;

        $productSync = Product::query()
            ->where('seller_id', $sellerId)
            ->where('id', $productId)
            ->quantity()
            ->totalIncoming()
            ->firstOrFail();

        $shops = WooShop::query()
            ->where('seller_id', $sellerId)
            ->with('shops')
            ->orderBy('site_url')
            ->get();

        $wooProductsCount = WooProduct::query()
            ->where('woo_products.seller_id', $sellerId)
            ->where('status', MarketPlaceProductStatusEnum::publish()->value)
            ->where('woo_products.type', '<>', 'variable')
            ->joinedWooShopDataTable()
            ->count();

        $wooProductSelectedCount = WooProduct::query()
            ->where('woo_products.seller_id', $sellerId)
            ->where('dodo_product_id', $productId)
            ->where('status', MarketPlaceProductStatusEnum::publish()->value)
            ->joinedWooShopDataTable()
            ->count();

        $wooProductAvailableCount = WooProduct::query()
            ->where('woo_products.seller_id', $sellerId)
            ->where('is_linked', MarketPlaceProductLinkedEnum::no()->value)
            ->where('status', MarketPlaceProductStatusEnum::publish()->value)
            ->where('woo_products.type', '<>', 'variable')
            ->joinedWooShopDataTable()
            ->count();

        $wooProductUnavailableCount = WooProduct::query()
            ->where('woo_products.seller_id', $sellerId)
            ->where('dodo_product_id', '<>', $productId)
            ->where('is_linked', MarketPlaceProductLinkedEnum::yes()->value) // 1
            ->where('status', MarketPlaceProductStatusEnum::publish()->value)
            ->joinedWooShopDataTable()
            ->count();

        $shopeeProductsSelectedCount = ShopeeProduct::query()
            ->where('seller_id', $sellerId)
            ->where('dodo_product_id', $productId)
            ->where('status', MarketPlaceProductStatusEnum::publish()->value)
            ->count();

        $lazadaProductsSelectedCount = LazadaProduct::query()
            ->where('seller_id', $sellerId)
            ->where('dodo_product_id', $productId)
            ->where('status', MarketPlaceProductStatusEnum::publish()->value)
            ->count();

        $totalProductsSelectedCount = $wooProductSelectedCount + $shopeeProductsSelectedCount + $lazadaProductsSelectedCount;

        $linkedWooProductCodes = WooProduct::query()
            ->where('seller_id', $sellerId)
            ->where('dodo_product_id', $productId)
            ->where('status', MarketPlaceProductStatusEnum::publish()->value)
            ->get()
            ->implode('product_code', ', ');

        $linkedShopeeProductCodes = ShopeeProduct::query()
            ->where('seller_id', $sellerId)
            ->where('dodo_product_id', $productId)
            ->where('status', MarketPlaceProductStatusEnum::publish()->value)
            ->get()
            ->implode('product_code', ', ');

        $linkedLazadaProductCodes = LazadaProduct::query()
            ->where('seller_id', $sellerId)
            ->where('dodo_product_id', $productId)
            ->where('status', MarketPlaceProductStatusEnum::publish()->value)
            ->get()
            ->implode('product_code', ', ');

        return view('seller.product.inventory_sync', compact(
                'productSync', 'shops',
                'wooProductsCount', 'wooProductSelectedCount',
                'wooProductAvailableCount', 'wooProductUnavailableCount',
                'linkedWooProductCodes', 'totalProductsSelectedCount',
                'shopeeProductsSelectedCount', 'linkedShopeeProductCodes',
                'lazadaProductsSelectedCount', 'linkedLazadaProductCodes'
            )
        );
    }

    /**
     * Handle the inventory sync datatable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function inventorySyncDataTable(Request $request)
    {
        $data = [];
        $sellerId = Auth::user()->id;

        $productSyncId = $request->get('productSyncId', 0);
        $salesChannel = $request->get('salesChannel', 'woo');
        $shopFilter = $request->get('shopFilter', 0);
        $selectionFilter = $request->get('selectionFilter', '');

        $wooProductsTable = (new WooProduct())->getTable();
        $shopsTable = (new Shop())->getTable();
        $shopeeProductsTable = (new ShopeeProduct())->getTable();
        $shopeeTable = (new Shopee())->getTable();
        $lazadaProductsTable = (new LazadaProduct())->getTable();
        $lazadaTable = (new Lazada())->getTable();

        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $search = isset($request->get('search')['value'])
            ? $request->get('search')['value']
            : null;

        $orderColumnIndex = isset($request->get('order')[0]['column'])
            ? $request->get('order')[0]['column']
            : 2;
        $orderDir = isset($request->get('order')[0]['dir'])
            ? $request->get('order')[0]['dir']
            : 'desc';

        $availableColumnsOrder = [
            'id', 'id', 'product_name', 'website_id', 'type', 'price', 'quantity', 'is_linked'
        ];

        $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
            ? $availableColumnsOrder[$orderColumnIndex]
            : $availableColumnsOrder[6];

        if ($salesChannel == 'woo') {
            $fields = WooProduct::selectRaw("{$wooProductsTable}.*,
                                {$shopsTable}.name AS shop_name")
                ->where("{$wooProductsTable}.seller_id", $sellerId)
                ->where("{$wooProductsTable}.type", '!=', 'variable')
                ->where('status', 'publish')
                ->searchTable($search)
                ->shopFilter($shopFilter)
                ->filterSelected($selectionFilter, $productSyncId)
                ->joinedWooShopDataTable()
                ->orderBy($orderColumnName, $orderDir)
                ->take($limit)
                ->skip($start)
                ->get();

            $count_total = WooProduct::where("{$wooProductsTable}.seller_id", $sellerId)->where('status', 'publish')->where("{$wooProductsTable}.type", '!=', 'variable')->shopFilter($shopFilter)->filterSelected($selectionFilter, $productSyncId)->joinedWooShopDataTable()->count();
            $count_total_search = WooProduct::where("{$wooProductsTable}.seller_id", $sellerId)->where('status', 'publish')->where("{$wooProductsTable}.type", '!=', 'variable')->shopFilter($shopFilter)->filterSelected($selectionFilter, $productSyncId)->searchTable($search)->joinedWooShopDataTable()->count();
        }
        elseif ($salesChannel == 'shopee') {
            $fields = ShopeeProduct::selectRaw("{$shopeeProductsTable}.*,
                                {$shopeeTable}.shop_name")
                ->where("{$shopeeProductsTable}.seller_id", $sellerId)
                ->where('status', 'publish')
                ->searchTable($search)
                ->shopFilter($shopFilter)
                ->filterSelected($selectionFilter, $productSyncId)
                ->joinedShopeeShopDataTable()
                ->orderBy($orderColumnName, $orderDir)
                ->take($limit)
                ->skip($start)
                ->get();

            $count_total = ShopeeProduct::where('seller_id', Auth::user()->id)->where('status', 'publish')->filterSelected($selectionFilter, $productSyncId)->count();
            $count_total_search = ShopeeProduct::where('seller_id', Auth::user()->id)->where('status', 'publish')->filterSelected($selectionFilter, $productSyncId)->searchTable($search)->count();
        }
        elseif ($salesChannel == 'lazada') {
            $fields = LazadaProduct::selectRaw("{$lazadaProductsTable}.*,
                                {$lazadaTable}.shop_name")
                ->where("{$lazadaProductsTable}.seller_id", $sellerId)
                ->where('status', 'publish')
                ->searchTable($search)
                ->shopFilter($shopFilter)
                ->filterSelected($selectionFilter, $productSyncId)
                ->joinedLazadaShopDataTable()
                ->orderBy($orderColumnName, $orderDir)
                ->take($limit)
                ->skip($start)
                ->get();

            $count_total = LazadaProduct::where('seller_id', Auth::user()->id)->where('status', 'publish')->filterSelected($selectionFilter, $productSyncId)->count();
            $count_total_search = LazadaProduct::where('seller_id', Auth::user()->id)->where('status', 'publish')->filterSelected($selectionFilter, $productSyncId)->searchTable($search)->count();
        }

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $row = [];

                $linked = '';
                if ($field->is_linked == 1){
                    $linked = 'linked';
                }

                $checked = '';
                if ($field->dodo_product_id == $productSyncId){
                    $checked = 'checked';
                }

                $row[] = $field->id;

                $image = asset('No-Image-Found.png');
                if (($field->images != '[null]') || ($field->images != '[]')) {
                    $images = json_decode($field->images);
                    if (!empty($images[0])) {
                        if ($salesChannel == 'woo') {
                            $image = $images[0]->src;
                        } else {
                            $image = $images[0];
                        }
                    }
                }

                $imageContent =  '<img src="' . $image . '" width="75px" class="65px">
                                  <div><span class="text-blue-500 font-bold">ID: '. $field->id .'</span></div>';
                $row[] = $imageContent;

                $productContent = '<span class="hide">
                                       '. $checked . ' ' . $linked .'
                                   </span>
                                   <span class="font-bold">
                                       '. $field->product_name .'
                                   </span><br>
                                   <span class="text-blue-500 font-bold">
                                       '. $field->product_code .'
                                   </span>';
                $row[] = $productContent;

                $row[] = $field->shop_name;
                $row[] = in_array($field->type, ['variation']) ? 'variable' : $field->type;
                $row[] = '฿'.$field->price;
                $row[] = number_format($field->quantity);

                $linkButton = '<span id="link_product_'.$field->id.'">';
                if ($field->is_linked == 1){
                    if($productSyncId == $field->dodo_product_id){
                        $linkButton .= '<a href="javascript:void(0)" id="'.$field->id.'" class="btn-action--yellow unlink_product_id" title="Unlink product"><i id="'.$field->id.'" class="fas fa-unlink"></i></a>&nbsp;';
                    }
                    $linkButton .= '<a href="'. route('product.inventory_sync', [ 'id' => $field->dodo_product_id ]) .'" id="inventory_link_'.$field->id.'" class="btn-action--green" title="'. __('translation.Inventory Link') .'">
                                <i class="fas fa-vector-square"></i>
                            </a>';
                } else {
                    $linkButton .= '<a href="javascript:void(1)" id="'.$field->id.'" class="btn-action--blue link_product_id" title="Link product"><i id="'.$field->id.'" class="fas fa-link"></i></a>';
                }
                $linkButton .= '</span>';
                $row[] = $linkButton;
                $data[] = $row;
            }
        }

        $response = [
            'draw' => $request->get('draw'),
            'recordsTotal' => $count_total,
            'recordsFiltered' => $count_total_search,
            'data' => $data
        ];

        return response()->json($response);
    }

    /**
     * Link the product to the correct online shop
     *
     * @param  \App\Http\Requests\Product\ProductSaveLinkRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function saveProductLink(ProductSaveLinkRequest $request)
    {
        $requestData = $request->validated();

        $salesChannel = $requestData['salesChannel'] ?? '';
        $productChannelId = $requestData['sync_product_id'] ?? 0;
        $productId = $requestData['productSyncId'] ?? 0;
        $action = $requestData['action'] ?? '';

        switch ($salesChannel) {
            case SalesChannelEnum::woocommerce()->value:
                $product = WooProduct::findOrFail($productChannelId);
                break;

            case SalesChannelEnum::shopee()->value;
                $product = ShopeeProduct::findOrFail($productChannelId);
                break;

            case SalesChannelEnum::lazada()->value;
                $product = LazadaProduct::findOrFail($productChannelId);
                break;

            default:
                return $this->apiResponse(Response::HTTP_UNPROCESSABLE_ENTITY, __('translation.Invalid Sales Channel'));
                break;
        }

        $dodoProduct = Product::findOrFail($productId);

        switch ($action) {
            case ProductLinkActionEnum::attach()->value:
                $product->dodo_product_id = $dodoProduct->id;
                $product->is_linked = 1;
                $product->update();
                break;

            case ProductLinkActionEnum::detach()->value:
                $product->dodo_product_id = 0;
                $product->is_linked = 0;
                $product->update();
                break;

            default:
                return $this->apiResponse(Response::HTTP_UNPROCESSABLE_ENTITY, __('translation.Invalid Action'));
                break;
        }

        InventoryQtySync::dispatch($dodoProduct);

        return $this->apiResponse(Response::HTTP_OK, __('translation.Data successfuly updated'),
            ['linked_to_url' => route('product.inventory_sync', [ 'id' => $dodoProduct->id ])]
        );
    }

    /**
     * Bulk Link the product to the correct online shop
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function saveMultipleProductLinks(Request $request)
    {
        $salesChannel = $request->get('salesChannel', 'woo');

        if (isset($request->productSyncId) && isset($request->sync_product_id) && isset($request->action)) {
            $dodo_product = Product::find($request->productSyncId);

            if ($salesChannel == 'woo') {
                if ($request->action == 'attach') {
                    if (count($request->sync_product_id) > 0){
                        foreach ($request->sync_product_id as $sync_product_id){
                            $product = WooProduct::find($sync_product_id);
                            $product->dodo_product_id = $dodo_product->id;
                            $product->is_linked = 1;
                            $product->update();
                        }
                    }

                    return [
                        'status' => 1
                    ];
                }
                else if ($request->action == 'detach') {
                    if (count($request->sync_product_id) > 0){
                        foreach ($request->sync_product_id as $sync_product_id){
                            $product = WooProduct::find($sync_product_id);
                            $product->dodo_product_id = 0;
                            $product->is_linked = 0;
                            $product->update();
                        }
                    }
                    return [
                        'status' => 1
                    ];
                }
            }
            elseif ($salesChannel == 'shopee') {
                if ($request->action == 'attach') {
                    if (count($request->sync_product_id) > 0){
                        foreach ($request->sync_product_id as $sync_product_id){
                            $product = ShopeeProduct::find($sync_product_id);
                            $product->dodo_product_id = $dodo_product->id;
                            $product->is_linked = 1;
                            $product->update();
                        }
                    }

                    return [
                        'status' => 1
                    ];
                }
                else if ($request->action == 'detach') {
                    if (count($request->sync_product_id) > 0){
                        foreach ($request->sync_product_id as $sync_product_id){
                            $product = ShopeeProduct::find($sync_product_id);
                            $product->dodo_product_id = 0;
                            $product->is_linked = 0;
                            $product->update();
                        }
                    }
                    return [
                        'status' => 1
                    ];
                }
            }
            elseif ($salesChannel == 'lazada') {
                if ($request->action == 'attach') {
                    if (count($request->sync_product_id) > 0){
                        foreach ($request->sync_product_id as $sync_product_id){
                            $product = LazadaProduct::find($sync_product_id);
                            $product->dodo_product_id = $dodo_product->id;
                            $product->is_linked = 1;
                            $product->update();
                        }
                    }

                    return [
                        'status' => 1
                    ];
                }
                else if ($request->action == 'detach') {
                    if (count($request->sync_product_id) > 0){
                        foreach ($request->sync_product_id as $sync_product_id){
                            $product = LazadaProduct::find($sync_product_id);
                            $product->dodo_product_id = 0;
                            $product->is_linked = 0;
                            $product->update();
                        }
                    }
                    return [
                        'status' => 1
                    ];
                }
            }
        }
    }

    /**
     * Auto link the product to the all marketplace
     *
     * @param  \App\Http\Requests\Product\AutoLinkProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function inventoryAutoLink(AutoLinkProductRequest $request)
    {
        $sellerId = Auth::user()->id;
        $requestData = $request->validated();

        $dodoProduct = Product::query()
            ->where('seller_id', $sellerId)
            ->where('id', $requestData['product_id'])
            ->firstOrFail();

        AutoLinkToWooCommerceProduct::make()->handle($dodoProduct, $sellerId);
        AutoLinkToShopeeProduct::make()->handle($dodoProduct, $sellerId);
        AutoLinkToLazadaProduct::make()->handle($dodoProduct, $sellerId);

        return $this->apiResponse(Response::HTTP_OK, __('translation.Product has been linked'));
    }

    /**
     * Sync the product quantity to the market places
     *
     * @param  \App\Http\Requests\Product\InventorySyncQtyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function inventorySyncQuantity(InventorySyncQtyRequest $request)
    {
        $sellerId = Auth::user()->id;

        $requestData = $request->validated();
        $productId = $requestData['productSyncId'] ?? 0;

        $dodoProduct = Product::query()
            ->where('seller_id', $sellerId)
            ->where('id', $productId)
            ->with('getQuantity')
            ->firstOrFail();

        InventoryQtySync::dispatch($dodoProduct);

        if($dodoProduct->child_products):
            $child_sku = explode(",", $dodoProduct->child_products);
            foreach ($child_sku as $child):
                $dodoChildProduct = Product::query()
                    ->where('seller_id', $sellerId)
                    ->where('product_code', trim($child))
                    ->with('getQuantity')
                    ->firstOrFail();
                InventoryQtySync::dispatch($dodoChildProduct, $dodoProduct->getQuantity->quantity);
            endforeach;
        endif;

        return $this->apiResponse(Response::HTTP_OK, __('translation.We are syncing the product quantity in the background'));
    }

    public function bulkAutoLink()
    {
        $products = Product::all();

        foreach($products as $product) {
            BulkAutoLink::dispatch($product)->delay(Carbon::now()->addSeconds(1));;
        }

        session::flash('success', __('translation.products_msg.product_link.link_processing'));
        return redirect()->back();
    }

    public function bulkAutoSync()
    {
        $products = Product::all();

        foreach($products as $product) {
            if($product->child_of == NULL):
                BulkAutoSync::dispatch($product)->delay(Carbon::now()->addSeconds(1));
            endif;

            if($product->child_products):
                $child_sku = explode(",", $product->child_products);
                foreach ($child_sku as $child):
                    $dodoChildProduct = Product::query()
                        ->where('product_code', trim($child))
                        ->with('getQuantity')
                        ->firstOrFail();
                    InventoryQtySync::dispatch($dodoChildProduct, $product->getQuantity->quantity)
                        ->delay(Carbon::now()->addSeconds(1));
                endforeach;
            endif;
        }

        session::flash('success', __('translation.products_msg.product_link.sync_processing'));
        return redirect()->back();
    }

    /**
     * Bulk delete stock_logs data.
     *
     * If you're using eloquent models to delete `stock_logs`
     * No need to update `main_stocks` table manually,
     * It's already triggered by \App\Observers\StockLogObserver.
     *
     * @param  \App\Http\Requests\InOutProductHistory\BulkDeleteRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteQuantityLogBulk(BulkDeleteRequest $request)
    {
        try {
            $ids = $request->id;

            foreach ($ids as $id) {
                StockLog::destroy($id);
            }

            return response()->json([
                'message' => __('translation.Data successfully deleted.')
            ]);

        } catch (\Throwable $th) {
            report($th);

            return response()->json([
                'message' => __('translation.Sorry, something went wrong')
            ], 500);
        }
    }

    public function updateQuantityLog(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if(session::has('product_data')){
            session::forget('product_data');
        }
        if($request->quantity > 0)
        {
            $quantityLog = StockLog::find($request->id);
            $productMainStock = ProductMainStock::where('product_id',$quantityLog->product_id)->first();

            if($quantityLog->check_in_out == 1)
            {
                $quantity = $productMainStock->quantity - $quantityLog->quantity;
                $quantity = $quantity + $request->quantity ;
                $productMainStock->quantity = $quantity;
                $result = $productMainStock->save();

                $quantityLog->quantity = $request->quantity;
                $quantityLog->save();

                if ($result) {
                    session::flash('success', __('translation.products_msg.quantity.updated'));
                    return redirect()->back();
                } else {
                    session::flash('error', __('translation.global.internal_server_error'));
                    return redirect()->back();
                }
            }
            else{
                if($productMainStock->quantity < $request->quantity){
                    session::flash('error', __('translation.products_msg.quantity.quantityAlert'));
                    return redirect()->back();
                }
                else
                {
                    $quantity = $productMainStock->quantity + $quantityLog->quantity;
                    $quantity = $quantity - $request->quantity ;
                    $productMainStock->quantity = $quantity;
                    $result = $productMainStock->save();

                    $quantityLog->quantity = $request->quantity;
                    $quantityLog->save();

                    if ($result) {
                        session::flash('success', __('translation.products_msg.quantity.updated'));
                        return redirect()->back();
                    } else {
                        session::flash('error', __('translation.global.internal_server_error'));
                        return redirect()->back();
                    }
                }
            }
        }
        session::flash('success', __('translation.products_msg.quantity.quantityAlert'));
        return redirect()->back();
    }

    public function dataQuantityLog(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->id) && $request->id != null) {
                $data = StockLog::where([
                    'id' => $request->id
                ])->first();

                $id = $request->id;

                return view('elements.form-update-quantity-log', compact(['data', 'id']));
            }
        }
    }

    public function bulkImport(Request $request){
        if ($request->hasFile('file')) {
            $result =  Excel::import(new BulkImport,request()->file('file'));
            if(session::has('product_data')){
                session::forget('product_data');
            }

            if($result)
            {
                session::flash('success', __('translation.products_msg.import.bulk_import'));
            }
            else{
                session::flash('error', __('translation.global.internal_server_error'));
            }
            return redirect()->back();
        }
    }

    public function deleteBulkProduct(Request $request)
    {
        if(isset($request->product_code) && count($request->product_code)>0)
        {
            if(session::has('product_data')){
                  session::forget('product_data');
            }

            foreach($request->product_code as $row)
            {
                $product = Product::where('product_code',$row)->first();
                if(isset($product)) {
                    $path = "qrcodes/" . $product->product_code . ".svg";

                    if (file_exists($path)) {
                        unlink($path);
                    }
                    if (file_exists($product->image)) {
                        unlink($product->image);
                    }

                    $product->deleted_at = Carbon::now();
                    $product->update();

                    $permission = Permission::where('product_id', '=', $product->id)->first();
                    if ($permission) {
                        $permission->delete();
                    }

                    ActivityLog::updateProductActivityLog('Bulk delete product', $product->id, $product->product_name, $product->product_code);
                }
            }
            return [
                'status' => 1
            ];
        }
    }


    /**
     * Handle the select2 for `product` data
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function selectTwoHandler(Request $request)
    {
        $page = $request->get('page', 1);
        if ($page <= 1) {
            $page = 1;
        }

        $keyword = $request->get('search', '');
        $limit = 20;
        $skip = ($page - 1) * $limit;

        $products = Product::where('active_status', 1)->searchTable($keyword)->orderBy('product_name', 'asc')->take($limit)->skip($skip)->get();
        $productsCollection = collect($products)->map(function($product) {
            return [
                'id' => $product->id,
                'text' => $product->product_name . ' -- ' . $product->product_code
            ];
        });

        $productsTotal = Product::where('active_status', 1)->searchTable($keyword)->count();

        $responseData = [
            'results' => $productsCollection,
            'pagination' => [
                'more' => $page * $limit < $productsTotal
            ]
        ];

        return response()->json($responseData);
    }


    /**
     * Handle the select2 for `product` data by `seller_id`
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function selectTwoBySellerHandler(Request $request)
    {
        $page = $request->get('page', 1);
        if ($page <= 1) {
            $page = 1;
        }

        $keyword = $request->get('search', '');
        $limit = 10;
        $skip = ($page - 1) * $limit;

        $products = Product::where('seller_id', Auth::user()->id)->searchTable($keyword)->orderBy('product_name', 'asc')->take($limit)->skip($skip)->get();
        $productsCollection = collect($products)->map(function($product) {
            return [
                'id' => $product->product_code,
                'text' => $product->product_name . ' -- ' . $product->product_code
            ];
        });

        $productsTotal = Product::where('seller_id', Auth::user()->id)->searchTable($keyword)->count();

        $responseData = [
            'results' => $productsCollection,
            'pagination' => [
                'more' => $page * $limit < $productsTotal
            ]
        ];

        return response()->json($responseData);
    }

    public function dataOrderDetails(Request $request){
         if (isset($request->id) && $request->id != null) {
                $productId = $request->id;

                $product_names = Product::select('product_name')
                    ->where('id', $productId)
                    ->first();
                $order_status_1 = OrderManagement::ORDER_STATUS_PROCESSING;

                $orders = DB::select(DB::raw("
                    SELECT  order_managements.id, order_managements.created_at, order_management_details.quantity
                    FROM order_management_details
                    LEFT JOIN order_managements ON order_management_details.order_management_id = order_managements.id
                    WHERE order_management_details.product_id = $productId
                    AND order_managements.order_status IN($order_status_1)
                    "));

                  return view('seller.product.orders_products', compact('product_names', 'orders'));
         }
    }

    public function dataOrderDetailsReservedNotPaid(Request $request){
         if (isset($request->id) && $request->id != null) {
                $productId = $request->id;

                $product_names = Product::select('product_name')
                    ->where('id', $productId)
                    ->first();
                $order_status_1 = OrderManagement::ORDER_STATUS_PENDING;
                $order_status_2 = OrderManagement::ORDER_STATUS_PENDING_PAYMENT;
                $order_status_3 = OrderManagement::ORDER_STATUS_PAYMENT_UNCONFIRMED;

                $orders = DB::select(DB::raw("
                    SELECT  order_managements.id, order_managements.created_at, order_management_details.quantity
                    FROM order_management_details
                    LEFT JOIN order_managements ON order_management_details.order_management_id = order_managements.id
                    WHERE order_management_details.product_id = $productId
                    AND order_managements.order_status IN($order_status_1, $order_status_2, $order_status_3)
                    "));

                  return view('seller.product.orders_products_reserved_not_paid', compact('product_names', 'orders'));
         }
    }

    public function dataOrderDetailsPs(Request $request){
         if (isset($request->id) && $request->id != null) {
                $productId = $request->id;

                $product_names = Product::select('product_name')
                    ->where('id', $productId)
                    ->first();

                 $orders = ShipmentProduct::leftJoin('shipments', function($join) {
                 $join->on('shipment_products.shipment_id', '=', 'shipments.id');
                 })
                ->where('shipment_products.product_id', $productId)
                ->get([
                    'shipments.id',
                    'shipments.created_at',
                    'shipment_products.quantity',

                ]);

                return view('seller.product.orders_products_pending_stock', compact('product_names', 'orders'));
         }
    }
}
