<x-app-layout>
    @section('title')
        {{ __('translation.Product Stock Report') }}
    @endsection



@push('top_css')
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

 @endpush


<style type="text/css">
.select2-container {
}

.select2-results__option {
  padding-right: 20px;
  vertical-align: middle;
}
.select2-results__option:before {
  content: "";
  display: inline-block;
  position: relative;
  height: 20px;
  width: 20px;
  border: 2px solid #e9e9e9;
  border-radius: 4px;
  background-color: #fff;
  margin-right: 20px;
  vertical-align: middle;
}
.select2-results__option[aria-selected=true]:before {
  font-family:fontAwesome;
  content: "\f00c";
  color: #fff;
  background-color: #f77750;
  border: 0;
  display: inline-block;
  padding-left: 3px;
}
.select2-container--default .select2-results__option[aria-selected=true] {
	background-color: #fff;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
	background-color: #eaeaeb;
	color: #272727;
}
.select2-container--default .select2-selection--multiple {
	margin-bottom: 10px;
}
.select2-container--default.select2-container--open.select2-container--below .select2-selection--multiple {
	border-radius: 4px;
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
	border-color: #007bff;
	border-width: 2px;
}
.select2-container--default .select2-selection--multiple {
	border-width: 2px;
}
.select2-container--open .select2-dropdown--below {

	border-radius: 6px;
	box-shadow: 0 0 10px rgba(0,0,0,0.5);

}
.select2-selection .select2-selection--multiple:after {
	content: 'hhghgh';
}
/* select with icons badges single*/
.select-icon .select2-selection__placeholder .badge {
	display: none;
}
.select-icon .placeholder {
	display: none;
}
.select-icon .select2-results__option:before,
.select-icon .select2-results__option[aria-selected=true]:before {
	display: none !important;
	/* content: "" !important; */
}
.select-icon  .select2-search--dropdown {
	display: none;
}

select.select2-hidden-accessible {
    width: 100px !important;
}

