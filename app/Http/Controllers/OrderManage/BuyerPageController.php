<?php

namespace App\Http\Controllers\OrderManage;

use App\Custom\Ksherpay\KsherPay;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderManagement\BuyerPage\PlaceOrderRequest;
use App\Models\CustomerShippingMethod;
use App\Models\OrderManagement;
use App\Models\Payment;
use App\Models\TaxRateSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Redirect;

class BuyerPageController extends Controller
{
    /**
     * View the buyer page
     *
     * @param  string  $orderId
     * @return  \Illuminate\View\View
     */
    public function edit($orderId)
    {
        $orderManagement = OrderManagement::where('order_id', $orderId)
                            ->with('shop')
                            ->with('channels')
                            ->with('customer')
                            ->with(['order_management_details' => function($detail) {
                                $detail->with('product');
                            }])
                            ->with(['customer_shipping_methods' => function($shippingMethod) {
                                $shippingMethod->where('enable_status', CustomerShippingMethod::ENABLE_STATUS_YES)
                                    ->with(['shipping_cost' => function($shippingCost) {
                                        $shippingCost->with('shipper');
                                    }]);
                            }])
                            ->with(['payments' => function($payment) {
                                $payment->where('payment_method', Payment::PAYMENT_METHOD_BANK_TRANSFER)
                                    ->orderBy('created_at', 'desc')
                                    ->take(1);
                            }])
                            ->first();

                            //dd($orderManagement);

        $orderPaymentDeatails = Payment::where('order_id', $orderManagement->id)->first();

        abort_if(!$orderManagement, Response::HTTP_NOT_FOUND, 'Order not found.');

        $sellerId = $orderManagement->seller_id;

        $selectedShippingMethod = CustomerShippingMethod::where('order_id', $orderManagement->id)
                                    // ->where('is_selected', CustomerShippingMethod::IS_SELECTED_YES)
                                    ->with(['shipping_cost' => function($shippingCost) {
                                        $shippingCost->with('shipper');
                                    }])
                                    ->first();

        $data = [
            'orderManagement' => $orderManagement,
            'isSelectedShippingMethod' => CustomerShippingMethod::IS_SELECTED_YES,
            'paymentMethodBankTransfer' => OrderManagement::PAYMENT_METHOD_BANK_TRANSFER,
            'paymentMethodInstant' => OrderManagement::PAYMENT_METHOD_INSTANT,
            'orderStatusPending' => OrderManagement::ORDER_STATUS_PENDING,
            'orderStatusPendingPayment' => OrderManagement::ORDER_STATUS_PENDING_PAYMENT,
            'orderStatusProcessing' => OrderManagement::ORDER_STATUS_PROCESSING,
            'paymentStatusUnPaid' => OrderManagement::PAYMENT_STATUS_UNPAID,
            'paymentStatusPaid' => OrderManagement::PAYMENT_STATUS_PAID,
            'orderStatusPaymentUnconfirmed' => OrderManagement::ORDER_STATUS_PAYMENT_UNCONFIRMED,
            'orderStatusCancel' => OrderManagement::ORDER_STATUS_CANCEL,
            'statusForInfoAlert' => OrderManagement::getStatusForInfoAlert(),
            'selectedShippingMethod' => $selectedShippingMethod,
            'orderPaymentDeatails' => $orderPaymentDeatails,
            'taxEnableValues' => OrderManagement::getAllTaxEnableValues(),
            'taxEnableYes' => OrderManagement::TAX_ENABLE_YES,
            'taxRateSetting' => TaxRateSetting::where('seller_id', $sellerId)->first(),
            'manualPaymentSum' => OrderManagement::getManualPaymentSum($orderManagement->id),
            'paymentDetailsOthers' => Payment::where('order_id', $orderManagement->id)->first()
        ];

        return view('seller.order_management.buyer-page', $data);
    }

