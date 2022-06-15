<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shopee\Product\DeleteCoverImageForShopeeProductRequest;
use App\Http\Requests\Shopee\Product\StoreCoverImageForShopeeProductRequest;
use App\Http\Requests\Shopee\Product\StoreShopeeProductRequest;
use App\Http\Requests\Shopee\Product\StoreVariationImageForShopeeProductRequest;
use App\Http\Requests\Shopee\Product\UpdateShopeeProductRequest;
use App\Jobs\DeleteShopeeProductImageFromShopee;
use App\Jobs\ShopeeProductDetailSync;
use App\Jobs\ShopeeProductSync;
use Illuminate\Http\Request;
use App\Jobs\ShopeeProductVariationInfoUpdate;
use App\Models\ShopeeProduct;
use App\Models\Shopee;
use App\Models\ShopeeProductCategory;
use App\Traits\ShopeeOrderPurchaseTrait;
use App\Traits\ShopeeProductTrait;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use function PHPSTORM_META\map;
use function PHPUnit\Framework\isJson;

class ShopeeProductsController extends Controller
{
    use ShopeeOrderPurchaseTrait, ShopeeProductTrait;

    private $tier_2_variation_data;

    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Create shopee product.
     */
    public function create()
    {
        /**
         * Get shopee product categories.
         * NOTE:
         * Where "website_id" refers to "shop_id" not "id" in "shopees" table.
         */
        /* Main parent category */
        $data["shopee_category_parent_id"] = 0;
        $data["shopee_product_parent_categories"] = [];
        /* Sub category */
        $data["shopee_category_parent_id_1"] = 0;
        $data["shopee_product_parent_categories_1"] = [];
        /* Sub sub category */
        $data["shopee_category_id"] = 0;
        $data["shopee_product_categories"] = [];

        $data["shopee_shops"] = Shopee::select("id", "shop_id", "shop_name")->get();

        return view('shopee.product.crud.create', $data);
    }


