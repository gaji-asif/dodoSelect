<x-app-layout>

    @section('title')
        {{ __('translation.Stock Value') }}
    @endsection


    @if(\App\Models\Role::checkRolePermissions('Can access menu: Report - Stock Value'))
        <div class="col-span-12">

            <x-card.card-default>
                <x-card.header>
                    <x-card.title>
                        {{ __('translation.Stock Value') }}
                    </x-card.title>
                </x-card.header>
                <x-card.body>

                    <div class="mb-8 border border-solid border-gray-300 rounded-lg p-6 bg-gray-50">
                        <div>
                            <span class="font-bold">
                                {{ __('translation.Summary') }}
                            </span>
                        </div>
                        <div class="mt-4">
                            <table class="w-full">
                                <tbody>
                                    <tr>
                                        <td class="px-4 pl-0 py-1 align-top sm:w-1/2 md:w-1/3">
                                            {{ __('translation.Total Stock Value') }}
                                        </td>
                                        <td class="w-1 py-1 align-top">:</td>
                                        <td class="px-4 pr-0 py-1 align-top">
                                            <span id="__summaryTotalStockValue" class="font-bold">
                                                <svg class="animate-spin h-3 w-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 pl-0 py-1 align-top sm:w-1/2 md:w-1/3">
                                            {{ __('translation.Total Stock Cost Value') }}
                                        </td>
                                        <td class="w-1 py-1 align-top">:</td>
                                        <td class="px-4 pr-0 py-1 align-top">
                                            <span id="__summaryTotalStockCostValue" class="font-bold">
                                                <svg class="animate-spin h-3 w-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="flex flex-col items-center justify-between gap-4 gap-x-8 sm:flex-row">
                            <div>
                                <x-button type="button" color="green" id="__btnExportExcel">
                                    <i class="bi bi-download"></i>
                                    <span class="ml-2">
                                        {{ __('translation.Export Excel') }}
                                    </span>
                                </x-button>
                            </div>
                            <div>
                                <div class="flex flex-row items-center justify-center gap-x-2 sm:justify-end">
                                    <div class="text-gray-700">
                                        <span class="relative top-1">
                                            {{ __('translation.Sort By') }}:
                                        </span>
                                    </div>
                                    <div>
                                        <x-form.select id="__sortByToolbar" class="w-56">
                                            <option value="id__asc">
                                                {{ __('translation.Lowest ID') }}
                                            </option>
                                            <option value="id__desc">
                                                {{ __('translation.Highest ID') }}
                                            </option>
                                            <option value="product_name__asc">
                                                {{ __('translation.Product Name') }} - {{ __('translation.A-Z') }}
                                            </option>
                                            <option value="product_name__desc">
                                                {{ __('translation.Product Name') }} - {{ __('translation.Z-A') }}
                                            </option>
                                            <option value="stock_value__asc">
                                                {{ __('translation.Lowest Stock Value') }}
                                            </option>
                                            <option value="stock_value__desc">
                                                {{ __('translation.Highest Stock Value') }}
                                            </option>
                                            <option value="profit_margin__asc">
                                                {{ __('translation.Lowest Profit Margin') }}
                                            </option>
                                            <option value="profit_margin__desc">
                                                {{ __('translation.Highest Profit Margin') }}
                                            </option>
                                        </x-form.select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 w-full overflow-x-auto">
                        <table class="w-full" id="__stockValueTable">
                            <thead>
                                <tr>
                                    <th class="w-auto px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.ID') }}
                                    </th>
                                    <th class="w-auto px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Image') }}
                                    </th>
                                    <th class="w-auto px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Details') }}
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

        <x-modal.modal-medium id="__modalDetail">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Details') }}
                </x-modal.title>
                <x-modal.close-button class="btn-close__modalDetail" />
            </x-modal.header>
            <x-modal.body>
                <div class="mb-5">
                    <table class="w-full">
                        <tbody>
                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                <td class="px-4 py-2 align-top sm:w-1/2 md:w-1/3">
                                    {{ __('translation.Product Name') }}
                                </td>
                                <td class="w-1 py-2 align-top">:</td>
                                <td class="px-4 py-2 align-top">
                                    <span id="__product_nameModalDetail" class="font-bold"></span>
                                </td>
                            </tr>
                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                <td class="px-4 py-2 align-top sm:w-1/2 md:w-1/3">
                                    {{ __('translation.SKU') }}
                                </td>
                                <td class="w-1 py-2 align-top">:</td>
                                <td class="px-4 py-2 align-top">
                                    <span id="__product_codeModalDetail" class="font-bold"></span>
                                </td>
                            </tr>
                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                <td class="px-4 py-2 align-top sm:w-1/2 md:w-1/3">
                                    {{ __('translation.Quantity') }}
                                </td>
                                <td class="w-1 py-2 align-top">:</td>
                                <td class="px-4 py-2 align-top">
                                    <span id="__quantityModalDetail" class="font-bold"></span>
                                </td>
                            </tr>
                            <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                <td class="px-4 py-2 align-top sm:w-1/2 md:w-1/3">
                                    {{ __('translation.Price') }}
                                </td>
                                <td class="w-1 py-2 align-top">:</td>
                                <td class="px-4 py-2 align-top">
                                    <span class="font-bold">
                                        {{ currency_symbol('THB') }}
                                    </span>
                                    <span id="__priceModalDetail" class="font-bold"></span>
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
                                    {{ __('translation.Product Cost') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="p-4">
                        <div x-show="activeTab === 1" x-transition.duration.500ms>
                            <table class="w-full -mt-2">
                                <tbody>
                                    <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                        <td class="px-4 py-2 align-top sm:w-1/2">
                                            {{ __('translation.Supplier Name') }}
                                        </td>
                                        <td class="w-1 py-2 align-top">:</td>
                                        <td class="px-4 py-2 align-top">
                                            <span id="__product_cost_supplier_nameModalDetail" class="font-bold"></span>
                                        </td>
                                    </tr>
                                    <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                        <td class="px-4 py-2 align-top sm:w-1/2">
                                            {{ __('translation.Cost Per Piece') }}
                                        </td>
                                        <td class="w-1 py-2 align-top">:</td>
                                        <td class="px-4 py-2 align-top">
                                            <span id="__product_cost_costModalDetail" class="font-bold"></span>
                                        </td>
                                    </tr>
                                    <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                        <td class="px-4 py-2 align-top sm:w-1/2">
                                            {{ __('translation.Currency Name') }}
                                        </td>
                                        <td class="w-1 py-2 align-top">:</td>
                                        <td class="px-4 py-2 align-top">
                                            <span id="__product_cost_exchange_nameModalDetail" class="font-bold"></span>
                                        </td>
                                    </tr>
                                    <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                        <td class="px-4 py-2 align-top sm:w-1/2">
                                            {{ __('translation.Currency Rate') }}
                                        </td>
                                        <td class="w-1 py-2 align-top">:</td>
                                        <td class="px-4 py-2 align-top">
                                            <span id="__product_cost_exchange_rateModalDetail" class="font-bold"></span>
                                        </td>
                                    </tr>
                                    <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                        <td class="px-4 py-2 align-top sm:w-1/2">
                                            {{ __('translation.Pieces Per Pack') }}
                                        </td>
                                        <td class="w-1 py-2 align-top">:</td>
                                        <td class="px-4 py-2 align-top">
                                            <span id="__product_cost_pieces_per_packModalDetail" class="font-bold"></span>
                                        </td>
                                    </tr>
                                    <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                        <td class="px-4 py-2 align-top sm:w-1/2">
                                            {{ __('translation.Pieces Per Carton') }}
                                        </td>
                                        <td class="w-1 py-2 align-top">:</td>
                                        <td class="px-4 py-2 align-top">
                                            <span id="__product_cost_pieces_per_cartonModalDetail" class="font-bold"></span>
                                        </td>
                                    </tr>
                                    <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                        <td class="px-4 py-2 align-top sm:w-1/2">
                                            {{ __('translation.Ship Cost') }}
                                        </td>
                                        <td class="w-1 py-2 align-top">:</td>
                                        <td class="px-4 py-2 align-top">
                                            <span id="__product_cost_operation_costModalDetail" class="font-bold">0</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex flex-row items-center justify-center">
                    <x-button type="button" color="green" class="btn-close__modalDetail">
                        {{ __('translation.Close') }}
                    </x-button>
                </div>
            </x-modal.body>
        </x-modal.modal-medium>
    @endif


    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.2.2/dist/sweetalert2.min.css">
    @endpush

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.2.2/dist/sweetalert2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
        <script src="{{ asset('pages/seller/report/stock-value/index/table.js?_=' . rand()) }}"></script>
        <script src="{{ asset('pages/seller/report/stock-value/index/summary.js?_=' . rand()) }}" defer></script>
    @endpush

</x-app-layout>
