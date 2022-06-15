<?php

namespace App\Http\Controllers\Lazada;

use App\Http\Controllers\Controller;
use App\Http\Requests\DatatableRequest;
use App\Http\Requests\Lazada\LinkedCatalog\StoreRequest;
use App\Models\LazadaProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class LinkedCatalogController extends Controller
{
    /**
     * Link product catalog to shopee product
     *
     * @param  \App\Http\Requests\Lazada\LinkedCatalog\StoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        try {
            $lazada_product_id = $request->lazada_product_id;
            $product_id = $request->product_id;

            $lazadaProduct = LazadaProduct::where('id', $lazada_product_id)->first();
            $lazadaProduct->dodo_product_id = $product_id;
            $lazadaProduct->is_linked = LazadaProduct::IS_LINKED_YES;
            $lazadaProduct->save();

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
                                    class="btn-action--yellow btn-link-lazada-product"
                                    data-product-id="'. $product->id .'"
                                    onClick="linkCatalogToLazadaProduct(this)">
                                    <i class="bi bi-link"></i>
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
