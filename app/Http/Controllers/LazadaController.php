<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatatableRequest;
use App\Jobs\LazadaProductSync;
use App\Models\Lazada;
use App\Models\LazadaProduct;
use App\Models\LazadaSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Lazada\LazopClient;
use Lazada\LazopRequest;
use Lazada\Client;
use Lazada\Nodes\Item\Item;
use Yajra\DataTables\DataTables;

class LazadaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return redirect(route('lazada.product.index'));
    }

    public function settings()
    {
        $data['title'] = 'lazada-settings';
        $data['shops'] = Lazada::where('seller_id',Auth::user()->id)
            ->select('id','shop_id','shop_name','code', 'response')
            ->get();
        return view('lazada.settings', $data);
    }

    public function authorization(Request $request)
    {
        $lazadaSetting = LazadaSetting::first();
        if($lazadaSetting !== null){
            $host = $lazadaSetting->host;
            $redirect_url = $lazadaSetting->redirect_url;
            $app_id = $lazadaSetting->app_id;
            $url = $host.'?response_type=code&redirect_uri='.$redirect_url.'&client_id='.$app_id;
            return redirect()->to($url);
        } else {
            return redirect()->back();
        }
    }

    public function refresh_token($id, Request $request)
    {
        $shop = Lazada::find($id);
        $settings = LazadaSetting::first();
        if($shop !== null):
            try {
                $client = new LazopClient(
                    'https://api.lazada.com/rest',
                    $settings->app_id,
                    $settings->app_secret
                );

                $lazadaRequest = new LazopRequest('/auth/token/refresh');
                $lazadaRequest->addApiParam('refresh_token', json_decode($shop->response)->refresh_token);
                $response = $client->execute($lazadaRequest);
                $shop->response = $response;
                session()->flash('msg', __('translation.lazada.shop.token.refresh'));
            } catch (\Exception $e) {
                $response = json_encode([
                    'status' => $e->getMessage(),
                ]);
                $shop->response = $response;
                session()->flash('msg', $e->getMessage());
            }
            $shop->save();
        endif;

        return redirect()->route('lazada.settings');
    }

    public function add(Request $request)
    {
        $settings = LazadaSetting::first();
        $shop = Lazada::create([
            'shop_name' => $request->shop_name,
            'code' => $request->code,
            'shop_id' => 0,
            'seller_id' => Auth::user()->id
        ]);

        // Generate auth token
        if($shop):
            try {
                $client = new LazopClient(
                    'https://api.lazada.com/rest',
                    $settings->app_id,
                    $settings->app_secret
                );

                $lazadaRequest = new LazopRequest('/auth/token/create');
                $lazadaRequest->addApiParam('code', $request->code);
                $response = $client->execute($lazadaRequest);
                $shop->response = $response;
            } catch (\Exception $e) {
                $response = json_encode([
                    'status' => $e->getMessage(),
                ]);
                $shop->response = $response;
            }
            $shop->save();
        endif;

        session()->flash('msg', __('translation.lazada.shop.added'));
        return redirect()->route('lazada.settings');
    }

    public function update($id, Request $request)
    {
        $shop = Lazada::find($id);
        if($shop !== null):
            $shop->shop_name = $request->shop_name;
            if(isset($request->shop_name)):
                $shop->save();
            endif;
        endif;
        session()->flash('msg', __('translation.lazada.shop.updated'));
        return redirect()->back();
    }

    public function delete($id)
    {
        Lazada::where('id',  $id)->delete();
        session()->flash('msg', __('translation.lazada.shop.deleted'));
        return redirect()->back();
    }

    public function product()
    {
        $data['title'] = 'lazada-product';
        $data['shops'] = Lazada::all();
        return view('lazada.product', $data);
    }

    public function sync(Request $request)
    {
        $number_of_products = (int) $request->number_of_products;
        Cache::forget('lazada_nop_'.Auth::user()->id);
        Cache::forget('lazada_last_index_'.Auth::user()->id);

        $lazadaSetting = LazadaSetting::first();
        $shop = Lazada::find($request->shop_id);
        $access_token = json_decode($shop->response)->access_token;
        $website_id= $shop->id;

        if ($number_of_products == '-1' || empty($request->number_of_products)) :
            $client = new LazopClient(
                $lazadaSetting->regional_host,
                $lazadaSetting->app_id,
                $lazadaSetting->app_secret
            );

            $lazadaRequest = new LazopRequest('/products/get','GET');
            $lazadaRequest->addApiParam('filter','live');
            $lazadaRequest->addApiParam('offset','0');
            $lazadaRequest->addApiParam('limit','1');
            $lazadaRequest->addApiParam('options','1');
            $response = $client->execute($lazadaRequest, $access_token);
            $number_of_products = (int) json_decode($response)->data->total_products;
            Cache::add('lazada_nop_'.Auth::user()->id, $number_of_products);
            Cache::add('lazada_last_index_'.Auth::user()->id, 0);
        else:
            $number_of_products = (int) $request->number_of_products;
        endif;
        LazadaProductSync::dispatch($lazadaSetting, $access_token, $website_id, $number_of_products, Auth::user()->id);
    }

    /**
     * Handle server side datatable of Lazada Products
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

            $orderColumnIndex = $request->get('order')[0]['column'] ?? 1;
            $orderDir = $request->get('order')[0]['dir'] ?? 'asc';
            $search = $request->get('search')['value'] ?? '';

            $availableOrderColumn = [
                'id'
            ];

            $orderColumn = $availableOrderColumn[$orderColumnIndex] ?? 'id';

            $lazadaProducts = LazadaProduct::query()
                                ->where('seller_id', $sellerId)
                                ->byWebsite($websiteId)
                                ->isLinked($inventoryLinkStatus)
                                ->published()
                                ->searchTable($search)
                                ->with('lazada')
                                ->with('catalog')
                                ->orderBy($orderColumn, $orderDir);

            return DataTables::of($lazadaProducts)
                    ->addColumn('checkbox', function ($lazadaProduct) {
                        return $lazadaProduct->website_id . '*' . $lazadaProduct->id . '*' . $lazadaProduct->product_id;
                    })
                    ->addColumn('details', function ($lazadaProduct) {
                        $strType = $lazadaProduct->type;
                        if (in_array($lazadaProduct->type, ['variable', 'variation'])) {
                            $strType = 'variable';
                        }

                        $shopName = $lazadaProduct->lazada->shop_name ?? '-';

                        $image = '<img src="'. asset('No-Image-Found.png') .'" alt="'. $lazadaProduct->product_name .'" class="w-full h-auto" />';
                        if (($lazadaProduct->images != '[null]') || ($lazadaProduct->images != '[]')) {
                            $imageSources = json_decode($lazadaProduct->images);

                            $image = '<img src="'. asset('No-Image-Found.png') .'" alt="'. $lazadaProduct->product_name .'" class="w-full h-auto" />';
                            if (!empty($imageSources[0])) {
                                $image = '<img src="'. $imageSources[0] .'" alt="'. $lazadaProduct->product_name .'" class="w-full h-auto">';
                            }
                        }

                        $catalogName = '-';
                        if ($lazadaProduct->is_linked == lazadaProduct::IS_LINKED_YES) {
                            $catalogName = $lazadaProduct->catalog->product_code;
                        }

                        $editCatalogButton = '
                            <button type="button"
                                class="btn-action--yellow-outline"
                                data-id="'. $lazadaProduct->id .'"
                                data-detail-url="'. route('lazada.product.show', [ 'id' => $lazadaProduct->id ] ) .'"
                                onClick="editLinkedCatalog(this)">
                                <i class="bi bi-bezier2"></i>
                                <span class="ml-2">
                                    '. ucwords(__('translation.edit_link')) .'
                                </span>
                            </button>
                        ';

                        $strStatus = ucfirst(str_replace('-', ' ', $lazadaProduct->status));
                        $strPrice = currency_number($lazadaProduct->price, 3);

                        $productCode = '-';
                        if (!empty($lazadaProduct->product_code)) {
                            $productCode = $lazadaProduct->product_code;
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
                                                ID : '. $lazadaProduct->id .'
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
                                                        '. $lazadaProduct->product_name .'
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
                                                        '. number_format($lazadaProduct->quantity, 0) .'
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
                    ->addColumn('action', function ($lazadaProduct) {
                        return '
                            <a href="'.route('lazada.product.edit_page', ["id" => $lazadaProduct->parent_id]).'" target="_blank" class="btn-action--green">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="BtnDelete btn-action--red" data-id="' . $lazadaProduct->id . '" data-website_id="' . $lazadaProduct->website_id . '">
                                <i class="bi bi-trash"></i>
                            </button>
                            <button disabled type="button" x-on:click="showEditModal=true" class="modal-open btn-action--green" data-id="' . $lazadaProduct->id . '" id="BtnUpdate">
                                <i class="bi bi-pencil"></i>
                            </button>
                        ';
                    })
                    ->rawColumns(['checkbox', 'details', 'action'])
                    ->make(true);
        }

        $shops = Lazada::all();
        $products = LazadaProduct::all();
        return view('lazada.product', compact('products', 'shops'));
    }

    /**
     * Get the lazada product data by id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $sellerId = Auth::user()->id;

            $lazadaProduct = LazadaProduct::query()
                            ->where('seller_id', $sellerId)
                            ->where('id', $id)
                            ->with('catalog')
                            ->first();

            abort_if(!$lazadaProduct, Response::HTTP_NOT_FOUND, ucfirst(__('translation.data_not_found')));

            return $this->apiResponse(Response::HTTP_OK, ucfirst(__('translation.success')), [
                'product' => $lazadaProduct
            ]);

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ucfirst(__('translation.something_went_wrong')));
        }
    }

    public function edit(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->id) && $request->id != null) {
                $data['id'] = $request->id;
                $data['row_index'] = $request->row_index;
                $data['wooProduct'] = LazadaProduct::find($request->id);
                return view('lazada.edit', $data);
            }
        }

        return false;
    }

    public function product_update(Request $request)
    {
        if ($request->ajax()) {
            $product = LazadaProduct::where('product_id', $request->id)->first();

            if ($product) {
                $is_updated = $this->update_product_in_lazada($product, $request);
                if($is_updated) {
                    $product->product_name = $request->name;
                    $product->product_code = $request->sku;
                    $product->price = $request->price;
                    $product->quantity = $request->quantity;
                    $product->save();
                    session::flash('success', __('translation.lazada.product.updated'));
                } else {
                    session::flash('error', __('translation.lazada.shop.down'));
                }
            } else {
                session::flash('error', __('translation.lazada.product.not_found'));
            }
        }
    }

    private function update_product_in_lazada($product, $request)
    {
        $lazadaSetting = LazadaSetting::first();
        $shop_id = (int) $request->website_id;

        $client = new LazopClient([
            'baseUrl' => $lazadaSetting->regional_host,
            'secret' => $lazadaSetting->parent_key,
            'partner_id' => (int) $lazadaSetting->parent_id,
            'shopid' => (int) $shop_id
        ]);

        $response = false;
        if($product->product_name != $request->name || $product->code != $request->sku ):
            $response = $client->item->UpdateItem(
                [
                    'item_id' => (int) $request->id,
                    'name' => $request->name,
                    'item_sku' => $request->sku
                ]
            );
        endif;

        if($product->price != $request->price ):
            if($product->type == 'simple'):
                $response = $client->item->updatePrice(
                    [
                        'item_id' => (int) $request->id,
                        'price' => (float) $request->price,
                    ]
                );
            else:
                $response = $client->item->updateVariationPrice(
                    [
                        'item_id' => (int) $product->parent_id,
                        'variation_id' => (int) $product->product_id,
                        'price' => (float) $request->price,
                    ]
                );
            endif;
        endif;

        if($product->quantity != $request->quantity ):
            if($product->type == 'simple'):
                $response = $client->item->updateStock(
                    [
                        'item_id' => (int) $request->id,
                        'stock' => (int) $request->quantity,
                    ]
                );
            else:
                $response = $client->item->updateVariationStock(
                    [
                        'item_id' => (int) $product->parent_id,
                        'variation_id' => (int) $product->product_id,
                        'stock' => (int) $request->quantity,
                    ]
                );
            endif;
        endif;

        if($response->getData()['request_id'] != ""){
            return $response->getData();
        }
        return false;
    }

    public function product_delete(Request $request)
    {
        $product = lazadaProduct::find($request->id);
        if($product !== null) {
            $lazadaSetting = LazadaSetting::first();
            $client = new LazopClient([
                'baseUrl' => $lazadaSetting->regional_host,
                'secret' => $lazadaSetting->parent_key,
                'partner_id' => (int) $lazadaSetting->parent_id,
                'shopid' => (int) $product->website_id
            ]);

            if($product->type == 'simple'):
                $client->item->delete([
                    'item_id' => (int) $product->product_id
                ]);
                $product->delete();
            elseif($product->type == 'variable' && $product->parent_id == 0):
                $client->item->delete([
                    'item_id' => (int) $product->product_id
                ]);
                $children = lazadaProduct::where('parent_id', $product->product_id)->get();
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

            session::flash('success', __('translation.lazada.product.deleted'));
            return [  'status' => 1 ];
        }

        session::flash('error', __('translation.lazada.shop.down'));
        return [
            'status' => 0,
            'message' => __('translation.lazada.shop.down')
        ];
    }
}
