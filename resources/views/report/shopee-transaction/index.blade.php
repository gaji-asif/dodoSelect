<x-app-layout>

    @section('title')
        {{ __('translation.Wallet Transaction') }}
    @endsection


    @if(\App\Models\Role::checkRolePermissions('Can access menu: Report - Shopee Transaction'))
        <div class="col-span-12">

            @include('partials.pages.shopee-transaction.tab-navigation')

            <x-card.card-default>
                <x-card.header>
                    <x-card.title>
                        {{ __('translation.Wallet Transaction') }}
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
                                            {{ __('translation.Transaction Total') }}
                                        </td>
                                        <td class="hidden w-1 py-1 align-top sm:table-cell">:</td>
                                        <td class="block px-4 py-0 align-top sm:table-cell sm:py-1">
                                            <span>{{ currency_symbol('THB') }}</span>
                                            <span id="__summaryAmountTotal" class="font-bold">0</span>
                                        </td>
                                    </tr>
                                    <tr class="block mb-2 sm:table-row sm:mb-0">
                                        <td class="block px-4 py-0 align-top sm:table-cell sm:w-1/2 sm:py-1 md:w-1/3">
                                            {{ __('translation.Transaction Fee Total') }}
                                        </td>
                                        <td class="hidden w-1 py-1 align-top sm:table-cell">:</td>
                                        <td class="block px-4 py-0 align-top sm:table-cell sm:py-1">
                                            <span>{{ currency_symbol('THB') }}</span>
                                            <span id="__summaryTransactionFeeTotal" class="font-bold">0</span>
                                        </td>
                                    </tr>
                                    <tr class="block mb-2 sm:table-row sm:mb-0">
                                        <td class="block px-4 py-0 align-top sm:table-cell sm:w-1/2 sm:py-1 md:w-1/3">
                                            {{ __('translation.Wallet Balance') }}
                                        </td>
                                        <td class="hidden w-1 py-1 align-top sm:table-cell">:</td>
                                        <td class="block px-4 py-0 align-top sm:table-cell sm:py-1">
                                            <span>{{ currency_symbol('THB') }}</span>
                                            <span id="__summaryWalletBalanceAmount" class="font-bold">0</span>
                                            (
                                            <span>
                                                as of
                                            </span>
                                            <span id="__summaryWalletBalanceDate" class="ml-1 font-bold">-</span>
                                            )
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <x-alert-info-simple id="__alertInfoSyncingData" class="hidden -mt-2 mb-6">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </span>
                        <span class="ml-2">
                            {{ __('translation.We are syncing your data') }}. {{ __('translation.It will take a few times') }}
                        </span>
                    </x-alert-info-simple>

                    <div class="flex flex-col items-center justify-between gap-8 gap-x-4 sm:flex-row">
                        <div class="w-full text-center sm:w-1/4 sm:text-left md:w-2/5">
                            <x-button type="button" color="yellow" id="__btnSyncData">
                                <i class="bi bi-arrow-repeat text-lg"></i>
                                <span class="ml-2">
                                    {{ __('translation.Sync Data') }}
                                </span>
                            </x-button>
                        </div>
                        <div class="w-4/5 mx-auto sm:w-3/4 md:w-3/5">
                            <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                                <div class="w-full sm:w-3/4 md:w-1/2">
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
                                </div>
                                <div id="__filterDate" class="w-full flex flex-row items-center justify-between gap-2 md:w-1/2">
                                    <x-form.input type="date" id="__filterDate" placeholder="{{ __('translation.Daterange Filter') }}" data-input />
                                    <x-button type="button" color="gray" title="{{ __('translation.Reset') }}" id="__filterBtnReset" data-clear>
                                        <i class="bi bi-arrow-counterclockwise text-lg"></i>
                                    </x-button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 w-full overflow-x-auto">
                        <table class="w-full" id="__shopeeTransactionTable">
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


        <x-modal.modal-small id="__modalSyncData">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Sync Data') }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>

                <x-alert-danger id="__alertDangerSyncData" class="alert hidden" />

                <form action="#" method="POST" id="__formSyncData">
                    @csrf
                    <div id="__flatpickrSyncData">
                        <x-form.form-group>
                            <x-form.label>
                                {{ __('translation.Shop') }} <x-form.required-mark/>
                            </x-form.label>
                            <x-form.select name="shop_id" id="__shop_idSyncData">
                                <option value="">
                                    {{ '- ' . __('translation.Select Shop') . ' -' }}
                                </option>
                                @foreach ($shops as $shop)
                                    <option value="{{ $shop->shop_id }}">
                                        {{ $shop->shop_name }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </x-form.form-group>
                        <x-form.form-group>
                            <x-form.label>
                                {{ __('translation.Date Range') }} <x-form.required-mark/>
                            </x-form.label>
                            <x-form.input type="date" name="date_range" id="__dateRangeSyncData" data-input />
                        </x-form.form-group>
                        <div class="flex flex-row items-center justify-center gap-2">
                            <x-button-no-bg type="reset" color="gray" id="__btnCancelSyncData" data-clear>
                                {{ __('translation.Cancel') }}
                            </x-button-no-bg>
                            <x-button type="submit" color="blue" id="__btnSubmitSyncData">
                                {{ __('translation.Sync Data') }}
                            </x-button>
                        </div>
                    </div>
                </form>
            </x-modal.body>
        </x-modal.modal-small>


        <x-modal.modal-small id="__modalDetails">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Details') }}
                </x-modal.title>
                <x-modal.close-button id="__btnCloseModalDetails" />
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
                                {{ __('translation.Buyer Name') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span id="__buyer_nameDetails" class="font-bold"></span>
                            </td>
                        </tr>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Order ID') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span id="__order_snDetails" class="font-bold"></span>
                            </td>
                        </tr>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Wallet Time') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span id="__timestampDetails" class="font-bold"></span>
                            </td>
                        </tr>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Status') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span id="__statusDetails" class="font-bold"></span>
                            </td>
                        </tr>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Amount') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span class="font-bold">
                                    {{ currency_symbol('THB') }}
                                </span>
                                <span id="__amountDetails" class="font-bold"></span>
                            </td>
                        </tr>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Transaction Fee') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span class="font-bold">
                                    {{ currency_symbol('THB') }}
                                </span>
                                <span id="__transaction_feeDetails" class="font-bold"></span>
                            </td>
                        </tr>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Current Balance') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span class="font-bold">
                                    {{ currency_symbol('THB') }}
                                </span>
                                <span id="__current_balanceDetails" class="font-bold"></span>
                            </td>
                        </tr>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Wallet Type') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span id="__wallet_typeDetails" class="font-bold"></span>
                            </td>
                        </tr>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Transaction ID') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span id="__transaction_idDetails" class="font-bold"></span>
                            </td>
                        </tr>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Transaction Type') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span id="__transaction_typeDetails" class="font-bold"></span>
                            </td>
                        </tr>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Refund ID') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span id="__refund_snDetails" class="font-bold"></span>
                            </td>
                        </tr>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Description') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span id="__descriptionDetails" class="font-bold"></span>
                            </td>
                        </tr>
                        <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                            <td class="px-4 py-2 align-top">
                                {{ __('translation.Reason') }}
                            </td>
                            <td class="w-1 py-2 align-top">:</td>
                            <td class="px-4 py-2 align-top">
                                <span id="__reasonDetails" class="font-bold"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </x-modal.body>
        </x-modal.modal-small>

    @endif


    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.2.2/dist/sweetalert2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.css">
    @endpush

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.2.2/dist/sweetalert2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.js"></script>
        <script src="{{ asset('pages/seller/shopee-transaction/index/table.js?_=' . rand()) }}"></script>

        <script>
            const textAll = '{{ __('translation.All') }}';
        </script>
        <script src="{{ asset('pages/seller/shopee-transaction/index/sync_data.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
