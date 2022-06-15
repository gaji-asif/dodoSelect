<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - Place Order</title>

    <link rel="preconnect" href="https://fonts.googleapis.com/" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net/" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com/" crossorigin>
    <link rel="preconnect" href="https://code.jquery.com/" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com/">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net/">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com/">
    <link rel="dns-prefetch" href="https://code.jquery.com/">

    <!-- Fonts and Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.12.1/jquery-ui.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-timepicker@1.3.3/jquery.timepicker.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">

    <link rel="stylesheet" href="{{ asset('css/app.css?_=' . rand()) }}">
    <link rel="stylesheet" href="{{ asset('css/typeaheadjs.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom_css.css') }}">

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="{{ asset('js/dodo-modal.js?_=' . rand()) }}"></script>
</head>

<body class="font-sans antialiased bg-white 2xl:bg-gray-100">
@include('layouts.sidebar-navigation')

<main class="min-h-screen mt-2 xl:mt-0  xl:ml-60 pb-16 lg:pb-0 bg-gray-100 2xl:bg-transparent">
    <div class="py-8 xl:pt-5 md:pb-12 px-5 justify-center bg-gray-100">
        <div class="w-full mx-auto bg-gray-100">
            <div class="mb-6">
                <x-card.card-default>
                    <x-card.body>
                        <div class="flex flex-row">
                            <div class="w-1/4">
                                <div class="relative mb-2">
                                    <div class="w-11 md:w-12 h-11 md:h-12 mx-auto rounded-full flex items-center justify-center shadow-md bg-blue-500 text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-cart3" viewBox="0 0 16 16">
                                            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <span>Order Items</span>
                                </div>
                            </div>
                            <div class="w-1/4">
                                <div class="relative mb-2">
                                    <div class="absolute flex align-center items-center align-middle content-center" style="width: calc(100% - 2.5rem - 1rem); top: 50%; transform: translate(-50%, -50%)">
                                        <div class="w-full bg-transparent items-center align-middle align-center flex-1">
                                            <div class="w-11/12 mx-auto bg-blue-200 px-1 py-1 rounded"></div>
                                        </div>
                                    </div>

                                    <div class="w-11 md:w-12 h-11 md:h-12 mx-auto rounded-full flex items-center justify-center shadow-md bg-white text-blue-500" id="__shippingStepItem">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-truck" viewBox="0 0 16 16">
                                            <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <span>Shipping</span>
                                </div>
                            </div>
                            <div class="w-1/4">
                                <div class="relative mb-2">
                                    <div class="absolute flex align-center items-center align-middle content-center" style="width: calc(100% - 2.5rem - 1rem); top: 50%; transform: translate(-50%, -50%)">
                                        <div class="w-full bg-transparent items-center align-middle align-center flex-1">
                                            <div class="w-11/12 mx-auto bg-blue-200 px-1 py-1 rounded"></div>
                                        </div>
                                    </div>

                                    <div class="w-11 md:w-12 h-11 md:h-12 mx-auto rounded-full flex items-center justify-center shadow-md bg-white text-blue-500" id="__paymentStepItem">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-currency-dollar" viewBox="0 0 16 16">
                                            <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <span>Payment</span>
                                </div>
                            </div>
                            <div class="w-1/4">
                                <div class="relative mb-2">
                                    <div class="absolute flex align-center items-center align-middle content-center" style="width: calc(100% - 2.5rem - 1rem); top: 50%; transform: translate(-50%, -50%)">
                                        <div class="w-full bg-transparent items-center align-middle align-center flex-1">
                                            <div class="w-11/12 mx-auto bg-blue-200 px-1 py-1 rounded"></div>
                                        </div>
                                    </div>

                                    <div class="w-11 md:w-12 h-11 md:h-12 mx-auto rounded-full flex items-center justify-center shadow-md bg-white text-blue-500" id="__confirmationStepItem">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-5 h-5 bi bi-card-checklist" viewBox="0 0 16 16">
                                            <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                                            <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0zM7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <span>Confirmation</span>
                                </div>
                            </div>
                        </div>
                    </x-card.body>
                </x-card.card-default>
            </div>
        </div>

        <div class="w-full mx-auto">
            <x-card.card-default>
                <x-card.body>
                    <form action="#" method="post" id="__formBuyerPage" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="order_id" value="{{ $order_id }}">

                        <div class="" id="__confirmOrderStepContentWrapper">
                            <div class="mb-6">
                                <h1 class="text-xl text-gray-800 font-bold leading-tight relative top-1">
                                    Create New Order
                                </h1>
                            </div>

                            <x-section.section>
                                <x-section.title-with-button titleText="{{ __('translation.Products') }}">
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
                                    Available Shipping Methods
                                </x-section.title>
                                <x-section.body>
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
                                        <label for="__tax_enable_BuyerPage" class="block mb-2">
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
                                    <div class="w-full">
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

                            <div class="pb-3">
                                <div class="flex items-center justify-center gap-1">
                                    <x-button type="button" color="blue" id="__btnNextShippingStep">
                                    <span class="mr-2">
                                        Next Steps
                                    </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-arrow-right" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                        </svg>
                                    </x-button>
                                </div>
                            </div>
                        </div>


                        <div class="hidden" id="__shippingStepContentWrapper">
                            <div class="mb-6">
                                <h1 class="text-xl text-gray-800 font-bold leading-tight relative top-1">
                                    Shipping Details
                                </h1>
                            </div>

                            <x-section.section>
                                <x-section.title>
                                    Where Should We Deliver Your Order
                                </x-section.title>
                                <x-section.body>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <x-label for="__shipping_nameBuyerPage">
                                                {{ __('translation.Your Name') }} <x-form.required-mark/>
                                            </x-label>
                                            <x-input type="text" name="shipping_name" id="__shipping_nameBuyerPage" value="{{ old('shipping_name') }}" />
                                        </div>
                                        <div>
                                            <x-label for="__shipping_phoneBuyerPage">
                                                {{ __('translation.Phone Number') }} <x-form.required-mark/>
                                            </x-label>
                                            <x-input type="text" name="shipping_phone" id="__shipping_phoneBuyerPage" value="{{ old('shipping_phone') }}" />
                                        </div>
                                        <div class="md:col-span-2">
                                            <x-label for="__addressBuyerPage">
                                                {{ __('translation.Address') }} <x-form.required-mark/>
                                            </x-label>
                                            <x-form.textarea name="shipping_address" id="__addressBuyerPage" rows="4">{{ old('shipping_address') }}</x-form.textarea>
                                        </div>
                                        <div>
                                            <x-label for="__shipping_provinceBuyerPage">
                                                {{ __('translation.Province') }} <x-form.required-mark/>
                                            </x-label>
                                            <x-select name="shipping_province" id="__shipping_provinceBuyerPage" style="width: 100%">
                                                <option value="" selected>Select Province</option>
                                            </x-select>
                                        </div>
                                        <div>
                                            <x-label for="__shipping_districtBuyerPage">
                                                {{ __('translation.District') }} <x-form.required-mark/>
                                            </x-label>
                                            <x-select name="shipping_district" id="__shipping_districtBuyerPage" style="width: 100%">
                                                <option value="" selected>Select District</option>
                                            </x-select>
                                        </div>
                                        <div>
                                            <x-label for="__shipping_sub_districtBuyerPage">
                                                {{ __('translation.Sub District') }} <x-form.required-mark/>
                                            </x-label>
                                            <x-select name="shipping_sub_district" id="__shipping_sub_districtBuyerPage" style="width: 100%">
                                                <option value="" selected>Select District</option>
                                            </x-select>
                                        </div>
                                        <div>
                                            <x-label for="__shipping_postcodeBuyerPage">
                                                {{ __('translation.Postal Code') }} <x-form.required-mark/>
                                            </x-label>
                                            <x-select name="shipping_postcode" id="__shipping_postcodeBuyerPage" style="width: 100%">
                                                <option value="" selected>Select District</option>
                                            </x-select>
                                        </div>
                                    </div>
                                </x-section.body>
                            </x-section.section>

                            <div class="pb-3">
                                <div class="flex items-center justify-center gap-1">
                                    <x-button-outline type="button" color="blue" id="__btnBackOrderItemStep">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-arrow-left" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                                        </svg>
                                        <span class="ml-2">Prev Steps</span>
                                    </x-button-outline>
                                    <x-button type="button" color="blue" id="__btnNextPaymentStep">
                                        <span class="mr-2">Next Steps</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-arrow-right" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                        </svg>
                                    </x-button>
                                </div>
                            </div>
                        </div>

                        <div class="hidden" id="__paymentStepContentWrapper">
                            <div class="mb-6">
                                <h1 class="text-xl text-gray-800 font-bold leading-tight relative top-1">
                                    Payment
                                </h1>
                            </div>

                            <x-section.section>
                                <x-section.title>
                                    Select Payment Method
                                </x-section.title>
                                <x-section.body>

                                    <div class="mb-5">
                                        <div class="mb-2">
                                            <x-form.input-radio name="payment_method" id="__paymentMethodBuyerPage_2" value="{{ $paymentMethodInstant }}" checked="true">
                                                Instant Payment
                                            </x-form.input-radio>
                                        </div>
                                        <div class="px-6" id="__instantPaymentMethodWrapper">
                                            <div class="border border-solid border-gray-300 rounded-md p-4">
                                                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                                                    <div class="flex items-center justify-center">
                                                        <img src="{{ asset('img/alipay.png') }}" class="w-full h-auto" alt="Alipay">
                                                    </div>
                                                    <div class="flex items-center justify-center">
                                                        <img src="{{ asset('img/promtpay.png') }}" class="w-full h-auto" alt="PromptPay">
                                                    </div>
                                                    <div class="flex items-center justify-center">
                                                        <img src="{{ asset('img/shopeepay.png') }}" class="w-full h-auto" alt="Shopee Pay">
                                                    </div>
                                                    <div class="flex items-center justify-center">
                                                        <img src="{{ asset('img/truemoney.png') }}" class="w-full h-auto" alt="True Money">
                                                    </div>
                                                    <div class="flex items-center justify-center">
                                                        <img src="{{ asset('img/wechatpay.png') }}" class="w-full h-auto" alt="WeChat Pay">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-5">
                                        <div class="mb-2">
                                            <x-form.input-radio name="payment_method" id="__paymentMethodBuyerPage_1" value="{{ $paymentMethodBankTransfer }}">
                                                Bank Transfer
                                            </x-form.input-radio>
                                        </div>
                                    </div>
                                </x-section.body>
                            </x-section.section>

                            <div class="pb-3">
                                <div class="flex items-center justify-center gap-1">
                                    <x-button-outline type="button" color="blue" id="__btnBackShippingStep">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-arrow-left" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                                        </svg>
                                        <span class="ml-2">
                                        Prev Steps
                                    </span>
                                    </x-button-outline>

                                    <x-button type="button" color="blue" id="__btnPlaceOrder">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-check2-all" viewBox="0 0 16 16">
                                            <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z"/>
                                            <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708z"/>
                                        </svg>
                                        <span class="ml-2">
                                        Place Order
                                    </span>
                                    </x-button>
                                </div>
                            </div>
                        </div>

                        <div class="hidden" id="__confirmationStepContentWrapper">
                            <div class="mb-6">
                                <h1 class="text-xl text-gray-800 font-bold leading-tight relative top-1">
                                    Order Confirmation
                                </h1>
                            </div>
                        </div>
                    </form>
                </x-card.body>
            </x-card.card-default>
        </div>
    </div>

    <div class="hidden" id="__templateProductItem">
        <div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200" id="__row_ProductItem_{product_code}">
            <input type="hidden" name="product_id[]" value="{product_id}" class="product-id__field" data-product-code="{product_code}">
            <input type="hidden" name="product_price[]" value="{price}" class="product-price__field" data-product-code="{product_code}">
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
                    </x-button-sm>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <x-modal.modal-small id="__modalConfirmPlaceOrder">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Confirm') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>

            <x-alert-success id="__alertSuccessConfirmPlaceOrder" class="hidden alert"></x-alert-success>
            <x-alert-danger id="__alertDangerConfirmPlaceOrder" class="hidden alert"></x-alert-danger>

            <div class="mb-5 text-center">
                <p>
                    Are you sure to place this order?
                </p>
            </div>
            <div class="pb-3">
                <div class="flex flex-row items-center justify-center gap-1">
                    <x-button type="button" color="gray" id="__btnNoModalConfirmPlaceOrder">
                        {{ __('translation.No, Close') }}
                    </x-button>
                    <x-button type="button" color="red" id="__btnYesModalConfirmPlaceOrder">
                        {{ __('translation.Yes, Continue') }}
                    </x-button>
                </div>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

