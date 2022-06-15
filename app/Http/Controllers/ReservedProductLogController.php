<?php

namespace App\Http\Controllers;

use App\Models\InventoryProductsReservedQuantityLog;
use App\Models\Product;
use App\Traits\Inventory\ShopTrait;
use Illuminate\Http\Request;

class ReservedProductLogController extends Controller
{
    use ShopTrait;

    private $shopee_shops, $lazada_shops, $woo_shops;

    /**
     * Show reserved_quantity logs table page.
     *
     * @param  int  $productId
     * @return \Illuminate\View\View
     */
    public function seeReservedQuantityLogDetails($productId)
    {
        $product = Product::findOrFail($productId);

        $quantityLogCount = InventoryProductsReservedQuantityLog::whereDodoProductId($productId)->count();

        $data = [
            'product' => $product,
            'quantityLogCount' => $quantityLogCount
        ];

        return view('seller.reserved-qunatity-log-details', $data);
    }


    /**
     * Handler the quantity-logs datatable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function seeDetailsDataTable(Request $request)
    {
        $data = [];

        $productId = $request->get('productId', 0);

        $this->getShopDetails();

        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $search = isset($request->get('search')['value'])
                ? $request->get('search')['value']
                : null;

        $orderColumnIndex = isset($request->get('order')[0]['column'])
                            ? $request->get('order')[0]['column']
                            : 2;
        $orderDir = isset($request->get('order')[0]['dir'])
                    ? $request->get('order')[0]['dir']
                    : 'desc';

        $availableColumnsOrder = [
            'id', 'id', 'check_in_out', 'quantity', 'created_at', 'seller_name'
        ];

        $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
            ? $availableColumnsOrder[$orderColumnIndex]
            : $availableColumnsOrder[6];

        $fields = InventoryProductsReservedQuantityLog::where('dodo_product_id', $productId)
            ->with('dodoProduct')
            ->searchDatatable($search)
            ->orderBy($orderColumnName, $orderDir)
            ->take($limit)
            ->skip($start)
            ->get();

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $row = [];

                $row[] = $field->id;
                $row[] = $this->getPlatformDisplayName($field->platform);
                $row[] = $field->order_id;
                $row[] = $this->getShopNameForDisplay($field->platform, $field->website_id);
                $row[] = number_format($field->quantity);

                $row[] = $field->status=="processed"?"Logistic":"Processing";
                $row[] = $field->created_at->format('Y-m-d H:i');

                $row[] = '';

                $data[] = $row;
            }
        }

        $count_total = InventoryProductsReservedQuantityLog::where('dodo_product_id', $productId)->count();
        $count_total_search = InventoryProductsReservedQuantityLog::where('dodo_product_id', $productId)->searchDatatable($search)->count();

        $response = [
            'draw' => $request->get('draw'),
            'recordsTotal' => $count_total,
            'recordsFiltered' => $count_total_search,
            'data' => $data
        ];

        return response()->json($response);
    }


    private function getShopDetails() {
        $data = $this->getShopDetailsFromDatabase();
        $this->shopee_shops = $data["shopee"];
        $this->lazada_shops = $data["lazada"];
        $this->woo_shops = $data["woo_commerce"];
    }


    private function getShopNameForDisplay($platform, $id)
    {
        if ($platform == "shopee") {
            return $this->shopee_shops[$id]??"";
        } else if ($platform == "woo_commerce") {
            return $this->woo_shops[$id]??"";
        } else if ($platform == "lazada") {
            return $this->lazada_shops[$id]??"";
        }
    }
}
