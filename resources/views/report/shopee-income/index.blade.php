<x-app-layout>

    @section('title')
        {{ __('translation.Income of Completed Order') }}
    @endsection


    @if(\App\Models\Role::checkRolePermissions('Can access menu: Report - Shopee Transaction'))
        <div class="col-span-12">

            @include('partials.pages.shopee-transaction.tab-navigation')

            <x-card.card-default>
                <x-card.header>
                    <x-card.title>
                        {{ __('translation.Income of Completed Order') }}
                    </x-card.title>
                </x-card.header>
                <x-card.body>
                    <div class="flex flex-col items-center justify-center sm:flex-row sm:justify-end">
                        <div class="w-4/5 sm:w-1/2 lg:w-1/3">
                            <div class="flex flex-row items-center justify-end gap-4">
                                <x-form.select id="__filterShop">
                                    <option value="">
                                        {{ '- ' . __('translation.Select Shop') . ' -' }}
                                    </option>
                                    <option value="">
                                        {{ __('translation.All') }}
                                    </option>
                                    @foreach ($shops as $shop)
                                        <option value="{{ $shop->shop_id }}">
                                            {{ $shop->shop_name }}
                                        </option>
                                    @endforeach
                                </x-form.select>
                                <x-button type="button" color="gray" title="{{ __('translation.Reset') }}" id="__filterBtnReset">
                                    <i class="bi bi-arrow-counterclockwise text-lg"></i>
                                </x-button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 w-full overflow-x-auto">
                        <table class="w-full" id="__shopeeIncomeTable">
                            <thead>
                                <tr>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.detail') }}
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.actions') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </x-card.body>
            </x-card.card-default>
        </div>


        <x-modal.modal-large id="__modalDetails">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Details') }}
                </x-modal.title>
                <x-modal.close-button class="__btnCloseModalDetails" />
            </x-modal.header>
            <x-modal.body>
                <table class="w-full">
                    <tbody>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Shop Name') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span id="__shop_nameDetails" class="font-bold"></span>
                            </td>
                        </tr>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Order SN') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span id="__ordersnDetails" class="font-bold"></span>
                            </td>
                        </tr>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Buyer Name') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span id="__buyer_user_nameDetails" class="font-bold"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <x-section.section class="mt-10">
                    <x-section.title>
                        {{ __('translation.Order Income') }}
                    </x-section.title>
                    <x-section.body>
                        <div class="flex flex-col items-center justify-between md:flex-row md:gap-6">
                            <div class="w-full md:w-1/2">
                                <table class="w-full">
                                    <tbody>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Escrow Amount') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__escrow_amountDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Buyer Total Amount') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__buyer_total_amountDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Original Price') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__original_priceDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Seller Discount') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__seller_discountDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Shopee Discount') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__shopee_discountDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Voucher From Seller') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__voucher_from_sellerDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Voucher From Shopee') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__voucher_from_shopeeDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Coins') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <span id="__coinsDetails" class="font-bold"></span>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Buyer Paid Shipping Fee') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__buyer_paid_shipping_feeDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Buyer Transaction Fee') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__buyer_transaction_feeDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Cross Border Tax') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__cross_border_taxDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Payment Promotion') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__payment_promotionDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Commission Fee') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__commission_feeDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Service Fee') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__service_feeDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Seller Transaction Fee') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__seller_transaction_feeDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Seller Lost Compensation') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__seller_lost_compensationDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="w-full md:w-1/2">
                                <table class="w-full">
                                    <tbody>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Seller Coin Cash Back') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <span id="__seller_coin_cash_backDetails" class="font-bold"></span>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Escrow Tax') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__escrow_taxDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Final Shipping Fee') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__final_shipping_feeDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Actual Shipping Fee') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__actual_shipping_feeDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Shopee Shipping Rebate') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__shopee_shipping_rebateDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Shipping Fee Discount From 3PL') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__shipping_fee_discount_from_3plDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Seller Shipping Discount') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__seller_shipping_discountDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Estimated Shipping Discount') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__estimated_shipping_discountDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Seller Voucher Code') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <span id="__seller_voucher_codeDetails" class="font-bold"></span>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.DRC Adjustable Refund') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__drc_adjustable_refundDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Escrow Amount Aff') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__escrow_amount_affDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Exchange Rate') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__exchanage_rateDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Local Currency') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__local_currencyDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Escrow Currency') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__escrow_currencyDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                            <td class="px-4 py-2 align-top">
                                                {{ __('translation.Reverse Shipping Fee') }}
                                            </td>
                                            <td class="w-1 py-2 align-top">:</td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="whitespace-nowrap">
                                                    <span class="font-bold">
                                                        {{ currency_symbol('THB') }}
                                                    </span>
                                                    <span id="__reverse_shipping_feeDetails" class="font-bold"></span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </x-section.body>
                </x-section.section>

                <div class="flex flex-row items-center justify-center">
                    <x-button type="button" color="green" class="__btnCloseModalDetails">
                        {{ __('translation.Close') }}
                    </x-button>
                </div>
            </x-modal.body>
        </x-modal.modal-large>

    @endif


    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.2.2/dist/sweetalert2.min.css">
    @endpush

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.2.2/dist/sweetalert2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
        <script src="{{ asset('pages/seller/shopee-income/index/table.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
