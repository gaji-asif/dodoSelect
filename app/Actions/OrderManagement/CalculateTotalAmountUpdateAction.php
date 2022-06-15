<?php

namespace App\Actions\OrderManagement;

use Lorisleiva\Actions\Concerns\AsAction;

class CalculateTotalAmountUpdateAction
{
    use AsAction;

    /**
     * Calculate total amount / in_total of orders
     *
     * @param  double  $subTotal
     * @param  double  $taxRate
     * @param  double  $shippingCost
     * @param  double  $discountTotal
     * @return double
     */
    public function handle($subTotal, $taxRate, $shippingCost, $discountTotal)
    {
        return $subTotal + $shippingCost - $discountTotal + $taxRate;
    }
}
