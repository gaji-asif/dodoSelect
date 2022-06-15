<?php

namespace App\Actions;

use App\Models\TaxRateSetting;
use Illuminate\Support\Facades\Auth;

class UpdateTaxRateValueAction
{
    public function handle(array $data)
    {
        try {
            $sellerId = Auth::user()->id;

            $taxRateSetting = TaxRateSetting::where('seller_id', $sellerId)->first();
            if (!$taxRateSetting) {
                $taxRateSetting = new TaxRateSetting();
            }

            $taxRateSetting->seller_id = $sellerId;
            $taxRateSetting->tax_name = $data['tax_name'];
            $taxRateSetting->tax_rate = $data['tax_rate'];
            $taxRateSetting->save();

            return $taxRateSetting;

        } catch (\Throwable $th) {
            report($th);

            throw $th;
        }
    }
}