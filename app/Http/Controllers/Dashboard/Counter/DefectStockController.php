<?php

namespace App\Http\Controllers\Dashboard\Counter;

use App\Http\Controllers\Controller;
use App\Models\StockLog;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DefectStockController extends Controller
{
    public function __invoke()
    {
        $sellerId = Auth::user()->id;

        $cacheKey = 'dashboard.defect-stock-count-' . $sellerId;

        $defectStockCount = Cache::remember($cacheKey, 3600, function () use ($sellerId) {
            return StockLog::query()
                ->where('seller_id', $sellerId)
                ->where('is_defect', StockLog::IS_DEFECT_YES)
                ->where('deffect_status', StockLog::DEFECT_STATUS_OPEN)
                ->count();
        });

        return $this->apiResponse(Response::HTTP_OK, __('translation.Success'), [
            'defect_stock' => $defectStockCount
        ]);
    }
}