    public function private_key(){
        $privatekey=<<<EOD
    -----BEGIN RSA PRIVATE KEY-----
    MIICYgIBAAKBgQCPzwGZv5sCMwf8Sv+FXUqrULSEdeB846z2OCnPw+ynDTUqApRz
    0Goj1gYaK5Gu4vLxTH06PpL96sAB9C0pACBz3xewotdAwoHK0B86TaWk0bt4+jSL
    HMAvgLOF2DH5uAlDzYp8KtQAyhXOowds/20POw+Q3m2RgLCMXQ4OzElp8QIDAQAB
    AoGBAI4VecBdZhp7LwWfV+x9axvuRhyllmHuVOKERRNIwZWfYAqct+3hWi0D9c1/
    hJWlF2E/MG8Oig6kFIcZp5OwAvIHsEkJjryQSk4qERpuU99TG9u5ayGmFUPaC0x6
    fzgEw3+ANYOytWTfsxGbUL1SFoZ1yqKD/iKuBE2BXgM6fZbBAkUAv3jyTVA5R+kg
    B3eFSu+hywi87Q2zZ+myBHGBC4Zb3mhmKRoiBMGZS40y9JXNsmrx3IhynQDSiywJ
    7DyX+Bo7SJ90eykCPQDAReuuYuU/wqcqtnscRzVCW9aydquaDYUHOUXWsAGdghtK
    SJFJW717RLHO/3L230f2pl5TBfPG3hGYmYkCRA8O0e9mmbqgCNbNfXwRMGYpP8Jc
    y3kmlctnqcBgRqVNDIu69GXvW8DnT9SQW2bmpjKzwF+8itJLGlSrxz/JwFPLxntR
    Aj0AjT1PqaSAHtxQjDHMMbOlTf/EsQg3ekzgIbRStyhHp3qBrYmtICRCBqEptJM1
    0l+mr2r68yX2M2nBp0VxAkUAkT6IL2UAbBi5mTK2YgakqyWCcFsLg7fGtArKcNiF
    QssbrooyyUHq8GKQ/4IYQO6M80xTf6vY3r3Gxs8LkqoQirHwRN0=
    -----END RSA PRIVATE KEY-----
    EOD;

    return $privatekey;
    }


    public function orderStatus($order_id)
    {
         $orderManagement = OrderManagement::where('order_id',$order_id)->first();
         $appid='mch37567';
         $privatekey = $this->private_key();
         set_time_limit(0);

         $ksherPay = new KsherPay($appid, $privatekey);
         $order_query_request_param = array(
            'mch_order_no'=>$orderManagement->id,
            'appid'=>$appid
        );

        $gateway_pay_response = $ksherPay->gateway_order_query($order_query_request_param);
        $gateway_pay_array = json_decode($gateway_pay_response, true);

        if(isset($gateway_pay_array['code']) && $gateway_pay_array['code'] == 0 && $gateway_pay_array['data']['result'] == 'SUCCESS'){

        $orderManagement->payment_status = OrderManagement::PAYMENT_STATUS_PAID;
        $orderManagement->payment_channel_from_ksher = $gateway_pay_array['data']['channel'];
        $orderManagement->order_status = OrderManagement::ORDER_STATUS_PROCESSING;
        $orderManagement->payment_date  = $gateway_pay_array['data']['time_end'];
        $result = $orderManagement->save();

        if($result){
            $paymentDetails = new Payment();
            $paymentDetails->amount = $orderManagement->in_total;
            $paymentDetails->is_confirmed = 1;
            $paymentDetails->order_id = $orderManagement->id;
            $paymentDetails->payment_date = $gateway_pay_array['data']['time_end'];
            $paymentDetails->payment_time = $gateway_pay_array['data']['time_end'];
            $paymentDetails->payment_method = $gateway_pay_array['data']['channel'];
            $paymentDetails->save();
        }

        return Redirect::to('/orders/'.$order_id)->with('success', 'Your Order Updated Successfully');
        }
    }



