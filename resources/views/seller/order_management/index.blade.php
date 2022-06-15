<x-app-layout>
    @section('title')
        {{ __('translation.Order Manage') }}
    @endsection

    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.12.1/jquery-ui.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
        <link rel="stylesheet" href="{{ asset('css/custom_css.css') }}">
    @endpush

    @push('bottom_css')
        <link rel="stylesheet" href="{{ asset('css/datatable-custom-toolbar.css?_=' . rand()) }}">

        <style>
            .dataTable tbody tr td {
                border-width: 0px !important;
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }
        </style>
    @endpush
    <input type="hidden" id="payment-receipt-path" value="<?php echo asset('storage/');?>">
    <input type="hidden" id="no-img-path" value="<?php echo asset('img/No_Image_Available.jpg');?>">

    @if (in_array('Can access menu: Order Management', session('assignedPermissions')))

        <div class="col-span-12">

            @if(session('roleName') != 'dropshipper')
                @include('partials.pages.orders.dodo_order_tab_navigation')
            @endif

            <div class="row">
                <div class="col-lg-12">
                    <x-card.card-default>
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

                            <div class="w-full mb-10">
                                <div class="py-4 border-0 sm:border border-solid border-gray-300 rounded-md bg-white sm:bg-gray-50">
                                    <div class="mb-5">
                                        <div class="w-full sm:w-2/5 lg:w-2/5 xl:w-1/3 sm:mx-auto">
                                            <ul class="nav justify-content-center grid grid-cols-2 gap-2">
                                                @foreach ($statusSchema as $idx => $status)
                                                    @if($idx == 0 || $idx == 1)
                                                        <li class="nav-item border border-solid border-gray-300 rounded-md bg-gray-500">
                                                            <a href="#status-filter__{{ $idx }}" class="nav-link order-status-filter__tab top-status-filter__tab flex flex-col items-center justify-center cursor-pointer text-center text-white @if ($idx == 0) active underline @endif" data-toggle="tab" role="tab" data-id="{{ $status['id'] }}" data-sub-status-id="{{ array_column($status['sub_status'], 'id')[0] }}" data-status-ids="{{ implode(',', array_column($status['sub_status'], 'id')) }}" data-status-type="top">
                                                            <span class="mb-2">
                                                                {!! $status['icon'] !!}
                                                            </span>
                                                                <span class="hidden sm:block">
                                                                {{ $status['text'] }}
                                                            </span>
                                                                <span>
                                                                 ( {!! $status['count'] !!} )
                                                            </span>
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="mb-5">
                                            <div class="w-full sm:w-2/5 lg:w-2/5 xl:w-1/3 sm:mx-auto">
                                                <ul class="nav justify-content-center grid grid-cols-3 gap-2">
                                                    @foreach ($statusSchema as $idx => $status)
                                                    @if($idx != 0 && $idx != 1)
                                                        <li class="nav-item border border-solid border-gray-300 rounded-md bg-white">
                                                            <a href="#status-filter__{{ $idx }}" class="nav-link order-status-filter__tab secondary-status-filter__tab flex flex-col items-center justify-center cursor-pointer text-center text-xs" data-toggle="tab" role="tab" data-id="{{ $status['id'] }}" data-sub-status-id="{{ array_column($status['sub_status'], 'id')[0] }}" data-status-ids="{{ implode(',', array_column($status['sub_status'], 'id')) }}" data-status-type="secondary">
                                                            <span class="mb-2">
                                                                {!! $status['icon'] !!}
                                                            </span>
                                                                <span class="hidden sm:block">
                                                                {{ $status['text'] }}
                                                            </span>
                                                                <span>
                                                                 ( {!! $status['count'] !!} )
                                                            </span>
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:justify-between items-start mb-2" id="datatableBtns">
                                <div class="w-full sm:mb-0">
                                    <div class="flex flex-col sm:flex-row">
                                        <div class="w-full sm:w-1/4 xl:ml-1 mb-1 sm:mb-0 relative -top-1">
                                            <x-input type="text" id="searchbar" placeholder="Search"></x-input>
                                        </div>

                                        <div class="w-full sm:w-1/2 flex flex-col sm:flex-row">
                                            <div class="row sm:ml-2">
                                                @if(session('roleName') == 'dropshipper')
                                                <a href="{{ route('order-management.public-url-dropshipper') }}">
                                                @else
                                                <a href="{{ url('order_managements/create/'. $customerType) }}">
                                                    @endif
                                                    <x-button class="mb-3 sm:mb-0" color="green" id="BtnInsert">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-3 h-3 bi bi-plus-circle" viewBox="0 0 16 16">
                                                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                                        </svg>
                                                        <span class="ml-2">Create Order</span>
                                                    </x-button>
                                                </a>

                                                @if(session('roleName') != 'dropshipper')
                                                    <a id="bulk_shipment">
                                                        <x-button class="ml-2 mb-3 sm:mb-0" color="green" id="">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16">
                                                                <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                                                            </svg>
                                                            <span class="ml-2">Batch Process</span>
                                                        </x-button>
                                                    </a>

                                                    <a id="batch_print" class="batch_print hidden">
                                                        <x-button class="ml-2 mb-3 sm:mb-0" color="green" id="">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
                                                                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                                                                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                                                            </svg>
                                                            <span class="ml-2">Batch Print</span>
                                                        </x-button>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="w-full sm:w-1/4 relative -top-1 sm:justify-end" >
                                            <x-select class="text-sm" name="order-status-filter" id="order-status-filter">
                                                <option disabled value="0">- Select Status -</option>
                                                @foreach ($statusSchema as $idx => $status)
                                                    @if($idx == 0)
                                                        @foreach ($status['sub_status'] as $subStatus)
                                                            <option value="{{ $subStatus['id'] }}">
                                                                {{ $subStatus['text'] }}  ( {{ $subStatus['count'] }} )
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            </x-select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="w-full overflow-x-auto">
                                <table class="w-full" id="__orderManagementTable">
                                    <thead>
                                    <tr class="bg-blue-500">
                                        <th class="px-4 py-2 text-white"></th>
                                        <th class="px-4 py-2 text-white text-center">
                                            {{ __('translation.Order Data') }}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </x-card.body>
                    </x-card.card-default>
                </div>
            </div>
        </div>

    @endif

    <div class="modal fade" tabindex="-1" role="dialog" id="shipment_modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{route('createShipment')}}" id="create_shipment" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <strong>Create New Shipment</strong>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="email">
                                <strong>Shipment Date:</strong>
                            </label>
                            <x-input type="text" name="shipment_date" id="shipment_date" autocomplete="off" required />
                        </div>
                        <div class="" id="order_details"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Create</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="order_locked">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <strong>Order Locked</strong>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h6>This Order is Currently locked for editing. Thanks</h6>

                    <div class="text-center text-center-1 mt-3">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="confirmPaymentModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{route('createShipment')}}" id="confirm_payment" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <strong>Confirm Payment</strong>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        Total Amount: <span id="total_amount" class="bold_asif"></span><br>
                        Payment Date: <span id="bank_payment_date" class="bold_asif"></span><br>
                        Payment Time: <span id="bank_payment_time" class="bold_asif"></span><br><br>
                        Payment Slip:

                        <span class="mt-3 margin_top_5" id="payment_slip"></span><br>

                        <!-- <input type="checkbox" class="payment_confirm mt-3"  id="payment_confirm" name="payment_confirm">  Confirm Payment<br> -->

                        <input type="hidden"  id="order_id_confirm_payment" name="order_id_confirm_payment">

                        <div class="mt-4 text-center">
                            <input class="btn btn-success" style="margin: 0 auto; padding: 5px 10px;" type="button" onclick="confirmPaymentBtn()" value="Confirm" />
                        </div>

                    </div>
                    <div class="modal-footer">
                        <!-- <button type="submit" class="btn btn-primary">Create</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> -->
                    </div>
                </form>
            </div>
        </div>
    </div>

