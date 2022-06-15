<?php

namespace App\Http\Controllers;

use App\Actions\GoogleSheet\FetchSingleSheetTpkPacking;
use App\Http\Requests\SheetName\SyncNowRequest;
use App\Models\SheetName;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SheetNameSyncNowController extends Controller
{
    /**
     * Force sync single sheet
     *
     * @param  \App\Http\Requests\SheetName\SyncNowRequest  $request
     * @param  int  $sheetDocId
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function syncNow(SyncNowRequest $request, int $sheetDocId, int $id)
    {
        $sheetName = SheetName::query()
            ->where('sheet_doc_id', $sheetDocId)
            ->where('id', $id)
            ->firstOrFail();

        FetchSingleSheetTpkPacking::make()->handle($sheetName, 0);

        return $this->apiResponse(Response::HTTP_OK, __('translation.We are syncing the data'));
    }
}
