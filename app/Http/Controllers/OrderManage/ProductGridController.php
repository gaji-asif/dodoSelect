<?php

namespace App\Http\Controllers\OrderManage;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderManagement\DatatableRequest;
use App\Models\Permission;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class ProductGridController extends Controller
{
    /**
     * Handle datatables server side of `product grid`
     * on Order Management create/edit page
     *
     * @param  \App\Http\Requests\OrderManagement\DatatableRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index(DatatableRequest $request)
    {
        $sellerId = Auth::user()->id;

        $categoryId = $request->get('categoryId', 0);

        $orderColumn = $request->get('order')[0]['column'] ?? 0;
        $orderDir = $request->get('order')[0]['dir'] ?? 'asc';
        $search = $request->get('search')['value'] ?? '';

        $availableColumnOrder = [
            'id', 'product_name'
        ];

        $orderByColumn = $availableColumnOrder[$orderColumn] ?? 'id';

        if (Auth::user()->role == 'dropshipper') {
            $userPermissions = Permission::dropshipperProductPermissions(Auth::user()->dropshipper_id);

            $products = Product::where('seller_id', $sellerId)
                ->whereIn('product_code', $userPermissions)
                ->with('getQuantity')
                ->category($categoryId)
                ->searchTable($search)
                ->orderBy($orderByColumn, $orderDir);
        } else {
            $products = Product::where('seller_id', $sellerId)
                ->with('getQuantity')
                ->category($categoryId)
                ->searchTable($search)
                ->orderBy($orderByColumn, $orderDir);
        }

        return DataTables::of($products)
                    ->addColumn('product_image', function($product) {
                        return '
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <img src="'. $product->image_url .'" class="w-16 md:w-11/12 h-auto" />
                                </div>
                                <div>
                                    <span class="whitespace-nowrap text-blue-500">
                                        ID : <strong>'. $product->id .'</strong>
                                    </span>
                                </div>
                            </div>
                        ';
                    })
                    ->addColumn('product_details', function($product) {
                        return '
                            <div>
                                <div class="mb-1">
                                    <strong>'. $product->product_name .'</strong>
                                </div>
                                <div class="mb-1">
                                    <strong class="text-blue-500">'. $product->product_code .'</strong>
                                </div>
                                <div class="mb-1">
                                    <strong class="text-lg">'. currency_symbol('THB') . number_format(floatval($product->price), 3) .'</strong>
                                </div>
                                <div class="mb-1">
                                    <div class="whitespace-nowrap">
                                        <label class="text-gray-700">Quantity :</label>
                                        <span class="text-gray-900">
                                            '. number_format($product->getQuantity->quantity) .'
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="whitespace-nowrap">
                                        <label class="text-gray-700">Pieces/Pack :</label>
                                        <span class="text-gray-900">
                                            '. number_format($product->pack) .'
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <button type="button" class="btn-action--green" data-code="'. $product->product_code .'" onClick="selectProductGrid(this)">
                                        <i class="fas fa-cart-plus"></i>
                                        <span class="ml-2">
                                            '. __('translation.Add to Cart') .'
                                        </span>
                                    </button>
                                </div>
                            </div>
                        ';
                    })
                    ->rawColumns(['product_image', 'product_details'])
                    ->make(true);
    }
}
