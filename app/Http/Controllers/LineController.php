<?php

namespace App\Http\Controllers;

use App\Events\LineEvent;
use App\Events\LineEventACSale;
use Illuminate\Http\Request;

class LineController {

    public function triggeredPayload(Request $request)
    {
        event(new LineEvent($request));

        return response()->json([
            "status" => "success"
        ], 200);
    }

    public function triggeredPayloadACSale(Request $request)
    {
        event(new LineEventACSale($request));

        return response()->json([
            "status" => "success"
        ], 200);
    }
}