{{--        New Shipment Modal--}}
    <x-modal.modal-large id="__modalCreateShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.New Shipment') }}
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalCreateShipment" />
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerCreateShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentCreateShipmentForOrder"></div>
            </x-alert-danger>

            <div id="modal_content_create_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>

    <x-modal.modal-small id="__modalEditShipment" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Edit Shipment') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>

            <x-alert-danger id="__alertDangerEditShipment" class="alert mb-5 hidden">
                <div id="__alertDangerContentEditShipment"></div>
            </x-alert-danger>

            <form action="{{ route('shipment.update') }}" method="post" id="__formEditShipment">
                <input type="hidden" name="shipment_id" id="__shipment_idEditShipment">

                <div class="mb-5">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <x-label for="__order_idEditShipment">
                                {{ __('translation.Order ID') }} <x-form.required-mark/>
                            </x-label>
                            <x-input type="text" id="__order_id_displayEditShipment" class="bg-gray-200" readonly />
                        </div>
                        <div>
                            <x-label for="__ready_to_shipEditShipment">
                                {{ __('translation.Ready to Ship') }} <x-form.required-mark/>
                            </x-label>
                            <div class="flex flex-row items-center">
                                <div>
                                    <x-form.input-radio name="ready_to_ship" id="__ready_to_shipEditShipment_0" value="0" checked="true">
                                        {{ __('translation.No') }}
                                    </x-form.input-radio>
                                </div>
                                <div class="ml-4">
                                    <x-form.input-radio name="ready_to_ship" id="__ready_to_shipEditShipment_1" value="1">
                                        {{ __('translation.Yes') }}
                                    </x-form.input-radio>
                                </div>
                            </div>
                        </div>
                        <div>
                            <x-label for="__shipment_dateEditShipment">
                                {{ __('translation.Shipment Date') }}
                            </x-label>
                            <x-input type="text" name="shipment_date" id="__shipment_dateEditShipment" placeholder="DD-MM-YYYY" />
                        </div>
                    </div>
                </div>
                <div class="pb-3 text-center">
                    <x-button type="reset" color="gray" id="__btnCancelEditShipment">
                        {{ __('translation.Cancel') }}
                    </x-button>
                    <x-button type="submit" color="blue" id="__btnSubmitEditShipment">
                        {{ __('translation.Update Shipment') }}
                    </x-button>
                </div>
            </form>
        </x-modal.body>
    </x-modal.modal-small>

    <x-modal.modal-large id="__modalPackOrder">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Pick Confirm') }}
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalPackOrder" />
        </x-modal.header>
        <x-modal.body>

            <x-alert-danger class="mb-5 alert hidden" id="__alertDangerPackOrder"></x-alert-danger>

            <div class="mb-5">
                <div class="mb-4">
                    <div class="grid grid-cols-6 gap-2">
                        <div class="col-span-2 sm:col-span-1">
                            Order ID
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            :
                            <strong class="ml-2 text-blue-500" id="__orderIdOutputPackOrder">
                                -
                            </strong>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            Shipment ID
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            :
                            <strong class="ml-2 text-blue-500" id="__shipmentIdOutputPackOrder">
                                -
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full overflow-x-auto mb-10">
                <table class="w-full" id="__tblProductPackOrder">
                    <thead>
                    <tr>
                        <th class="w-24 md:w-36 text-center">
                            {{ __('translation.Image') }}
                        </th>
                        <th class="text-center">
                            {{ __('translation.Product Details') }}
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <div class="text-center pb-5 hidden" id="__actionButtonWrapperPackOrder">
                <x-button type="button" color="gray" class="__btnCloseModalPackOrder" id="__btnCancelPackOrder">
                    {{ __('translation.Close') }}
                </x-button>
                <x-button type="button" color="blue" id="__btnConfirmPackingPackOrder">
                    {{ __('translation.Confirm') }}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.modal-large>

