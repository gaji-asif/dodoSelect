<?php

namespace App\Http\Controllers;

use App\Models\WooShop;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Automattic\WooCommerce\Client;

class WcShopController extends Controller
{
  /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shops = WooShop::where('seller_id',Auth::user()->id)->get();
        $data = WooShop::where('seller_id',Auth::user()->id)->get();
        
        return view('settings.wc_shop',compact('shops','data'));
    }

    public function wooSettings()
    {
        $data = WooShop::where('woo_shops.seller_id',Auth::user()->id)
        ->join('shops', 'shops.id', '=', 'woo_shops.shop_id')
        ->select('woo_shops.id','shops.name','shop_id','site_url','rest_api_key','rest_api_secrete')
        ->get();

        //dd($data);
        $title = 'woo-settings';
        $shops = Shop::where('seller_id',Auth::user()->id)->get();
        return view('settings.wc_shop',compact('shops','data', 'title'));
    }

    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $shop = new WooShop();
        $shop->shop_id = $request->shop_id;
        $shop->site_url = $request->site_url;
        $shop->rest_api_key = $request->rest_api_key;
        $shop->rest_api_secrete = $request->rest_api_secrete;
        $shop->seller_id = Auth::user()->id;
        $shop->save();

        if($shop)
        {

            try{
                $woocommerce = new Client(
                    $shop->site_url,
                    $shop->rest_api_key,
                    $shop->rest_api_secrete,
                    [
                        'version' => 'wc/v3',
                    ]
                );
                $existing_hooks = $woocommerce->get('webhooks', ['per_page' => 100, 'search' => env('APP_URL')]);

                $hooks_to_remove = array_column((Array) $existing_hooks, 'id' );

                $hooks_to_create = [
                    [
                        'name' => 'Product Created '.env('APP_NAME'),
                        'topic' => 'product.created',
                        'delivery_url' => route('product.webhook', ['id' => $shop->id])
                    ],
                    [
                        'name' => 'Product Updated '.env('APP_NAME'),
                        'topic' => 'product.updated',
                        'delivery_url' => route('product.webhook', ['id' => $shop->id])
                    ],
                    [
                        'name' => 'Product Deleted '.env('APP_NAME'),
                        'topic' => 'product.deleted',
                        'delivery_url' => route('product.webhook', ['id' => $shop->id])
                    ],
                    [
                        'name' => 'Order Created '.env('APP_NAME'),
                        'topic' => 'order.created',
                        'delivery_url' => route('order.webhook', ['id' => $shop->id])
                    ],
                    [
                        'name' => 'Order Updated '.env('APP_NAME'),
                        'topic' => 'order.updated',
                        'delivery_url' => route('order.webhook', ['id' => $shop->id])
                    ],
                    [
                        'name' => 'Order Deleted '.env('APP_NAME'),
                        'topic' => 'order.deleted',
                        'delivery_url' => route('order.webhook', ['id' => $shop->id])
                    ]
                ];

                $data = [
                    'create' => $hooks_to_create,
                    'delete' => $hooks_to_remove
                ];

                $woocommerce->post('webhooks/batch', $data);
;

            } catch (\Exception $e) {
                return redirect()->back()->with('success','Shop Added Successfully');
            }

            return redirect()->back()->with('success','Shop Added Successfully');
        }
        else{
         return redirect()->back()->with('danger','Something happened wrong');
        }
    }


    public function shopRefresh($id)
    {
        try{
            $shop = WooShop::where('id', $id)->first();
            $woocommerce = new Client(
                $shop->site_url,
                $shop->rest_api_key,
                $shop->rest_api_secrete,
                [
                    'version' => 'wc/v3',
                ]
            );
            $existing_hooks = $woocommerce->get('webhooks');

            $hooks_to_create = [
                [
                    'name' => 'Product Created '.env('APP_NAME'),
                    'topic' => 'product.created',
                    'delivery_url' => route('api.products.create')
                ],
                [
                    'name' => 'Product Updated '.env('APP_NAME'),
                    'topic' => 'product.updated',
                    'delivery_url' => route('api.products.update')
                ],
                [
                    'name' => 'Product Deleted '.env('APP_NAME'),
                    'topic' => 'product.deleted',
                    'delivery_url' => route('api.products.delete')
                ],
                [
                    'name' => 'Order Created '.env('APP_NAME'),
                    'topic' => 'order.created',
                    'delivery_url' => route('api.orders.create')
                ],
                [
                    'name' => 'Order Updated '.env('APP_NAME'),
                    'topic' => 'order.updated',
                    'delivery_url' => route('api.orders.update')
                ],
                [
                    'name' => 'Order Deleted '.env('APP_NAME'),
                    'topic' => 'order.deleted',
                    'delivery_url' => route('api.orders.delete')
                ]
            ];

            $hooks_to_remove = [];

            foreach ($hooks_to_create as $hook) {
                foreach ($existing_hooks as $existing_hook) {
                    if ($existing_hook->topic == $hook['topic'] && $existing_hook->delivery_url == $hook['delivery_url']) {
                        $hooks_to_remove[] = $existing_hook->id;
                    }
                }
            }

            $data = [
                'create' => $hooks_to_create,
                'delete' => $hooks_to_remove
            ];

            $woocommerce->post('webhooks/batch', $data);
        } catch (\Exception $e) {
            return redirect()->back()->with('success','Webhooks Updated  Successfully');
        }

        return redirect()->back()->with('success','Webhooks Updated  Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $shops = Shop::where('seller_id',Auth::user()->id)->get();
        $data = WooShop::where('seller_id',Auth::user()->id)->get();
        $editData = WooShop::where('id',$id)->where('seller_id',Auth::user()->id)->first();
        
        return view('settings.wc_shop',compact('shops','data','editData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }
    public function updateShop(Request $request, $id)
    {
        $shop = WooShop::find($id);
        $shop->shop_id = $request->shop_id;
        $shop->site_url = $request->site_url;
        $shop->rest_api_key = $request->rest_api_key;
        $shop->rest_api_secrete = $request->rest_api_secrete;
        $shop->seller_id = Auth::user()->id;
        $shop->save();

        if($shop)
        {
            return redirect('woo-settings')->with('success','Shop Updated Successfully');
        }
        else{
         return redirect('woo-settings')->with('danger','Something happened wrong');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function delete($id)
    {
        $shop = WooShop::where('id',$id)->where('seller_id',Auth::user()->id)->delete();
        if($shop){
            return redirect()->back()->with('success','Shop Deleted Successfully');
        }
        else{
         return redirect()->back()->with('danger','Something happened wrong');
        }
    }

    /**
     * @param $id
     */
    public function productWebhook($id)
    {

    }

    /**
     * @param $id
     */
    public function orderWebhook($id)
    {

    }
}

