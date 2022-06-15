<x-app-layout>
    @section('title')
        {{ __('translation.Edit Order') . ' #' . $orderManagement->id }}
    @endsection

    @push('top_css')
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
        <link rel="stylesheet" href="{{ asset('css/typeaheadjs.css') }}">
        <link rel="stylesheet" href="{{ asset('css/custom_css.css') }}">
    @endpush

    @if($orderManagement->order_status == 3 || $orderManagement->order_status == 4 || $orderManagement->order_status == 5)
        <div class="col-span-12">
            <div class="locked_editing">
                <div class="alert-danger custom_padd" role="alert">
                    <strong>This order is locked for editing.</strong>
                </div>
            </div>
        </div>
    @endif

    <div class="col-span-12 card btn_card">
        <div class="col-lg-12 tabs">
            <button type="button" class="btn btn-outline-info mr-2 mb-2" id="orderDetailsBtn">Order Details</button>
            <button type="button" class="btn btn-outline-warning mr-2 mb-2" id="paymentDetailsBtn">Payment Details</button>
            <button type="button" class="btn btn-outline-primary mr-2 mb-2" id="shipmentDetailsBtn">Shipment Details</button>
            <button type="button" class="btn btn-outline-success mb-2" id="customShipmentDetailsBtn"> {{ __('translation.Custom Shipment') }}</button>
        </div>
    </div>

    <div class="col-span-12 card btn_card" id="payment_details_wrapper">
        <x-card.header>
            <x-card.back-button href="{{ route('order_management.index') }}" id="left_pad" />
            <x-card.title>
                Payment Details #{{$orderManagement->id}}
            </x-card.title>
        </x-card.header>
        <div class="col-lg-12 tabs" id="full_payments_wrapper">
            @include('seller.order_management.payment_table')
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="make_manual_payment_modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{route('createShipment')}}" id="make_new_manual_payment" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <strong>Make Payment</strong>
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <x-section.title>
                                Payment Summary
                            </x-section.title>
                            <div class="mb-3">
                                Paid Amount: <strong id="total_paid_in_modal"></strong><br>
                                Pending Amount : <strong id="total_pending_in_modal"></strong>
                            </div>
                            <x-section.title>
                                Make a New Payment
                            </x-section.title>
                            <div class="form-group mb-5">
                                <input type="radio" id="payment" name="is_refund" value="0" checked class="">
                                <label for="payment" class="mr-5">Payment</label>

                                <input type="radio" id="refund" name="is_refund" value="1" class="">
                                <label for="refund">Refund</label>
                            </div>
                            <div class="form-group">

                                <input type="hidden" name="pending_total_input" id="pending_total_input" />
                                <input type="hidden" id="generated_order_id" value="{{$orderManagement->order_id}}" />
                            </div>
                            <div class="form-group">
                                <label for="email">
                                    <strong>Payment Amount</strong>
                                </label>
                                <x-input type="text" name="payment_amount" id="payment_amount" placeholder="Payment Amount" />
                            </div>

                            <div class="form-group">
                                <label for="email">
                                    <strong>Method</strong>
                                </label>
                                <x-input type="text" name="payment_method" id="payment_method" placeholder="Method" />
                            </div>

                            <div class="form-group">
                                <label for="email">
                                    <strong>Select Status</strong>
                                </label>
                                <select class="form-control" id="is_confirmed" name="is_confirmed">
                                    <option value="1">Confirmed</option>
                                    <option value="0">Unconfirmed</option>
                                </select>
                            </div>
                            <input type="hidden" id="order_id_confirm_payment" name="order_id_confirm_payment">
                            <div class="mt-4 text-center">
                                <input class="btn btn-success" style="margin: 0 auto; padding: 5px 10px;" type="button" onclick="makeNewPayment()" value="Make Payment" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="make_manual_payment_edit_modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{route('createShipment')}}" id="create_shipment" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <strong>Edit Payment</strong>
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <x-section.title>
                                Payment Summary
                            </x-section.title>
                            <div class="mb-3">
                                Paid Amount: <strong id="edit_total_paid_in_modal"></strong><br>
                                Pending Amount : <strong id="edit_total_pending_in_modal"></strong>
                            </div>
                            <div class="form-group mb-5">
                                <input type="radio" id="edit_payment" name="edit_is_refund" value="0" class="">
                                <label for="edit_payment" class="mr-5">Payment</label>

                                <input type="radio" id="edit_refund" name="edit_is_refund" value="1" class="">
                                <label for="edit_refund">Refund</label>
                            </div>
                            <div class="form-group">
                                <label for="email">
                                    <strong>Payment Amount</strong>
                                </label>
                                <x-input type="text" name="edit_payment_amount" id="edit_payment_amount" />
                            </div>

                            <div class="form-group">
                                <label for="email">
                                    <strong>Method</strong>
                                </label>
                                <x-input type="text" name="edit_payment_method" id="edit_payment_method" />
                            </div>

                            <div class="form-group">
                                <label for="email">
                                    <strong>Select Status</strong>
                                </label>
                                <select class="form-control" id="edit_is_confirmed" name="edit_is_confirmed">
                                    <option value="1">Confirmed</option>
                                    <option value="0">Unconfirmed</option>
                                </select>
                            </div>
                            <x-input type="hidden" name="payment_id_edit" id="payment_id_edit" />
                            <div class="mt-4 text-center">
                                <input class="btn btn-success" style="margin: 0 auto; padding: 5px 10px;" type="button" onclick="updateManualPayment()" value="Update Payment" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="chnage_payment_status_modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{route('createShipment')}}" id="chnage_payment_status_form" enctype="multipart/form-data">
                        @csrf

                        <div class="modal-header">
                            <h5 class="modal-title">
                                <strong>Change Status</strong>
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="email">
                                    <strong>Status</strong>
                                </label>
                                <select class="form-control" id="bank_is_confirmed" name="bank_is_confirmed">
                                    <option value="1" @if(isset($paymentDetailsOthers)) @if($paymentDetailsOthers->is_confirmed == 1) selected @endif @endif>
                                        Confirmed
                                    </option>
                                    <option value="0" @if(isset($paymentDetailsOthers)) @if($paymentDetailsOthers->is_confirmed == 0) selected @endif @endif>
                                        Unconfirmed
                                    </option>
                                </select>
                            </div>

                            @if(isset($paymentDetailsOthers->payment_slip))
                                <div class="form-group">
                                    <label for="slip">
                                        <strong>Payment Slip</strong>
                                    </label>
                                   <img class="img_class mt-2" src="<?php echo asset('storage/').'/'.$paymentDetailsOthers->payment_slip;?>" class="img-responsive">
                                </div>
                            @endif

                            @if(isset($paymentDetailsOthers))
                                @if($paymentDetailsOthers->is_confirmed == 0)
                                    <div class="mt-4 text-center">
                                        <input class="btn btn-success" id="change_payment_status_btn" style="margin: 0 auto; padding: 5px 10px;" type="button" onclick="change_payment_status()" value="Change Status" />
                                    </div>
                                @endif
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="shipment_details_wrapper" class="card btn_card col-span-12"></div>
    <div id="custom_shipment_details_wrapper" class="card btn_card col-span-12"></div>
    <div class="col-span-12 custom_to_padding" id="order_details_wrapper">
        <x-card.card-default>
            <x-card.header>
                <x-card.back-button href="{{ route('order_management.index') }}" />
                <x-card.title>
                    <span class="text_left_asif">{{ __('translation.Edit Order') . ' #' . $orderManagement->id }}</span>
                    <span class="text_left_asif mt__20 ml-3">
                        <x-select name="take_action" id="__order_statusEditOrder">
                            <option value="">Select Option</option>
                            @if($orderManagement->order_status == 1)
                                <option value="0">Cancel Order</option>
                                <option value="1">Pay Order Link</option>
                                <option value="2">Mark as Processing</option>
                            @elseif($orderManagement->order_status == 2)
                                <option value="0">Cancel Order</option>
                            @elseif($orderManagement->order_status == 7)
                                <option value="3">Change Payment Method</option>
                                <option value="4">Mark as Processing</option>
                                <option value="0">Cancel Order</option>
                            @elseif($orderManagement->order_status == 8)
                                <option value="5">Confirm Payment</option>
                                <option value="2">Mark as Processing</option>
                                <option value="0">Cancel Order</option>
                            @endif
                         </x-select>
                    </span>
                </x-card.title>
            </x-card.header>
            <x-card.body>

                <x-alert-success class="mb-6 alert hidden" id="__alertSuccess">
                    <div id="__alertSuccessContent"></div>
                </x-alert-success>

                <x-alert-danger class="mb-6 alert hidden" id="__alertDanger">
                    <div id="__alertDangerContent"></div>
                </x-alert-danger>

                <div class="mb-5">
                    <div class="flex flex-row items-center justify-center lg:justify-end gap-2">
                        <x-button-outline type="button" color="green" data-url="{{ route('order_manage.quotation.pdf', [ 'order_id' => $orderManagement->id ]) }}" class="btn-print-pdf">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-download" viewBox="0 0 16 16">
                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                            </svg>
                            <span class="ml-2">
                                Quotation
                            </span>
                        </x-button-outline>
                        @if ($orderManagement->tax_enable == $taxEnableYes)
                            <x-button-outline type="button" color="green" data-url="{{ route('tax-invoice.pdf-invoice', [ 'order_id' => $orderManagement->id ]) }}" class="btn-print-pdf">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-download" viewBox="0 0 16 16">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                </svg>
                                <span class="ml-2">
                                    Tax Invoice
                                </span>
                            </x-button-outline>
                        @else
                            <x-button-outline type="button" color="green" data-url="{{ route('order_manage.invoice.pdf', [ 'order_id' => $orderManagement->id ]) }}" class="btn-print-pdf">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-download" viewBox="0 0 16 16">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                </svg>
                                <span class="ml-2">
                                    Invoice
                                </span>
                            </x-button-outline>
                        @endif
                    </div>
                </div>

                <form action="#" method="post" id="__formEditOrder" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="id" id="id" value="{{ $orderManagement->id }}">
                    <input type="hidden" id="orders_order_status" value="{{ $orderManagement->order_status }}">
                    <input type="hidden" name="customer_type" value="{{ $customerType }}">

                    <x-section.section>
                        <x-section.title>
                            {{ __('translation.Order Info') }}
                        </x-section.title>
                        <x-section.body>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-4 sm:gap-x-8">
                                <div class="sm:col-span-2 lg:col-span-3">
                                    <x-label for="__shop_idEditOrder">
                                        {{ __('translation.Shop') }} <x-form.required-mark />
                                    </x-label>
                                    @if($orderManagement->order_status == 6 || $orderManagement->customer_type == 1)
                                        <x-select name="shop_id" id="__shop_idEditOrder" disabled>
                                            <option value="{{ $orderManagement->shop_id }}" selected>
                                                {{ $orderManagement->shop->name }}
                                            </option>
                                        </x-select>
                                    @else
                                        <x-select name="shop_id" id="__shop_idEditOrder">
                                            <option value="{{ $orderManagement->shop_id }}" selected>
                                                {{ $orderManagement->shop->name }}
                                            </option>
                                        </x-select>
                                    @endif
                                </div>
                                <div class="lg:col-span-2">
                                    <x-label for="__order_statusEditOrder">
                                        {{ __('translation.Order Status') }}
                                    </x-label>
                                    <strong class="mt-2 pt-2">{{$getOrderStatus}}</strong>
                                </div>

                                <div class="lg:col-span-2">
                                    <x-label for="__order_statusEditOrder">
                                        Payment Status
                                    </x-label>
                                    <strong class="mt-2 pt-2">
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
                                    </strong>
                                </div>

                                <div class="sm:col-span-2 lg:col-span-3">
                                    <x-label for="order_status">
                                        {{ __('translation.Public/Buyer URL') }}
                                    </x-label>
                                    <div>
                                        <x-form.textarea class="focus:outline-none bg-gray-50" readonly>
                                            {{ route('order-management.public-url', [ 'order_id' => $orderManagement->order_id ]) }}
                                        </x-form.textarea>
                                        <div id="public_url_actions_wrapper">
                                            <x-button-sm type="button" color="blue" id="__btnCopyBuyerLink" title="Copy Public URL" data-clipboard-text="{{ route('order-management.public-url', [ 'order_id' => $orderManagement->order_id ]) }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                <span class="ml-2">
                                                    Copy URL
                                                </span>
                                            </x-button-sm>
                                            <x-button-link-sm href="{{ route('order-management.public-url', [ 'order_id' => $orderManagement->order_id ]) }}" target="_blank" color="green" id="open_url_wrapper">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                                                    <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                                                </svg>
                                                <span class="ml-2">
                                                    Open URL
                                                </span>
                                            </x-button-link-sm>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-section.body>
                    </x-section.section>

                    <x-section.section>
                        <x-section.title>
                            {{ __('translation.Channel Info') }}
                        </x-section.title>
                        <x-section.body>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-x-8">
                                <div>
                                    <x-label for="channel_id">
                                        {{ __('translation.Channel Name') }} <x-form.required-mark />
                                    </x-label>
                                    <div class="w-full mt-2 grid grid-cols-6 gap-4">
                                        @if($orderManagement->customer_type == '0')
                                            @foreach ($channels as $idx => $channel)
                                            @if ($orderManagement->channel_id == $channel->id)
                                                <label for="channel_{{ $channel->id }}" class="channel-item block w-10 h-10 p-1 rounded-md border border-solid border-blue-500 hover:border-blue-500 cursor-pointer transition duration-300" data-name="{{ $channel->name }}">
                                                    <input type="radio" name="channel_id" id="channel_{{ $channel->id }}" value="{{ $channel->id }}" class="hidden" checked>
                                                    <div class="w-full h-full rounded-md bg-no-repeat bg-cover" style="background-image: url('{{ $channel->image_url }}')"></div>
                                                </label>
                                            @else
                                                <label for="channel_{{ $channel->id }}" class="channel-item block w-10 h-10 p-1 rounded-md border border-solid border-gray-300 hover:border-blue-500 cursor-pointer transition duration-300" data-name="{{ $channel->name }}">
                                                    <input type="radio" name="channel_id" id="channel_{{ $channel->id }}" value="{{ $channel->id }}" class="hidden">
                                                    <div class="w-full h-full rounded-md bg-no-repeat bg-cover" style="background-image: url('{{ $channel->image_url }}')"></div>
                                                </label>
                                            @endif
                                        @endforeach
                                        @else
                                            <label for="channel_{{ $channels->id }}" class="block w-10 h-10 p-1 rounded-md border border-solid border-blue-500 hover:border-blue-500 cursor-pointer transition duration-300" data-name="{{ $channels->name }}">
                                                <input type="radio" name="channel_id" id="channel_{{ $channels->id }}" value="{{ $channels->id }}" class="hidden" checked>
                                                <div class="w-full h-full rounded-md bg-no-repeat bg-cover" style="background-image: url('{{ $channels->image_url }}')"></div>
                                            </label>
                                        @endif
                                    </div>
                                    <div class="mt-3">
                                        <span class="text-gray-500">Selected Channel : </span>
                                        <span class="font-bold text-blue-500" id="__selectedChannelOutput">
                                            @if($orderManagement->customer_type == '0')
                                                {{ $orderManagement->channels->name ?? '' }}
                                            @else
                                                {{ $channels->name }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <x-label for="contact_name">
                                        {{ __('translation.Channel ID') }} <x-form.required-mark />
                                    </x-label>
                                    @if($orderManagement->order_status == 6 || $orderManagement->customer_type == 1)
                                        <x-input type="text" name="contact_name" id="contact_name" value="{{ $orderManagement->contact_name }}" class="bg-gray-200" readonly/>
                                    @else
                                        <x-input type="text" name="contact_name" id="contact_name" value="{{ $orderManagement->contact_name }}"/>
                                    @endif
                                </div>
                            </div>
                        </x-section.body>
                    </x-section.section>

                    <x-section.section>
                        <x-section.title>
                            {{ __('translation.Customer Info') }}
                            <small class="ml-2 text-yellow-500">
                                {{ __('translation.Search by phone number first') }}
                            </small>
                        </x-section.title>
                        <x-section.body>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-x-8">
                                <div>
                                    <x-label for="search_contact_phone">
                                        {{ __('translation.Search Phone Number') }} <x-form.required-mark />
                                    </x-label>
                                    <div class="flex flex-row items-center justify-between">
                                        @if($orderManagement->order_status == 6 || $orderManagement->customer_type == 1)
                                            <x-input type="text" id="search_contact_phone" class="rounded-tr-none rounded-br-none" value="{{ $orderManagement->customer->contact_phone }}" class="bg-gray-200" disabled/>
                                            <x-button type="button" color="blue" class="rounded-tl-none rounded-bl-none relative">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                                                </svg>
                                            </x-button>
                                        @else
                                            <x-input type="text" id="search_contact_phone" class="rounded-tr-none rounded-br-none" value="{{ $orderManagement->customer->contact_phone }}" />
                                            <x-button type="button" color="blue" class="rounded-tl-none rounded-bl-none relative" id="__btnContactPhone">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                                                </svg>
                                            </x-button>
                                        @endif
                                    </div>
                                    <div class="mt-2 hidden" id="__fetchCustomerResultMessage">
                                        <span class="font-bold"></span>
                                    </div>
                                </div>
                                <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-x-8">
                                    <div id="__customerNameWrapper">
                                        <x-label for="__customer_nameEditOrder">
                                            {{ __('translation.Customer Name') }} <x-form.required-mark />
                                        </x-label>
                                        <x-input type="text" name="customer_name" id="__customer_nameEditOrder" class="bg-gray-200" value="{{ $orderManagement->customer->customer_name }}" readonly />
                                    </div>
                                    <div id="__contactPhoneWrapper">
                                        <x-label for="__contact_phoneEditOrder">
                                            {{ __('translation.Phone Number') }} <x-form.required-mark />
                                        </x-label>
                                        <x-input type="text" name="contact_phone" id="__contact_phoneEditOrder" class="bg-gray-200" value="{{ $orderManagement->customer->contact_phone }}" readonly />
                                    </div>
                                </div>
                            </div>
                        </x-section.body>
                    </x-section.section>

                    <x-section.section class="xl:mb-12">
                        <x-section.title>
                            {{ __('translation.Shipping Address') }}
                        </x-section.title>
                        <x-section.body>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-x-8">
                                <div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-x-8">
                                        <div>
                                            <x-label for="__shipping_nameEditOrder">
                                                {{ __('translation.Customer Name') }} <x-form.required-mark />
                                            </x-label>
                                            @if($orderManagement->order_status == 6)
                                                <x-input type="text" name="shipping_name" id="__shipping_nameEditOrder" value="{{ $orderManagement->shipping_name }}" class="bg-gray-200" readonly />
                                            @else
                                                <x-input type="text" name="shipping_name" id="__shipping_nameEditOrder" value="{{ $orderManagement->shipping_name }}" />
                                            @endif
                                        </div>
                                        <div>
                                            <x-label for="__shipping_phoneEditOrder">
                                                {{ __('translation.Phone Number') }} <x-form.required-mark />
                                            </x-label>
                                            @if($orderManagement->order_status == 6)
                                                <x-input type="text" name="shipping_phone" id="__shipping_phoneEditOrder" value="{{ $orderManagement->shipping_phone }}" class="bg-gray-200" readonly />
                                            @else
                                                <x-input type="text" name="shipping_phone" id="__shipping_phoneEditOrder" value="{{ $orderManagement->shipping_phone }}" />
                                            @endif
                                        </div>
                                        <div class="sm:col-span-2 md:col-span-1 lg:col-span-2">
                                            <x-label for="__addressEditOrder">
                                                {{ __('translation.Shipping Address') }} <x-form.required-mark />
                                            </x-label>
                                            @if($orderManagement->order_status == 6)
                                                <x-form.textarea name="shipping_address" id="__addressEditOrder" rows="4" class="bg-gray-200" readonly>{{ $orderManagement->shipping_address }}</x-form.textarea>
                                            @else
                                                <x-form.textarea name="shipping_address" id="__addressEditOrder" rows="4">{{ $orderManagement->shipping_address }}</x-form.textarea>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-x-8">
                                        <div>
                                            <x-label for="__shipping_provinceEditOrder">
                                                {{ __('translation.Province') }} <x-form.required-mark />
                                            </x-label>
                                            @if($orderManagement->order_status == 6)
                                                <x-select name="shipping_province" id="__shipping_provinceEditOrder" style="width: 100%" disabled>
                                                    <option value="{{ $orderManagement->shipping_province }}" selected>
                                                        {{ $orderManagement->shipping_province }}
                                                    </option>
                                                </x-select>
                                            @else
                                                <x-select name="shipping_province" id="__shipping_provinceEditOrder" style="width: 100%">
                                                    <option value="{{ $orderManagement->shipping_province }}" selected>
                                                        {{ $orderManagement->shipping_province }}
                                                    </option>
                                                </x-select>
                                            @endif
                                        </div>
                                        <div>
                                            <x-label for="__shipping_districtEditOrder">
                                                {{ __('translation.District') }} <x-form.required-mark />
                                            </x-label>
                                            @if (empty($orderManagement->shipping_district))
                                                <x-select name="shipping_district" id="__shipping_districtEditOrder" style="width: 100%" disabled>
                                                    <option value="{{ $orderManagement->shipping_district }}" selected>
                                                        {{ $orderManagement->shipping_district }}
                                                    </option>
                                                </x-select>
                                            @else
                                                @if($orderManagement->order_status == 6)
                                                    <x-select name="shipping_district" id="__shipping_districtEditOrder" style="width: 100%" disabled>
                                                        <option value="{{ $orderManagement->shipping_district }}" selected>
                                                            {{ $orderManagement->shipping_district }}
                                                        </option>
                                                    </x-select>
                                                @else
                                                    <x-select name="shipping_district" id="__shipping_districtEditOrder" style="width: 100%">
                                                        <option value="{{ $orderManagement->shipping_district }}" selected>
                                                            {{ $orderManagement->shipping_district }}
                                                        </option>
                                                    </x-select>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="sm:col-span-2 md:col-span-1 lg:col-span-2">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-x-8">
                                                <div>
                                                    <x-label for="__shipping_sub_districtEditOrder">
                                                        {{ __('translation.Sub District') }} <x-form.required-mark />
                                                    </x-label>
                                                    @if (empty($orderManagement->shipping_sub_district))
                                                        <x-select name="shipping_sub_district" id="__shipping_sub_districtEditOrder" style="width: 100%" disabled>
                                                            <option value="{{ $orderManagement->shipping_sub_district }}" selected>
                                                                {{ $orderManagement->shipping_sub_district }}
                                                            </option>
                                                        </x-select>
                                                    @else
                                                        @if($orderManagement->order_status == 6)
                                                            <x-select name="shipping_sub_district" id="__shipping_sub_districtEditOrder" style="width: 100%" disabled>
                                                                <option value="{{ $orderManagement->shipping_sub_district }}" selected>
                                                                    {{ $orderManagement->shipping_sub_district }}
                                                                </option>
                                                            </x-select>
                                                        @else
                                                            <x-select name="shipping_sub_district" id="__shipping_sub_districtEditOrder" style="width: 100%">
                                                                <option value="{{ $orderManagement->shipping_sub_district }}" selected>
                                                                    {{ $orderManagement->shipping_sub_district }}
                                                                </option>
                                                            </x-select>
                                                        @endif
                                                    @endif
                                                </div>
                                                <div>
                                                    <x-label for="__shipping_postcodeEditOrder">
                                                        {{ __('translation.Postal Code') }} <x-form.required-mark />
                                                    </x-label>
                                                    @if (empty($orderManagement->shipping_postcode))
                                                        <x-select name="shipping_postcode" id="__shipping_postcodeEditOrder" style="width: 100%" disabled>
                                                            <option value="{{ $orderManagement->shipping_postcode }}" selected>
                                                                {{ $orderManagement->shipping_postcode }}
                                                            </option>
                                                        </x-select>
                                                    @else
                                                        @if($orderManagement->order_status == 6)
                                                            <x-select name="shipping_postcode" id="__shipping_postcodeEditOrder" style="width: 100%" disabled>
                                                                <option value="{{ $orderManagement->shipping_postcode }}" selected>
                                                                    {{ $orderManagement->shipping_postcode }}
                                                                </option>
                                                            </x-select>
                                                        @else
                                                            <x-select name="shipping_postcode" id="__shipping_postcodeEditOrder" style="width: 100%">
                                                                <option value="{{ $orderManagement->shipping_postcode }}" selected>
                                                                    {{ $orderManagement->shipping_postcode }}
                                                                </option>
                                                            </x-select>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-section.body>
                    </x-section.section>

                    <x-section.section>
                        <x-section.title-with-button titleText="Products">
                            @if($orderManagement->order_status != 6)
                                <x-button-sm type="button" color="red" class="ml-3 relative -top-1" id="__btnClearProductList">
                                    {{ __('translation.Reset') }}
                                </x-button-sm>
                            @endif
                        </x-section.title-with-button>
                        <x-section.body>
                            @if($orderManagement->order_status != 6)
                                <div class="mb-6 flex flex-row items-center justify-between">
                                    <div class="w-full sm:w-full">
                                        <x-input type="text" id="__product_id_EditOrder" placeholder="Enter Product Name or Code" autocomplete="off" />
                                    </div>
                                    <div class="w-auto mx-4 lg:mx-8 sm:w-1/6 lg:w-auto text-center">
                                        <span class="font-bold text-gray-500">OR</span>
                                    </div>
                                    <div class="w-auto sm:w-2/5 lg:w-1/4 xl:w-1/5">
                                        <div class="flex items-center justify-center sm:justify-end sm:relative sm:top-1">
                                            <x-button type="button" color="blue" id="__btnFindByGrid" class="h-10 relative top-[0.10rem] sm:-top-1 lg:w-full" title="{{ __('translation.Find By Grid') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                                </svg>
                                                <span class="whitespace-nowrap hidden sm:block sm:ml-2">
                                                {{ __('translation.Find By Grid') }}
                                            </span>
                                            </x-button>
                                        </div>
                                    </div>
                                </div>

                                <hr class="w-full border border-dashed border-r-0 border-b-0 border-l-0 border-blue-300 mb-5">
                            @endif

                            @if ($orderManagement->order_management_details->isNotEmpty())
                                <div id="__productListWrapper">
                                    @foreach ($orderManagement->order_management_details as $detail)
                                        <div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200" id="__row_ProductItem_{{ $detail->product->product_code }}">
                                            <input type="hidden" name="product_id[]" value="{{ !empty($detail->product_id) ? $detail->product_id : 0 }}" class="product-id__field" data-product-code="{{ $detail->product->product_code }}">
                                            <input type="hidden" name="product_price[]" value="{{ !empty($detail->price) ? $detail->price : 0 }}" class="product-price__field" data-product-code="{{ $detail->product->product_code }}">
                                            <input type="hidden" name="product_discount[]" value="{{ !empty($detail->discount_price) ? $detail->discount_price : 0 }}" min="0" max="{{ $detail->price }}" step="0.001" class="product-discount__field" data-product-code="{{ $detail->product->product_code }}">
                                            <input type="hidden" name="dropship_price[]" value="{{ !empty($detail->discount_price) ? ($detail->price - $detail->discount_price) : $detail->price }}" min="0" max="{{ $detail->price }}" class="dropship-price__field" data-product-code="{{ $detail->product->product_code }}">
                                            <input type="hidden" name="product_weight[]" value="{{ !empty($detail->product->weight) ? $detail->product->weight : 0 }}" class="product-weight__field" data-product-code="{{ $detail->product->product_code }}">

                                            <div class="w-1/4 sm:w-1/4 md:w-1/5 lg:w-1/6 mb-4 md:mb-0">
                                                <div class="mb-4">

                                                    @if(Storage::disk('s3')->exists($detail->product->image))
                                                    <img src="{{Storage::disk('s3')->url($detail->product->image)}}" alt="{{ $detail->product->product_name }}" class="w-full h-auto rounded-md">
                                                    @else
                                                    <img src="{{asset('No-Image-Found.png')}}" alt="" class="w-full h-auto rounded-md">
                                                    @endif
                                                    
                                                </div>
                                                @if($orderManagement->order_status != 6)
                                                    <div>
                                                        <x-button-sm type="button" color="red" class="block w-full" data-code="{{ $detail->product->product_code }}" onClick="removeProductItem(this)">
                                                        <span class="block sm:hidden">
                                                            <i class="fas fa-times"></i>
                                                        </span>
                                                            <span class="hidden sm:block">
                                                            {{ __('translation.Remove') }}
                                                        </span>
                                                        </x-button-sm>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="w-3/4 sm:w-3/4 md:w-4/5 lg:w-5/6 ml-4 sm:ml-6">
                                                <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 sm:gap-x-8 lg:pt-1">
                                                    <div class="sm:col-span-3">
                                                        <div class="mb-2 xl:mb-4 lg:col-span-2 xl:col-span-3">
                                                            <label class="hidden lg:block mb-0">
                                                                {{ __('translation.Product Name') }} :
                                                            </label>
                                                            <p class="font-bold">
                                                                {{ $detail->product->product_name }} <br>
                                                                <span class="text-gray-700">{{ $detail->product->product_code }}</span>
                                                            </p>
                                                        </div>
                                                        <div>
                                                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 lg:gap-x-8">
                                                                <div>
                                                                    <label class="mb-0 lg:block">
                                                                        {{ __('translation.Price') }} :
                                                                    </label>

                                                                    @if ($detail->discount_price == 0 && $customerType != 1)
                                                                        <span class="font-bold product-old-price">
                                                                            {{ currency_symbol('THB') }}
                                                                            {{ currency_number($detail->price, 3) }}
                                                                        </span>
                                                                        <button type="button" class="ml-3 bg-transparent border-0 outline-none focus:outline-none font-bold text-blue-500 btn-product-discount" data-product-code="{{ $detail->product->product_code }}">
                                                                            {{ __('translation.Discount Price') }}
                                                                        </button>
                                                                    @endif

                                                                    @if ($detail->discount_price > 0)
                                                                        @php
                                                                            $displayedDiscountPrice = $detail->price - $detail->discount_price;
                                                                        @endphp

                                                                        <span class="font-bold product-old-price line-through">
                                                                            {{ currency_symbol('THB') }}
                                                                            {{ currency_number($detail->price, 3) }}
                                                                        </span>
                                                                        <button type="button" class="ml-3 bg-transparent border-0 outline-none focus:outline-none font-bold text-blue-500 btn-product-discount" data-product-code="{{ $detail->product->product_code }}">
                                                                            <span>
                                                                                @if($customerType != 1)
                                                                                    {{ __('translation.Discount Price') }} :
                                                                                @else
                                                                                    {{ __('translation.Dropship Price') }} :
                                                                                @endif
                                                                            </span>{{ currency_symbol('THB') . ' ' . currency_number($displayedDiscountPrice, 3) }}
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                                <div>
                                                                    <label class="mb-0">
                                                                        {{ __('translation.Available Qty') }} :
                                                                    </label>
                                                                    <span class="font-bold lg:block">
                                                                        {{ number_format($detail->product->getQuantity->quantity) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="sm:col-span-2 xl:w-3/5">
                                                        <div class="grid grid-cols-2 sm:grid-cols-1 gap-3 sm:gap-4">
                                                            <div>
                                                                <label class="mb-0">
                                                                    {{ __('translation.Order Qty') }} <x-form.required-mark /> :
                                                                </label>
                                                                @if($orderManagement->order_status == 6)
                                                                    <x-input type="number" name="product_qty[]" value="0" min="1" class="product-qty__field" data-product-code="{{ $detail->product->product_code }}" value="{{ $detail->quantity }}" class="bg-gray-200" readonly />
                                                                @else
                                                                    <x-input type="number" name="product_qty[]" value="0" min="1" class="product-qty__field" data-product-code="{{ $detail->product->product_code }}" value="{{ $detail->quantity }}" />
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div id="__noProductWrapper" @if ($orderManagement->order_management_details->isNotEmpty()) style="display: none" @endif>
                                <div class="w-full py-4 rounded-lg text-center">
                                    <span class="font-bold text-base text-gray-500">
                                        --- {{ __('translation.No Product Added') }} ---
                                    </span>
                                </div>
                            </div>
                        </x-section.body>
                    </x-section.section>

                    <x-section.section>
                        <x-section.title>
                            {{ __('translation.Shipping Methods') }}
                        </x-section.title>
                        <x-section.body>
                            <div id="__shippingMethodOuterWrapper">

                                @if($orderManagement->order_status != 6)
                                    <div id="__shippingMethodButtonWrapper" class="mb-7">
                                        <x-button-sm type="button" color="blue" id="__btnAddNewShippingMethod">
                                            <i class="fas fa-plus"></i>
                                            <span class="ml-2">
                                            Add New
                                        </span>
                                        </x-button-sm>
                                    </div>
                                @endif

                                <div id="__shippingMethodListWrapper">
                                    @php
                                        $shippingItemId = 1;
                                    @endphp
                                    @foreach ($availableShippingCosts as $shippingCost)
                                        <div class="mb-4 pb-3 border border-t-0 border-r-0 border-l-0 border-dashed border-gray-300" id="__shippingMethodItem_{{ $shippingItemId }}">
                                            <div class="flex flex-row items-start">
                                                <div>
                                                    <input type="radio" name="shipping_method_radio[]" id="__shipping_method_{{ $shippingItemId }}" class="shipping-method__id-radio-field" data-id="{{ $shippingItemId }}" disabled>
                                                    <input type="hidden" name="shipping_method_id[]" value="{{ $shippingCost->id }}" class="shipping-method__id-input--field" data-id="{{ $shippingItemId }}">
                                                    <input type="hidden" name="shipping_method_name[]" value="{{ $shippingCost->name }} ({{ $shippingCost->shipper->name }})" class="shiping-method__name-field" disabled>
                                                    <input type="hidden" name="shipping_method_price[]" value="{{ $shippingCost->price }}" class="shiping-method__price-field" disabled>
                                                    <input type="hidden" name="shipping_method_discount[]" value="0" class="shiping-method__discount-field" disabled>
                                                    <input type="hidden" name="shipping_method_selected[]" value="0" class="shiping-method__selected-input-field" disabled>
                                                </div>
                                                <div class="ml-2">
                                                    <div class="flex flex-col lg:flex-row lg:items-center shipping-method__content-wrapper">
                                                        <div class="flex flex-col sm:flex-row sm:items-center">
                                                            <div class="mb-2 sm:mb-0">
                                                                <label for="__shipping_method_{{ $shippingItemId }}" class="ml-1">
                                                                    {{ $shippingCost->name }} ({{ $shippingCost->shipper->name }})
                                                                </label>
                                                            </div>
                                                            <div class="hidden sm:block ml-2">
                                                                -
                                                            </div>
                                                            <div class="sm:ml-2">
                                                                <span class="font-bold shipping-method__price-display">
                                                                    {{ currency_symbol('THB') }} {{ currency_number($shippingCost->price, 3) }}
                                                                </span>
                                                                <button type="button" class="ml-2 bg-transparent border-0 outline-none focus:outline-none font-bold text-blue-500 shipping-method__btn-product-discount" data-id="{{ $shippingItemId }}">
                                                                    {{ __('translation.Discount Cost') }}
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="hidden lg:block ml-6">
                                                            -
                                                        </div>
                                                        <div class="sm:ml-2">
                                                            <div class="flex flex-row items-center mt-4 lg:mt-0">
                                                                <div class="mr-4">
                                                                    <label class="mb-0 font-bold">
                                                                        Public Page :
                                                                    </label>
                                                                </div>
                                                                <div>
                                                                    <div class="mr-4 relative top-[0.10rem]">
                                                                        <input type="checkbox" id="__shiping_method_enable_checkbox_{{ $shippingItemId }}" class="shiping-method__enable-checkbox-field" data-id="{{ $shippingItemId }}">
                                                                        <label for="__shiping_method_enable_checkbox_{{ $shippingItemId }}" class="mb-0 ml-1">
                                                                            {{ __('translation.Enable') }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @php
                                            $shippingItemId++;
                                        @endphp
                                    @endforeach
                                </div>

                                <div id="__shippingMethodEnabledListWrapper">
                                    @foreach ($orderManagement->customer_shipping_methods as $customerShipping)
                                        @if (!empty($customerShipping->shipping_cost->shipper->name))
                                            <div class="mb-4 pb-3 border border-t-0 border-r-0 border-l-0 border-dashed border-gray-300" id="__shippingMethodItem_{{ $shippingItemId }}">
                                                <div class="flex flex-row items-start">
                                                    <div>
                                                        <input type="radio" name="shipping_method_radio[]" id="__shipping_method_{{ $shippingItemId }}" class="shipping-method__id-radio-field" value="0" data-id="{{ $shippingItemId }}" @if ($customerShipping->is_selected == $isSelectedShippingMethod) checked @endif>
                                                        <input type="hidden" name="shipping_method_id[]" value="{{ $customerShipping->shipping_cost->id }}" class="shipping-method__id-input--field" data-id="{{ $shippingItemId }}">
                                                        <input type="hidden" name="shipping_method_name[]" value="{{ $customerShipping->shipping_cost->name }} ({{ $customerShipping->shipping_cost->shipper->name }})" class="shiping-method__name-field">
                                                        <input type="hidden" name="shipping_method_price[]" value="{{ $customerShipping->price }}" class="shiping-method__price-field">
                                                        <input type="hidden" name="shipping_method_discount[]" value="{{ $customerShipping->discount_price }}" class="shiping-method__discount-field">
                                                        <input type="hidden" name="shipping_method_selected[]" value="{{ $customerShipping->is_selected }}" class="shiping-method__selected-input-field">
                                                    </div>
                                                    <div class="ml-2">
                                                        <div class="flex flex-col lg:flex-row lg:items-center shipping-method__content-wrapper">
                                                            <div class="flex flex-col sm:flex-row sm:items-center">
                                                                <div class="mb-2 sm:mb-0">
                                                                    <label for="__shipping_method_{{ $shippingItemId }}" class="ml-1">
                                                                        {{ $customerShipping->shipping_cost->name }} ({{ $customerShipping->shipping_cost->shipper->name }})
                                                                    </label>
                                                                </div>
                                                                <div class="hidden sm:block ml-6">
                                                                    -
                                                                </div>
                                                                <div class="sm:ml-2">
                                                                    @if ($customerShipping->discount_price == 0)
                                                                        <span class="font-bold shipping-method__price-display">
                                                                        {{ currency_symbol('THB') }} {{ currency_number($customerShipping->price, 3) }}
                                                                    </span>
                                                                        <button type="button" class="ml-2 bg-transparent border-0 outline-none focus:outline-none font-bold text-blue-500 shipping-method__btn-product-discount" data-id="{{ $shippingItemId }}">
                                                                            {{ __('translation.Discount Cost') }}
                                                                        </button>
                                                                    @endif

                                                                    @if ($customerShipping->discount_price > 0)
                                                                        @php
                                                                            $discountPrice = $customerShipping->price - $customerShipping->discount_price;
                                                                        @endphp
                                                                        <span class="font-bold line-through shipping-method__price-display">
                                                                        {{ currency_symbol('THB') }} {{ currency_number($customerShipping->price, 3) }}
                                                                    </span>
                                                                        <button type="button" class="ml-2 bg-transparent border-0 outline-none focus:outline-none font-bold text-blue-500 shipping-method__btn-product-discount" data-id="{{ $shippingItemId }}">
                                                                            {{ __('translation.Discount Cost') . ' : ' . currency_symbol('THB') . ' ' . currency_number($discountPrice, 3) }}
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="hidden lg:block ml-6">
                                                                -
                                                            </div>
                                                            <div class="sm:ml-2">
                                                                <div class="flex flex-row items-center mt-4 lg:mt-0">
                                                                    <div class="mr-4">
                                                                        <label class="mb-0 font-bold">
                                                                            Public Page :
                                                                        </label>
                                                                    </div>
                                                                    <div>
                                                                        <div class="mr-4 relative top-[0.10rem]">
                                                                            @if($orderManagement->order_status == 6)
                                                                                <input type="checkbox" id="__shiping_method_enable_checkbox_{{ $shippingItemId }}" class="shiping-method__enable-checkbox-field" data-id="{{ $shippingItemId }}" checked disabled>
                                                                            @else
                                                                                <input type="checkbox" id="__shiping_method_enable_checkbox_{{ $shippingItemId }}" class="shiping-method__enable-checkbox-field" data-id="{{ $shippingItemId }}" checked>
                                                                            @endif
                                                                            <label for="__shiping_method_enable_checkbox_{{ $shippingItemId }}" class="mb-0 ml-1">
                                                                                {{ __('translation.Enable') }}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="mb-4 pb-3 border border-t-0 border-r-0 border-l-0 border-dashed border-gray-300" id="__shippingMethodItem_{{ $shippingItemId }}">
                                                <div class="flex flex-row items-start">
                                                    <div>
                                                        <input type="radio" name="shipping_method_radio[]" id="__shipping_method_{{ $shippingItemId }}" class="shipping-method__id-radio-field" data-id="{{ $shippingItemId }}" @if ($customerShipping->is_selected == $isSelectedShippingMethod) checked @endif>
                                                        <input type="hidden" name="shipping_method_id[]" value="0" class="shipping-method__id-input--field" data-id="{{ $shippingItemId }}">
                                                        <input type="hidden" name="shipping_method_name[]" value="{{ $customerShipping->shipping_cost->name }}" class="shiping-method__name-field">
                                                        <input type="hidden" name="shipping_method_price[]" value="{{ $customerShipping->price }}" class="shiping-method__price-field">
                                                        <input type="hidden" name="shipping_method_discount[]" value="{{ $customerShipping->discount_price }}" class="shiping-method__discount-field">
                                                        <input type="hidden" name="shipping_method_selected[]" value="{{ $customerShipping->is_selected }}" class="shiping-method__selected-input-field">
                                                    </div>
                                                    <div class="ml-2">
                                                        <div class="flex flex-col lg:flex-row lg:items-center shipping-method__content-wrapper">
                                                            <div class="flex flex-col sm:flex-row sm:items-center">
                                                                <div class="mb-2 sm:mb-0">
                                                                    <label for="__shipping_method_{{ $shippingItemId }}" class="ml-1">
                                                                        {{ $customerShipping->shipping_cost->name }}
                                                                    </label>
                                                                </div>
                                                                <div class="hidden sm:block ml-6">
                                                                    -
                                                                </div>
                                                                <div class="sm:ml-2">
                                                                    @if ($customerShipping->discount_price == 0)
                                                                        <span class="font-bold shipping-method__price-display">
                                                                            {{ currency_symbol('THB') }} {{ currency_number($customerShipping->price, 3) }}
                                                                        </span>
                                                                        <button type="button" class="ml-2 bg-transparent border-0 outline-none focus:outline-none font-bold text-blue-500 shipping-method__btn-product-discount" data-id="{{ $shippingItemId }}">
                                                                            {{ __('translation.Discount Cost') }}
                                                                        </button>
                                                                    @endif

                                                                    @if ($customerShipping->discount_price > 0)
                                                                        @php
                                                                            $discountPrice = $customerShipping->price - $customerShipping->discount_price;
                                                                        @endphp
                                                                        <span class="font-bold line-through shipping-method__price-display">
                                                                            {{ currency_symbol('THB') }} {{ currency_number($customerShipping->price, 3) }}
                                                                        </span>
                                                                        <button type="button" class="ml-2 bg-transparent border-0 outline-none focus:outline-none font-bold text-blue-500 shipping-method__btn-product-discount" data-id="{{ $shippingItemId }}">
                                                                            {{ __('translation.Discount Cost') . ' : ' . currency_symbol('THB') . ' ' . currency_number($discountPrice, 3) }}
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="hidden lg:block ml-6">
                                                                -
                                                            </div>
                                                            <div class="sm:ml-2">
                                                                <div class="flex flex-row items-center mt-4 lg:mt-0">
                                                                    <div class="mr-4">
                                                                        <label class="mb-0 font-bold">
                                                                            Public Page :
                                                                        </label>
                                                                    </div>
                                                                    <div>
                                                                        <div class="mr-4 relative top-[0.10rem]">
                                                                            <input type="checkbox" id="__shiping_method_enable_checkbox_{{ $shippingItemId }}" class="shiping-method__enable-checkbox-field" data-id="{{ $shippingItemId }}" checked>
                                                                            <label for="__shiping_method_enable_checkbox_{{ $shippingItemId }}" class="mb-0 ml-1">
                                                                                {{ __('translation.Enable') }}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="hidden lg:block ml-3">
                                                                -
                                                            </div>
                                                            <div class="lg:ml-2 mt-3 lg:mt-0">
                                                                <button type="button" class="ml-2 bg-transparent border-0 outline-none focus:outline-none font-bold text-red-500 shipping-method__btn-remove-item" data-id="{{ $shippingItemId }}">
                                                                    {{ __('translation.Remove') }}
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @php
                                            $shippingItemId++;
                                        @endphp
                                    @endforeach
                                </div>

                                <div id="__noShippingMethodsWrapper" style="display: none">
                                    <div class="w-full py-4 rounded-lg text-center">
                                        <span class="font-bold text-base text-gray-500">
                                            --- {{ __('translation.No Shipping Methods Available') }} ---
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </x-section.body>
                    </x-section.section>

                    <x-section.section>
                        <x-section.title>
                            {{ __('translation.Tax Details') }}
                        </x-section.title>
                        <x-section.body>
                            <div class="mb-4">
                                <label for="__tax_enable_EditOrder" class="block mb-2">
                                    {{ __('translation.Request Tax') }} <x-form.required-mark/>
                                </label>
                                <div class="flex flex-row gap-x-4">
                                    @foreach ($taxEnableValues as $value => $text)
                                        @if ($value == $orderManagement->tax_enable)
                                            <x-form.input-radio name="tax_enable" id="__tax_enable_{{ $value }}EditOrder" value="{{ $value }}" checked="true">
                                                {{ $text }}
                                            </x-form.input-radio>
                                        @else
                                            <x-form.input-radio name="tax_enable" id="__tax_enable_{{ $value }}EditOrder" value="{{ $value }}">
                                                {{ $text }}
                                            </x-form.input-radio>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-8" id="__taxCompanyInfoWrapper" @if ($orderManagement->tax_enable != $taxEnableYes) style="display:none" @endif>
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">
                                        <div>
                                            <x-label for="__company_nameEditOrder">
                                                {{ __('translation.Company Name') }}
                                            </x-label>
                                            <x-input type="text" name="company_name" id="__company_nameEditOrder" value="{{ $orderManagement->company_name }}" />
                                        </div>
                                        <div>
                                            <x-label for="__tax_numberEditOrder">
                                                {{ __('translation.Tax Number') }}
                                            </x-label>
                                            <x-input type="text" name="tax_number" id="__tax_numberEditOrder" value="{{ $orderManagement->tax_number }}" />
                                        </div>
                                        <div>
                                            <x-label for="__company_phone_numberEditOrder">
                                                {{ __('translation.Phone Number') }}
                                            </x-label>
                                            <x-input type="text" name="company_phone_number" id="__company_phone_numberEditOrder" value="{{ $orderManagement->company_phone_number }}" />
                                        </div>
                                        <div>
                                            <x-label for="__company_contact_nameEditOrder">
                                                {{ __('translation.Contact Name') }}
                                            </x-label>
                                            <x-input type="text" name="company_contact_name" id="__company_contact_nameEditOrder" value="{{ $orderManagement->company_contact_name }}" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">
                                        <div class="sm:col-span-2">
                                            <x-label for="__company_addressEditOrder">
                                                {{ __('translation.Address') }}
                                            </x-label>
                                            <x-form.textarea name="company_address" id="__company_addressEditOrder" rows="3">{{ $orderManagement->company_address }}</x-form.textarea>
                                        </div>
                                        <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-x-8">
                                            <div>
                                                <x-label for="__company_provinceEditOrder">
                                                    {{ __('translation.Province') }}
                                                </x-label>
                                                <x-select name="company_province" id="__company_provinceEditOrder" style="width: 100%">
                                                    <option value="{{ $orderManagement->company_province }}" selected>
                                                        {{ $orderManagement->company_province }}
                                                    </option>
                                                </x-select>
                                            </div>
                                            <div>
                                                <x-label for="__company_districtEditOrder">
                                                    {{ __('translation.District') }}
                                                </x-label>
                                                @if (!empty($orderManagement->company_district))
                                                    <x-select name="company_district" id="__company_districtEditOrder" style="width: 100%">
                                                        <option value="{{ $orderManagement->company_district }}">
                                                            {{ $orderManagement->company_district }}
                                                        </option>
                                                    </x-select>
                                                @else
                                                    <x-select name="company_district" id="__company_districtEditOrder" style="width: 100%" disabled></x-select>
                                                @endif
                                            </div>
                                            <div>
                                                <x-label for="__company_sub_districtEditOrder">
                                                    {{ __('translation.Sub-District') }}
                                                </x-label>
                                                @if (!empty($orderManagement->company_sub_district))
                                                    <x-select name="company_sub_district" id="__company_sub_districtEditOrder" style="width: 100%">
                                                        <option value="{{ $orderManagement->company_sub_district }}">
                                                            {{ $orderManagement->company_sub_district }}
                                                        </option>
                                                    </x-select>
                                                @else
                                                    <x-select name="company_sub_district" id="__company_sub_districtEditOrder" style="width: 100%" disabled></x-select>
                                                @endif
                                            </div>
                                            <div>
                                                <x-label for="__company_postcodeEditOrder">
                                                    {{ __('translation.Postal Code') }}
                                                </x-label>
                                                @if (!empty($orderManagement->company_postcode))
                                                    <x-select name="company_postcode" id="__company_postcodeEditOrder" style="width: 100%">
                                                        <option value="{{ $orderManagement->company_postcode }}">
                                                            {{ $orderManagement->company_postcode }}
                                                        </option>
                                                    </x-select>
                                                @else
                                                    <x-select name="company_postcode" id="__company_postcodeEditOrder" style="width: 100%" disabled></x-select>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid cols-1 sm:grid-cols-4 gap-4 sm:gap-x-8">
                                    <div class="sm:col-span-2">
                                        <x-label for="__tax_invoice_noteEditOrder">
                                            {{ __('translation.Note') }}
                                        </x-label>
                                        <x-form.textarea name="tax_invoice_note" id="__tax_invoice_noteEditOrder" rows="3">{{ $orderManagement->tax_invoice_note }}</x-form.textarea>
                                    </div>
                                </div>
                            </div>
                        </x-section.body>
                    </x-section.section>

                    <x-section.section>
                        <x-section.title>
                            {{ __('translation.Cart Totals') }}
                        </x-section.title>
                        <x-section.body>
                            <div class="w-full lg:w-1/2 lg:mx-auto">
                                <table class="w-full -mt-1">
                                    <tbody>
                                    <tr>
                                        <td class="pr-3 py-1">
                                            Sub Total
                                        </td>
                                        <td class="py-1">
                                            <span class="text-white">-</span>
                                            <span class="font-bold">
                                                    {{ currency_symbol('THB') }}
                                                </span>
                                        </td>
                                        <td class="pl-3 py-1 text-right">
                                                <span class="font-bold" id="__subTotalCurrency">
                                                    {{ currency_number($orderManagement->sub_total, 3) }}
                                                </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pr-3 py-1">
                                            Shipping Cost
                                        </td>
                                        <td class="py-1">
                                            <span class="text-white">-</span>
                                            <span class="font-bold">
                                                    {{ currency_symbol('THB') }}
                                                </span>
                                        </td>
                                        <td class="pl-3 py-1 text-right">
                                                <span class="font-bold" id="__shippingCostCurrency">
                                                    {{ currency_number($orderManagement->shipping_cost, 3) }}
                                                </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pr-3 py-1">
                                            Discount
                                        </td>
                                        <td class="py-1">
                                            <span class="text-gray-900">-</span>
                                            <span class="font-bold">
                                                    {{ currency_symbol('THB') }}
                                                </span>
                                        </td>
                                        <td class="pl-3 py-1 text-right">
                                                <span class="font-bold" id="__discountCurrency">
                                                    {{ currency_number($orderManagement->amount_discount_total, 3) }}
                                                </span>
                                        </td>
                                    </tr>
                                    <tr id="__taxRateRowCartTotals" @if ($orderManagement->tax_enable != $taxEnableYes && $orderManagement->tax_rate <= 0) style="display: none;" @endif>
                                        <td class="pr-3 py-1">
                                            {{ $taxRateSetting->tax_name ?? '' }} (<span id="__taxRateCartTotal">{{ currency_number($orderManagement->tax_rate, 2) . '%' }}</span>)
                                        </td>
                                        <td class="py-1">
                                            <span class="text-white">-</span>
                                            <span class="font-bold">
                                                    {{ currency_symbol('THB') }}
                                                </span>
                                        </td>
                                        <td class="pl-3 py-1 text-right">
                                                <span class="font-bold" id="__taxRateCurrency">
                                                    {{ currency_number($orderManagement->amount_tax_rate, 3) }}
                                                </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="pt-1 border border-dashed border-r-0 border-b-0 border-l-0 border-gray-400"></td>
                                    </tr>
                                    <tr>
                                        <td class="pr-3 py-1 font-bold text-red-500">
                                            Total Amount
                                        </td>
                                        <td class="py-1">
                                            <span class="text-white">-</span>
                                            <span class="font-bold text-red-500">
                                                    {{ currency_symbol('THB') }}
                                                </span>
                                        </td>
                                        <td class="pl-3 py-1 text-right">
                                                <span class="font-bold text-red-500" id="__grandTotalCurrency">
                                                    {{ currency_number($orderManagement->in_total, 3) }}
                                                </span>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </x-section.body>
                    </x-section.section>

                    <div class="text-center pb-4">
                        <x-button type="button" color="gray" class="mr-1" id="__btnCancelEditOrder">
                            {{ __('translation.Cancel') }}
                        </x-button>
                        @if($orderManagement->order_status == 6)
                            <x-button type="submit" color="blue" id="__btnSubmitEditOrder" disabled>
                                {{ __('translation.Update Order') }}
                            </x-button>
                        @else
                            <x-button type="submit" color="blue" id="__btnSubmitEditOrder">
                                {{ __('translation.Update Order') }}
                            </x-button>
                        @endif
                    </div>
                </form>
            </x-card.body>
        </x-card.card-default>


        <x-modal.modal-small class="modal-hide" id="__modalCancelEditOrder">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Confirm') }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>
                <div class="mb-5">
                    <p class="text-center">
                        {{ __('translation.Are you sure to cancel this order') . '?' }}
                    </p>
                </div>
                <div class="text-center pb-5">
                    <x-button type="button" color="gray" id="__btnCloseModalCancelEditOrder">
                        {{ __('translation.No, Close') }}
                    </x-button>
                    <x-button-link href="{{ route('order_management.index') }}" color="red">
                        {{ __('translation.Yes, Continue') }}
                    </x-button-link>
                </div>
            </x-modal.body>
        </x-modal.modal-small>


        <x-modal.modal-small class="modal-hide" id="__modalRemoveShippingItem">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Confirm') }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>
                <div class="mb-5">
                    <p class="text-center">
                        {{ __('translation.Are you sure to remove this shipping method') . '?' }}
                    </p>
                </div>
                <div class="text-center pb-5">
                    <x-button type="button" color="gray" id="__btnNoModalRemoveShippingItem">
                        {{ __('translation.No, Close') }}
                    </x-button>
                    <x-button type="button" color="red" id="__btnYesModalRemoveShippingItem">
                        {{ __('translation.Yes, Remove') }}
                    </x-button>
                </div>
            </x-modal.body>
        </x-modal.modal-small>

        <x-modal.modal-small class="modal-hide" id="__modalTakeAction">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Confirm') }}
                </x-modal.title>
                <x-modal.close-button id="__btnCloseModalConfirm"/>
            </x-modal.header>
            <x-modal.body>
                <div class="mb-5">
                    <p class="text-center" id="your_text"></p>
                    <input type="hidden" id="take_action_value_input">
                </div>
                <div class="text-center pb-5">
                    <span id="cancel_wrapper">
                        <x-button type="button" color="gray" id="__btnCloseModalCancelTakeAction">
                            {{ __('translation.No, Close') }}
                        </x-button>
                    </span>
                    <span id="cancel_processing_wrapper">
                        <x-button type="button" color="gray" id="__btnCloseModalContinueCancelProcessing">
                            {{ __('translation.Back to New Order') }}
                        </x-button>
                    </span>
                    <span id="yes_continue_wrapper">
                        <x-button-link class="__btnCloseModalContinueTakeAction"  color="red">
                            {{ __('translation.Yes, Continue') }}
                        </x-button-link>
                    </span>
                    <span id="pay_link_wrapper">
                        <x-button  id="btnPayLinkTakeAction" target="_blank" color="red">
                            {{ __('translation.Yes, Continue') }}
                        </x-button>
                    </span>
                    <span id="change_payment_method_wrapper">
                        <x-button-link id="__btnCloseModalContinueChangePaymentMethod"  color="red">
                            {{ __('translation.Yes, Continue') }}
                        </x-button-link>
                    </span>
                </div>
            </x-modal.body>
        </x-modal.modal-small>
        <!-- dev-asif -->

        <div class="modal" tabindex="-1" role="dialog" id="asif">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modal title</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Modal body text goes here.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary">Save changes</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="hidden" id="__templateProductItem">
            <div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200" id="__row_ProductItem_{product_code}">
                <input type="hidden" name="product_id[]" value="{product_id}" class="product-id__field" data-product-code="{product_code}">
                <input type="hidden" name="product_price[]" value="{price}" class="product-price__field" data-product-code="{product_code}">
                <input type="hidden" name="product_discount[]" value="0" min="0" max="{price}" step="0.001" class="product-discount__field" data-product-code="{product_code}">
                <input type="hidden" name="dropship_price[]" value="{dropship_price}" class="dropship-price__field" data-product-code="{product_code}">
                <input type="hidden" name="product_weight[]" value="{weight}" class="product-weight__field" data-product-code="{product_code}">

                <div class="w-1/4 sm:w-1/4 md:w-1/5 lg:w-1/6 mb-4 md:mb-0">
                    <div class="mb-4">
                        <img src="#" alt="{product_name}" class="w-full h-auto rounded-md">
                    </div>
                    <div>
                        <x-button-sm type="button" color="red" class="block w-full" data-code="{product_code}" onClick="removeProductItem(this)">
                            <span class="block sm:hidden">
                                <i class="fas fa-times"></i>
                            </span>
                            <span class="hidden sm:block">
                                {{ __('translation.Remove') }}
                            </span>
                            </x-button>
                    </div>
                </div>
                <div class="w-3/4 sm:w-3/4 md:w-4/5 lg:w-5/6 ml-4 sm:ml-6">
                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 sm:gap-x-8 lg:pt-1">
                        <div class="sm:col-span-3">
                            <div class="mb-2 xl:mb-4 lg:col-span-2 xl:col-span-3">
                                <label class="hidden lg:block mb-0">
                                    {{ __('translation.Product Name') }} :
                                </label>
                                <p class="font-bold">
                                    {product_name} <br>
                                    <span class="text-gray-700">{product_code}</span>
                                </p>
                            </div>
                            <div>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 lg:gap-x-8">
                                    <div>
                                        <label class="mb-0 lg:block">
                                            {{ __('translation.Price') }} :
                                        </label>
                                        <span class="font-bold product-old-price">
                                            {{ currency_symbol('THB') }}
                                            {priceString}
                                        </span>
                                        @if($customerType == '0')
                                            <button type="button" class="ml-3 bg-transparent border-0 outline-none focus:outline-none font-bold text-blue-500 btn-product-discount" data-product-code="{product_code}">
                                                {{ __('translation.Discount Price') }}
                                            </button>
                                        @endif
                                        <button type="button" class="hidden ml-3 bg-transparent border-0 outline-none focus:outline-none font-bold text-blue-500 btn-dropship-price" data-product-code="{product_code}">
                                            {{ __('translation.Dropship Price') }}
                                        </button>
                                    </div>
                                    <div>
                                        <label class="mb-0">
                                            {{ __('translation.Available Qty') }} :
                                        </label>
                                        <span class="font-bold lg:block">
                                            {qty}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sm:col-span-2 xl:w-3/5">
                            <div class="grid grid-cols-2 sm:grid-cols-1 gap-3 sm:gap-4">
                                <div>
                                    <label class="mb-0">
                                        {{ __('translation.Order Qty') }} <x-form.required-mark /> :
                                    </label>
                                    <x-input type="number" name="product_qty[]" value="0" min="1" class="product-qty__field" data-product-code="{product_code}" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="__templateShippingItem" class="hidden">
        <div class="mb-4 pb-3 border border-t-0 border-r-0 border-l-0 border-dashed border-gray-300" id="__shippingMethodItem_{item_id}">
            <div class="flex flex-row items-start">
                <div>
                    <input type="radio" name="shipping_method_radio[]" id="__shipping_method_{item_id}" class="shipping-method__id-radio-field" data-id="{item_id}">
                    <input type="hidden" name="shipping_method_id[]" value="{shipping_id}" class="shipping-method__id-input--field" data-id="{item_id}">
                    <input type="hidden" name="shipping_method_name[]" value="{shipping_cost_name} ({shipper_name})" class="shiping-method__name-field">
                    <input type="hidden" name="shipping_method_price[]" value="{price}" class="shiping-method__price-field">
                    <input type="hidden" name="shipping_method_discount[]" value="0" class="shiping-method__discount-field">
                    <input type="hidden" name="shipping_method_selected[]" value="0" class="shiping-method__selected-input-field">
                </div>
                <div class="ml-2">
                    <div class="flex flex-col lg:flex-row lg:items-center shipping-method__content-wrapper">
                        <div class="flex flex-col sm:flex-row sm:items-center">
                            <div class="mb-2 sm:mb-0">
                                <label for="__shipping_method_{item_id}" class="ml-1">
                                    {shipping_cost_name} ({shipper_name})
                                </label>
                            </div>
                            <div class="hidden sm:block ml-6">
                                -
                            </div>
                            <div class="sm:ml-2">
                                <span class="font-bold shipping-method__price-display">
                                    {{ currency_symbol('THB') }} {priceString}
                                </span>
                                <button type="button" class="ml-2 bg-transparent border-0 outline-none focus:outline-none font-bold text-blue-500 shipping-method__btn-product-discount" data-id="{item_id}">
                                    {{ __('translation.Discount Cost') }}
                                </button>
                            </div>
                        </div>
                        <div class="hidden lg:block ml-6">
                            -
                        </div>
                        <div class="sm:ml-2">
                            <div class="flex flex-row items-center mt-4 lg:mt-0">
                                <div class="mr-4">
                                    <label class="mb-0 font-bold">
                                        Public Page :
                                    </label>
                                </div>
                                <div>
                                    <div class="mr-4 relative top-[0.10rem]">
                                        <input type="checkbox" id="__shiping_method_enable_checkbox_{item_id}" class="shiping-method__enable-checkbox-field" data-id="{item_id}" checked>
                                        <label for="__shiping_method_enable_checkbox_{item_id}" class="mb-0 ml-1">
                                            {{ __('translation.Enable') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="__templateRemoveButtonShippingItem" class="hidden">
        <div class="hidden lg:block ml-3">
            -
        </div>
        <div class="lg:ml-2 mt-3 lg:mt-0">
            <button type="button" class="ml-2 bg-transparent border-0 outline-none focus:outline-none font-bold text-red-500 shipping-method__btn-remove-item" data-id="{item_id}">
                {{ __('translation.Remove') }}
            </button>
        </div>
    </div>


    <x-modal.modal-small id="__modalAddDiscount" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Product Discount Price') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">

                <input type="hidden" id="__productCodeAddDiscount">

                <div class="mb-5">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="mb-0">
                                {{ __('translation.Original Price') }} <x-form.required-mark /> :
                            </label>
                            <x-input type="number" id="__currentPriceAddDiscount" min="0" step="0.001" class="bg-gray-200" readonly />
                        </div>
                        <div>
                            <label class="mb-0">
                                {{ __('translation.Discount Price') }} <x-form.required-mark /> :
                            </label>
                            <x-input type="number" id="__discountPriceAddDiscount" min="0" step="0.001" />
                        </div>
                    </div>
                </div>
                <div class="text-center flex flex-col items-center">
                    <div>
                        <x-button type="button" color="red-text" id="__btnResetDiscountModalAddDiscount" class="mb-2">
                            {{ __('translation.Reset Discount') }}
                        </x-button>
                    </div>
                    <div class="text-center">
                        <x-button type="button" color="gray" id="__btnCancelModalAddDiscount" class="mb-2">
                            {{ __('translation.Cancel') }}
                        </x-button>
                        <x-button type="button" color="blue" id="__btnSaveModalAddDiscount">
                            {{ __('translation.Save Changes') }}
                        </x-button>
                    </div>
                </div>
            </div>
        </x-modal.body>
    </x-modal.modal-small>


    <x-modal.modal-small id="__modalAddShippingDiscount" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Shipping Discount Cost') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">

                <input type="hidden" id="__shippingIdAddShippingDiscount">

                <div class="mb-5">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="mb-0">
                                {{ __('translation.Original Cost') }} <x-form.required-mark /> :
                            </label>
                            <x-input type="number" id="__currentPriceAddShippingDiscount" min="0" step="0.001" class="bg-gray-200" readonly />
                        </div>
                        <div>
                            <label class="mb-0">
                                {{ __('translation.Discount Cost') }} <x-form.required-mark /> :
                            </label>
                            <x-input type="number" id="__discountPriceAddShippingDiscount" min="0" step="0.001" />
                        </div>
                    </div>
                </div>
                <div class="text-center flex flex-col items-center">
                    <div>
                        <x-button type="button" color="red-text" id="__btnResetDiscountModalAddShippingDiscount" class="mb-2">
                            {{ __('translation.Reset Discount') }}
                        </x-button>
                    </div>
                    <div class="text-center">
                        <x-button type="button" color="gray" id="__btnCancelModalAddShippingDiscount" class="mb-2">
                            {{ __('translation.Cancel') }}
                        </x-button>
                        <x-button type="button" color="blue" id="__btnSaveModalAddShippingDiscount">
                            {{ __('translation.Save Changes') }}
                        </x-button>
                    </div>
                </div>
            </div>
        </x-modal.body>
    </x-modal.modal-small>


    <x-modal.modal-large id="__modalProductGrid" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Find Any Products') }}
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalProductGrid" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <div id="__categoryGridWrapper">
                    <div class="mb-10">
                        <div class="w-full">
                            <x-label for="__category_idProductGrid">
                                {{ __('translation.Category') }} <x-form.required-mark/>
                            </x-label>
                            <x-select name="category_id" id="__category_idProductGrid">
                                <option value="" selected disabled>
                                    {{ '- '.  __('translation.All Categories') .' -' }}
                                </option>
                            </x-select>
                        </div>
                    </div>

                    <div class="mb-10">
                        <div class="flex flex-row items-center justify-center">
                            <span class="mr-2">
                                {{ __('translation.Search') }}:
                            </span>
                            <div class="w-3/5">
                                <x-input type="text" id="__searchSubCategoryGrid" />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-x-8 gap-y-6" id="__subCategoryGridList"></div>
                    <div id="__subCategoryListLoadMoreWrapper"></div>
                </div>

                <div class="hidden" id="__subCategoryGridWrapper">
                    <div class="mb-10">
                        <div class="mb-4">
                            <div class="flex flex-row items-center">
                                <x-back-button title="View Sub Categories" id="__btnViewSubCategoriesGrid" />
                                <div class="ml-2">
                                    <span>
                                        Selected Sub-Category
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <span class="font-bold" id="__subCategoryNameDisplayGrid"></span>
                        </div>
                    </div>

                    <div class="w-full overflow-x-auto mb-10">
                        <table class="w-full" id="__tableProductGrid">
                            <thead>
                            <tr class="bg-blue-500">
                                <th class="px-2 py-4 text-white w-24 md:w-36 text-center">ID</th>
                                <th class="px-2 py-4 text-white text-center">Product Details</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </x-modal.body>
    </x-modal.modal-large>


    <div id="__templateSubCategoryItemGrid" class="hidden">
        <div class="grid--sub-category__item cursor-pointer hover:text-blue-800 focus:text-blue-800 transition duration-300"
             data-id="{id}"
             data-name="{name}">
            <div class="w-full h-32 md:h-28 mb-3 bg-no-repeat bg-cover bg-center rounded-md" style="background-image: url('{image_url}')"></div>
            <div class="px-1 text-center">
                <p class="truncate-2 font-bold">{name}</p>
            </div>
        </div>
    </div>


    <div id="__templateLoadMoreButtonSubCategoryGrid" class="hidden">
        <div class="text-center">
            <x-button type="button" color="blue" class="sub-category__btn-load-more">
                {{ __('translation.Load More') }}
            </x-button>
        </div>
    </div>


    <x-modal.modal-small id="__modalNewShippingMethod" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.New Shipping Method') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form action="#" method="post" id="__formNewShippingMethod">
                    <div class="mb-5">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="mb-0">
                                    {{ __('translation.Name') }} <x-form.required-mark /> :
                                </label>
                                <x-input type="text" name="name" id="__nameNewShippingMethod" autocomplete="off" required />
                            </div>
                            <div>
                                <label class="mb-0">
                                    {{ __('translation.Cost') }} <x-form.required-mark /> :
                                </label>
                                <x-input type="number" name="price" id="__priceNewShippingMethod" step="0.001" autocomplete="off" required />
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <x-button type="reset" color="gray" id="__btnCancelModalNewShippingMethod" class="mb-2">
                            {{ __('translation.Cancel') }}
                        </x-button>
                        <x-button type="submit" color="blue" id="__btnSaveModalNewShippingMethod">
                            {{ __('translation.Save') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

    <div class="modal fade" tabindex="-1" role="dialog" id="confirmPaymentModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{route('createShipment')}}" id="confirm_payment" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <strong>Confirm Payment</strong>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        Total Amount: <span id="total_amount" class="bold_asif"></span><br>
                        Payment Date: <span id="bank_payment_date" class="bold_asif"></span><br>
                        Payment Time: <span id="bank_payment_time" class="bold_asif"></span><br><br>
                        Payment Slip:

                        <span class="mt-3" id="payment_slip"></span><br>

                        <input type="hidden"  id="order_id_confirm_payment" name="order_id_confirm_payment">

                        <div class="mt-4 text-center">
                            <input class="btn btn-success" style="margin: 0 auto; padding: 5px 10px;" type="button" onclick="confirmPaymentBtn()" value="Confirm" />
                        </div>

                    </div>
                    <div class="modal-footer">
                    </div>
                </form>
            </div>
        </div>
    </div>


    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/typeahead.js@0.11.1/dist/typeahead.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>
        <script src="{{ asset('js/delayKeyup.js?_=' . rand()) }}"></script>

        <script>
            const customerType = '{{ $customerType }}';

            const orderUpdateUrl = '{{ route('order_management.update') }}';
            const orderDatatableUrl = '{{ route('order_management.index') }}';
            const selectTwoShopUrl = '{{ route('shop.select') }}';
            const getCustomerInfoUrl = '{{ route('customer-phone.show') }}';

            const selectTwoCategoriesParentUrl = '{{ route('categories-parent.select') }}';
            const subCategoryGridUrl = '{{ route('order_manage.sub-category-grid.index') }}';

            const productGridTableUrl = '{{ route('order_manage.product-grid.index') }}';
            const getShippingCostsUrl = '{{ route('shipper.shipping-cost.weight') }}';

            const selectProvinceUrl = '{{ route('buyer-page.select-province') }}';
            const selectDistrictUrl = '{{ route('buyer-page.select-district') }}';
            const selectSubDistrictUrl = '{{ route('buyer-page.select-sub-district') }}';
            const selectPostCodeUrl = '{{ route('buyer-page.select-post-code') }}';

            const enterKeyCode = 13;
            const THBSymbol = '{{ currency_symbol('THB') }}';

            const textEditOrder = '{{ __('translation.Update Order') }}';
            const textProcessing = '{{ __('translation.Processing') }}';
            const textCustomerFound = '{{ __('translation.Customer found') }}';
            const textCustomerNotFound = '{{ __('translation.Customer not found. Create new customer.') }}';

            const taxRateValue = {{ $taxRateSetting->tax_rate ?? 0 }};
            const taxEnableYes = {{ $taxEnableYes }};

            var selectedProductsToList = {!! $addedProductCodes->toJson() !!};
            var productSource = {!! $products->toJson() !!};

            var latestShippingId = {{ $shippingItemId }};

            var subTotal = 0;
            var shippingCost = 0;
            var discountTotal = 0;
            var totalAmount = 0;

            var weightTotal = 0;

            const selectedCategory = {
                id: 0,
                name: ''
            };

            const selectedSubCategory = {
                id: 0,
                name: ''
            };

            const subCategoryParams = {
                page: 1,
                search: null
            };

            const shippingAddress = {
                provinceCode: -1,
                districtCode: -1,
                subDistrictCode: -1
            };

            const companyAddress = {
                provinceCode: -1,
                districtCode: -1,
                subDistrictCode: -1
            };

            $('#__shop_idEditOrder').select2({
                placeholder: '- Select Shop -',
                width: 'element',
                ajax: {
                    type: 'GET',
                    url: selectTwoShopUrl,
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                    delay: 500
                }
            });


            $('#__order_statusEditOrder').select2({
                width: 'element'
            });


            $('#__category_idProductGrid').select2({
                width: '100%',
                ajax: {
                    type: 'GET',
                    url: selectTwoCategoriesParentUrl,
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                    delay: 500
                }
            });


            loadSubCategoryGridList = (category, page = 1, search = null) => {
                const { id : categoryId, name : categoryName } = category;

                $.ajax({
                    type: 'GET',
                    url: subCategoryGridUrl,
                    data: {
                        categoryId: categoryId,
                        page: page,
                        search: search
                    },
                    success: function(response) {
                        let subCategories = response.results;
                        let { more } = response.pagination;

                        subCategories.map((subCategory) => {
                            let $templateSubCategoryItemGrid = $('#__templateSubCategoryItemGrid').clone();

                            $templateSubCategoryItemGrid.html(function(index, html) {
                                return html.replaceAll('{id}', subCategory.id)
                            });

                            $templateSubCategoryItemGrid.html(function(index, html) {
                                return html.replaceAll('{name}', subCategory.name)
                            });

                            $templateSubCategoryItemGrid.html(function(index, html) {
                                return html.replaceAll('{image_url}', subCategory.image_url)
                            });

                            $('#__subCategoryGridList').append($templateSubCategoryItemGrid.html());
                        });


                        $('#__subCategoryListLoadMoreWrapper').html(null);
                        if (more === true) {
                            const $templateLoadMoreButton = $('#__templateLoadMoreButtonSubCategoryGrid').clone();
                            subCategoryParams.page += 1;

                            $('#__subCategoryListLoadMoreWrapper').append($templateLoadMoreButton.html());
                        }

                        $('.sub-category__btn-load-more').each(function() {
                            $(this).attr('disabled', false);
                        });
                    },
                    error: function(error) {
                        $('.sub-category__btn-load-more').each(function() {
                            $(this).attr('disabled', false);
                        });

                        console.error(error);
                        alert(`Something went wrong`);
                    }
                });
            }


            loadSubCategoryGridList(selectedCategory);


            $('body').on('click', '.sub-category__btn-load-more', function() {
                $(this).attr('disabled', true);

                const { page, search } = subCategoryParams;
                loadSubCategoryGridList(selectedCategory, page, search);
            });


            $('#__searchSubCategoryGrid').delayKeyup(function() {
                const search = $(this).val();
                const page = 1;

                subCategoryParams.page = page;
                subCategoryParams.search = search;

                $('#__subCategoryGridList').html(null);

                loadSubCategoryGridList(selectedCategory, page, search);
            }, 500);


            const loadProductGridTable = (subCategory) => {
                let { id: subCategoryId } = subCategory;

                $('#__tableProductGrid').DataTable({
                    bDestroy: true,
                    serverSide: true,
                    processing: true,
                    ajax: {
                        type: 'GET',
                        url: productGridTableUrl,
                        data: {
                            categoryId: subCategoryId
                        }
                    },
                    columns: [
                        {
                            name: 'product_image',
                            data: 'product_image'
                        },
                        {
                            name: 'product_details',
                            data: 'product_details'
                        }
                    ],
                    columnDefs: [
                        {
                            targets: [0, 1],
                            className: 'text-left'
                        }
                    ],
                    pagingType: 'numbers'
                });
            }


            $('#__category_idProductGrid').on('select2:select', function(event) {
                const { id, text } = event.params.data;
                selectedCategory.id = id;
                selectedCategory.name = text;

                $('#__searchSubCategoryGrid').val(null);
                $('#__subCategoryGridList').html(null);

                const page = 1;
                const search = null;
                subCategoryParams.page = page;
                subCategoryParams.search = search;

                loadSubCategoryGridList(selectedCategory, page, search);
            });


            $('body').on('click', '.grid--sub-category__item', function() {
                const subCategoryId = $(this).data('id');
                const subCategoryName = $(this).data('name');

                selectedSubCategory.id = subCategoryId;
                selectedSubCategory.name = subCategoryName;

                loadProductGridTable(selectedSubCategory);

                $('#__subCategoryNameDisplayGrid').html(subCategoryName);
                $('#__categoryGridWrapper').addClass('hidden');
                $('#__subCategoryGridWrapper').removeClass('hidden');
            });


            $('#__btnViewSubCategoriesGrid').on('click', function() {
                $('#__subCategoryGridWrapper').addClass('hidden');
                $('#__categoryGridWrapper').removeClass('hidden');
            });


            const selectProductGrid = (el) => {
                let productCode = el.getAttribute('data-code');
                renderProductToList(productCode);

                $('#__modalProductGrid').doModal('close');
            }


            const fetchCustomerDataByPhone = phoneNumber => {
                $.ajax({
                    type: 'GET',
                    url: getCustomerInfoUrl,
                    data: {
                        phoneNumber: phoneNumber,
                        customerType: customerType
                    },
                    success: function(responseJson) {
                        let customerData = responseJson.data;

                        $('#__customer_nameEditOrder').val(customerData.customer_name);
                        $('#__contact_phoneEditOrder').val(customerData.contact_phone);

                        $('#__customer_nameEditOrder').addClass('bg-gray-200').attr('readonly', true);
                        $('#__contact_phoneEditOrder').addClass('bg-gray-200').attr('readonly', true);

                        $('#__fetchCustomerResultMessage').removeClass('hidden');
                        $('#__fetchCustomerResultMessage').find('span')
                            .addClass('text-green-500')
                            .removeClass('text-yellow-500')
                            .html(`${textCustomerFound} : ${customerData.contact_phone}`);

                    },
                    error: function(error) {
                        let responseJson = error.responseJSON;

                        let alertMessage = responseJson.message;

                        if (error.status == 404) {
                            $('#__fetchCustomerResultMessage').removeClass('hidden');
                            $('#__fetchCustomerResultMessage').find('span')
                                .addClass('text-red-500')
                                .removeClass('text-green-500')
                                .html(textCustomerNotFound);

                            $('#__customer_nameEditOrder')
                                .val('')
                                .removeClass('bg-gray-200')
                                .removeAttr('readonly');
                            $('#__customer_nameEditOrder').focus();

                            $('#__contact_phoneEditOrder').val(phoneNumber);
                        }

                        if (error.status != 404) {
                            alert(alertMessage);
                        }

                        throw error;
                    }
                });
            }


            $('#search_contact_phone').on('keyup', function(event) {
                let keyCode = event.keyCode || event.which;

                if (keyCode != enterKeyCode) {
                    $('#__fetchCustomerResultMessage').addClass('hidden');
                    $('#__fetchCustomerResultMessage').find('span')
                        .removeClass('text-red-500 text-gree-500')
                        .html(null);

                    $('#__customer_nameEditOrder').val('');
                    $('#__contact_phoneEditOrder').val('');

                    $('#__customer_nameEditOrder').addClass('bg-gray-200').attr('readonly', true);
                    $('#__contact_phoneEditOrder').addClass('bg-gray-200').attr('readonly', true);
                }
            });


            $('#search_contact_phone').on('keypress', function(event) {
                let keyCode = event.keyCode || event.which;

                if (keyCode == enterKeyCode) {
                    let contactPhone = $(this).val();
                    fetchCustomerDataByPhone(contactPhone);

                    return false;
                }
            });


            $('#__btnContactPhone').on('click', function() {
                let contactPhone = $('#search_contact_phone').val();
                fetchCustomerDataByPhone(contactPhone);
            });


            $('.channel-item').on('click', function() {
                let selectedName = $(this).data('name');

                $('.channel-item').each(function() {
                    $(this).removeClass('border-blue-500')
                        .addClass('border-gray-300');
                });

                $(this).removeClass('border-gray-300')
                    .addClass('border-blue-500');

                $('#__selectedChannelOutput').html(selectedName);
            });


            const substringMatcher = function(strs) {
                return function findMatches(q, cb) {
                    var matches, substringRegex;
                    matches = [];

                    substrRegex = new RegExp(q, 'i');

                    $.each(strs, function(i, str) {
                        if (substrRegex.test(str)) {
                            matches.push(str);
                        }
                    });

                    cb(matches);
                };
            };


            const initializeTypeAheadField = () => {
                $('#__product_id_EditOrder').typeahead('destroy');

                $('#__product_id_EditOrder').typeahead({
                    hint: true,
                    minLength: 1,
                    highlight: true
                }, {
                    source: substringMatcher(productSource)
                });
            }

            initializeTypeAheadField();


            $('#__product_id_EditOrder').on('typeahead:selected', function(event, selectedItem) {
                renderProductToList(selectedItem);
            });


            const renderProductToList = typeAheadValue => {
                let reverseTypeAheadValue = typeAheadValue.split('').reverse().join('');
                let startPosForProductCode = typeAheadValue.length - (reverseTypeAheadValue.indexOf('('));
                let endPosForProductCode = typeAheadValue.indexOf(')', typeAheadValue.length - 1);
                let productCode = typeAheadValue.substring(startPosForProductCode, endPosForProductCode);

                if (startPosForProductCode == 0 && endPosForProductCode == -1) {
                    productCode = typeAheadValue;
                }

                if (productCode !== '') {
                    $.ajax({
                        type: 'GET',
                        data: {
                            product_code: productCode
                        },
                        url: '{{ route('get_qr_code_product') }}',
                        success: function(responseJson) {
                            if (responseJson.status === 1) {
                                $('#error_modal').modal('show');
                            }

                            if (responseJson.status === 3) {
                                let templateProductItemElement = $('#__templateProductItem').clone();
                                let product = responseJson.product;

                                let productQuantity = 0;
                                if (typeof(product.get_quantity.quantity) !== 'undefined') {
                                    productQuantity = product.get_quantity.quantity;
                                }

                                let productWeight = parseFloat(product.weight);
                                if (isNaN(productWeight)) {
                                    productWeight = 0;
                                }

                                let dropshipPrice = parseFloat(product.dropship_price);
                                if (isNaN(dropshipPrice)) {
                                    dropshipPrice = parseFloat(product.price);
                                }

                                if (selectedProductsToList.indexOf(product.product_code) === -1) {
                                    selectedProductsToList.push(product.product_code);

                                    templateProductItemElement.html(function(index, html) {
                                        return html.replace('src="#"', 'src="' + product.image_url + '"');
                                    });

                                    templateProductItemElement.html(function(index, html) {
                                        return html.replaceAll('{product_id}', product.id);
                                    });

                                    templateProductItemElement.html(function(index, html) {
                                        return html.replaceAll('{weight}', productWeight);
                                    });

                                    templateProductItemElement.html(function(index, html) {
                                        return html.replaceAll('{product_name}', product.product_name);
                                    });

                                    templateProductItemElement.html(function(index, html) {
                                        return html.replaceAll('{product_code}', product.product_code);
                                    });

                                    templateProductItemElement.html(function(index, html) {
                                        return html.replaceAll('{price}', product.price);
                                    });

                                    templateProductItemElement.html(function(index, html) {
                                        return html.replaceAll('{priceString}', product.price.toLocaleString());
                                    });

                                    templateProductItemElement.html(function(index, html) {
                                        return html.replaceAll('{dropship_price}', dropshipPrice);
                                    });

                                    templateProductItemElement.html(function(index, html) {
                                        return html.replaceAll('{qty}', productQuantity.toLocaleString());
                                    });

                                    $('#__noProductWrapper').hide();
                                    $('#__productListWrapper').prepend(templateProductItemElement.html());
                                }

                                let $productItemWrapper = $('#__row_ProductItem_' + product.product_code);
                                let $oldPriceDisplayElement = $productItemWrapper.find('.product-old-price');
                                let $dropshippPriceElement = $productItemWrapper.find('.btn-dropship-price');

                                if (customerType == 1 && product.price != dropshipPrice) {
                                    $oldPriceDisplayElement.addClass('line-through');
                                    $dropshippPriceElement.removeClass('hidden');
                                    $dropshippPriceElement.html(`Dropship Price : ${THBSymbol} ${dropshipPrice}`);
                                }

                                if (selectedProductsToList.indexOf(product.product_code) > -1) {
                                    let stockAdjustElement = $(`#__row_ProductItem_${product.product_code} .product-qty__field`);
                                    let currentValue = parseInt(stockAdjustElement.val());

                                    let increasedValue = currentValue + 1;
                                    stockAdjustElement.val(increasedValue);
                                }

                                $('#__product_id_EditOrder').typeahead('destroy');
                                $('#__product_id_EditOrder').val(null);

                                initializeTypeAheadField();
                                $('#__product_id_EditOrder').focus();

                                calculateCartTotal();
                                fetchAvailableShippingMethods();
                            }
                        }
                    });
                }
            }


            $(document).on('keypress', '#__product_id_EditOrder', function(event) {
                let keyboardCode = event.keyCode || event.which;

                if (keyboardCode == 13) { // enter key
                    event.preventDefault();

                    let typeAheadValue = $(this).val();
                    renderProductToList(typeAheadValue);

                    return false;
                }
            });


            $('#__btnFindByGrid').on('click', function() {
                $('#__category_idProductGrid').val('').trigger('change');

                $('#__modalProductGrid').doModal('open');
            });


            $('.__btnCloseModalProductGrid').on('click', function() {
                $('#__modalProductGrid').doModal('close');
            });


            const removeProductItem = el => {
                const productCode = el.getAttribute('data-code');

                selectedProductsToList.splice(selectedProductsToList.indexOf(productCode), 1);

                $(`#__row_ProductItem_${productCode}`).remove();

                if (selectedProductsToList.length === 0) {
                    $('#__noProductWrapper').show();

                    $('#__shippingMethodListWrapper').html(null);
                    $('#__shippingMethodButtonWrapper').hide();
                    $('#__noShippingMethodsWrapper').show();
                }

                calculateCartTotal();
                fetchAvailableShippingMethods();
            }


            $('#__btnClearProductList').click(function() {
                selectedProductsToList = [];

                $('#__productListWrapper').html(null);
                $('#__noProductWrapper').show();
            });


            $('body').on('keyup', '.product-qty__field', function() {
                let productCode = $(this).data('product-code');

                let qtyValue = parseInt($(this).val());
                if (isNaN(qtyValue)) {
                    qtyValue = 0;
                }

                if (qtyValue < 0) {
                    alert('Minimum quantity is 1');

                    $(this).val(1);
                    return;
                }

                calculateCartTotal();
                fetchAvailableShippingMethods();
            });


            $('body').on('change', '.product-qty__field', function() {
                let productCode = $(this).data('product-code');

                let qtyValue = parseInt($(this).val());
                if (isNaN(qtyValue)) {
                    qtyValue = 0;
                }

                /**
                 *  Fixing bug
                 *  When user try to edit by deleting the value first
                 */
                if (qtyValue <= 0) {
                    alert('Minimum quantity is 1');

                    $(this).val(1);
                    return;
                }

                calculateCartTotal();
                fetchAvailableShippingMethods();
            });


            const calculateCartTotal = _ => {
                subTotal = 0;
                shippingCost = 0;
                discountTotal = 0;
                totalAmount = 0;
                weightTotal = 0;

                /**
                 * --------------------------------
                 * Calculate the product section
                 * --------------------------------
                 */
                $('#__productListWrapper').find('.product-id__field').each(function() {
                    let productCode = $(this).data('product-code');
                    let $productItemWrapper = $('#__row_ProductItem_' + productCode);

                    let productPrice = parseFloat($productItemWrapper.find('.product-price__field').val());
                    if (isNaN(productPrice)) {
                        productPrice = 0;
                    }

                    let productQty = parseInt($productItemWrapper.find('.product-qty__field').val());
                    if (isNaN(productQty)) {
                        productQty = 0;
                    }

                    let productWeight = parseInt($productItemWrapper.find('.product-weight__field').val());
                    if (isNaN(productWeight)) {
                        productWeight = 0;
                    }

                    let dropshipPrice = parseFloat($productItemWrapper.find('.dropship-price__field').val());
                    if (isNaN(dropshipPrice)) {
                        dropshipPrice = 0;
                    }

                    let productDiscount;
                    if (customerType == 1) {
                        if (productPrice === dropshipPrice) {
                            productDiscount = 0;
                        } else
                            productDiscount = productPrice - dropshipPrice;
                    }
                    else {
                        productDiscount = parseFloat($productItemWrapper.find('.product-discount__field').val());
                        if (isNaN(productDiscount)) {
                            productDiscount = 0;
                        }
                    }

                    subTotal += productPrice * productQty;
                    discountTotal += productDiscount * productQty;

                    weightTotal += productWeight * productQty;
                });

                /**
                 * ----------------------------------
                 * Calculate the shipping section
                 * ----------------------------------
                 */
                $('#__shippingMethodOuterWrapper').find('.shipping-method__id-radio-field').each(function() {
                    let itemId = $(this).data('id');

                    let $shippingItemWrapper = $('#__shippingMethodItem_' + itemId);
                    let $nameField = $shippingItemWrapper.find('.shiping-method__name-field');
                    let $priceField = $shippingItemWrapper.find('.shiping-method__price-field');
                    let $discountField = $shippingItemWrapper.find('.shiping-method__discount-field');


                    if ($(this).is(':checked')) {
                        let priceValue = parseFloat($priceField.val());
                        if (isNaN(priceValue)) {
                            priceValue = 0;
                        }

                        let discountValue = parseFloat($discountField.val());
                        if (isNaN(discountValue)) {
                            discountValue = 0;
                        }

                        shippingCost += priceValue;
                        discountTotal += discountValue;
                    }
                });


                let taxRateAmount = 0;
                let subTotalAndShippingCost = subTotal + shippingCost;
                if (taxRateValue > 0 && parseInt($('input[name="tax_enable"]:checked').val()) === taxEnableYes) {
                    taxRateAmount = (subTotalAndShippingCost - discountTotal) * taxRateValue / 100;
                }

                totalAmount = subTotalAndShippingCost - discountTotal + taxRateAmount;

                $('#__subTotalCurrency').html(subTotal.toLocaleString());
                $('#__discountCurrency').html(discountTotal.toLocaleString());
                $('#__shippingCostCurrency').html(shippingCost.toLocaleString());
                $('#__taxRateCurrency').html(taxRateAmount.toLocaleString());
                $('#__grandTotalCurrency').html(totalAmount.toLocaleString());
            }


            const fetchAvailableShippingMethods = _ => {
                latestShippingId = 0;

                if (selectedProductsToList.length > 0) {
                    $.ajax({
                        type: 'GET',
                        url: `${getShippingCostsUrl}?weight=${weightTotal}`,
                        beforeSend: function() {
                            $('#__shippingMethodButtonWrapper').hide();
                            $('#__shippingMethodListWrapper').html(null);
                            // $('#__noShippingMethodsWrapper').show();
                        },
                        success: function(responseData) {
                            let data = responseData.data;
                            let shippingCosts = data.shipping_costs;

                            $('#__shippingMethodButtonWrapper').show();

                            if (shippingCosts.length > 0) {
                                $('#__shippingMethodListWrapper').html(null);
                                $('#__noShippingMethodsWrapper').hide();

                                shippingCosts.map((shippingCost, idx) => {
                                    let $templateShippingItem = $('#__templateShippingItem').clone();

                                    latestShippingId = idx;

                                    renderShippingItem({
                                        itemId: latestShippingId,
                                        shippingId: shippingCost.id,
                                        shippingCostName: shippingCost.name,
                                        shippingCostPrice: shippingCost.price,
                                        shipperName: shippingCost.shipper.name
                                    });
                                });
                            }

                            shippingCost = 0;
                        },
                        error: function(error) {
                            alert(`Something went wrong with shipping cost`);
                        }
                    });
                }
            }


            $('body').on('click', 'input[name="shipping_method_id"]', function() {
                let selectedShippingId = $(this).val();
                let $shippingItemWrapper = $('#__shippingMethodItem_' + selectedShippingId);

                let $shippingCostPriceElement = $shippingItemWrapper.find('.shiping-method__price-field');
                let shippingCost = parseFloat($shippingCostPriceElement.val());
                if (isNaN(shippingCost)) {
                    shippingCost = 0;
                }

                totalAmount = subTotal + shippingCost - discountTotal;

                $('#__shippingCostCurrency').html(shippingCost.toLocaleString());
                $('#__grandTotalCurrency').html(totalAmount.toLocaleString());
            });


            /**
             * Product Discount
             */
            $('body').on('click', '.btn-product-discount', function() {
                if (customerType != 1) {
                    let productCode = $(this).data('product-code');

                    let $productItemWrapper = $('#__row_ProductItem_' + productCode);
                    let $productPriceElement = $productItemWrapper.find('.product-price__field');
                    let $productDiscountElement = $productItemWrapper.find('.product-discount__field');

                    let originPriceValue = $productPriceElement.val();
                    let discountPriceValue = parseFloat($productDiscountElement.val());
                    if (isNaN(discountPriceValue)) {
                        discountPriceValue = 0;
                    }

                    let discountPriceModalField = '';
                    if (discountPriceValue > 0) {
                        discountPriceModalField = originPriceValue - discountPriceValue;
                    }

                    $('#__modalAddDiscount').doModal('open');

                    $('#__currentPriceAddDiscount').val(originPriceValue);
                    $('#__discountPriceAddDiscount').val(discountPriceModalField);
                    $('#__discountPriceAddDiscount').focus();

                    $('#__productCodeAddDiscount').val(productCode);
                }
            });


            $('#__btnCancelModalAddDiscount').on('click', function() {
                $('#__modalAddDiscount').doModal('close');

                $('#__currentPriceAddDiscount').val('');
                $('#__discountPriceAddDiscount').val('');
                $('#__productCodeAddDiscount').val('');
            });


            $('body').on('click', '#__btnSaveModalAddDiscount', function() {
                let productCode = $('#__productCodeAddDiscount').val();

                let $productItemWrapper = $('#__row_ProductItem_' + productCode);
                let $oldPriceDisplayElement = $productItemWrapper.find('.product-old-price');
                let $productPriceElement = $productItemWrapper.find('.product-price__field');
                let $productDiscountElement = $productItemWrapper.find('.product-discount__field');
                let $btnAddDiscountElement = $productItemWrapper.find('.btn-product-discount');

                let productOldPrice = parseFloat($productPriceElement.val());

                let discountPrice = parseFloat($('#__discountPriceAddDiscount').val());
                if (isNaN(discountPrice)) {
                    discountPrice = 0;
                }

                if (productOldPrice - 1 < discountPrice) {
                    alert(`Maximum discount price for this product is ${THBSymbol} ${productOldPrice - 1}`);
                    return;
                }

                if (discountPrice < 0) {
                    alert(`Minimum discount price is ${THBSymbol} 0.`);
                    return;
                }

                productDiscountValue = productOldPrice - discountPrice;

                $oldPriceDisplayElement.removeClass('line-through');
                $btnAddDiscountElement.html(`Discount Price`);
                if (discountPrice >= 0) {
                    $oldPriceDisplayElement.addClass('line-through');
                    $btnAddDiscountElement.html(`Discount Price : ${THBSymbol} ${discountPrice}`);
                }

                $productDiscountElement.val(productDiscountValue);
                calculateCartTotal();

                $('#__modalAddDiscount').doModal('close');

                $('#__currentPriceAddDiscount').val('');
                $('#__discountPriceAddDiscount').val('');
                $('#__productCodeAddDiscount').val('');
            });


            $('body').on('click', '#__btnResetDiscountModalAddDiscount', function() {
                let productCode = $('#__productCodeAddDiscount').val();

                let $productItemWrapper = $('#__row_ProductItem_' + productCode);
                let $oldPriceDisplayElement = $productItemWrapper.find('.product-old-price');
                let $productPriceElement = $productItemWrapper.find('.product-price__field');
                let $productDiscountElement = $productItemWrapper.find('.product-discount__field');
                let $btnAddDiscountElement = $productItemWrapper.find('.btn-product-discount');

                let productOldPrice = parseFloat($productPriceElement.val());

                $oldPriceDisplayElement.removeClass('line-through');
                $btnAddDiscountElement.html(`Discount Price`);

                $productDiscountElement.val(0);
                calculateCartTotal();

                $('#__modalAddDiscount').doModal('close');

                $('#__currentPriceAddDiscount').val('');
                $('#__discountPriceAddDiscount').val('');
                $('#__productCodeAddDiscount').val('');
            });


            /*
            * -----------------------
            * Shipping Method Price
            * -----------------------
            */
            $('body').on('click', '.shipping-method__btn-product-discount', function() {
                let shippingId = $(this).data('id');

                let $shippingItemWrapper = $('#__shippingMethodItem_' + shippingId);
                let $shippingPriceElement = $shippingItemWrapper.find('.shiping-method__price-field');
                let $shippingDiscountPriceElement = $shippingItemWrapper.find('.shiping-method__discount-field');

                let originalShippingCost = $shippingPriceElement.val();
                let discountShippingCost = $shippingDiscountPriceElement.val();
                if (isNaN(discountShippingCost)) {
                    discountShippingCost = 0;
                }

                let discountCostFieldModal = '';
                if (discountShippingCost > 0) {
                    discountCostFieldModal = originalShippingCost - discountShippingCost;
                }

                $('#__modalAddShippingDiscount').doModal('open');

                $('#__currentPriceAddShippingDiscount').val(originalShippingCost);
                $('#__discountPriceAddShippingDiscount').val(discountCostFieldModal);

                $('#__discountPriceAddShippingDiscount').focus();

                $('#__shippingIdAddShippingDiscount').val(shippingId);
            });


            $('#__btnCancelModalAddShippingDiscount').on('click', function() {
                $('#__modalAddShippingDiscount').doModal('close');

                $('#__currentPriceAddShippingDiscount').val('');
                $('#__discountPriceAddShippingDiscount').val('');
                $('#__shippingIdAddShippingDiscount').val('');
            });


            $('body').on('click', '#__btnResetDiscountModalAddShippingDiscount', function() {
                let shippingId = $('#__shippingIdAddShippingDiscount').val();

                let $shippingItemWrapper = $('#__shippingMethodItem_' + shippingId);
                let $oldPriceDisplayElement = $shippingItemWrapper.find('.shipping-method__price-display');
                let $productPriceElement = $shippingItemWrapper.find('.shiping-method__price-field');
                let $productDiscountElement = $shippingItemWrapper.find('.shiping-method__discount-field');
                let $btnAddDiscountElement = $shippingItemWrapper.find('.shipping-method__btn-product-discount');

                let productOldPrice = parseFloat($productPriceElement.val());

                $oldPriceDisplayElement.removeClass('line-through');
                $btnAddDiscountElement.html(`Discount Cost`);

                $productDiscountElement.val(0);
                calculateCartTotal();

                $('#__modalAddShippingDiscount').doModal('close');

                $('#__currentPriceAddShippingDiscount').val('');
                $('#__discountPriceAddShippingDiscount').val('');
                $('#__shippingIdAddShippingDiscount').val('');
            });


            $('body').on('click', '#__btnSaveModalAddShippingDiscount', function() {
                let shippingId = $('#__shippingIdAddShippingDiscount').val();

                let $shippingMethodItemWrapper = $('#__shippingMethodItem_' + shippingId);
                let $oldPriceDisplayElement = $shippingMethodItemWrapper.find('.shipping-method__price-display');
                let $shippingCostElement = $shippingMethodItemWrapper.find('.shiping-method__price-field');
                let $productDiscountElement = $shippingMethodItemWrapper.find('.shiping-method__discount-field');
                let $btnAddDiscountElement = $shippingMethodItemWrapper.find('.shipping-method__btn-product-discount');

                let shippingOldPrice = parseFloat($shippingCostElement.val());

                let discountPrice = parseFloat($('#__discountPriceAddShippingDiscount').val());
                if (isNaN(discountPrice)) {
                    discountPrice = 0;
                }

                if (shippingOldPrice - 1 < discountPrice) {
                    alert(`Maximum discount cost for this shipping is ${THBSymbol} ${shippingOldPrice - 1}`);
                    return;
                }

                if (discountPrice < 0) {
                    alert(`Minimum discount cost is ${THBSymbol} 0.`);
                    return;
                }

                shippingDiscountValue = shippingOldPrice - discountPrice;

                $oldPriceDisplayElement.removeClass('line-through');
                $btnAddDiscountElement.html(`Discount Cost`);
                if (discountPrice >= 0) {
                    $oldPriceDisplayElement.addClass('line-through');
                    $btnAddDiscountElement.html(`Discount Cost : ${THBSymbol} ${discountPrice}`);
                }

                $productDiscountElement.val(shippingDiscountValue);
                calculateCartTotal();

                $('#__modalAddShippingDiscount').doModal('close');

                $('#__currentPriceAddShippingDiscount').val('');
                $('#__discountPriceAddShippingDiscount').val('');
                $('#__shippingIdAddShippingDiscount').val('');
            });


            /**
             * -------------------------------------------
             * Add New Shipping Method
             * -------------------------------------------
             */

            $('#__btnAddNewShippingMethod').on('click', function() {
                $('#__modalNewShippingMethod').doModal('open');
            });


            $('#__btnCancelModalNewShippingMethod').on('click', function() {
                $('#__modalNewShippingMethod').doModal('close');
            });


            $('#__formNewShippingMethod').on('submit', function(event) {
                event.preventDefault();

                let formData = new FormData($(this)[0]);
                latestShippingId += 1;

                renderShippingItem({
                    itemId: latestShippingId,
                    shippingId: 0,
                    shippingCostName: formData.get('name'),
                    shippingCostPrice: parseFloat(formData.get('price')),
                    shipperName: 'Custom'
                }, true);

                calculateCartTotal();

                $('#__modalNewShippingMethod').doModal('close');

                $(this)[0].reset();

                return false;
            });


            const renderShippingItem = ({
                                            itemId = 0,
                                            shippingId,
                                            shippingCostName,
                                            shippingCostPrice,
                                            shipperName
                                        }, isCustomShipping = false) => {
                let $templateShippingItem = $('#__templateShippingItem').clone();

                latestShippingId = itemId;

                $templateShippingItem.html(function(index, html) {
                    return html.replaceAll('{item_id}', itemId);
                });

                $templateShippingItem.html(function(index, html) {
                    return html.replaceAll('{shipping_id}', shippingId);
                });

                $templateShippingItem.html(function(index, html) {
                    return html.replaceAll('{shipping_cost_name}', shippingCostName);
                });

                $templateShippingItem.html(function(index, html) {
                    return html.replaceAll('{shipper_name}', shipperName);
                });

                $templateShippingItem.html(function(index, html) {
                    return html.replaceAll('{price}', shippingCostPrice);
                });

                $templateShippingItem.html(function(index, html) {
                    return html.replaceAll('{priceString}', shippingCostPrice.toLocaleString());
                });


                if (shippingId == 0) {
                    $('#__shippingMethodEnabledListWrapper').append($templateShippingItem.html())
                } else {
                    $('#__shippingMethodListWrapper').append($templateShippingItem.html());
                }


                if (isCustomShipping === true) {
                    addRemoveButtonToShippingMethod(itemId);
                }
            }


            const addRemoveButtonToShippingMethod = itemId => {
                $removeButtonTemplate = $('#__templateRemoveButtonShippingItem').clone();
                $shippingMethodItem = $('#__shippingMethodItem_' + itemId);

                $removeButtonTemplate.html(function(index, html) {
                    return html.replaceAll('{item_id}', itemId);
                });

                $shippingMethodItem
                    .find('.shipping-method__content-wrapper')
                    .append(
                        $removeButtonTemplate.html()
                    );
            }


            $('body').on('change', '.shipping-method__id-radio-field', function() {
                let itemId = $(this).data('id');
                let $shippingItemWrapper = $('#__shippingMethodItem_' + itemId);
                let $selectedInputField = $shippingItemWrapper.find('.shiping-method__selected-input-field');

                $('.shiping-method__selected-input-field').each(function() {
                    $(this).val(0);
                });

                $selectedInputField.val(0);
                if ($(this).is(':checked')) {
                    $selectedInputField.val(1);
                }

                calculateCartTotal();
            });


            $('body').on('click', '.shiping-method__enable-checkbox-field', function() {
                let itemId = $(this).data('id');

                let $shippingItemWrapper = $('#__shippingMethodItem_' + itemId);
                let $shippingRadioField = $shippingItemWrapper.find('.shipping-method__id-radio-field');
                let $shippingIdField = $shippingItemWrapper.find('.shipping-method__id-input--field');
                let $nameField = $shippingItemWrapper.find('.shiping-method__name-field');
                let $priceField = $shippingItemWrapper.find('.shiping-method__price-field');
                let $discountField = $shippingItemWrapper.find('.shiping-method__discount-field');
                let $selectedInputField = $shippingItemWrapper.find('.shiping-method__selected-input-field');

                $shippingRadioField.prop('checked', false);

                $shippingRadioField.attr('disabled', true);
                $shippingIdField.attr('disabled', true);
                $nameField.attr('disabled', true);
                $priceField.attr('disabled', true);
                $discountField.attr('disabled', true);
                $selectedInputField.attr('disabled', true);

                $selectedInputField.val(0);


                if ($(this).is(':checked')) {
                    $shippingRadioField.attr('disabled', false);
                    $shippingIdField.attr('disabled', false);
                    $nameField.attr('disabled', false);
                    $priceField.attr('disabled', false);
                    $discountField.attr('disabled', false);
                    $selectedInputField.attr('disabled', false);
                }

                calculateCartTotal();
            });


            $('body').on('click', '.shipping-method__btn-remove-item', function() {
                let itemId = $(this).data('id');

                $('#__modalRemoveShippingItem').doModal('open');

                $('#__btnYesModalRemoveShippingItem').attr('data-id', itemId);
            });


            $('#__btnNoModalRemoveShippingItem').on('click', function() {
                $('#__btnYesModalRemoveShippingItem').removeAttr('data-id');

                $('#__modalRemoveShippingItem').doModal('close');
            });


            $('#__btnYesModalRemoveShippingItem').on('click', function() {
                let itemId = $(this).data('id');

                $('#__shippingMethodItem_' + itemId).remove();

                calculateCartTotal();

                $('#__modalRemoveShippingItem').doModal('close');
            });

            /***
             *
             * Store the data
             * Using ajax
             *
             */
            $('#__formEditOrder').on('submit', function(event) {
                event.preventDefault();

                let formData = new FormData($(this)[0]);

                $.ajax({
                    type: 'POST',
                    url: orderUpdateUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('.alert').addClass('hidden');
                        $('#__alertSuccessContent').html(null);
                        $('#__alertDangerContent').html(null);

                        $('#__btnCancelEditOrder').attr('disabled', true);
                        $('#__btnSubmitEditOrder').attr('disabled', true).html(textProcessing);
                    },
                    success: function(responseData) {
                        let orderResult = responseData.data;

                        $('html, body').animate({
                            scrollTop: 0
                        }, 500);

                        $('#__alertSuccessContent').html(responseData.message);
                        $('#__alertSuccess').removeClass('hidden');

                        setTimeout(() => {
                            window.location.href = orderDatatableUrl;
                        }, 1000);
                    },
                    error: function(error) {
                        let responseJson = error.responseJSON;

                        $('html, body').animate({
                            scrollTop: 0
                        }, 500);

                        $('#__btnCancelEditOrder').attr('disabled', false);
                        $('#__btnSubmitEditOrder').attr('disabled', false).html(textEditOrder);

                        if (error.status == 422) {
                            let errorFields = Object.keys(responseJson.errors);
                            errorFields.map(field => {
                                $('#__alertDangerContent').append(
                                    $('<span/>', {
                                        class: 'block mb-1',
                                        html: `- ${responseJson.errors[field][0]}`
                                    })
                                );
                            });

                        } else {
                            $('#__alertDangerContent').html(responseJson.message);

                        }

                        $('#__alertDanger').removeClass('hidden');
                    }
                });

                return false;
            });


            $('#__btnCancelEditOrder').on('click', function() {
                $('#__modalCancelEditOrder').doModal('open');
            });


            $('#__btnCloseModalCancelEditOrder').on('click', function() {
                $('#__modalCancelEditOrder').doModal('close');
            });


            var copyBuyerLinkOrderCreated = new ClipboardJS('#__btnCopyBuyerLinkOrderCreated');
            copyBuyerLinkOrderCreated.on('success', function(event) {
                alert('URL Copied.');
                event.clearSelection();
            });


            var copyBuyerLink = new ClipboardJS('#__btnCopyBuyerLink');
            copyBuyerLink.on('success', function(event) {
                alert('URL Copied.');
                event.clearSelection();
            });


            $('body').on('click', '#__publicUrlOrderCreated', function() {
                let publicUrl = $(this).data('href');
                window.open(publicUrl, '_blank');
            });

            $('body').on('click', '#__btnEditOrderCreated', function() {
                let editUrl = $(this).data('href');
                window.location.href = editUrl;
            });

            $('body').on('click', '#make_payment', function() {
                
                $("#make_manual_payment_modal").modal('show');
                $('#make_new_manual_payment')[0].reset();
                $("#pending_total").val('฿ ' + $("#total_manual_pending_input").val());
                $("#pending_total_input").val($("#total_manual_pending_input").val());
                $("#total_paid_in_modal").text('฿ ' + $("#total_manual_payment_input").val());
                $("#total_pending_in_modal").text('฿ ' +$("#total_manual_pending_input").val());
            });


            function makeNewPayment() {
                var pending_total_input = $("#pending_total_input").val();
                var payment_amount = $("#payment_amount").val();
                var total_manual_payment_input = $("#total_manual_payment_input").val();

                var order_id = $("#id").val();
                var is_confirmed = $("#is_confirmed").val();
                var payment_method = $("#payment_method").val();
                var is_refund = $('input[name="is_refund"]:checked').val();

                if (is_refund === '0') {
                    if (Number(payment_amount) > Number(pending_total_input)) {
                        alert("Must less than pending amount !");
                        return;
                    }
                }
                if (!(payment_amount)) {
                    alert("Must be filled Out !");
                    return;
                }
                if (is_refund === '1') {
                    if (Number(payment_amount) > Number(total_manual_payment_input)) {
                        alert("Must less than Paid amount !");
                        return;
                    }
                }

                $.ajax({
                    type: 'POST',
                    data: {
                        payment_amount: payment_amount,
                        order_id: order_id,
                        is_confirmed: is_confirmed,
                        payment_method: payment_method,
                        is_refund:is_refund
                    },
                    url: '{{ url('makeNewPayment') }}',
                    success: function(result) {
                        $("#make_manual_payment_modal").modal('hide');
                        $("#full_payments_wrapper").html('');
                        $("#full_payments_wrapper").html(result);
                    }
                });
            }

            function updateManualPayment() {

                var payment_id = $("#payment_id_edit").val();
                var pending_total_input = $("#pending_total_input").val();
                var payment_amount = $("#edit_payment_amount").val();

                var order_id = $("#id").val();
                var is_confirmed = $("#edit_is_confirmed").val();
                var payment_method = $("#edit_payment_method").val();
                var is_refund = $('input[name="edit_is_refund"]:checked').val();

                var total_manual_payment_input = $("#total_manual_payment_input").val();
                if (!(payment_amount)) {
                    alert("Must be filled Out !");
                    return;
                }
                if (is_refund === '0') {
                    if (Number(payment_amount) > Number(pending_total_input)) {
                        alert("Must less than pending amount !");
                        return;
                    }
                }
                if (is_refund === '1') {
                    if (Number(payment_amount) > Number(total_manual_payment_input)) {
                        alert("Must less than Paid amount !");
                        return;
                    }
                }

                //alert(order_id);

                $.ajax({
                    type: 'POST',
                    data: {
                        payment_id: payment_id,
                        payment_amount: payment_amount,
                        order_id: order_id,
                        is_confirmed: is_confirmed,
                        payment_method: payment_method,
                        is_refund:is_refund
                    },
                    url: '{{ url('updateManualPayment') }}',
                    success: function(result) {
                        $("#make_manual_payment_edit_modal").modal('hide');
                        $("#full_payments_wrapper").html('');
                        $("#full_payments_wrapper").html(result);
                    }
                });
            }


            $("body").on('change', '#is_confirmed', function() {
                var is_confirmed = $("#is_confirmed").val();
                //alert(is_confirmed);
                if (is_confirmed === '0') {
                    $(".full_paid_checkbox_wrapper").hide();
                }
                if (is_confirmed === '2') {
                    $(".full_paid_checkbox_wrapper").hide();
                }
                if (is_confirmed === '1') {
                    $(".full_paid_checkbox_wrapper").show();
                }

            });


            $('body').on('click', '#BtnUpdateManualPayment', function() {

                var payment_id = $(this).data('id');
                $("#make_manual_payment_edit_modal").modal('show');

                $("#payment_id_edit").val(payment_id);
                $("#edit_total_paid_in_modal").text('฿ ' + $("#total_manual_payment_input").val());
                $("#edit_total_pending_in_modal").text('฿ ' +$("#total_manual_pending_input").val());
                $.ajax({
                    type: 'GET',
                    data: {
                        payment_id: payment_id
                    },
                    url: '{{ url('getManualPaymentData') }}',
                    success: function(result) {
                        console.log(result);
                        $("#edit_payment_amount").val(result.paymentDetails.amount);
                        $("#edit_payment_method").val(result.paymentDetails.payment_method);

                        if (result.paymentDetails.is_refund === 1) {
                            document.getElementById("edit_refund").checked = true;
                        }
                        if (result.paymentDetails.is_refund === 0) {
                            document.getElementById("edit_payment").checked = true;
                        }

                    },
                    error: function() {
                        alert('something went wrong');
                    }
                });

            });

            $(document).ready(function() {
                var orders_order_status = $("#orders_order_status").val();
                if (orders_order_status === '3' || orders_order_status === '4' || orders_order_status === '5') {
                    $('.locked_editing').show();
                    $("#__formEditOrder :input").prop("disabled", true);
                    $("#__btnCancelEditOrder").prop("disabled", false);
                    $("#make_payment_btn_wrapper").hide();
                    $("#public_url_actions_wrapper").hide();
                } else {
                    $('.locked_editing').hide();
                    $("#make_payment_btn_wrapper").show();
                    $("#public_url_actions_wrapper").show();
                }
            });

            $('body').on('click', '#orderDetailsBtn', function() {
                $("#payment_details_wrapper").hide();
                $("#order_details_wrapper").show();
                $("#shipment_details_wrapper").hide();
                $("#custom_shipment_details_wrapper").hide();
            });

            $('body').on('click', '#paymentDetailsBtn', function() {
                $("#payment_details_wrapper").show();
                $("#order_details_wrapper").hide();
                $("#shipment_details_wrapper").hide();
                $("#custom_shipment_details_wrapper").hide();
            });

            $('body').on('click', '#shipmentDetailsBtn', function() {
                $("#shipment_details_wrapper").show();
                $("#order_details_wrapper").hide();
                $("#payment_details_wrapper").hide();
                $("#custom_shipment_details_wrapper").hide();
                var order_id = $("#id").val();
                $.ajax({
                    type: 'GET',
                    data: {
                        order_id: order_id
                    },
                    url: '{{ url('getShipmentDetailsData') }}',
                    beforeSend: function() {
                        $("#shipment_details_wrapper").html("Loading ....");
                    },
                    success: function(result) {
                        //alert(result);
                        $("#shipment_details_wrapper").html(result);
                        //$("#manual_payment_" + payment_id).remove();
                    },
                    error: function() {
                        alert('Something went wrong');
                    }
                });

            });

            $('body').on('click', '#customShipmentDetailsBtn', function() {
                $("#custom_shipment_details_wrapper").show();
                $("#order_details_wrapper").hide();
                $("#payment_details_wrapper").hide();
                $("#shipment_details_wrapper").hide();
                var order_id = $("#id").val();
                $.ajax({
                    type: 'GET',
                    data: {
                        order_id: order_id
                    },
                    url: '{{ url('getCustomShipmentDetailsData') }}',
                    beforeSend: function() {
                        $("#custom_shipment_details_wrapper").html("Loading ......");
                    },
                    success: function(result) {
                        $("#custom_shipment_details_wrapper").html(result);

                    },
                    error: function() {
                        alert('Something went wrong');
                    }
                });

            });


            $('body').on('click', '#BtnDeleteManualPayment', function() {
                let drop = confirm('Are you sure?');
                if (drop) {
                    var payment_id = $(this).data('id');
                    var order_id = $("#id").val();

                    $.ajax({
                        type: 'GET',
                        data: {
                            payment_id: payment_id,
                            order_id: order_id
                        },
                        url: '{{ url('delManualPaymentData') }}',
                        success: function(result) {
                            //alert(result);
                            $("#manual_payment_" + payment_id).remove();
                            $("#full_payments_wrapper").html('');
                            $("#full_payments_wrapper").html(result);
                        },
                        error: function() {
                            alert('Something went wrong');
                        }
                    });
                }
            });

            $('body').on('click', '.chnage_payment_status', function() {
                $("#chnage_payment_status_modal").modal('show');
                var current_status = $("#bank_is_confirmed").val();

                if (current_status === 1) {
                    $("#change_payment_status_btn").hide();
                }
                if (current_status === 0) {
                    $("#change_payment_status_btn").show();
                }
            });

            function change_payment_status() {
                var current_status = $("#bank_is_confirmed").val();
                var payment_id = $("#payment_id_input").val();
                var order_id = $("#id").val();
                $.ajax({
                    type: 'GET',
                    data: {
                        payment_id: payment_id,
                        current_status: current_status,
                        order_id: order_id
                    },
                    url: '{{ url('changeBankPaymentStatus') }}',
                    success: function(result) {
                        //alert(result);
                        $("#chnage_payment_status_modal").modal('hide');
                        $("#full_payments_wrapper").html('');
                        $("#full_payments_wrapper").html(result);

                    },
                    error: function() {
                        alert('something went wrong');
                    }
                });
            }

            $('body').on('change', '#__order_statusEditOrder',
                function(){
                    var this_value = $(this).val();
                    $("#take_action_value_input").val(this_value);
                    $('#__modalTakeAction').doModal('open');
                    if(this_value == '0'){
                        $("#your_text").html("Do your want to cancel this Order?");
                        $("#pay_link_wrapper").hide();
                        $("#cancel_processing_wrapper").hide();
                        $("#change_payment_method_wrapper").hide();
                        $("#cancel_wrapper").show();
                        $("#yes_continue_wrapper").show();
                    }
                    if(this_value == '1'){
                        $("#your_text").html("Do your want to open pay link page?");
                        $("#pay_link_wrapper").show();
                        $("#yes_continue_wrapper").hide();
                        $("#cancel_processing_wrapper").hide();
                        $("#change_payment_method_wrapper").hide();
                        $(".__btnCloseModalContinueTakeAction").hide();
                    }
                    if(this_value == '2'){
                        $("#pay_link_wrapper").hide();
                        $("#cancel_processing_wrapper").hide();
                        $("#change_payment_method_wrapper").hide();
                        $("#yes_continue_wrapper").show();
                        $("#your_text").html("Do your want to change the status to Processing?");
                    }
                    if(this_value == '3'){
                        $("#pay_link_wrapper").show();
                        $("#cancel_wrapper").show();
                        $("#yes_continue_wrapper").hide();
                        $("#cancel_processing_wrapper").hide();
                        $("#change_payment_method_wrapper").hide();
                        $(".__btnCloseModalContinueTakeAction").hide();
                        $("#your_text").html("Do your want to change the Payment Method?");
                    }
                    if(this_value == '4'){
                        $("#cancel_wrapper").hide();
                        $("#pay_link_wrapper").hide();
                        $("#change_payment_method_wrapper").hide();
                        $("#cancel_processing_wrapper").show();
                        $("#yes_continue_wrapper").show();
                        $("#your_text").html("Do your want to change the status to Processing?");
                    }
                    if(this_value == '5'){
                        $('#__modalTakeAction').doModal('close');
                        confirmPaymentModal();
                    }
                });

            $('#__btnCloseModalConfirm').on('click', function() {
                $('#__modalTakeAction').doModal('close');
            });

            $('#__btnCloseModalCancelTakeAction').on('click', function() {
                $('#__modalTakeAction').doModal('close');
            });

            $('.__btnCloseModalContinueTakeAction').on('click', function() {

                var order_id = $("#id").val();
                var this_value=  $("#take_action_value_input").val();
                $.ajax({
                    type: 'POST',
                    data: {
                        action_value: this_value,
                        order_id: order_id

                    },
                    url: '{{ url('order_take_action') }}',
                    beforeSend: function() {
                        $(".__btnCloseModalContinueTakeAction").html("Processing ......");
                    },
                    success: function(result) {
                        console.log(result);
                        $('#__modalTakeAction').doModal('close');
                        var url = "{{ url('/order_management/') }}";
                        window.location.href = url+'/'+order_id+'/edit';
                    }
                });
            });

            $('#cancel_processing_wrapper').on('click', function() {
                var order_id = $("#id").val();
                $.ajax({
                    type: 'POST',
                    data: {
                        action_value: 9,
                        order_id: order_id
                    },
                    url: '{{ url('order_take_action') }}',
                    beforeSend: function() {
                        $("#__btnCloseModalContinueCancelProcessing").html("Processing ......");
                    },
                    success: function(result) {
                        console.log(result);
                        $('#__modalTakeAction').doModal('close');
                        var url = "{{ url('/order_management/') }}";
                        window.location.href = url+'/'+order_id+'/edit';
                    }
                });
            });

            $('#btnPayLinkTakeAction').on('click', function() {
                var generated_order_id = $("#generated_order_id").val();
                var url = "{{ url('/order_management_buyer/') }}";
                var new_location = url+'/'+generated_order_id;
                window.open(new_location, '_blank');
            });

            function confirmPaymentModal(){
                $("#confirmPaymentModal").modal('show');
                var order_id = $("#id").val();

                $.ajax
                ({
                    type: 'GET',
                    data: {order_id:order_id},
                    url: '{{url('getOrderPaymentDetails')}}',
                    success: function(result)
                    {
                        var payment_slip = '{{asset('')}}'+result.payment_slip;

                        $("#total_amount").text('฿'+result.amount);
                        $("#bank_payment_date").text(result.payment_date);
                        $("#bank_payment_time").text(result.payment_time);
                        $("#payment_slip").text('');
                        $("#payment_slip").append('<img width="100%" src="'+payment_slip+'">');
                    }
                });
            }

            function confirmPaymentBtn(){
                var result = confirm("Are you Sure?");
                if (result) {
                    var order_id = $("#id").val();
                    $.ajax
                    ({
                        type: 'POST',
                        data: {order_id:order_id},
                        url: '{{url('confirmPaymentForOrder')}}',
                        success: function(result)
                        {
                            if(result === 'ok'){
                                $("#confirmPaymentModal").modal('hide');
                                alert("Payment has been successfully confirmed");
                                var url = "{{ url('/order_management/') }}";
                                window.location.href = url+'/'+order_id+'/edit';
                            }
                        }
                    });
                }
            }

            // for shipment details table

            var order_id = $("#id").val();
            dataTables("{{ route('all_shipment_list_for_order') }}?order_id=" + order_id);
            var datatable;

            function dataTables(url) {
                // Datatable
                datatable = $('#yajra_datatable').DataTable({
                    processing: true,
                    // responsive: true,
                    serverSide: true,
                    columnDefs : [
                        {
                            'targets': 0,
                            'checkboxes': {
                                'selectRow': true
                            }
                        }
                    ],
                    order: [[1, 'desc']],
                    ajax: url,
                    columns: [
                        {
                            name: 'checkbox',
                            data: 'checkbox'
                        },
                        {data: 'shipment_data', name: 'shipment_data'},
                    ]
                });
            }
        </script>

        <script src="{{ asset('pages/seller/order_management/edit/shipping_address.js?_=' . rand()) }}"></script>
        <script src="{{ asset('pages/seller/order_management/edit/tax_invoice.js?_=' . rand()) }}"></script>
        <script src="{{ asset('pages/seller/order_management/edit/btn_print_pdf.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
