<?php

namespace App\Actions;

use App\Models\TaxRateSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UpdateCompanyInfoAction
{
    public function handle(array $data)
    {
        try {
            $sellerId = Auth::user()->id;

            $taxRateSetting = TaxRateSetting::where('seller_id', $sellerId)->first();
            if (!$taxRateSetting) {
                $taxRateSetting = new TaxRateSetting();
            }

            $companyLogo = $taxRateSetting->company_logo ?? null;
            if (!empty($data['company_logo'])) {
                $companyLogo = Storage::disk('public')->put('company-logo', $data['company_logo']);
            }

            $taxRateSetting->seller_id = $sellerId;
            $taxRateSetting->tax_number = $data['tax_number'];
            $taxRateSetting->company_name = $data['company_name'];
            $taxRateSetting->company_logo = $companyLogo;
            $taxRateSetting->company_phone = $data['company_phone'];
            $taxRateSetting->company_contact_person = $data['company_contact_person'];
            $taxRateSetting->company_address = $data['company_address'];
            $taxRateSetting->company_province = $data['company_province'];
            $taxRateSetting->company_district = $data['company_district'];
            $taxRateSetting->company_sub_district = $data['company_sub_district'];
            $taxRateSetting->company_postcode = $data['company_postcode'];
            $taxRateSetting->save();

            return $taxRateSetting;

        } catch (\Throwable $th) {
            report($th);

            throw $th;
        }
    }
}