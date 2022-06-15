<?php
/**
 * API Controller was written for accepting
 * payload from WooCommerce to perform CRUD operations
 * @author Arnob (adstcare@gmail.com)
 * @since Jun 12th, 2021 @ 3:01pm
 */
namespace App\Http\Controllers;

use App\Jobs\AdjustDisplayReservedQty;
use App\Models\DoDoChat;
use App\Models\Shop;
use App\Models\WooOrderPurchase;
use App\Models\WooProduct;
use App\Models\WooShop;
use App\Traits\Inventory\AdjustDisplayReservedQtyTrait;
use App\Traits\Inventory\WooCommerceInventoryProductsStockUpdateTrait;
use Automattic\WooCommerce\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class ApiController
 * @package App\Http\Controllers
 */
class ApiController extends Controller
{
    use WooCommerceInventoryProductsStockUpdateTrait, AdjustDisplayReservedQtyTrait;

    private $topic;
    private $source_shop;

    public function __construct()
    {
        $this->topic = isset($_SERVER['HTTP_X_WC_WEBHOOK_TOPIC']) ? $_SERVER['HTTP_X_WC_WEBHOOK_TOPIC'] : '';
        $this->source_shop = isset($_SERVER['HTTP_X_WC_WEBHOOK_SOURCE']) ? $_SERVER['HTTP_X_WC_WEBHOOK_SOURCE'] : '';
    }

    /**
     * Order Create
     * @param Request $request
     * When order is placed in WooCommerce and all the Payload comes as request
     * @return string
     */
    public function orderCreatePayload(Request $request)
    {
        if( $shop = WooShop::is_valid($this->source_shop) ){
            $order = new WooOrderPurchase();
            $order->website_id = $shop->id;
            $order->order_id = $request->id;
            $order->product_id = $request->product_id ? $request->product_id : 0;
            $order->supplier_id = WooShop::get_supplier_id($shop->seller_id);
            $order->seller_id = $shop->seller_id;
            $order->reference = 'any';
            $order->status = $request->status;
            $order->billing = json_encode($request->billing, JSON_UNESCAPED_UNICODE);
            $order->shipping = json_encode($request->shipping, JSON_UNESCAPED_UNICODE);
            $order->line_items = json_encode($request->line_items, JSON_UNESCAPED_UNICODE);
            $order->shipping_lines = empty($request->shipping_lines) ? "" : json_encode($request->shipping_lines);
            $order->label_printed = 'any';
            $order->payment_method = $request->payment_method;
            $order->payment_method_title = $request->payment_method_title;
            $order->total = $request->total;
            $order->currency_symbol = $request->currency_symbol;
            $order->supply_from = 1;
            $order->order_date = $request->date_created;
            $order->order_created_block = true;
            $order->print_status = 0;
            $order->save();

            if (in_array(strtolower($request->status), [
                WooOrderPurchase::ORDER_STATUS_PROCESSING,
                // WooOrderPurchase::ORDER_STATUS_ON_HOLD,
                // WooOrderPurchase::ORDER_STATUS_PENDING,
            ])) {
                /* Update inventory quantity */
                if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($request->id, $shop->id, $this->getTagForWooCommercePlatform())) {
                    $this->initInventoryQtyUpdateForWooCommerce($order);
                }
            } else {
                /**
                 * Update "display_reserved_qty" for the dodo products in this order.
                 * NOTE:
                 * This will be triggered for any other status/status_custom other than "processing".
                 */
                if ($this->checkIfDisplayReservedQtyShouldBeUpdated($order->order_id, $order->website_id, $this->getTagForWooCommercePlatform())) {
                    AdjustDisplayReservedQty::dispatch($order->order_id, $order->website_id, $this->getTagForWooCommercePlatform())->delay(Carbon::now()->addSeconds(2));
                }
            }

            return json_encode([
                'status'    => 200,
                'message'   => json_encode('Order has been created successfully!')
            ]);
        }

