<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatatableRequest;
use App\Jobs\ShopeeGetBoostedProduct;
use App\Jobs\ShopeeSetBoostedProduct;
use App\Models\Shopee;
use App\Models\ShopeeProduct;
use App\Models\ShopeeProductBoost;
use App\Traits\ShopeeOrderPurchaseTrait;
use App\Traits\ShopeeProductBoostTrait;
use App\Traits\ShopeeProductTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

use function PHPUnit\Framework\isJson;

class ShopeeProductBoostController extends Controller
{
    use ShopeeOrderPurchaseTrait, ShopeeProductTrait, ShopeeProductBoostTrait;
    
    /* Limit for products to be boosted at a time for a shop. */
    private $boosted_product_limit;
    

    public function __construct()
    {
        $this->middleware('auth');
        $this->boosted_product_limit = $this->getBoostedProductLimit();
    }


    public function product()
    {
        $shops = Shopee::query()
            ->where('seller_id', $this->getShopeeSellerId())
            ->orderBy('shop_name', 'asc')
            ->get();
            
        $data = [
            'shops' => $shops,
            'missing_info_options' => ShopeeProduct::getShopeeProductMissingInfoOptions()
        ];

        return view('shopee.product.boost.index', $data);
    }


    /**
     * Handle server side datatable of Shopee Products
     *
     * @param  \App\Http\Requests\DatatableRequest $request
     * @return \Illuminate\Http\Response
     */
    public function data(DatatableRequest $request)
    {
        if ($request->ajax()) {
            $websiteId = $request->get('website_id');
            $boost_status = $request->get('boost_status');

            $orderColumnIndex = $request->get('order')[0]['column'] ?? 1;
            $orderDir = $request->get('order')[0]['dir'] ?? 'asc';
            $search = $request->get('search')['value'] ?? '';

            $availableOrderColumn = [
                'id'
            ];

            $orderColumn = $availableOrderColumn[$orderColumnIndex] ?? 'id';

            $shopeeProductsTable = (new ShopeeProduct())->getTable();
            $shopeeShopsTable = (new Shopee())->getTable();
            $shopeeProductBoostTable = (new ShopeeProductBoost())->getTable();

            /* Delete old "boosting" products. */
            if ($this->checkQueueForEmptySlotForBoostedProducts($websiteId)) {
                $this->deleteOldBoostingProducts($websiteId);
                ShopeeSetBoostedProduct::dispatch($websiteId);
            }

            $shopeeProducts = ShopeeProduct::select("{$shopeeProductsTable}.*", "{$shopeeShopsTable}.shop_id", "{$shopeeProductBoostTable}.item_id", "{$shopeeProductBoostTable}.status AS product_boost_status", "{$shopeeProductBoostTable}.boost_expires_at", "{$shopeeProductBoostTable}.repeat_boost")
                ->joinedDataTableWithBoostedInfo()
                ->bySellerId($this->getShopeeSellerId())
                ->byProductParentId(0)
                ->byShopeeWebsiteId($websiteId)
                ->byProductBoostStatus($boost_status)
                ->searchTable($search)
                ->orderBy($orderColumn, $orderDir);
                
            $queues_product_ids = $this->getQueueProductForBoosting($websiteId);

            return DataTables::of($shopeeProducts)
                ->addColumn('checkbox', function ($shopeeProduct) {
                    return $shopeeProduct->id;
                })
                ->addColumn('image', function ($shopeeProduct) {
                    $image = '<img src="'. asset('No-Image-Found.png') .'" alt="'. $shopeeProduct->product_name .'" class="w-full h-auto" />';
                    if (($shopeeProduct->images != '[null]') || ($shopeeProduct->images != '[]')) {
                        $imageSources = json_decode($shopeeProduct->images);

                        $image = '<img src="'. asset('No-Image-Found.png') .'" alt="'. $shopeeProduct->product_name .'" class="w-full h-auto" />';
                        if (!empty($imageSources[0])) {
                            $image = '<img src="'. $imageSources[0] .'" alt="'. $shopeeProduct->product_name .'" class="w-full h-auto">';
                        }
                    }

                    return '
                        <div class="flex flex-col sm:flex-row justify-between gap-4">
                            <div class="sm:w-3/4">
                                <div class="flex flex-col gap-2">
                                    <div class="w-full lg:w-24">
                                        '. $image .'
                                    </div>
                                    <div>
                                        <span class="text-blue-500 font-bold">
                                            ID : '. $shopeeProduct->id .'
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('details', function ($shopeeProduct) {
                    return '
                        <div class="flex flex-col sm:flex-row justify-between gap-4">
                            <div class="sm:w-full">
                                <div class="flex flex-col gap-2">
                                    <div class="w-full" style="max-width:200px;">
                                        '. $shopeeProduct->product_name .'
                                    </div>
                                    <div>
                                        <span class="text-blue-500 font-bold">
                                            SKU : '. $shopeeProduct->product_code .'
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('shop_name', function ($shopeeProduct) {
                    $shopName = $shopeeProduct->shopee->shop_name ?? '-';
                    return $shopName;
                })
                ->addColumn('type', function ($shopeeProduct) {
                    $type = ucwords($shopeeProduct->type);
                    $count = 0;
                    if ($type == "Variable") {
                        $count = ShopeeProduct::getChildProductCount($shopeeProduct->product_id);
                    }
                    return '
                        <div class="flex flex-col sm:flex-row justify-between gap-4">
                            <div class="sm:w-full">
                                <div class="flex flex-col gap-2">
                                    <div class="w-full">
                                        '. $type .'
                                    </div>
                                    <div class="w-full '.($type!="Variable"?"hide":"").'">
                                        <span>
                                            ('.$count.' variants)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('price', function ($shopeeProduct) {
                    return $shopeeProduct->price?currency_number($shopeeProduct->price, 3):0;
                })
                ->addColumn('quantity', function ($shopeeProduct) {
                    return $shopeeProduct->quantity ?? 0;
                })
                ->addColumn('action', function ($shopeeProduct) use ($queues_product_ids) {
                    $status = $shopeeProduct->product_boost_status;
                    if (!isset($status)) {
                        return '---';
                    } else if ($status == "queued") {
                        $queue_product_serial_no = array_search($shopeeProduct->product_id, $queues_product_ids);
                        if ($queue_product_serial_no >= 0) {
                            $html = '<div>'.ucwords($status).'<br/>['.($queue_product_serial_no+1).'/'.sizeof($queues_product_ids).']</div>
                                <div><button type="button" class="btn-action--red text-xs mt-1 remove_product_from_queue_btn remove_product_from_queue_'.$shopeeProduct->product_id.'_btn" data-product_id="'.$shopeeProduct->product_id.'" title="Remove From Queue"><i class="bi bi-trash text-base"></i></button></div>
                            ';
                            $html .= $this->getRepeatBoostingSameProductHtml($shopeeProduct->product_id, $shopeeProduct->repeat_boost);
                            return $html;
                        }
                    } else if ($status == "boosting") {
                        $diff = Carbon::now()->valueOf() - Carbon::parse($shopeeProduct->boost_expires_at)->valueOf(); 
                        if ($diff >= 0) {
                            $this->deleteSpecificOldBoostingProductFromDatabase($shopeeProduct->product_id, $shopeeProduct->website_id);
                            if ($shopeeProduct->repeat_boost) {
                                $this->addNewProductInQueueForBoostingInShopee($shopeeProduct->product_id, $shopeeProduct->website_id);
                            }
                            return "---";
                        }
                        $html = '<div class="boosting_product_timer" id="boosting_product_time_'.$shopeeProduct->id.'" data-product_id="'.$shopeeProduct->id.'" data-expires_at="'.$shopeeProduct->boost_expires_at.'">Boosting<br/><span>--</span></div>';
                        $html .= $this->getRepeatBoostingSameProductHtml($shopeeProduct->product_id, $shopeeProduct->repeat_boost);
                        return $html;
                    }
                    return ucwords($status);
                })
                ->rawColumns(['checkbox', 'image', 'details', 'type', 'sku', 'action'])
                ->make(true);
        }
    }


    /**
     * Get boosted products from "Shopee".
     */
    public function getBoostedProductsFromShopee(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->website_id) and !empty($request->website_id)) {
                $shopee_shop = $this->getShopeeShopBasedOnShopId($request->website_id);
                if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Shop not found")
                    ]);
                }

                ShopeeGetBoostedProduct::dispatch((int) $request->website_id);

                return response()->json([
                    "success"   => true,
                    "data"      => [],
                    "message"   => __("translation.Successfully fetched boosted products from \"Shopee\"")
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("translation.Failed to fetch boosted products from \"Shopee\"")
        ]);
    }


    /**
     * Set boosted products in "Shopee".
     */
    public function setBoostedProductsFromShopee(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->website_id, $request->product_ids) and 
                !empty($request->website_id) and !empty($request->product_ids) and isJson($request->product_ids)) {
                $shopee_shop = $this->getShopeeShopBasedOnShopId((int) $request->website_id);
                if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Shop not found")
                    ]);
                }

                $product_ids_json = json_decode($request->product_ids);
                if (sizeof($product_ids_json) == 0) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Not enough products")
                    ]);
                }

                $shopee_products = ShopeeProduct::select("id", "website_id", "product_code", "parent_id", "product_id")
                    ->whereWebsiteId($shopee_shop->shop_id)
                    ->whereParentId(0)
                    ->whereIn("id", $product_ids_json)
                    ->get();

                if (sizeof($shopee_products) == 0) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Invalid products")
                    ]);
                }

                /* Queue the products for "boosting". */
                foreach ($shopee_products as $product) {
                    /* Check if already product queued for boosting */
                    $already_queued = ShopeeProductBoost::whereItemId($product->product_id)
                        ->whereWebsiteId($shopee_shop->shop_id)
                        ->whereIn('status', ['boosting', 'queued'])
                        ->first();
                    if (!isset($already_queued)) {
                        /* Queue the products. */
                        $queued_product = new ShopeeProductBoost();
                        $queued_product->item_id = $product->product_id;
                        $queued_product->website_id = $shopee_shop->shop_id;
                        $queued_product->save();
                    }
                }

                /* Check products are in "boost" in "Shopee" */
                $boosting_products_count = ShopeeProductBoost::whereStatus('boosting')
                    ->whereWebsiteId($shopee_shop->shop_id)
                    ->where('boost_expires_at', '>=', Carbon::now()->format("Y-m-d H:i:s"))
                    ->count();
                if ($boosting_products_count < $this->boosted_product_limit) {
                    $queued_products = ShopeeProductBoost::whereStatus('queued')
                        ->whereWebsiteId($shopee_shop->shop_id)
                        ->orderBy('created_at', 'asc')
                        ->limit($this->boosted_product_limit-$boosting_products_count)
                        ->get();

                    if (sizeof($queued_products) > 0) {
                        $item_ids = [];
                        foreach ($queued_products as $product) {
                            array_push($item_ids, $product->item_id);
                        }

                        $response = $this->setBoostedProductsByShopeeApi($shopee_shop->shop_id, $item_ids);

                        if (isset($response["error"]) and $response["error"] == "error_banned") {
                            /* Can only boost 5 items during 4 hours. */
                            ShopeeGetBoostedProduct::dispatch($shopee_shop->shop_id, false, false);
                        } else {
                            /* Check for products successfully started boosting */
                            if (isset($response["successes"]) and sizeof($response["successes"]) > 0) {
                                foreach ($queued_products as $product) {
                                    if (in_array($product->item_id, $response["successes"])) {
                                        ShopeeGetBoostedProduct::dispatch($shopee_shop->shop_id, false, false);
                                        break;
                                    }
                                }
                            }

                            /* Check for products failed boosting */
                            if (isset($response["failures"]) and sizeof($response["failures"]) > 0) {
                                foreach ($response["failures"] as $failed_product) {
                                    if ($failed_product["error_code"] == "error_banned") {
                                        $duplicate_products = ShopeeProductBoost::whereItemId($failed_product["id"])
                                            ->whereStatus("queued")
                                            ->get();
                                        foreach ($duplicate_products as $duplicate_product) {
                                            $duplicate_product->delete();
                                        }
                                    }
                                }
                                /* Againg look for queued products and initiate again. */
                                ShopeeSetBoostedProduct::dispatch($shopee_shop->shop_id);
                            }
                        }
                    }
                }

                /* Remove products which have "boosting" status but not in $items */
                $this->deleteOldBoostingProducts((int) $request->website_id);
                
                return response()->json([
                    "success"   => true,
                    "data"      => [],
                    "message"   => __("translation.Successfully initiated the process to set products as boosted items")
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("translation.Failed to set products as boosted items")
        ]);
    }


    /**
     * Get total product are boosting for specific shopee shop.
     */
    public function getTotalBoostedProductsForSpecificShopeeShop(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->website_id) and !empty($request->website_id)) {
                $shopee_shop = $this->getShopeeShopBasedOnShopId($request->website_id);
                if (isset($shopee_shop, $shopee_shop->shop_id)) {
                    /* Check products are in "boost" in "Shopee" */
                    $boosting_products_count = ShopeeProductBoost::whereStatus('boosting')
                        ->whereWebsiteId($shopee_shop->shop_id)
                        ->where('boost_expires_at', '>=', Carbon::now()->format("Y-m-d H:i:s"))
                        ->count();
                    return response()->json([
                        "success"   => false,
                        "data"      => [
                            "count" => $boosting_products_count,
                            "limit" => $this->boosted_product_limit
                        ],
                        "message"   => __("translation.Failed to get total boosted products")
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "data"      => [
                "count" => 0,
                "limit" => $this->boosted_product_limit
            ],
            "message"   => __("translation.Failed to get total boosted products")
        ]);
    }


    /**
     * Toggle "repeat_boost" value.
     */
    public function updateBoostRepeatForQueuedBoostedProducts(Request $request) 
    {
        try {
            if ($request->ajax()) {
                if(isset($request->product_id, $request->website_id) and !empty($request->website_id) and !empty($request->product_id)) {
                    $queued_product = ShopeeProductBoost::whereItemId((int) $request->product_id)
                        ->whereWebsiteId((int) $request->website_id)
                        ->first();
                    if (!isset($queued_product)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __('translation.No such product found for Shopee.')
                        ]);
                    }

                    $message = "";
                    if ($queued_product->repeat_boost) {
                        $message = __('translation.Stoped from being repeating successfully');
                    } else {
                        $message = __('translation.Initiate repeating successfully');
                    }
                    
                    $queued_product->repeat_boost = !$queued_product->repeat_boost;
                    $queued_product->save();

                    return response()->json([
                        "success"   => true,
                        "message"   => $message
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __('translation.Failed to change the repeat status for boosting.')
        ]);
    }


    /**
     * Remove queued product for "boosting" from queue.
     */
    public function removeShopeeProductForBoostingFromQueue(Request $request) 
    {
        try {
            if ($request->ajax()) {
                if(isset($request->product_id, $request->website_id) and !empty($request->website_id) and !empty($request->product_id)) {
                    $queued_product = ShopeeProductBoost::whereItemId((int) $request->product_id)
                        ->whereWebsiteId((int) $request->website_id)
                        ->whereStatus("queued")
                        ->first();
                    if (!isset($queued_product)) {
                        return response()->json([
                            "success"   => false,
                            "message"   => __('translation.No such product found for Shopee.')
                        ]);
                    }
                    $queued_product->delete();

                    return response()->json([
                        "success"   => true,
                        "message"   => __('translation.Successfully removed product from queue.')
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __('translation.Failed to change the repeat status for boosting.')
        ]);
    }
}
