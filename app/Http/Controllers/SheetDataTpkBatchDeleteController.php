<?php

namespace App\Http\Controllers;

use App\Http\Requests\SheetDataTpk\BatchDeleteRequest;
use App\Models\SheetDataTpk;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SheetDataTpkBatchDeleteController extends Controller
{
    /**
     * Batch delete of sheet data tpk data
     *
     * @param  \App\Http\Requests\SheetDataTpk\BatchDeleteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function batchDelete(BatchDeleteRequest $request)
    {
        $requestData = $request->validated();
        $ids = $requestData['ids'];

        $sellerId = Auth::user()->id;

        SheetDataTpk::query()
            ->where('seller_id', $sellerId)
            ->whereIn('id', $ids)
            ->delete();

        return $this->apiResponse(Response::HTTP_OK, __('translation.Data has been deleted'));
    }
}