    /**
     * Store shopee product.
     * NOTE:
     * Here "website_id" refers to "shop_id".
     */
    public function store(StoreShopeeProductRequest $request)
    {
        try {
            if ($request->ajax()) {
                $shopee_shop = Shopee::whereShopId($request->website_id)->first();
                if (!isset($shopee_shop)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __('translation.Shop Not Found')
                    ]);
                }

                $shopee_product_images = [];
                $uploaded_images_path = [];
                if (isset($request->cover_images_count) and $request->cover_images_count > 0) {
                    for ($i=0; $i<$request->cover_images_count; $i++) {
                        if ($request->hasFile('cover_image_'.$i)) {
                            $image = $request->file('cover_image_'.$i);
                            $path = Storage::disk('s3')->put('uploads/shopee/products', $image, 'public');
                            $path = Storage::disk('s3')->url($path);
                            array_push($shopee_product_images, $path);
                            array_push($uploaded_images_path, $path);
                        }
                    }
                }

                $data = $this->storeProductInShopee($request, $shopee_product_images);
                if ($data["request_id"]) {
                    if (isset($data["item_id"], $data["item"])) {
                        if ($request->type == "variable") {
                            /* Store the variation images. */
                            $response = $this->storeShopeeVariationSpecificProductImage($request, $data["item_id"]);
                            if (isset($response["item_id"])) {
                                /* Sync the new product. */
                                ShopeeProductDetailSync::dispatch([
                                    "shopid"    => (int) $shopee_shop->shop_id,
                                    "item_id"   => (int) $data["item_id"]
                                ], null, $this->getShopeeSellerId())->delay(Carbon::now()->addSeconds(10));
                            } else if (isset($response["uploaded_images_path"])) {
                                foreach ($response["uploaded_images_path"] as $file_path) {
                                    /* Remove the uploaded variation images. */
                                    if (File::exists($file_path)) {
                                        File::delete($file_path);
                                    }
                                }

                                /* Sync the new product. */
                                ShopeeProductDetailSync::dispatch([
                                    "shopid"    => (int) $shopee_shop->shop_id,
                                    "item_id"   => (int) $data["item_id"]
                                ], null, $this->getShopeeSellerId());

                                return response()->json([
                                    "success"   => true,
                                    "message"   => __('translation.Successfully created new product in Shopee. Failed to store the variation images.')
                                ]);
                            }
                        } else {
                            /* Sync the new product. */
                            ShopeeProductDetailSync::dispatch([
                                "shopid"    => (int) $shopee_shop->shop_id,
                                "item_id"   => (int) $data["item_id"]
                            ], null, $this->getShopeeSellerId());
                        }

                        return response()->json([
                            "success"   => true,
                            "message"   => __('translation.Successfully created new product in Shopee')
                        ]);
                    } else if (isset($data["msg"])) {
                        return response()->json([
                            "success"   => false,
                            "message"   => $data["msg"]
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __('translation.Failed to create new product in Shopee')
        ]);
    }


    /**
     * Store shopee variation sepecific product image.
     */
    public function storeShopeeVariationSpecificProductImage($request, $item_id, $name="Size")
    {
        try {
            if (!isset($request->variation_name, $request->variation_images_count) || $request->variation_images_count == 0 ||
                !in_array(strtolower($name), ["size", "color"])) {
                return;
            }
            $shopee_variation_images = [];
            $uploaded_images_path = [];
            for ($i=0; $i<$request->variation_images_count; $i++) {
                if ($request->hasFile('variation_image_'.$i)) {
                    $image = $request->file('variation_image_'.$i);
                    $path = Storage::disk('s3')->put('uploads/shopee/products', $image, 'public');
                    $path = Storage::disk('s3')->url($path);
                    array_push($shopee_variation_images, $path);
                    array_push($uploaded_images_path, $path);
                } else {
                    array_push($shopee_variation_images, "");
                }
            }

            $client = $this->getShopeeClient((int) $request->website_id);
            if (isset($client)) {
                $this->tier_2_variation_data["item_id"] = $item_id;
                $this->tier_2_variation_data["tier_variation"][0]["images_url"] = $shopee_variation_images;
                $response = $client->item->initTierVariation($this->tier_2_variation_data)->getData();
                $response["uploaded_images_path"] = $uploaded_images_path;
                return $response;
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [];
    }


    /**
     * Create product in Shopee.
     */
    private function storeProductInShopee($request, $shopee_product_images=[])
    {
        try {
            $client = $this->getShopeeClient((int) $request->website_id);
            if (isset($client)) {
                $data = [
                    'name'          => $request->name,
                    'description'   => $request->description,
                    'item_sku'      => $request->sku ?? "",
                    'category_id'   => (int) $request->shopee_category_id,
                    'weight'        => (float) $request->weight,
                    'package_length'=> (int) $request->package_length,
                    'package_width' => (int) $request->package_width,
                    'package_height'=> (int) $request->package_height,
                    'condition'     => 'NEW',
                    'status'        => 'NORMAL',
                    'timestamp'     => time(),
                    // 'days_to_ship'  => (int) $request->days_to_ship ?? 0,
                    // 'is_pre_order'  => true,
                ];

                /* Product variables. */
                if ($request->type == "variable") {
                    $data["price"] = 0;
                    $data["stock"] = 0;
                    if (isset($request->variation_name, $request->variation_sku, $request->variation_price, $request->variation_stock)) {
                        $variation_name = json_decode($request->variation_name);
                        $variation_sku = json_decode($request->variation_sku);
                        $variation_price = json_decode($request->variation_price);
                        $variation_stock = json_decode($request->variation_stock);
                        $total_variation = sizeof($variation_name);
                        $data["variations"] = [];

                        $tier_2_variation = [];
                        for ($i=0; $i<$total_variation; $i++) {
                            $variation_data = [
                                "name"  => $variation_name[$i],
                                "price" => (float) $variation_price[$i] ?? 0,
                                "stock" => (int) $variation_stock[$i] ?? 0
                            ];
                            if (isset($variation_sku[$i])) {
                                $variation_data["variation_sku"] = $variation_sku[$i];
                            }
                            array_push($data["variations"], $variation_data);

                            array_push($tier_2_variation, [
                                "tier_index"    => [$i],
                                "price"         => (float) $variation_price[$i] ?? 0,
                                "stock"         => (int) $variation_stock[$i] ?? 0,
                                "variation_sku" => $variation_sku[$i] ?? ""
                            ]);
                        }

                        $this->tier_2_variation_data = [
                            "tier_variation"    => [[
                                "name"      => (isset($request->variation_option_name) and !empty($request->variation_option_name))?$request->variation_option_name:"Size",
                                "options"   => $variation_name,
                                "images_url"=> []
                            ]],
                            "variation"     => $tier_2_variation
                        ];
                    }
                } else {
                    $data["price"] = (float) $request->price ?? 0;
                    $data["stock"] = (int) $request->stock ?? 0;
                    $data["variations"] = [];
                }

                /* Product images. */
                if (sizeof($shopee_product_images) > 0) {
                    $data["images"] = [];
                    foreach($shopee_product_images as $image) {
                        array_push($data["images"], [
                            // "url" => "https://cf.shopee.co.th/file/d5aaa0b0cb81bf95ade1c1936fedd0cf",
                            "url" => $image,
                        ]);
                    }
                }

                // $data["wholesales"] = [[
                //     "min"        => 2,
                //     "max"        => 5,
                //     "unit_price" => 18
                // ]];

                $data["logistics"] = [];
                if (isset($request->logistic_data)) {
                    $logistic_data = json_decode($request->logistic_data);
                    foreach ($logistic_data as $logistic) {
                        $logistic = (array) $logistic;
                        array_push($data["logistics"], [
                            "logistic_id"   => (int) $logistic["logistic_id"] ?? 0,
                            "enabled"       => (isset($logistic["logistic_enabled"]) and $logistic["logistic_enabled"]=="yes")?true:false
                        ]);
                    }
                }

                /* Product category specific attributes. */
                $data["attributes"] =  [[
                    "attributes_id" => (int) $request->brand_attribute_id,
                    "value"         => $request->brand ?? "NoBrand"
                ]];

                return $client->item->add($data)->getData();
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return [];
    }


    /**
     * Get pickup address info from "Shopee".
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductAttributesFromShopee(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->shopee_shop_id, $request->shopee_category_id) and
                !empty($request->shopee_shop_id) and !empty($request->shopee_category_id)) {
                $shopee_shop = Shopee::whereShopId($request->shopee_shop_id)->first();
                if (!isset($shopee_shop, $shopee_shop->shop_id)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Shop Not Found")
                    ]);
                }

                $client = $this->getShopeeClient($shopee_shop->shop_id);
                if (isset($client)) {
                    $response = $client->item->getAttributes([
                        'category_id'   => (int) $request->shopee_category_id,
                        'language'      => 'en',
                        'timestamp'     => time()
                    ])->getData();

                    if (isset($response["attributes"])) {
                        $data = [];
                        foreach ($response["attributes"] as $attribute) {
                            if ($attribute["is_mandatory"]) {
                                array_push($data, $attribute);
                            }
                        }
                        return response()->json([
                            "success"   => true,
                            "data"      => $data
                        ]);
                    } else if (isset($response["msg"])) {
                        return response()->json([
                            "success"   => true,
                            "message"   => $response["msg"],
                            "data"      => []
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => "Failed to fetch product attributes"
        ]);
    }


    /**
     * Edit shopee product.
     * Note:
     * For variation $id refers to "production_id" instead of "id.
     *
     * @param integer $id
     */
    public function edit($id)
    {
        $data['product'] = ShopeeProduct::where('id', $id)->first();
        abort_if(!$data['product'], Response::HTTP_NOT_FOUND, ucfirst(__('translation.data_not_found')));

        if (!in_array($data['product']['type'], ["variable", "simple"])) {
            $parent_id = $data['product']['parent_id'];
            $data['product'] = ShopeeProduct::whereProductId($parent_id)->first();
            abort_if(!$data['product'], Response::HTTP_NOT_FOUND, ucfirst(__('translation.data_not_found')));
            $data['id'] = $data['product']['id'];
        }
        $data['id'] = $data['product']['id'];

        $data["shop_name"] = $data["product"]->shopee->shop_name;

        /* Link to shopee dashboard for the product. */
        $data['shopee_product_page_link_live'] = $this->getShopeePortalLinkForProduct()."/".(in_array($data["product"]["type"], ["simple", "variable"])?$data["product"]["product_id"]:$data["product"]["parent_id"]);

        if (isset($data["product"]["images"]) and !empty($data["product"]["images"])) {
            $data['product_images'] = json_decode($data["product"]["images"]);
        }

        /* Get shopee variation product specific images. */
        $data["variations"] = [];
        $data["variation_specific_image"] = [];
        if (isset($data["product"]["variations"]) and !empty($data["product"]["variations"])) {
            $data["variations"] = json_decode($data["product"]["variations"]);
            foreach ($data["variations"] as $variation) {
                $shopee_variation_product = ShopeeProduct::select("images", "product_id")
                    ->whereProductId($variation->variation_id)
                    ->first();
                if (isset($shopee_variation_product, $shopee_variation_product->images) and !empty($shopee_variation_product->images)) {
                    $images = json_decode($shopee_variation_product->images);
                    if (isset($images[0])) {
                        $data["variation_specific_image"][$variation->variation_id] = $images[0];
                    }
                }
                $variation_name_data = explode(",", $variation->name);
                if (isset($variation_name_data[0])) {
                    $variation->name = $variation_name_data[0];
                }
            }
        }

        /* Variation option name */
        if (isset($data["product"]["tier_2_variations"]) and !empty($data["product"]["tier_2_variations"])) {
            $tier_2_variations = json_decode($data["product"]["tier_2_variations"]);
            $data["variation_option_name"] = $tier_2_variations[0]->name;
            if (isset($tier_2_variations[1], $tier_2_variations[1]->name)) {
                $data["variation_option_name_2"] = $tier_2_variations[1]->name;
            }
        } else {
            $data["variation_option_name"] = "";
            $data["variation_option_name_2"] = "";
        }

        /**
         * Get shopee product categories.
         * NOTE:
         * Where "website_id" refers to "shop_id" not "id" in "shopees" table.
         */
        /* Main parent category */
        $data["shopee_category_parent_id"] = 0;
        $data["shopee_product_parent_categories"] = [];
        /* Sub category */
        $data["shopee_category_parent_id_1"] = 0;
        $data["shopee_product_parent_categories_1"] = [];
        /* Sub sub category */
        $data["shopee_category_id"] = 0;
        $data["shopee_product_categories"] = [];
        if (isset($data["product"]["website_id"])) {
            $shopee_shop_id = (int)$data["product"]["website_id"];
            $shopee_shop = Shopee::whereShopId($shopee_shop_id)->first();
            $data["shopee_product_parent_categories"] = ShopeeProductCategory::whereShopeeId($shopee_shop->id)
                ->whereParentId(0)
                ->get();
            if (isset($data["product"]["shopee_category_id"]) and (int)$data["product"]["shopee_category_id"] > 0) {
                $category_id = (int)$data["product"]["shopee_category_id"];
                $shopee_category = ShopeeProductCategory::whereShopeeId($shopee_shop->id)
                    ->whereCategoryId($category_id)
                    ->first();
                if (isset($shopee_category, $shopee_category->parent_id)) {
                    /**
                     * Get available categories falling under same parent for the selected "shopee_category_id"
                     */
                    $data["shopee_category_id"] = $category_id;
                    $data["shopee_product_categories"] = ShopeeProductCategory::whereShopeeId($shopee_shop->id)
                        ->whereParentId($shopee_category->parent_id)
                        ->orderBy('category_name', 'asc')
                        ->get();
                    /**
                     * Get parent shopee product categories.
                     * NOTE:
                     * Only getting the first parent.
                     * Ex: Category >> Sub Category >> Sub Sub Category.
                     * "shopee_category_id" refers to "Sub Sub Category". Get the "parent_id" of "Sub Sub Category" then
                     * get the "Sub Category". From the "Sub Category" get the "parent_id" and get all the "Sub Category"
                     * as "Shopee Parent Category".
                     */
                    $shopee_parent_category = ShopeeProductCategory::whereShopeeId($shopee_shop->id)
                        ->whereCategoryId($shopee_category->parent_id)
                        ->first();
                    if (isset($shopee_parent_category, $shopee_parent_category->parent_id)) {
                        $shopee_parent_categories = ShopeeProductCategory::whereShopeeId($shopee_shop->id)
                            ->whereParentId($shopee_parent_category->parent_id)
                            ->orderBy('category_name', 'asc')
                            ->get();
                        $data["shopee_category_parent_id"] = $shopee_parent_category->parent_id;
                        if (sizeof($shopee_parent_categories) > 0) {
                            $data["shopee_category_parent_id_1"] = $shopee_category->parent_id;
                            $data["shopee_product_parent_categories_1"] = $shopee_parent_categories;
                        }
                    } else {
                        $shopee_parent_categories = ShopeeProductCategory::whereShopeeId($shopee_shop->id)
                            ->whereCategoryId($shopee_category->parent_id)
                            ->orderBy('category_name', 'asc')
                            ->get();
                        if (sizeof($shopee_parent_categories) > 0) {
                            $data["shopee_category_parent_id_1"] = $shopee_category->category_id;
                            $data["shopee_product_parent_categories_1"] = $shopee_parent_categories;
                        }
                    }
                }
            }
        }

        $client = $this->getShopeeClient($data['product']['website_id']);

        $item_response = $client->item->getItemDetail([
            'item_id' => $data['product']['product_id']
        ])->getData();

        if ($data['product']['type'] == "variable") {
            $tier_variation_response = $client->item->getTierVariation([
                'item_id' => $data['product']['product_id']
            ])->getData();

            $data["tier_variation_options"] = json_encode($tier_variation_response["tier_variation"]);

            $data["option_1_options"] = $tier_variation_response["tier_variation"][0]["options"];
            $data["option_1_images_url"] = $tier_variation_response["tier_variation"][0]["images_url"] ?? [];

            $tier_variation_with_indexes = $tier_variation_response["variations"];
            $tier_variation_with_index__arr = [];
            foreach ($tier_variation_with_indexes as $tier_variation_with_index) {
                $index = implode("_", $tier_variation_with_index["tier_index"]);
                foreach ($item_response["item"]["variations"] as $variation) {
                    if ($tier_variation_with_index["variation_id"] == $variation["variation_id"]) {
                        $tier_variation_with_index__arr[$index] = [
                            "tier_index"        => $index,
                            "name"              => $variation["name"],
                            "variation_id"      => $variation["variation_id"],
                            "variation_price"   => $variation["price"],
                            "variation_stock"   => $variation["stock"],
                            "variation_sku"     => $variation["variation_sku"]
                        ];
                    }
                }
            }
            $data["tier_variation_with_index"] = json_encode($tier_variation_with_index__arr);
        } else {
            $data["tier_variation_options"] = json_encode([]);
            $data["tier_variation_with_index"] = json_encode([]);
        }

        return view('shopee.product.crud.edit_1', $data);
    }


    /**
     * Update shopee product.
     */
    public function update(UpdateShopeeProductRequest $request)
    {
        try {
            if ($request->ajax()) {
                $product = ShopeeProduct::whereProductId((int)$request->id)->first();
                if (!isset($product)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __('translation.No such product found for Shopee.')
                    ]);
                }
                $client = $this->getShopeeClient((int) $request->website_id);
                if (!isset($client)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __('translation.Failed to update product in Shopee.')
                    ]);
                }

                $is_updated = $this->updateBasicProductInfoInShopee($product, $request);
                if($is_updated) {
                    if ($product->type == "variable" and isset($request->variation_id)) {
                        $tier_variation_data = [];
                        $option_name_1 = $request->option_name_1;
                        $tier_variation_choices_1__option_val = json_decode($request->tier_variation_choices_1__option_val);
                        $tier_variation_data[0] = [
                            "name"          => $option_name_1,
                            "options"       => $tier_variation_choices_1__option_val
                        ];

                        /* Get tier variation info from Shopee. */
                        $all_2_tier_variations = $client->item->getTierVariation([
                            'item_id' => $product["product_id"]
                        ])->getData();

                        /* Handle "images_url" for tier variation option 1. */
                        if (isset($all_2_tier_variations['tier_variation'], $all_2_tier_variations['tier_variation'][0])) {
                            $tier_variation = $all_2_tier_variations['tier_variation'][0];

                            $tier_images_url = [];
                            if (!isset($tier_variation["images_url"])) {
                                for ($i = 0; $i < sizeof($tier_variation["options"]); $i++) {
                                    $tier_images_url[$i] = "";
                                }
                            } else {
                                $tier_images_url = $tier_variation["images_url"];
                            }

                            $has_enough_images = $this->validateTier2VariationImages($request, $tier_variation, $tier_images_url);

                            if (!$has_enough_images) {
                                return response()->json([
                                    "success"   => false,
                                    "data"      => [
                                        "show"  => false
                                    ],
                                    "message"   => $response_utvl["msg"]??__("translation.Not enough images")
                                ]);
                            }

                            /* Update for the exisiting "images_url" for tier variation option 1. */
                            for ($i=0; $i<sizeof($tier_images_url); $i++) {
                                if ($request->hasFile('new_variation_option_image_'.$i)) {
                                    $image = $request->file('new_variation_option_image_'.$i);
                                    $path = Storage::disk('s3')->put('uploads/shopee/products', $image, 'public');
                                    $path = Storage::disk('s3')->url($path);
                                    $tier_images_url[$i] = $path;
                                }
                            }

                            /**
                             * This is done check if new varition image has been added.
                             * This is done when new variation has been added for tier variation option 1.
                             */
                            if (sizeof($tier_variation_choices_1__option_val) > sizeof($tier_images_url)) {
                                for ($k=sizeof($tier_images_url); $k<sizeof($tier_variation_choices_1__option_val); $k++) {
                                    $image = $request->file('new_variation_option_image_'.$i);
                                    $path = Storage::disk('s3')->put('uploads/shopee/products', $image, 'public');
                                    $path = Storage::disk('s3')->url($path);
                                    $tier_images_url[$k] = $path;
                                }
                            }
                            $tier_variation_data[0]["images_url"] = $tier_images_url;
                        }

                        $option_name_2 = "";
                        $tier_variation_choices_2__option_val = [];
                        if (isset($request->option_name_2, $request->tier_variation_choices_2__option_val) and !empty($request->option_name_2)) {
                            $option_name_2 = $request->option_name_2;
                            $tier_variation_choices_2__option_val = json_decode($request->tier_variation_choices_2__option_val);
                            $tier_variation_data[1] = [
                                "name"          => $option_name_2,
                                "options"       => $tier_variation_choices_2__option_val
                            ];
                        }

                        $response_utvl = $client->item->updateTierVariationList([
                            'item_id'           => $product["product_id"],
                            'tier_variation'    => $tier_variation_data
                        ])->getData();

                        if (isset($response_utvl["item_id"])) {
                            $variation_id = json_decode($request->variation_id);
                            $variation_sku = json_decode($request->variation_sku);
                            $variation_price = json_decode($request->variation_price);
                            $variation_stock = json_decode($request->variation_stock);
                            $variation_sku_old = json_decode($request->variation_sku_old);
                            $variation_price_old = json_decode($request->variation_price_old);
                            $variation_stock_old = json_decode($request->variation_stock_old);
                            $variation_data_for_jobs = [];

                            $counter = 0;
                            $new_variation_data = [];
                            for ($i=0; $i<sizeof($tier_variation_choices_1__option_val); $i++) {
                                if (sizeof($tier_variation_choices_2__option_val) > 0) {
                                    for ($j=0; $j<sizeof($tier_variation_choices_2__option_val); $j++) {
                                        if ($variation_id[$counter] != 0) {
                                            /* This is for old variation */
                                            $data = [
                                                "variation_id"  => (int)$variation_id[$counter]
                                            ];
                                            $add_for_job = false;
                                            if ($variation_price[$counter] !== $variation_price_old[$counter]) {
                                                $data["price"] = (float) $variation_price[$counter];
                                                $add_for_job = true;
                                            }
                                            if ($variation_stock[$counter] !== $variation_stock_old[$counter]) {
                                                $data["stock"] = (int) $variation_stock[$counter];
                                                $add_for_job = true;
                                            }
                                            if(!empty($variation_sku[$counter]) and $variation_sku[$counter] !== $variation_sku_old[$counter]) {
                                                $data["variation_sku"] = $variation_sku[$counter];
                                                $add_for_job = true;
                                            }
                                            if ($add_for_job) {
                                                array_push($variation_data_for_jobs, $data);
                                            }
                                        } else {
                                            /* This is for new variation */
                                            $data = [
                                                "tier_index"    => [$i, $j],
                                                "price"         => (float)$variation_price[$counter],
                                                "stock"         => (int)$variation_stock[$counter],
                                            ];
                                            if(!empty($variation_sku[$counter])) {
                                                $data["variation_sku"] = $variation_sku[$counter];
                                            }
                                            array_push($new_variation_data, $data);
                                        }
                                        $counter += 1;
                                    }
                                } else {
                                    if ($variation_id[$counter] != 0) {
                                        /* This is for old variation */
                                        $data = [
                                            "variation_id"  => (int)$variation_id[$counter]
                                        ];
                                        $add_for_job = false;
                                        if ($variation_price[$counter] !== $variation_price_old[$counter]) {
                                            $data["price"] = (float) $variation_price[$counter];
                                            $add_for_job = true;
                                        }
                                        if ($variation_stock[$counter] !== $variation_stock_old[$counter]) {
                                            $data["stock"] = (int) $variation_stock[$counter];
                                            $add_for_job = true;
                                        }
                                        if(!empty($variation_sku[$counter]) and $variation_sku[$counter] !== $variation_sku_old[$counter]) {
                                            $data["variation_sku"] = $variation_sku[$counter];
                                            $add_for_job = true;
                                        }
                                        if ($add_for_job) {
                                            array_push($variation_data_for_jobs, $data);
                                        }
                                    } else {
                                        /* This is for new variation */
                                        $data = [
                                            "tier_index"    => [$i],
                                            "price"         => (float)$variation_price[$counter],
                                            "stock"         => (int)$variation_stock[$counter],
                                        ];
                                        if(!empty($variation_sku[$counter])) {
                                            $data["variation_sku"] = $variation_sku[$counter];
                                        }
                                        array_push($new_variation_data, $data);
                                    }
                                    $counter += 1;
                                }
                            }

                            if (sizeof($new_variation_data) > 0) {
                                foreach ($new_variation_data as $data) {
                                    $client->item->addTierVariationList([
                                        'item_id'       => $product->product_id,
                                        'variation'     => $new_variation_data
                                    ])->getData();
                                    sleep(1);
                                }
                            }

                            if (sizeof($variation_data_for_jobs) > 0) {
                                ShopeeProductVariationInfoUpdate::dispatch((int)$product->website_id, $product->product_id, $variation_data_for_jobs);
                                sleep(1*sizeof($variation_data_for_jobs));
                            }
                        }
                    }
                }

                /* Sync the product again. */
                ShopeeProductDetailSync::dispatch([
                    "shopid"    => (int) $product->website_id,
                    "item_id"   => (int) $product["product_id"]
                ], null, $this->getShopeeSellerId());

                return response()->json([
                    "success"   => true,
                    "message"   => "Success"
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => "Failed"
        ]);
    }


    /**
     * Update product basic information in Shopee.
     */
    private function updateBasicProductInfoInShopee($product, $request)
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
                        ]);
                    }
                    if($product->price != $request->price) {
                        $client->item->updatePrice([
                            'item_id'   => (int) $request->id,
                            'price'     => (float) $request->price,
                        ]);
                    }
                }

