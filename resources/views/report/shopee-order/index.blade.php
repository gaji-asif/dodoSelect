<x-app-layout>

    @section('title')
        {{ __('translation.Shopee Order') }}
    @endsection


    @if(\App\Models\Role::checkRolePermissions('Can access menu: Report - Shopee Transaction'))
        <div class="col-span-12">

            @include('partials.pages.shopee-transaction.tab-navigation')

            <x-card.card-default>
                <x-card.header>
                    <x-card.title>
                        {{ __('translation.Shopee Order') }}
                    </x-card.title>
                </x-card.header>
                <x-card.body>

                    <div class="mb-8 border border-solid border-gray-300 rounded-lg p-6 bg-gray-50">
                        <div class="px-4">
                            <span>
                                {{ __('translation.Summary') }}
                            </span><br>
                            <span id="__summaryDateFrom" class="font-bold"></span>
                            <span>-</span>
                            <span id="__summaryDateTo" class="font-bold"></span>
                        </div>
                        <div class="mt-4">
                            <table class="w-full">
                                <tbody>
                                    <tr class="block mb-2 sm:table-row sm:mb-0">
                                        <td class="block px-4 py-0 align-top sm:table-cell sm:w-1/2 sm:py-1 md:w-1/3">
                                            {{ __('translation.Shop Name') }}
                                        </td>
                                        <td class="hidden w-1 py-1 align-top sm:table-cell">:</td>
                                        <td class="block px-4 py-0 align-top sm:table-cell sm:py-1">
                                            <span id="__summaryShopName" class="font-bold">
                                                {{ __('translation.All') }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr class="block mb-2 sm:table-row sm:mb-0">
                                        <td class="block px-4 py-0 align-top sm:table-cell sm:w-1/2 sm:py-1 md:w-1/3">
                                            {{ __('translation.Total Amount') }}
                                        </td>
                                        <td class="hidden w-1 py-1 align-top sm:table-cell">:</td>
                                        <td class="block px-4 py-0 align-top sm:table-cell sm:py-1">
                                            <span>{{ currency_symbol('THB') }}</span>
                                            <span id="__summaryAmountTotal" class="font-bold">0</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex flex-col items-center justify-center gap-4 sm:flex-row sm:justify-end" id="__filterTableWrapper">
                        <div class="w-4/5 sm:w-1/3">
                            <x-form.select id="__filterShop">
                                <option value="">
                                    {{ '- ' . __('translation.Select Shop') . ' -' }}
                                </option>
                                <option value="">
                                    {{ __('translation.All') }}
                                </option>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop->id }}">
                                        {{ $shop->shop_name }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>
                        <div class="w-4/5 sm:w-1/3">
                            <x-form.input type="date" id="__filterDate" placeholder="{{ __('translation.Daterange Filter') }}" data-input />
                        </div>
                        <div class="w-4/5 sm:w-1/3">
                            <div class="flex flex-row items-center justify-end gap-4">
                                <x-form.select id="__filterStatus">
                                    <option value="">
                                        {{ '- ' . __('translation.Select Status') . ' -' }}
                                    </option>
                                    <option value="">
                                        {{ __('translation.All') }}
                                    </option>
                                    @foreach ($orderStatus as $value => $label)
                                        <option value="{{ $value }}">
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </x-form.select>
                                <x-button type="button" color="gray" title="{{ __('translation.Reset') }}" id="__filterBtnReset" data-clear>
                                    <i class="bi bi-arrow-counterclockwise text-lg"></i>
                                </x-button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 w-full overflow-x-auto">
                        <table class="w-full" id="__shopeeOrderTable">
                            <thead>
                                <tr>
                                    <th class="w-auto px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Detail') }}
                                    </th>
                                    <th class="w-auto px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Amount') }}
                                    </th>
                                    <th class="w-auto px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Actions') }}
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
                <div class="mb-5">
                    <table class="w-full">
                        <tbody>
                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                <td class="px-4 py-2 align-top md:w-2/5">
                                    {{ __('translation.Shop Name') }}
                                </td>
                                <td class="w-1 py-2 align-top">:</td>
                                <td class="px-4 py-2 align-top">
                                    <span id="__shop_nameDetails" class="font-bold"></span>
                                </td>
                            </tr>
                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                <td class="px-4 py-2 align-top md:w-2/5">
                                    {{ __('translation.Order ID') }}
                                </td>
                                <td class="w-1 py-2 align-top">:</td>
                                <td class="px-4 py-2 align-top">
                                    <span id="__order_idDetails" class="font-bold"></span>
                                </td>
                            </tr>
                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                <td class="px-4 py-2 align-top md:w-2/5">
                                    {{ __('translation.Order Date') }}
                                </td>
                                <td class="w-1 py-2 align-top">:</td>
                                <td class="px-4 py-2 align-top">
                                    <span id="__order_dateDetails" class="font-bold"></span>
                                </td>
                            </tr>
                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                <td class="px-4 py-2 align-top md:w-2/5">
                                    {{ __('translation.Total Amount') }}
                                </td>
                                <td class="w-1 py-2 align-top">:</td>
                                <td class="px-4 py-2 align-top">
                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                    <span id="__total_amountDetails" class="font-bold"></span>
                                </td>
                            </tr>
                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                <td class="px-4 py-2 align-top md:w-2/5">
                                    {{ __('translation.Payment Method') }}
                                </td>
                                <td class="w-1 py-2 align-top">:</td>
                                <td class="px-4 py-2 align-top">
                                    <span id="__payment_method_titleDetails" class="font-bold"></span>
                                </td>
                            </tr>
                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                <td class="px-4 py-2 align-top md:w-2/5">
                                    {{ __('translation.Status') }}
                                </td>
                                <td class="w-1 py-2 align-top">:</td>
                                <td class="px-4 py-2 align-top">
                                    <span id="__statusDetails" class="font-bold"></span>
                                </td>
                            </tr>

                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                <td class="px-4 py-2 align-top md:w-2/5">
                                    {{ __('translation.Buyer Paid') }}
                                </td>
                                <td class="w-1 py-2 align-top">:</td>
                                <td class="px-4 py-2 align-top">
                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                    <span id="__income_buyer_total_amountDetails" class="font-bold"></span>
                                </td>
                            </tr>
                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                <td class="px-4 py-2 align-top md:w-2/5">
                                    {{ __('translation.Payout Amount') }}
                                </td>
                                <td class="w-1 py-2 align-top">:</td>
                                <td class="px-4 py-2 align-top">
                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                    <span id="__income_escrow_amountDetails" class="font-bold"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div x-data="{ activeTab: 1 }" class="border border-solid border-gray-300 rounded-sm">
                    <div class="bg-gray-100">
                        <ul class="block w-full overflow-x-auto">
                            <li class="inline-block -mr-1">
                                <a href="#"
                                    class="block border-2 border-solid border-r-0 border-b-0 border-l-0 px-4 py-2 cursor-pointer transition-all duration-300 hover:border-blue-500"
                                    x-bind:class="activeTab === 1 ? 'border-blue-500 bg-white text-gray-900' : 'border-transparent bg-gray-100 text-gray-700'"
                                    x-on:click="activeTab = 1">
                                    {{ __('translation.Shipping Lines') }}
                                </a>
                            </li>
                            <li class="inline-block -mr-1">
                                <a href="#"
                                    class="block border-2 border-solid border-r-0 border-b-0 border-l-0 px-4 py-2 cursor-pointer transition-all duration-300 hover:border-blue-500"
                                    x-bind:class="activeTab === 2 ? 'border-blue-500 bg-white text-gray-900' : 'border-transparent bg-gray-100 text-gray-700'"
                                    x-on:click="activeTab = 2">
                                    {{ __('translation.Buyer Info') }}
                                </a>
                            </li>
                            <li class="inline-block -mr-1">
                                <a href="#"
                                    class="block border-2 border-solid border-r-0 border-b-0 border-l-0 px-4 py-2 cursor-pointer transition-all duration-300 hover:border-blue-500"
                                    x-bind:class="activeTab === 3 ? 'border-blue-500 bg-white text-gray-900' : 'border-transparent bg-gray-100 text-gray-700'"
                                    x-on:click="activeTab = 3">
                                    {{ __('translation.Fee Details') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="p-4">
                        <div x-show="activeTab === 1" x-transition.duration.500ms>
                            <x-section.section>
                                <x-section.title>
                                    {{ __('translation.Shipping Lines') }}
                                </x-section.title>
                                <x-section.body>
                                    <table class="w-full">
                                        <tbody>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top">
                                                    {{ __('translation.Shipping Carrier') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span id="__shipping_line_carrierDetails" class="font-bold"></span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top">
                                                    {{ __('translation.Service Name') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span id="__shipping_line_serviceDetails" class="font-bold"></span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top">
                                                    {{ __('translation.Tracking Number') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span id="__shipping_line_tracking_numberDetails" class="font-bold"></span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </x-section.body>
                            </x-section.section>
                        </div>
                        <div x-show="activeTab === 2" x-transition.duration.500ms>
                            <div>
                                <div class="flex flex-col items-center justify-between md:flex-row md:gap-x-6">
                                    <div class="w-full md:w-1/2">
                                        <x-section.section>
                                            <x-section.title>
                                                {{ __('translation.Billing') }}
                                            </x-section.title>
                                            <x-section.body>
                                                <div class="mb-4">
                                                    <label class="block mb-1">
                                                        {{ __('translation.Name') }}
                                                    </label>
                                                    <span id="__billing_nameDetails" class="font-bold">-</span>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block mb-1">
                                                        {{ __('translation.Phone') }}
                                                    </label>
                                                    <span id="__billing_phoneDetails" class="font-bold">-</span>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block mb-1">
                                                        {{ __('translation.Address') }}
                                                    </label>
                                                    <span id="__billing_addressDetails" class="font-bold">-</span>
                                                </div>
                                            </x-section.body>
                                        </x-section.section>
                                    </div>
                                    <div class="w-full md:w-1/2">
                                        <x-section.section>
                                            <x-section.title>
                                                {{ __('translation.Shipping') }}
                                            </x-section.title>
                                            <x-section.body>
                                                <div class="mb-4">
                                                    <label class="block mb-1">
                                                        {{ __('translation.Name') }}
                                                    </label>
                                                    <span id="__shipping_nameDetails" class="font-bold">-</span>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block mb-1">
                                                        {{ __('translation.Phone') }}
                                                    </label>
                                                    <span id="__shipping_phoneDetails" class="font-bold">-</span>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block mb-1">
                                                        {{ __('translation.Address') }}
                                                    </label>
                                                    <span id="__shipping_addressDetails" class="font-bold">-</span>
                                                </div>
                                            </x-section.body>
                                        </x-section.section>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div x-show="activeTab === 3" x-transition.duration.500ms>
                            <x-section.section>
                                <x-section.title>
                                    {{ __('translation.Fee Details') }}
                                </x-section.title>
                                <x-section.body>
                                    <table class="w-full">
                                        <tbody>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Buyer Paid') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_buyer_total_amountDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Payout Amount') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_escrow_amountDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Original Price') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_original_priceDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Seller Discount') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_seller_discountDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Shopee Discount') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_shopee_discountDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Voucher From Seller') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_voucher_from_sellerDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Voucher From Shopee') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_voucher_from_shopeeDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Coins') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span id="__shopee_income_coinsDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Buyer Paid Shipping Fee') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_buyer_paid_shipping_feeDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Buyer Transaction Fee') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_buyer_transaction_feeDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Cross Border Tax') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_cross_border_taxDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Payment Promotion') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_payment_promotionDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Commission Fee') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_commission_feeDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Service Fee') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_service_feeDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Seller Transaction Fee') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_seller_transaction_feeDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Seller Lost Compensation') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_seller_lost_compensationDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Seller Coin Cashback') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span id="__shopee_income_seller_coin_cash_backDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Escrow Tax') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_escrow_taxDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Final Shipping Fee') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_final_shipping_feeDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Actual Shipping Fee') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_actual_shipping_feeDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Shopee Shipping Rebate') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_shopee_shipping_rebateDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Shipping Fee Discount From 3pl') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_shipping_fee_discount_from_3plDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Seller Shipping Discount') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_seller_shipping_discountDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Estimated Shipping Fee') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_estimated_shipping_feeDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Seller Voucher Code') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span id="__shopee_income_seller_voucher_codeDetails" class="font-bold"></span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Drc Adjustable Refund') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_drc_adjustable_refundDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Escrow Amount Aff') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_escrow_amount_affDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Exchange Rate') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_exchange_rateDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Local Currency') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_local_currencyDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Escrow Currency') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_escrow_currencyDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Reverse Shipping Fee') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span class="font-bold">{{ currency_symbol('THB') }}</span>
                                                    <span id="__shopee_income_reverse_shipping_feeDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Return ID List') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span id="__shopee_income_returnsn_listDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                                <td class="px-4 py-2 align-top md:w-1/2">
                                                    {{ __('translation.Refund ID List') }}
                                                </td>
                                                <td class="w-1 py-2 align-top">:</td>
                                                <td class="px-4 py-2 align-top">
                                                    <span id="__shopee_income_refund_id_listDetails" class="font-bold">0</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </x-section.body>
                            </x-section.section>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex flex-row items-center justify-center">
                    <x-button type="button" color="green" class="__btnCloseModalDetails">
                        {{ __('translation.Close') }}
                    </x-button>
                </div>

            </x-modal.body>
        </x-modal.modal-large>

    @endif


    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.2.2/dist/sweetalert2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.css">
    @endpush

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.2.2/dist/sweetalert2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.js"></script>

        <script>
            const textAll = '{{ __('translation.All') }}';
            const thbSymbol = '{{ currency_symbol('THB') }}';
        </script>
        <script src="{{ asset('pages/seller/shopee-order/index/table.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
