<x-app-layout>

    @section('title')
        {{ __('translation.Create Order') }}
    @endsection

    @push('top_css')
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
        <link rel="stylesheet" href="{{ asset('css/typeaheadjs.css') }}">
        <link rel="stylesheet" href="{{ asset('css/custom_css.css') }}">
    @endpush

    @if (in_array('Can access menu: Order Management', session('assignedPermissions')))

    <div class="col-span-12">
        <x-card.card-default>
            <x-card.header>
                <x-card.back-button href="{{ route('order_management.index') }}" />
                <x-card.title>
                    @if($customerType == '0')
                        {{ __('translation.Create New Order') }}
                    @else
                        {{ __('translation.Create New Dropshipper Order') }}
                    @endif
                    <a class="btn btn-success ml-3" id="import_order">
                    <span class="">
                        <i class="fa fa-file-import mr-1" aria-hidden="true"></i>
                        Import
                    </span>
                </a>
                </x-card.title>

            </x-card.header>
            <x-card.body>

                <x-alert-danger class="mb-6 alert hidden" id="__alertDanger">
                    <div id="__alertDangerContent"></div>
                </x-alert-danger>

                <form action="#" method="post" id="__formCreateOrder" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="order_id" value="{{ $order_id }}">
                    <input type="hidden" name="customer_type" value="{{ $customerType }}">

                    @if($customerType == '0')
                    <x-section.section>
                        <x-section.title>
                            {{ __('translation.Order Info') }}
                        </x-section.title>
                        <x-section.body>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-4 sm:gap-x-8">
                                <div class="sm:col-span-2 lg:col-span-3">
                                    <x-label for="__shop_idCreateOrder">
                                        {{ __('translation.Shop') }} <x-form.required-mark/>
                                    </x-label>
                                    <x-select name="shop_id" id="__shop_idCreateOrder" style="width: 100%"></x-select>
                                </div>
                            </div>
                        </x-section.body>
                    </x-section.section>
                    @endif

                        <x-section.section>
                        <x-section.title>
                            {{ __('translation.Channel Info') }}
                        </x-section.title>
                        <x-section.body>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-x-8">
                                <div>
                                    <x-label for="channel_id">
                                        {{ __('translation.Channel Name') }} <x-form.required-mark/>
                                    </x-label>
                                    <div class="w-full mt-2 grid grid-cols-6 gap-4">
                                        <!-- @php $images = ''; @endphp -->
                                        @if($customerType == '0')
                                        <?php
                                            

                                            ?>
                                            @foreach ($channels as $idx => $channel)
                                              @if (Storage::disk('s3')->exists($channel->image) && !empty($channel->image)) 
                                                @php $images = Storage::disk('s3')->url($channel->image); @endphp

                                                @else
                                                 @php $images = Storage::disk('s3')->url('uploads/No-Image-Found.png'); @endphp
                                                @endif

                                                @if ($idx == 0)
                                              
                                                    <label for="channel_{{ $channel->id }}" class="channel-item block w-10 h-10 p-1 rounded-md border border-solid border-blue-500 hover:border-blue-500 cursor-pointer transition duration-300" data-name="{{ $channel->name }}">
                                                        <input type="radio" name="channel_id" id="channel_{{ $channel->id }}" value="{{ $channel->id }}" class="hidden" checked>
                                                        <div class="w-full h-full rounded-md bg-no-repeat bg-cover" style="background-image: url('{{ $images }}')"></div>
                                                    </label>
                                                @else


                                                    <label for="channel_{{ $channel->id }}" class="channel-item block w-10 h-10 p-1 rounded-md border border-solid border-gray-300 hover:border-blue-500 cursor-pointer transition duration-300" data-name="{{ $channel->name }}">
                                                        <input type="radio" name="channel_id" id="channel_{{ $channel->id }}" value="{{ $channel->id }}" class="hidden">
                                                        <div class="w-full h-full rounded-md bg-no-repeat bg-cover" style="background-image: url('{{ $images }}')"></div>
                                                    </label>
                                                @endif
                                            @endforeach
                                        @else
                                        
                                            <label for="channel_{{ $channels->id }}" class="block w-10 h-10 p-1 rounded-md border border-solid border-blue-500 hover:border-blue-500 cursor-pointer transition duration-300" data-name="{{ $channels->name }}">
                                                <input type="radio" name="channel_id" id="channel_{{ $channels->id }}" value="{{ $channels->id }}" class="hidden" checked>
                                                <div class="w-full h-full rounded-md bg-no-repeat bg-cover" style="background-image: url('{{ $images }}')"></div>
                                            </label>
                                        @endif
                                    </div>
                                    <div class="mt-3">
                                        <span class="text-gray-500">Selected Channel : </span>
                                        @if($customerType == '0')
                                            <span class="font-bold text-blue-500" id="__selectedChannelOutput">

                                            </span>
                                        @else
                                            <span class="font-bold text-blue-500" id="__selectedChannelOutput">
                                                {{ $channels->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if($customerType == '0')
                                    <div>
                                    <x-label for="contact_name">
                                        {{ __('translation.Channel ID') }} <x-form.required-mark/>
                                    </x-label>
                                            <x-input type="text" name="contact_name" id="contact_name" />
                                    </div>
                                @endif
                            </div>
                        </x-section.body>
                    </x-section.section>

                    <x-section.section class="xl:mb-12">
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
                                        {{ __('translation.Search Phone Number') }} <x-form.required-mark/>
                                    </x-label>
                                    <div class="flex flex-row items-center justify-between">
                                        <x-input type="text" id="search_contact_phone" class="rounded-tr-none rounded-br-none" />
                                        <x-button type="button" color="blue" class="rounded-tl-none rounded-bl-none relative" id="__btnContactPhone">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                            </svg>
                                        </x-button>
                                    </div>
                                    <div class="mt-2 hidden" id="__fetchCustomerResultMessage">
                                        <span class="font-bold"></span>
                                    </div>
                                </div>
                                <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-x-8">
                                    <div id="__customerNameWrapper">
                                        <x-label for="__customer_nameCreateOrder">
                                            {{ __('translation.Customer Name') }} <x-form.required-mark/>
                                        </x-label>
                                        <x-input type="text" name="customer_name" id="__customer_nameCreateOrder" class="bg-gray-200" readonly />
                                    </div>
                                    <div id="__contactPhoneWrapper">
                                        <x-label for="__contact_phoneCreateOrder">
                                            {{ __('translation.Phone Number') }} <x-form.required-mark/>
                                        </x-label>
                                        <x-input type="text" name="contact_phone" id="__contact_phoneCreateOrder" class="bg-gray-200" readonly />
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
                                    {{ __('translation.Customer Name') }}
                                </x-label>
                                <x-input type="text" name="shipping_name" id="__shipping_nameEditOrder" value="" />

                            </div>
                            <div>
                                <x-label for="__shipping_phoneEditOrder">
                                    {{ __('translation.Phone Number') }}
                                </x-label>

                                <x-input type="text" name="shipping_phone" id="__shipping_phoneEditOrder" value="" />

                            </div>
                            <div class="sm:col-span-2 md:col-span-1 lg:col-span-2">
                                <x-label for="__addressEditOrder">
                                    {{ __('translation.Shipping Address') }} 
                                </x-label>

                                <x-form.textarea name="shipping_address" id="__addressEditOrder" rows="4"></x-form.textarea>

                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-x-8">
                            <div>
                                <x-label for="__shipping_provinceEditOrder">
                                    {{ __('translation.Province') }} 
                                </x-label>
                                <x-select name="shipping_province" id="__shipping_provinceEditOrder" style="width: 100%" class="mt-1">

                                </x-select>

                            </div>
                            <div>
                                <x-label for="__shipping_districtEditOrder">
                                    {{ __('translation.District') }}
                                </x-label>


                                <x-select name="shipping_district" id="__shipping_districtEditOrder" style="width: 100%">

                                </x-select>

                            </div>
                            <div class="sm:col-span-2 md:col-span-1 lg:col-span-2">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-x-8">
                                    <div>
                                        <x-label for="__shipping_sub_districtEditOrder">
                                            {{ __('translation.Sub District') }}
                                        </x-label>

                                        <x-select name="shipping_sub_district" id="__shipping_sub_districtEditOrder" style="width: 100%">

                                        </x-select>

                                    </div>
                                    <div>
                                        <x-label for="__shipping_postcodeEditOrder">
                                            {{ __('translation.Postal Code') }}
                                        </x-label>

                                        <x-select name="shipping_postcode" id="__shipping_postcodeEditOrder" style="width: 100%">

                                        </x-select>

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
                            <x-button-sm type="button" color="red" class="ml-3 relative -top-1" id="__btnClearProductList">
                                {{ __('translation.Reset') }}
                            </x-button-sm>
                        </x-section.title-with-button>
                        <x-section.body>
                            <div>
                                <div class="mb-6 flex flex-row items-center justify-between">
                                    <div class="w-full sm:w-full">
                                        <x-input type="text" id="__product_id_CreateOrder" placeholder="Enter Product Name or Code" autocomplete="off" />
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

                                <hr class="w-full border border-dashed border-r-0 border-b-0 border-l-0 border-blue-500 mb-5">

                                <div id="__productListWrapper"></div>

                                <div id="__noProductWrapper">
                                    <div class="w-full py-4 rounded-lg text-center">
                                        <span class="font-bold text-base text-gray-500">
                                            --- {{ __('translation.No Product Added') }} ---
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </x-section.body>
                    </x-section.section>

                    <x-section.section>
                        <x-section.title>
                            {{ __('translation.Shipping Methods') }}
                        </x-section.title>
                        <x-section.body>

                            <div id="__shippingMethodButtonWrapper" class="mb-7" style="display: none">
                                <x-button-sm type="button" color="blue" id="__btnAddNewShippingMethod">
                                    <i class="fas fa-plus"></i>
                                    <span class="ml-2">
                                        Add New
                                    </span>
                                </x-button-sm>
                            </div>

                            <div id="__shippingMethodListWrapper"></div>

                            <div id="__noShippingMethodsWrapper">
                                <div class="w-full py-4 rounded-lg text-center">
                                    <span class="font-bold text-base text-gray-500">
                                        --- {{ __('translation.No Shipping Methods Available') }} ---
                                    </span>
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
                                <label for="__tax_enable_CreateOrder" class="block mb-2">
                                    {{ __('translation.Request Tax') }} <x-form.required-mark/>
                                </label>
                                <div class="flex flex-row gap-x-4">
                                    @foreach ($taxEnableValues as $value => $text)
                                        @if ($value == $taxEnableNo)
                                            <x-form.input-radio name="tax_enable" id="__tax_enable_{{ $value }}CreateOrder" value="{{ $value }}" checked="true">
                                                {{ $text }}
                                            </x-form.input-radio>
                                        @else
                                            <x-form.input-radio name="tax_enable" id="__tax_enable_{{ $value }}CreateOrder" value="{{ $value }}">
                                                {{ $text }}
                                            </x-form.input-radio>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-8" id="__taxCompanyInfoWrapper" style="display:none">
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">
                                        <div>
                                            <x-label for="__company_nameCreateOrder">
                                                {{ __('translation.Company Name') }}
                                            </x-label>
                                            <x-input type="text" name="company_name" id="__company_nameCreateOrder" />
                                        </div>
                                        <div>
                                            <x-label for="__tax_numberCreateOrder">
                                                {{ __('translation.Tax Number') }}
                                            </x-label>
                                            <x-input type="text" name="tax_number" id="__tax_numberCreateOrder" />
                                        </div>
                                        <div>
                                            <x-label for="__company_phone_numberCreateOrder">
                                                {{ __('translation.Phone Number') }}
                                            </x-label>
                                            <x-input type="text" name="company_phone_number" id="__company_phone_numberCreateOrder" />
                                        </div>
                                        <div>
                                            <x-label for="__company_contact_nameCreateOrder">
                                                {{ __('translation.Contact Name') }}
                                            </x-label>
                                            <x-input type="text" name="company_contact_name" id="__company_contact_nameCreateOrder" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">
                                        <div class="sm:col-span-2">
                                            <x-label for="__company_addressCreateOrder">
                                                {{ __('translation.Address') }}
                                            </x-label>
                                            <x-form.textarea name="company_address" id="__company_addressCreateOrder" rows="3"></x-form.textarea>
                                        </div>
                                        <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-x-8">
                                            <div>
                                                <x-label for="__company_provinceCreateOrder">
                                                    {{ __('translation.Province') }}
                                                </x-label>
                                                <x-select name="company_province" id="__company_provinceCreateOrder" style="width: 100%"></x-select>
                                            </div>
                                            <div>
                                                <x-label for="__company_districtCreateOrder">
                                                    {{ __('translation.District') }}
                                                </x-label>
                                                <x-select name="company_district" id="__company_districtCreateOrder" style="width: 100%" disabled></x-select>
                                            </div>
                                            <div>
                                                <x-label for="__company_sub_districtCreateOrder">
                                                    {{ __('translation.Sub-District') }}
                                                </x-label>
                                                <x-select name="company_sub_district" id="__company_sub_districtCreateOrder" style="width: 100%" disabled></x-select>
                                            </div>
                                            <div>
                                                <x-label for="__company_postcodeCreateOrder">
                                                    {{ __('translation.Postal Code') }}
                                                </x-label>
                                                <x-select name="company_postcode" id="__company_postcodeCreateOrder" style="width: 100%" disabled></x-select>
                                            </div>
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
                                                    0
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="pr-3 py-1">
                                                Shipping Price
                                            </td>
                                            <td class="py-1">
                                                <span class="text-white">-</span>
                                                <span class="font-bold">
                                                    {{ currency_symbol('THB') }}
                                                </span>
                                            </td>
                                            <td class="pl-3 py-1 text-right">
                                                <span class="font-bold" id="__shippingCostCurrency">
                                                    0
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
                                                    0
                                                </span>
                                            </td>
                                        </tr>
                                        <tr id="__taxRateRowCartTotals" style="display: none">
                                            <td class="pr-3 py-1">
                                                {{ $taxRateSetting->tax_name ?? '' }} (<span id="__taxRateCartTotal">{{ currency_number($taxRateSetting->tax_rate ?? 0, 2) . '%' }}</span>)
                                            </td>
                                            <td class="py-1">
                                                <span class="text-white">-</span>
                                                <span class="font-bold">
                                                    {{ currency_symbol('THB') }}
                                                </span>
                                            </td>
                                            <td class="pl-3 py-1 text-right">
                                                <span class="font-bold" id="__taxRateCurrency">
                                                    0
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
                                                    0
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </x-section.body>
                    </x-section.section>

                    <div class="text-center pb-4">
                        <x-button type="button" color="gray" class="mr-1" id="__btnCancelCreateOrder">
                            {{ __('translation.Cancel') }}
                        </x-button>
                        <x-button type="submit" color="blue" id="__btnSubmitCreateOrder">
                            {{ __('translation.Create Order') }}
                        </x-button>
                    </div>
                <input type="hidden" name="tax_vat_amount" id="tax_vat_amount">
                </form>
            </x-card.body>
        </x-card.card-default>
    </div>

    @endif

    <x-modal.modal-small class="modal-hide" id="__modalCancelCreateOrder">
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
            <div class="pb-3 text-center">
                <x-button type="button" color="gray" id="__btnCloseModalCancelCreateOrder">
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


    <div class="hidden" id="__templateProductItem">
        <div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200" id="__row_ProductItem_{product_code}">
            <input type="hidden" name="product_id[]" value="{product_id}" class="product-id__field" data-product-code="{product_code}">
            <input type="hidden" name="product_price[]" value="{price}" class="product-price__field" data-product-code="{product_code}">
            <input type="hidden" name="product_discount[]" value="0" min="0" max="{price}" step="0.001" class="product-discount__field" data-product-code="{product_code}">
            <input type="hidden" name="product_orginal_discount[]" value="0" min="0" max="{price}" step="0.001" class="product-orginal-discount__field" data-product-code="{product_code}">
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
                                    {{ __('translation.Order Qty') }} <x-form.required-mark/> :
                                </label>
                                <x-input type="number" name="product_qty[]" value="0" min="1" class="product-qty__field" data-product-code="{product_code}" />
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
                    <input type="hidden" name="shipping_method_discount_price[]" value="0" class="shiping-method__discount-price-field">
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
                                    {{ __('translation.Discount Price') }}
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
                                {{ __('translation.Original Price') }} <x-form.required-mark/> :
                            </label>
                            <x-input type="number" id="__currentPriceAddDiscount" min="0" step="0.001" class="bg-gray-200" readonly />
                        </div>
                        <div>
                            <label class="mb-0">
                                {{ __('translation.Discount Price') }} <x-form.required-mark/> :
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
                {{ __('translation.Shipping Discount Price') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">

                <input type="hidden" id="__shippingIdAddShippingDiscount">

                <div class="mb-5">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="mb-0">
                                {{ __('translation.Original Price') }} <x-form.required-mark/> :
                            </label>
                            <x-input type="number" id="__currentPriceAddShippingDiscount" min="0" step="0.001" class="bg-gray-200" readonly />
                        </div>
                        <div>
                            <label class="mb-0">
                                {{ __('translation.Discount Price') }} <x-form.required-mark/> :
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
                            <x-select name="category_id" id="__category_idProductGrid" style="width: 100%">
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
                                    {{ __('translation.Name') }} <x-form.required-mark/> :
                                </label>
                                <x-input type="text" name="name" id="__nameNewShippingMethod" autocomplete="off" required />
                            </div>
                            <div>
                                <label class="mb-0">
                                    {{ __('translation.Cost') }} <x-form.required-mark/> :
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


    <x-modal.modal-medium id="__modalOrderCreated" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Order Created') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">

                <div class="mb-5">
                    <div class="grid grid-cols-12 gap-2 py-2">
                        <div class="col-span-3">
                            {{ __('translation.Order ID') }}
                        </div>
                        <div class="col-span-1">:</div>
                        <div class="col-span-8">
                            <span class="font-bold" id="__orderIdOrderCreated"></span>
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-2 py-2">
                        <div class="col-span-3">
                            {{ __('translation.Buyer Link') }}
                        </div>
                        <div class="col-span-1">:</div>
                        <div class="col-span-8">
                            <p class="font-bold text-blue-500 underline cursor-pointer break-new-line" id="__publicUrlOrderCreated"></p>
                        </div>
                    </div>
                </div>
                <div class="mb-5">
                    <table class="w-full -mt-1">
                        <tbody>
                            <tr>
                                <td class="pr-3 py-1 w-1/2">
                                    Sub Total
                                </td>
                                <td class="py-1 w-6">
                                    <span class="text-white">-</span>
                                    <span class="font-bold">
                                        {{ currency_symbol('THB') }}
                                    </span>
                                </td>
                                <td class="pl-3 py-1 text-right">
                                    <span class="font-bold" id="__subTotalOrderCreated">
                                        0
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
                                    <span class="font-bold" id="__shippingCostOrderCreated">
                                        0
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
                                    <span class="font-bold" id="__discountTotalOrderCreated">
                                        0
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
                                    <span class="font-bold text-red-500" id="__totalAmountOrderCreated">
                                        0
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-center">
                    <x-button-link href="{{ route('order_management.index') }}" color="red" class="mb-1">
                        {{ __('translation.Close') }}
                    </x-button-link>
                   
                    <x-button type="button" color="yellow" id="__btnCopyBuyerLinkOrderCreated" class="mb-1" data-clipboard-text="#">
                        {{ __('translation.Copy Buyer Link') }}
                    </x-button>
                </div>

            </div>
        </x-modal.body>
    </x-modal.modal-medium>

    <x-modal.modal-small class="modal-hide" id="__searchShopeeOrders">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Search Shopee Order') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <div class="mb-5">
                <div class="form-group">
                     <x-label for="orders_option">
                       Select
                    </x-label>
                    <x-select name="orders_option" id="orders_option">
                        <option value="">Select</option>
                        <option value="shopee">Shopee</option>
                    </x-select>
                </div>
               <div class="form-group hide" id="shopee_order_no_div">
                   <x-label for="shopee_order_no">
                       Order No
                    </x-label>
                    <x-input type="text" name="shopee_order_no" id="shopee_order_no" placeholder="Order No" />
                </div> 
                <div class="form-group font-bold color-red hide" id="not_found_msg_shown">
                   No data found against this Order ID
                </div> 
            </div>
            <div class="text-center pb-5">
                <x-button type="button" color="gray" id="__btnCloseModalShopeeOrderNo">
                    {{ __('translation.Close') }}
                </x-button>
                <x-button-link color="red" id="__btnCloseModalFinalShopeeOrderNo">
                    {{ __('translation.Continue') }}
                </x-button-link>
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
            const customerType = '{{ $customerType }}';
            const orderStoreUrl = '{{ route('order_management.store') }}';
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

            const textCreateOrder = '{{ __('translation.Create Order') }}';
            const textProcessing = '{{ __('translation.Processing') }}';
            const textCustomerFound = '{{ __('translation.Customer found') }}';
            let textCustomerNotFound = '';

            const taxRateValue = {{ $taxRateSetting->tax_rate ?? 0 }};
            const taxEnableYes = {{ $taxEnableYes }};

            var selectedProductsToList = [];
            var productSource = {!! $products->toJson() !!};

            var latestShippingId = 0;

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

            const companyAddress = {
                provinceCode: -1,
				districtCode: -1,
				subDistrictCode: -1
            };

            const shippingAddress = {
                provinceCode: -1,
                districtCode: -1,
                subDistrictCode: -1
            };


            $(window).on('load', function() {
                $('input[type="text"]').val('');
                $('input[type="number"]').val('');
                $('select').val('');
                $('#__shop_idCreateOrder').val('').trigger('change');
            });


            $('#__shop_idCreateOrder').select2({
                placeholder: '- Select Shop -',
                width: 'resolve',
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


            $('#__category_idProductGrid').select2({
                width: 'resolve',
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

                        $('#__customer_nameCreateOrder')
                            .val(customerData.customer_name)
                            .attr('readonly', true)
                            .addClass('bg-gray-200');
                        $('#__contact_phoneCreateOrder')
                            .val(customerData.contact_phone)
                            .attr('readonly', true)
                            .addClass('bg-gray-200');

                        $('#__fetchCustomerResultMessage').removeClass('hidden');
                        $('#__fetchCustomerResultMessage').find('span')
                                                        .addClass('text-green-500')
                                                        .removeClass('text-red-500')
                                                        .html(`${textCustomerFound} : ${customerData.contact_phone}`);

                    },
                    error: function(error) {
                        let responseJson = error.responseJSON;

                        let alertMessage = responseJson.message;

                        if (customerType == '0'){
                            textCustomerNotFound = '{{ __('translation.Customer not found. Create new customer.') }}';
                        }
                        else
                            textCustomerNotFound = '{{ __('translation.Dropshipper not found. Please create dropshipper first.') }}';

                        if (error.status == 404) {
                            $('#__fetchCustomerResultMessage').removeClass('hidden');
                            $('#__fetchCustomerResultMessage').find('span')
                                                            .addClass('text-red-500')
                                                            .removeClass('text-green-500')
                                                            .html(textCustomerNotFound);

                            if (customerType == '0') {
                                $('#__customer_nameCreateOrder')
                                    .val('')
                                    .attr('readonly', false)
                                    .removeClass('bg-gray-200');
                                $('#__customer_nameCreateOrder').focus();

                                $('#__contact_phoneCreateOrder').val(phoneNumber);
                            }
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

                    $('#__customer_nameCreateOrder')
                        .val('')
                        .attr('readonly', true)
                        .addClass('bg-gray-200');
                    $('#__contact_phoneCreateOrder')
                        .val('')
                        .attr('readonly', true)
                        .addClass('bg-gray-200');
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
                // alert(selectedName);
                // if(selectedName == 'Shopee'){
                //     $('#__searchShopeeOrders').doModal('open');
                // }
                // else{
                //     $('#__searchShopeeOrders').doModal('close');
                // }

                $('.channel-item').each(function() {
                    $(this).removeClass('border-blue-500')
                        .addClass('border-gray-300');
                });

                $(this).removeClass('border-gray-300')
                    .addClass('border-blue-500');

                $('#__selectedChannelOutput').html(selectedName);
            });


            $('#import_order').on('click', function() {
                 $('#__searchShopeeOrders').doModal('open');
                 $("#shopee_order_no_div").addClass('hide');
                 $("#shopee_order_no").val('');

            });

            $('#__btnCloseModalShopeeOrderNo').on('click', function() {
                $('#__searchShopeeOrders').doModal('close');
                
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
                $('#__product_id_CreateOrder').typeahead({
                    hint: true,
                    minLength: 1,
                    highlight: true
                }, {
                    source: substringMatcher(productSource)
                });
            }

            initializeTypeAheadField();


            $('#__product_id_CreateOrder').on('typeahead:selected', function(event, selectedItem) {
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
                                let product_img = responseJson.product_image_url;
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
                                        return html.replace('src="#"', 'src="'+ product_img +'"');
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

                                if (customerType == 'dropshipper' && product.price != dropshipPrice) {
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

                                $('#__product_id_CreateOrder').typeahead('destroy');
                                $('#__product_id_CreateOrder').val(null);
                                initializeTypeAheadField();

                                $('#__product_id_CreateOrder').focus();

                                calculateCartTotal();
                                fetchAvailableShippingMethods();
                            }
                        }
                    });
                }
            }


            $(document).on('keypress', '#__product_id_CreateOrder', function(event) {
                let keyboardCode = event.keyCode || event.which;

                if (keyboardCode == 13) { // enter key
                    event.preventDefault();

                    let typeAheadValue = $(this).val();
                    renderProductToList(typeAheadValue);

                    return false;
                }
            });


            $('#__btnFindByGrid').on('click', function() {
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
                    if (customerType == 'dropshipper') {
                        if (productPrice === dropshipPrice) {
                            productDiscount = 0;
                        } else
                            productDiscount = productPrice - dropshipPrice;
                    }
                    else {
                        productDiscount = parseFloat($productItemWrapper.find('.product-orginal-discount__field').val());
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
                $('#__shippingMethodListWrapper').find('.shipping-method__id-radio-field').each(function() {
                    let itemId = $(this).data('id');

                    let $shippingItemWrapper = $('#__shippingMethodItem_' + itemId);
                    let $nameField = $shippingItemWrapper.find('.shiping-method__name-field');
                    let $priceField = $shippingItemWrapper.find('.shiping-method__price-field');
                    let $discountField = $shippingItemWrapper.find('.shiping-method__discount-field');
                    let $discountPriceField = $shippingItemWrapper.find('.shiping-method__discount-price-field');

                    if ($(this).is(':checked')) {
                        let priceValue = parseFloat($priceField.val());
                        let discountPriceField = parseFloat($discountPriceField.val());
                        if (isNaN(priceValue)) {
                            priceValue = 0;
                        }

                        let discountValue = parseFloat($discountField.val());
                        if (isNaN(discountValue)) {
                            discountValue = 0;
                        }

                        if(discountPriceField === 0){
                            priceValue = parseFloat($priceField.val());
                        }
                        if(discountPriceField>0){
                            priceValue = discountPriceField;
                        }

                        shippingCost += priceValue;
                        //discountTotal += discountValue;
                    }
                });


                let taxRateAmount = 0;
                let subTotalAndShippingCost = subTotal + shippingCost;
                if (taxRateValue > 0 && parseInt($('input[name="tax_enable"]:checked').val()) === taxEnableYes) {
                    taxRateAmount = (subTotal - discountTotal) * taxRateValue / 100;
                }

                $('#tax_vat_amount').val(taxRateAmount);
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
                            $('#__noShippingMethodsWrapper').show();
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
                $('#__discountPriceAddDiscount').val(discountPriceValue);
                $('#__discountPriceAddDiscount').focus();

                $('#__productCodeAddDiscount').val(productCode);
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
                let $productOrginalDiscountElement = $productItemWrapper.find('.product-orginal-discount__field');
                let $btnAddDiscountElement = $productItemWrapper.find('.btn-product-discount');

                let productOldPrice = parseFloat($productPriceElement.val());

                let discountPrice = parseFloat($('#__discountPriceAddDiscount').val());

                //alert(productOldPrice+'//'+discountPrice);
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

                $productDiscountElement.val(discountPrice);
                $productOrginalDiscountElement.val(productDiscountValue);
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

                // product discount amount
                let $productOrginalDiscountElement = $productItemWrapper.find('.product-orginal-discount__field');

                let productOldPrice = parseFloat($productPriceElement.val());

                $oldPriceDisplayElement.removeClass('line-through');
                $btnAddDiscountElement.html(`Discount Price`);

                $productDiscountElement.val(0);
                $productOrginalDiscountElement.val(0);
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

                let $shippingCostDiscountPrice = $shippingItemWrapper.find('.shiping-method__discount-price-field');

                let productOldPrice = parseFloat($productPriceElement.val());

                $oldPriceDisplayElement.removeClass('line-through');
                $btnAddDiscountElement.html(`Discount Cost`);

                $shippingCostDiscountPrice.val(0);
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
                
                let $shippingCostDiscountPrice = $shippingMethodItemWrapper.find('.shiping-method__discount-price-field');
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

                $shippingCostDiscountPrice.val(discountPrice);
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

                $('#__shippingMethodListWrapper').append($templateShippingItem.html());


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
            $('#__formCreateOrder').on('submit', function(event) {
                event.preventDefault();

                let formData = new FormData($(this)[0]);

                $.ajax({
                    type: 'POST',
                    url: orderStoreUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('.alert').addClass('hidden');
                        $('#__alertSuccessContent').html(null);
                        $('#__alertDangerContent').html(null);

                        $('#__btnCancelCreateOrder').attr('disabled', true);
                        $('#__btnSubmitCreateOrder').attr('disabled', true).html(textProcessing);
                    },
                    success: function(responseData) {
                        let orderResult = responseData.data;

                        // $('#__alertSuccessContent').html(responseData.message);
                        // $('#__alertSuccess').removeClass('hidden');

                        $('#__orderIdOrderCreated').html(`#${orderResult.orderId}`);
                        $('#__publicUrlOrderCreated')
                            .attr('data-href', orderResult.publicUrl)
                            .html(orderResult.publicUrl);

                        $('#__subTotalOrderCreated').html(orderResult.subTotal.toLocaleString());
                        $('#__shippingCostOrderCreated').html(orderResult.shippingCost.toLocaleString());
                        $('#__discountTotalOrderCreated').html(orderResult.discountTotal.toLocaleString());
                        $('#__totalAmountOrderCreated').html(orderResult.totalAmount.toLocaleString());

                        $('#__btnEditOrderCreated').attr('data-href', orderResult.editUrl);
                        $('#__btnCopyBuyerLinkOrderCreated').attr('data-clipboard-text', orderResult.publicUrl);

                        $('#__modalOrderCreated').doModal('open');

                        $('#__btnCancelCreateOrder').attr('disabled', false);
                        $('#__btnSubmitCreateOrder').attr('disabled', false).html(textCreateOrder);
                    },
                    error: function(error) {
                        let responseJson = error.responseJSON;

                        $('html, body').animate({
                            scrollTop: 0
                        }, 500);

                        $('#__btnCancelCreateOrder').attr('disabled', false);
                        $('#__btnSubmitCreateOrder').attr('disabled', false).html(textCreateOrder);

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


            $('#__btnCancelCreateOrder').on('click', function() {
                $('#__modalCancelCreateOrder').doModal('open');
            });


            $('#__btnCloseModalCancelCreateOrder').on('click', function() {
                $('#__modalCancelCreateOrder').doModal('close');
            });


            var copyBuyerLink = new ClipboardJS('#__btnCopyBuyerLinkOrderCreated');
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

            
            $('body').on('change', '#orders_option', function() {
                var orders_option = $("#orders_option").val();

                if (orders_option == 'shopee') {
                    $("#shopee_order_no_div").removeClass('hide');
                }
                else{
                    $("#shopee_order_no_div").addClass('hide');
                }
            });

            

            $('#__btnCloseModalFinalShopeeOrderNo').on('click', function() {
                let shopee_order_no = $("#shopee_order_no").val();
                if(shopee_order_no){

                $.ajax({
                    url: '{{ route('shopee.display_customer_address_for_order') }}?id=' + shopee_order_no,
                    beforeSend: function() {
                        $('#__btnCloseModalFinalShopeeOrderNo').html('Processing..');
                    }
                }).done(function(result) {
                    console.log(result.shipping_phone);
                    //alert(result.shipping_postcode);
                    if(result.shipping_phone){
                        $("#not_found_msg_shown").addClass('hide');
                        $("#__shipping_nameEditOrder").val(result.shipping_name);
                        $("#__shipping_phoneEditOrder").val(result.shipping_phone);
                        $("#__addressEditOrder").val(result.shipping_address_1);
                       
                        $('#__shipping_provinceEditOrder').append($('<option>', { 
                            value: result.shipping_state,
                            text : result.shipping_state 
                        }));

                        $('#__shipping_districtEditOrder').append($('<option>', { 
                            value: result.shipping_district,
                            text : result.shipping_district 
                        }));

                        $('#__shipping_sub_districtEditOrder').append($('<option>', { 
                            value: result.shipping_city,
                            text : result.shipping_city 
                        }));

                        $('#__shipping_postcodeEditOrder').append($('<option>', { 
                            value: result.shipping_postcode,
                            text : result.shipping_postcode 
                        }));

                        $('#__searchShopeeOrders').doModal('close');
                     }
                     else{
                        $("#not_found_msg_shown").removeClass('hide');
                        
                     }
                    
                    // $('#form-address').html(result);
                    $('#__btnCloseModalFinalShopeeOrderNo').html('');
                    $('#__btnCloseModalFinalShopeeOrderNo').html('Continue');
                });
                }
                
            });

           

        </script>
        <script src="{{ asset('pages/seller/order_management/edit/shipping_address.js?_=' . rand()) }}"></script>
        <script src="{{ asset('pages/seller/order_management/create/tax_invoice.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
