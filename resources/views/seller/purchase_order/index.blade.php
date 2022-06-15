<x-app-layout>
    @section('title')
        {{ __('translation.Purchase Order') }}
    @endsection

    @if (in_array('Can access menu: Purchase Order - Purchase Order', session('assignedPermissions')))
        <x-card title="{{ __('translation.Purchase Order') }}">

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

            <div class="mb-6">
                <x-button-link href="{{ route('order_purchase.create') }}" color="green">
                    <i class="bi bi-plus-circle"></i>
                    <span class="ml-2">
                        {{ __('translation.Create Order') }}
                    </span>
                </x-button-link>
            </div>

            <div class="flex flex-col items-start justify-between gap-4 mb-6 lg:flex-row">
                <div class="w-full lg:w-1/2">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <div class="w-full sm:w-1/2">
                            <x-select name="supplier_id" id="supplier_id" style="width: 100%">
                                <option disabled>
                                    - {{ __('translation.Select Supplier Name') }} -
                                </option>
                                <option value="" selected>
                                    {{ __('translation.All Supplier') }}
                                </option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->supplier_id }}">
                                        {{ $supplier->supplier_name }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="w-full sm:w-1/2">
                            <x-select name="arrive_or_over_due" id="arrive_or_over_due" style="width: 100%">
                                <option disabled>
                                    - {{ __('translation.Select Arrival Status') }} -
                                </option>
                                <option value="" selected>
                                    {{ __('translation.All Arrival Status') }}
                                </option>
                                @foreach ($dueStatus as $value => $label)
                                    <option value="{{ $value }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="lg:relative lg:top-1">
                        <div id="statusFilterWrapper" class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-2">
                            <x-page.order-purchase.card-filter label="{{ __('translation.All') }}" data-status="all" class="btn-status-filter active all">
                                {{ number_format($orderPurchaseTotalAll) }}
                            </x-page.order-purchase.card-filter>
                            <x-page.order-purchase.card-filter label="{{ __('translation.Open') }}" data-status="open" class="btn-status-filter open">
                                {{ number_format($orderPurchaseTotalOpen) }}
                            </x-page.order-purchase.card-filter>
                            <x-page.order-purchase.card-filter label="{{ __('translation.Arrive') }}" data-status="arrive" class="btn-status-filter arrive">
                                {{ number_format($orderPurchaseTotalArrive) }}
                            </x-page.order-purchase.card-filter>
                            <x-page.order-purchase.card-filter label="{{ __('translation.Close') }}" data-status="close" class="btn-status-filter closes">
                                {{ number_format($orderPurchaseTotalClose) }}
                            </x-page.order-purchase.card-filter>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="__purchaseOrderTable">
                    <thead>
                        <tr>
                            <th>
                                {{ __('translation.Date') }}
                            </th>
                            <th>
                                {{ __('translation.Shipment') }}<br>
                                <span class="block whitespace-nowrap">
                                    {{ __('translation.Estimated Arrival') }}
                                </span>
                            </th>
                            <th>
                                {{ __('translation.Details') }}
                            </th>

                            <th>
                                {{ __('translation.Author') }}
                            </th>

                            <th>
                                {{ __('translation.Status') }}
                            </th>
                            <th>
                                {{ __('translation.Action') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </x-card>

        <div class="modal fade" id="po_chnage_status_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form method="POST" action="{{url('change_otder_purchase_status/')}}" id="form-importss" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title font-bold text-lg">
                                Change Status
                            </h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true" class="text-xl">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="text-center mb-4">Do you want to change this order purchase status ?</p>
                            @csrf
                            <input type="hidden" name="id" id="order_id">
                            <div class="row">
                                <div class="col-lg-4 offset-lg-4">
                                    <x-select class="form-control" id="exampleFormControlSelect1" name='status'>
                                        <option value="open">{{ __('translation.Open') }}</option>
                                        <option value="arrive">{{ __('translation.Arrive') }}</option>
                                        <option value="close">{{ __('translation.Close') }}</option>
                                    </x-select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-center pb-4">
                            <x-button type="button" color="green" data-dismiss="modal">
                                {{ __('translation.Close') }}
                            </x-button>
                            <x-button type="submit" color="red" class="ml-1">
                                {{ __('translation.Yes') }}
                            </x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif


    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    @endpush


    @push('bottom_css')
        <style type="text/css">
            .arrive_soon tbody tr { background-color:#ffd57f !important; }
            .overdue tbody tr{ background-color:#f7a49e !important; }
        </style>
    @endpush


    @push('bottom_js')
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/typeahead.js@0.11.1/dist/typeahead.bundle.min.js"></script>

        <script>
            const purchaseOrderTableUrl = route('order_purchase_list');
            var status = 'all';
            var supplier_id = '0';
            var arrive_or_over_due = '';

            function loadPurchaseOrderTable(url) {
                $('#__purchaseOrderTable').dataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: url,
                        data: {
                            supplierId: $("#supplier_id").val(),
                            status: $("#statusFilterWrapper .active").attr('data-status'),
                            arrive_or_over_due: $("#arrive_or_over_due").val(),
                        },
                        dataSrc: function ( json ) {
                            for ( var i=0, ien=json.data.length ; i<ien ; i++ ) {
                                json.data[i][0] = json.data[i][0];
                            }

                            if(json.supplier !='' && json.draw=='1') {
                                $(".all .order-purchase__filter-total").html(" ("+json.suppliersCountAll+")");
                                $(".open .order-purchase__filter-total").html(" ("+json.suppliersCountOpen+")");
                                $(".arrive .order-purchase__filter-total").html(" ("+json.suppliersCountArrive+")");

                                $(".closes .order-purchase__filter-total").html(" ("+json.suppliersCountClose+")");
                            }

                            return json.data;
                        }
                    },
                    columns: [
                        {
                            name: 'date',
                            data: 'date'
                        },
                        {
                            name: 'estimate_arrival',
                            data: 'estimate_arrival'
                        },
                        {
                            name: 'details',
                            data: 'details'
                        },
                        {
                            name: 'author_name',
                            data: 'author_name'
                        },
                        {
                            name: 'status',
                            data: 'status'
                        },
                        {
                            name: 'action',
                            data: 'action'
                        },
                    ],
                    pagingType: 'numbers',
                    columnDefs: [
                       {
                            targets: [5],
                            orderable: false,
                            className: 'text-center'
                        },
                    ],
                    order: [
                        [ 0, 'desc' ]
                    ]
                });
            }

            loadPurchaseOrderTable(purchaseOrderTableUrl);


            $('#supplier_id').change(function() {
                $("#arrive_or_over_due").val("").trigger( "change" );
                $('#__purchaseOrderTable').removeClass('overdue');
                $('#__purchaseOrderTable').removeClass('arrive_soon');
                $('#__purchaseOrderTable').dataTable().fnDestroy();

                $('.btn-status-filter').removeClass('active');
                $('.btn-status-filter[data-status="all"]').addClass("active");

                loadPurchaseOrderTable(purchaseOrderTableUrl);
            });


            $('input[type="search"]').on('keyup', function(e) {
                $("#arrive_or_over_due").select2('val', '');
                $("#supplier_id").select2('val', '');

                $('#__purchaseOrderTable').removeClass('overdue');
                $('#__purchaseOrderTable').removeClass('arrive_soon');
            });


            $('.btn-status-filter').click(function() {
                $('#__purchaseOrderTable').dataTable().fnDestroy();
                $('.btn-status-filter').removeClass('active');
                $(this).addClass("active");
                loadPurchaseOrderTable(purchaseOrderTableUrl);
            });


            $(document).on('click', '#purchase_status_chnage', function() {
                var order_id = $(this).attr('data-id');
                $('#po_chnage_status_modal').modal('show');
                $('#order_id').val(order_id);
            });

            $('#supplier_id').select2({
                width: 'resolve'
            });

            $("#arrive_or_over_due").select2({
                width: 'resolve'
            });


            $('#arrive_or_over_due').change(function() {
                $('#__purchaseOrderTable').dataTable().fnDestroy();

                $('.btn-status-filter').removeClass('active');
                $('.btn-status-filter[data-status="all"]').addClass("active");

                arrive_or_over_due = $(this).val();
                status = 'all';

                $('#__purchaseOrderTable').addClass(arrive_or_over_due);
                $('#__purchaseOrderTable').removeClass('overdue');
                $('#__purchaseOrderTable').removeClass('arrive_soon');

                if (arrive_or_over_due == 'arrive_soon'){
                    $('#__purchaseOrderTable').removeClass('overdue');
                }

                if(arrive_or_over_due == 'overdue'){
                    $('#__purchaseOrderTable').removeClass('arrive_soon');
                }

                loadPurchaseOrderTable(purchaseOrderTableUrl);
            });

            $(document).on('click', '#BtnDelete', function() {
                let drop = confirm('Are you sure?');
                if (drop) {
                    //remove from data table
                    $(this).closest('tr').remove();

                    $.ajax({
                        url: '{{ route('delete po') }}',
                        type: 'post',
                        data: {
                            'id': $(this).data('id'),
                            '_token': $('meta[name=csrf-token]').attr('content')
                        }
                    }).done(function(result) {
                        if (result.status === 1) {
                            alert('Data deleted successfully');

                            $('#__purchaseOrderTable').dataTable().fnDestroy();
                            loadPurchaseOrderTable(purchaseOrderTableUrl);

                        } else {
                            alert(result.message);
                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
