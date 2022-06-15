<div class="mt-4">
    <x-section.title>
        Payment Status
    </x-section.title>
    <div class="mb-3">
        <h5><strong id="payment_status_manual">
                @if($orderManagement->payment_status == 1)
                    PAID
                @else

                    @if($manualPaymentSum == 0 AND $orderManagement->payment_status == 0 AND empty($paymentDetailsOthers))
                        NOT PAID
                    @endif

                    @if($manualPaymentSum > 0 AND $manualPaymentSum < $orderManagement->in_total)
                        PARTIAL PAID
                    @endif

                    @if($manualPaymentSum == $orderManagement->in_total AND $orderManagement->order_status == 1)
                        PAID
                    @endif

                    @if(isset($paymentDetailsOthers) AND $paymentDetailsOthers->is_confirmed == 0)
                        NOT PAID
                    @endif

                    @if(isset($paymentDetailsOthers) AND $paymentDetailsOthers->is_confirmed == 1)
                        PAID
                    @endif

                    @if($orderManagement->in_total == $manualPaymentSum AND $orderManagement->in_total == $manualRefundedSum)
                        NOT PAID
                    @endif

                @endif
            </strong></h5>
    </div>

    <x-section.title>
        Total Payment
    </x-section.title>
    <div class="mb-3">
        Paid Amount: <strong id="total_manual_payment">฿
            @php $total_paid_amount = ''; @endphp
            @if($manualPaymentSum > 0)
                @php $total_paid_amount = $manualPaymentSum - $manualRefundedSum; @endphp
                {{$total_paid_amount}}
            @endif

            @if(isset($paymentDetailsOthers))
                @if($paymentDetailsOthers->is_confirmed == 1)
                    @php $total_paid_amount = $paymentDetailsOthers->amount; @endphp
                    {{$total_paid_amount}}
                @else
                    @php $total_paid_amount = 0; @endphp
                    {{$total_paid_amount}}
                @endif
            @endif

            @if(empty($paymentDetailsOthers) AND $manualPaymentSum == 0)
                @php $total_paid_amount = 0; @endphp
                {{$total_paid_amount}}
            @endif

        </strong><br>
        Pending Amount : <strong id="total_manual_pending">฿
            @php $total_pending_amount = ''; @endphp
            @if($manualPaymentSum > 0)
                @php $total_pending_amount = $orderManagement->in_total - ($manualPaymentSum - $manualRefundedSum); @endphp
                {{$total_pending_amount}}
            @endif

            @if(isset($paymentDetailsOthers))
                @if($paymentDetailsOthers->is_confirmed == 0)
                    @php $total_pending_amount = $orderManagement->in_total; @endphp
                    {{$total_pending_amount}} 
                @else
                    @php $total_pending_amount = 0; @endphp
                    {{$total_pending_amount}} 
                @endif
            @endif

            @if(empty($paymentDetailsOthers) AND $manualPaymentSum == 0)
                @php $total_pending_amount = $orderManagement->in_total; @endphp
                {{$total_pending_amount}}
            @endif

        </strong>
        <br>
        @if(!empty($manualRefundedSum) AND $manualRefundedSum > 0)
            Refunded Amount : <strong id="refunded_amount">฿
                @php $total_pending_amount = $manualRefundedSum; @endphp
                {{$total_pending_amount}}
            </strong>
        @endif
        <input type="hidden" id="total_manual_payment_input" value="{{$total_paid_amount}}">
        <input type="hidden" id="total_manual_pending_input" value="{{$total_pending_amount}}">

        <input type="hidden" id="unpaid_total" value="{{$orderManagement->in_total - $manualPaymentSum}}">
        <input type="hidden"
               value="@if(isset($paymentDetailsOthers)){{$paymentDetailsOthers->is_confirmed}}
               @endif
                   " id="payment_confirm_status_input">
        <input type="hidden"
               value="@if(isset($paymentDetailsOthers)){{$paymentDetailsOthers->id}}
               @endif" id="payment_id_input">
    </div>

    <div class="mt-2">
        <h6 class="mt-4"><strong>Manual Payment : </strong></h6>
            <table class="table tbl_border">
            <thead>
            <tr class="bg-blue-500 text-white">
                <th scope="col">{{ __('translation.ID') }}</th>
                <th scope="col">{{ __('translation.Date/Time') }}</th>
                <th scope="col">{{ __('translation.Details') }}</th>
                <th scope="col">{{ __('translation.Action') }}</th>
            </tr>
            </thead>
            <tbody>
            @if(count($paymentDetailsAllManual)>0)
                @if(isset($paymentDetailsAllManual))
                    @foreach($paymentDetailsAllManual as $rows)
                        <tr id="manual_payment_{{$rows->id}}" class="@if($rows->is_refund == 1) tr_color @endif">
                            <td>{{$rows->id}}</td>
                            <td>
                                Date: <br>
                                <strong>{{date('d-M-Y', strtotime($rows->payment_date))}}</strong><br>
                                Time: {{date('h:i', strtotime($rows->payment_time))}}
                            </td>
                            <td>
                                Amount : <br><strong>฿ {{$rows->amount}}</strong><br>
                                Method : <br><strong>{{$rows->payment_method}}</strong><br>
                                Status : <br><strong>@if($rows->is_confirmed == 1)
                                        Confirmed
                                    @elseif($rows->is_confirmed == 0)
                                        Not Comfirmed
                                    @else
                                        Refund
                                    @endif</strong><br>
                            </td>
                            <td>
                                <button type="button" data-id="{{$rows->id}}" class="modal-open btn-action--yellow" id="BtnUpdateManualPayment">
                                    <i class="fas fa-pencil-alt"></i>
                                </button><br>
                                <button type="button" data-id="{{$rows->id}}" class="btn btn-danger btn-sm" id="BtnDeleteManualPayment">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            @else
                <tr>
                    <td colspan="5" class="text-center">
                        No payment made yet
                    </td>
                </tr>
            @endif

            </tbody>
        </table>
    </div>
    <div id="make_payment_btn_wrapper" class="text-center">
        <a id="make_payment" class="h-8 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-600 outline-none focus:outline-none focus:border-green-600 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"  color="green">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
                <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path>
            </svg>
            <span class="ml-2">
                Make New Payment
            </span>
        </a>
    </div>
