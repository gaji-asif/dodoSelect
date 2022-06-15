<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerPhoneController extends Controller
{
    /**
     * Get customer data by phone number.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $sellerId = Auth::user()->id;

        $phoneNumber = $request->get('phoneNumber');
        $customerType = $request->get('customerType');

        $customerData = Customer::where('contact_phone', $phoneNumber)
                            ->where('seller_id', $sellerId)
                            ->customerType($customerType)
                            ->first();

        abort_if(!$customerData, 404, 'Data not found');

        $responseData = [
            'customer_name' => $customerData->customer_name,
            'contact_phone' => $customerData->contact_phone
        ];

        return $this->apiResponse(200, 'Success', $responseData);
    }
}
