<?php

namespace App\Http\Controllers;

use App\Models\InventoryProductsStockUpdateErrorLog;
use App\Traits\Inventory\PlatformTrait;
use App\Traits\Inventory\ShopTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InventoryQtySyncErrorLogController extends Controller
{
    use ShopTrait, PlatformTrait;

    private $shopee_shops, $lazada_shops, $woo_shops;

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = [];

        $totalErrorLog = InventoryProductsStockUpdateErrorLog::count();

        $data = [];

        $title = 'Error Log';
        return view('settings.inventory_qty_sync_error_log',compact('categories','totalErrorLog','data', 'title'));
    }


    /**
     * Handler the quantity-logs datatable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getErrorLogDetailsDataTable(Request $request)
    {
        $data = [];

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

        $fields = InventoryProductsStockUpdateErrorLog::with('dodoProduct')
            ->searchDatatable($search)
            // ->orderBy($orderColumnName, $orderDir)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->skip($start)
            ->get();

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $row = [];

                $platform = (isset($field->platform_name) and !empty($field->platform_name))?$field->platform_name:$this->getPlatformNoWisePlatformName($field->platform);
                $row[] = $field->id;
                $row[] = [
                    "dodo_product_id"   => $field->dodo_product_id,
                    "product_name"      => $field->product_name
                ];
                $row[] = number_format($field->quantity);
                $row[] = $field->message;
                $row[] = $this->getPlatformDisplayName($platform);
                $row[] = (isset($field->shop_name) and !empty($field->shop_name))?$field->shop_name:$this->getShopNameForDisplay($platform, $field->platform_sid);
                $row[] = $field->created_at->format('Y-m-d H:i');
                $row[] = [
                    "id"    => $field->id
                ];

                $data[] = $row;
            }
        }

        $count_total = InventoryProductsStockUpdateErrorLog::count();
        $count_total_search = InventoryProductsStockUpdateErrorLog::searchDatatable($search)->count();

        $response = [
            'draw' => $request->get('draw'),
            'recordsTotal' => $count_total,
            'recordsFiltered' => $count_total_search,
            'data' => $data
        ];

        return response()->json($response);
    }


    private function getShopDetails() {
        $data = $this->getShopDetailsFromDatabase(["shopee_key"=> "shop_id"]);
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


    /**
     * Delete specific error log.
     */
    public function delete(Request $request)
    {
        try {
            if ($request->ajax()) {
                $log = InventoryProductsStockUpdateErrorLog::find($request->id);
                if (isset($log)) {
                    $log->delete();
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => true,
            "message"   => __("translation.Successfully deleted error log.")
        ]);
    }


    /**
     * Delete all error log.
     */
    public function deleteAll(Request $request)
    {
        try {
            if ($request->ajax()) {
                InventoryProductsStockUpdateErrorLog::query()->delete();
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => true,
            "message"   => __("translation.Successfully deleted all error log.")
        ]);
    }
}
