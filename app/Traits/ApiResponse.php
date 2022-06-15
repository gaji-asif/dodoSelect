<?php

namespace App\Traits;

trait ApiResponse
{
    /**
     * Response api format
     *
     * @param  int  $statusCode
     * @param  int  $message
     * @param  mixed|null  $data
     * @return \Illuminate\Http\Response
     */
    public function apiResponse($statusCode, $message, $data = null)
    {
        $responseData = [
            'message' => $message,
            'data' => $data
        ];

        return response()->json($responseData, $statusCode);
    }
}