<?php

namespace App\Http\Controllers\SheetDataTpk;

use App\Actions\SheetDataTpk\LineWebhookHandle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LineBotController extends Controller
{
    /**
     * Handle webhook from line
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function webhook(Request $request)
    {
        LineWebhookHandle::make()->handle($request->all());

        return $this->apiResponse(Response::HTTP_OK, 'Success');
    }
}
