<?php

namespace App\Http\Controllers\BuyerPage;

use App\Actions\OrderManagement\CalculateTotalAmountUpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\BuyerPage\ShippingMethod\UpdateRequest;
use App\Models\CustomerShippingMethod;
use App\Models\OrderManagement;
use App\Models\TaxRateSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShippingMethodController extends Controller
{
    /**
     * Update the shipping method according to the first step
     * on buyer page
     *
     * @param  \App\Http\Requests\BuyerPage\ShippingMethod\UpdateRequest  $request
     * @param  string  $orderId
     * @param  \App\Actions\OrderManagement\CalculateTotalAmountUpdateAction  $calculateTotalAmount
     * @return  \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $orderId, CalculateTotalAmountUpdateAction $calculateTotalAmount)
    {
        try {
            //$sellerId = Auth::user()->id;
            if(isset(Auth::user()->id)){
                $sellerId = Auth::user()->id;
            }
            else{
                $sellerId = 15;
            }

            $orderManagementTable = (new OrderManagement())->getTable();
            $customerShippingMethodTable = (new CustomerShippingMethod())->getTable();
            $taxRateSettingTable = (new TaxRateSetting())->getTable();

            DB::beginTransaction();

            $orderManagement = OrderManagement::where('order_id', $orderId)->first();
            $orderManagementId = $orderManagement->id;


            $taxRate = 0;
            if ($request->tax_enable == OrderManagement::TAX_ENABLE_YES) {
                $taxRateSetting = DB::table($taxRateSettingTable)->where('seller_id', $sellerId)->first();
                $taxRate = $taxRateSetting->tax_rate ?? 0;
            }

            $subTotal = $orderManagement->sub_total;
            $shippingCostTotal = 0;
            $discountTotal = $orderManagement->amount_discount_total; // Please use eloquent for orderManagement to get this attributes
            $totalAmount = $orderManagement->in_total;


            $prevSelectedShipping = DB::table($customerShippingMethodTable)
                                    ->where('order_id', $orderManagementId)
                                    ->where('is_selected', CustomerShippingMethod::IS_SELECTED_YES)
                                    ->first();

            $prevSelectedShippingDiscount = $prevSelectedShipping->discount_price ?? 0;
            $discountTotal -= $prevSelectedShippingDiscount ?? 0;


            DB::table($customerShippingMethodTable)
                ->where('order_id', $orderManagementId)
                ->update([
                    'is_selected' => CustomerShippingMethod::IS_SELECTED_NO
                ]);


            foreach ($request->shipping_method_id as $idx => $shippingMethodId) {
                $shippingMethodSelected = $request->shipping_method_selected[$idx] ?? 0;

                if ($shippingMethodSelected == CustomerShippingMethod::IS_SELECTED_YES) {
                    $customerShippingMethod = DB::table($customerShippingMethodTable)
                                                ->where('id', $shippingMethodId)
                                                ->first();

                    $shippingMethodPrice = $customerShippingMethod->price;
                    $shippingMethodDiscount = $customerShippingMethod->discount_price;

                    $shippingCostTotal += $shippingMethodPrice;
                    $discountTotal += $shippingMethodDiscount;

                    DB::table($customerShippingMethodTable)
                        ->where('id', $shippingMethodId)
                        ->update([
                            'is_selected' => CustomerShippingMethod::IS_SELECTED_YES
                        ]);
                }
            }


            $totalAmount = $calculateTotalAmount->handle($subTotal, $taxRate, $shippingCostTotal, $discountTotal);

            DB::table($orderManagementTable)
                ->where('id', $orderManagementId)
                ->update([
                    'company_name' => $request->company_name,
                    'tax_number' => $request->tax_number,
                    'company_phone_number' => $request->company_phone_number,
                    'company_contact_name' => $request->company_contact_name,
                    'company_address' => $request->company_address,
                    'company_province' => $request->company_province,
                    'company_district' => $request->company_district,
                    'company_sub_district' => $request->company_sub_district,
                    'company_postcode' => $request->company_postcode,
                    'sub_total' => $subTotal,
                    'shipping_cost' => $shippingCostTotal,
                    'tax_rate' => $taxRate,
                    'in_total' => $totalAmount,
                ]);


            DB::commit();

            return $this->apiResponse(Response::HTTP_OK, 'Data successfully updated.');

        } catch (\Throwable $th) {
            report($th);

            DB::rollBack();

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong.', [
                'backtrace' => $th->getMessage() . ' on line ' . $th->getLine()
            ]);
        }
    }
}
