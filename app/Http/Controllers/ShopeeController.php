<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatatableRequest;
use App\Http\Requests\Shopee\SyncProductRequest;
use App\Jobs\ShopeeProductDetailSync;
use App\Jobs\ShopeeProductSync;
use App\Jobs\ShopeeProductVariationInfoUpdate;
use App\Models\ShopeeProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ShopeeSetting;
use App\Models\Shopee;
use App\Models\ShopeeProductCategory;
use App\Traits\LineBotTrait;
use App\Traits\ShopeeOrderPurchaseTrait;
use App\Traits\ShopeeProductTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Ramsey\Uuid\Type\Decimal;
use Shopee\Client;
use Yajra\DataTables\DataTables;

class ShopeeController extends Controller
{
    use ShopeeOrderPurchaseTrait, ShopeeProductTrait, LineBotTrait;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return redirect(route('shopee.product.index'));
    }

    public function settings(Request $request)
    {
        $data['title'] = 'shopee-settings';
        $data['shops'] = Shopee::where('seller_id',Auth::user()->id)
            ->select('id','shop_id','shop_name','code')
            ->get();

        $data["shop_authorization_route"] = '';
        if(isset($request->shop_id)) {
            $shopee_shop = Shopee::whereShopId((int)$request->shop_id)->first();
            if (isset($shopee_shop)) {
                $data["selected_shop"] = $shopee_shop;
                $data["shop_authorization_route"] = route("shopee.update.shop", ["id" => $shopee_shop->id]);
            } else {
                $data["shop_authorization_route"] = route("shopee.add.shop");
            }
        }
        return view('shopee.settings', $data);
    }

    public function authorization(Request $request)
    {
        $shopeeSetting = ShopeeSetting::first();
        if($shopeeSetting !== null){
            $timestamp = time();
            $host = $shopeeSetting->host;
            $path = $shopeeSetting->path;
            $redirect_url = $shopeeSetting->redirect_url;
            $partner_id = $shopeeSetting->parent_id;
            $partner_key = $shopeeSetting->parent_key;
            $salt = $partner_id . $path . $timestamp;
            $sign = hash_hmac('sha256', $salt, $partner_key);

            $url = $host . $path . '?partner_id=' . $partner_id . '&timestamp=' . $timestamp . '&sign=' . $sign . '&redirect=' . $redirect_url;
            return redirect()->to($url);
        } else {
            $validator = \Validator::make($request->all(), [
                'partner_id' => 'required|',
                'partner_key' => 'required',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        return false;
    }

    public function add(Request $request)
    {
        if (isset($request->shop_name, $request->code, $request->shop_id) and
            !empty($request->shop_name) and !empty($request->code) and !empty($request->shop_id)) {
            Shopee::create([
                'shop_name'         => $request->shop_name,
                'code'              => $request->code,
                'shop_id'           => (int) $request->shop_id,
                'token_updated_at'  => Carbon::now()->format("Y-m-d H:i:s"),
                'seller_id'         => Auth::user()->id
            ]);
            session()->flash('msg', __('translation.shopee.shop.added'));
        } else {
            session()->flash('msg', __('translation.Failed to add new shopee shop.'));
            return redirect()->back()->withInput();
        }
        return redirect()->route('shopee.settings');
    }

    public function update($id, Request $request)
    {
        $shop = Shopee::find($id);
        if(isset($shop)) {
            if(isset($request->shop_name)) {
                $shop->shop_name = $request->shop_name;
            }
            if(isset($request->shop_id)) {
                $shop->shop_id = (int) $request->shop_id;
            }
            if(isset($request->code)) {
                $shop->prev_code = $shop->code;
                $shop->code = $request->code;
                $shop->token_updated_at = Carbon::now()->format("Y-m-d H:i:s");
            }
            $shop->save();
            session()->flash('msg', __('translation.shopee.shop.updated'));
        } else {
            session()->flash('msg', __('translation.Failed to update shopee shop.'));
            return redirect()->back()->withInput();
        }
        return redirect()->route('shopee.settings');
    }

    public function delete($id)
    {
        Shopee::where('id',  $id)->delete();
        session()->flash('msg', __('translation.shopee.shop.deleted'));
        return redirect()->back();
    }

    public function product()
    {
        $sellerId = Auth::user()->id;
        $shops = Shopee::query()
            ->where('seller_id', $sellerId)
            ->orderBy('shop_name', 'asc')
            ->get();

        $data = [
            'shops' => $shops,
            'missing_info_options' => ShopeeProduct::getShopeeProductMissingInfoOptions()
        ];

        return view('shopee.product', $data);
    }

    /**
     * Sync shopee product by shop / website
     *
     * @param  \App\Http\Requests\Shopee\SyncProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function sync(SyncProductRequest $request)
    {
        try {
            $shopeeSetting = ShopeeSetting::first();
            $shop_id = (int) $request->shop_id;

            $client = new Client([
                'baseUrl' => $shopeeSetting->host,
                'secret' => $shopeeSetting->parent_key,
                'partner_id' => (int) $shopeeSetting->parent_id,
                'shopid' => (int) $shop_id,
            ]);

            $response = $client->item->getItemsList([
                'pagination_offset' => 100,
                'pagination_entries_per_page' => 100
            ]);

            $number_of_products = (int) $response->getData()['total'];

            for($i=0; $i <= $number_of_products; $i = $i + 100) {
                $response = $client->item->getItemsList([
                    'pagination_offset' => $i,
                    'pagination_entries_per_page' => 100
                ]);
                $shopee_items = $response->getData()['items'];

                foreach ($shopee_items as $index => $product){
                    ShopeeProductDetailSync::dispatch($product, $shopeeSetting, Auth::id())->delay(Carbon::now()->addSeconds(1));
                }
            }
            return $this->apiResponse(Response::HTTP_OK, ucfirst(__('translation.products_are_syncing')));
        } catch (\Exception $th) {
            $this->triggerPushMessage("Failed to start syncing products from \"Shopee\"");
            report($th);
            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ucfirst(__('translation.something_went_wrong')));
        }
    }

    /**
     * Handle server side datatable of Shopee Products
     *
     * @param  \App\Http\Requests\DatatableRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function data(DatatableRequest $request)
    {
        if ($request->ajax()) {
            $sellerId = Auth::user()->id;
            $websiteId = $request->get('website_id');
            $inventoryLinkStatus = $request->get('inventory_status');
            $missingInfo = $request->get('missing_info');
            $type = $request->get('type');

            $orderColumnIndex = $request->get('order')[0]['column'] ?? 1;
            $orderDir = $request->get('order')[0]['dir'] ?? 'asc';
            $search = $request->get('search')['value'] ?? '';

            $availableOrderColumn = [
                'id'
            ];

            $orderColumn = $availableOrderColumn[$orderColumnIndex] ?? 'id';

            $shopeeProducts = ShopeeProduct::query()
                ->where('seller_id', $sellerId)
                ->byWebsite($websiteId)
                ->byType($type)
                ->byMissingInfo($missingInfo)
                ->isLinked($inventoryLinkStatus)
                ->published()
                ->searchTable($search)
                ->with('shopee')
                ->with('catalog')
                ->orderBy($orderColumn, $orderDir);

            return DataTables::of($shopeeProducts)
                    ->addColumn('checkbox', function ($shopeeProduct) {
                        return $shopeeProduct->website_id . '*' . $shopeeProduct->id . '*' . $shopeeProduct->product_id;
                    })
                    ->addColumn('details', function ($shopeeProduct) {
                        $strType = $shopeeProduct->type;
                        if (in_array($shopeeProduct->type, ['variable', 'variation'])) {
                            $strType = 'variable';
                        }

                        $shopName = $shopeeProduct->shopee->shop_name ?? '-';

                        $image = '<img src="'. asset('No-Image-Found.png') .'" alt="'. $shopeeProduct->product_name .'" class="w-full h-auto" />';
                        if (($shopeeProduct->images != '[null]') || ($shopeeProduct->images != '[]')) {
                            $imageSources = json_decode($shopeeProduct->images);

                            $image = '<img src="'. asset('No-Image-Found.png') .'" alt="'. $shopeeProduct->product_name .'" class="w-full h-auto" />';
                            if (!empty($imageSources[0])) {
                                $image = '<img src="'. $imageSources[0] .'" alt="'. $shopeeProduct->product_name .'" class="w-full h-auto">';
                            }
                        }

                        $catalogName = '-';
                        if ($shopeeProduct->is_linked == ShopeeProduct::IS_LINKED_YES) {
                            $catalogName = $shopeeProduct->catalog->product_code;
                        }

                        $editCatalogButton = '
                            <button type="button"
                                class="btn-action--yellow-outline"
                                data-id="'. $shopeeProduct->id .'"
                                data-detail-url="'. route('shopee.product.show', [ 'id' => $shopeeProduct->id ] ) .'"
                                onClick="editLinkedCatalog(this)">
                                <i class="bi bi-bezier2"></i>
                                <span class="ml-2">
                                    '. ucwords(__('translation.edit_link')) .'
                                </span>
                            </button>
                        ';

                        $strStatus = ucfirst(str_replace('-', ' ', $shopeeProduct->status));
                        $strPrice = currency_number($shopeeProduct->price, 3);

                        $productCode = '-';
                        if (!empty($shopeeProduct->product_code)) {
                            $productCode = $shopeeProduct->product_code;
                        }

                        return '
                            <div class="flex flex-col sm:flex-row justify-between gap-4">
                                <div class="sm:w-1/4">
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
                                <div class="sm:w-full">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-8 gap-2 sm:gap-x-8">
                                        <div class="col-span-1 lg:col-span-2">
                                            <div class="grid grid-cols-1 gap-2">
                                                <div>
                                                    <span class="block whitepace-nowrap text-gray-500">
                                                        Type
                                                    </span>
                                                    <span class="font-bold">
                                                        '. $strType .'
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="block whitepace-nowrap text-gray-500">
                                                        Site Name
                                                    </span>
                                                    <span class="font-bold">
                                                        '. $shopName .'
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="block whitepace-nowrap text-gray-500">
                                                        Status
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
                                                        Product Name
                                                    </span>
                                                    <span class="font-bold">
                                                        '. $shopeeProduct->product_name .'
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="block whitepace-nowrap text-gray-500">
                                                        SKU
                                                    </span>
                                                    <span class="font-bold">
                                                        '. $productCode .'
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="block whitepace-nowrap text-gray-500">
                                                        Quantity
                                                    </span>
                                                    <span class="font-bold">
                                                        '. number_format($shopeeProduct->quantity, 0) .'
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-span-1 lg:col-span-3">
                                            <div class="grid grid-cols-1 gap-2">
                                                <div>
                                                    <span class="block whitepace-nowrap text-gray-500">
                                                        Price
                                                    </span>
                                                    <span class="font-bold">
                                                        '. currency_symbol('THB') . $strPrice .'
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="block whitepace-nowrap text-gray-500">
                                                        Linked Catalog
                                                    </span>
                                                    <span class="block mb-2 font-bold">
                                                        '. $catalogName .'
                                                    </span>
                                                    '. $editCatalogButton .'
                                                </div>
                                                <div>&nbsp;</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ';
                    })
                    ->addColumn('action', function ($shopeeProduct) {
                        return '<div style="width: 106px">
                            <a href="'.$this->getShopeePortalLinkForProduct()."/".(in_array($shopeeProduct->type, ["simple", "variable"])?$shopeeProduct->product_id:$shopeeProduct->parent_id).'" class="btn-action--blue" target="_blank">
                                <i class="bi bi-search"></i>
                            </a>
                            <a href="'.route("shopee.product.edit_page", ["id" => $shopeeProduct->id]).'" data-id="'.$shopeeProduct->id.'" class="btn-action--green shopee_product_edit_btn shopee_product_edit_btn__'.$shopeeProduct->id.'" target="_blank">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="BtnDelete btn-action--red" data-id="'.$shopeeProduct->id.'">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        ';
                    })
                    ->rawColumns(['checkbox', 'details', 'action'])
                    ->make(true);
        }
    }

    /**
     * Show the shope product details by id
     *
     * @param  int  $shopeProductId
     * @return \Illuminate\Http\Response
     */
    public function show($shopeProductId)
    {
        $sellerId = Auth::user()->id;

        $shopeeProduct = ShopeeProduct::query()
                        ->where('seller_id', $sellerId)
                        ->where('id', $shopeProductId)
                        ->with('catalog')
                        ->first();

        abort_if(!$shopeeProduct, Response::HTTP_NOT_FOUND, ucfirst(__('translation.data_not_found')));

        return $this->apiResponse(Response::HTTP_OK, 'Success', [
            'product' => $shopeeProduct
        ]);
    }


    public function edit(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->id) && $request->id != null) {
                $data['id'] = $request->id;
                $data['row_index'] = $request->row_index;
                $data['product'] = ShopeeProduct::where('id', $request->id)->first();

                abort_if(!$data['product'], Response::HTTP_NOT_FOUND, ucfirst(__('translation.data_not_found')));

                return view('elements.woo-form-update-product', $data);
            }
        }
        return false;
    }


    public function product_update(Request $request)
    {
        if ($request->ajax()) {
            $product = ShopeeProduct::where('product_id', $request->id)->first();

            if ($product) {
                $is_updated = $this->update_product_in_shopee($product, $request);
                if($is_updated) {
                    $product->product_name = $request->name;
                    $product->product_code = $request->sku;
                    $product->price = $request->price;
                    $product->quantity = $request->quantity;
                    $product->save();
                    session::flash('success', __('translation.shopee.product.updated'));
                } else {
                    session::flash('error', __('translation.shopee.shop.down'));
                }
            } else {
                session::flash('error', __('translation.shopee.product.not_found'));
            }
        }
    }


    private function update_product_in_shopee($product, $request)
    {
        try {
            $client = $this->getShopeeClient((int) $request->website_id);
            if (isset($client)) {
                $client->item->updateItem([
                    'item_id'       => (int) $request->id,
                    'name'          => $request->name,
                    'description'   => $request->description ?? "",
                    'item_sku'      => $request->sku ?? "",
                    'category_id'   => $category_id ?? 0,
                    'timestamp'     => time()
                ]);

                if($product->type == 'simple') {
                    if($product->quantity != $request->quantity) {
                        $client->item->updateStock([
                            'item_id'   => (int) $request->id,
                            'stock'     => (int) $request->quantity,
                            'timestamp' => time()
                        ]);
                    }
                    if($product->price != $request->price) {
                        $client->item->updatePrice([
                            'item_id'   => (int) $request->id,
                            'price'     => (float) $request->price,
                            'timestamp' => time()
                        ]);
                    }
                }

                if($product->image_path and !empty($image_path)) {
                    $client->item->updateItemImage([
                        'item_id'   => (int) $request->id,
                        'images'    => [$request->image],
                        'timestamp' => time()
                    ]);
                }
                return true;
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return false;
    }


    public function product_delete(Request $request)
    {
        $product = ShopeeProduct::find($request->id);
        if($product !== null) {
            $client = $this->getShopeeClient((int) $product->website_id);

            if($product->type == 'simple'):
                $client->item->delete([
                    'item_id' => (int) $product->product_id
                ]);
                $product->delete();
            elseif($product->type == 'variable' && $product->parent_id == 0):
                $client->item->delete([
                    'item_id' => (int) $product->product_id
                ]);
                $children = ShopeeProduct::where('parent_id', $product->product_id)->get();
                foreach ($children as $child):
                    $child->delete();
                endforeach;
                $product->delete();
            else:
                $client->item->deleteVariation([
                    'item_id' => (int) $product->parent_id,
                    'variation_id' => (int) $product->product_id
                ]);
                $product->delete();
            endif;

            session::flash('success', __('translation.shopee.product.deleted'));
            return [  'status' => 1 ];
        }

        session::flash('error', __('translation.shopee.shop.down'));
        return [
            'status' => 0,
            'message' => __('translation.shopee.shop.down')
        ];
    }
}
