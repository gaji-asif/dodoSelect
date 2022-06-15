<?php

namespace App\Http\Controllers;

use App\Actions\UpdateCompanyInfoAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\CompanyInfoSetting\UpdateRequest;
use App\Models\TaxRateSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CompanyInfoSettingController extends Controller
{
    /**
     * Show form to update company info data
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $sellerId = Auth::user()->id;

        $data = [
            'tax_rate_setting' => TaxRateSetting::where('seller_id', $sellerId)->first()
        ];

        return view('settings.company-info-settings', $data);
    }

    /**
     * Update the company info data
     *
     * @param  \App\Http\Requests\Settings\CompanyInfoSetting\UpdateRequest  $request
     * @param  \App\Actions\UpdateCompanyInfoAction  $action
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, UpdateCompanyInfoAction $action)
    {
        try {
            $companyData = [
                'tax_number' => $request->tax_number,
                'company_name' => $request->company_name,
                'company_logo' => $request->file('company_logo'),
                'company_phone' => $request->company_phone,
                'company_contact_person' => $request->company_contact_person,
                'company_address' => $request->company_address,
                'company_province' => $request->company_province,
                'company_district' => $request->company_district,
                'company_sub_district' => $request->company_sub_district,
                'company_postcode' => $request->company_postcode,
            ];

            $action->handle($companyData);

            return $this->apiResponse(Response::HTTP_OK, 'Data successfully updated.');

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong.');
        }
    }
}
