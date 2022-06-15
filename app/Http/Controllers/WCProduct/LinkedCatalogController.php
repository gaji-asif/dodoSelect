<?php

namespace App\Http\Controllers\WCProduct;

use App\Http\Controllers\Controller;
use App\Http\Requests\DatatableRequest;
use App\Http\Requests\WCProduct\LinkedCatalog\StoreRequest;
use App\Models\Product;
use App\Models\WooProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class LinkedCatalogController extends Controller
{
    /**
     * Link the wc_products to products
     *
     * @param \App\Http\Requests\WCProduct\LinkedCatalog\StoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        try {
            $woo_product_id = $request->woo_product_id;
            $product_id = $request->product_id;

            $wooProduct = WooProduct::where('id', $woo_product_id)->first();
            $wooProduct->dodo_product_id = $product_id;
            $wooProduct->is_linked = WooProduct::IS_LINKED_YES;
            $wooProduct->save();

            return $this->apiResponse(Response::HTTP_OK, ucfirst(__('translation.product_was_linked_successfully')));

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, ucfirst(__('translation.something_went_wrong')));
        }
    }

    /**
     * Handle server side datatable of linked catalog (products)
     *
     * @param  \App\Http\Requests\DatatableRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function dataTable(DatatableRequest $request)
    {
        $sellerId = Auth::user()->id;

        $orderColumn = $request->get('order')[0]['column'] ?? 0;
        $orderDir = $request->get('order')[0]['dir'] ?? 'asc';
        $search = $request->get('search')['value'] ?? '';

        $availableColumnOrder = [
            'id', 'product_name'
        ];

        $orderByColumn = $availableColumnOrder[$orderColumn] ?? 'id';

        $products = Product::query()
                    ->where('seller_id', $sellerId)
                    ->searchTable($search)
                    ->orderBy($orderByColumn, $orderDir);

        return DataTables::of($products)
                ->addColumn('image_with_id', function ($product) {
                    return '
                        <div class="grid grid-cols-1 gap-2">
                            <div>
                                <img src="'. $product->image_url .'" alt="'. $product->product_name .'" class="w-full h-auto" />
                            </div>
                            <div>
                                <span class="font-bold text-blue-500">
                                    ID : '. $product->id .'
                                </span>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('product_details', function ($product) {
                    return '
                        <div class="grid grid-cols-1 gap-2">
                            <div>
                                <span class="font-bold">
                                '. $product->product_name .'
                                </span>
                            </div>
                            <div>
                                <span class="text-blue-500">
                                    '. $product->product_code .'
                                </span>
                            </div>
                            <div>
                                <span class="text-lg">
                                    '. currency_symbol('THB') . number_format(floatval($product->price), 3) .'
                                </span>
                            </div>
                            <div>
                                <button type="button"
                                    class="btn-action--yellow btn-link-wc-product"
                                    data-product-id="'. $product->id .'"
                                    onClick="linkCatalogToWCProduct(this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-link" viewBox="0 0 16 16">
                                        <path d="M6.354 5.5H4a3 3 0 0 0 0 6h3a3 3 0 0 0 2.83-4H9c-.086 0-.17.01-.25.031A2 2 0 0 1 7 10.5H4a2 2 0 1 1 0-4h1.535c.218-.376.495-.714.82-1z"/>
                                        <path d="M9 5.5a3 3 0 0 0-2.83 4h1.098A2 2 0 0 1 9 6.5h3a2 2 0 1 1 0 4h-1.535a4.02 4.02 0 0 1-.82 1H12a3 3 0 1 0 0-6H9z"/>
                                    </svg>
                                    <span class="ml-2">
                                        '. strtoupper(__('translation.link_this_product')) .'
                                    </span>
                                </button>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns([ 'image_with_id', 'product_details' ])
                ->make(true);
    }
}