    /**
     * Update order data from `buyer page`
     *
     * @param  \App\Http\Requests\OrderManagement\BuyerPage\PlaceOrderRequest  $request
     * @return  \Illuminate\Http\Response
     */
    public function update(PlaceOrderRequest $request, $orderId)
    {
        try {
            if ($request->payment_method == OrderManagement::PAYMENT_METHOD_BANK_TRANSFER) {
                $orderManagement = OrderManagement::where('order_id', $orderId)->first();

                $orderManagement->payment_method = OrderManagement::PAYMENT_METHOD_BANK_TRANSFER;
                $orderManagement->order_status = OrderManagement::ORDER_STATUS_PENDING_PAYMENT;
                $orderManagement->save();

                return $this->apiResponse(Response::HTTP_OK, 'Data successfully updated.');
            }


            if ($request->payment_method == OrderManagement::PAYMENT_METHOD_INSTANT) {
                $userAgent = new Agent();

                $orderManagement = OrderManagement::where('order_id', $orderId)
                                    ->with('shop')
                                    ->with('customer')
                                    ->first();

                $appid='mch37567';
                $privatekey = $this->private_key();
                $ksherPay = new KsherPay($appid, $privatekey);

                $orderID = $orderManagement->id;
                $productName = $orderManagement->shop->name . ' Order';
                $totalFee = round($request->in_total_amount, 2) * 100;

                $device = 'PC';
                if ($userAgent->isMobile()) {
                    $device = 'h5';
                }

                $customer_name = (!empty($orderManagement->customer->customer_name)) ? $orderManagement->customer->customer_name : '';

                $paymentData = [
                    'mch_order_no' => $orderID,
                    'total_fee' => $totalFee,
                    'fee_type' => 'THB',
                    'lang' => 'th',
                    'channel_list' => 'bbl_promptpay,truemoney,airpay,linepay,ktbcard',
                    'mch_code' => '#'.$orderID.' ('.$customer_name.')',
                    'mch_redirect_url' => route('order-status', [ 'order_id' => $orderId ]),
                    'mch_redirect_url_fail' => route('order-management.public-url', [ 'order_id' => $orderId, 'status' => 'failed' ]),
                    'mch_notify_url' => route('payment-order-notify', [ 'order_id' => $orderId ]),
                    'product_name' => $productName,
                    'refer_url' => config('app.url'),
                    'device' => $device,
                    'logo' => $orderManagement->shop->logo_url,
                    'time_stamp' => date('YmdHis')
                ];

                $paymentResponse = json_decode($ksherPay->gateway_pay($paymentData), true);
                $paymentUrl = $paymentResponse['data']['pay_content'] ?? '';

                if (!empty($paymentUrl)) {
                    $orderManagement = OrderManagement::where('order_id', $orderId)->first();

                    $orderManagement->payment_url = $paymentUrl;
                    $orderManagement->payment_method = OrderManagement::PAYMENT_METHOD_INSTANT;
                    $orderManagement->order_status = OrderManagement::ORDER_STATUS_PENDING_PAYMENT;
                    $orderManagement->place_order_time = date('Y-m-d H:i:s');
                    $orderManagement->sign = $paymentResponse['sign'];
                    $orderManagement->save();

                    return $this->apiResponse(Response::HTTP_OK, __('translation.The Page will be redirecting. Please wait....'), [
                        'payment_url' => $paymentUrl
                    ]);
                }

                return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Payment failed');
            }

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong.' . $th->getMessage() . ' on ' . $th->getLine());
        }
    }

    public function changePaymentMethod(Request $request){
       try{
            if (!empty($request->order_Id)) {
                $orderManagement = OrderManagement::where('id', $request->order_Id)->first();

                $orderManagement->payment_method = NULL;
                $orderManagement->order_status = OrderManagement::ORDER_STATUS_PENDING;
                $orderManagement->save();

                return $this->apiResponse(Response::HTTP_OK, 'Payment Method Changing');
            }
        }
        catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong.' . $th->getMessage() . ' on ' . $th->getLine());
        }

    }
    public function paymentOrderNotify($order_id){
        echo $order_id;
    }
}