                return true;
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return false;
    }


    /**
     * Update shopee product image.
     */
    public function updateShopeeProductImage(StoreCoverImageForShopeeProductRequest $request)
    {
        try {
            if ($request->ajax()) {
                $product = ShopeeProduct::whereProductId($request->id)->first();
                if (!isset($product)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.No such variation product found.")
                    ]);
                }
                if ($request->hasFile('image')) {
                    $data = [];
                    $destination_path = "";
                    $image = $request->file('image');
                    $path = Storage::disk('s3')->put('uploads/shopee/products', $image, 'public');
                    $path = Storage::disk('s3')->url($path);

                    $product_images = $product->images;
                    if (isset($product_images) and !empty($product_images)) {
                        $product_images = json_decode($product_images);

                        if (sizeof($product_images) > 0) {
                            array_push($product_images, $path);
                        } else {
                            $product_images = [$path];
                        }

                        $data = $product_images;
                        /* At most 9 images can be uploaded. */
                        if (sizeof($data) > 9) {
                            $start_index = sizeof($data)-9;
                            $data = array_slice($data, $start_index);
                        }

                        $client = $this->getShopeeClient((int) $request->website_id);
                        if (isset($client)) {
                            $response = $client->item->updateItemImage([
                                'item_id'   => (int) $request->id,
                                'images'    => $data,
                                'timestamp' => time()
                            ])->getData();
                            if (isset($response["request_id"])) {
                                if (isset($response["error"])) {
                                    if (!empty($destination_path) and File::exists($destination_path)) {
                                        File::delete($destination_path);
                                    }
                                    return response()->json([
                                        "success"   => false,
                                        "message"   => $response["msg"]??__("translation.Failed to update images in Shopee")
                                    ]);
                                } else {
                                    /* Update the "total_cover_images" count of the product in database. */
                                    $product->total_cover_images += 1;
                                    $product->images = json_encode($data);
                                    $product->save();
                                }
                            }
                        }
                    }

                    return response()->json([
                        "success"   => true,
                        "data"      => $data,
                        "message"   => __("translation.Successfully uploaded shopee product images")
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }

        return response()->json([
            "success"   => false,
            "message"   => __("translation.Failed to upload shopee product images")
        ]);
    }


    /**
     * Delete specific shopee product cover image.
     */
    public function deleteShopeeProductImage(DeleteCoverImageForShopeeProductRequest $request)
    {
        try {
            if ($request->ajax()) {
                $product = ShopeeProduct::whereProductId($request->id)->first();
                if (!isset($product)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.No such variation product found.")
                    ]);
                }
                $file_name = "";
                /* To check if the selected image file is missing in the system. */
                $image_file_is_missing = false;
                $destination_path = "";
                if (isset($request->image)) {
                    /* Get the file path if the image file was uploaded from the system. */
                    if (is_numeric(strpos($request->image, "/uploads/product/"))) {
                        $file_data = explode("/uploads/product/", $request->image);
                        if (isset($file_data[1])) {
                            $file_name = $file_data[1];
                            $destination_path = public_path('uploads/product/'.$file_name);
                            if (!File::exists($destination_path)) {
                                $image_file_is_missing = true;
                            }
                        }
                    } else {
                        $file_name = $request->image;
                    }

                    if (!empty($file_name)) {
                        $data = [];
                        $product_images = $product->images;
                        if (isset($product_images) and !empty($product_images)) {
                            $product_images = json_decode($product_images);
                            foreach ($product_images as $image) {
                                if (is_numeric(strpos($image, $file_name))) {
                                    continue;
                                } else {
                                    array_push($data, $image);
                                }
                            }

                            /* Here "website_id" refers to "shop_id". */
                            $client = $this->getShopeeClient((int) $request->website_id);
                            if (isset($client)) {
                                $response = $client->item->updateItemImage([
                                    'item_id'   => (int) $request->id,
                                    'images'    => $data,
                                    'timestamp' => time()
                                ])->getData();
                                if (isset($response["request_id"])) {
                                    if (isset($response["error"])) {
                                        if ($image_file_is_missing) {
                                            $product->images = json_encode($data);
                                            $product->save();
                                        } else {
                                            return response()->json([
                                                "success"   => false,
                                                "message"   => $response["msg"]??__("translation.Failed to remove image in Shopee")
                                            ]);
                                        }
                                    } else {
                                        /* Update the "total_cover_images" count of the product in database. */
                                        $new_total_cover_images = $product->total_cover_images - 1;
                                        $product->total_cover_images = ($new_total_cover_images > 0)?$new_total_cover_images:0;
                                        $product->images = json_encode($data);
                                        $product->save();
                                        if (!empty($destination_path) and File::exists($destination_path)) {
                                            File::delete($destination_path);
                                        }
                                    }
                                }
                            }
                        }

                        return response()->json([
                            "success"   => true,
                            "data"      => $data,
                            "message"   => __("translation.Successfully deleted shopee product image")
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }

        return response()->json([
            "success"   => false,
            "message"   => __("translation.Failed to delete shopee product images")
        ]);
    }


    /**
     * Update shopee variation sepecific product image.
     */
    public function updateShopeeVariationSpecificProductImage(Request $request)
    {
        try {
            if ($request->ajax()) {
                $product = ShopeeProduct::whereProductId($request->product_id)->whereParentId(0)->first();
                if (!isset($product)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.No such variation product found.")
                    ]);
                }

                $client = $this->getShopeeClient((int) $request->website_id);
                $all_2_tier_variations = $client->item->getTierVariation([
                    'item_id' => $product["product_id"]
                ])->getData();

                if (isset($all_2_tier_variations['tier_variation'], $all_2_tier_variations['tier_variation'][0])) {
                    $tier_variation = $all_2_tier_variations['tier_variation'][0];

                    $tier_images_url = [];
                    if (!isset($tier_variation["images_url"])) {
                        for ($i = 0; $i < sizeof($tier_variation["options"]); $i++) {
                            $tier_images_url[$i] = "";
                        }
                    } else {
                        $tier_images_url = $tier_variation["images_url"];
                    }

                    $has_enough_images = $this->validateTier2VariationImages($request, $tier_variation, $tier_images_url);

                    if (!$has_enough_images) {
                        return response()->json([
                            "success"   => false,
                            "data"      => [
                                "show"  => false
                            ],
                            "message"   => $response_utvl["msg"]??__("translation.Not enough images")
                        ]);
                    }

                    for ($i=0; $i<sizeof($tier_images_url); $i++) {
                        if ($request->hasFile('new_variation_option_image_'.$i)) {
                            $image = $request->file('new_variation_option_image_'.$i);
                            $path = Storage::disk('s3')->put('uploads/shopee/products', $image, 'public');
                            $path = Storage::disk('s3')->url($path);
                            $tier_images_url[$i] = $path;
                        }
                    }

                    $tier_variation["images_url"] = $tier_images_url;

                    $tier_variation_data = [];
                    array_push($tier_variation_data, $tier_variation);
                    if (isset($all_2_tier_variations['tier_variation'][1])) {
                        array_push($tier_variation_data, $all_2_tier_variations['tier_variation'][1]);
                    }

                    $response_utvl = $client->item->updateTierVariationList([
                        'item_id'           => $product["product_id"],
                        'tier_variation'    => $tier_variation_data
                    ])->getData();

                    if (isset($response_utvl["request_id"])) {
                        if (isset($response_utvl["error"])) {
                            return response()->json([
                                "success"   => false,
                                "message"   => $response_utvl["msg"]??__("translation.Failed to update image in Shopee")
                            ]);
                        }
                        else {
                            /* Store the new image in database. */
                            $product->images = json_encode($tier_images_url);
                            $product->save();

                            return response()->json([
                                "success"   => true,
                                "data"      => [
                                    "image"  => $path,
                                ],
                                "message"   => __("translation.Successfully uploaded shopee vairation specific product image")
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }

        return response()->json([
            "success"   => false,
            "message"   => __("translation.Failed to upload shopee product images")
        ]);
    }


    /**
     * Validate variation images for tier 2 variation.
     */
    private function validateTier2VariationImages ($request, $tier_variation, $tier_images_url) {
        $has_enough_images = true;
        try {
            for ($i=0; $i<sizeof($tier_images_url); $i++) {
                if ($request->hasFile('new_variation_option_image_'.$i)) {
                    continue;
                } else if (empty($tier_variation["images_url"][$i])) {
                    $has_enough_images = false;
                    break;
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return $has_enough_images;
    }


    /**
     * Get categories for specific shopee shop.
     */
    public function getShopeeProductCategory(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = [];
                if (isset($request->shopee_shop_id)) {
                    $shopee_shop = Shopee::whereShopId($request->shopee_shop_id)->first();
                    if (isset($shopee_shop)) {
                        $data = ShopeeProductCategory::select("category_id", "category_name")
                            ->whereShopeeId($shopee_shop->id)
                            ->whereParentId(0)
                            ->orderBy('category_name', 'asc')
                            ->get();
                    }
                }
                return response()->json([
                    "success"   => true,
                    "data"      => $data
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }

        return response()->json([
            "success"   => false,
            "data"      => []
        ]);
    }


    /**
     * Get sub category for shopee category.
     */
    public function getShopeeProductSubCategory(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = [];
                if (isset($request->category_parent_id, $request->shopee_shop_id)) {
                    $shopee_shop = Shopee::whereShopId($request->shopee_shop_id)->first();
                    if (isset($shopee_shop)) {
                        $shopee_parent_category = ShopeeProductCategory::whereShopeeId($shopee_shop->id)
                            ->whereCategoryId($request->category_parent_id)
                            ->first();
                        if (isset($shopee_parent_category, $shopee_parent_category->parent_id)) {
                            $data = ShopeeProductCategory::select("category_id", "category_name")
                                ->whereShopeeId($shopee_shop->id)
                                ->whereParentId($request->category_parent_id)
                                ->orderBy('category_name', 'asc')
                                ->get();
                        }
                    }
                }
                return response()->json([
                    "success"   => true,
                    "data"      => $data
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }

        return response()->json([
            "success"   => false,
            "data"      => []
        ]);
    }


    /**
     * Get sub sub category for shopee category.
     */
    public function getShopeeProductSubSubCategory(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = [];
                if (isset($request->category_parent_id, $request->shopee_shop_id)) {
                    $shopee_shop = Shopee::whereShopId($request->shopee_shop_id)->first();
                    if (isset($shopee_shop)) {
                        $shopee_parent_category = ShopeeProductCategory::whereShopeeId($shopee_shop->id)
                            ->whereCategoryId($request->category_parent_id)
                            ->first();
                        if (isset($shopee_parent_category, $shopee_parent_category->parent_id)) {
                            $data = ShopeeProductCategory::select("category_id", "category_name")
                                ->whereShopeeId($shopee_shop->id)
                                ->whereParentId($request->category_parent_id)
                                ->orderBy('category_name', 'asc')
                                ->get();
                        }
                    }
                }
                return response()->json([
                    "success"   => true,
                    "data"      => $data
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }

        return response()->json([
            "success"   => false,
            "data"      => []
        ]);
    }


    public function getShopeeProductMissingInfo(Request $request)
    {
        try {
            if (isset($request->ids) and !empty($request->ids) and isJson($request->ids)) {
                $data = [];
                $product_ids = json_decode($request->ids);
                if (sizeof($product_ids) > 0) {
                    $shopee_products = ShopeeProduct::select("id", "type", "parent_id", "product_id", "website_id", "images", "tier_2_variations", "total_cover_images", "total_size_wise_variation_images", "total_size_wise_options")
                        ->whereIn("id", $product_ids)
                        ->get();
                    foreach ($shopee_products as $product) {
                        $sync_shopee_product = false;
                        /* Check if there are more than 5 cover images available. */
                        $data[$product["id"]]["need_more_cover_images"] = true;
                        if (isset($product["total_cover_images"]) and $product["total_cover_images"] != -1) {
                            if ($product["total_cover_images"] >= 5) {
                                $data[$product["id"]]["need_more_cover_images"] = false;
                            }
                        } else {
                            $sync_shopee_product = true;
                            if (isset($product["images"]) and !empty($product["images"]) and isJson($product["images"]) and
                                sizeof(json_decode($product["images"])) >= 5) {
                                $data[$product["id"]]["need_more_cover_images"] = false;
                            }
                        }

                        /**
                         * Check if any variation has missing images.
                         * Check if necessary info is available in database else process info.
                         */
                        $data[$product["id"]]["missing_variation_images"] = false;
                        if ($product["type"] == "variable") {
                            if (isset($product["total_size_wise_variation_images"], $product["total_size_wise_options"]) and
                                $product["total_size_wise_options"] != -1 and $product["total_size_wise_variation_images"] != -1) {
                                if ($product["total_size_wise_options"] > 0 and $product["total_size_wise_options"] != $product["total_size_wise_variation_images"]) {
                                    $data[$product["id"]]["missing_variation_images"] = true;
                                }
                            } else {
                                $sync_shopee_product = true;
                                $data[$product["id"]]["missing_variation_images"] = true;
                                if (isset($product["tier_2_variations"]) and !empty($product["tier_2_variations"]) and isJson($product["tier_2_variations"])) {
                                    $tier_2_variations = json_decode($product["tier_2_variations"]);
                                    $tier_2_variation__size = [];
                                    foreach ($tier_2_variations as $index => $variation) {
                                        if ($index == 0) {
                                            $tier_2_variation__size = (array) $variation;
                                            break;
                                        }
                                    }
                                    if(isset($tier_2_variation__size["images_url"], $tier_2_variation__size["options"])) {
                                        $total_variation_images_count = 0;
                                        foreach ($tier_2_variation__size["images_url"] as $image_url) {
                                            if (!empty($image_url)) {
                                                $total_variation_images_count += 1;
                                            }
                                        }
                                        if ($total_variation_images_count == sizeof($tier_2_variation__size["options"])) {
                                            $data[$product["id"]]["missing_variation_images"] = false;
                                        }
                                    }
                                }
                            }
                        }

                        /**
                         * Update shopee product and the variations in database.
                         * NOTE: Only dispatch sync for parent products.
                         * This is done to avoid the image calculation every time for missing necessary info in database.
                         */
                        if ($sync_shopee_product and $product["parent_id"]==0) {
                            ShopeeProductDetailSync::dispatch([
                                "shopid"    => (int) $product["website_id"],
                                "item_id"   => (int) $product["product_id"]
                            ], null, $this->getShopeeSellerId());
                        }
                    }

                    return response()->json([
                        "success"   => true,
                        "data"      => $data
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }

        return response()->json([
            "success"   => false,
            "data"      => []
        ]);
    }


    /**
     * Delete shopee product.
     */
    public function delete(Request $request)
    {
        try {
            if (isset($request->id) and !empty($request->id)) {
                $shopee_product = ShopeeProduct::whereId($request->id)
                    ->orWhere('product_id', $request->id)
                    ->first();

                if (isset($shopee_product)) {
                    $client = $this->getShopeeClient($shopee_product->website_id);
                    if (isset($client)) {
                        if ($shopee_product->type == "simple" || strpos($shopee_product->type, "V-") !== false) {
                            /* Remove cover images from system. */
                            $images_in_shopee = [];
                            $cover_images = json_decode($shopee_product->images);
                            foreach ($cover_images as $image) {
                                if (empty($image)) {
                                    continue;
                                }
                                if (is_numeric(strpos($image, "/uploads/product/"))) {
                                    $file_data = explode("/uploads/product/", $image);
                                    if (isset($file_data[1])) {
                                        $destination_path = public_path('uploads/product/'.$file_data[1]);
                                        if (File::exists($destination_path)) {
                                            File::delete($destination_path);
                                        }
                                    }
                                } else {
                                    array_push($images_in_shopee, $image);
                                }
                            }

                            if (sizeof($images_in_shopee)) {
                                /* Remove cover images from Shopee. */
                                $client->item->deleteItemImg([
                                    "item_id" => $shopee_product->product_id,
                                    "images"  => $images_in_shopee
                                ]);
                            }

                            /* Remove variation images from system. */
                            if (strpos($shopee_product->type, "V-") !== false) {
                                $tier_2_variations = $shopee_product->tier_2_variations;
                                if (isset($tier_2_variations)) {
                                    $tier_2_variations = json_decode($tier_2_variations);
                                    foreach ($tier_2_variations as $variation) {
                                        $variation_images = $variation["images_url"];
                                        foreach ($variation_images as $image) {
                                            if (empty($image)) {
                                                continue;
                                            }
                                            if (is_numeric(strpos($image, "/uploads/product/"))) {
                                                $file_data = explode("/uploads/product/", $image);
                                                if (isset($file_data[1])) {
                                                    $destination_path = public_path('uploads/product/'.$file_data[1]);
                                                    if (File::exists($destination_path)) {
                                                        File::delete($destination_path);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            /* Remove product from shopee. */
                            $client->item->delete([
                                "item_id" => $shopee_product->product_id
                            ])->getData();

                            /* Remove product from database. */
                            if (strpos($shopee_product->type, 'V-') !== false) {
                                ShopeeProduct::whereParentId($shopee_product->product_id)->delete();
                            }
                            $shopee_product->delete();
                            return response()->json([
                                "success"   => true,
                                "message"   => __("translation.Successfully deleted shopee product")
                            ]);
                        } else {
                            if ($shopee_product->parent_id !== 0) {
                                $response = $client->item->deleteVariation([
                                    'item_id'       => (int) $shopee_product->parent_id,
                                    'variation_id'  => (int) $shopee_product->product_id
                                ]);

                                if (isset($response->item_id)) {
                                    /* Update shopee product and the variations in database. */
                                    ShopeeProductDetailSync::dispatch([
                                        "shopid"    => (int) $shopee_product->website_id,
                                        "item_id"   => (int) $shopee_product->parent_id
                                    ], null, $this->getShopeeSellerId());

                                    /* Delete the variation from database. */
                                    $shopee_product->delete();

                                    return response()->json([
                                        "success"   => true,
                                        "message"   => __("translation.Successfully deleted shopee product")
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }

        return response()->json([
            "success"   => false,
            "message"   => __("translation.Failed to delete shopee product")
        ]);
    }
}
