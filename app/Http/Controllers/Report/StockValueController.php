<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\DatatableRequest;
use App\Models\Product;
use App\Models\ProductCost;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class StockValueController extends Controller
{
    /**
     * Shows stock values datatable
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('report.stock-value.index');
    }

    /**
     * Handle server side datatable
     *
     * @param  \App\Http\Requests\DatatableRequest  $request
     * @return mixed
     */
    public function datatable(DatatableRequest $request)
    {
        $sellerId = Auth::user()->id;

        $search = isset($request->get('search')['value'])
            ? $request->get('search')['value']
            : null;

        $orderColumnList = [
            'id', 'profit_margin', 'product_name', 'stock_value'
        ];

        $orderColumnIndex = isset($request->get('order')[0]['column'])
            ? $request->get('order')[0]['column']
            : 0;

        $orderColumnDir = isset($request->get('order')[0]['dir'])
            ? $request->get('order')[0]['dir']
            : 'desc';

        $orderColumnName = $orderColumnList[$orderColumnIndex] ?? 'id';

        $products = Product::query()
            ->stockValueReport()
            ->where('seller_id', $sellerId)
            ->searchTable($search)
            // ->with(['preferredProductCost' => function ($productCost) {
            //     $productCost->with('exchangeRate');
            // }])
            // ->leftJoin('product_main_stocks', 'product_main_stocks.product_id', '=', 'products.id')
            ->orderBy($orderColumnName, $orderColumnDir);

        return DataTables::of($products)
                ->addColumn('product_image', function ($product) {
                    return '
                        <img src="'. $product->image_url .'" class="w-20 sm:w-32 h-auto" />
                    ';
                })
                ->addColumn('details', function ($product) {
                    return '
                        <div class="grid grid-cols-1 gap-4 gap-x-8 py-4 md:grid-cols-2 lg:grid-cols-6">
                            <div class="md:col-span-2 lg:col-span-4">
                                <span class="block whitepace-nowrap text-gray-500">
                                    '. __('translation.Product Name') .'
                                </span>
                                <span class="font-bold">
                                    '. $product->product_name .'
                                </span>
                            </div>
                            <div class="md:col-span-2 lg:col-span-2">
                                <span class="block whitepace-nowrap text-gray-500">
                                    '. __('translation.SKU') .'
                                </span>
                                <span class="font-bold">
                                    '. $product->product_code .'
                                </span>
                            </div>
                            <div class="lg:col-span-2">
                                <span class="block whitepace-nowrap text-gray-500">
                                    '. __('translation.Qty') .'
                                </span>
                                <span class="font-bold">
                                    '. number_format($product->quantity) .'
                                </span>
                            </div>
                            <div class="lg:col-span-2">
                                <span class="block whitepace-nowrap text-gray-500">
                                    '. __('translation.Price') .'
                                </span>
                                <span class="font-bold">
                                    '. currency_symbol('THB') . currency_number($product->price, 3) .'
                                </span>
                            </div>
                            <div class="lg:col-span-2">
                                <span class="block whitepace-nowrap text-gray-500">
                                    '. __('translation.Cost Price') .'
                                </span>
                                <span class="font-bold">
                                    '. currency_symbol('THB') . currency_number($product->pc_cost_price, 3) .'
                                </span>
                            </div>
                            <div class="lg:col-span-2">
                                <span class="block whitepace-nowrap text-gray-500">
                                    '. __('translation.Stock Value') .'
                                </span>
                                <span class="font-bold">
                                    '. currency_number($product->stock_value, 3) .'
                                </span>
                            </div>
                            <div class="lg:col-span-2">
                                <span class="block whitepace-nowrap text-gray-500">
                                    '. __('translation.Stock Cost Value') .'
                                </span>
                                <span class="font-bold">
                                    '. currency_number($product->stock_cost_value, 3) .'
                                </span>
                            </div>
                            <div class="lg:col-span-2">
                                <span class="block whitepace-nowrap text-gray-500">
                                    '. __('translation.Profit Margin') .'
                                </span>
                                <span class="font-bold">
                                    '. currency_number($product->profit_margin, 2) .'%
                                </span>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('actions', function ($product) {
                    return '
                        <button class="btn-action--green"
                            data-id="'. $product->id .'"
                            onClick="showStockValue(this)">
                            <i class="bi bi-info-circle"></i>
                        </button>
                    ';
                })
                ->rawColumns(['product_image', 'details', 'actions'])
                ->make(true);
    }

    /**
     * Shows stock value details
     *
     * @param  int  $productId
     * @return \Illuminate\Http\Response
     */
    public function show(int $productId)
    {
        $sellerId = Auth::user()->id;

        $product = Product::query()
            ->where('seller_id', $sellerId)
            ->where('id', $productId)
            ->with('product_main_stock')
            ->with(['preferredProductCost' => function ($productCost) {
                $productCost->with('exchangeRate')
                    ->with('supplier');
            }])
            ->firstOrFail();

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'product' => $product
        ]);
    }

    /**
     * Get Summary of Stock Value
     *
     * @return \Illuminate\Http\Response
     */
    public function summary()
    {
        $sellerId = Auth::user()->id;

        $stockSummary = ProductCost::summaryStockValueBySeller($sellerId);

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'total_stock_value' => currency_number($stockSummary->stock_value_sum, 3),
            'total_stock_cost_value' => currency_number($stockSummary->stock_cost_value_sum, 3),
        ]);
    }
}