</style>
@if (\App\Models\Role::checkRolePermissions('Can access menu: Report - Stock'))
    <div class="col-span-12">
        <x-card.card-default>
            <x-card.header>
                <x-card.title>
                    {{ __('translation.Order Analysis') }}
                </x-card.title>
            </x-card.header>
            <x-card.body>
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
                        <div class="w-full md:w-full lg:w-full xl:w-full flex flex-col sm:flex-row mb-0 sm:mb-4">
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

                            <div class="w-full lg:w-4/5 mb-4 sm:mb-0 sm:ml-2">
                                <x-select name="status" id="__selectStatusFilter"  multiple="multiple" class="status" style="width: 100%;">
                                    <option value="0" disabled>
                                        {{ '- ' . __('translation.Select Status') . ' -' }}
                                    </option>
                                    <option value="1">
                                        {{ __('translation.Out Of Stock') }}
                                    </option>
                                    <option value="2">
                                        {{ __('translation.Low Stock') }}
                                    </option>
                                    <option value="3">
                                        {{ __('translation.OVER STOCK') }}
                                    </option>
                                    <option value="4">
                                        N/A
                                    </option>
                                </x-select>
                            </div>
                            <div class="w-full lg:w-5/5 mb-4 sm:mb-0 sm:ml-2">
                                <x-select name="category" id="__selectCategoryFilter" class="category" style="width: 100%;">
                                    <option value="" selected disabled>
                                        {{ '- ' . __('translation.Select Product Category') . ' -' }}
                                    </option>
                                    @if (isset($categories))
                                        @foreach ($categories as $cateroy)
                                            <option value="{{$cateroy->id}}">{{$cateroy->cat_name}}</option>
                                        @endforeach
                                    @endif
                                </x-select>
                            </div>
							
							
                        </div>
                    </div>
					
					<div class="flex flex-col md:flex-row items-center justify-between">
                        <div class="w-full md:w-full lg:w-full xl:w-full flex flex-col sm:flex-row mb-0 sm:mb-4">
            

                            <div class="w-full lg:w-1/5 mb-4 sm:mb-0 sm:ml-2">
                                <x-label>
                                    {{ __('translation.Date From') }} <x-form.required-mark/>
                                </x-label>
                                <x-input type="text" name="date_from" id="date_from" class="datepicker-1" required />
                            </div>

                            <div class="w-full lg:w-1/5 mb-4 sm:mb-0 sm:ml-2">
                                <x-label>
                                    {{ __('translation.Date To') }} <x-form.required-mark/>
                                </x-label>
                                <x-input type="text" name="date_to" id="date_to" class="datepicker-1" required />
                            </div>


                            <div class="w-full md:w-2/4 lg:w-2/3 xl:w-1/2 flex items-center justify-end lg:justify-start lg:ml-2 mt-8">
								
                                <x-button type="button" color="blue" class="relative -top-1 order-last md:order-first mx-1" id="__btnSubmitFilter">
                                    {{ __('translation.Search') }}
                                </x-button>
                                <x-button type="button" color="yellow" class="relative -top-1 reset-filter" id="__btnResetFilter">
                                    {{ __('translation.Reset') }}
                                </x-button>

                                <x-button type="button" color="green" class="relative -top-1 reset-filter ml-1" id="__btnExcelExport">
                                    {{ __('translation.Export') }}
                                </x-button>
							</div>

                            
                        </div>
                    </div>
					
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full" id="datatable">
                        <thead>
                            <tr>
                                <th class="text-center">{{ __('translation.ID') }}</th>
                                <th class="text-center">{{ __('translation.Image') }}</th>
                                <th class="text-center">{{ __('translation.Product Name') }}</th>
                                <th class="text-center">{{ __('translation.Product Code') }}</th>
                                <th class="text-center">{{ __('translation.Quantity') }}</th>
                                <th class="text-center">{{ __('translation.Incoming') }}</th>
                                <th class="text-center">{{ __('translation.Alert Stock') }}</th>
                                <th class="text-center">{{ __('translation.Stock In') }}</th>
                                <th class="text-center">{{ __('translation.Stock Out') }}</th>                                
                                <th class="text-center">{{ __('translation.Normal Price') }}</th>
                                <th class="text-center">{{ __('translation.Lowest Sell Price') }}</th>
                                <th class="text-center">{{ __('translation.Report Status') }}</th>                                
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </x-card.body>
        </x-card.card-default>
    </div>
    @endif




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


    <x-modal.modal-large class="modal-excel modal-hide">
        <x-modal.header>
            <x-modal.title>
                 Excel Export
            </x-modal.title>
            <x-modal.close-button id="closeModalExcel" />
        </x-modal.header>
        <x-modal.body>
        <div id="excel-wrapper"></div>
        </x-modal.body>
    </x-modal.modal-large>

    
    @push('bottom_js')
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/typeahead.js@0.11.1/dist/typeahead.bundle.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

        <script>
            const reportTableUrl = '{{ route('datatable_order_analysis') }}';
            const loadReportTable = (supplierId,status, categoryId,dateFrom,dateTo) => {
                $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    bDestroy: true,
                    ajax: {
                        type: 'GET',
                        url: reportTableUrl,
                        data: {
                            supplierId: supplierId,
                            status: status,
                            categoryId: categoryId,
                            dateFrom: dateFrom,
                            dateTo: dateTo
                        },
                        dataSrc: function ( json ) {
                        //console.log(json);
                         return json.data;
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
                            name: 'quantity',
                            data: 'quantity'
                        },
                        {
                            name: 'total_incoming',
                            data: 'total_incoming'
                        },
                        {
                            name: 'alert_stock',
                            data: 'alert_stock'
                        },
                        {
                            name: 'total_stock_in',
                            data: 'total_stock_in'
                        },
                        {
                            name: 'total_stock_out',
                            data: 'total_stock_out'
                        },
                        {
                            name: 'price',
                            data: 'price'
                        },
                        {
                            name: 'lowest_sell_price',
                            data: 'lowest_sell_price'
                        },
                        {
                            name: 'report_status',
                            data: 'report_status'
                        },
                    ],
                    columnDefs: [
                        {
                            targets: [1],
                            orderable: false
                        },
                        {
                            targets: [4, 5, 6, 7],
                            className: 'text-center'
                        },
                        { "width": "22%", "targets": 7 },
                        { "width": "23%", "targets": 8 },
                    ],
                    paginationType: 'numbers'
                });
            }
            let supplierId = $('#__selectSupplierFilter').val();
            let status = $('#__selectStatusFilter').val();
            let categoryId = $('#__selectCategoryFilter').val();
            if(supplierId==null){
                supplierId=0;
            }
            if(categoryId==null){
                categoryId=0;
            }
            loadReportTable(supplierId,status, categoryId);

            $(".datepicker-1").datepicker({
                dateFormat: 'yy-mm-dd'
            });


            $(document).ready(function() {
                $('#__selectStatusFilter').select2({
                    placeholder: '- Select Status -',
                    allowClear: true
                });
                $('#__selectSupplierFilter').select2({
                    placeholder: '- Select Supplier -',
                    allowClear: true
                });
                $('#__selectCategoryFilter').select2({
                    placeholder: '- Select Product Category -',
                    allowClear: true
                });
                $('#__selectStatusFilter').val('').trigger('change');
                $('#__selectSupplierFilter').val('').trigger('change');
                $('#__selectCategoryFilter').val('').trigger('change');
            });
            $('#__btnSubmitFilter').click(function() {
                let supplierId = $('#__selectSupplierFilter').val();
                let status = $('#__selectStatusFilter').val();
                let categoryId = $('#__selectCategoryFilter').val();
                let dateFrom = $('#date_from').val();
                let dateTo = $('#date_to').val();
                if(supplierId==null){
                    supplierId=0;
                }
                if(categoryId==null){
                    categoryId=0;
                }
                loadReportTable(supplierId,status, categoryId,dateFrom,dateTo);
            });
            $('#__btnResetFilter').on('click',function() {
                let supplierId = $('#__selectSupplierFilter').val();
                let status = $('#__selectStatusFilter').val();
                let categoryId = $('#__selectCategoryFilter').val();
                let dateFrom = $('#date_from').val();
                let dateTo = $('#date_to').val();
                if(supplierId==null){
                    supplierId=0;
                }
                if(categoryId==null){
                    categoryId=0;
                }
                loadReportTable(supplierId,status, categoryId,dateFrom,dateTo);
                $('#__selectStatusFilter').val('').trigger('change');
                $('#__selectSupplierFilter').val('').trigger('change');
                $('#__selectCategoryFilter').val('').trigger('change');
            });
            $(document).on('click', '#BtnProduct', function() {
                $.ajax({
                    url: '{{ route('update form') }}?id=' + $(this).data('id'),
                    beforeSend: function() {
                        $('#form-update-stock-reorder').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-update-stock-reorder').html(result);
                    $('.modal-producut').removeClass('modal-hide');
                    $('body').addClass('modal-open');
                });
            });

             // Handle to Export as a excel file
             $(document).on('click', '#__btnExcelExport', function() {

                let supplierId = $('#__selectSupplierFilter').val();
                let status = $('#__selectStatusFilter').val();
                let categoryId = $('#__selectCategoryFilter').val();
                let dateFrom = $('#date_from').val();
                let dateTo = $('#date_to').val();
                let excelSearch = $("input[type='search']").val();
                if(supplierId==null){
                    supplierId=0;
                }
                if(categoryId==null){
                    categoryId=0;
                }


                $.ajax({
                    type: 'GET',
                        url: reportTableUrl,
                        data: {
                            excel: 1,
                            excelSearch: excelSearch,
                            supplierId: supplierId,
                            status: status,
                            categoryId: categoryId,
                            dateFrom : dateFrom,
                            dateTo : dateTo
                        },
                    beforeSend: function() {
                        $('#excel-wrapper').html('processing...');
                        $('.modal-excel').removeClass('modal-hide');
                    }
                }).done(function(result) {
                // console.log(result);
                    let downloadLink = '<a href="'+result+'" download >Download Now</a>';
                    $('#excel-wrapper').html(downloadLink);
                });
                });
         
            $(document).on('click', '#BtnShowIncoming', function() {
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

            $(document).on('click', '#closeModalExcel', function() {
                $('.modal-excel').addClass('modal-hide');
                $('body').removeClass('modal-open');
            });

            
        </script>
    @endpush

</x-app-layout>