</main>

<footer class="w-full">
    <div class="text-center py-6">
        <span class="text-gray-500">Powered By</span>
        <a href="https://dodoselect.com/" target="_blank" class="no-underline hover:underline text-gray-900 font-bold">Dodoselect.com</a>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.12.1/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-slimscroll@1.3.8/jquery.slimscroll.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-timepicker@1.3.3/jquery.timepicker.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/typeahead.js@0.11.1/dist/typeahead.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>
<script src="{{ asset('js/sidebar-nav.js?_' . rand()) }}"></script>
<script src="{{ asset('js/delayKeyup.js?_=' . rand()) }}"></script>

<script>
    const orderId = '{{ $order_id }}';

    const orderStoreUrl = '{{ route('order-management.dropshipper.place-order') }}';
    const selectProvinceUrl = '{{ route('buyer-page.select-province') }}';
    const selectDistrictUrl = '{{ route('buyer-page.select-district') }}';
    const selectSubDistrictUrl = '{{ route('buyer-page.select-sub-district') }}';
    const selectPostCodeUrl = '{{ route('buyer-page.select-post-code') }}';
    const shippingAddressCheckUrl = '{{ route('buyer-page.shipping-address.check-address') }}';

    const paymentMethodBankTransfer = {{ $paymentMethodBankTransfer }};
    const paymentMethodInstant = {{ $paymentMethodInstant }};

    const selectTwoCategoriesParentUrl = '{{ route('categories-parent.select') }}';
    const subCategoryGridUrl = '{{ route('order_manage.sub-category-grid.index') }}';

    const productGridTableUrl = '{{ route('order_manage.product-grid.index') }}';
    const getShippingCostsUrl = '{{ route('shipper.shipping-cost.weight') }}';

    const enterKeyCode = 13;
    const THBSymbol = '{{ currency_symbol('THB') }}';

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

                        let productQuantity = 0;
                        if (typeof(product.get_quantity.quantity) !== 'undefined') {
                            productQuantity = product.get_quantity.quantity;
                        }

                        let dropshipPrice = parseFloat(product.dropship_price);
                        if (isNaN(dropshipPrice)) {
                            dropshipPrice = parseFloat(product.price);
                        }

                        let productWeight = parseFloat(product.weight);
                        if (isNaN(productWeight)) {
                            productWeight = 0;
                        }


                        if (selectedProductsToList.indexOf(product.product_code) === -1) {
                            selectedProductsToList.push(product.product_code);

                            templateProductItemElement.html(function(index, html) {
                                return html.replace('src="#"', 'src="'+ product.image_url +'"');
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
                                return html.replaceAll('{dropshipPriceString}', dropshipPrice.toLocaleString());
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

                        if (product.price != dropshipPrice) {
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
            $('#__noShippingMethodsWrapper').show();
        }

        calculateCartTotal();
        fetchAvailableShippingMethods();
    }


    $('#__btnClearProductList').click(function() {
        selectedProductsToList = [];

        $('#__productListWrapper').html(null);
        $('#__noProductWrapper').show();

        $('#__shippingMethodListWrapper').html(null);
        $('#__noShippingMethodsWrapper').show();

        $('#__subTotalCurrency').html(0);
        $('#__discountCurrency').html(0);
        $('#__shippingCostCurrency').html(0);
        $('#__grandTotalCurrency').html(0);
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

            let dropshipPrice = parseFloat($productItemWrapper.find('.dropship-price__field').val());
            if (isNaN(dropshipPrice)) {
                dropshipPrice = 0;
            }

            var productDiscount;
            if (productPrice === dropshipPrice) {
                productDiscount = 0;
            } else
                productDiscount = productPrice - dropshipPrice;

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
                    $('#__shippingMethodListWrapper').html(null);
                    $('#__noShippingMethodsWrapper').show();
                },
                success: function(responseData) {
                    let data = responseData.data;
                    let shippingCosts = data.shipping_costs;

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
    }

    $('#__shipping_provinceBuyerPage').select2({
        width: 'resolve',
        placeholder: '- {{ __('translation.Select Province') }} -',
        allowClear: true,
        ajax: {
            type: 'GET',
            url: selectProvinceUrl,
            data: function(params) {
                return {
                    page: params.page || 1,
                    search: params.term
                };
            },
            delay: 500
        }
    });


    $('#__shipping_districtBuyerPage').select2({
        width: 'resolve',
        placeholder: '- {{ __('translation.Select District') }} -',
        allowClear: true,
        ajax: {
            type: 'GET',
            url: selectDistrictUrl,
            data: function(params) {
                return {
                    page: params.page || 1,
                    search: params.term,
                    province_code: shippingAddress.provinceCode
                };
            },
            delay: 500
        }
    });


    $('#__shipping_sub_districtBuyerPage').select2({
        width: 'resolve',
        placeholder: '- {{ __('translation.Select Sub District') }} -',
        allowClear: true,
        ajax: {
            type: 'GET',
            url: selectSubDistrictUrl,
            data: function(params) {
                return {
                    page: params.page || 1,
                    search: params.term,
                    district_code: shippingAddress.districtCode
                };
            },
            delay: 500
        }
    });


    $('#__shipping_postcodeBuyerPage').select2({
        width: 'resolve',
        placeholder: '- {{ __('translation.Select Postal Code') }} -',
        allowClear: true,
        ajax: {
            type: 'GET',
            url: selectPostCodeUrl,
            data: function(params) {
                return {
                    page: params.page || 1,
                    search: params.term,
                    sub_district_code: shippingAddress.subDistrictCode
                };
            },
            delay: 500
        }
    });


    $('#__shipping_provinceBuyerPage').on('select2:select', function(event) {
        const selectedData = event.params.data;
        shippingAddress.provinceCode = selectedData.code;

        $('#__shipping_districtBuyerPage').val(null).trigger('change');
        $('#__shipping_sub_districtBuyerPage').val(null).trigger('change');
        $('#__shipping_postcodeBuyerPage').val(null).trigger('change');
    });


    $('#__shipping_provinceBuyerPage').on('select2:clear', function(event) {
        shippingAddress.provinceCode = -1;
        shippingAddress.districtCode = -1;
        shippingAddress.subDistrictCode = -1;

        $('#__shipping_districtBuyerPage').val(null).trigger('change');
        $('#__shipping_sub_districtBuyerPage').val(null).trigger('change');
        $('#__shipping_postcodeBuyerPage').val(null).trigger('change');
    });


    $('#__shipping_districtBuyerPage').on('select2:select', function(event) {
        const selectedData = event.params.data;
        shippingAddress.districtCode = selectedData.code;

        $('#__shipping_sub_districtBuyerPage').val(null).trigger('change');
        $('#__shipping_postcodeBuyerPage').val(null).trigger('change');
    });


    $('#__shipping_districtBuyerPage').on('select2:clear', function(event) {
        shippingAddress.districtCode = -1;
        shippingAddress.subDistrictCode = -1;

        $('#__shipping_sub_districtBuyerPage').val(null).trigger('change');
        $('#__shipping_postcodeBuyerPage').val(null).trigger('change');
    });


    $('#__shipping_sub_districtBuyerPage').on('select2:select', function(event) {
        const selectedData = event.params.data;
        shippingAddress.subDistrictCode = selectedData.code;

        $('#__shipping_postcodeBuyerPage').val(null).trigger('change');
    });


    $('#__shipping_sub_districtBuyerPage').on('select2:clear', function(event) {
        shippingAddress.subDistrictCode = -1;

        $('#__shipping_postcodeBuyerPage').val(null).trigger('change');
    });


    $('#__btnNextShippingStep').on('click', function() {
        let products = $('#__productListWrapper').find('.product-id__field');

        let shipping_method = false;
        let shippings = $('#__shippingMethodListWrapper').find('.shipping-method__id-radio-field');
        shippings.each(function() {
            if ($(this).is(':checked')) {
                shipping_method = true;
            }
        });

        if (products.length !== 0 && shipping_method)
            nextToShippingStep();
        else{
            if (products.length === 0) {
                alert("Please add product(s) before proceeding.")
                $('#__product_id_CreateOrder').focus();
            } else {
                alert("Please select a shipping method before proceeding.")
            }
        }
    });

    const nextToShippingStep = () => {
        $('#__confirmOrderStepContentWrapper').addClass('hidden animate__animated animate__fadeOut');
        $('#__shippingStepContentWrapper').removeClass('hidden').addClass('animate__animated animate__fadeIn');

        $('html, body').animate({
            scrollTop: 0
        }, 500);

        setTimeout(() => {
            $('#__shippingStepItem').addClass('bg-blue-500 text-white').removeClass('bg-white text-blue-500');
        }, 500);

        setTimeout(() => {
            $('#__confirmOrderStepContentWrapper').removeClass('animate__animated animate__fadeOut');
            $('#__shippingStepContentWrapper').removeClass('animate__animated animate__fadeIn');
        }, 1100);
    }


    $('#__btnBackOrderItemStep').on('click', function() {
        backToOrderItemStep();
    });

    const backToOrderItemStep = () => {
        $('#__shippingStepContentWrapper').addClass('hidden animate__animated animate__fadeOut');
        $('#__confirmOrderStepContentWrapper').removeClass('hidden').addClass('animate__animated animate__fadeIn');

        $('html, body').animate({
            scrollTop: 0
        }, 500);

        setTimeout(() => {
            $('#__shippingStepItem').addClass('bg-white text-blue-500').removeClass('bg-blue-500 text-white');
        }, 500);

        setTimeout(() => {
            $('#__shippingStepContentWrapper').removeClass('animate__animated animate__fadeOut');
            $('#__confirmOrderStepContentWrapper').removeClass('animate__animated animate__fadeIn');
        }, 1100);
    }


    $('#__btnNextPaymentStep').on('click', function() {

        const formData = new FormData($('#__formBuyerPage')[0]);

        $.ajax({
            type: 'POST',
            url: shippingAddressCheckUrl,
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#__btnBackOrderItemStep').attr('disabled', true);
                $('#__btnNextPaymentStep').attr('disabled', true);
            },
            success: function(response) {
                const responseData = response.data;

                nextToPaymentStep();

                setTimeout(() => {
                    $('#__btnBackOrderItemStep').attr('disabled', false);
                    $('#__btnNextPaymentStep').attr('disabled', false);
                }, 500);
            },
            error: function(error) {
                const errorResponse = error.responseJSON;
                let alertMessage = '';

                $('#__btnBackOrderItemStep').attr('disabled', false);
                $('#__btnNextPaymentStep').attr('disabled', false);

                if (error.status == 422) {
                    let errorFields = Object.keys(errorResponse.errors);
                    errorFields.map(field => {
                        alertMessage += `- ${errorResponse.errors[field][0]} <br>`
                    });

                } else {
                    alertMessage = errorResponse.message;

                }

                Swal.fire({
                    icon: 'error',
                    html: alertMessage
                });
            }
        });

    });


    const nextToPaymentStep = () => {
        $('#__shippingStepContentWrapper').addClass('hidden animate__animated animate__fadeOut');
        $('#__paymentStepContentWrapper').removeClass('hidden').addClass('animate__animated animate__fadeIn');

        $('html, body').animate({
            scrollTop: 0
        }, 500);

        setTimeout(() => {
            $('#__paymentStepItem').addClass('bg-blue-500 text-white').removeClass('bg-white text-blue-500');
        }, 500);

        setTimeout(() => {
            $('#__shippingStepContentWrapper').removeClass('animate__animated animate__fadeOut');
            $('#__paymentStepContentWrapper').removeClass('animate__animated animate__fadeIn');
        }, 1100);
    }


    $('#__btnBackShippingStep').on('click', function() {
        $('#__paymentStepContentWrapper').addClass('hidden animate__animated animate__fadeOut');
        $('#__shippingStepContentWrapper').removeClass('hidden').addClass('animate__animated animate__fadeIn');

        $('html, body').animate({
            scrollTop: 0
        }, 500);

        setTimeout(() => {
            $('#__paymentStepItem').addClass('bg-white text-blue-500').removeClass('bg-blue-500 text-white');
        }, 500);

        setTimeout(() => {
            $('#__paymentStepContentWrapper').removeClass('animate__animated animate__fadeOut');
            $('#__shippingStepContentWrapper').removeClass('animate__animated animate__fadeIn');
        }, 1100);
    });


    $('input[name="payment_method"]').on('change', function() {
        let selectedPaymentMethod = paymentMethodInstant;
        if ($(this).is(':checked')) {
            selectedPaymentMethod = $(this).val();
        }

        if (selectedPaymentMethod == paymentMethodInstant) {
            $('#__instantPaymentMethodWrapper').show('medium');
        }

        if (selectedPaymentMethod == paymentMethodBankTransfer) {
            $('#__instantPaymentMethodWrapper').hide('medium');
        }
    });


    $('#__btnPlaceOrder').on('click', function() {
        $('#__modalConfirmPlaceOrder').doModal('open');
    });


    $('#__btnNoModalConfirmPlaceOrder').on('click', function() {
        $('.alert').addClass('hidden').find('.alert-content').html(null);

        $('#__modalConfirmPlaceOrder').doModal('close');
    });

    $('#__btnYesModalConfirmPlaceOrder').on('click', function() {
        const formData = new FormData($('#__formBuyerPage')[0]);

        $('#__alertDangerConfirmPlaceOrder').addClass('hidden');
        $('#__alertDangerConfirmPlaceOrder').find('.alert-content').html(null);
        $('#__alertSuccessConfirmPlaceOrder').find('.alert-content').html(null);

        $.ajax({
            type: 'POST',
            data: formData,
            url: orderStoreUrl,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#__btnNoModalConfirmPlaceOrder').attr('disabled', true);
                $('#__btnYesModalConfirmPlaceOrder').attr('disabled', true).html('Processing...');
            },
            success: function(response) {
                const responseData = response.data;

                $('#__alertSuccessConfirmPlaceOrder').find('.alert-content').html(response.message);
                $('#__alertSuccessConfirmPlaceOrder').removeClass('hidden');

                setTimeout(() => {
                    window.location.href = responseData.publicUrl;
                }, 1500);

            },
            error: function(error) {
                let responseJson = error.responseJSON;

                $('#__btnNoModalConfirmPlaceOrder').attr('disabled', false);
                $('#__btnYesModalConfirmPlaceOrder').attr('disabled', false).html('Yes, Continue');

                if (error.status == 422) {
                    let errorFields = Object.keys(responseJson.errors);
                    errorFields.map(field => {
                        $('#__alertDangerConfirmPlaceOrder').find('.alert-content').append(
                            $('<span/>', {
                                class: 'block mb-1',
                                html: `- ${responseJson.errors[field][0]}`
                            })
                        );
                    });

                } else {
                    $('#__alertDangerConfirmPlaceOrder').find('.alert-content').html(responseJson.message);

                }

                $('#__alertDangerConfirmPlaceOrder').removeClass('hidden');
            }
        });
    });


    $('#__btnNextConfirmationStep').on('click', function() {
        nextToConfirmationStep();
    });


    const nextToConfirmationStep = () => {
        $('#__modalConfirmPlaceOrder').doModal('close');

        $('#__paymentStepContentWrapper').addClass('hidden animate__animated animate__fadeOut');
        $('#__confirmationStepContentWrapper').removeClass('hidden').addClass('animate__animated animate__fadeIn');

        $('html, body').animate({
            scrollTop: 0
        }, 500);

        setTimeout(() => {
            $('#__confirmationStepItem').addClass('bg-blue-500 text-white').removeClass('bg-white text-blue-500');
        }, 500);

        setTimeout(() => {
            $('#__paymentStepContentWrapper').removeClass('animate__animated animate__fadeOut');
            $('#__confirmationStepContentWrapper').removeClass('animate__animated animate__fadeIn');
        }, 1100);
    }


    $('#__btnBackPaymentStep').on('click', function() {
        $('#__confirmationStepContentWrapper').addClass('hidden animate__animated animate__fadeOut');
        $('#__paymentStepContentWrapper').removeClass('hidden').addClass('animate__animated animate__fadeIn');

        $('html, body').animate({
            scrollTop: 0
        }, 500);

        setTimeout(() => {
            $('#__confirmationStepItem').addClass('bg-white text-blue-500').removeClass('bg-blue-500 text-white');
        }, 500);

        setTimeout(() => {
            $('#__confirmationStepContentWrapper').removeClass('animate__animated animate__fadeOut');
            $('#__paymentStepContentWrapper').removeClass('animate__animated animate__fadeIn');
        }, 1100);
    });


    $('#__payment_dateBuyerPage').datepicker({
        dateFormat: 'dd-mm-yy',
    });

    $('#__payment_timeBuyerPage').timepicker({
        timeFormat: 'HH:mm',
        interval: 1,
        defaultTime: '09',
        dynamic: false,
        dropdown: true,
        scrollbar: true
    });

</script>

<script src="{{ asset('pages/seller/order_management/create/tax_invoice.js?_=' . rand()) }}"></script>

</body>

</html>
