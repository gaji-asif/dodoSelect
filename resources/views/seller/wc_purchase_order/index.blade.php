<x-app-layout>
    @section('title')
        {{ __('translation.Purchase Order') }}
    @endsection

    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.12.1/jquery-ui.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
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

    @if(\App\Models\Role::checkRolePermissions('Can access menu: WooCommerce - Order'))
        <div class="col-span-12">

            @include('partials.pages.orders.web_order_tab_navigation')

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

                            <div class="w-full flex flex-col sm:flex-row sm:justify-end items-end mb-3">
                                <div class="w-full sm:w-1/4 relative -top-1" >
                                    <x-select class="text-sm" name="__btnShopFilter" id="__btnShopFilter">
                                        <option selected value="0">- All Shops -</option>
                                        @if (isset($shops))
                                            @foreach ($shops as $shop)
                                            @php
                                            $totalShopOrders = \App\Models\WooOrderPurchase::getOrdersFromShop('processing', $shop->id);
                                            @endphp
                                                <option value="{{$shop->id}}">{{$shop->name}} ({{$totalShopOrders}} to Process)</option>
                                            @endforeach
                                        @endif
                                    </x-select>
                                </div>
                            </div>

                            <div class="w-full mb-5">
                                <div class="py-4 border-0 sm:border border-solid border-gray-300 rounded-md bg-white sm:bg-gray-50">
                                    <div class="mb-5">
                                        <div class="w-full sm:w-2/5 lg:w-2/5 xl:w-1/3 sm:mx-auto">
                                            <ul class="nav justify-content-center grid grid-cols-2 gap-2">
                                                @foreach ($statusMainSchema as $idx => $status)
                                                    <li class="nav-item border border-solid border-gray-300 rounded-md bg-gray-500">
                                                        <a class="nav-link order-status-filter__tab top-status-filter__tab shadow-lg text-white flex flex-col items-center justify-center text-center cursor-pointer @if ($idx == 0) active underline @endif" data-toggle="tab" role="tab" data-id="{{ $status['id'] }}" data-sub-status-id="{{ array_column($status['sub_status'], 'id')[0] }}" data-status-ids="{{ implode(',', array_column($status['sub_status'], 'id')) }}" data-status-type="top" id="status-filter__{{ $idx }}">
                                                            <span class="mb-2">
                                                                {!! $status['icon'] !!}
                                                            </span>
                                                            <span class="hidden sm:block">
                                                                {{ $status['text'] }}
                                                            </span>
                                                            <span class="text-sm" id="__tabCount_{{ $status['id'] }}">
                                                            @if($status['text']=='To Ship')
                                                                ( {!! $totalToShip !!} )
                                                            @else
                                                                ( {!! $status['count'] !!} )
                                                            @endif
                                                            </span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="mb-5">
                                            <div class="w-full sm:w-2/5 lg:w-2/5 xl:w-1/3 sm:mx-auto">
                                                <ul class="nav justify-content-center grid grid-cols-3 gap-2">
                                                    @foreach ($statusSecondarySchema as $idx => $status)
                                                        <li class="nav-item border border-solid border-gray-300 rounded-md bg-white">
                                                            <a class="nav-link order-status-filter__tab secondary-status-filter__tab flex flex-col items-center justify-center text-center cursor-pointer text-xs" data-toggle="tab" role="tab" data-id="{{ $status['id'] }}"data-sub-status-id="{{ array_column($status['sub_status'], 'id')[0] }}" data-status-ids="{{ implode(',', array_column($status['sub_status'], 'id')) }}" data-status-type="secondary" id="status-filter__{{ $idx }}">
                                                                <span class="mb-2">
                                                                    {!! $status['icon'] !!}
                                                                </span>
                                                                <span class="hidden sm:block">
                                                                    {{ $status['text'] }}
                                                                </span>
                                                                <span id="__tabCount_{{ $status['id'] }}">
                                                                     ( {!! $status['count'] !!} )
                                                                </span>
                                                            </a>
                                                        </li>
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
                                        <div class="w-full sm:w-2/4 flex flex-col sm:flex-row">
                                            <div class="sm:ml-2">
                                                <x-button class="mb-3 sm:mb-0 sm:ml-2" color="green" data-toggle="modal" data-target="#SyncModalOrder" id="BtnSyncModalOrder">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                                                        <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                                                        <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                                                    </svg>
                                                    <span class="ml-2">Sync Order</span>
                                                </x-button>

                                            </div>

                                            <div class="sm:ml-2">
                                                <x-button class="mb-3 sm:mb-0 sm:ml-2" color="orange" id="bulk_print" class="btn btn-success rest_btn_wrapper bulk_print_shipment">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill" viewBox="0 0 16 16">
                                                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
                                                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                                                </svg>
                                                    <span class="ml-2">Batch Print</span>
                                                </x-button>

                                            </div>
                                        </div>



                                    <?PHP 
                                    //echo "<pre>";print_r($statusMainSchema);
                                    ?>

                                        <div id="order-status-filter-wrapper" class="w-full sm:w-1/4 relative -top-1 sm:justify-end" >
                                            <x-select class="text-sm" name="order-status-filter" id="order-status-filter">
                                                <option disabled value="0">- Select Status -</option>
                                                @foreach ($statusMainSchema as $idx => $status)
                                                    @if($idx == 0)
                                                        @foreach ($status['sub_status'] as $subStatus)
                                                            <option value="{{ $subStatus['id'] }}">
                                                                {{ $subStatus['text'] }} ( {{ $subStatus['count'] }} )
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
                                <table class="w-full" id="__wooOrderPurchaseTable">
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

    <div class="modal fade" id="SyncModalOrder" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><strong>Sync Purchase Order</strong></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Shop</label>
                            <select id="shop" style="width: 100%; margin-bottom: 15px;" class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 js-example-basic-single3" name="shop_id" required>
                                <option></option>
                                @if (isset($shops))
                                    @foreach ($shops as $shop)
                                        <option data-site_url="{{$shop->site_url}}" data-key="{{$shop->rest_api_key}}" data-secrete="{{$shop->rest_api_secrete}}" value="{{$shop->id}}">{{$shop->name}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Sync Record Total</label>
                            <input class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="number_of_orders" name="number_of_orders" placeholder="Enter -1 for ALL" type="text" />
                        </div>
                    </div>
                    <div class="col-lg-12 message_sync"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">Close</button>
                    <button id="btn_sync_order" type="submit" class="btn btn-success">Load</button>
                </div>
            </div>
        </div>
    </div>

    <x-modal.modal-small class="modal-hide modal-message">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Processing...') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalMessage"/>
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6" id="form-message"></div>
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
                    <div class="grid grid-cols-9 gap-4">
                        <div class="col-span-3">
                            Order ID
                        </div>
                        <div class="col-span-6">
                            :
                            <strong class="ml-2 text-blue-500" id="__orderIdOutputProductsOrder">
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
                <x-button type="button" color="blue" class="__btnCloseModalProductsOrder">
                    {{ __('translation.Close') }}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.modal-large>



    <x-modal.modal-large id="__modalProductsShipped">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Shipment Products') }}
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalProductsShipped" />
        </x-modal.header>
        <x-modal.body>
            <div class="mb-5">
                <div class="mb-4">
                    <div class="grid grid-cols-9 gap-4">
                        <div class="col-span-3">
                            Order ID
                        </div>
                        <div class="col-span-6">
                            :
                            <strong class="ml-2 text-blue-500" id="__orderIdOutputProductsShipped">
                                -
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full overflow-x-auto mb-10">
                <table class="w-full" id="__tblProductProductsShipped">
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
            <div class="text-center pb-5 hidden" id="__actionButtonWrapperProductsShipped">
                <x-button type="button" color="blue" class="__btnCloseModalProductsShipped">
                    {{ __('translation.Close') }}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.modal-large>





    <x-modal.modal-small class="modal-hide modal-address" id="modal-address">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Customer Shipping Address') }}
            </x-modal.title>
            <x-modal.close-button class="__btnCancelModalAddress"/>
        </x-modal.header>
        <x-modal.body>
            <div id="form-address"></div>
        </x-modal.body>
    </x-modal.modal-small>

    <x-modal.modal-small class="modal-hide modal-status">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Status') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalStatus"/>
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form style="max-height:90vh" action="" id="form-status" enctype="multipart/form-data"></form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>


    {{--        Arrange Shipment Modal--}}
    <x-modal.modal-large id="__modalCreateShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Arrange Shipment') }}
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalCreateShipment" />
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerCreateShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentCreateShipmentForOrder"></div>
            </x-alert-danger>

            <div id="modal_content_arrange_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>


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
    

    <div class="modal" tabindex="-1" role="dialog" id="print_level_modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <strong>Create Print Label</strong>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <button type="button" class="btn btn-success" id="customers_details_btn">Customer details</button>
                        <button type="button" class="btn btn-warning" id="order_details_btn">Shipment details</button>
                    </div>
                    <div id="printableArea">
                        <h6 class="order_shipment_color">
                            <strong id="order_id_div"></strong>
                            <strong style="float: right;" id="shipment_id_div"></strong>
                        </h6>
                        <div class="" id="order_details"></div>
                    </div>
                    <div class="mt-4 text-center">
                       <!-- <input class="btn btn-success" style="margin: 0 auto; padding: 5px 10px;" type="button" onclick="printDiv('printableArea')" value="Print" /> -->

                       <form method="POST" action="{{route('WCprintLabelPrint')}}" enctype="multipart/form-data">
                         @csrf
                         <input type="hidden" id="shipment_id_input_val" name="shipment_id_input_val">
                         <input type="hidden" id="order_id_input_val" name="order_id_input_val">
                         <input type="hidden" id="shop_id_input_val" name="shop_id_input_val">
                         <input class="btn btn-success" type="submit" style="margin: 0 auto; padding: 5px 10px;" value="Print" />

                     </form>
                 </div>
             </div>
             <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>





<div class="modal" tabindex="-1" role="dialog" id="bulk_print_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <strong>Bulk Print</strong>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
             <div class="" id="selected_item"></div>

             <div class="mt-4 text-center">
                <form method="POST" action="{{route('WCprintLevelBulk')}}" enctype="multipart/form-data">
                 @csrf
                 <input type="hidden" id="website_shipment_ids_input_array" name="website_shipment_ids_input_array">

                 <input class="btn btn-success" type="submit" style="margin: 0 auto; padding: 5px 10px;" value="Print" />

             </form>
         </div>
     </div>
     <div class="modal-footer">

        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    </div>

</div>
</div>
</div>



{{--   Update Order Status modal--}}
    <x-modal.modal-small id="__modalUpdateOrderStatus" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                Update Status
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalUpdateOrderStatus"/>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger class="mb-5 alert hidden" id="__alertDangerUpdateStatus"></x-alert-danger>
            <div class="mb-5">
                Update Order Status To:
            </div>
            <div class="w-full relative -top-1 sm:justify-end mb-5">
                <x-select class="text-sm" name="order-status" id="order-status">
                    <option disabled selected value="0">- Select Status -</option>
                    <option value="{{ \App\Models\WooOrderPurchase::ORDER_STATUS_CANCEL }}">Cancelled</option>
                    <option value="{{ \App\Models\WooOrderPurchase::ORDER_STATUS_COMPLETED }}">Completed</option>
                    <option value="{{ \App\Models\WooOrderPurchase::ORDER_STATUS_ON_HOLD }}">On Hold</option>
                    <option value="{{ \App\Models\WooOrderPurchase::ORDER_STATUS_PENDING }}">Pending</option>
                    <option value="{{ \App\Models\WooOrderPurchase::ORDER_STATUS_PROCESSING }}">Processing</option>
                    <option value="{{ \App\Models\WooOrderPurchase::ORDER_STATUS_PROCESSED }}">Processed</option>
                    <option value="{{ \App\Models\WooOrderPurchase::ORDER_STATUS_PRE_ORDERED }}">Pre-ordered</option>
                    <option value="{{ \App\Models\WooOrderPurchase::ORDER_STATUS_REFUNDED }}">Refunded</option>
                    <option value="{{ \App\Models\WooOrderPurchase::ORDER_STATUS_READY_TO_SHIP }}">Ready to Ship</option>
                </x-select>
            </div>

            <div class="text-center pb-5">
                <x-button type="button" color="gray" class="__btnCloseModalUpdateOrderStatus" id="__btnCloseModalUpdateOrderStatus">
                    {{ __('translation.Close') }}
                </x-button>
                <x-button type="button" color="red" id="__btnConfirmUpdateOrderStatus">
                    {{ __('translation.Update Status') }}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.modal-small>



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

        
    <x-modal.modal-small class="modal-hide" id="__modalMarkAsShipped">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Confirm') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <div class="mb-5">
                <p class="text-center">
                    {{ __('translation.Are your Sure Your want to confirm Shipment Status?') }}
                </p>
                <input type="hidden1" id="order_id">
                <input type="hidden1" id="shipment_id_value_MarkAsShipped">
            </div>
            <div class="text-center pb-5">
                <x-button type="button" color="gray" id="__btnCloseModalCancelMarkAsShipped">
                    {{ __('translation.No, Close') }}
                </x-button>
                <x-button color="red" id="__btnCloseModalFinalMarkAsShipped">
                    {{ __('translation.Yes, Continue') }}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

    @push('bottom_js')
        <script src="{{ asset('js/jquery.validate.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.12.1/jquery-ui.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.0.0/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            const wooOrderPurchaseDatatableUrl = '{{ route('data order') }}';
            const wooOrderStatusChangeUrl = '{{ route('wc_change_order_purchase_status') }}';

            const wooOrderPurchaseBulkStatusUrl = '{{ route('wc data bulkStatus') }}';
            const getWooStatusUrl = '{{ route('wc_order.status.get_woo_status') }}';
            const getWooShipmentStatusUrl = '{{ route('wc_order.status.get_woo_shipment_status') }}';

            const packOrderProductDataUrl = '{{ route('data order products') }}';
            const packShipmentProductDataUrl = '{{ route('data shipment products') }}';
            const packWooOrderUrl = '{{ route('shipment.pack-woo-order') }}';
            const updateShipStatusUrl = '{{ route('shipment.update-ship-status') }}';
            const updateOrderStatusUrl = '{{ route('wc_order.update-order-status') }}';

            const wooOrderPurchaseDeleteUrl = '{{ route('wc_order_delete') }}';

            var selectedStatusIds = '{{ $firstStatusOrderId }}';
            var selectedSubStatusIds = '';
            var wooOrderPurchaseTable = '';

            var totalProductProductsOrder = 0;

            var syncBtn = document.getElementById('datatableBtns');
            $("div.datatable_buttons").html(syncBtn);

            $(document).ready(function() {
                $('#__btnShopFilter').select2({
                    placeholder: '- All Shops -',
                    allowClear: true
                });
            });
  
            $("#bulk_print").removeClass('inline-flex');
            $("#bulk_print").hide();
            const loadOrderManagementTable = (statusId = -1, shopId) => {
                if(statusId=='{{ \App\Models\WooOrderPurchase::ORDER_STATUS_PROCESSED }}' || statusId=='{{ \App\Models\Shipment::SHIPMENT_STATUS_READY_TO_SHIP }}'){
                    $("#bulk_print").addClass('inline-flex');
                    //$("#bulk_print").hide();
                }else{
                    $("#bulk_print").removeClass('inline-flex');
                }

                
                wooOrderPurchaseTable = $('#__wooOrderPurchaseTable').DataTable({
                    dom: '<<"datatable_buttons"><rt>lip>',
                    bDestroy: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        type: 'GET',
                        url: wooOrderPurchaseDatatableUrl,
                        data: {
                            status: statusId,
                            shopId: shopId
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

            const loadOrderStatusList = (parentStatusId, shopId) => {

                $.ajax({
                    url: getWooStatusUrl,
                    type: "POST",
                    data: {
                        parentStatusId: parentStatusId,
                        shopId: shopId
                    },
                    dataType: 'json',
                    success: function (result) {

                       // console.log(result);
                        $.each(result.tabCounts, function (key, value) {
                            $('#__tabCount_' + key).html(`( ${value} )`);
                        });

                        $('#order-status-filter').html('<option disabled value="0">- Select Status -</option>');

                        $.each(result.orderStatusCounts, function (key, value) {
                            $("#order-status-filter").append('<option value="' + value.id + '">' + value.text + ' (' + value.count + ')</option>');
                        });
                    }
                });
            }

            
            const loadShipmentStatusList = (shopId) => {
                
                $.ajax({
                    url: getWooShipmentStatusUrl,
                    type: "POST",
                    data: {
                        shopId: shopId
                    },
                    dataType: 'json',
                    success: function (result) {

                        $.each(result.tabCounts, function (key, value) {
                            $('#__tabCount_' + key).html(`( ${value} )`);
                        });

                        $('#order-status-filter').html('<option disabled value="0">- Select Status -</option>');

                        $.each(result.shipmentStatusCount, function (key, value) {
                            
                            $("#order-status-filter").append('<option  value="' + value.id + '">' + value.text + ' (' + value.count + ')</option>');
                        });

                    }
                });
                }

                

            
            $("#searchbar").keyup(function() {
                wooOrderPurchaseTable.search(this.value).draw();
            });
           
            $('#__btnShopFilter').on('change', function () {
                let shopId = this.value;
                let statusId = $('#order-status-filter').val();
                let active = $("ul.nav li a.active");
                const parentStatusId = active.data('id');

                loadOrderStatusList(parentStatusId, shopId);

                loadOrderManagementTable(statusId, shopId);
            });

            $(document).on('change', '#bulk_action', function() {
                var status = $(this).val();
                var rows_selected = wooOrderPurchaseTable.column(0).checkboxes.selected();

                var arr = [];
                $.each(rows_selected, function(index, rowId) {
                    arr[index] = rowId;
                });

                if (arr.length === 0) {
                    alert("Please Select At Least 1 Row...");
                    return;
                }

                var jSonData = JSON.stringify(arr);

                $.ajax({
                    url: wooOrderPurchaseBulkStatusUrl,
                    type: "POST",
                    data: {
                        'jSonData': jSonData,
                        'status': status,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                    beforeSend: function() {
                        $('.modal-message').removeClass('modal-hide');
                        $('#form-message').html('Please Wait');
                    }
                }).done(function(result) {
                    if(result === 'OK'){
                        $('#form-message').html('<div class="alert alert-success" role="alert">Status changed successfully...</div>');
                        setTimeout(function(){
                            $('.modal-message').addClass('modal-hide')
                        }, 1500);
                    }
                    else {
                        alert("Status change FAILED.");
                    }
                });
            });

            $(document).on('click', '.sync_selected', function() {
                var status = $('#bulk_action').find(":selected").val();
                var rows_selected = wooOrderPurchaseTable.column(0).checkboxes.selected();

                var arr = [];
                $.each(rows_selected, function(index, rowId) {
                    arr[index] = rowId;
                });

                if (arr.length === 0) {
                    alert("Please Select At Least 1 Row...");
                    return;
                }

                var jSonData = JSON.stringify(arr);
                //console.log(jSonData)

                $.ajax({
                    url: orderManageStatusUrl,
                    type: "POST",
                    data: {
                        'jSonData': jSonData,
                        'status': status,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                    beforeSend: function() {
                        $('.modal-message').removeClass('modal-hide');
                        $('#form-message').html("Please wait...");
                    }
                }).done(function(result) {
                    $('#form-message').html('<div class="alert alert-success" role="alert">Orders Synchronized successfully...</div>');
                    location.reload();
                });
            });

            $(document).on('click', '#closeModalMessage', function() {
                $('.modal-message').addClass('modal-hide');

            });

            const productsOrder = (el) => {
                const orderId = el.getAttribute('data-order-id');
                const shopId = el.getAttribute('data-shop-id');

                $('#__orderIdOutputProductsOrder').html(`#${orderId}`);

                $('#__tblProductProductsOrder').DataTable().destroy();
                const productTable = $('#__tblProductProductsOrder').DataTable({
                    ajax: {
                        type: 'GET',
                        url: packOrderProductDataUrl,
                        data: {
                            orderId: orderId,
                            shopId : shopId
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
                });


                productTable.on('draw', function() {
                    const { recordsTotal } = productTable.page.info();
                    totalProductProductsOrder = recordsTotal;

                    if (recordsTotal > 0) {
                        $('#__actionButtonWrapperProductsOrder').removeClass('hidden');
                    }
                });

                $('#__modalProductsOrdered').doModal('open');
            }

            $('.__btnCloseModalProductsOrder').on('click', function() {
                $('#__modalProductsOrdered').doModal('close');
            });

            $('.__btnCancelModalAddress').on('click', function() {
                $('.modal-address').doModal('close');
            });

            $('.__btnCloseModalCreateShipment').on('click', function() {
                $('#__modalCreateShipmentForOrder').doModal('close');
            });


            
            const productsShipped = (el) => {
                const shipmentId = el.getAttribute('data-shipment-id');
                const orderId = el.getAttribute('data-order-id');
                const shopId = el.getAttribute('data-shop-id');
                
                
                $('#__orderIdOutputProductsShipped').html(`#${orderId}`);

                $('#__tblProductProductsShipped').DataTable().destroy();
                const productTable = $('#__tblProductProductsShipped').DataTable({
                    ajax: {
                        type: 'GET',
                        url: packShipmentProductDataUrl,
                        data: {
                            orderId: orderId,
                            shopId : shopId,
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
                });


                productTable.on('draw', function() {
                    const { recordsTotal } = productTable.page.info();
                    totalProductProductsOrder = recordsTotal;

                    if (recordsTotal > 0) {
                        $('#__actionButtonWrapperProductsShipped').removeClass('hidden');
                    }
                });

                $('#__modalProductsShipped').doModal('open');
            }

            $('.__btnCloseModalProductsShipped').on('click', function() {
                $('#__modalProductsShipped').doModal('close');
            });

            $(document).on('click', '#BtnAddress', function() {

                $('.modal-address').removeClass('modal-hide');
                $.ajax({
                    url: '{{ route('data customer address') }}?order_id=' + $(this).data('order-id') +'&&shop_id=' + $(this).data('shop-id'),
                    beforeSend: function() {
                        $('#form-address').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-address').html(result);
                });
            });





            const packOrder = (el) => {
                const shipmentId = el.getAttribute('data-shipment-id');
                const orderId = el.getAttribute('data-order-id');
                const shopId = el.getAttribute('data-shop-id');
                
                $('#__orderIdOutputPackOrder').html(`#${orderId}`);
                $('#__shipmentIdOutputPackOrder').html(`#${shipmentId}`);

                $('#__btnConfirmPackingPackOrder').attr('data-id', shipmentId);

                $('#__tblProductPackOrder').DataTable().destroy();
                const productTable = $('#__tblProductPackOrder').DataTable({
                    ajax: {
                        type: 'GET',
                        url: packShipmentProductDataUrl,
                        data: {
                            shopId:shopId,
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
                        url: packWooOrderUrl,
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



            const updateOrderStatus = (el) => {
                const orderId = el.getAttribute('data-order-id');
                
                var selectedStatus = 'processed';//$("#order-status-filter").val();
                $("#order-status option[value=" + selectedStatus + "]").attr('disabled', true);
                //alert(orderId+selectedStatus);
                $('#__btnConfirmUpdateOrderStatus').attr('data-order-id', orderId);

                $('#__modalUpdateOrderStatus').doModal('open');
            }


            $('.__btnCloseModalUpdateOrderStatus').on('click', function() {
                $('#__modalUpdateOrderStatus').doModal('close');
                $("#shipment-status").val(0);
                $("#shipment-status option").removeAttr('disabled');
                $("#shipment-status option[value='0']").attr('disabled', true);
            });

            $('#__btnConfirmUpdateOrderStatus').on('click', function() {
                const orderId = $(this).data('order-id');
                const orderStatus = $("#order-status").val();
                if (orderStatus) {
                    $.ajax({
                        type: 'POST',
                        url: updateOrderStatusUrl,
                        dataType: 'json',
                        data: {
                            orderId: orderId,
                            orderStatus: orderStatus
                        },
                        beforeSend: function () {
                            $('.alert').addClass('hidden');

                            $('#__btnCloseModalUpdateStatus').attr('disabled', true);
                            $('#__btnConfirmUpdateOrderStatus').attr('disabled', true);
                            $('#__btnConfirmUpdateOrderStatus').html('Processing...');
                        },
                        success: function (responseData) {
                            const alertMessage = responseData.message;

                            $('#__btnCloseModalUpdateStatus').attr('disabled', false);
                            $('#__btnConfirmUpdateOrderStatus').attr('disabled', false);
                            $('#__btnConfirmUpdateOrderStatus').html('Confirm Packing');
                            
                            loadOrderManagementTable(selectedStatusIds);

                            $("#order-status").val(0);
                            $("#order-status option").removeAttr('disabled');
                            $("#order-status option[value='0']").attr('disabled', true);
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

                            //location.reload();
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
                            $('#__btnConfirmUpdateOrderStatus').attr('disabled', false);
                            $('#__btnConfirmUpdateOrderStatus').html('Update');
                        }
                    })
                }
                else
                    alert('Please Select an Order status to update.');
            });



            const updateShipmentStatus = (el) => {
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
                            $('#__btnConfirmUpdateStatus').html('Confirm Update');
                            
                            loadOrderManagementTable(selectedStatusIds);
                            loadShipmentStatusList();


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

                            //location.reload();
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


            $(document).on('click', '#BtnUpdateStatus', function() {
                $('.modal-status').removeClass('modal-hide');
                $.ajax({
                    url: '{{ route('data order status') }}?id=' + $(this).data('id') + '&&row_index=' + $(this).closest('tr').index(),
                    beforeSend: function() {
                        $('#form-status').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-status').html(result);
                });
            });

            $(document).on('click', '#BtnSubmitChangeStatus', function() {
           
                $('.f').removeClass('modal-hide');
                var row_index = $(this).data('row_index');
                var status = $("#status").val();
                $.ajax({
                    url: wooOrderStatusChangeUrl,
                    type: 'post',
                    data: {
                        'id': $(this).data('id'),
                        'website_id': $(this).data('website_id'),
                        'order_id': $(this).data('order_id'),
                        'status': $("#status").val(),
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                    beforeSend: function() {
                        $('#messageStatusSubmit').html("Please wait...");
                    }
                }).done(function(result) {
                    wooOrderPurchaseTable.cell(row_index, 9).data(status);
                    $('#messageStatusSubmit').html(result);
                });
            });

            $('#closeModalStatus').on('click', function() {
                $('.modal-status').doModal('close');
            });



        $(document).on('click', '#markAsShipped', function() {
          var orderId = $(this).data('order-id');
          var shipment_id = $(this).data('id');

          $("#order_id").val(orderId);
          $("#shipment_id_value_MarkAsShipped").val(shipment_id);
          $('#__modalMarkAsShipped').doModal('open');
        });

         $('#__btnCloseModalCancelMarkAsShipped').on('click', function() {
            $('#__modalMarkAsShipped').doModal('close');
            $('#__btnCloseModalCancelMarkAsShipped').addClass('hidden');
         });

         
         $(document).on('click', '#__btnCloseModalFinalMarkAsShipped', function() {
         var orderId = $("#order_id").val();
         var shipment_id = $("#shipment_id_value_MarkAsShipped").val();
   
         $.ajax({
                type: 'GET',
                url: '{{url('WCmarkAsShipped')}}',
                data: {orderId:orderId, shipment_id:shipment_id},
                beforeSend: function() {
                    $("#__btnCloseModalFinalMarkAsShipped").html("Processing...");
                },
                success: function(responseData) {
                //alert(responseData);
                        $("#custom_shipment_details_wrapper").html("");
                        $.ajax({
                        type: 'GET',
                        data: {
                            order_id: orderId
                        },
                        url: '{{ url('getWCCustomShipmentDetailsData') }}',
                        beforeSend: function() {
                         $("#custom_shipment_details_wrapper").html("loading ......");   
                        },
                        success: function(result) {
                          
                          Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: 'Succcess',
                            text: '{{__('translation.Shipment Status has chnaged to Shipped')}}',
                            timerProgressBar: true,
                            timer: 2000,
                            position: 'top-end'
                        });
                        $("#custom_shipment_details_wrapper").html(result);
                        },
                        error: function() {
                            alert('something went wrong');
                        }
                    });
                        
                $('#__modalCancelCustomShipment').doModal('close');
                //console.log(responseData);
              },
              error: function(error) {

              }
            });
            });

            $(document).on('click', '.BtnDelete', function() {
                let drop = confirm('Are you sure?');
                if (drop) {
                    var id = $(this).data('id');
                    var order_id = $(this).data('order_id');

                    $("#tr_" + id).addClass("current2");

                    $.ajax({
                        url: wooOrderPurchaseDeleteUrl,
                        type: 'post',
                        data: {
                            'id': $(this).data('id'),
                            'order_id': $(this).data('order_id'),
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        beforeSend: function() {
                            // Pesan yang muncul ketika memproses delete
                        }
                    }).done(function(result) {
                        if (result.status === 1) {
                            // Pesan jika data berhasil di hapus
                            alert('Data deleted successfully');
                            $("#tr_" + id).hide();
                        } else {
                            alert(result.message);
                        }
                        location.reload();
                    });
                }
            });


            $(document).on('click', '#printLevel', function() {
                $("#pack_order_modal").modal('hide');
                $("#print_level_modal").modal('show');
                var shipment_id = $(this).data('shipment_id');
                var order_id = $(this).attr('order-id');
                var shop_id = $(this).attr('shop-id');
                $("#shop_id_input_val").val(shop_id);
                $("#shipment_id_input_val").val(shipment_id);
                $("#order_id_input_val").val(order_id);
                $("#order_id_div").text('Order ID #'+order_id);
                $("#shipment_id_div").text('Shipment ID #'+shipment_id);
                $.ajax
                ({
                    type: 'GET',
                    data: {shipment_id:shipment_id, order_id:order_id,shop_id:shop_id},
                    url: '{{url('getWCCustomerOrderHistory')}}',
                    success: function(result)
                    {
                        //console.log(result);
                        $("#order_details").html(result);
                    }
                });
            });



            $(document).on('click', '#bulk_print', function() {

                var rows_selected = $('#__wooOrderPurchaseTable').DataTable().column(0).checkboxes.selected();

                var arr = [];
                $.each(rows_selected, function(index, rowId){
                    arr[index]=rowId;
                                //datatable.cell(index,8).data(status);
                            });

                //console.log(arr);

                if(arr.length === 0){
                    alert("Please Select Shipment ID First");
                    return;
                }
                $( '#website_shipment_ids_input_array' ).val( arr );
                $("#bulk_print_modal").modal('show');

                $("#selected_item").text('You have selected total '+arr.length+' shipments.');
            });

        </script>

        <script type="text/javascript">
            $(document).ready(function() {
                $('#btn_sync_order').click(function() {
                    var website_id = $('#SyncModalOrder #shop option:selected').val();

                    var number_of_orders = $('input[name="number_of_orders"]').val();

                    var site_url = $('#SyncModalOrder #shop option:selected').attr('data-site_url');
                    var rest_api_key = $('#SyncModalOrder #shop option:selected').attr('data-key');
                    var rest_api_secret = $('#SyncModalOrder #shop option:selected').attr('data-secrete');
                    if (typeof rest_api_key === "undefined") {

                        $('.message_sync').html('<div class="alert alert-danger" role="alert">Please Select A Shop</div>');
                        return;
                    }
                    if (rest_api_key === "") {
                        $('.message_sync').html('<div class="alert alert-danger" role="alert">Please add REST API Consumer Key</div>');
                        return;
                    }

                    if (rest_api_secret === "") {
                        $('.message_sync').html('<div class="alert alert-danger" role="alert">Please add REST API Consumer Secrete</div>');
                        return;
                    }

                    $('.message_sync').html('<div class="alert alert-warning" role="alert">Proccesing...Please wait a bit </div>');
                    $.ajax({
                        url: '{{ route('wc_orders_sync_manually') }}',
                        type: 'POST',
                        data: {
                            'number_of_orders': number_of_orders,
                            'website_id': website_id,
                            'site_url': site_url,
                            'rest_api_key': rest_api_key,
                            'rest_api_secret': rest_api_secret,
                            'page': 1,
                            'limit': 100,
                            'per_page': 100,
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        success: function(data) {
                            $('.message_sync').html('<div class="alert alert-success" role="alert">Orders are Synchronized successfully...</div>');
                            location.reload();
                        }
                    });
                });
            });


            const arrangeShipment = (el) => {
               let order_id = el.getAttribute('data-order_id');
               let website_id = el.getAttribute('data-website_id');
               $('#__modalCreateShipmentForOrder').doModal('open');
               $.ajax({
                   type: 'GET',
                   url: '{{url('arrangeShipment')}}',
                   data: {website_id:website_id,order_id:order_id,disable_edit_quantity:1},
                   beforeSend: function() {
                       $("#modal_content_arrange_shipment_for_order").html("Loading...");
                   },
                   success: function(responseData) {
                     $("#modal_content_arrange_shipment_for_order").html("");
                       $("#modal_content_arrange_shipment_for_order").html(responseData);
                       $('#order_id').val(order_id);                       
                   },
                   error: function(error) {

                   }
               });
           }

            
            const createShipment = (el) => {
               
               let orderId = el.getAttribute('data-id');
               $('#__modalCreateShipmentForOrder').doModal('open');
               $.ajax({
                   type: 'GET',
                   url: '{{url('getAllWCOrderedProForOrder')}}',
                   data: {orderId:orderId,disable_edit_quantity:1},
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
           
           
        </script>

        <script src="{{ asset('pages/seller/order_management/index/woo_status_filter.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