{{--    Ordered Producnts List Modal--}}
    <x-modal.modal-large id="__modalProductsOrdered">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Ordered Products') }}
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalProductsOrder" />
        </x-modal.header>
        <x-modal.body>
            <div class="mb-5">
                <div class="mb-4">
                    <div class="grid grid-cols-6 gap-2">
                        <div class="col-span-2 sm:col-span-1">
                            Order ID
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            :
                            <strong class="ml-2 text-blue-500" id="__orderIdOutputProductsOrder">
                                -
                            </strong>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            Shipment ID
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            :
                            <strong class="ml-2 text-blue-500" id="__shipmentIdOutputProductsOrder">
                                -
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full overflow-x-auto mb-10">
                <table class="w-full" id="__tblProductProductsOrder">
                    <thead>
                    <tr>
                        <th class="w-24 md:w-36 text-center">
                            {{ __('translation.Image') }}
                        </th>
                        <th class="text-center">
                            {{ __('translation.Product Details') }}
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <div class="text-center pb-5 hidden" id="__actionButtonWrapperProductsOrder">
                <x-button type="button" color="gray" class="__btnCloseModalProductsOrder">
                    {{ __('translation.Close') }}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.modal-large>

{{--    Shipping Address Modal--}}
    <x-modal.modal-small class="modal-hide __modalShippingAddress" id="__modalShippingAddress">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Customer Shipping Address') }}
            </x-modal.title>
            <x-modal.close-button class="__btnCancelModalAddress"/>
        </x-modal.header>
        <x-modal.body>
            <div id="__shippingAddress_Content"></div>
        <div class="text-center pb-5">
            <x-button type="button" color="gray" class="__btnCancelModalAddress">
                {{ __('translation.Close') }}
            </x-button>
        </div>
        </x-modal.body>
    </x-modal.modal-small>

    <!--   // bulk shipment modal -->
    <x-modal.modal-medium id="__modalBulkShipment" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                Batch Process
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>

            <x-alert-danger id="__alertDangerCreateShipmentBulk" class="alert mb-5 hidden">
                <div id="__alertDangerContentCreateShipmentBulk"></div>
            </x-alert-danger>

            <input type="hidden" name="order_id" id="__order_idCreateShipment_bulk">

            <div id="total_orders" class="mb-5 order_status">

            </div>

            <div class="mb-5">
                <div class="grid grid-cols-7 gap-4">
                    <div class="col-span-6">
                        <x-label for="__shipment_dateCreateShipment_bulk">
                            {{ __('translation.Shipment Date') }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="text" name="shipment_date_bulk" id="__shipment_dateCreateShipment_bulk" value="{{ date('Y-m-d') }}" />
                    </div>
                </div>
            </div>
            <div class="pb-5 text-center">
                <x-button type="reset" color="gray" id="__btnCancelCreateShipmentBulk">
                    {{ __('translation.Cancel') }}
                </x-button>
                <x-button type="submit" color="blue" id="__btnSubmitCreateShipmentBulk">
                    {{ __('translation.Create Shipment') }}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.modal-medium>

    {{--    batch shipment print modal--}}
    <x-modal.modal-medium id="__modalBatchPrint" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                Batch Print
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>

            <x-alert-danger id="__alertDangerCreatePrintBulk" class="alert mb-5 hidden">
                <div id="__alertDangerContentCreatePrintBulk"></div>
            </x-alert-danger>

            <input type="hidden" name="order_id" id="__order_idCreatePrint_bulk">

            <div id="total_orders_print" class="mb-5 order_status"></div>

            <div class="pb-5 text-center">
                <form method="POST" action="{{route('orderPrintLabelBulk')}}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="order_ids_input_array" name="order_ids_input_array">

                    <x-button type="reset" color="gray" id="__btnCancelCreatePrintBulk">
                        {{ __('translation.Cancel') }}
                    </x-button>
                    <x-button type="submit" color="blue" id="__btnSubmitCreatePrintBulk">
                        {{ __('translation.Print') }}
                    </x-button>
                </form>
            </div>
        </x-modal.body>
    </x-modal.modal-medium>

    {{--   mark as shipped modal--}}
    <x-modal.modal-small id="__modalUpdateShipmentStatus" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                Update Status
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalUpdateStatus"/>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger class="mb-5 alert hidden" id="__alertDangerUpdateStatus"></x-alert-danger>
            <div class="mb-5">
                Update Shipment Status To:
            </div>
            <div class="w-full relative -top-1 sm:justify-end mb-5">
                <x-select class="text-sm" name="shipment-status" id="shipment-status">
                    <option disabled selected value="0">- Select Status -</option>
                    <option value="{{ \App\Models\Shipment::SHIPMENT_STATUS_READY_TO_SHIP }}">Ready To Ship</option>
                    <option value="{{ \App\Models\Shipment::SHIPMENT_STATUS_SHIPPED }}">Shipped</option>
                    <option value="{{ \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK }}">Wait For Stock</option>
                    <option value="{{ \App\Models\Shipment::SHIPMENT_STATUS_CANCEL }}">Cancel</option>
                </x-select>
            </div>

            <div class="text-center pb-5">
                <x-button type="button" color="gray" class="__btnCloseModalUpdateStatus" id="__btnCloseModalUpdateStatus">
                    {{ __('translation.Close') }}
                </x-button>
                <x-button type="button" color="red" id="__btnConfirmUpdateStatus">
                    {{ __('translation.Update Status') }}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.modal-small>


     <x-modal.modal-small id="__modalUpdateShipmentStatusForProcessed" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Order Status') }}
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalUpdateStatusForProcessed"/>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger class="mb-5 alert hidden" id="__alertDangerUpdateStatusForProcessed"></x-alert-danger>
            <div class="mb-5">
               {{ __('translation. Update Order Status To') }}
            </div>
            <div class="w-full relative -top-1 sm:justify-end mb-5">
                <x-select class="text-sm" name="order-status" id="order-status">
                    <option disabled selected value="0">- Select Status -</option>
                    <option value="{{ \App\Models\OrderManagement::ORDER_STATUS_PROCESSING }}">Processing</option>
                    <option value="{{ \App\Models\OrderManagement::ORDER_STATUS_PROCESSED }}">Processed</option>
                    <option value="{{ \App\Models\OrderManagement::ORDER_STATUS_COMPLETED }}">Completed</option>
                </x-select>
            </div>

            <div class="text-center pb-5">
                <x-button type="button" color="gray" class="__btnCloseModalUpdateStatusForProcessed" id="__btnCloseModalUpdateStatusForProcessed">
                    {{ __('translation.Close') }}
                </x-button>
                <x-button type="button" color="red" id="__btnConfirmUpdateStatusForProcessed">
                    {{ __('translation.Update Status') }}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

    @push('bottom_js')
        <script src="{{ asset('js/jquery.validate.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.12.1/jquery-ui.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>

        <script>
            const customerType = '{{ $customerType }}';
            const orderManagementDatatableUrl = '{{ route('ordersList') }}';
            const orderManagementDeleteUrl = '{{ route('delete order') }}';
            const orderManagementCancelUrl = '{{ route('cancel order') }}';
            const orderManageStatusUrl = '{{ route('order_manage.status.index') }}';
            const getOrderStatusUrl = '{{ route('order_manage.status.list') }}';
            const orderManagementBulkStatusUrl = '{{ route('data bulkStatus') }}';

            const packOrderProductDataUrl = '{{ route('order_manage.pack-order.product.index') }}';
            const packOrderUrl = '{{ route('shipment.pack-order') }}';
            const updateShipStatusUrl = '{{ route('shipment.update-ship-status') }}';
            const updateOrderStatusUrl = '{{ route('order.update-order-status') }}';
            const dodoOrderedProductDataUrl = '{{ route('get_ordered_dodo_products') }}';
            const shippingAddressUrl = '{{ route('get_shipping_address') }}';

            var selectedStatusIds = '{{ $defaultStatusOrderId }}';
            var selectedSubStatusIds = '';
            var orderManagementTable = '';

            const textProcessing = '{{ __('translation.Processing') }}';
            const textCreateShipment = '{{ __('translation.Create Shipment') }}';
            const textUpdateShipment = '{{ __('translation.Update Shipment') }}';
            const textYesDelete = '{{ __('translation.Yes, Delete') }}';

            var totalProductPackOrder = 0;
            var totalDodoProductsOrdered = 0;

            const waitingForStock = {{ \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK }};
            const readyToShip = {{ \App\Models\Shipment::SHIPMENT_STATUS_READY_TO_SHIP }};

            var syncBtn = document.getElementById('datatableBtns');
            $("div.datatable_buttons").html(syncBtn);

            const loadStatusList = () => {
                $.ajax({
                    type: 'GET',
                    url: orderManageStatusUrl,
                    success: function(response) {
                        let responseJson = response.data;
                        let orderStatuses = responseJson.orderStatuses;

                        orderStatuses.map((orderStatus, idx) => {
                            let total = orderStatus.total;
                            if (orderStatus.total > 100) {
                                let total = '100+';
                            }

                            $('#__statusCounter_' + orderStatus.id).html(`(${total})`);
                        });
                    },
                    error: function(error) {
                        throw error;
                    }
                });
            }

            loadStatusList();

            const loadOrderManagementTable = (statusIds = -1) => {
                orderManagementTable = $('#__orderManagementTable').DataTable({
                    dom: '<<"datatable_buttons"><rt>lip>',
                    bDestroy: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        type: 'GET',
                        url: orderManagementDatatableUrl,
                        data: {
                            customerType: customerType,
                            status: statusIds
                        }
                    },
                    columnDefs : [
                        {
                            targets: [0],
                            checkboxes: {
                                selectRow: true
                            }
                        },
                    ],
                    order: [
                        [1, 'desc']
                    ],
                    columns: [
                        {
                            name: 'checkbox',
                            data: 'checkbox'
                        },
                        {
                            data: 'order_data',
                            name: 'order_data'
                        }
                    ]
                });
            }

            loadOrderManagementTable(selectedStatusIds);

            $("#searchbar").keyup(function() {
                orderManagementTable.search(this.value).draw();
            });

            const loadOrderStatusList = (parentStatusId) => {
                $.ajax({
                    url: getOrderStatusUrl,
                    type: "POST",
                    data: {
                        parentStatusId: parentStatusId,
                        customerType: customerType,
                    },
                    dataType: 'json',
                    success: function (result) {
                        $('#order-status-filter').html('<option disabled value="0">- Select Status -</option>');

                        $.each(result.orderStatusCounts, function (key, value) {
                            $("#order-status-filter").append('<option value="' + value.id + '">' + value.text + ' (' + value.count + ')</option>');
                        });
                    }
                });
            }

            $(document).on('click', '#bulk_shipment', function() {
                var rows_selected = orderManagementTable.column(0).checkboxes.selected();

                var arr = [];
                $.each(rows_selected, function(index, rowId){
                    arr[index]=rowId;
                });

                if(arr.length === 0){
                    alert("Please Select Order ID");
                    return;
                }

                $("#__modalBulkShipment").doModal('open');
                $("#total_orders").text('You have selected total '+arr.length+' orders.');
            });

            $('#__btnCancelCreateShipmentBulk').on('click', function() {
                $('.alert').addClass('hidden');
                $('#__alertDangerContentCreateShipmentBulk').html(null);

                $('#__modalBulkShipment').doModal('close');
            });


            $(document).on('click', '#batch_print', function() {
                var rows_selected = orderManagementTable.column(0).checkboxes.selected();

                var arr = [];
                $.each(rows_selected, function(index, rowId){
                    arr[index]=rowId;
                });

                if(arr.length === 0){
                    alert("Please Select Order ID");
                    return;
                }

                $('#order_ids_input_array').val( arr );

                $("#__modalBatchPrint").doModal('open');

                $("#total_orders_print").text('You have selected total '+arr.length+' shipments.');
            });

            $('#__btnCancelCreatePrintBulk').on('click', function() {
                $('.alert').addClass('hidden');
                $('#__alertDangerContentCreatePrintBulk').html(null);

                $('#__modalBatchPrint').doModal('close');
            });

            const productsOrdered = (el) => {
                const orderId = el.getAttribute('data-order-id');
                const shipmentId = el.getAttribute('data-shipment-id');

                $('#__orderIdOutputProductsOrder').html(`#${orderId}`);
                $('#__shipmentIdOutputProductsOrder').html(`#${shipmentId}`);

                $('#__tblProductProductsOrder').DataTable().destroy();
                const productTable = $('#__tblProductProductsOrder').DataTable({
                    ajax: {
                        type: 'GET',
                        url: dodoOrderedProductDataUrl,
                        data: {
                            orderId: orderId,
                            shipmentId: shipmentId
                        }
                    },
                    columnDefs : [
                        {
                            targets: [0],
                            orderable: false
                        },
                        {
                            targets: [1],
                            className: 'text-left'
                        }
                    ],
                    paging: false,
                    filter : false,
                    info : false,
                });

                productTable.on('draw', function() {
                    const { recordsTotal } = productTable.page.info();
                    totalDodoProductsOrdered = recordsTotal;

                    if (recordsTotal > 0) {
                        $('#__actionButtonWrapperProductsOrder').removeClass('hidden');
                    }
                });

                $('#__modalProductsOrdered').doModal('open');
            }

            $('.__btnCloseModalProductsOrder').on('click', function() {
                $('#__modalProductsOrdered').doModal('close');
            });

            $(document).on('click', '#BtnAddress', function() {
                const orderId = $(this).data('id');

                $('.__modalShippingAddress').removeClass('modal-hide');
                $.ajax({
                    type: 'GET',
                    url: shippingAddressUrl,
                    data: {
                        orderId: orderId
                    },
                    beforeSend: function() {
                        $('#__shippingAddress_Content').html('Loading');
                    }
                }).done(function(result) {
                    $('#__shippingAddress_Content').html(result.data);
                });
            });

            $('.__btnCancelModalAddress').on('click', function() {
                $('#__modalShippingAddress').doModal('close');
            });

            $(document).on('click', '.BtnDelete', function() {
                let drop = confirm('Are you sure?');

                if (drop) {
                    $.ajax({
                        url: orderManagementDeleteUrl,
                        type: 'post',
                        data: {
                            'id': $(this).data('id'),
                            '_token': $('meta[name=csrf-token]').attr('content')
                        }
                    }).done(function(result) {
                        if (result.status === 1) {
                            alert('Order deleted successfully');

                            loadOrderManagementTable(selectedStatusIds);
                            loadStatusList();

                        } else {
                            alert(result.message);

                        }
                    });
                }
            });

            $(document).on('click', '.BtnCancel', function() {
                let drop = confirm('Are you sure to CANCEL this order?');

                if (drop) {
                    $.ajax({
                        type: 'POST',
                        url: orderManagementCancelUrl,
                        data: {
                            'id': $(this).data('id'),
                            '_token': $('meta[name=csrf-token]').attr('content')
                        }
                    }).done(function(result) {
                        if (result.status === 1) {
                            alert('Order cancelled successfully');

                            loadOrderManagementTable(selectedStatusIds);
                            loadStatusList();

                        } else {
                            alert(result.message);

                        }
                    });
                }
            });

            $('body').on('click', '#BtnShipment', function() {
                let order_id = $(this).data('id');

                $.ajax({
                    type: 'GET',
                    data: {order_id:order_id},
                    url: '{{ url('getOrderHistory') }}',
                    success: function(result) {
                        $("#shipment_modal").modal('show');
                        $("#order_details").html(result);
                    },
                    error: function() {
                        alert('Something went wrong');
                    }
                });
            });


            $('#shipment_date').datepicker({
                dateFormat: 'yy-mm-dd',
            });


            const createShipment = (el) => {

                let orderId = el.getAttribute('data-id');
                $('#__modalCreateShipmentForOrder').doModal('open');
                $.ajax({
                    type: 'GET',
                    url: '{{url('getAllOrderedProForOrder')}}',
                    data: {orderId:orderId},
                    beforeSend: function() {
                        $("#modal_content_create_shipment_for_order").html("Loading...");
                    },
                    success: function(responseData) {
                        $("#modal_content_create_shipment_for_order").html("");
                        $("#modal_content_create_shipment_for_order").html(responseData);
                        $('#order_id').val(orderId);
                    },
                    error: function(error) {

                    }
                });
            }

            $('#__shipment_dateCreateShipment_bulk').datepicker({
                dateFormat: 'yy-mm-dd'
            });

            $('.__btnCloseModalCreateShipment').on('click', function() {
                $('#__modalCreateShipmentForOrder').doModal('close');
            });

            const editShipment = (el) => {
                const orderId = el.getAttribute('data-order-id');
                const shipmentId = el.getAttribute('data-shipment-id');
                const shipmentDate = el.getAttribute('data-shipment-date');

                $('#__shipment_idEditShipment').val(shipmentId);
                $('#__order_id_displayEditShipment').val(`#${orderId}`);
                $('#__shipment_dateEditShipment').val(shipmentDate);

                $('#__modalEditShipment').doModal('open');
            }

            $('#__shipment_dateEditShipment').datepicker({
                dateFormat: 'dd-mm-yy'
            });


            $('#__btnCancelEditShipment').on('click', function() {
                $('#__modalEditShipment').doModal('close');
            });


            $('#__formEditShipment').on('submit', function(event) {
                event.preventDefault();

                const shipmentUpdateUrl = $(this).attr('action');
                const formData = new FormData($(this)[0]);

                $.ajax({
                    type: 'POST',
                    url: shipmentUpdateUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('.alert').addClass('hidden');
                        $('#__alertDangerContentEditShipment').html(null);

                        $('#__btnCancelEditShipment').attr('disabled', true);
                        $('#__btnSubmitEditShipment').attr('disabled', true).html(textProcessing);
                    },
                    success: function(responseData) {
                        let alertMessage = responseData.message;

                        loadStatusList();
                        loadOrderManagementTable(selectedStatusIds);

                        $('#__modalEditShipment').doModal('close');

                        $('#__btnCancelEditShipment').attr('disabled', false);
                        $('#__btnSubmitEditShipment').attr('disabled', false).html(textUpdateShipment);

                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: 'Succcess',
                            text: alertMessage,
                            timerProgressBar: true,
                            timer: 2000,
                            position: 'top-end'
                        });
                    },
                    error: function(error) {
                        let responseJson = error.responseJSON;

                        $('#__btnCancelEditShipment').attr('disabled', false);
                        $('#__btnSubmitEditShipment').attr('disabled', false).html(textUpdateShipment);

                        if (error.status == 422) {
                            let errorFields = Object.keys(responseJson.errors);
                            errorFields.map(field => {
                                $('#__alertDangerContentEditShipment').append(
                                    $('<span/>', {
                                        class: 'block mb-1',
                                        html: `- ${responseJson.errors[field][0]}`
                                    })
                                );
                            });

                        } else {
                            $('#__alertDangerContentEditShipment').html(responseJson.message);
                        }

                        $('#__alertDangerEditShipment').removeClass('hidden');
                    }
                });

                return false;
            });


            $(document).on('click', '#confirmPayment', function() {
                $("#confirmPaymentModal").modal('show');
                var order_id = $(this).data('id');

                //var order_id = $(this).attr('order-id');
                $("#order_id_confirm_payment").val(order_id);

                $.ajax({
                    type: 'GET',
                    data: {order_id:order_id},
                    url: '{{url('getOrderPaymentDetails')}}',
                    success: function(result) {
                        $("#total_amount").text('฿'+result.amount);
                        $("#bank_payment_date").text(result.payment_date);
                        $("#bank_payment_time").text(result.payment_time);
                        $("#payment_slip").text('');
                        $("#payment_slip").append(`<img class="margin_top_10 border_1" width="100%" src="${result.payment_slip_url}">`);
                    }
                });
            });

            function confirmPaymentBtn(){

                var result = confirm("Are you Sure?");
                if (result) {
                    var order_id = $("#order_id_confirm_payment").val();
                    $.ajax
                    ({
                        type: 'POST',
                        data: {order_id:order_id},
                        url: '{{url('confirmPaymentForOrder')}}',
                        success: function(result)
                        {
                            if(result === 'ok'){
                                $("#confirmPaymentModal").modal('hide');
                                alert("Payment has been successfully confirmed");
                                window.location.href = "{{ url('order_management')}}";
                            }
                        }
                    });
                }
            }


            const packOrder = (el) => {
                const shipmentId = el.getAttribute('data-shipment-id');
                const orderId = el.getAttribute('data-order-id');

                $('#__orderIdOutputPackOrder').html(`#${orderId}`);
                $('#__shipmentIdOutputPackOrder').html(`#${shipmentId}`);

                $('#__btnConfirmPackingPackOrder').attr('data-id', shipmentId);

                $('#__tblProductPackOrder').DataTable().destroy();
                const productTable = $('#__tblProductPackOrder').DataTable({
                    ajax: {
                        type: 'GET',
                        url: packOrderProductDataUrl,
                        data: {
                            orderId: orderId,
                            shipmentId: shipmentId
                        }
                    },
                    columnDefs : [
                        {
                            targets: [0],
                            orderable: false
                        },
                        {
                            targets: [1],
                            className: 'text-left'
                        }
                    ],
                    paging: false,
                    filter : false,
                    info : false,
                });


                productTable.on('draw', function() {
                    const { recordsTotal } = productTable.page.info();
                    totalProductPackOrder = recordsTotal;

                    if (recordsTotal > 0) {
                        $('#__actionButtonWrapperPackOrder').removeClass('hidden');
                    }
                });

                $('#__modalPackOrder').doModal('open');
            }


            $('.__btnCloseModalPackOrder').on('click', function() {
                $('#__modalPackOrder').doModal('close');
                $('#__actionButtonWrapperPackOrder').addClass('hidden');
            });


            $('#__btnConfirmPackingPackOrder').on('click', function() {
                const shipmentId = $(this).data('id');

                    $.ajax({
                        type: 'POST',
                        url: packOrderUrl,
                        dataType: 'json',
                        data: {
                            id: shipmentId
                        },
                        beforeSend: function() {
                            $('.alert').addClass('hidden');

                            $('#__btnCancelPackOrder').attr('disabled', true);
                            $('#__btnConfirmPackingPackOrder').attr('disabled', true);
                            $('#__btnConfirmPackingPackOrder').html('Processing...');
                        },
                        success: function(responseData) {
                            const alertMessage = responseData.message;

                            $('#__btnCancelPackOrder').attr('disabled', false);
                            $('#__btnConfirmPackingPackOrder').attr('disabled', false);
                            $('#__btnConfirmPackingPackOrder').html('Confirm Packing');

                            loadOrderManagementTable(selectedStatusIds);
                            loadStatusList();

                            $('#__modalPackOrder').doModal('close');
                            $('#__actionButtonWrapperPackOrder').addClass('hidden');

                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: 'Succcess',
                                text: alertMessage,
                                timerProgressBar: true,
                                timer: 2000,
                                position: 'top-end'
                            });
                        },
                        error: function(error) {
                            const responseJson = error.responseJSON;

                            $('#__alertDangerPackOrder').find('.alert-content').html(null);

                            if (error.status == 422) {
                                const errorFields = Object.keys(responseJson.errors);
                                errorFields.map(field => {
                                    $('#__alertDangerPackOrder')
                                        .find('.alert-content')
                                        .append(
                                            $('<span/>', {
                                                class: 'block mb-1',
                                                html: `- ${responseJson.errors[field][0]}`
                                            })
                                        );
                                });

                            } else {
                                $('#__alertDangerPackOrder').find('.alert-content').html(responseJson.message);

                            }


                            $('#__alertDangerPackOrder').removeClass('hidden');

                            $('#__modalPackOrder').find('div.overflow-y-auto').animate({
                                scrollTop: 0
                            }, 500);

                            $('#__btnCancelPackOrder').attr('disabled', false);
                            $('#__btnConfirmPackingPackOrder').attr('disabled', false);
                            $('#__btnConfirmPackingPackOrder').html('Confirm Packing');
                        }
                    })
            });


            const updateStatus = (el) => {
                const shipmentId = el.getAttribute('data-shipment-id');
                const orderId = el.getAttribute('data-order-id');
                var selectedStatus = $("#order-status-filter").val();
                $("#shipment-status option[value=" + selectedStatus + "]").attr('disabled', true);

                $('#__btnConfirmUpdateStatus').attr('data-id', shipmentId);

                $('#__modalUpdateShipmentStatus').doModal('open');
            }

            $('.__btnCloseModalUpdateStatus').on('click', function() {
                $('#__modalUpdateShipmentStatus').doModal('close');
                $("#shipment-status").val(0);
                $("#shipment-status option").removeAttr('disabled');
                $("#shipment-status option[value='0']").attr('disabled', true);
            });


            $('#__btnConfirmUpdateStatus').on('click', function() {
                const shipmentId = $(this).data('id');
                const shipmentStatus = $("#shipment-status").val();

                if (shipmentStatus > 0) {
                    $.ajax({
                        type: 'POST',
                        url: updateShipStatusUrl,
                        dataType: 'json',
                        data: {
                            id: shipmentId,
                            shipmentStatus: shipmentStatus
                        },
                        beforeSend: function () {
                            $('.alert').addClass('hidden');

                            $('#__btnCloseModalUpdateStatus').attr('disabled', true);
                            $('#__btnConfirmUpdateStatus').attr('disabled', true);
                            $('#__btnConfirmUpdateStatus').html('Processing...');
                        },
                        success: function (responseData) {
                            const alertMessage = responseData.message;

                            $('#__btnCloseModalUpdateStatus').attr('disabled', false);
                            $('#__btnConfirmUpdateStatus').attr('disabled', false);
                            $('#__btnConfirmUpdateStatus').html('Confirm Packing');

                            loadOrderManagementTable(selectedStatusIds);

                            $("#shipment-status").val(0);
                            $("#shipment-status option").removeAttr('disabled');
                            $("#shipment-status option[value='0']").attr('disabled', true);
                            $('#__modalUpdateShipmentStatus').doModal('close');

                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: 'Success',
                                text: alertMessage,
                                timerProgressBar: true,
                                timer: 2000,
                                position: 'top-end'
                            });
                        },
                        error: function (error) {
                            const responseJson = error.responseJSON;

                            $('#__alertDangerUpdateStatus').find('.alert-content').html(null);

                            if (error.status == 422) {
                                const errorFields = Object.keys(responseJson.errors);
                                errorFields.map(field => {
                                    $('#__alertDangerUpdateStatus')
                                        .find('.alert-content')
                                        .append(
                                            $('<span/>', {
                                                class: 'block mb-1',
                                                html: `- ${responseJson.errors[field][0]}`
                                            })
                                        );
                                });

                            } else {
                                $('#__alertDangerUpdateStatus').find('.alert-content').html(responseJson.message);

                            }
                            $('#__alertDangerUpdateStatus').removeClass('hidden');

                            $('#__modalUpdateShipmentStatus').find('div.overflow-y-auto').animate({
                                scrollTop: 0
                            }, 500);

                            $('#__btnCloseModalUpdateStatus').attr('disabled', false);
                            $('#__btnConfirmUpdateStatus').attr('disabled', false);
                            $('#__btnConfirmUpdateStatus').html('Update');
                        }
                    })
                }
                else
                    alert('Please select a shipment status to update.');
            });

            // for update status from Processed Order status
            const updateStatusForProcessed = (el) => {
                const shipmentId = el.getAttribute('data-shipment-id');
                const orderId = el.getAttribute('data-order-id');
                var selectedStatus = $("#order-status-filter").val();
                $("#shipment-status option[value=" + selectedStatus + "]").attr('disabled', true);

                $('#__btnConfirmUpdateStatusForProcessed').attr('data-id', orderId);

                $('#__modalUpdateShipmentStatusForProcessed').doModal('open');
            }


            $('.__btnCloseModalUpdateStatusForProcessed').on('click', function() {
                $('#__modalUpdateShipmentStatusForProcessed').doModal('close');
                $("#shipment-status").val(0);
                $("#shipment-status option").removeAttr('disabled');
                $("#shipment-status option[value='0']").attr('disabled', true);
            });

            $('#__btnConfirmUpdateStatusForProcessed').on('click', function() {
                const orderId = $(this).data('id');
                const orderStatus = $("#order-status").val();

                if (orderStatus > 0) {
                    $.ajax({
                        type: 'POST',
                        url: updateOrderStatusUrl,
                        dataType: 'json',
                        data: {
                            id: orderId,
                            orderStatus: orderStatus
                        },
                        beforeSend: function () {
                            $('.alert').addClass('hidden');

                            $('#__btnCloseModalUpdateStatusForProcessed').attr('disabled', true);
                            $('#__btnConfirmUpdateStatusForProcessed').attr('disabled', true);
                            $('#__btnConfirmUpdateStatusForProcessed').html('Processing...');
                        },
                        success: function (responseData) {
                            const alertMessage = responseData.message;

                            $('#__btnCloseModalUpdateStatusForProcessed').attr('disabled', false);
                            $('#__btnConfirmUpdateStatusForProcessed').attr('disabled', false);
                            loadOrderManagementTable(selectedStatusIds);

                            $("#shipment-status").val(0);
                            $("#shipment-status option").removeAttr('disabled');
                            $("#shipment-status option[value='0']").attr('disabled', true);
                            $('#__modalUpdateShipmentStatusForProcessed').doModal('close');

                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: 'Success',
                                text: alertMessage,
                                timerProgressBar: true,
                                timer: 2000,
                                position: 'top-end'
                            });
                        },
                        error: function (error) {
                            const responseJson = error.responseJSON;

                            $('#__alertDangerUpdateStatusForProcessed').find('.alert-content').html(null);

                            if (error.status == 422) {
                                const errorFields = Object.keys(responseJson.errors);
                                errorFields.map(field => {
                                    $('#__alertDangerUpdateStatusForProcessed')
                                        .find('.alert-content')
                                        .append(
                                            $('<span/>', {
                                                class: 'block mb-1',
                                                html: `- ${responseJson.errors[field][0]}`
                                            })
                                        );
                                });

                            } else {
                                $('#__alertDangerUpdateStatusForProcessed').find('.alert-content').html(responseJson.message);

                            }
                            $('#__alertDangerUpdateStatusForProcessed').removeClass('hidden');

                            $('#__modalUpdateShipmentStatusForProcessed').find('div.overflow-y-auto').animate({
                                scrollTop: 0
                            }, 500);

                            $('#__btnCloseModalUpdateStatusForProcessed').attr('disabled', false);
                            $('#__btnConfirmUpdateStatusForProcessed').attr('disabled', false);
                            $('#__btnConfirmUpdateStatusForProcessed').html('Update');
                        }
                    })
                }
                else
                    alert('Please select a Order status to update.');
            });



            $('#__btnSubmitCreateShipmentBulk').on('click', function() {
                var rows_selected = orderManagementTable.column(0).checkboxes.selected();

                var arr = [];
                $.each(rows_selected, function(index, rowId){
                    arr[index]=rowId;
                });

                var jSonData = JSON.stringify(arr);

                $.ajax
                ({
                    type: 'POST',
                    data: {
                        'jSonData': jSonData,
                        'shipment_date': $("#__shipment_dateCreateShipment_bulk").val(),
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                    url: '{{ route('bulkShipment') }}',
                    success: function(result)
                    {
                        if(result === 'OK'){
                            window.location.href = "{{ url('order_management')}}";
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: 'Succcess',
                                text: '{{__('translation.You made successfully batch processes')}}',
                                timerProgressBar: true,
                                timer: 2000,
                                position: 'top-end'
                            });
                        }

                    }
                });
            });

            $(document).on('click', '#editOrder', function() {
                var order_id = $(this).data('id');
                var order_status = $(this).attr('order-status-id');

                // alert(order_status);
                if(order_status === '3'  || order_status === '4' || order_status === '5'){
                    $("#order_locked").modal('show');
                }
                else{
                    $("#order_locked").modal('hide');
                    var url = "{{ url('/order_management/') }}";
                    window.location.href = url+'/'+order_id+'/edit';
                }
            });

        </script>

        <script src="{{ asset('pages/seller/order_management/index/status_filter.js?_=' . rand()) }}"></script>
        <script src="{{ asset('js/orderManagementJs.js') }}"></script>
    @endpush

</x-app-layout>
