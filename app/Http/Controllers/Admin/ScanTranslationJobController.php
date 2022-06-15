<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class ScanTranslationJobController extends Controller
{
    /**
     * Get status of scan translation job
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->apiResponse(Response::HTTP_OK, 'Success', [
            'isProcessing' => Cache::get('scantranslationwordjob_job_status', false)
        ]);
    }
}
