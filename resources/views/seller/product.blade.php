<x-app-layout>
    @section('title', 'Product')

    @push('top_css')
        <link type="text/css" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
        <link rel="stylesheet" href="{{ asset('css/LC-select-theme-light.css') }}">
    @endpush

    <style type="text/css">

    select.select2-hidden-accessible {
        width: 100%!important;
    }
    /* Small devices (tablets, 768px and 800px) */
    @media only screen and (min-width: 768px) and (max-width: 800px) {
    .btn-action--gray {
        font-size: .60rem!important;
    }

}

    /* mobile devices  */
    @media (max-width: 767px) {

        td .flex {
            display: grid !important;
        }
        .dataTable tbody tr td {
            padding: .5rem 0rem!important;
        }
        .w-1\/2 {
            width: 100%!important;
        }

    }
    </style>

    {{-- <a href="{{asset('qrcode.svg')}}" download><img src="{{asset('qrcode.svg')}}" alt=""></a> --}}
    @if(\App\Models\Role::checkRolePermissions('Can access menu: Product'))
        <x-card title="Product ({{ number_format($productCount) }})">
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

                <x-alert-success class="mb-6 alert hidden" id="__alertSuccess">
                    <div id="__alertSuccessContent"></div>
                </x-alert-success>

                <x-alert-danger class="mb-6 alert hidden" id="__alertDanger">
                    <div id="__alertDangerContent"></div>
                </x-alert-danger>

                @if ($errors->any())
                    <x-alert-danger>
                        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert-danger>
                @endif

                @if (session('bulk_product_code'))
                    <x-alert-danger>
                        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                            Those products are not import
                            @foreach (session('bulk_product_code') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert-danger>
                @endif

                @if (session('bulk_product_code'))
                    {{ Session::forget('bulk_product_code')}}
                @endif

                <div class="flex flex-col sm:flex-row sm:justify-between items-start mb-4">
                    <div class="w-full md:w-2/5 lg:w-2/5 mb-6 sm:mb-0">
                        <div class="flex flex-col sm:flex-row">
                            @if(Auth::user()->role != 'dropshipper')
                            <div class="sm:mr-2">
                                <x-button type="button" color="green" id="BtnInsert" class="mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="ml-2">Add Product</span>
                                </x-button>
                            </div>
                            @endif
                            <div class="w-full md:w-4/5 lg:w-2/5 xl:w-3/5 xl:ml-1 relative -top-1">
                                <div class="w-4/5 md:w-11/12 xl:w-3/5">
                                    <x-select class="text-sm" id="__sortByToolbar">
                                        <option disabled>
                                            - {{ __('translation.Sort by') }} -
                                        </option>
                                        <option value="product_name_asc" selected>
                                            {{ __('translation.Product Name') . ' - ' . __('translation.A-Z') }}
                                        </option>
                                        <option value="product_name_desc">
                                            {{ __('translation.Product Name') . ' - ' . __('translation.Z-A') }}
                                        </option>
                                        <option value="price_asc">
                                            {{ __('translation.Lowest Price') }}
                                        </option>
                                        <option value="price_desc">
                                            {{ __('translation.Highest Price') }}
                                        </option>
                                        <option value="quantity_asc">
                                            {{ __('translation.Lowest Quantity') }}
                                        </option>
                                        <option value="quantity_desc">
                                            {{ __('translation.Highest Quantity') }}
                                        </option>
                                    </x-select>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(Auth::user()->role != 'dropshipper')
                    <div class="w-full md:w-3/5 lg:w-3/5 flex flex-row sm:justify-end">
                        <x-button-link href="{{ route('product_bulk_auto_link') }}" color="yellow" class="mb-2">
                            Bulk Auto-Link
                        </x-button-link>

                        <x-button-link href="{{ route('product_bulk_sync') }}" color="gray" class="mb-2 ml-1">
                            Bulk Sync
                        </x-button-link>

                        <form method="POST" action="{{ route('print qr code') }}" id="form-import" enctype="multipart/form-data" class="mb-2 ml-1 new_form_new">
                            @csrf
                            <x-button color="blue" class="click-submit">
                                View Qr Code
                            </x-button>
                        </form>

                        <form method="POST" action="{{ route('delete_bulk_product') }}" id="form-import11" enctype="multipart/form-data" class="ml-1">
                            @csrf
                            <x-button color="red" id="submit-delete">
                                Bulk Delete
                            </x-button>
                        </form>
                    </div>
                    @endif
                </div>

                <div class="mb-8 md:mb-2">
                    <div class="flex flex-col md:flex-row items-center justify-between">
                        <div class="w-full md:w-3/4 lg:w-3/4 xl:w-2/3 flex flex-col sm:flex-row mb-4">
                            <x-select name="parent_category" id="__selectParentCategoryFilter" class="category ProductCategoryFilter">
                                <option value="" selected disabled>
                                    {{ '- ' . __('translation.Select Product Category') . ' -' }}
                                </option>
                                @if (isset($categories))
                                    @foreach ($categories as $cateroy)
                                        <option value="{{$cateroy->id}}">{{$cateroy->cat_name}}</option>
                                    @endforeach
                                @endif
                            </x-select>

                            <x-select name="category" id="__selectCategoryFilter" class="category ProductCategoryFilter">
                                <option value="" selected disabled>
                                    {{ '- ' . __('translation.Select Sub Category') . ' -' }}
                                </option>
                                @if (isset($sub_categories))
                                    @foreach ($sub_categories as $cateroy)
                                        <option value="{{$cateroy->id}}">{{$cateroy->cat_name}}</option>
                                    @endforeach
                                @endif
                            </x-select>

                            <x-select name="product_tag" id="__selectTagFilter" class="status">
                                <option value="" selected disabled>
                                    {{ '- ' . __('translation.Select Product Tag') . ' -' }}
                                </option>
                                @if (isset($product_tags))
                                    @foreach ($product_tags as $product_tag)
                                        <option value="{{$product_tag->id}}">{{$product_tag->name}}</option>
                                    @endforeach
                                @endif
                            </x-select>
                        </div>

                        <div class="w-full md:w-1/4 lg:w-1/4 xl:w-1/3 flex items-center justify-end lg:justify-start lg:ml-2">
                            <x-button type="button" color="yellow" class="relative -top-1 order-last md:order-first mx-1" id="__btnSubmitFilter">
                                {{ __('translation.Search') }}
                            </x-button>
                            <x-button type="button" color="grey" class="relative -top-1 reset-filter" id="__btnResetFilter">
                                {{ __('translation.Reset') }}
                            </x-button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="w-full" id="datatable">
                    <thead>
                    <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                        <th></th>
                        <th class="px-4 py-2 text-center">
                            {{ __('translation.Product Detail') }}
                        </th>
                        @if(session('roleName') == 'dropshipper')
                            <th></th>
                        @else
                            <th class="px-4 py-2 text-center">
                                {{ __('translation.Actions') }}
                            </th>
                        @endif
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </x-card>
    @endif

    <x-modal.modal-small class="modal-hide modal-import">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Import Product') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalImport" />
        </x-modal.header>
        <x-modal.body>
            <p class="pb-3" style="margin-bottom: 20px;">Download Sample Format File
                <a target="_blank" style="font-weight: bold; text-decoration: underline; margin-left: 10px;" href="{{asset('/download-format.xlsx')}}">
                    <span class="fa fa-fw fa-download"></span> Download
                </a>
            </p>

            <form method="POST" action="{{ route('product_bulk_import') }}" id="form-import" enctype="multipart/form-data">
                <div>
                    @csrf
                    <x-label>File</x-label>
                    <x-input type="file" name="file" id="file" required></x-input>
                </div>
                <div class="flex justify-end mt-4 pb-6">
                    <x-button color="blue" type="submit" id="BtnImportSubmit">Save</x-button>
                </div>
            </form>
        </x-modal.body>
    </x-modal.modal-small>

    <!-- insert modal -->
    <x-modal.modal-large id="__modalInsert" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Add Product') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalInsert" />
        </x-modal.header>
        <x-modal.body>
            <form method="POST" action="{{ route('insert product') }}" id="form-insert" enctype="multipart/form-data">
                @csrf

                <div class="grid md:grid-cols-2 md:gap-x-5">
                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Select Category') }}
                        </x-label>
                        <x-select name="parent_category_id" id="parent_category_id">
                            <option disabled selected value="0">{{ __('translation.Select Category') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"> {{ $category->cat_name }} </option>
                            @endforeach
                        </x-select>
                    </div>
                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Select Sub Category') }}
                        </x-label>
                        <x-select name="category_id" id="category_id"></x-select>
                    </div>
                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Product Name') }} <x-form.required-mark />
                        </x-label>
                        <x-input type="text" name="product_name" id="product_name" value="{{ old('product_name') }}" required />
                    </div>
                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Product Code') }} <x-form.required-mark />
                        </x-label>
                        <x-input type="text" name="product_code" id="product_code" value="{{ old('product_code') }}" required />
                    </div>
                </div>

                <div>
                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Specifications') }}
                        </x-label>
                        <x-form.textarea name="specifications" id="specifications" rows="4">@if (isset($data->details)) {{$data->details}}@else{{old('specifications')}}@endif</x-form.textarea>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 md:gap-x-5">
                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Price') }} <x-form.required-mark />
                        </x-label>
                        <x-input type="number" name="price" id="price" value="{{ old('price') }}" required />
                    </div>
                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Dropship Price') }}
                        </x-label>
                        <x-input type="number" name="dropship_price" id="dropship_price" value="{{ old('dropship_price') }}" />
                    </div>
                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Weight') }} <x-form.required-mark />
                        </x-label>
                        <x-input type="text" name="weight" id="weight" value="{{ old('weight') }}" required />
                    </div>
                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Pieces / Pack') }} <x-form.required-mark />
                        </x-label>
                        <x-input type="number" name="pack" id="pack" value="{{ old('pack') }}" required />
                    </div>
                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Alert Stock') }} <x-form.required-mark />
                        </x-label>
                        <x-input type="number" name="alert_stock" id="alert_stock" value="{{ old('alert_stock') }}" required />
                    </div>
                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Select Product Tag') }}
                        </x-label>

                        <select name="product_tag[]" id="input_product_tag" data-placeholder="Select Product Tag" multiple>
                            @if (isset($product_tags))
                                @foreach ($product_tags as $product_tag)
                                    <option value="{{$product_tag->id}}">{{$product_tag->name}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="mb-5">
                        <x-label>
                            Product Status
                        </x-label>
                        <x-select name="product_status" id="product_status">
                            <option value="1" selected>Active </option>
                            <option value="0">Not active </option>
                        </x-select>
                    </div>
                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Image') }} <x-form.required-mark />
                        </x-label>
                        <x-input type="file" onchange="previewFile(this);" name="image" id="image" value="{{ old('image') }}"/>
                    </div>
                    <div class="mb-5"></div>
                    <div class="mb-5 hide" id="preview_image_div">
                        <x-label>
                            {{ __('translation.Preview Image') }}
                        </x-label>
                        <img id="previewImg" width="100" height="100" src="{{asset('img/No_Image_Available.jpg')}}" alt="Placeholder">
                    </div>
                </div>

                <div class="flex justify-end py-6">
                    <x-button type="reset" color="gray" class="mr-1" id="__btnCancelModalInsert">
                        {{ __('translation.Cancel') }}
                    </x-button>
                    <x-button type="submit" color="blue">
                        {{ __('translation.Save') }}
                    </x-button>
                </div>
            </form>
        </x-modal.body>
    </x-modal.modal-large>

    {{-- update modal --}}
    <x-modal.modal-large id="__modalUpdate" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Product') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalUpdate" />
        </x-modal.header>
        <x-modal.body>
            <form method="POST" action="{{ route('product.update') }}" id="form-update" enctype="multipart/form-data">
            </form>
        </x-modal.body>
    </x-modal.modal-large>

    {{-- Incoming Product Details--}}
    <x-modal.modal-large class="modal-producut modal-hide">
        <x-modal.header>
            <x-modal.title>
                Incoming Product Details
            </x-modal.title>
            <x-modal.close-button id="closeModalproduct" />
        </x-modal.header>
        <x-modal.body>
            <div id="form-producut"></div>
        </x-modal.body>
    </x-modal.modal-large>

      <x-modal.modal-large class="modal-order modal-hide" id="modalReserverdOrder">
        <x-modal.header>
            <x-modal.title>
                Reserved (Confirmed) Orders
            </x-modal.title>
            <x-modal.close-button id="closeModalOrder" />
        </x-modal.header>
        <x-modal.body>
            <div id="order_details"></div>
        </x-modal.body>
    </x-modal.modal-large>

    {{-- Quantity modal --}}
    <x-modal.modal-small class="modal-hide modal-quantity">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Quantity') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalQuantity" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form style="max-height:90vh" method="POST" action="{{ route('seller quantity update') }} " id="form-quantity" enctype="multipart/form-data"></form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

    {{-- Product Tag modal --}}
    <x-modal.modal-small class="modal-hide modal-product-tag">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Product Tag') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalProductTag" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form style="max-height:90vh" method="POST" action="{{ route('product.update_tag') }} " id="form-product-tag-update" enctype="multipart/form-data"></form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

    <div class="modal fade" tabindex="-1" role="dialog" id="pendingStockModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                        <h5 class="modal-title">
                            <strong>
                            {{ __('translation.Pending Stock Shipments') }}
                           </strong>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="order_details_pending_stock">

                        </div>
                    </div>
                </div>
        </div>
    </div>


    <div class="modal fade" tabindex="-1" role="dialog" id="reservedNotPaidModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                        <h5 class="modal-title">
                            <strong>Reserved (Not Paid) Orders</strong>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="order_details_reserved_not_paid">

                        </div>
                    </div>
                </div>
        </div>
    </div>

@push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="{{ asset('js/lc_select.js') }}"></script>

        <script>
            const productTableUrl = '{{ route('data product') }}';
            var productTable = '';

            const loadProductTable = (productTag, categoryId) => {
                productTable = $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    bDestroy: true,
                    pageLength: 10,
                    pagingType: 'numbers',
                    columnDefs: [
                        {
                            'targets': 0,
                            'checkboxes': {
                                'selectRow': true
                            }
                        },
                        {
                            'width': 180, targets: 2
                        }
                    ],
                    select: {
                        style: 'multi'
                    },
                    bDeferRender: true,
                    ajax: {
                        type: 'GET',
                        url: productTableUrl                   ,
                        data: {
                            productTag: productTag,
                            categoryId: categoryId
                        }
                    },
                    columns: [
                        {
                            name: 'checkbox',
                            data: 'product_code'
                        },
                        {
                            name: 'details',
                            data: 'details',
                            orderable: false
                        },
                        {
                            name: 'actions',
                            data: 'actions',
                            orderable: false
                        }
                    ]
                });
            }

            loadProductTable();

            const sortProductTable = sortBy => {
                switch (sortBy) {
                    case 'product_name_asc':
                        productTable.order([0, 'asc']).draw();
                        break;

                    case 'product_name_desc':
                        productTable.order([0, 'desc']).draw();
                        break;

                    case 'price_asc':
                        productTable.order([1, 'asc']).draw();
                        break;

                    case 'price_desc':
                        productTable.order([1, 'desc']).draw();
                        break;

                    case 'quantity_asc':
                        productTable.order([2, 'asc']).draw();
                        break;

                    case 'quantity_desc':
                        productTable.order([2, 'desc']).draw();
                        break;

                    default:
                        productTable.order([0, 'asc']).draw();
                        break;
                }
            }

            $('#__sortByToolbar').on('change', function() {
                let sortByValue = $(this).val();
                sortProductTable(sortByValue);
            });


            $(document).ready(function() {
                $('#__selectParentCategoryFilter').select2({
                    placeholder: '- Select Product Category -',
                    allowClear: true
                });

                $('#__selectCategoryFilter').select2({
                    placeholder: '- Select Sub Category -',
                    allowClear: true
                });

                $('#__selectTagFilter').select2({
                    placeholder: '- Select Product Tag -',
                    allowClear: true
                });

                $('#__selectParentCategoryFilter').val('').trigger('change');
                $('#__selectCategoryFilter').val('').trigger('change');
                $('#__selectTagFilter').val('').trigger('change');
            });

            $('#__btnSubmitFilter').click(function() {
                let parentCategoryId = $('#__selectParentCategoryFilter').val();
                let categoryId = $('#__selectCategoryFilter').val();
                let productTag = $('#__selectTagFilter').val();

                if (parentCategoryId != '' && categoryId == 0)
                    alert('Please select a sub category to filter');
                else
                    loadProductTable(productTag, categoryId);
            });

            function loadSubCategoryAfterReset(){
                $.ajax({
                    url: "{{route('get all sub categories')}}",
                    type: "GET",
                    dataType: 'json',
                    success: function (result) {
                        $('#__selectCategoryFilter').html('<option disabled selected value="0" style="background-color: #999">- Select Sub Category -</option>');

                        $.each(result.sub_categories, function (key, value) {
                            $("#__selectCategoryFilter").append('<option value="' + value
                                .id + '">' + value.cat_name + '</option>');
                        });
                    }
                });
            }

            $('#__btnResetFilter').on('click',function() {
                loadProductTable();

                $('#__selectParentCategoryFilter').val('').trigger('change');
                $('#__selectCategoryFilter').val('').trigger('change');
                $('#__selectTagFilter').val('').trigger('change');
                loadSubCategoryAfterReset();
            });

            $(document).ready(function () {
                $('#__selectParentCategoryFilter').on('change', function () {
                    var idParent = this.value;
                    $("#__selectCategoryFilter").html('');

                    $.ajax({
                        url: "{{route('fetch sub categories')}}",
                        type: "POST",
                        data: {
                            parent_category_id: idParent,
                            _token: '{{csrf_token()}}'
                        },
                        dataType: 'json',
                        success: function (result) {
                            $('#__selectCategoryFilter').html('<option value="">Select Sub Category</option>');
                            $.each(result.sub_categories, function (key, value) {
                                $("#__selectCategoryFilter").append('<option value="' + value
                                    .id + '">' + value.cat_name + '</option>');
                            });
                        }
                    });
                });
            });


            $('.new_form_new').on('submit', function(e) {
                var form = this;
                var rows_selected = productTable.column(0).checkboxes.selected();

                $.each(rows_selected, function(index, rowId) {
                    $(form).append(
                        $('<input>')
                            .attr('type', 'hidden')
                            .attr('name', 'product_code[]')
                            .val(rowId)
                    );
                });
            });

            $(document).ready(function () {
                $('#parent_category_id').on('change', function () {
                    var idParent = this.value;
                    $("#category_id").html('');
                    $.ajax({
                        url: "{{route('fetch sub categories')}}",
                        type: "POST",
                        data: {
                            parent_category_id: idParent,
                            _token: '{{csrf_token()}}'
                        },
                        dataType: 'json',
                        success: function (result) {
                            $('#category_id').html('<option value="">Select Sub Category</option>');
                            $.each(result.sub_categories, function (key, value) {
                                $("#category_id").append('<option value="' + value
                                    .id + '">' + value.cat_name + '</option>');
                            });
                        }
                    });
                });


                new lc_select('select[id="input_product_tag"]', {
                    wrap_width : '100%',
                    enable_search : true,
                });
            });

            function previewFile(input){
                var preview_div = $("#preview_image_div");
                if($(preview_div).hasClass('hide'))
                {
                    $(preview_div).removeClass('hide');
                    $(preview_div).addClass('show');
                }

                var file = $("#image").get(0).files[0];

                if(file){
                    var reader = new FileReader();
                    reader.onload = function(){
                        $("#previewImg").attr("src", reader.result);
                    }
                    reader.readAsDataURL(file);
                }
            }

            $(document).on('click', '#form-import11', function(event) {
                event.preventDefault();
                var form = this;
                var innerDiv = $(this).find('#innerDiv');
                var rows_selected = productTable.column(0).checkboxes.selected();
                var getDatas = [];
                $.each(rows_selected, function(index, rowId) {
                    getDatas.push(rowId);
                });

                let drop = confirm('Are you sure?');

                if (getDatas.length === 0) {
                    alert('Please select a product before delete');
                    return;
                }
                if (drop) {
                    $.ajax({
                        url: '{{ route('delete_bulk_product') }}',
                        type: 'post',
                        data: {
                            'product_code': getDatas,
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        beforeSend: function() {
                            // Pesan yang muncul ketika memproses delete
                        }
                    }).done(function(result) {
                        if (result.status === 1) {
                            // Pesan jika data berhasil di hapus
                            alert('Data deleted successfully');
                            var select_all = $(productTable.table().container()).find('.dt-checkboxes-select-all');
                            select_all.prop('indeterminate') ? productTable.column(0).checkboxes.select() : productTable.column(0).checkboxes.deselect();
                            select_all.trigger('change');
                            $('#datatable').DataTable().ajax.reload();
                        } else {
                            alert(result.message);
                        }
                    });
                }
            });


            $('#BtnInsert').click(function() {
                $('body').addClass('modal-open');
                $('#__modalInsert').removeClass('modal-hide');
            });

            $('#closeModalInsert').click(function() {
                $('body').removeClass('modal-open');
                $('#__modalInsert').addClass('modal-hide');
            });

            $('#__btnCancelModalInsert').click(function() {
                $('body').removeClass('modal-open');
                $('#__modalInsert').addClass('modal-hide');
            });


            $(document).on('click', '#BtnUpdate', function() {
                $('body').addClass('modal-open');
                $('#__modalUpdate').removeClass('modal-hide');

                $.ajax({
                    url: '{{ route('data product') }}?id=' + $(this).data('id'),
                    beforeSend: function() {
                        $('#form-update').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-update').html(result);
                });
            });

            $('#form-update').on('submit', function(event) {
                event.preventDefault();

                let formData = new FormData($(this)[0]);
                let actionUrl = $(this).attr('action');

                $.ajax({
                    type: 'POST',
                    url: actionUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('.alert').addClass('hidden');
                        $('#__alertSuccessContent').html(null);
                        $('#__alertDangerContent').html(null);
                    },
                    success: function(responseData) {
                        $('body').removeClass('modal-open');
                        $('#__modalUpdate').addClass('modal-hide');
                        $('#__alertSuccessContent').html(responseData.message);
                        $('#__alertSuccess').removeClass('hidden');
                        $("#product_tag_div_"+responseData.product_id).html(responseData.tagContent);
                        var url = "{{ url('/product/') }}";
                        window.location.href = url;

                    },
                    error: function(error) {
                        $('body').removeClass('modal-open');
                        $('#__modalUpdate').addClass('modal-hide');
                        let responseJson = error.responseJSON;

                        $('#__alertDangerContent').html(responseJson.message);
                        $('#__alertDanger').removeClass('hidden');
                    }
                })
            })


            $(document).on('click', '#BtnDelete', function() {
                let drop = confirm('Are you sure?');
                if (drop) {
                    $.ajax({
                        url: '{{ route('product.delete') }}',
                        type: 'post',
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
                            $('#datatable').DataTable().ajax.reload();
                        } else {
                            alert(result.message);
                        }
                    });
                }
            });


            $(document).on('click', '#BtnQuantity', function() {
                $('.modal-quantity').removeClass('modal-hide');
                $.ajax({
                    url: '{{ route('product data seller') }}?id=' + $(this).data('id'),
                    beforeSend: function() {
                        $('#form-quantity').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-quantity').html(result);
                });
            });

            $(document).on('click', '#closeModalQuantity', function() {
                $('.modal-quantity').addClass('modal-hide');
            });


            $(document).on('click', '#BtnProductTag', function() {
                $('.modal-product-tag').removeClass('modal-hide');
                $.ajax({
                    url: '{{ route('product.edit_tag') }}?id=' + $(this).data('id'),
                    beforeSend: function() {
                        $('#form-product-tag-update').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-product-tag-update').html(result);
                });
            });

            $(document).on('click', '#closeModalProductTag', function() {
                $('.modal-product-tag').addClass('modal-hide');
            });

             $(document).on('click', '#closeModalOrder', function() {
                $('#modalReserverdOrder').addClass('modal-hide');

            });

            $('#form-product-tag-update').on('submit', function(event) {
                event.preventDefault();

                let formData = new FormData($(this)[0]);
                let actionUrl = $(this).attr('action');

                $.ajax({
                    type: 'POST',
                    url: actionUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('.alert').addClass('hidden');
                        $('#__alertSuccessContent').html(null);
                        $('#__alertDangerContent').html(null);
                    },
                    success: function(responseData) {
                        $('body').removeClass('modal-open');
                        $('.modal-product-tag').addClass('modal-hide');
                        $('#__alertSuccessContent').html(responseData.message);
                        $('#__alertSuccess').removeClass('hidden');
                        $("#product_tag_div_"+responseData.product_id).html(responseData.tagContent);
                    },
                    error: function(error) {
                        $('body').removeClass('modal-open');
                        $('.modal-product-tag').addClass('modal-hide');
                        let responseJson = error.responseJSON;

                        $('#__alertDangerContent').html(responseJson.message);
                        $('#__alertDanger').removeClass('hidden');
                    }
                })
            })

        </script>
        <script type="text/javascript">
            $('#BtnImport').click(function() {
                $('.modal-import').removeClass('modal-hide');
            });

            $('#closeModalImport').click(function() {
                $('.modal-import').addClass('modal-hide');
            });


            $(document).on('click', '#BtnProduct', function() {
                $('.modal-producut').removeClass('modal-hide');
                $('body').addClass('modal-open');

                $.ajax({
                    url: '{{ route('data purchase order') }}?id=' + $(this).data('id'),
                    beforeSend: function() {
                        $('#form-producut').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-producut').html(result);
                });
            });

            $(document).on('click', '#closeModalproduct', function() {
                $('.modal-producut').addClass('modal-hide');
                $('body').removeClass('modal-open');
            });
        </script>
        <script>
            $('#__btnAddNewShopPrice').click(function() {
                let newShopPriceTemplate = $('#__newShopPriceTemplate').html();
                $('#__wrapperAdditionalShopPrice').append(newShopPriceTemplate);

                initialRemoveShopPriceButton();
            });

            const initialRemoveShopPriceButton = () => {
                $('.__btnRemoveShopPrice').click(function() {
                    $(this).parents(".additional-shop-price").remove();
                });
            }


             $(document).on('click', '#reservedProduct', function() {
                $('#modalReserverdOrder').removeClass('modal-hide');
                $('body').addClass('modal-open');


                $.ajax({
                    url: '{{ route('data order details') }}?id=' + $(this).data('id'),
                    beforeSend: function() {
                        $('#order_details').html('Loading');
                    }
                }).done(function(result) {
                    $('#order_details').html(result);
                });
            });

             $(document).on('click', '#reservedNotPaidBtn', function() {
                $('#reservedNotPaidModal').modal('show');
                $.ajax({
                    url: '{{ route('data order details reservedNotPaid') }}?id=' + $(this).data('id'),
                    beforeSend: function() {
                        $('#order_details_reserved_not_paid').html('Loading');
                    }
                }).done(function(result) {
                    $('#order_details_reserved_not_paid').html(result);
                });
            });


             $(document).on('click', '#pendingStockBtn', function() {
                $('#pendingStockModal').modal('show');
                $.ajax({
                    url: '{{ route('data order details ps') }}?id=' + $(this).data('id'),
                    beforeSend: function() {
                        $('#order_details_pending_stock').html('Loading');
                    }
                }).done(function(result) {
                    $('#order_details_pending_stock').html(result);
                });
            });


        </script>
    @endpush
</x-app-layout>
