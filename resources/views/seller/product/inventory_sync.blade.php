<x-app-layout>
    @section('title')
        Inventory Sync
    @endsection

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Product'))
        <div class="col-span-12">
            <x-card.card-default>
                <x-card.header>
                    <x-card.title>
                        Inventory Sync
                    </x-card.title>
                </x-card.header>
                <x-card.body>
                    <div>
                        @if (session('success'))
                            <x-alert-success>
                                {{ session('success') }}
                            </x-alert-success>
                        @endif

                        @if (session('danger'))
                            <x-alert-danger>
                                {{ session('danger') }}
                            </x-alert-danger>
                        @endif

                        @if (session('error'))
                            <x-alert-danger>
                                {{ session('error') }}
                            </x-alert-danger>
                        @endif

                        @if ($errors->any())
                            <x-alert-danger>
                                <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </x-alert-danger>
                        @endif

                        <div class="flex flex-col justify-between items-start gap-x-8 mb-6 p-3 border-solid border-gray-300 rounded-lg sm:flex-row md:px-4">
                            <div class="w-full sm:w-2/5 sm:py-2 md:w-1/4">
                                <div class="mb-6">
                                    <img src="{{ $productSync->image_url }}" alt="{{ $productSync->product_name }}" class="w-full h-auto">
                                </div>
                                <div class="mb-3">
                                    @if (! $productSync->productTags->isEmpty())
                                        <span>
                                            @foreach ($productSync->productTags as $tag)
                                                <span class="badge-status--yellow my-2">{{ ucwords($tag->name) }}</span>
                                            @endforeach
                                        </span>
                                        <br>
                                    @endif
                                    <span class="text-base font-bold">
                                        {{ $productSync->product_name }}
                                    </span><br>
                                    <span class="text-blue-500 font-bold">{{ $productSync->product_code }}</span>
                                </div>
                                <div class="mb-3">
                                    <span class="font-bold text-lg">
                                        {{ currency_symbol('THB') }}
                                        {{ number_format($productSync->price, 2, '.', '') }}</span>
                                </div>
                            </div>
                            <div class="w-full sm:w-3/5 md:w-3/4">
                                <div class="my-3">
                                    <table class="w-full">
                                        <tbody>
                                            <tr>
                                                <td class="block align-top md:table-cell md:pr-2 md:py-1 lg:w-40">
                                                    {{ __('translation.Product ID') }}
                                                </td>
                                                <td class="hidden w-1 px-2 py-1 align-top md:table-cell">:</td>
                                                <td class="block mb-2 align-top md:table-cell md:px-2 md:py-1">
                                                    <span class="font-bold">
                                                        {{ $productSync->id }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="block align-top md:table-cell md:pr-2 md:py-1 lg:w-40">
                                                    {{ __('translation.Quantity') }}
                                                </td>
                                                <td class="hidden w-1 px-2 py-1 align-top md:table-cell">:</td>
                                                <td class="block mb-2 align-top md:table-cell md:px-2 md:py-1">
                                                    <span class="font-bold">
                                                        {{ number_format($productSync->quantity) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="block align-top md:table-cell md:pr-2 md:py-1 lg:w-40">
                                                    {{ __('translation.Pieces') . '/' . __('translation.Pack') }}
                                                </td>
                                                <td class="hidden w-1 px-2 py-1 align-top md:table-cell">:</td>
                                                <td class="block mb-2 align-top md:table-cell md:px-2 md:py-1">
                                                    <span class="font-bold">
                                                        {{ number_format($productSync->pack) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="block align-top md:table-cell md:pr-2 md:py-1 lg:w-40">
                                                    {{ __('translation.Incoming') }}
                                                </td>
                                                <td class="hidden w-1 px-2 py-1 align-top md:table-cell">:</td>
                                                <td class="block mb-2 align-top md:table-cell md:px-2 md:py-1">
                                                    <span class="font-bold">
                                                        {{ number_format($productSync->total_incoming) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="block align-top md:table-cell md:pr-2 md:py-1 lg:w-40">
                                                    {{ __('translation.Last Updated') }}
                                                </td>
                                                <td class="hidden w-1 px-2 py-1 align-top md:table-cell">:</td>
                                                <td class="block mb-2 align-top md:table-cell md:px-2 md:py-1">
                                                    <span class="font-bold">
                                                        {{ date('F d, Y', strtotime($productSync->updated_at)) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="block align-top md:table-cell md:pr-2 md:py-1 lg:w-40">
                                                    {{ __('translation.Linked WooCommerce Products') }}
                                                </td>
                                                <td class="hidden w-1 px-2 py-1 align-top md:table-cell">:</td>
                                                <td class="block mb-2 align-top md:table-cell md:px-2 md:py-1">
                                                    <span class="font-bold">
                                                        @if ($linkedWooProductCodes) {{ $linkedWooProductCodes }} @else - @endif
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="block align-top md:table-cell md:pr-2 md:py-1 lg:w-40">
                                                    {{ __('translation.Linked Shopee Products') }}
                                                </td>
                                                <td class="hidden w-1 px-2 py-1 align-top md:table-cell">:</td>
                                                <td class="block mb-2 align-top md:table-cell md:px-2 md:py-1">
                                                    <span class="font-bold">
                                                        @if ($linkedShopeeProductCodes) {{ $linkedShopeeProductCodes }} @else - @endif
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="block align-top md:table-cell md:pr-2 md:py-1 lg:w-40">
                                                    {{ __('translation.Linked Lazada Products') }}
                                                </td>
                                                <td class="hidden w-1 px-2 py-1 align-top md:table-cell">:</td>
                                                <td class="block mb-2 align-top md:table-cell md:px-2 md:py-1">
                                                    <span class="font-bold">
                                                        @if ($linkedLazadaProductCodes) {{ $linkedLazadaProductCodes }} @else - @endif
                                                    </span>
                                                </td>
                                            </tr>
                                            @if($productSync->child_of)
                                                <tr bgcolor="#f5f5dc">
                                                    <td class="block align-top md:table-cell md:pr-2 md:py-1 lg:w-40">
                                                        {{ __('translation.Linked to') }}
                                                    </td>
                                                    <td class="hidden w-1 px-2 py-1 align-top md:table-cell">:</td>
                                                    <td class="block mb-2 align-top md:table-cell md:px-2 md:py-1">
                                                    <span class="font-bold">
                                                        <a href="{{ route("product.inventory_sync", ["id" => product_id_by_code($productSync->child_of)]) }}" target="_blank">{{ $productSync->child_of }}</a>
                                                    </span>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <span class="rounded-md px-3 py-1 bg-red-200 text-red-600 font-bold">
                                        {{ $totalProductsSelectedCount }} Product(s) Linked
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col justify-between mb-4 lg:flex-row lg:gap-x-4">
                            <div class="w-full mb-2 lg:w-3/5">
                                <div class="flex flex-col items-stretch justify-between gap-2 sm:flex-row">
                                    <x-select id="__selectChannelFilter">
                                        <option disabled>
                                            - {{ __('translation.Select Sales Channel') }} -
                                        </option>
                                        <option value="woo" selected>
                                            Woocommerce ({{ $wooProductSelectedCount }})
                                        </option>
                                        <option value="shopee">
                                            Shopee ({{ $shopeeProductsSelectedCount }})
                                        </option>
                                        <option value="lazada">
                                            Lazada ({{ $lazadaProductsSelectedCount }})
                                        </option>
                                    </x-select>
                                    <x-select id="__selectShopFilter">
                                        <option value="0">
                                            - {{ __('translation.Select Shop') }} -
                                        </option>
                                        @if(isset($shops))
                                            @foreach($shops as $shop)
                                                <option value="{{$shop->id}}">{{$shop->shops->name}}</option>
                                            @endforeach
                                        @endif
                                    </x-select>
                                    <x-select id="__selectionFilter">
                                        <option disabled>
                                            - {{ __('translation.Sort by') }} -
                                        </option>
                                        <option value="all" selected>
                                            {{ __('translation.All') }} ({{ $wooProductsCount }})
                                        </option>
                                        <option value="selected">
                                            {{ __('translation.Selected') }} ({{ $wooProductSelectedCount }})
                                        </option>
                                        <option value="available">
                                            {{ __('translation.Available') }} ({{ $wooProductAvailableCount }})
                                        </option>
                                        <option value="unavailable">
                                            {{ __('translation.Unavailable') }} ({{ $wooProductUnavailableCount }})
                                        </option>
                                    </x-select>
                                </div>
                            </div>
                            <div class="w-full overflow-x-auto px-2 py-3 sm:px-0 lg:w-2/5 lg:py-0">
                                <div class="flex flex-row items-start gap-2 sm:flex-row lg:justify-end">
                                    <x-button color="yellow" id="__btnAutoLink">
                                        {{ __('translation.Auto Link') }}
                                    </x-button>
                                    @if($productSync->child_of)
                                        <x-button color="blue" disabled>
                                            {{ __('translation.Sync Quantity')  }}
                                        </x-button>
                                    @else
                                        <x-button color="blue" id="__btnSyncQuantity">
                                            {{ __('translation.Sync Quantity')  }}
                                        </x-button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full overflow-x-auto">
                        <table class="w-full table" id="__productSyncTable">
                            <thead>
                                <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                                    <th></th>
                                    <th class="px-4 py-2 text-center">
                                        {{ __('translation.Image') }}
                                    </th>
                                    <th class="px-4 py-2 text-center">
                                        {{ __('translation.Product Name') }}
                                    </th>
                                    <th class="px-4 py-2 text-center">
                                        {{ __('translation.Website') }}
                                    </th>
                                    <th class="px-4 py-2 text-center">
                                        {{ __('translation.Type') }}
                                    </th>
                                    <th class="px-4 py-2">
                                        {{ __('translation.Price') . '/' . __('translation.Pack') }}
                                    </th>
                                    <th class="px-4 py-2">
                                        {{ __('translation.Quantity') }}
                                    </th>
                                    <th class="px-4 py-2">
                                        {{ __('translation.Inventory Link') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </x-card.body>
            </x-card.card-default>

            <x-modal.modal-small id="__modalConfirmAutoLink">
                <x-modal.header>
                    <x-modal.title>
                        {{ __('translation.Confirm') }}
                    </x-modal.title>
                </x-modal.header>
                <x-modal.body>
                    <x-alert-danger id="__alertDangerConfirmAutoLink" class="alert hidden" />
                    <p class="text-center mb-5">
                        {{ __('translation.Are you sure auto link this product?') }}
                    </p>
                    <div class="flex flex-row items-center justify-center gap-4 pb-3">
                        <x-button type="button" color="gray" id="__btnCancelConfirmAutoLink">
                            {{ __('translation.No, Close') }}
                        </x-button>
                        <x-button type="button" color="blue" id="__btnSubmitConfirmAutoLink">
                            {{ __('translation.Yes, Continue') }}
                        </x-button>
                    </div>
                </x-modal.body>
            </x-modal.modal-small>

            <x-modal.modal-small id="__modalConfirmSyncQty">
                <x-modal.header>
                    <x-modal.title>
                        {{ __('translation.Confirm') }}
                    </x-modal.title>
                </x-modal.header>
                <x-modal.body>
                    <x-alert-danger id="__alertDangerConfirmSyncQty" class="alert hidden" />
                    <p class="text-center mb-5">
                        {{ __('translation.Are you sure to sync the quantity?') }}
                    </p>
                    <div class="flex flex-row items-center justify-center gap-4 pb-3">
                        <x-button type="button" color="gray" id="__btnCancelConfirmSyncQty">
                            {{ __('translation.No, Close') }}
                        </x-button>
                        <x-button type="button" color="blue" id="__btnSubmitConfirmSyncQty">
                            {{ __('translation.Yes, Continue') }}
                        </x-button>
                    </div>
                </x-modal.body>
            </x-modal.modal-small>
        </div>
    @endif


    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.0/dist/sweetalert2.min.css">
    @endpush


    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.0/dist/sweetalert2.min.js"></script>

        <script>
            const productSyncId = {{ $productSync->id }};
            // const saveMultipleProductLinksUrl = '{{route('product.save_multiple_links')}}';
            // var selectedRows = [];
        </script>

        <script src="{{ asset('pages/seller/product/inventory-sync/table.js?_=' . rand()) }}"></script>
        <script src="{{ asset('pages/seller/product/inventory-sync/auto-link.js?_=' . rand()) }}"></script>
        <script src="{{ asset('pages/seller/product/inventory-sync/save-link.js?_=' . rand()) }}"></script>
        <script src="{{ asset('pages/seller/product/inventory-sync/sync-quantity.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
