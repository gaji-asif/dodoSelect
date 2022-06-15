<?php

namespace App\Http\Controllers\SheetDataTpk;

use App\Http\Controllers\Controller;
use App\Http\Requests\DatatableRequest;
use App\Http\Requests\SheetDataTpk\OrderAnalysisChartRequest;
use App\Models\SheetDataTpk;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class OrderAnalysisController extends Controller
{
    /**
     * Shows order analysis graph of tpk data page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $channels = SheetDataTpk::selectRaw('channel')->groupBy('channel')->get();

        return view('sheet-data-tpks.order-analysis', compact('channels'));
    }

    /**
     * Get shop code by id
     *
     * @param  string|null  $id
     * @return string
     */
    private function getShopCode(?string $id)
    {
        if ($id == '-1') {
            $id = null;
        }

        $shop = Shop::where('id', $id)->first();

        return $shop->code ?? null;
    }

    /**
     * Handle server-side datatable of sheet data tpk data
     *
     * @param  \App\Http\Requests\DatatableRequest  $request
     * @return mixed
     */
    public function datatable(DatatableRequest $request)
    {
        $sellerId = Auth::user()->id;

        $shopId = $request->get('shop_id', '-1');
        $dateRange = $request->get('date_range', null);
        $interval = $request->get('interval', null);
        $channel = $request->get('channel', null);

        $dateRangeExplode = explode(' to ', $dateRange);
        $dateRangeFrom = $dateRangeExplode[0] ?? date('Y-01-01');
        $dateRangeTo = $dateRangeExplode[1] ?? date('Y-12-31');

        $shopCode = $this->getShopCode($shopId);

        $dataTpks = SheetDataTpk::query()
            ->selectOrderAnalysis($interval)
            ->where('seller_id', $sellerId)
            ->whereBetween('date', [$dateRangeFrom, $dateRangeTo])
            ->byShop($shopCode)
            ->byChannel($channel)
            ->groupBy('str_date', 'shop')
            ->with('shopData')
            ->orderBy('date', 'asc')
            ->orderBy('shop', 'asc');

        return DataTables::of($dataTpks)
            ->addColumn('str_shop_name', function ($tpk) {
                $shopName = '<i class="text-red-400">N/A</i>';
                if ($tpk->shopData) {
                    $shopName = $tpk->shopData->name;
                }

                return $shopName;
            })
            ->addColumn('str_date', function ($tpk) use ($interval) {
                if ($interval == 'per_week') {
                    return Carbon::createFromDate($tpk->str_date)->format('d M Y')
                        . ' - '
                        . Carbon::createFromDate($tpk->str_date)->addDays(6)->format('d M Y');
                }

                if ($interval == 'per_month') {
                    return Carbon::createFromDate($tpk->str_date)->format('M Y');
                }

                if ($interval == 'per_year') {
                    return Carbon::createFromDate($tpk->str_date)->format('Y');
                }

                return Carbon::createFromDate($tpk->str_date)->format('d M Y');
            })
            ->addColumn('total_orders', function ($tpk) {
                return number_format($tpk->total_orders);
            })
            ->addColumn('total_amount', function ($tpk) {
                return number_format($tpk->total_amount, 2);
            })
            ->addIndexColumn()
            ->rawColumns(['str_shop_name', 'str_date', 'total_orders', 'total_amount'])
            ->make(true);
    }

    /**
     * Handle chart data
     *
     * @param  \App\Http\Requests\SheetDataTpk\OrderAnalysisChartRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function chart(OrderAnalysisChartRequest $request)
    {
        $sellerId = Auth::user()->id;

        $shopId = $request->get('shop_id', '-1');
        $dateRange = $request->get('date_range', null);
        $interval = $request->get('interval', null);
        $channel = $request->get('channel', null);

        $dateRangeExplode = explode(' to ', $dateRange);
        $dateRangeFrom = $dateRangeExplode[0] ?? date('Y-01-01');
        $dateRangeTo = $dateRangeExplode[1] ?? date('Y-12-31');

        $shopCode = $this->getShopCode($shopId);

        $dataTpks = SheetDataTpk::query()
            ->selectOrderAnalysis($interval)
            ->where('seller_id', $sellerId)
            ->whereBetween('date', [$dateRangeFrom, $dateRangeTo])
            ->byShop($shopCode)
            ->byChannel($channel)
            ->groupBy('str_date')
            ->orderBy('date', 'asc')
            ->get();

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'data' => $dataTpks
        ]);
    }
}
