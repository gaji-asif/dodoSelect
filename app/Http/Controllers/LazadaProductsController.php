<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lazada\Product\StoreLazadaProductRequest;
use App\Jobs\LazadaProductSyncSpecificItem;
use App\Models\Lazada;
use App\Models\LazadaProduct;
use App\Models\LazadaProductBrand;
use App\Models\LazadaProductCategory;
use App\Models\LazadaSetting;
use App\Traits\LazadaOrderPurchaseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Lazada\LazopRequest;

use function Aws\filter;
use function PHPUnit\Framework\isJson;

class LazadaProductsController extends Controller
{
    use LazadaOrderPurchaseTrait;

    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Create lazada product.
     */
    public function create()
    {
        /**
         * Get lazada product categories.
         * NOTE: 
         * Where "website_id" refers "id" in "lazadas" table. 
         */
        /* Main parent category */
        $data["lazada_category_parent_id"] = 0;
        $data["lazada_product_parent_categories"] = [];
        /* Sub category */
        $data["lazada_category_parent_id_1"] = 0;
        $data["lazada_product_parent_categories_1"] = [];
        /* Sub sub category */
        $data["lazada_category_id"] = 0;
        $data["lazada_product_categories"] = [];

        $data["lazada_shops"] = Lazada::select("id", "shop_name")->get();
        
        return view('lazada.product.crud.create', $data);
    }


