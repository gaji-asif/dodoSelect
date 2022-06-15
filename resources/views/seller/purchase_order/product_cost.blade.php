<x-app-layout>
    @section('title', 'Product Cost')

    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    @endpush

    @if (in_array('Can access menu: Purchase Order - Product Cost', session('assignedPermissions')))
        <x-card title="Product Cost">
            <div class="mt-6">
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
            </div>

            <div class="mb-8 md:mb-2">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="w-full md:w-3/4 lg:w-2/3 xl:w-1/2 flex flex-col sm:flex-row mb-0 sm:mb-4">
                        <div class="w-full lg:w-3/5 mb-4 sm:mb-0 sm:ml-2">
                            <x-select name="lowest_value" id="__selectCostFilter" class="lowest_value" style="width: 100%;">
                                <option value="" selected disabled>
                                    {{ '- ' . __('translation.Select Price') . ' -' }}
                                </option>
                                <option value="price_asc">Lowest - Highest Cost</option>
                                <option value="price_desc">Highest -Lowest Cost</option>
                            </x-select>
                        </div>

                        <div class="w-full lg:w-3/5 mb-4 sm:mb-0 sm:ml-2">
                            <x-select name="supplier" id="__selectSupplierFilter" class="supplier" style="width: 100%;">
                                <option value="" selected disabled>
                                    {{ '- ' . __('translation.Select Supplier') . ' -' }}
                                </option>
                                @if (isset($suppliers))
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{$supplier->id}}">{{$supplier->supplier_name}}</option>
                                    @endforeach
                                @endif
                            </x-select>
                        </div>
                    </div>
                    <div class="w-full md:w-1/4 lg:w-1/3 xl:w-1/2 flex items-center justify-end lg:justify-start lg:ml-2">
                        <x-button type="button" color="blue" class="relative -top-1 order-last md:order-first mx-1" id="__btnSubmitFilter">
                            {{ __('translation.Search') }}
                        </x-button>
                        <x-button type="button" color="yellow" class="relative -top-1 reset-filter" id="__btnResetFilter">
                            {{ __('translation.Reset') }}
                        </x-button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="datatable">
                    <thead>
                    <tr>
                        <th class="text-center">ID</th>
                        <th class="text-center">Image</th>
                        <th class="text-center">Product Name</th>
                        <th class="text-center">Product Code</th>
                        <th class="text-center">Supplier</th>
                        <th class="text-center">Lowest Sell Price</th>
                        <th class="text-center">Re Order</th>
                        <th class="text-center">Manage</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </x-card>
    @endif

    <x-modal.modal-large class="modal-producut modal-hide">
        <x-modal.header>
            <x-modal.title>
                Product Cost Details
            </x-modal.title>
            <x-modal.close-button id="closeModalproduct" />
        </x-modal.header>
        <x-modal.body>
            <form style="max-height:90vh" method="POST" action="{{ route('update product lowest cost') }}" id="form-update-cost" enctype="multipart/form-data"></form>
        </x-modal.body>
    </x-modal.modal-large>

    <x-modal.modal-large class="modal-reorder modal-hide">
        <x-modal.header>
            <x-modal.title>
                Product Reorder
            </x-modal.title>
            <x-modal.close-button id="closeModalReorder" />
        </x-modal.header>
        <x-modal.body>
            <form style="max-height:90vh" method="POST" action="{{ route('update product reorder data') }}" id="form-update-reorder" enctype="multipart/form-data">
            </form>
        </x-modal.body>
    </x-modal.modal-large>

    <div hidden class="item w-full clone">
        <div class="item w-full">
            <div class="mt-4" style="width: 30%; float: left;  margin-bottom: 10px; margin-right: 1%;">
                <x-label>
                    Status
                </x-label>
                <x-select name="status[]" class="status relative top-1">
                    <option value="" selected disabled>
                        {{ '- ' . __('translation.Select Status') . ' -' }}
                    </option>
                    <option value="low_stock" >Low Stock</option>
                    <option value="out_of_stock">Out Of Stock</option>
                    <option value="over_stock">Over Stock</option>
                </x-select>
            </div>
            <div class="mt-4" style="width: 30%; float: left;  margin-bottom: 10px; margin-right: 1%;">
                <x-label>Type </x-label>
                <x-select name="type[]" class="type relative top-1">
                    <option value="" selected disabled>
                        {{ '- ' . __('translation.Select Ship Type') . ' -' }}
                    </option>
                    @if(isset($shipTypes))
                        @foreach($shipTypes as $shiptype)
                            <option value="{{$shiptype->id}}">{{$shiptype->name}}</option>
                        @endforeach
                    @endif
                </x-select>
            </div>

            <div class="mt-4" style="width: 30%; float: left;  margin-bottom: 10px; margin-right: 1%;">
                <x-label>Qty(Pieces)</x-label>
                <x-input  type="text"  name="quantity[]" id="quantity" value=""></x-input>
            </div>
            <div class="mt-4" style="width: 5%; float: left;  margin-bottom: 10px; margin-right: 1%;">
                <x-label>  &nbsp;  </x-label>
                <button type="button" class="btn-action--red mt-2" data-id="1" id="btnRemoveReorder"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
    </div>

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            const reportTableUrl = '{{ route('data_product_cost') }}';
            $.fn.dataTable.ext.errMode = 'throw';
            var productTable = '';

            const loadProductTable = (supplierId) => {

                productTable = $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    bDestroy: true,
                    ajax: {
                        type: 'GET',
                        url: reportTableUrl,
                        data: {
                            supplierId: supplierId,
                        }
                    },
                    columns: [
                        {
                            name: 'id',
                            data: 'id'
                        },
                        {
                            name: 'image',
                            data: 'image'
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
                            name: 'supplier_name',
                            data: 'supplier_name'
                        },
                        {
                            name: 'lowest_value',
                            data: 'lowest_value'
                        },
                        {
                            name: 'reorder',
                            data: 'reorder'
                        },
                        {
                            name: 'action',
                            data: 'action'
                        },
                    ],
                    columnDefs: [
                        {
                            targets: [1, 7],
                            orderable: false,
                        },
                        {
                            targets: [4, 5],
                            className: 'text-center'
                        },
                        { width: 150, targets: 5 }
                    ],
                    order: [
                        [ 5, 'asc' ]
                    ],
                    paginationType: 'numbers',
                    initComplete: function(settings, json) {
                    }
                });
            }

            loadProductTable();

            $(document).ready(function() {
                $('#__selectSupplierFilter').select2({
                    placeholder: '- Select Supplier -',
                    allowClear: true
                });

                $('#__selectCostFilter').select2({
                    placeholder: '- Select Lowest Sell Price  -',
                    allowClear: true
                });

                $('#__selectCostFilter').val('').trigger('change');
                $('#__selectSupplierFilter').val('').trigger('change');
            });

            const sortProductTable = sortBy => {
                switch (sortBy) {
                    case 'price_asc':
                        productTable.order([5, 'asc']).draw();
                        break;

                    case 'price_desc':
                        productTable.order([5, 'desc']).draw();
                        break;

                    default:
                        productTable.order([5, 'asc']).draw();
                        break;
                }
            }

            $('#__selectCostFilter').on('change', function() {
                let sortByValue = $(this).val();
                sortProductTable(sortByValue);
            });

            $('#__btnSubmitFilter').click(function() {
                let supplierId = $('#__selectSupplierFilter').val();

                loadProductTable(supplierId);
            });

            $('#__btnResetFilter').on('click',function() {
                loadProductTable();

                $('#__selectCostFilter').val('').trigger('change');
                $('#__selectSupplierFilter').val('').trigger('change');
            });

            $(document).on('click', '#BtnProductCost', function() {
                $.ajax({
                    url: '{{ route('update cost form') }}?id=' + $(this).data('id'),
                    beforeSend: function() {
                        $('#form-update-cost').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-update-cost').html(result);

                    $('.modal-producut').removeClass('modal-hide');
                    $('body').addClass('modal-open');
                });
            });

            $(document).on('click', '.BtnProductReOrder', function() {
                $.ajax({
                    url: '{{ route('show product reorder form') }}?id=' + $(this).data('id'),
                    beforeSend: function() {
                        $('#form-update-reorder').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-update-reorder').html(result);
                    $('.modal-reorder').removeClass('modal-hide');
                    $('body').addClass('modal-open');
                });
            });

            $(document).on('click', '#__btnAddReorderInput', function(event) {
                event.preventDefault();
                let templateProductItemElement = $('.clone').clone();
                $('#reorderData').prepend(templateProductItemElement.html());
                return false;
            });

            $(document).on('click', '#btnRemoveReorder', function(event) {
                $(this).parent().parent().remove();
            });

            $('#closeModalproduct').click(function() {
                $('.modal-producut').addClass('modal-hide');
                $('body').removeClass('modal-open');
            });

            $('#closeModalReorder').click(function() {
                $('.modal-reorder').addClass('modal-hide');
                $('body').removeClass('modal-open');
            });
        </script>
    @endpush

</x-app-layout>
