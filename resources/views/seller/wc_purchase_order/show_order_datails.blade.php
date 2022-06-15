<x-app-layout>
    @section('title')
        {{ __('translation.Order') . ' #' . $orderManagement->order_id }}
    @endsection

    @push('top_css')
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
        <link rel="stylesheet" href="{{ asset('css/typeaheadjs.css') }}">
        <link rel="stylesheet" href="{{ asset('css/custom_css.css') }}">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    @endpush


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
                Payment Details #{{$orderManagement->order_id}}
            </x-card.title>
        </x-card.header>
        <div class="col-lg-12 tabs" id="full_payments_wrapper">
            
            <div id="system_payments_wrapper mt-4" class="padding_tops">
                <div class="mt-4">
                    <h6 class="mt-4"><strong>System Payment : </strong></h6>
                    <table class="table mt-2 dt-responsive nowrap tbl_border">
                        <thead>
                        <tr class="bg-gray-200">
                            <th scope="col">{{ __('translation.Details') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(isset($orderManagement))
                            <tr>
                                <td>
                                    Method : <br>
                                    <strong>

                                        @if($orderManagement->payment_method)
                                           {{ $orderManagement->payment_method_title }}
                                        @endif

                                    </strong><br>
                                    Amount:<strong> ฿ {{$orderManagement->total}}</strong><br>
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
        </div>
    </div>


    <div id="shipment_details_wrapper" class="card btn_card col-span-12">
    <x-section.section>
        <x-section.body>
        <x-section.title>
            {{ __('translation.Shipments') }}
        </x-section.title>

            @if ($orderProductDetails)
                <div id="__productListWrapper">

                <table class="table text-center tbl_border" id="shipments_table">
                    <thead>
                    <tr class="bg-blue-500 text-white align-self-sm-baseline">
                        <th>Product Name</th>
                        <th>translation.Ordered</th>
                        <th>Quantity</th>
                        <th>translation.Remaining</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white">
                    @php $cart_total = 0; @endphp
                    @foreach ($orderProductDetails as $detail)
                    <tr>
                        <td class="text-left">
                        <div>
                        <span>{{ $detail->name }}</span> <br>
                        <span class="text-blue-500">{{ $detail->sku }}</span>
                        </div>
                        </td>
                        <td>{{ $detail->quantity }} </td>
                        <td>
                        <div>
                        <strong>-</strong>
                        </div>
                        </td>
                        <td>
                         -
                        </td>
                        </tr>
                        
                        @php 
                            $cart_total +=$detail->price;
                         @endphp
                    @endforeach
                    </tbody>
                </table>
                </div>
            @endif

            @if($orderManagement->status=='processing')
            <div id="add_new_shipment_div" class="text-center mb-8">
                <a id="add_new_shipment" class="h-8 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-600 outline-none focus:outline-none focus:border-green-600 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"  color="green">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
                        <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path>
                    </svg>
                    <span class="ml-2">
                    Arrange Shipment
                    </span>
                </a>
            </div>
            @endif


            </x-section.body>
        </x-section.section>    
    </div>


    <div id="custom_shipment_details_wrapper" class="card btn_card col-span-12"></div>
    <div class="col-span-12 custom_to_padding" id="order_details_wrapper">
        <x-card.card-default>
            <x-card.header>
                <x-card.back-button href="{{ route('order_management.index') }}" />
                <x-card.title>
                    <span class="text_left_asif">{{ __('translation.Order') . ' #' . $orderManagement->order_id }}</span>
                </x-card.title>
            </x-card.header>
            <x-card.body>


                <div class="mb-5">

                    <div class="flex flex-row items-center justify-center lg:justify-end gap-2">
                        <x-button-outline type="button" color="green" data-url="{{ route('wc-order-purchase.quotation.pdf', [ 'order_id' => $orderManagement->order_id, 'shop_id' => $shop_id]) }}" class="btn-print-pdf">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-download" viewBox="0 0 16 16">
                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                            </svg>
                            <span class="ml-2">
                                Quotation
                            </span>
                        </x-button-outline>

                            <x-button-outline type="button" color="green" data-url="{{ route('wc-order-purchase.invoice.pdf', [ 'order_id' => $orderManagement->order_id, 'shop_id' => $shop_id ]) }}" class="btn-print-pdf">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-download" viewBox="0 0 16 16">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                </svg>
                                <span class="ml-2">
                                    Invoice
                                </span>
                            </x-button-outline>
                            
                    </div>
                </div>

                <form action="#" method="post" id="__formEditOrder" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="website_id" id="website_id" value="{{ $shop_id }}">
                    <input type="hidden" name="id" id="id" value="{{ $orderManagement->order_id }}">
                    <input type="hidden" id="orders_order_status" value="{{ $orderManagement->status }}">
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
                                   
                                        <x-select name="shop_id" id="__shop_idEditOrder" disabled>
                                            <option value="{{ $shopDetails->id }}" selected>
                                                {{ $shopDetails->name }}
                                            </option>
                                        </x-select>
                                </div>
                                <div class="lg:col-span-2">
                                    <x-label for="__order_statusEditOrder">
                                        {{ __('translation.Order Status') }}
                                    </x-label>
                                    <strong class="mt-2 pt-2 uppercase "> {{ $orderManagement->status }} </strong><br>
                                    <button type="button" data-order_id="{{$orderManagement->order_id}}" data-id="{{$orderManagement->id}}" id="BtnUpdateStatus" class="btn  shipment_btns  btn btn-primary btn-sm action_btns mt-2 letter-spacing-1">
                                    <i class="fa fa-shipping-fast mr-1" aria-hidden="true"></i>{{ __('translation.UPDATE ORDER STATUS') }}</button>
                                </div>

                                <div class="lg:col-span-2">
                                    <x-label for="__order_statusEditOrder">
                                        Payment Status
                                    </x-label>
                                    <strong class="mt-2 pt-2">
                                       
                                    </strong>
                                </div>

                              
                            </div>
                        </x-section.body>
                    </x-section.section>



                    <x-section.section>
                        <x-section.title>
                            {{ __('translation.Customer Info') }}
                        </x-section.title>
                        <x-section.body>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-x-8">
                                <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-x-8">
                                    <div id="__customerNameWrapper">
                                        <x-label for="__customer_nameEditOrder">
                                            {{ __('translation.Customer Name') }} <x-form.required-mark />
                                        </x-label>
                                        <x-input type="text" name="customer_name" id="__customer_nameEditOrder" class="bg-gray-200" value="{{ $shipping['shipping_name'] }}" readonly />
                                    </div>
                                    <div id="__contactPhoneWrapper">
                                        <x-label for="__contact_phoneEditOrder">
                                            {{ __('translation.Phone Number') }} <x-form.required-mark />
                                        </x-label>
                                        <x-input type="text" name="contact_phone" id="__contact_phoneEditOrder" class="bg-gray-200" value="{{ $shipping['shipping_phone'] }}" readonly />
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
                                            @if($shipping['shipping_name'])
                                                <x-input type="text" name="shipping_name" id="__shipping_nameEditOrder" value="{{ $shipping['shipping_name'] }}" class="bg-gray-200" readonly />
                                             @endif
                                        </div>
                                        <div>
                                            <x-label for="__shipping_phoneEditOrder">
                                                {{ __('translation.Phone Number') }} <x-form.required-mark />
                                            </x-label>
                                            @if($shipping['shipping_phone'])
                                                <x-input type="text" name="shipping_phone" id="__shipping_phoneEditOrder" value="{{ $shipping['shipping_phone'] }}" class="bg-gray-200" readonly />
                                            @endif
                                        </div>
                                        <div class="sm:col-span-2 md:col-span-1 lg:col-span-2">
                                            <x-label for="__addressEditOrder">
                                                {{ __('translation.Shipping Address') }} <x-form.required-mark />
                                            </x-label>
                                            @if($shipping['shipping_address_1'])
                                                <x-form.textarea name="shipping_address" id="__addressEditOrder" rows="4" class="bg-gray-200" readonly>{{ $shipping['shipping_address_1'] }}</x-form.textarea>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-x-8">
                                        <div>
                                            <x-label for="__shipping_cityEditOrder">
                                                {{ __('translation.City') }} <x-form.required-mark />
                                            </x-label>
                                            @if($shipping['shipping_city'])
                                                <x-select name="shipping_city" id="__shipping_cityEditOrder" style="width: 100%" disabled>
                                                    <option value="{{ $shipping['shipping_city'] }}" selected>
                                                        {{ $shipping['shipping_city'] }}
                                                    </option>
                                                </x-select>
                                            @endif
                                        </div>

                                        <div>
                                            <x-label for="__shipping_provinceEditOrder">
                                                {{ __('translation.Province') }} <x-form.required-mark />
                                            </x-label>
                                            @if($shipping['shipping_state'])
                                                <x-select name="shipping_province" id="__shipping_provinceEditOrder" style="width: 100%" disabled>
                                                    <option value="{{ $shipping['shipping_state'] }}" selected>
                                                        {{ $shipping['shipping_state'] }}
                                                    </option>
                                                </x-select>
                                            @endif
                                        </div>


                                        <div class="sm:col-span-2 md:col-span-1 lg:col-span-2">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-x-8">
                                                
                                                <div>
                                                    <x-label for="__shipping_postcodeEditOrder">
                                                        {{ __('translation.Postal Code') }} <x-form.required-mark />
                                                    </x-label>
                                                    @if (!empty($shipping['shipping_postcode']))
                                                        <x-select name="shipping_postcode" id="__shipping_postcodeEditOrder" style="width: 100%" disabled>
                                                            <option value="{{ $shipping['shipping_postcode'] }}" selected>
                                                                {{ $shipping['shipping_postcode'] }}
                                                            </option>
                                                        </x-select>
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
                        <x-section.body>
                        <x-section.title>
                            {{ __('translation.Products') }}
                        </x-section.title>

                            @if ($orderProductDetails)
                                <div id="__productListWrapper">
                                @php $cart_total = 0; @endphp;
                                    @foreach ($orderProductDetails as $detail)
                                        <div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200" id="__row_ProductItem_{{ $detail->sku }}">
                                            <div class="w-1/4 sm:w-1/4 md:w-1/5 lg:w-1/6 mb-4 md:mb-0">
                                                <div class="mb-4">
                                                    @php 
                                                    $images = [];
                                                    $quantity = 0;
                                                    if(!empty($arrProductImageWithID[$detail->product_id])){
                                                        $product = $arrProductImageWithID[$detail->product_id];
                                                        $images = json_decode($product->images);
                                                        $quantity = $product->quantity;
                                                    }
                                                    @endphp

                                                    @if(isset($images[0]->src))
                                                        <img src="{{ $images[0]->src}}" alt="{{ $detail->name }}" class="w-full h-auto rounded-md">
                                                    @else
                                                        <img src="{{asset('No-Image-Found.png')}}" class="w-full h-auto rounded-md">
                                                    @endif
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
                                                                {{ $detail->name }} <br>
                                                                <span class="text-gray-700">{{ $detail->sku }}</span>
                                                            </p>
                                                        </div>
                                                        <div>
                                                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 lg:gap-x-8">
                                                                <div>
                                                                    <label class="mb-0 lg:block">
                                                                        {{ __('translation.Price') }} :
                                                                    </label>

                                                                    @if ($detail->price)
                                                                        <span class="font-bold product-old-price">
                                                                            {{ currency_symbol('THB') }}
                                                                            {{ currency_number($detail->price, 3) }}
                                                                        </span>
                                                                    @endif
                                                                    
                                                                </div>
                                                                
                                                                <div>
                                                                    <label class="mb-0">
                                                                        {{ __('translation.Available Qty') }} :
                                                                    </label>
                                                                    <span class="font-bold lg:block">
                                                                        {{ number_format($quantity) }}
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
                                                                @if($detail->quantity)
                                                                    <x-input type="number" name="product_qty[]" value="0" min="1" class="product-qty__field" data-product-code="{{ $detail->sku }}" value="{{ $detail->quantity }}" class="bg-gray-200" readonly />
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @php $cart_total +=$detail->price; @endphp;
                                    @endforeach
                                </div>
                            @endif

                            <div id="__noProductWrapper" @if ($orderProductDetails) style="display: none" @endif>
                                <div class="w-full py-4 rounded-lg text-center">
                                    <span class="font-bold text-base text-gray-500">
                                        --- {{ __('translation.No Product Added') }} ---
                                    </span>
                                </div>
                            </div>
                        </x-section.body>
                    </x-section.section>

                    <x-section.section class="xl:mb-12">
                        <x-section.title>
                            {{ __('translation.Shipping Methods') }}
                        </x-section.title>
                        <x-section.body>
                            <div id="__templateShippingItem">
                                <div class="mb-4 pb-3 border border-t-0 border-r-0 border-l-0 border-dashed border-gray-300" id="__shippingMethodItem_{item_id}">
                                    <div class="flex flex-row items-start">
                                        <div class="ml-2">
                                        @php 
                                        $total_tax = 0; 
                                        @endphp
                                            @foreach($shipping_lines as $line)
                                                <div class="flex flex-col lg:flex-row lg:items-center shipping-method__content-wrapper">
                                                    <div class="flex flex-col sm:flex-row sm:items-center">
                                                        <div class="mb-2 sm:mb-0">
                                                            <label for="__shipping_method_{$line->method_title}" class="ml-1">
                                                                {{$line->method_title}}
                                                            </label>
                                                        </div>
                                                        <div class="hidden sm:block ml-6">
                                                            -
                                                        </div>
                                                        <div class="sm:ml-2">
                                                            <span class="font-bold shipping-method__price-display">
                                                                {{ currency_symbol('THB') }} {{$line->total}}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                @php 
                                                $total_tax +=$line->total_tax; 
                                                @endphp
                                            @endforeach
                                        </div>
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
                                                    {{ currency_number($orderManagement->total, 3) }}
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
                                                    {{ currency_number(0, 3) }}
                                                </span>
                                            </td>
                                        </tr>
                                      
                                        <tr id="__taxRateRowCartTotals">
                                            <td class="pr-3 py-1">
                                              TAX
                                            </td>
                                            <td class="py-1">
                                                <span class="text-white">-</span>
                                                <span class="font-bold">
                                                    {{ currency_symbol('THB') }}
                                                </span>
                                            </td>
                                            <td class="pl-3 py-1 text-right">
                                                <span class="font-bold" id="__taxRateCurrency">
                                                    {{ currency_number($total_tax, 3) }}
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
                                                    {{ currency_number(($orderManagement->total+$total_tax), 3) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </x-section.body>
                    </x-section.section>

            </x-card.body>
        </x-card.card-default>

    </div>


     <x-modal.modal-small class="modal-hide modal-status">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Status') }}
            </x-modal.title>
            <x-modal.close-button id="btnCancelModalStatus"/>
        </x-modal.header>
        <x-modal.body>
             <div class="pb-6">
                <div id="woo_order_status"></div>
                <form style="max-height:90vh" action="" id="form-status" enctype="multipart/form-data"></form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

    
    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/typeahead.js@0.11.1/dist/typeahead.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>
        <script src="{{ asset('js/delayKeyup.js?_=' . rand()) }}"></script>

        <script>
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

            const taxRateValue = 0;
            const taxEnableYes = 0;

            var selectedProductsToList = '';
            var productSource ='';

            var latestShippingId = '';

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
                        phoneNumber: phoneNumber
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
                                        return html.replaceAll('{qty}', productQuantity.toLocaleString());
                                    });

                                    $('#__noProductWrapper').hide();
                                    $('#__productListWrapper').prepend(templateProductItemElement.html());
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

                    let productDiscount = parseFloat($productItemWrapper.find('.product-discount__field').val());
                    if (isNaN(productDiscount)) {
                        productDiscount = 0;
                    }

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

                    subTotal += productPrice * productQty;
                    discountTotal += productDiscount * productQty;

                    weightTotal += productWeight * productQty;
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
                var website_id = $("#website_id").val();
                var order_id = $("#id").val();
                $.ajax({
                        type: 'GET',
                        data: {
                            website_id: website_id,
                            order_id: order_id
                        },
                        url: '{{ url('getWCShipmentDetailsData') }}',
                        beforeSend: function() {
                            $("#shipment_details_wrapper").html("Loading ....");
                        },
                        success: function(result) {
                            $("#shipment_details_wrapper").html(result);
                        },
                        error: function() {
                            alert('Something went wrong...');
                        }
                    });

            });

            $('body').on('click', '#customShipmentDetailsBtn', function() {
                $("#custom_shipment_details_wrapper").show();
                $("#order_details_wrapper").hide();
                $("#payment_details_wrapper").hide();
                $("#shipment_details_wrapper").hide();
                var order_id = $("#id").val();
                var website_id = $("#website_id").val();
                $.ajax({
                        type: 'GET',
                        data: {
                            order_id: order_id,
                            website_id : website_id
                        },
                        url: '{{ url('getWCCustomShipmentDetailsData') }}',
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




            $('#__btnCloseModalConfirm').on('click', function() {
                $('#__modalTakeAction').doModal('close');
            });

             $('#__btnCloseModalCancelTakeAction').on('click', function() {
                $('#__modalTakeAction').doModal('close');
            });

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

        $(document).on('click', '#BtnUpdateStatus', function() {
            $('.modal-status').removeClass('modal-hide');
           

            $.ajax({
            type: 'GET',
                url: '{{url('woo-data-order-status')}}',
                data: {id:$(this).data('id')},
            beforeSend: function() {
                 $('#woo_order_status').html('Loading');
            },
            success: function(responseData) {
                console.log(responseData);
                $('#woo_order_status').html(responseData);
                
            },
          error: function(error) {

          }
        });
        });

        $(document).ready(function() {
            $('#btnCancelModalStatus').click(function() {
                $('.modal-status').doModal('close');
            });
        });

        $(document).on('click', '#BtnSubmitChangeStatus', function() {
                $('.f').removeClass('modal-hide');
                var status = $("#status").val();
                var order_id = $(this).data('order_id');
               
                $.ajax({
                    url: '{{ route('wc_change_order_purchase_status') }}',
                    type: 'post',
                    data: {
                        'id': $(this).data('id'),
                        'website_id': $(this).data('website_id'),
                        'order_id': $(this).data('order_id'),
                        'status': $("#status").val(),
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                    beforeSend: function() {
                        $('#BtnSubmitChangeStatus').html("Processing...");
                    }
                }).done(function(result) {
                   
                    //wooOrderPurchaseTable.cell(row_index, 9).data(status);
                    $('#messageStatusSubmit').html(result);
                    $('.modal-status').doModal('close');
                    Swal.fire({
                        toast: true,
                        icon: 'success',
                        title: 'Succcess',
                        text: '{{__('translation.You updated successfully')}}',
                        timerProgressBar: true,
                        timer: 2000,
                        position: 'top-end'
                    });
                    var url = "{{ url('/wc-order-purchase-details/') }}";
                    window.location.href = url+'/'+order_id;
                });
            });


            

        $(document).on('click', '#delete_new_shipment', function() {
            var orderId = $("#id").val();
            var shipment_id = $(this).data('id');
            var shop_id = $(this).data('shop_id');
            $('#__modalCancelShipment').doModal('open');
            $("#shipment_id_value").val(shipment_id);
            $("#shop_id_value").val(shop_id);
        });

        $('#__btnCloseModalCancelShipment').on('click', function() {
            $('#__modalCancelShipment').doModal('close');
            $('#__btnCloseModalCancelShipment').addClass('hidden');
        });

        $(document).on('click', '#__btnCloseModalFinalDeleleShipment', function() {
            var orderId = $("#id").val();
            var website_id = $("#website_id").val();  
            var shipment_id = $("#shipment_id_value").val();
            $.ajax({
                type: 'GET',
                url: '{{url('deleteWCShipmentForOrder')}}',
                data: {orderId:orderId,website_id:website_id, shipment_id:shipment_id},
                beforeSend: function() {
                    $("#shipment_details_wrapper").html("Loading ......");
                },
                success: function(responseData) {
                    var order_id = $("#__order_id_displayCreateShipment").val();
                    console.log(website_id);
                    $.ajax({
                        type: 'GET',
                        data: {
                                order_id: orderId,
                                website_id : website_id
                        },
                        url: '{{ url('getWCShipmentDetailsData') }}',
                        
                        success: function(result) {
                            $("#shipment_details_wrapper").html(result);
                           
                        }
                    });

                    Swal.fire({
                        toast: true,
                        icon: 'success',
                        title: 'Success',
                        text: 'Shipment Deleted',
                        timerProgressBar: true,
                        timer: 2000,
                        position: 'top-end'
                    });
                            
                    $('#__modalCancelShipment').doModal('close');
                },
                error: function(error) {
                    alert('Something went wrong');
                }
            });
        });
        

        </script>

        <script src="{{ asset('pages/seller/order_management/edit/shipping_address.js?_=' . rand()) }}"></script>
        <script src="{{ asset('pages/seller/order_management/edit/tax_invoice.js?_=' . rand()) }}"></script>
        <script src="{{ asset('pages/seller/order_management/edit/btn_print_pdf.js?_=' . rand()) }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
    @endpush

</x-app-layout>