    /**
     * Store lazada product.
     * NOTE:
     * Here "website_id" refers to "shop_id".
     */
    // public function store(Request $request) 
    public function store(StoreLazadaProductRequest $request) 
    {
        try {
            if ($request->ajax()) {
                $lazada_shop = Lazada::find($request->website_id);
                if (!isset($lazada_shop)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __('translation.Shop Not Found')
                    ]);
                }

                /* Product cover images. */
                $lazada_product_images = [];
                $uploaded_images_path = [];
                if (isset($request->cover_images_count) and $request->cover_images_count > 0) {
                    $destination_path = public_path('uploads/product');
                    for ($i=0; $i<$request->cover_images_count; $i++) {
                        if ($request->hasFile('cover_image_'.$i)) {
                            $image = $request->file('cover_image_'.$i);

                            /* Old image upload code. */
                            // $upload_name =  time().'_'.Str::random(25).'.'.$image->getClientOriginalExtension();
                            // $image->move($destination_path, $upload_name);
                            // array_push($lazada_product_images, asset('uploads/product/'.$upload_name));
                            // array_push($uploaded_images_path, $destination_path.'/'.$upload_name);

                            $path = Storage::disk('s3')->put('uploads/lazada/products', $image, 'public');
                            $path = Storage::disk('s3')->url($path);
                            array_push($lazada_product_images, $path);
                            array_push($uploaded_images_path, $path);
                        }
                    }
                }

                /* Product variation images. */
                $lazada_product_variation_images = [];
                $uploaded_variation_images_path = [];
                if (isset($request->variation_images_count) and $request->variation_images_count > 0) {
                    $destination_path = public_path('uploads/product');
                    for ($i=0; $i<$request->variation_images_count; $i++) {
                        $lazada_product_variation_images[$i] = [];
                        $uploaded_variation_images_path[$i] = [];
                        for($j=0; $j<9; $j++) {
                            if ($request->hasFile('variation_image_'.$i.'_'.$j)) {
                                $image = $request->file('variation_image_'.$i.'_'.$j);

                                /* Old image upload code. */
                                // $upload_name =  time().'_'.Str::random(25).'.'.$image->getClientOriginalExtension();
                                // $image->move($destination_path, $upload_name);
                                // array_push($lazada_product_variation_images[$i], asset('uploads/product/'.$upload_name));
                                // array_push($uploaded_variation_images_path[$i], $destination_path.'/'.$upload_name);

                                $path = Storage::disk('s3')->put('uploads/lazada/products', $image, 'public');
                                $path = Storage::disk('s3')->url($path);
                                array_push($lazada_product_variation_images[$i], $path);
                                array_push($uploaded_variation_images_path[$i], $path);
                            } else {
                                break;
                            }
                        }
                    }
                }

                $access_token = $this->getAccessTokenForLazada($request->website_id);
                if (!empty($access_token)) { 
                    $client = $this->getLazadaClient();
                    $obj = $this->getRequestObjectForProductInLazada([
                        "product_type"      => $request->type,
                        "category_id"       => (int)$request->lazada_category_id,
                        "name"              => $request->name,
                        "description"       => isset($request->description)?$request->description:null,
                        "short_desciption"  => isset($request->short_desciption)?$request->short_desciption:null,
                        "description_en"    => isset($request->description_en)?$request->description_en:null,
                        "short_desciption_en"  => isset($request->short_desciption_en)?$request->short_desciption_en:null,
                        "brand"             => isset($request->lazada_brand)?$request->lazada_brand:null,
                        "model"             => isset($request->lazada_model)?$request->lazada_model:null,
                        "video"             => isset($request->lazada_video)?$request->lazada_video:null,
                        "warranty"          => isset($request->lazada_warranty)?$request->lazada_warranty:null,
                        "warranty_type"     => isset($request->lazada_warranty_type)?$request->lazada_warranty_type:null,
                        "color_family"      => isset($request->lazada_color_family)?$request->lazada_color_family:null,
                        "variation_name"    => (isset($request->variation_name) and isJson($request->variation_name))?json_decode($request->variation_name):[],
                        "variation_sku"     => (isset($request->variation_sku) and isJson($request->variation_sku))?json_decode($request->variation_sku):[],
                        "variation_price"   => (isset($request->variation_price) and isJson($request->variation_price))?json_decode($request->variation_price):[],
                        "variation_stock"   => (isset($request->variation_stock) and isJson($request->variation_stock))?json_decode($request->variation_stock):[],
                        "variation_package_weight"    => (isset($request->variation_package_weight) and isJson($request->variation_package_weight))?json_decode($request->variation_package_weight):[],
                        "variation_package_length"    => (isset($request->variation_package_length) and isJson($request->variation_package_length))?json_decode($request->variation_package_length):[],
                        "variation_package_height"    => (isset($request->variation_package_height) and isJson($request->variation_package_height))?json_decode($request->variation_package_height):[],
                        "variation_package_width"     => (isset($request->variation_package_width) and isJson($request->variation_package_width))?json_decode($request->variation_package_width):[],
                        "variation_package_content"   => (isset($request->variation_package_content) and isJson($request->variation_package_content))?json_decode($request->variation_package_content):[],
                        "cover_images"      => $lazada_product_images,
                        "variation_images"  => $lazada_product_variation_images,
                    ], "create");

                    if (isset($client, $obj)) {
                        $response = $client->execute($obj, $access_token);
                        if (isset($response) and $this->isJson($response)) {
                            $data = json_decode($response);
                            if (isset($data->data, $data->data->item_id)) {
                                LazadaProductSyncSpecificItem::dispatch($data->data->item_id, $lazada_shop->id, $this->getLazadaSellerId());
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    public function edit($id)
    {
        $data['id'] = $id;
        $products = LazadaProduct::whereParentId($id)->get();
        $product = $products[0];
        $data['product'] = $product;

        abort_if(!$data['product'], Response::HTTP_NOT_FOUND, ucfirst(__('translation.data_not_found')));

        $data["lazada_shops"] = Lazada::select("id", "shop_name")->get();

        if (isset($data["product"]["images"]) and !empty($data["product"]["images"])) {
            $data['product_images'] = json_decode($data["product"]["images"]);
        }

        $data['attributes'] = isset($product->specifications)?json_decode($product->specifications):null;

        /**
         * Get lazada product categories.
         * NOTE: 
         * Where "website_id" refers to "shop_id" not "id" in "lazadas" table. 
         */
        /* Main parent category */
        $data["lazada_category_parent_id"] = 0;
        $data["lazada_product_parent_categories"] = [];
        /* Sub category */
        $data["lazada_category_parent_id_1"] = 0;
        $data["lazada_product_parent_categories_1"] = [];
        /* Sub sub category */
        $data["lazada_category_id"] = 0;
        $data["lazada_product_categories"] = [];
        if (isset($data["product"]["website_id"])) {
            $lazada_shop_id = (int)$data["product"]["website_id"];
            $lazada_shop = Lazada::find($lazada_shop_id);
            $data["lazada_product_parent_categories"] = LazadaProductCategory::whereParentId(0)
                ->distinct()
                ->get();
            if (isset($data["product"]["lazada_category_id"]) and (int)$data["product"]["lazada_category_id"] > 0) {
                $category_id = (int)$data["product"]["lazada_category_id"];
                $lazada_category = LazadaProductCategory::whereCategoryId($category_id)
                    ->first();
                if (isset($lazada_category, $lazada_category->parent_id)) {
                    /**
                     * Get available categories falling under same parent for the selected "lazada_category_id" 
                     */
                    $data["lazada_category_id"] = $category_id;
                    $data["lazada_product_categories"] = LazadaProductCategory::whereParentId($lazada_category->parent_id)
                        ->orderBy('category_name', 'asc')
                        ->groupBy('category_name')
                        ->get();
                    /**
                     * Get parent lazada product categories.
                     * NOTE: 
                     * Only getting the first parent. 
                     * Ex: Category >> Sub Category >> Sub Sub Category.
                     * "lazada_category_id" refers to "Sub Sub Category". Get the "parent_id" of "Sub Sub Category" then 
                     * get the "Sub Category". From the "Sub Category" get the "parent_id" and get all the "Sub Category"
                     * as "Lazada Parent Category". 
                     */
                    $lazada_parent_category = LazadaProductCategory::whereLazadaId($lazada_shop->id)
                        ->whereCategoryId($lazada_category->parent_id)
                        ->first();
                    if (isset($lazada_parent_category, $lazada_parent_category->parent_id)) {
                        $lazada_parent_categories = LazadaProductCategory::whereParentId($lazada_parent_category->parent_id)
                            ->orderBy('category_name', 'asc')
                            ->groupBy('category_name')
                            ->get();        
                        $data["lazada_category_parent_id"] = $lazada_parent_category->parent_id;
                        if (sizeof($lazada_parent_categories) > 0) {
                            $data["lazada_category_parent_id_1"] = $lazada_category->parent_id;
                            $data["lazada_product_parent_categories_1"] = $lazada_parent_categories;
                        }
                    } else {
                        $lazada_parent_categories = LazadaProductCategory::whereCategoryId($lazada_category->parent_id)
                            ->orderBy('category_name', 'asc')
                            ->groupBy('category_name')
                            ->get();
                        if (sizeof($lazada_parent_categories) > 0) {
                            $data["lazada_category_parent_id_1"] = $lazada_category->category_id;
                            $data["lazada_product_parent_categories_1"] = $lazada_parent_categories;
                        }
                    }
                } 
            } 
        }

        /* Get lazada variation product specific images. */
        $data["variations"] = [];
        foreach($products as $index => $product) {
            if (isset($product->variations) and isJson($product->variations)) {
                $variation_info = json_decode($product->variations);
                $name = "";
                for ($i=1;$i<11;$i++) {
                    $var_name = "Variation".($i);
                    if (isset($variation_info->$var_name)) {
                        $name = $variation_info->$var_name;
                        break;
                    }
                }
                $info = [
                    "name"      => $name,
                    "sku"       => isset($variation_info->SellerSku)?$variation_info->SellerSku:"",
                    "price"     => $product->price,
                    "stock"     => isset($variation_info->quantity)?$variation_info->quantity:0,
                    "images"    => isset($variation_info->Images)?$variation_info->Images:[] // variation_specific_image
                ];
                if (isset($variation_info->package_width)) {
                    $info["package_width"] = (float) $variation_info->package_width;
                }
                if (isset($variation_info->package_height)) {
                    $info["package_height"] = (float) $variation_info->package_height;
                }
                if (isset($variation_info->package_length)) {
                    $info["package_length"] = (float) $variation_info->package_length;
                }
                if (isset($variation_info->package_weight)) {
                    $info["package_weight"] = (float) $variation_info->package_weight;
                }
                array_push($data["variations"], $info);
            }
        }
        $data["total_variations_count"] = sizeof($data["variations"]);

        return view('lazada.product.crud.edit', $data);
    }


    /**
     * Store lazada product.
     * NOTE:
     * Here "website_id" refers to "shop_id".
     */
    public function update(Request $request) 
    {       
        try {
            if ($request->ajax()) {
                $lazada_shop = Lazada::find($request->website_id);
                if (!isset($lazada_shop)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __('translation.Shop Not Found')
                    ]);
                }
                $lazada_products = LazadaProduct::whereParentId($request->item_id)->get();
                if (!isset($lazada_products[0])) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __('translation.No Such Product Found')
                    ]);
                }

                /* Product cover images. */
                $lazada_product_images = [];
                $uploaded_images_path = [];
                if (isset($request->cover_images_count) and $request->cover_images_count > 0) {
                    $destination_path = public_path('uploads/product');
                    for ($i=0; $i<$request->cover_images_count; $i++) {
                        if ($request->hasFile('cover_image_'.$i)) {
                            $image = $request->file('cover_image_'.$i);

                            /* Old image upload code. */
                            // $upload_name =  time().'_'.Str::random(25).'.'.$image->getClientOriginalExtension();
                            // $image->move($destination_path, $upload_name);
                            // array_push($lazada_product_images, asset('uploads/product/'.$upload_name));
                            // array_push($uploaded_images_path, $destination_path.'/'.$upload_name);

                            $path = Storage::disk('s3')->put('uploads/lazada/products', $image, 'public');
                            $path = Storage::disk('s3')->url($path);
                            array_push($lazada_product_images, $path);
                            array_push($uploaded_images_path, $path);
                        }
                    }
                }
                $existing_product_images = [];
                if (isset($lazada_products[0], $lazada_products[0]->images)) {
                    /* Variation images in database. */
                    $existing_product_images = (array) json_decode($lazada_products[0]->images);
                    if (sizeof($existing_product_images) > 0) {
                        $lazada_product_images = array_merge($existing_product_images, $lazada_product_images);
                        if (sizeof($lazada_product_images) > 8) {
                            $lazada_product_images = array_slice($lazada_product_images, 0, 8);
                        }
                    }
                }

                /* Product variation images. */
                $lazada_product_variation_images = [];
                $uploaded_variation_images_path = [];
                $destination_path = public_path('uploads/product');
                for ($i=0; $i<sizeof($lazada_products); $i++) {
                    $lazada_product_variation_images[$i] = [];
                    $uploaded_variation_images_path[$i] = [];
                    for($j=0; $j<9; $j++) {
                        if ($request->hasFile('variation_image_'.$i.'_'.$j)) {
                            $image = $request->file('variation_image_'.$i.'_'.$j);

                            /* Old image upload code. */
                            // $upload_name =  time().'_'.Str::random(25).'.'.$image->getClientOriginalExtension();
                            // $image->move($destination_path, $upload_name);
                            // array_push($lazada_product_variation_images[$i], asset('uploads/product/'.$upload_name));
                            // array_push($uploaded_variation_images_path[$i], $destination_path.'/'.$upload_name);

                            $path = Storage::disk('s3')->put('uploads/lazada/products', $image, 'public');
                            $path = Storage::disk('s3')->url($path);
                            array_push($lazada_product_variation_images[$i], $path);
                            array_push($uploaded_variation_images_path[$i], $path);
                        } else {
                            break;
                        }
                    }

                    $existing_variation_product_images = [];
                    if (isset($lazada_products[$i], $lazada_products[$i]->variations)) {
                        /* Variation images in database. */
                        $variations_data_db = json_decode($lazada_products[$i]->variations);
                        if (isset($variations_data_db->Images)) {
                            $existing_variation_product_images = (array) $variations_data_db->Images;
                            if (sizeof($existing_variation_product_images) > 0) {
                                $lazada_product_variation_images[$i] = array_merge($lazada_product_variation_images[$i], $existing_variation_product_images);
                                if (sizeof($lazada_product_variation_images[$i]) > 8) {
                                    $lazada_product_variation_images[$i] = array_slice($lazada_product_variation_images[$i], 0, 8);
                                }
                            }
                        }
                    }
                }

                $access_token = $this->getAccessTokenForLazada($request->website_id);
                if (!empty($access_token)) { 
                    $client = $this->getLazadaClient();
                    $obj = $this->getRequestObjectForProductInLazada([
                        "product_type"      => $request->type,
                        "category_id"       => (int)$request->lazada_category_id,
                        "name"              => $request->name,
                        "description"       => isset($request->description)?$request->description:null,
                        "short_desciption"  => isset($request->short_desciption)?$request->short_desciption:null,
                        "description_en"    => isset($request->description_en)?$request->description_en:null,
                        "short_desciption_en"  => isset($request->short_desciption_en)?$request->short_desciption_en:null,
                        "brand"             => isset($request->lazada_brand)?$request->lazada_brand:null,
                        "model"             => isset($request->lazada_model)?$request->lazada_model:null,
                        "video"             => isset($request->lazada_video)?$request->lazada_video:null,
                        "warranty"          => isset($request->lazada_warranty)?$request->lazada_warranty:null,
                        "warranty_type"     => isset($request->lazada_warranty_type)?$request->lazada_warranty_type:null,
                        "color_family"      => isset($request->lazada_color_family)?$request->lazada_color_family:null,
                        "variation_name"    => (isset($request->variation_name) and isJson($request->variation_name))?json_decode($request->variation_name):[],
                        "variation_sku"     => (isset($request->variation_sku) and isJson($request->variation_sku))?json_decode($request->variation_sku):[],
                        "variation_price"   => (isset($request->variation_price) and isJson($request->variation_price))?json_decode($request->variation_price):[],
                        "variation_stock"   => (isset($request->variation_stock) and isJson($request->variation_stock))?json_decode($request->variation_stock):[],
                        "variation_package_weight"    => (isset($request->variation_package_weight) and isJson($request->variation_package_weight))?json_decode($request->variation_package_weight):[],
                        "variation_package_length"    => (isset($request->variation_package_length) and isJson($request->variation_package_length))?json_decode($request->variation_package_length):[],
                        "variation_package_height"    => (isset($request->variation_package_height) and isJson($request->variation_package_height))?json_decode($request->variation_package_height):[],
                        "variation_package_width"     => (isset($request->variation_package_width) and isJson($request->variation_package_width))?json_decode($request->variation_package_width):[],
                        "variation_package_content"   => (isset($request->variation_package_content) and isJson($request->variation_package_content))?json_decode($request->variation_package_content):[],
                        "cover_images"      => $lazada_product_images,
                        "variation_images"  => $lazada_product_variation_images,
                    ], "update");

                    if (isset($client, $obj)) {
                        $response = $client->execute($obj, $access_token);
                        if (isset($response) and $this->isJson($response)) {
                            $data = json_decode($response);
                            if (isset($data->data, $data->data->item_id)) {
                                LazadaProductSyncSpecificItem::dispatch($data->data->item_id, $lazada_shop->id, $this->getLazadaSellerId());
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
    }


    private function getRequestObjectForProductInLazada($params=[], $method="create") 
    {
        try {
            $uri = "/product/create";
            $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><Request><Product>";
            if ($method == "update") {
                $uri = "/product/update";
                if (isset($params["parent_id"]) and !empty($params["parent_id"])) {
                    $xml .= "<ItemId>".$params["parent_id"]."</ItemId>";
                }
                if (isset($params["category_id"]) and !empty($params["category_id"])) {
                    $xml .= "<PrimaryCategory>".$params["category_id"]."</PrimaryCategory>";
                }
            } else {
                if (isset($params["category_id"]) and !empty($params["category_id"])) {
                    $xml .= "<PrimaryCategory>".$params["category_id"]."</PrimaryCategory>";
                    $xml .= "<SPUId/><AssociatedSku/>";
                }
            }

            /* Normal Attributes */
            $xml .= "<Attributes>";
            if (isset($params["name"]) and !empty($params["name"])) {   
                $xml .= "<name>".$params["name"]."</name>";
            }
            if (isset($params["description"]) and !empty($params["description"])) {   
                $xml .= "<description>".$params["description"]."</description>";
            }
            if (isset($params["short_description"]) and !empty($params["short_description"])) {   
                $xml .= "<short_description>".$params["short_description"]."</short_description>";
            }
            if (isset($params["description_en"]) and !empty($params["description_en"])) {   
                $xml .= "<description_en>".$params["description_en"]."</description_en>";
            }
            if (isset($params["short_description_en"]) and !empty($params["short_description_en"])) {   
                $xml .= "<short_description_en>".$params["description"]."</short_description_en>";
            }
            if (isset($params["brand"]) and !empty($params["brand"])) {   
                $xml .= "<brand>".$params["brand"]."</brand>";
            }
            if (isset($params["model"]) and !empty($params["model"])) {   
                $xml .= "<model>".$params["model"]."</model>";
            }
            if (isset($params["video"]) and !empty($params["video"])) {   
                /* <video>12345 (fill with the video id of the previously uploaded video) optional</video> */
                $xml .= "<video>".$params["video"]."</video>";
            }
            if (isset($params["color_family"]) and !empty($params["color_family"])) {   
                $xml .= "<color_family>".$params["color_family"]."</color_family>";
            }
            if (isset($params["warranty"]) and !empty($params["warranty"])) {   
                $xml .= "<warranty>".$params["warranty"]."</warranty>";
            }
            if (isset($params["warranty_type"]) and !empty($params["warranty_type"])) {   
                $xml .= "<warranty_type>".$params["warranty_type"]."</warranty_type>";
            }
            if (isset($params["cover_images"]) and sizeof($params["cover_images"]) > 0) {
                $xml .= "<Images>";
                foreach($params["cover_images"] as $cover_image) {
                    $xml .= "<Image>".$cover_image."</Image>";
                }
                $xml .= "</Images>";
            }
            $xml .= "</Attributes>";

            /* Sku (Variation) attributes. */
            if (isset($params["product_type"]) and !empty($params["product_type"]) and $params["product_type"]=="variable") {   
                $xml .= "<Skus>";
                if (isset($params["variation_name"], $params["variation_sku"], $params["variation_price"])) {
                    $variation_name = $params["variation_name"];
                    $variation_sku = $params["variation_sku"];
                    $variation_price = $params["variation_price"];
                    $variation_stock = $params["variation_stock"];
                    $variation_package_weight = $params["variation_package_weight"];
                    $variation_package_length = $params["variation_package_length"];
                    $variation_package_height = $params["variation_package_height"];
                    $variation_package_width = $params["variation_package_width"];
                    $variation_package_content = $params["variation_package_content"];
                    if (sizeof($variation_name) > 0) {
                        for($i=0; $i<sizeof($variation_name); $i++) {
                            $xml .= "<Sku>";
                            // $xml .= "<name>".$variation_name[$i]."</name>";
                            if ($method == "update") {
                                if(isset($variation_sku[$i])) {
                                    $xml .= "<SellerSku>".str_replace(" ", "-", $variation_sku[$i])."</SellerSku>";
                                }
                            }
                            if (isset($variation_price[$i])) {
                                $xml .= "<price>".round((float)$variation_price[$i], 2)."</price>";
                            }
                            if (isset($variation_stock[$i])) {
                                $xml .= "<quantity>".round($variation_stock[$i])."</quantity>";
                            }
                            if (isset($variation_package_weight[$i])) {
                                $xml .= "<package_weight>".round((float)$variation_package_weight[$i], 2)."</package_weight>";
                            }
                            if (isset($variation_package_length[$i])) {
                                $xml .= "<package_length>".round((float)$variation_package_length[$i], 2)."</package_length>";
                            }
                            if (isset($variation_package_height[$i])) {
                                $xml .= "<package_height>".round((float)$variation_package_height[$i], 2)."</package_height>";
                            }
                            if (isset($variation_package_width[$i])) {
                                $xml .= "<package_width>".round((float)$variation_package_width[$i], 2)."</package_width>";
                            }
                            if (isset($variation_package_content[$i])) {
                                $xml .= "<package_content>".$variation_package_content[$i]."</package_content";
                            }
                            if (isset($params["variation_images"],$params["variation_images"][$i]) and sizeof($params["variation_images"][$i]) > 0) {
                                $xml .= "<Images>";
                                foreach ($params["variation_images"][$i] as $image) {
                                    $xml .= "<Image>".$image."</Image>";
                                }
                                $xml .= "</Images>";
                            }
                            $xml .= "</Sku>";
                        }
                    }
                }
                $xml .= "</Skus>";
            }
            $xml .= "</Product></Request>";
            
            $request = new LazopRequest($uri, 'POST');
            $request->addApiParam('payload', $xml);
            return $request;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    /**
     * 
     */
    public function updateLazadaProductImage(Request $request)
    {
        try {
            if ($request->ajax()) {
                $product = LazadaProduct::where('product_id', $request->id)->first();
                $data = [];
                if ($request->hasFile('image')) {
                    $upload = $request->file('image');
                    $upload_name =  time() . '_' . Str::random(10) . '_' . $upload->getClientOriginalName();
                    $destinationPath = public_path('uploads/product');
                    $upload->move($destinationPath, $upload_name);
                    $request->image_path = "$destinationPath/$upload_name";

                    $product_images = $product->images;
                    if (isset($product_images) and !empty($product_images)) {
                        $product_images = json_decode($product_images);
                        if (sizeof($product_images) > 0) {
                            array_push($product_images, asset('uploads/product/'.$upload_name));
                        } else {
                            $product_images = [asset('uploads/product/'.$upload_name)];
                        }
                        $product->images = json_encode($product_images);
                        $data = $product_images;
                        $product->save();

                        $access_token = $this->getAccessTokenForLazada((int) $request->website_id);
                        $client = $this->getLazadaClient();
                        if (isset($client) and !empty($access_token)) {
                            $obj = $this->getRequestObjuectToSetImages([
                                "seller_sku"=> $product->product_code,
                                "images"    => $data    
                            ]);
    
                            $response = $client->execute($obj, $access_token);
                        }
                    }

                    return response()->json([
                        "success"   => true,
                        "data"      => $data,
                        "message"   => __("translation.Successfully uploaded lazada product images")
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }

        return response()->json([
            "success"   => false,
            "message"   => __("translation.Failed to upload lazada product images")
        ]);
    }


    /**
     * Delete specific lazada variation product image.
     */
    public function deleteLazadaProductVariationImage(Request $request)
    {
        try {
            if ($request->ajax()) {
                $product = LazadaProduct::whereProductCode($request->sku)->first();
                if (!isset($product)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __('translation.Product Not Found')
                    ]);
                }
                $data = [];
                if (isset($product, $product->variations, $request->image)) {
                    $variation = json_decode($product->variations);
                    if (isset($variation->Images)) {
                        $product_images = $variation->Images;
                        if (sizeof($product_images) > 0) {
                            foreach ($product_images as $image) {
                                if ($image == $request->image) {
                                    continue;
                                } else {
                                    array_push($data, $image);
                                }
                            }
                            
                            $variation->Images = $data;
                            $product->variations = json_encode($variation);
                            $product->save();
    
                            $access_token = $this->getAccessTokenForLazada((int) $product->website_id);
                            $client = $this->getLazadaClient();
                            if (isset($client) and !empty($access_token)) {
                                $obj = $this->getRequestObjuectToSetImages([
                                    "seller_sku"=> $product->product_code,
                                    "images"    => $data    
                                ]);
        
                                $response = json_decode($client->execute($obj, $access_token));
                                if (isset($response->data)) {
                                    if (isset($response->data->code) and (int)$response->data->code !== 0) {
                                        return response()->json([
                                            "success"   => true,
                                            "message"   => __("translation.Successfully removed lazada product.")
                                        ]);
                                    }
                                }
                            }
                        }
                    }

                    return response()->json([
                        "success"   => true,
                        "data"      => $data,
                        "message"   => __("translation.Successfully deleted lazada product image")
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }

        return response()->json([
            "success"   => false,
            "message"   => __("translation.Failed to delete lazada product images")
        ]);
    }


    /**
     * 
     * @param array $params
     */
    private function getRequestObjuectToSetImages($params=[]) 
    {
        try {
            $request = new LazopRequest('/images/set', 'POST');
            $payload = "<Request><Product><Skus><Sku>";
            if (isset($params["seller_sku"]) and !empty($params["seller_sku"])) {
                $payload .= "<SellerSku>".$params["seller_sku"]."</SellerSku>";
            }
            if (isset($params["images"])) {
                $payload .= "<Images>";
                if (sizeof($params["images"]) > 0) {
                    foreach ($params["images"] as $image) {
                        $payload .= "<Image>".$image."</Image>";
                    }
                }
                $payload .= "</Images>";
            }      
            $payload .= "</Sku></Skus></Product></Request>";
            $request->addApiParam('payload', $payload);
            return $request;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    /**
     * Get categories for specific lazada shop.
     */
    public function getLazadaProductCategory(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = [];
                if (isset($request->id)) {
                    $lazada_shop = Lazada::find((int)$request->id);
                    if (isset($lazada_shop)) {
                        $data = LazadaProductCategory::select("category_id", "category_name")
                            ->whereParentId(0)
                            ->orderBy('category_name', 'asc')
                            ->groupBy('category_name')
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
     * Get sub category for lazada category.
     */
    public function getLazadaProductSubCategory(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = [];
                if (isset($request->category_parent_id, $request->id)) {
                    $lazada_shop = Lazada::find($request->id);
                    if (isset($lazada_shop)) {
                        $lazada_parent_category = LazadaProductCategory::whereCategoryId($request->category_parent_id)
                            ->first();
                        if (isset($lazada_parent_category, $lazada_parent_category->parent_id)) {
                            $data = LazadaProductCategory::select("category_id", "category_name")
                                ->whereParentId($request->category_parent_id)
                                ->orderBy('category_name', 'asc')
                                ->groupBy('category_name')
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
     * Get sub sub category for lazada category.
     */
    public function getLazadaProductSubSubCategory(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = [];
                if (isset($request->category_parent_id, $request->id)) {
                    $lazada_shop = Lazada::find($request->id);
                    if (isset($lazada_shop)) {
                        $lazada_parent_category = LazadaProductCategory::whereCategoryId($request->category_parent_id)
                            ->first();
                        if (isset($lazada_parent_category, $lazada_parent_category->parent_id)) {
                            $data = LazadaProductCategory::select("category_id", "category_name")
                                ->whereParentId($request->category_parent_id)
                                ->orderBy('category_name', 'asc')
                                ->groupBy('category_name')
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
     * Get pickup address info from "Lazada".
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductCategoryWiseAttributesFromLazada(Request $request)
    {
        try {
            if ($request->ajax() and isset($request->id, $request->lazada_category_id)) {
                $lazada_shop = Lazada::find($request->id);
                if (!isset($lazada_shop)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __("translation.Shop Not Found")
                    ]);
                }

                $access_token = $this->getAccessTokenForLazada((int) $request->id);
                $client = $this->getLazadaClient();
                if (isset($client) and !empty($access_token)) {
                    $obj = $this->getRequestObjectToGetProductCategoryAttributes([
                        'primary_category_id'   => (int) $request->lazada_category_id
                    ]);
        
                    $response = json_decode($client->execute($obj, $access_token));
                    
                    if (isset($response->data)) {
                        $normal_attributes = [];
                        $sku_attributes = [];
                        foreach ($response->data as $attribute) {
                            if ($attribute->attribute_type == "normal") {  
                                if ($attribute->is_mandatory and !in_array($attribute->name, [
                                    "name"
                                ])) {
                                    array_push($normal_attributes, [
                                        "id"             => $attribute->id,
                                        "input_type"     => $attribute->input_type,
                                        "attribute_type" => $attribute->attribute_type,
                                        "label"          => $attribute->label, 
                                        "name"           => $attribute->name, 
                                        "options"        => $attribute->options
                                    ]);
                                } else {
                                    if ($attribute->name == "color_family") {
                                        array_push($normal_attributes, [
                                            "id"             => $attribute->id,
                                            "input_type"     => $attribute->input_type,
                                            "attribute_type" => $attribute->attribute_type,
                                            "label"          => $attribute->label, 
                                            "name"           => $attribute->name, 
                                            "options"        => $attribute->options
                                        ]);
                                    }
                                }
                            } else if ($attribute->attribute_type == "sku") {
                                if ($attribute->is_mandatory and !in_array($attribute->name, [
                                    "price", "SellerSku"
                                ])) {
                                    array_push($sku_attributes, [
                                        "id"             => $attribute->id,
                                        "input_type"     => $attribute->input_type,
                                        "attribute_type" => $attribute->attribute_type,
                                        "label"          => $attribute->label, 
                                        "name"           => $attribute->name, 
                                        "options"        => $attribute->options
                                    ]);
                                }
                            }
                        }
                        return response()->json([
                            "success"   => true,
                            "data"      => [
                                "normal_attributes" => $normal_attributes,
                                "sku_attributes"    => $sku_attributes
                            ]
                        ]);
                    } else if (isset($response->data, $response->data->message)) {
                        return response()->json([
                            "success"   => true,
                            "message"   => $response->data->message,
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
            "message"   => __("translation.Failed to fetch product attributes")
        ]);
    }


    /**
     * 
     * @param array $params
     */
    private function getRequestObjectToGetProductCategoryAttributes($params=[]) 
    {
        try {
            $request = new LazopRequest('/category/attributes/get', 'GET');
            if (isset($params["primary_category_id"])) {
                $request->addApiParam('primary_category_id', $params["primary_category_id"]);
            }
            if (isset($params["language_code"])) {
                $request->addApiParam('language_code', $params["language_code"]);
            } else {
                $request->addApiParam('language_code', 'en_US');
            }
            return $request;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }


    /**
     * Get pickup address info from "Lazada".
     * 
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductBrandsFromLazada(Request $request)
    {
        try {
            if ($request->ajax()) {
                return response()->json([
                    "success"   => true,
                    "data"      => LazadaProductBrand::select("id", "brand_id", "name_en", "name", "global_identifier")->orderBy("name", "asc")->get()
                ]);
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "data"      => [],
            "message"   => __("translation.Failed to fetch product brands")
        ]);
    }


    /**
     * Delete lazada product.
     */
    public function delete(Request $request)
    {
        try {
            if ($request->ajax()) {
                $lazada_shop = Lazada::find($request->website_id);
                if (!isset($lazada_shop)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __('translation.Shop Not Found')
                    ]);
                }
                $lazada_product = LazadaProduct::find($request->id);
                if (!isset($lazada_product)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __('translation.Product Not Found')
                    ]);
                }
                if (!isset($lazada_product->product_code) and !empty($lazada_product->product_code)) {
                    return response()->json([
                        "success"   => false,
                        "message"   => __('translation.Product sku is not valid')
                    ]);
                }
                $access_token = $this->getAccessTokenForLazada($request->website_id);
                if (!empty($access_token)) { 
                    $client = $this->getLazadaClient();
                    $obj = $this->getRequestObjectForDeleteProductInLazada([
                        "seller_sku_list"   => [$lazada_product->product_code]
                    ]);
                    if (isset($client, $obj)) {                    
                        $response = json_decode($client->execute($obj, $access_token));
                        if (isset($response->data)) {
                            if (isset($response->data->code) and (int)$response->data->code !== 0) {
                                return response()->json([
                                    "success"   => true,
                                    "message"   => __("translation.Successfully removed lazada product.")
                                ]);
                            }
                        }
                        return response()->json([
                            "success"   => false,
                            "message"   => isset($response->data->message)?$response->data->message: __("translation.Failed to removed lazada product.")
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => true,
            "message"   => __("translation.Something went wrong.")
        ]);
    }


    private function getRequestObjectForDeleteProductInLazada($params) {
        try {
            $request = new LazopRequest('/product/remove', 'POST');
            if (isset($params["seller_sku_list"]) and sizeof($params["seller_sku_list"]) > 0 and sizeof($params["seller_sku_list"]) <= 50) {
                $request->addApiParam('seller_sku_list', json_encode($params["seller_sku_list"]));
            }
            return $request;
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return null;
    }
}