        return json_encode([
            'status'    => 422,
            'message'   => "Shop is not valid!"
        ]);
    }

    /**
     * Order Update
     * @param Request $request
     * When order is placed in WooCommerce and all the Payload comes as request
     * Find the related order ID and -- Update Status --
     * @return string
     */
    public function orderUpdatePayload(Request $request)
    {
        if (WooShop::is_valid($this->source_shop)) {
            $order = WooOrderPurchase::whereOrderId($request->id)->first();
            if (isset($order)) {
                $order->status = $request->status;
                $order->updated_at = Carbon::now()->format('Y-m-d');
                $order->save();

                if (in_array(strtolower($request->status), [
                    WooOrderPurchase::ORDER_STATUS_PROCESSING,
                    // WooOrderPurchase::ORDER_STATUS_ON_HOLD,
                    // WooOrderPurchase::ORDER_STATUS_PENDING,
                ])) {
                    /* Update inventory quantity */
                    if (!$this->checkIfInventoryUpdatedForProductsInSpecificOrder($request->id, $order->website_id, $this->getTagForWooCommercePlatform())) {
                        $this->initInventoryQtyUpdateForWooCommerce($order);
                    }
                } else {
                    /**
                     * Update "display_reserved_qty" for the dodo products in this order.
                     * NOTE:
                     * This will be triggered for any other status/status_custom other than "processing".
                     */
                    if ($this->checkIfDisplayReservedQtyShouldBeUpdated($order->order_id, $order->website_id, $this->getTagForWooCommercePlatform())) {
                        AdjustDisplayReservedQty::dispatch($order->order_id, $order->website_id, $this->getTagForWooCommercePlatform())->delay(Carbon::now()->addSeconds(2));
                    }
                }

                return json_encode([
                    'status'    => 200,
                    'message'   => json_encode('Order has been updated successfully!')
                ]);
            }
        }

        return json_encode([
            'status'    => 422,
            'message'   => "Shop is not valid!"
        ]);
    }

    /**
     * Order Delete
     * @param Request $request
     * When a order is deleted from the WordPress then this statement executes
     * and deletes from the Database as well
     * @return string
     */
    public function orderDeletePayload(Request $request)
    {
        if(WooShop::is_valid($this->source_shop)):
            $order_id = $request->id;
            $order = WooOrderPurchase::where('order_id', $request->id)->delete();
            return json_encode([
                'status'    => 200,
                'message'   => json_encode('Order has been deleted successfully!')
            ]);
        endif;

        return json_encode([
            'status'    => 422,
            'message'   => "Shop is not valid!"
        ]);
    }

    /**
     * Product Create
     * @param Request $request
     * If product is created in WooCommerce this statement executes
     * and create in the Database as well
     * @return string
     */

    public function productsCreatePayload(Request $request)
    {
        if($shop = WooShop::is_valid($this->source_shop)):
            $this->productStoreOrUpdate($request, $shop);
            return json_encode([
                'status'    => 200,
                'message'   => json_encode('Product has been created successfully!')
            ]);
        endif;

        return json_encode([
            'status'    => 422,
            'message'   => "Shop is not valid!"
        ]);
    }

    private function productStoreOrUpdate(Request $request, WooShop $shop)
    {
        $product_id = $request->id;

        $data = [
            'images'        => json_encode($request->images, JSON_UNESCAPED_UNICODE),
            'product_id'    => $product_id,
            'parent_id'     => $request->parent_id,
            'type'          => $request->type,
            'product_name'  => $request->name,
            'product_code'  => $request->sku,
            'variations'    => json_encode($request->variations, JSON_UNESCAPED_UNICODE),
            'meta_data'     => json_encode($request->meta_data, JSON_UNESCAPED_UNICODE),
            'seller_id'     => $shop->seller_id,
            'from_where'    => 1,
            'incoming'      => 1,
            'price'         => $request->price,
            'regular_price' => $request->regular_price,
            'sale_price'    => $request->sale_price,
            'price_html'    => $request->price_html == null ? '' : $request->price_html,
            'weight'        => $request->weight,
            'specifications'=> $request->description != null ? $request->description : '',
            'website_id'    => $shop->id,
            'status'        => $request->status,
            'inventory_id'  => 1
        ];
        $product = WooProduct::where('product_id', $product_id)
                        ->where('website_id', $shop->id)
                        ->first();
        if ($product):
            unset($data['quantity']);
            $product->update($data);
        else:
            $product = WooProduct::create($data);
            if ($product->type == 'variable' && $product->parent_id == 0):
                $this->createVariableProducts($product, $shop);
            endif;
        endif;
    }

    private function createVariableProducts(WooProduct $product, WooShop $shop)
    {
        $variable_products = json_decode($product->variations, 1);

        $WC_Client = new Client(
            $shop->site_url,
            $shop->rest_api_key,
            $shop->rest_api_secrete,
            [
                'version' => 'wc/v3',
            ]
        );

        $data = [];

        foreach ($variable_products as $variable_product) {
            $variable_product_info = $WC_Client->get('products/' . $product->product_id . '/variations/'. $variable_product);

            $data[] = [
                'images'        => json_encode($variable_product_info->image, JSON_UNESCAPED_UNICODE),
                'product_id'    => $variable_product,
                'parent_id'     => $product->product_id,
                'type'          => 'variation',
                'product_name'  => $product->product_name,
                'product_code'  => $variable_product_info->sku,
                'variations'    => json_encode([]),
                'meta_data'     => json_encode($variable_product_info->meta_data, JSON_UNESCAPED_UNICODE),
                'seller_id'     => $shop->seller_id,
                'from_where'    => 1,
                'incoming'      => 1,
                'price'         => $variable_product_info->price,
                'regular_price' => $variable_product_info->regular_price,
                'sale_price'    => $variable_product_info->sale_price,
                'price_html'    => '',
                'weight'        => $variable_product_info->weight,
                'specifications'=> $variable_product_info->description != null ? $variable_product_info->description : '',
                'website_id'    => $shop->id,
                'status'        => $variable_product_info->status,
                'inventory_id'  => 1
            ];
        }

        if ($data) {
            WooProduct::insert($data);
        }
    }

    /**
     * Product Update
     * @param Request $request
     * If product is updated in WooCommerce this statement executes
     * and update on the Database as well
     * @return string
     */
    public function productsUpdatePayload(Request $request)
    {
        if($shop = WooShop::is_valid($this->source_shop)):
            $this->productStoreOrUpdate($request, $shop);
            return json_encode([
                'status'    => 200,
                'message'   => json_encode('Product has been updated successfully!')
            ]);
        endif;

        return json_encode([
            'status'    => 422,
            'message'   => "Shop is not valid!"
        ]);
    }

    /**
     * Product Delete
     * @param Request $request
     * If product is deleted in WooCommerce this statement executes
     * and deletes from the Database as well
     * @return string
     */

    public function productsDeletePayload(Request $request)
    {
        if($shop = WooShop::is_valid($this->source_shop)):
            WooProduct::where("product_id", $request->id)
                ->where('website_id', $shop->id)
                ->delete();

            return json_encode([
                'status'    => 200,
                'message'   => json_encode('Product has been deleted successfully!')
            ]);
        endif;

        return json_encode([
            'status'    => 422,
            'message'   => "Shop is not valid!"
        ]);
    }

    public function dodoChatAuthentication($username, $password)
    {
        if(Auth::attempt([
            'phone' => $username,
            'password' => $password,
            'is_active' => 1
        ])) {

            $authCode = Str::random(20);
            $dodohat = new DoDoChat();
            $dodohat->username = $username;
            $dodohat->login_time = now();
            $dodohat->auth_code = $authCode;
            $dodohat->name = Auth::user()->name;
            $dodohat->save();

            if(Auth::user()->role == 'member') {
                $shops = Shop::where('seller_id', Auth::id())->get();
                $all_shops = [];
                foreach ($shops as $shop) {
                    $shop_type = json_decode($shop->shop_type);
                    if(count((array) $shop_type)){
                        if(isset($shop_type->Shopee)):
                            $all_shops[] = [
                                'shop_name' => $shop->name,
                                'username' => $shop_type->Shopee->username,
                                'password' => $shop_type->Shopee->password,
                                'site' => 'Shopee',
                                'proxy' => ""
                            ];
                        endif;

                        if(isset($shop_type->Lazada)):
                            $all_shops[] = [
                                'shop_name' => $shop->name,
                                'username' => $shop_type->Lazada->username,
                                'password' => $shop_type->Lazada->password,
                                'site' => 'Lazada',
                                'proxy' => ""
                            ];
                        endif;

                    }
                }

                if(count($all_shops)){
                    return response()->json([
                        'status'=>'success',
                        'data' => Auth::user(),
                        'shops' => json_encode($all_shops),
                        'auth_code' => $authCode
                    ], 200);
                } else {
                    return response()->json(['status'=>'No shops are configured.'], 200);
                }
            }

            if(Auth::user()->role == 'staff') {
                $shops = json_decode(Auth::user()->assigned_shops);
                if(count((array)$shops)) {
                    $all_shops = [];
                    foreach ($shops as $item) {
                        $shop = Shop::find($item);
                        $shop_type = json_decode($shop->shop_type);
                        if(count((array) $shop_type)){
                            if(isset($shop_type->Shopee)):
                                $all_shops[] = [
                                    'shop_name' => $shop->name,
                                    'username' => $shop_type->Shopee->username,
                                    'password' => $shop_type->Shopee->password,
                                    'site' => 'Shopee',
                                    'proxy' => ""
                                ];
                            endif;

                            if(isset($shop_type->Lazada)):
                                $all_shops[] = [
                                    'shop_name' => $shop->name,
                                    'username' => $shop_type->Lazada->username,
                                    'password' => $shop_type->Lazada->password,
                                    'site' => 'Lazada',
                                    'proxy' => ""
                                ];
                            endif;

                        }
                    }

                    return response()->json([
                        'status'=>'success',
                        'data' => Auth::user(),
                        'shops' => json_encode($all_shops),
                        'auth_code' => $authCode
                    ], 200);

                } else {
                    return response()->json(['status'=>'No shops are assigned to you.'], 200);
                }
            }
        } else {
            return response()->json(['status'=>'Invalid credentials, please try again...'], 401);
        }
    }
}