</div>

<div id="system_payments_wrapper mt-4" class="padding_tops">
    <div class="mt-4">
        <h6 class="mt-4"><strong>System Payment : </strong></h6>
        <table class="table mt-2 dt-responsive nowrap tbl_border">
            <thead>
            <tr class="bg-gray-200">
                <th scope="col">{{ __('translation.ID') }}</th>
                <th scope="col">{{ __('translation.Date/Time') }}</th>
                <th scope="col">{{ __('translation.Details') }}</th>
                <th scope="col">{{ __('translation.Action') }}</th>
            </tr>
            </thead>
            <tbody>
            @if(isset($paymentDetailsOthers))
                <tr>
                    <td>{{$paymentDetailsOthers->id}}</td>
                    <td>
                        Date : <br><strong>{{date('d-M-Y', strtotime($paymentDetailsOthers->payment_date))}}</strong><br>
                        Time :<strong>{{$paymentDetailsOthers->payment_time}}</strong>
                    </td>
                    <td>
                        Method : <br>
                        <strong>

                            @if($paymentDetailsOthers->payment_method == 'ktbcard')
                                Credit Card
                            @elseif($paymentDetailsOthers->payment_method == 'bbl_promptpay')
                                Prompt Pay
                            @else
                                {{$paymentDetailsOthers->payment_method}}
                            @endif

                        </strong><br>
                        Amount:<strong> ฿ {{$paymentDetailsOthers->amount}}</strong><br>
                        Status: <br><strong>
                            @if($paymentDetailsOthers->is_confirmed == 1)
                                Confirmed
                            @else
                                Unconfirmed
                            @endif
                        </strong>
                    </td>
                    <td>
                        @if($paymentDetailsOthers->payment_method == 'Bank Transfer')
                            <button class="btn btn-sm btn-success chnage_payment_status"  data-id="{{$paymentDetailsOthers->id}}">Details</button>
                        @endif

                        @if($paymentDetailsOthers->payment_method != 'Bank Transfer')
                            <button class="btn btn-sm btn-success" >PAID</button>
                        @endif


                    </td>
                </tr>

            @else
                <tr class="text-center">
                    <td colspan="5">No payments made yet</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>
<!-- end for payment status = 1 -->
