<x-app-layout>

    @section('title')
        {{ ucfirst(__('translation.WooCommerce Products')) }}
    @endsection

    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">
    @endpush

    @push('bottom_css')
        <link rel="stylesheet" href="{{ asset('css/datatable-custom-toolbar.css?_=' . rand()) }}">
    @endpush

    @if(\App\Models\Role::checkRolePermissions('Can access menu: WooCommerce - Product'))

    <div class="col-span-12">

        <x-card.card-default>
            <x-card.header>
                <x-card.title>
                    {{ ucwords(__('translation.WooCommerce Products')) }}
                </x-card.title>
            </x-card.header>
            <x-card.body>

                @if(session()->has('error'))
                    <div class="alert alert-danger mb-3 background-danger" role="alert">
                        {{ session()->get('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session()->has('success'))
                    <div class="alert alert-success mb-3 background-success" role="alert">
                    {{ session()->get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div id="messageStatus"></div>

                <x-alert-info id="__alertInfoWooProductTable" class="alert hidden"></x-alert-info>
                <x-alert-success id="__alertSuccessWooProductTable" class="alert hidden"></x-alert-success>

                <div class="w-full sm:w-4/4 lg:w-4/4 mb-4">
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                        <x-select id="website_id" name="website_id" class="select__shop">
                            <option value="" disabled selected>
                                - {{ ucwords(__('translation.select_shop')) }} -
                            </option>
                            <option value="-1">
                                {{ ucfirst(__('translation.all')) }}
                            </option>

                            @if (isset($wooShops))
                                @foreach ($wooShops as $wooShop)
                                    <option data-site_url="{{ $wooShop->site_url }}" data-key="{{ $wooShop->rest_api_key }}" data-secrete="{{ $wooShop->rest_api_secrete }}" value="{{ $wooShop->id }}">
                                        {{ $wooShop->shops->name }}
                                    </option>
                                @endforeach
                            @endif
                        </x-select>

                        <x-select name="inventory_link" id="inventory_link" class="select__inventory-status">
                            <option value="" disabled selected>
                                - {{ ucwords(__('translation.select_inventory_status')) }} -
                            </option>
                            <option value="-1">
                                {{ ucwords(__('translation.all')) }}
                            </option>
                            <option value="0">
                                {{ ucwords(__('translation.no_inventory')) }}
                            </option>
                            <option value="1">
                                {{ ucwords(__('translation.has_inventory')) }}
                            </option>
                        </x-select>

                        <x-select name="type" id="type" class="select__type">
                            <option value="" disabled selected>
                                - {{ ucwords(__('translation.select_product_type')) }} -
                            </option>
                            <option value="-1">
                                {{ ucwords(__('translation.all')) }}
                            </option>
                            <option value="simple">
                                {{ ucwords(__('translation.Simple')) }}
                            </option>
                            <option value="ex_variable">
                                {{ ucwords(__('translation.exclude_variable')) }}
                            </option>
                        </x-select>
                        <x-select name="discount_range" id="discount_range" class="discount_range">
                            <option value="" disabled selected>
                                - {{ ucwords(__('translation.Select Range')) }} -
                            </option>
                            <option value="1">
                                {{ ucwords(__('translation.High To low')) }}
                            </option>
                            <option value="2">
                                {{ ucwords(__('translation.Low To High')) }}
                            </option>
                        </x-select>
                    </div>
                </div>

                <div class="hidden">
                    <x-button type="button" color="yellow" class="btn__sync-selected" disabled="true">
                        <i class="bi bi-list-check text-base"></i>
                        <span class="btn__sync-selected__text ml-2 mr-1">
                            {{ __('translation.sync_selected') }}
                        </span>
                        (<span class="btn__sync-selected__count">0</span>)
                    </x-button>

                    <x-button color="blue" class="btn__sync-product">
                        <i class="bi bi-arrow-repeat text-base"></i>
                        <span class="ml-2">
                            {{ __('translation.sync_product') }}
                        </span>
                    </x-button>

                    <x-button-link color="green" href="{{ route('wc-products.export-excel-linked-catalog') }}" class="btn__export-excel-linked-catalog">
                        <i class="bi bi-download text-base"></i>
                        <span class="ml-2">
                            {{ __('translation.Export Linked Catalog') }}
                        </span>
                    </x-button-link>

                    <x-button-link color="green" target="_blank" href="{{ route('wc-products-create') }}" class="btn__export-excel-linked-catalog">
                        <i class="bi bi-plus text-base"></i>
                        <span class="ml-2">
                            {{ __('translation.Add New Product') }}
                        </span>
                    </x-button-link>
                </div>

                <x-alert-info id="__alertInfoWooProductTable" class="alert hidden"></x-alert-info>
                <x-alert-success id="__alertSuccessWooProductTable" class="alert hidden"></x-alert-success>
                <x-alert-danger id="__alertDangerWooProductTable" class="alert hidden"></x-alert-danger>

                <div class="w-full overflow-x-auto">
                    <table class="w-full" id="wooProductTable">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-center bg-blue-500 text-white"></th>
                                <th class="px-4 py-2 text-center bg-blue-500 text-white">
                                    {{ ucfirst(__('translation.detail')) }}
                                </th>
                                <th class="w-24 px-4 py-2 text-center bg-blue-500 text-white">
                                    {{ ucfirst(__('translation.Action')) }}
                                </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </x-card.body>
        </x-card.card-default>

    </div>


    <x-modal.modal-medium id="__modalSyncProduct">
        <x-modal.header>
            <x-modal.title>
                {{ ucwords(__('translation.sync_product')) }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>

            <x-alert-info id="__alertInfoSyncProduct" class="alert hidden"></x-alert-info>
            <x-alert-danger id="__alertDangerSyncProduct" class="alert hidden"></x-alert-danger>

            <form action="{{ route('wc_products_sync') }}" method="get" id="__formSyncProduct">
                @csrf

                <div class="mb-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <x-label for="__shop_idSyncProduct">
                                {{ ucwords(__('translation.shop')) }} <x-form.required-mark/>
                            </x-label>
                            <x-select name="shop_id" id="__shop_idSyncProduct">
                                <option value="0" disabled selected>
                                    - {{ ucwords(__('translation.select_shop')) }} -
                                </option>
                                @if (isset($wooShops))
                                    @foreach ($wooShops as $wooShop)
                                        <option value="{{ $wooShop->id }}">
                                            {{ $wooShop->shops->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </x-select>
                        </div>
                        <div>
                            <x-label for="__number_of_productsSyncProduct">
                                {{ ucwords(__('translation.sync_record_total')) }} <x-form.required-mark/>
                            </x-label>
                            <x-input type="number" name="number_of_products" id="__number_of_productsSyncProduct" placeholder="Enter -1 for ALL" />
                        </div>
                    </div>
                </div>

                <div class="pb-3">
                    <div class="flex flex-row items-center justify-center gap-2">
                        <x-button type="reset" color="gray" id="__btnCancelSyncProduct">
                            {{ ucwords(__('translation.cancel')) }}
                        </x-button>
                        <x-button type="submit" color="blue" id="__btnSubmitSyncProduct">
                            {{ ucwords(__('translation.load_data')) }}
                        </x-button>
                    </div>
                </div>
            </form>

        </x-modal.body>
    </x-modal.modal-medium>


    <x-modal.modal-medium id="__modalEditProduct">
        <x-modal.header>
            <x-modal.title>
                {{ ucwords(__('translation.update_product')) }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerEditProduct" class="alert hidden"></x-alert-danger>

            <div class="my-3" id="__alertMessageIfHasParent" style="display: none">
                <p class="text-center text-red-500">
                    {{ ucwords(__('translation.product_name')) . ' ' . strtolower(__('translation.cannot_be_updated_from_the_child')) }}
                </p>
            </div>

            <div class="my-3" id="__alertMessageIfVariable" style="display: none">
                <p class="text-center text-red-500">
                    {{ ucfirst(__('translation.qty_and_Price_does_not_allow_to_change_from_parent_product')) }}
                </p>
            </div>

            <form action="{{ route('wc_products.update') }}" method="post" id="__formEditProduct">
                @csrf

                <input type="hidden" name="id" id="__idEditProduct">

                <div class="grid grid-cols-2 gap-4 gap-x-8">
                    <div class="col-span-2">
                        <x-label>
                            {{ ucwords(__('translation.product')) . ' ID' }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="text" id="__idDisplayEditProduct" class="bg-gray-200" readonly />
                    </div>
                    <div class="col-span-2">
                        <x-label for="__product_nameEditProduct">
                            {{ ucwords(__('translation.product_name')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="text" name="product_name" id="__product_nameEditProduct" />
                    </div>
                    <div class="col-span-2">
                        <x-label for="__product_codeEditProduct">
                            {{ ucwords(__('translation.product_code')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="text" name="product_code" id="__product_codeEditProduct" />
                    </div>
                    <div>
                        <x-label for="__priceEditProduct">
                            {{ ucwords(__('translation.price')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="number" name="price" id="__priceEditProduct" steps="0.001" />
                    </div>
                    <div>
                        <x-label for="__quantityEditProduct">
                            {{ ucwords(__('translation.quantity')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="number" name="quantity" id="__quantityEditProduct" />
                    </div>
                </div>
                <div class="mt-4 pb-3">
                    <div class="flex flex-row items-center justify-center gap-2">
                        <x-button type="reset" color="gray" id="__btnCancelEditProduct">
                            {{ __('translation.cancel') }}
                        </x-button>
                        <x-button type="submit" color="blue" id="__btnSubmitEditProduct">
                            {{ __('translation.update_data') }}
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal.body>
    </x-modal.modal-medium>


    <x-modal.modal-small id="__modalDeleteProduct">
        <x-modal.header>
            <x-modal.title>
                {{ ucwords(__('translation.delete_product')) }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerDeleteProduct" class="alert hidden"></x-alert-danger>

            <form action="{{ route('wc_products.delete') }}" method="post" id="__formDeleteProduct">
                @csrf
                <input type="hidden" name="id" id="__idDeleteProduct">

                <p class="text-center">
                    {{ ucfirst(__('translation.are_you_sure_to_delete_this_product')) . '?' }}
                </p>

                <div class="mt-4 pb-3">
                    <div class="flex flex-row items-center justify-center gap-2">
                        <x-button type="reset" color="gray" id="__btnCancelDeleteProduct">
                            {{ __('translation.no') . ', ' . __('translation.close') }}
                        </x-button>
                        <x-button type="submit" color="red" id="__btnSubmitDeleteProduct">
                            {{ __('translation.yes') . ', ' . __('translation.delete') }}
                        </x-button>
                    </div>
                </div>
            </form>
        </x-modal.body>
    </x-modal.modal-small>


    <x-modal.modal-large id="__modalEditLinkedCatalog">
        <x-modal.header>
            <x-modal.title>
                {{ ucwords(__('translation.edit_linked_catalog')) }}
            </x-modal.title>
            <x-modal.close-button id="__btnCloseModalEditLinkedCatalog" />
        </x-modal.header>
        <x-modal.body>
            <div class="border border-dashed border-gray-300 rounded-md p-4 mb-5 bg-gray-50">
                <h4 class="font-bold mb-3">
                    {{ ucwords(__('translation.linked_catalog')) }}
                </h4>
                <div id="__linkedProductNotFoundWrapper">
                    <span class="italic">
                        {{ ucwords(__('translation.no_linked_product')) }}
                    </span>
                </div>
                <div id="__linkedProductFoundWrapper" style="display: none">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 md:gap-x-8">
                        <div class="md:col-span-3">
                            <span class="block whitespace-nowrap mb-1 text-gray-500">
                                {{ ucwords(__('translation.product_name')) }}
                            </span>
                            <span class="font-bold" id="__linkedCatalogProductName"></span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="block whitespace-nowrap mb-1 text-gray-500">
                                {{ ucwords(__('translation.product_code')) }}
                            </span>
                            <span class="font-bold" id="__linkedCatalogProductCode"></span>
                        </div>
                    </div>
                </div>
            </div>

            <x-alert-success id="__alertSuccessEditLinkedCatalog" class="alert hidden"></x-alert-success>
            <x-alert-danger id="__alertDangerEditLinkedCatalog" class="alert hidden"></x-alert-danger>

            <div class="w-full overflow-x-auto">
                <table class="w-full" id="__tblLinkedCatalog">
                    <thead>
                        <tr>
                            <th class="md:w-36 px-4 py-2 bg-blue-500 text-white">
                                ID
                            </th>
                            <th class="md:w-auto px-4 py-2 bg-blue-500 text-white">
                                {{ ucwords(__('translation.product_details')) }}
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
        </x-modal.body>
    </x-modal.modal-large>

    @endif

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>

        <script>
            const wooProductDatatableUrl = '{{ route('wc_products') }}';
            const syncSelectedUrl = '{{ route('bulkSync') }}';
            const linkedCatalogDatatableUrl = '{{ route('wc-product.linked-catalog.datatable') }}';
            const linkWooProductToCatalogUrl = '{{ route('wc-product.linked-catalog.store') }}';

            const textSyncSelected = '{{ __('translation.sync_selected') }}';
            const textSyncProduct = '{{ __('translation.sync_product') }}';
            const textSyncing = '{{ __('translation.syncing') }}';
            const textProcessing = '{{ __('translation.processing') }}';
            const textLoadData = '{{ __('translation.load_data') }}';
            const textUpdateData = '{{ __('translation.update_data') }}';
            const textYesDelete = '{{ __('translation.yes') . ', ' . __('translation.delete') }}';

            let WCProductId = 0;
            let selectedWebsiteId = ''; // -1 or null or empty value for ALL
            let selectedInventoryStatus = ''; // -1 or null or empty value for ALL
            let selectedType = ''; // -1 or null or empty value for ALL
            let discount_range = ''; // 1 High to low and 2 low to high
        </script>

        <script src="{{ asset('pages/seller/wc_products/index/sync_product.js?_=' . rand()) }}"></script>
        <script src="{{ asset('pages/seller/wc_products/index/sync_selected.js?_=' . rand()) }}"></script>
        <script src="{{ asset('pages/seller/wc_products/index/table.js?_=' . rand()) }}"></script>
        <script src="{{ asset('pages/seller/wc_products/index/linked-catalog.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
