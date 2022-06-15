<?php

namespace App\Http\Controllers;

use App\Actions\UpdateTaxRateValueAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\TaxRateSetting\UpdateRequest;
use App\Models\TaxRateSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TaxRateSettingController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $sellerId = Auth::user()->id;

        $data = [
            'tax_rate_setting' => TaxRateSetting::where('seller_id', $sellerId)->first()
        ];

        return view('settings.tax-rate-settings', $data);
    }

    /**
     * Update the tax rate value.
     *
     * @param  \App\Http\Requests\Settings\TaxRateSetting\UpdateRequest  $request
     * @param  \App\Actions\UpdateTaxRateValueAction  $action
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, UpdateTaxRateValueAction $action)
    {
        try {
            $data = [
                'tax_name' => $request->tax_name,
                'tax_rate' => $request->tax_rate
            ];

            $action->handle($data);

            return $this->apiResponse(Response::HTTP_OK, __('translation.global.data_updated'));

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, __('translation.global.internal_server_error'));
        }
    }
}
