<x-app-layout>

    @section('title')
        {{ ucwords(__('translation.shopee_products')) }}
    @endsection

    @push('top_css')
        <style>
            .missing_info_messages .alert {
                padding: 5px 10px;
                margin-bottom: 5px;
            }
        </style>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">
    @endpush

    @push('bottom_js')
        <link rel="stylesheet" href="{{ asset('pages/seller/wc_products/index/index.css?_=' . rand()) }}">
    @endpush

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Shopee - Product'))
        <div class="col-span-12">

            <x-card.card-default>
                <x-card.header>
                    <x-card.title>
                        {{ ucwords(__('translation.shopee_products')) }}
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

                    <div class="w-full mb-4">
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                            <x-select name="website_id" id="website_id" class="select-shop">
                                <option value="" disabled selected>
                                    - {{ ucwords(__('translation.select_shop')) }} -
                                </option>
                                <option value="-1">
                                    {{ ucfirst(__('translation.all')) }}
                                </option>

                                @if (isset($shops))
                                    @foreach ($shops as $shop)
                                        <option value="{{ $shop->shop_id }}">
                                            {{ $shop->shop_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </x-select>

                            <x-select name="inventory_link" id="inventory_link" class="inventory-link">
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
                                <option value="ex_variable">
                                    {{ ucwords(__('translation.exclude_variable')) }}
                                </option>
                            </x-select>

                            <x-select name="missing_info" id="missing_info" class="missing_info">
                                <option value="" disabled selected>
                                    - {{ ucwords(__('translation.select_missing_info')) }} -
                                </option>
                                @foreach($missing_info_options as $key => $val)
                                <option value="{{$key}}">
                                    {{ $val }}
                                </option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>

                    <div class="hidden">
                        <x-button type="button" color="yellow" class="btn__sync-selected" disabled="true">
                            <i class="bi bi-list-check text-base"></i>
                            <span class="ml-2">
                                {{ ucwords(__('translation.sync_selected')) }}
                            </span>
                        </x-button>

                        <x-button type="button" color="green" class="btn__sync-product">
                            <i class="bi bi-arrow-repeat text-base"></i>
                            <span class="ml-2">
                                {{ ucwords(__('translation.sync_product')) }}
                            </span>
                        </x-button>

                        <x-button-link color="blue" href="{{ route('shopee.product.create_page') }}" class="btn__create-product" target="_blank">
                            <i class="bi bi-pencil"></i>
                            <span class="ml-2">
                                {{ ucwords(__('translation.create_product')) }}
                            </span>
                        </x-button-link>

                        <x-button-link color="green" href="{{ route('shopee.export-excel-linked-catalog') }}" class="btn__export-excel-linked-catalog">
                            <i class="bi bi-download"></i>
                            <span class="ml-2">
                                {{ ucwords(__('translation.Export Linked Catalog')) }}
                            </span>
                        </x-button-link>
                    </div>

                    <x-alert-success id="__alertSuccessShopeeTable" class="alert hidden"></x-alert-success>
                    <x-alert-danger id="__alertDangerShopeeTable" class="alert hidden"></x-alert-danger>

                    <div class="w-full mt-4 overflow-x-auto">
                        <table class="w-full" id="shopeeTable">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 bg-blue-500 text-white">
                                        &nbsp;
                                    </th>
                                    <th class="px-4 py-2 bg-blue-500 text-white">
                                        {{ ucwords(__('translation.details')) }}
                                    </th>
                                    <th class="w-24 px-4 py-2 bg-blue-500 text-white">
                                        {{ ucwords(__('translation.action')) }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </x-card.body>
            </x-card.card-default>
        </div>


        <x-modal.modal-small id="__modalSyncProduct">
            <x-modal.header>
                <x-modal.title>
                    {{ ucwords(__('translation.sync_product')) }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>

                <x-alert-info id="__alertInfoSyncProduct" class="alert hidden"></x-alert-info>
                <x-alert-danger id="__alertDangerSyncProduct" class="alert hidden"></x-alert-danger>

                <form action="{{ route('shopee.product.sync') }}" method="POST" id="__formSyncProduct">
                    @csrf

                    <div class="grid grid-cols-1 gap-4 sm:gap-x-8">
                        <div>
                            <x-label id="__shop_idSyncProduct">
                                {{ ucwords(__('translation.shop')) }}
                            </x-label>
                            <x-select name="shop_id" id="__shop_idSyncProduct">
                                <option value="">
                                    {{ ucwords(__('translation.select_shop')) }}
                                </option>
                                @if (isset($shops))
                                    @foreach ($shops as $shop)
                                        <option value="{{ $shop->shop_id }}">
                                            {{ $shop->shop_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </x-select>
                        </div>
                        <div>
                            <x-label>
                                {{ ucwords(__('translation.sync_record_total')) }}
                            </x-label>
                            <x-input type="number" name="number_of_products" id="__number_of_productsSyncProduct" placeholder="{{ __('translation.Enter -1 for All') }}" autocomplete="off" />
                        </div>
                    </div>

                    <div class="mt-4 pb-3">
                        <div class="flex flex-row items-center justify-center gap-2">
                            <x-button type="reset" color="gray" id="__btnCancelSyncProduct">
                                {{ __('translation.cancel') }}
                            </x-button>
                            <x-button type="submit" color="blue" id="__btnSubmitSyncProduct">
                                {{ __('translation.load_data') }}
                            </x-button>
                        </div>
                    </div>
                </form>
            </x-modal.body>
        </x-modal.modal-small>


        <div class="modal fade" id="addProduct" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">
                            <strong>Add inventory</strong>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="{{route("inventoryProductAdd")}}" class="inventoryToProduct">
                            @csrf
                            <div class="row" style="">
                                <div class="col-lg-12">
                                    <div class="alert alert-warning alert-dismissible fade show d-none" role="alert" id="modal_error_inventory_div_2">
                                        <div class="modal_error_inventory_2"></div>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <lebel for="product_id">Inventory</lebel>
                                    <select search="search" class=" product_id form-controll js-data-example-ajax product_id"  id="product_id" name="inventory_id"></select>
                                </div>
                                <input type="hidden" id="inventory_id" name="product_id"  class="inventory_id">
                                <div class="col-lg-12"></div>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">Close</button>
                        <button id="btn_add_invitory" type="button" class="btn btn-success mt-16">Add</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>


        <x-modal.modal-medium id="__modalEditProduct">
            <x-modal.header>
                <x-modal.title>
                    {{ ucwords(__('translation.update_product')) }}
                </x-modal.title>
                <x-modal.close-button class="btn-close_edit-product" />
            </x-modal.header>
            <x-modal.body>
                <div id="form-producut"></div>
            </x-modal.body>
        </x-modal.modal-medium>


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
            const shopeeProductTableUrl = '{{ route('shopee.products') }}';
            const linkedCatalogDatatableUrl = '{{ route('shopee.linked-catalog.datatable') }}';
            const linkWooProductToCatalogUrl = '{{ route('shopee.linked-catalog.store') }}';
            const syncSelectedUrl = '{{ route('bulkSync') }}';

            const textSyncing = '{{ __('translation.syncing') }}';
            const textLoadData = '{{ __('translation.load_data') }}';

            let shopeeProductId = 0;

            let selectedWebsiteId = '';
            let selectedInventoryStatus = '';
            let selectedType = '';
            let missingInfo = '';

            $('body').on('change', '#shop', function(){
                localStorage.setItem('shop_id', $(this).val());
            });

            $('body').on('change', '.select-shop', function() {
                selectedWebsiteId = $(this).val();
                loadShopeeTable(selectedWebsiteId, selectedInventoryStatus, selectedType, missingInfo);
            });

            $('body').on('change', '.inventory-link', function() {
                selectedInventoryStatus = $(this).val();
                loadShopeeTable(selectedWebsiteId, selectedInventoryStatus, selectedType, missingInfo);
            });

            $('body').on('change', '.select__type', function() {
                selectedType = $(this).val();
                loadShopeeTable(selectedWebsiteId, selectedInventoryStatus, selectedType, missingInfo);
            });

            $('body').on('change', '#missing_info', function() {
                missingInfo = $(this).val();
                loadShopeeTable(selectedWebsiteId, selectedInventoryStatus, selectedType, missingInfo);
            });

            const loadShopeeTable = (websiteId = null, inventoryStatus = null, type = null, missingInfo = null) => {
                $('#shopeeTable').DataTable({
                    bDestroy: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        type: 'GET',
                        url: '{{ route("shopee.products") }}',
                        data: {
                            website_id: websiteId,
                            inventory_status: inventoryStatus,
                            missing_info: missingInfo,
                            type: type
                        }
                    },
                    dom: '<"#dt-top-toolbar">frt<"#dt-bottom-toolbar"lip><"clear">',
                    initComplete: function () {
                        $('#dt-top-toolbar').append(
                            $('<div/>', {
                                class: 'flex flex-col sm:flex-row items-center gap-2 mb-4'
                            }).append(
                                $('<div/>', {
                                    class: 'flex flex-row items-center justify-center flex-wrap gap-2'
                                }).append(
                                    $('.btn__sync-selected').clone(),
                                    $('.btn__sync-product').clone(),
                                    $('.btn__create-product').clone(),
                                    $('.btn__export-excel-linked-catalog').clone()
                                )
                            )
                        );

                        if (typeof($("#shopeeTable_length").find("select").attr("id")) === "undefined") {
                            $("#shopeeTable_length").find("select").attr("id", "shopee_table_page_length");
                            setTimeout(function() {
                                checkForShopeeProductMissingInfo();
                            }, 1000);
                        }
                    },
                    columns: [
                        {
                            name: 'checkbox',
                            data: 'checkbox',
                            checkboxes: {
                                'selectRow': true
                            },
                            orderable: false
                        },
                        {
                            name: 'details',
                            data: 'details'
                        },
                        {
                            name: 'action',
                            data: 'action',
                            orderable: false,
                            className: 'text-center'
                        }
                    ],
                    select : {
                        style: 'multi'
                    },
                    createdRow: function (row, data, dataIndex) {
                        $(row).find('td:eq(0)').attr('class', data.type);
                    },
                });
            }

            loadShopeeTable();

            $('body').on('click', '.btn__sync-product', function () {
                $('#__modalSyncProduct').doModal('open');
            });


            $('#__btnCancelSyncProduct').on('click', function () {
                $('#__modalSyncProduct').doModal('close');
                $('.alert').addClass('hidden').find('.alert-content').html(null);
            });


            $('#__formSyncProduct').on('submit', function (event) {
                event.preventDefault();

                const actionUrl = $(this).attr('action');
                const formData = new FormData($(this)[0]);

                $.ajax({
                    type: 'POST',
                    url: actionUrl,
                    processData: false,
                    contentType: false,
                    data: formData,
                    beforeSend: function () {
                        $('.alert').addClass('hidden').find('.alert-content').html(null);
                        $('#__btnCancelSyncProduct').attr('disabled', true);
                        $('#__btnSubmitSyncProduct').attr('disabled', true).html(textSyncing);
                    },
                    success: function (response){
                        $('#__alertSuccessShopeeTable').removeClass('hidden').find('.alert-content').html(response.message);

                        $('#__btnCancelSyncProduct').attr('disabled', false);
                        $('#__btnSubmitSyncProduct').attr('disabled', false).html(textLoadData);

                        $('#__modalSyncProduct').doModal('close');
                    },
                    error: function (error) {
                        const response = error.responseJSON;
                        let alertMessage = response.message;

                        if (error.status === 422) {
                            const errorFields = Object.keys(response.errors);
                            alertMessage += '<br>';
                            $.each(errorFields, function (index, field) {
                                alertMessage += response.errors[field][0] + '<br>';
                            });
                        }

                        $('#__alertDangerSyncProduct').removeClass('hidden').find('.alert-content').html(alertMessage);
                        $('#__btnCancelSyncProduct').attr('disabled', false);
                        $('#__btnSubmitSyncProduct').attr('disabled', false).html(textLoadData);
                    }
                });

                return false;
            })


            $('body').on('click', '.addProduct', function() {
                $("#addProduct").modal("show");
                $('#datatable2').DataTable().destroy();
                $("#inventory_id").val($(this).data('id'));
                dataTables2("{{ route('inventoryProduct') }}");
                var datatable2;

                function dataTables2(url) {
                    datatable = $('#datatable2').DataTable({
                        processing: true,
                        serverSide: true,
                        order: [[2, 'desc']],
                        ajax: url+"?inv_id="+$('#inventory_id').val(),
                        columnDefs : [

                            {
                                'targets': 1,
                                'checkboxes': {
                                    'selectRow': true
                                }
                            },
                        ],
                        columns: [
                            {
                                name: 'expand',
                                data:'expand',
                            },


                            {
                                name: 'checkbox',
                                data: 'checkbox'
                            },
                            {

                                name: 'product_id',
                                data: 'product_id'
                            },
                            {
                                name: 'image',
                                data: 'image'
                            },


                            {
                                name: 'website_id',
                                data: 'website_id'
                            },


                            {
                                name: 'type',
                                data: 'type'
                            },

                            {
                                name: 'product_name',
                                data: 'product_name'
                            },

                            {
                                name: 'product_code',
                                data: 'product_code'
                            },

                            {
                                name: 'quantity',
                                data: 'quantity'
                            },
                            {
                                name: 'price',
                                data: 'price'
                            },


                            {
                                name: 'manage',
                                data: 'manage'
                            }
                        ]
                    });

                    // Add event listener for opening and closing details
                    $('#datatable tbody').on('click', 'td', function () {
                        var tr = $(this).closest('tr');
                        var row = $('#shopeeTable').DataTable().row( tr );
                        var row_index = $(this).closest('tr').index()
                        //alert('Row index: ' + $('#shopeeTable').DataTable().rows( { selected: true } ).data()[row_index]['checkbox']);
                        if ( row.child.isShown() ) {
                            // This row is already open - close it
                            row.child.hide();
                            tr.removeClass('shown');
                        }
                        else {
                            // Open this row
                            row.child( format(row.data()) ).show();
                            tr.addClass('shown');
                        }
                    });


                    /* Formatting function for row details - modify as you need */
                    function format ( d ) {

                        // `d` is the original data object for the row
                        var web_id_product_id = d.checkbox;

                        $.ajax({
                            url: '{{ route('data get_inventories_variations_by_id') }}?id_details=' + web_id_product_id,
                            beforeSend: function() {
                                //$('#form-producut').html('Loading');
                            }
                        }).done(function(result) {
                            $('.child_table').html(result);
                        });
                        return '<table class="child_table table"> </table>';
                    }
                }
            });


            //alert('Row index: ' + $('#shopeeTable').DataTable().rows( { selected: true } ).data()[row_index]['checkbox']);

            // Add event listener for opening and closing details

            $('#datatable tbody').on('click', 'td:first-child', function (e) {
                e.preventDefault();

                var tr = $(this).closest('tr');
                var row = $('#shopeeTable').DataTable().row( tr );
                var row_index = $(this).closest('tr').index();
                var tr_class = tr.attr('class');
                if ( tr_class=="odd shown" || tr_class=="even shown") {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                    return false;
                }
                else {
                    // Open this row
                    $(this).closest('tr').addClass('shown');
                    tr.addClass('shown');
                    row.child( format(row.data()) ).show();
                }
            });


            /* Formatting function for row details - modify as you need */
            function format ( d ) {
                // `d` is the original data object for the row
                var web_id_product_id = d.checkbox;
                //alert(web_id_product_id);
                $.ajax({
                    url: '{{ route('data get_variations_by_id') }}',
                    type: 'get',
                    data: {
                        'id_details': web_id_product_id,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                    success: function (result){
                        //$('tbody').html(result);

                        $('.child_table').html(result);

                    }
                });
                return '<table class="child_table table"> </table>';
            }


            $('body').on('click', '#BtnUpdate', function() {
                $('#__modalEditProduct').doModal('open');

                $.ajax({
                    url: '{{ route('shopee.product.edit') }}?id=' + $(this).data('id')+'&&row_index='+$(this).closest('tr').index(),
                    beforeSend: function() {
                        $('#form-producut').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-producut').html(result);
                });
            });


            $('body').on('click', '.btn-close_edit-product', function () {
                $('#__modalEditProduct').doModal('close');
            });


            $('body').on('click', '#btnSubmitProduct', function() {
                $.ajax({
                    url: '{{ route('shopee.product.update') }}',
                    type: 'post',
                    data: {
                        'website_id': $(this).data('website_id'),
                        'id': $(this).data('id'),
                        'name': $("#name").val(),
                        'sku': $("#sku").val(),
                        'price': $("#price").val(),
                        'quantity': $("#quantity").val(),
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                }).done(function(result) {
                    location.reload();
                });
            });


            $('body').on('click', '.inventory_link', function() {
                $('#__modalEditProduct').doModal('open');
                $.ajax({
                    url: '{{ route('data show_inventory_link') }}',
                    type: 'post',
                    data: {
                        'product_code': $(this).data('product_code'),
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                    beforeSend: function() {
                        $('#form-producut').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-producut').html(result);

                });
            });


            $('#BtnInsert').click(function() {
                $('.modal-insert').removeClass('modal-hide');
            });

            $('body').on('click', '#closeModalproduct', function() {
                $('#__modalEditProduct').doModal('close');
            });

            $('body').on('click', '#closeModalUpdate', function() {
                $('.modal-update').addClass('modal-hide');
            });

            $('#closeModalInsert').click(function() {
                $('.modal-insert').addClass('modal-hide');
            });

            $('body').on('click', '.BtnDelete', function() {
                let drop = confirm('Are you sure?');
                if (drop) {
                    var id = $(this).data('id');
                    var order_id = $(this).data('order_id');
                    $("#tr_"+id).addClass("current2");

                    $.ajax({
                        url: '{{ route("shopee.product.delete_product") }}',
                        type: 'POST',
                        data: {
                            'id': $(this).data('id'),
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        beforeSend: function() {
                            // Pesan yang muncul ketika memproses delete
                        }
                    }).done(function(result) {
                        if (result.status === 1) {
                            // Pesan jika data berhasil di hapus
                            alert('Data deleted successfully');
                            $("#tr_"+id).remove();
                        } else {
                            alert(result.message);
                        }
                        location.reload();
                    });
                }
            });


            $('body').on('click', '#BtnQuantity', function() {
                $('.modal-quantity').removeClass('modal-hide');
                $.ajax({
                    url: '{{ route("product data seller") }}?id=' + $(this).data('id'),
                    beforeSend: function() {
                        $('#form-quantity').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-quantity').html(result);
                });
            });

            $('body').on('click', '#closeModalQuantity', function() {
                $('.modal-quantity').addClass('modal-hide');
            });


            $('.new_form').on('submit', function(e){
                var form = this;
                var rows_selected = $('#shopeeTable').DataTable().column(0).checkboxes.selected();

                $.each(rows_selected, function(index, rowId) {
                    // Create a hidden element
                    $(form).append(
                        $('<input>')
                            .attr('type', 'hidden')
                            .attr('name', 'product_code[]')
                            .val(rowId)
                    );
                });
            });
        </script>


        <script>
            $('body').ready(function() {
                $('.js-example-basic-single3').select2({
                    placeholder: "Select A Shop Name"
                });
            });
        </script>

        <script type="text/javascript">
            $('body').ready(function() {
                $('#btn_sync_order').click(function () {
                    var website_id = $('#SyncModalOrder #shop option:selected').val();

                    var number_of_orders = $('input[name="number_of_orders"]').val();
                    var site_url = $('#SyncModalOrder #shop option:selected').attr('data-site_url');
                    var consumer_key = $('#SyncModalOrder #shop option:selected').attr('data-key');
                    var consumer_secret = $('#SyncModalOrder #shop option:selected').attr('data-secrete');


                    if (typeof consumer_key === "undefined") {

                        $('.message_sync').html('<div class="alert alert-danger" role="alert">Please Select A Shop</div>');
                        return;
                    }

                    if (consumer_key === "") {
                        $('.message_sync').html('<div class="alert alert-danger" role="alert">Please add REST API Consumer Key</div>');
                        return;
                    }

                    if (consumer_secret === "") {
                        $('.message_sync').html('<div class="alert alert-danger" role="alert">Please add REST API Consumer Secrete</div>');
                        return;
                    }

                    $(".page-item").removeClass("active");
                    $(this).parents("li").addClass("active");
                    $('.message_sync').html('<div class="alert alert-warning" role="alert">Proccesing...Please wait a bit </div>');

                    $.ajax({
                        url: '{{ route("wc_orders_sync") }}',
                        type: 'get',
                        data: {
                            'number_of_orders': number_of_orders,
                            'website_id': website_id,
                            'site_url': site_url,
                            'consumer_key': consumer_key,
                            'consumer_secret': consumer_secret,
                            'page': 1,
                            'limit': 100,
                            'per_page': 100,
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        success: function (data){
                            //$('tbody').html(data);
                            $('.message_sync').html('<div class="alert alert-success" role="alert">Orders are Synchronized successfully...</div>');
                            //  location.reload();
                        }
                    });
                });
            });

            $(document).on("change", "#shopee_table_page_length", function() {
                let total_rows = $(this).find("option:selected").val();
                checkForShopeeProductMissingInfo(total_rows);
            });

            $(document).on("click", ".paginate_button", function() {
                let total_rows = $(this).find("option:selected").val();
                checkForShopeeProductMissingInfo(total_rows);
            });

            const checkForShopeeProductMissingInfo = (total_rows) => {
                let time = 1000;
                if (total_rows === 25) {
                    time = 1500;
                } else if (total_rows === 50) {
                    time = 2000;
                } else if (total_rows === 100) {
                    time = 2500;
                }
                setTimeout(function() {
                    let rows = $("#shopeeTable").find("tbody").children("tr");
                    let shopee_product_id = [];
                    $.each(rows, function (index, tr) {
                        let target = $(tr).find(".shopee_product_edit_btn");
                        if (typeof(target) !== "undefined") {
                            shopee_product_id.unshift(target.attr("data-id"));
                        }
                    });
                    if (shopee_product_id.length > 0) {
                        getMissingInfoForShopeeProducts(shopee_product_id);
                    }
                }, time);
            }

            const getMissingInfoForShopeeProducts = (shopee_product_ids) => {
                $.ajax({
                    url: '{{ route("shopee.product.get_missing_info") }}',
                    type: 'post',
                    data: {
                        'ids': JSON.stringify(shopee_product_ids),
                        '_token': $('meta[name=csrf-token]').attr('content')
                    }
                }).done(function(response) {
                    if (typeof(response.data) !== "undefined") {
                        updateHtmlForMissingInfoForShopeeProducts(response.data);
                    }
                });
            }

            const updateHtmlForMissingInfoForShopeeProducts = (data) => {
                $(".missing_info_messages").remove();
                $.each(data, function (index, message) {
                    let update_table = false;
                    let html = '<tr class="missing_info_messages"><td style="border:0px !important;" colspan="3">';
                    if (typeof(message.missing_variation_images) !== "undefined" && message.missing_variation_images) {
                        html += '<p class="alert alert-danger">Variation has missing images</p>';
                        update_table = true;
                    }
                    if (typeof(message.need_more_cover_images) !== "undefined" && message.need_more_cover_images) {
                        html += '<p class="alert alert-warning">Images uploaded is less than 5</p>';
                        update_table = true;
                    }
                    html += '</td></tr>';
                    if (update_table) {
                        $(".shopee_product_edit_btn__"+index).closest("tr").before(html);
                    }
                });
            }
        </script>


        <script type="text/javascript">
            $( document ).ready(function() {
                $('#btn_sync_countries_state').click(function () {
                    var website_id = $('#SyncModalCountriesState #shop option:selected').val();
                    var site_url = $('#SyncModalCountriesState #shop option:selected').attr('data-site_url');
                    var consumer_key = $('#SyncModalCountriesState #shop option:selected').attr('data-key');
                    var consumer_secret = $('#SyncModalCountriesState #shop option:selected').attr('data-secrete');


                    if (typeof consumer_key === "undefined") {
                        $('.message_sync_country_state').html('<div class="alert alert-danger" role="alert">Please Select A Shop</div>');
                        return;
                    }

                    if (consumer_key === "") {
                        $('.message_sync_country_state').html('<div class="alert alert-danger" role="alert">Please add REST API Consumer Key</div>');
                        return;
                    }

                    if (consumer_secret === "") {
                        $('.message_sync_country_state').html('<div class="alert alert-danger" role="alert">Please add REST API Consumer Secrete</div>');
                        return;
                    }

                    $(".page-item").removeClass("active");
                    $(this).parents("li").addClass("active");
                    $('.message_sync_country_state').html('<div class="alert alert-warning" role="alert">Proccesing...</div>');

                    $.ajax({
                        url: '{{ route("wc_country_state_sync") }}',
                        type: 'post',
                        data: {
                            'website_id': website_id,
                            'site_url': site_url,
                            'consumer_key': consumer_key,
                            'consumer_secret': consumer_secret,
                            'page': 1,
                            'limit': 100,
                            'per_page': 100,
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        success: function (data) {
                            $('.message_sync_country_state').html('<div class="alert alert-success" role="alert">Orders are Synchronized successfully...</div>');
                            //location.reload();
                        }
                    });
                });
            });
        </script>

        <script>
            $("#btn_add_invitory").click(function (){
                let product_id = $("#inventory_id").val();
                let inventory_id = $("#product_id").val()

                $.ajax({
                    url: '{{route("inventoryProductAdd")}}',
                    type: 'post',
                    data: {
                        product_id: product_id,
                        inventory_id: inventory_id,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                    success: function (data){
                        $("#addProduct").modal('hide')
                        alert(data.message)
                        $('#shopeeTable').DataTable().ajax.reload();
                    }
                });
            });


            searchResult('.js-data-example-ajax', 'brands');

            function searchResult(tag, type) {
                $(tag).select2({
                    width: '100%',
                    ajax: {
                        url: "{{route('autocomplete.inventrory')}}",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
                            // parse the results into the format expected by Select2
                            // since we are using custom formatting functions we do not need to
                            // alter the remote JSON data, except to indicate that infinite
                            // scrolling can be used
                            params.page = params.page;

                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        },
                        cache: true
                    },
                    placeholder: 'Search for a repository',
                    minimumInputLength: 1,
                    templateResult: formatRepo,
                    templateSelection: formatRepoSelection
                });
            }


            function formatRepo(repo) {
                var $container = $(
                    "<div class='select2-result-repository clearfix '>" +
                    "<div class='select2-result-repository__meta'>" +
                    "<div class='select2-result-repository__title'></div>" +
                    "<p class=''><b class='select2-result-repository__shop'></b></p>" +
                    "</div>" +
                    "</div>" +
                    "</div>"
                );

                $container.find(".select2-result-repository__title").text(repo.full_name);
                $container.find(".select2-result-repository__shop").text(repo.shop_name);

                return $container;
            }

            function formatRepoSelection(repo) {
                return repo.full_name;
            }
        </script>

        <script src="{{ asset('pages/seller/shopee/product/index/sync_selected.js?_=' . rand()) }}"></script>
        <script src="{{ asset('pages/seller/shopee/product/index/linked-catalog.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
