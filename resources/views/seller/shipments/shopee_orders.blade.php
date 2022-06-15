<x-app-layout>
    @section('title')
        {{ __('translation.Purchase Order') }}
    @endsection

    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.12.1/jquery-ui.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
        <link rel="stylesheet" href="{{ asset('css/custom_css.css') }}">
    @endpush

    @push('bottom_css')
        <style>
            .dataTable tbody tr td {
                border-width: 0px !important;
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }
            .card:hover {
                cursor: pointer;
            }
            .card-active {
                background-color: #d5dade;
                color: #ffffff !important;
            }
        </style>
    @endpush
    <input type="hidden" name="" id="shipment_for" value="{{$shipment_for}}">
    @if(\App\Models\Role::checkRolePermissions('Can access menu: Shopee - Order'))
        <div class="col-span-12">

            {{-- @include('partials.pages.order_management.shopee.tab_navigation') --}}
            @include('seller.shipments.shipments_tab')
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

                            @include('shopee.order.top_nav_custom_status_filter_for_shipment')

                            <div class="w-full overflow-x-auto">
                                <table class="w-full" id="__shopeeOrderPurchaseTable">
                                    <thead>
                                    <tr class="bg-blue-500">
                                        <th class="px-4 py-2 text-white"></th>
                                        <th class="px-4 py-2 text-white text-center">
                                            {{__("shopee.order.datatable.th.order_data")}}
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
                    <h5 class="modal-title" id="exampleModalLabel"><strong>{{__("shopee.order.orders_sync_data.sync_purchase_order_modal_title")}}</strong></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="exampleInputEmail1">{{__("shopee.order.orders_sync_data.shop")}}</label>
                            <select id="shop" style="width: 100%; margin-bottom: 15px;" class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 js-example-basic-single3" name="shop_id" required>
                                <option>{{__("shopee.order.orders_sync_data.select_shop")}}</option>
                                @if (isset($shops))
                                    @foreach ($shops as $shop)
                                        <option data-site_url="{{$shop->site_url}}" data-shopee_shop_id="{{$shop->shop_id}}" data-code="{{$shop->code}}" value="{{$shop->id}}">{{$shop->shop_name}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="exampleInputEmail1">{{__("shopee.order.orders_sync_data.sync_total_records")}}</label>
                            <input class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="number_of_orders" name="number_of_orders" placeholder="{{__("shopee.order.orders_sync_data.sync_total_records_placeholder")}}" type="text" />
                        </div>
                    </div>
                    <div class="col-lg-12 message_sync"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">{{__("shopee.close")}}</button>
                    <button id="btn_sync_order" type="submit" class="btn btn-success">{{__("shopee.load")}}</button>
                </div>
            </div>
        </div>
    </div>

    <x-modal.modal-small class="modal-hide modal-message">
        <x-modal.header>
            <x-modal.title>
                {{__("shopee.order.product.processing")}}
            </x-modal.title>
            <x-modal.close-button id="closeModalMessage"/>
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6" id="form-message"></div>
        </x-modal.body>
    </x-modal.modal-small>

    <x-modal.modal-large id="__modalProductsOrdered">
        <x-modal.header>
            <x-modal.title>
                {{__("shopee.order.product.ordered_products_modal_title")}}
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalProductsOrder" />
        </x-modal.header>
        <x-modal.body>
            <div class="mb-5">
                <div class="mb-4">
                    <div class="grid grid-cols-9 gap-4">
                        <div class="col-span-3">
                            {{__("shopee.order.product.order_id")}}
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
                            {{__("shopee.order.product.image")}}
                        </th>
                        <th class="text-center">
                            {{__("shopee.order.product.details")}}
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <div class="text-center pb-5 hidden" id="__actionButtonWrapperProductsOrder">
                <x-button type="button" color="blue" class="__btnCloseModalProductsOrder">
                    {{__("shopee.close")}}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.modal-large>

    @include('shopee.modals.order.arrange_order_shipment')

    @include('shopee.modals.order.arrange_batch_order_shipment')

    @include('shopee.modals.order.airway_bill_download')

    <x-modal.modal-large id="__modalOrderCancellation">
        <x-modal.header>
            <x-modal.title>
                Order Cancellation
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalOrderCancellation" />
        </x-modal.header>
        <x-modal.body>
            <div class="w-full overflow-x-auto mb-10" id="div_delivery_method">
                <div class="mb-2">
                    <div class="grid grid-cols-1 gap-4">
                        <p class="pt-2">Please select one of the reasons for cancelling the specific order.</p>
                        <input type="hidden" id="selected_cancel_order_id">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-4 pt-3">
                            <strong class="text-blue-500">
                                Reason
                            </strong>
                        </div>
                        <div class="col-span-4">
                            <x-select class="text-sm" id="order_cancellation_reason">
                                @foreach ($orderCancelationReasons as $key => $reason)
                                <option value="{{ $key }}"> {{ $reason }} </option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <p id="selected_order_cancellation_message" class="pt-4"></p>
                        <button class="btn-action--blue sm:w-1/4 mx-auto" id="cancel_order_btn">Confirm</button>
                    </div>
                </div>
            </div>
        </x-modal.body>
    </x-modal.modal-large>

    <x-modal.modal-small class="modal-hide modal-address" id="modal-address">
        <x-modal.header>
            <x-modal.title>
                {{ __("shopee.order.customer_shipping_address") }}
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
    <div class="modal" tabindex="-1" role="dialog" id="__modalAfterSearchShopeeOrderID">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title width_100">
                    <strong class="order_shipment_id" id="order_shipment_id"></strong>
                    <font class="pull-right float_right color-blue font_family_custom">STATUS: <strong id="shipment_status_div"></strong></font>
                </h5>
                <button type="button" class="close" onclick="clearShipmentNo()" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
               <div class="mb-5 font-size-15">
                    Customer Name : <strong id="customer_id_div"></strong><br>
                    <!-- Total Items : <strong id="total_items_div"></strong> -->
                  
                    <input type="hidden" id="order_id_value_after_search">
                    <input type="hidden" id="shipment_id_value_after_search">
                </div>
                <div id="after_search_modal_content"></div>
            </div>
    </div>
</div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="markAsShippedUpdateModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <strong>{{ __('translation.Cancel') }}</strong>
                </h5>
                <button type="button" class="close" onclick="clearShipmentNo()" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
               <div class="mb-5">
                    <p class="text-center">
                        <strong>{{ __('translation.Are you sure you want to cancel this?') }}</strong>
                    </p>
                    <input type="hidden" id="order_id_value_for_cancel_mark_as_shipped">
                    <input type="hidden" id="shipment_id_for_cancel_mark_as_shipped">
                </div>
                <div class="text-center pb-5">
                   
                    <x-button-link color="red" id="__btnCloseModalMarkAsShippedCancel">
                        {{ __('translation.Yes, Continue') }}
                    </x-button-link>
                </div>
            </div>
    </div>
</div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="pickOrderCancel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <strong>{{ __('translation.Cancel') }}</strong>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
               <div class="mb-5">
                    <p class="text-center">
                        <strong>{{ __('translation.Are you sure you want to cancel this?') }}</strong>
                    </p>
                    <input type="hidden" id="order_id_value_for_cancel_pick_order">
                    <input type="hidden" id="shipment_id_for_cancel_pick_order">
                </div>
                <div class="text-center pb-5">
                   
                    <x-button-link color="red" id="__btnCloseModalpickOrderCancel">
                        {{ __('translation.Yes, Continue') }}
                    </x-button-link>
                </div>
            </div>
    </div>
</div>
</div>
<!-- PICK CONFIRM MODAL -->
<div class="modal" tabindex="-1" role="dialog" id="pack_order_modal_for_shopee">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <strong> {{__('translation.Create Pick Confirm')}} </strong>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div id="printableArea_pack">
                    <h6 class="order_shipment_color">
                        <font class="color-black">Order ID # </font>
                        <strong id="order_id_div_pack"></strong>
                    </h6>
                    <div class="" id="order_details_pack"></div>
                    <input type="hidden" id="_id_for_shopee_order">

                    <div class="mt-4 text-center">
                        <button  type="button" onClick="confirmPacking()" class="btn btn-success" style="margin: 0 auto; padding: 5px 10px;">{{__('translation.Confirm Pick')}}</button>
                    </div>
            </div>
           <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>

    </div>
</div>
</div>
</div>

<x-modal.modal-small class="modal-hide" id="__modalMarkAsShippedForShopee">
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
            <input type="hidden" id="order_id_value_MarkAsShippedForShopee">
            <input type="hidden" id="shipment_id_value_MarkAsShippedForShopee">
        </div>
        <div class="text-center pb-5">
            <x-button type="button" color="gray" id="__btnCloseModalCancelMarkAsShippedForShopee">
                {{ __('translation.No, Close') }}
            </x-button>
            <x-button-link color="red" id="__btnCloseModalFinalMarkAsShippedForShopee">
                {{ __('translation.Yes, Continue') }}
            </x-button-link>
        </div>
    </x-modal.body>
</x-modal.modal-small>

    @push('bottom_js')
        <script src="{{ asset('js/jquery.validate.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.12.1/jquery-ui.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>

         <script>
            $(document).ready(function() {
                $(".shopee_shop_for_order_sync").select2({
                    maximumSelectionSize: 5
                });
            });

            var selectedStatusIds = '{{ $firstStatusOrderId }}';
            var shopeeOrderPurchaseTable = '';

            var totalProductProductsOrder = 0;

            var checkShopeeOrdersInitStatusInterval;
            const loadOrderManagementTable = (statusIds = -1, shopeeId = -1, shippingMethod = -1) => {
                shopeeOrderPurchaseTable = $('#__shopeeOrderPurchaseTable').DataTable({
                    dom: '<<"datatable_buttons"><rt>lip>',
                    bDestroy: true,
                    processing: true,
                    serverSide: true,
                    lengthMenu: [[10, 25, 50, 100, 300],[10, 25, 50, 100, 300]],
                    pageLength: 10,
                    ajax: {
                        type: 'GET',
                        url: '{{ route("shopee.order.dataAllShopeeShipments") }}',
                        data: {
                            status: statusIds,
                            shopee_id: shopeeId,
                            shipping_method: shippingMethod
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
                            data: 'checkbox',
                            className: 'asdf'
                        },
                        {
                            data: 'order_data',
                            name: 'order_data'
                        }
                    ],
                    initComplete:function() {
                        removeShopeeOrdersCountMissingAwburlMessage();
                        clearInterval(checkShopeeOrdersInitStatusInterval);
                        let status = statusIds.toLowerCase();
                        if (status === "processing") {
                            removeArrangeShipmentForProcessingNowShopeeOrders();
                            updateShopeeShippingMethodDropdown(shopeeId, shippingMethod);
                            checkProcessingShopeeOrdersForInit();
                            checkShopeeOrdersInitStatusInterval = setInterval(function() {
                                checkProcessingShopeeOrdersForInit();
                            }, 20000);
                        } else if (status === "not_printed") {
                            getShopeeOrdersCountMissingAwburlAndTrackingNumber();
                        }
                    }
                });
            }


            // const loadOrderManagementTableForShipment = (statusIds = -1, shopeeId = -1, shippingMethod = -1, shopeeShipmentNo = -1) => {
                const loadOrderManagementTableForShipment = (shopeeShipmentNo = -1) =>{
                shopeeOrderPurchaseTable = $('#__shopeeOrderPurchaseTable').DataTable({
                    dom: '<<"datatable_buttons"><rt>lip>',
                    bDestroy: true,
                    processing: true,
                    serverSide: true,
                    lengthMenu: [[10, 25, 50, 100, 300],[10, 25, 50, 100, 300]],
                    pageLength: 25,
                    ajax: {
                        type: 'GET',
                        url: '{{ route("shopee.order.dataAllShopeeShipments") }}',
                        data: {
                            // status: statusIds,
                            // shopee_id: shopeeId,
                            // shipping_method: shippingMethod,
                            shopeeShipmentNo:shopeeShipmentNo
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
                            data: 'checkbox',
                            className: 'asdf'
                        },
                        {
                            data: 'order_data',
                            name: 'order_data'
                        }
                    ],
                    initComplete:function() {
                        removeShopeeOrdersCountMissingAwburlMessage();
                        clearInterval(checkShopeeOrdersInitStatusInterval);
                        // let status = statusIds.toLowerCase();
                        // if (status === "processing") {
                        //     removeArrangeShipmentForProcessingNowShopeeOrders();
                        //     updateShopeeShippingMethodDropdown(shopeeId, shippingMethod);
                        //     checkProcessingShopeeOrdersForInit();
                        //     checkShopeeOrdersInitStatusInterval = setInterval(function() {
                        //         checkProcessingShopeeOrdersForInit();
                        //     }, 20000);
                        // } else if (status === "not_printed") {
                        //     getShopeeOrdersCountMissingAwburlAndTrackingNumber();
                        // }
                    }
                });
            }


            const updateShopeeShippingMethodDropdown = (shopeeId, shippingMethod) => {
                $.ajax({
                    url: '{{ route("shopee.order.get_shopee_shipement_methods_with_count") }}',
                    type: "POST",
                    data: {
                        'id': shopeeId,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                }).done(function(response) {
                    if (response.success && typeof(response.data.shipping_methods) !== 'undefined') {
                        let html = '<option value="" selected>- Select Shipping Method -</option>';
                        $.each(response.data.shipping_methods, function(index, data) {
                            html += '<option value="'+data.id+'" '+(data.id === shippingMethod?'selected':'')+'> ';
                            html += data.text+' <span id="'+data.id+'_orders_count">( '+data.count+' )</span></option>';
                        });
                        $("#shopee_shipment_method_filter").html(html);
                    }
                });
            }


            const toggelShopeeShippingMethodFilter = (selectedStatusIds = -1) => {
                let status = selectedStatusIds.toLowerCase();
                if (status === 'processing') {
                    let target = $("#shopee_shipping_method_div");
                    if (target.hasClass("hide")) {
                        target.removeClass("hide");
                    }
                } else {
                    let target = $("#shopee_shipping_method_div");
                    if (!target.hasClass("hide")) {
                        $('#shopee_shipment_method_filter').prop("selectedIndex", 0);
                        target.addClass("hide");
                    }
                }

                let target_download_btn = $("#btn_bulk_download_awb");
                if (["printed", "not_printed"].includes(status)) {
                    if (target_download_btn.hasClass("hide")) {
                        target_download_btn.removeClass("hide");
                    }
                } else {
                    if (!target_download_btn.hasClass("hide")) {
                        target_download_btn.addClass("hide");
                    }
                }
                
                target = $("#shopee_airway_bills_div");
                if (status == 'ready_to_ship_awb' || status == 'printed' || status == 'not_printed' || status == 'retry_ship') {
                    if (target.hasClass("hide")) {
                        target.removeClass("hide");
                    }
                } else {
                    if (!target.hasClass("hide")) {
                        target.addClass("hide");
                    }
                }
            }

            const getShopeeOrdersCountMissingAwburlAndTrackingNumber = () => {
                $.ajax({
                    url: '{{ route("shopee.order.get_missing_awb_url_and_tracking_no_count") }}'
                }).done(function(response) {
                    let message = "";
                    let show_message = false;
                    if (typeof(response.data) !== "undefined" && typeof(response.data.missing_awb_url_count) !== "undefined") {
                        if (typeof(response.data.missing_awb_url_count) !== "undefined" && parseInt(response.data.missing_awb_url_count) > 0) {
                            show_message = true;
                            message += "* "+response.data.missing_awb_url_count+(response.data.missing_awb_url_count==1?" order is":" orders are")+" missing AWB.</br>";
                        } else {
                            message = " 0 order is missing AWB.</br>";
                        }
                        if (typeof(response.data.missing_tracking_no_count) !== "undefined" && parseInt(response.data.missing_tracking_no_count) > 0) {
                            show_message = true;
                            message += "* "+response.data.missing_tracking_no_count+(response.data.missing_awb_url_count==1?" order is":" orders are")+" missing Tracking No.";
                        } else {
                            message = " 0 order is missing Tracking No.";
                        }
                        if (show_message) {
                            message = "<a href='' class='text-red-800' id='refresh_shopee_datatable_btn'>"+message+" Refresh.</a>";
                            setTimeout(function() {
                                $("#shopee_missing_awb_url_message_div").find("span").html(message);
                            }, 1000);
                        }
                    }
                });
            }

            const removeShopeeOrdersCountMissingAwburlMessage = () => {
                $("#shopee_missing_awb_url_message_div").find("span").html("");
            }

            $(document).on('click', '#refresh_shopee_datatable_btn', function(el) {
                el.preventDefault();
                $("#shopee_missing_awb_url_message_div").find("span").html("");
                shopeeOrderPurchaseTable.ajax.reload();
                getShopeeOrdersCountMissingAwburlAndTrackingNumber();
            });

            toggelShopeeShippingMethodFilter(selectedStatusIds);
            loadOrderManagementTable(selectedStatusIds);

            $(document).on('change', '#bulk_action_update_order_status', function(e) {
                var status = $(this).val();
                var rows_selected = shopeeOrderPurchaseTable.column(0).checkboxes.selected();

                var arr = [];
                $.each(rows_selected, function(index, row_id) {
                    arr[index] = row_id;
                });

                if (arr.length === 0) {
                    alert("Please Select At Least 1 Row");
                    return;
                }

                var json_data = JSON.stringify(arr);

                $.ajax({
                    url: '{{ route("shopee.order.bulk_status_update") }}',
                    type: "POST",
                    data: {
                        'json_data': json_data,
                        'status': status,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                    beforeSend: function() {
                        $('.modal-message').removeClass('modal-hide');
                        $('#form-message').html('Please wait');
                    }
                }).done(function(response) {
                    if(response.success){
                        var message = "Order status changed successfully";
                        if (typeof(response.message) !== "undefined") {
                            message = response.message;
                        }
                        $('#form-message').html('<div class="alert alert-success" role="alert">'+message+'</div>');
                        setTimeout(function(){
                            $('.modal-message').addClass('modal-hide')
                        }, 1500);
                    } else {
                        $('.modal-message').addClass('modal-hide');
                        alert(response.message);
                    }
                });
            });

            $(document).on('click', '#btn_sync_selected_order', function(e) {
                var status = $('#bulk_action_update_order_status').find(":selected").val();
                var rows_selected = shopeeOrderPurchaseTable.column(0).checkboxes.selected();

                var arr = [];
                $.each(rows_selected, function(index, row_id) {
                    arr[index] = row_id;
                });

                if (arr.length === 0) {
                    alert("Please Select At Least 1 Row");
                    return;
                }

                var json_data = JSON.stringify(arr);

                $.ajax({
                    url: '{{ route("shopee.order.bulk_sync_selected_order") }}',
                    type: "POST",
                    data: {
                        'json_data': json_data,
                        'status': status,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                    beforeSend: function() {
                        $('.modal-message').removeClass('modal-hide');
                        $('#form-message').html('Please wait');
                    }
                }).done(function(response) {
                    $('#form-message').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                    location.reload();
                });
            });

            $(document).on('click', '#closeModalMessage', function() {
                $('.modal-message').addClass('modal-hide');
            });

            const productsOrder = (el) => {
                const orderId = el.getAttribute('data-order-id');
                $('#__orderIdOutputProductsOrder').html(`#${orderId}`);

                $('#__tblProductProductsOrder').DataTable().destroy();
                const productTable = $('#__tblProductProductsOrder').DataTable({
                    ajax: {
                        type: 'GET',
                        url: '{{ route("shopee.order.products") }}',
                        data: {
                            orderId: orderId
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

            /* Cancel specific order */
            let cancelSpecificOrder = (el) => {
                var id = $(el).data('id');
                if (typeof(id) === "undefined") {
                    return;
                }

                $("#selected_order_id").val(id);
                $('#__modalOrderCancellation').doModal('open');
            }

            $('.__btnCloseModalOrderCancellation').on('click', function() {
                $('#__modalOrderCancellation').doModal('close');
                $("#selected_order_cancellation_message").html("");
                $("#order_cancellation_reason").prop("selectedIndex", 0);
            });

            $("#cancel_order_btn").on("click", function() {
                $("#div_delivery_method").addClass("hide");
                $("#div_pickup_confirmation").removeClass("hide");

                var id = $("#selected_order_id").val();
                if (typeof(id) === "undefined") {
                    $('#selected_order_cancellation_message').html('<div class="alert alert-danger" role="alert">The order is invalid.</div>');
                    return;
                }

                var reason = $('#__modalOrderCancellation #order_cancellation_reason option:selected').val();
                if (typeof(reason) === "undefined" || reason === "") {
                    $('#selected_order_cancellation_message').html('<div class="alert alert-danger" role="alert">Select a valid cancellation reason.</div>');
                    return;
                }
                $("#cancel_order_btn").prop("disabled", false);
                cancelShopeeOrder(id, reason);
            });

            function cancelShopeeOrder(id, reason) {
                $.ajax({
                    url: '{{ route("shopee.order.cancel_specific_order") }}',
                    type: "POST",
                    data: {
                        'id': id,
                        'reason': reason,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    }
                }).done(function(response) {
                    $("#cancel_order_btn").prop("disabled", false);
                    if (response.success) {
                        $('#selected_order_cancellation_message').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                        setTimeout(function() {
                            $('#__modalOrderCancellation').doModal('close');
                            location.reload();
                        }, 2000);
                    } else {
                        $('#selected_order_cancellation_message').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
                    }
                });
            }

            /* Get airway bill specific order */
            let getAirwayBillForSpecificOrder = (el) => {
                var id = $(el).data('id');
                if (typeof(id) === "undefined" || id === "") {
                    return;
                }

                var url = $(el).data('airway_bill_url');
                if (typeof(url) !== "undefined" && url !== "") {
                    window.open(url, '_blank');
                    return;
                }

                $(el).prop("disabled", true);
                $.ajax({
                    url: '{{ route("shopee.order.get_specific_order_airway_bill") }}',
                    type: "POST",
                    data: {
                        'id': id,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    }
                }).done(function(response) {
                    $(el).prop("disabled", false);
                    if (response.success && typeof(response.data) !== "undefined" && typeof(response.data.url) !== "undefined") {
                        $(el).data('airway_bill_url', response.data.url);
                        window.open(response.data.url, '_blank');
                    } else {
                        var message = "Failed to retrieve url for airway bill."
                        if (typeof(response.message) !== "undefined" && response.message !== "") {
                            message = response.message;
                        }
                        alert(message);
                    }
                });
            }

            $('.__btnCancelModalAddress').on('click', function() {
                $('.modal-address').doModal('close');
            });

            $(document).on('click', '#BtnAddress', function() {
                $('.modal-address').removeClass('modal-hide');
                $.ajax({
                    url: '{{ route('shopee.display_customer_address') }}?id=' + $(this).data('id'),
                    beforeSend: function() {
                        $('#form-address').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-address').html(result);
                });
            });

            $(document).on('click', '#btn_bulk_print_awb', function(e) {
                var status = $(this).val();
                var rows_selected = shopeeOrderPurchaseTable.column(0).checkboxes.selected();

                var arr = [];
                $.each(rows_selected, function(index, row_id) {
                    arr[index] = row_id;
                });

                if (arr.length === 0) {
                    alert("Please Select At Least 1 Row");
                    return;
                }

                var json_data = JSON.stringify(arr);

                $.ajax({
                    url: '{{ route("shopee.order.print_airway_bill_in_bulk") }}',
                    type: "POST",
                    data: {
                        'json_data': json_data,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                    beforeSend: function() {
                        $('#form-message').html('Please wait');
                    }
                }).done(function(response) {
                    if (typeof(response.message) !== "undefined" && response.message !== "") {
                        shopeeOrderPurchaseTable.ajax.reload();
                        reloadOrderStatusList();
                        alert(response.message);
                    }
                });
            });

            // $(document).on("click", ".btn_update_wearhouse_shipped_status", function() {
                function btn_update_wearhouse_shipped_status(el){
                clearShipmentNo();
                var id = $(el).data("id");
                var order_id = $(el).data("order_id");
                if (typeof(id) === "undefined" || id === "") {
                    alert("Order is not valid");
                    return;
                }
                // let conf = confirm("Please confirm your order will be \"Marked As Shipped\"");
                // if (!conf) {
                //     return;
                // }
                $('#__modalAfterSearchShopeeOrderID').modal('hide');
                $('#__modalMarkAsShippedForShopee').doModal('open');
                $("#shipment_id_value_MarkAsShippedForShopee").val(id);
               
            };

            $('#__btnCloseModalCancelMarkAsShippedForShopee').on('click', function() {
                clearShipmentNo();
                $('#__modalMarkAsShippedForShopee').doModal('close');
                $('#__btnCloseModalCancelMarkAsShippedForShopee').addClass('hidden');
            });

            $('#__btnCloseModalFinalMarkAsShippedForShopee').on('click', function() {
             clearShipmentNo();
             //var orderId = $("#order_id_value_MarkAsShipped").val();
             var id = $("#shipment_id_value_MarkAsShippedForShopee").val();
              $.ajax({
                    url: '{{ route("shopee.order.mark_order_as_shipped_to_warehouse") }}',
                    type: "POST",
                    data: {
                        'id': id,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                    beforeSend: function() {
                        $('#form-message').html('Please wait');
                    }
                }).done(function(response) {
                    if (typeof(response.message) !== "undefined" && response.message !== "") {
                        //shopeeOrderPurchaseTable.ajax.reload();
                        const selectedStatusIds = 'SHIPPED_TO_WAREHOUSE';
                        const shopId = $('#__btnShopFilter').val();
                        loadOrderManagementTable(selectedStatusIds, shopId);
                        $("#order-status-filter-for-shipment").show();
                        $("#order-status-filter").hide();
                        alert(response.message);
                        $('#__modalMarkAsShippedForShopee').doModal('close');
                    }
                });
         });

            //$(document).on("click", ".btn_update_pickup_confirm_status", function() {
            function btnUpdatePickupConfirmStatus(el){
                var id = $(el).data('id');
                var order_id = $(el).data("order_id");
                if (typeof(id) === "undefined" || id === "") {
                    alert("Order is not valid");
                    return;
                }
                $("#order_id_div_pack").text(order_id);
                $("#_id_for_shopee_order").val(id);
                $('#__modalAfterSearchShopeeOrderID').modal('hide');
                $('#pack_order_modal_for_shopee').modal('show');
                 $.ajax({
                    type: 'GET',
                    url: '{{url('get_shopee_ordered_products')}}',
                    data: {shipment_id:id},
                    beforeSend: function() {
                        $("#order_details_pack").html("Loading...");
                    },
                    success: function(responseDataNew) {
                        $("#order_details_pack").html("");
                        console.log(responseDataNew);
                        $("#order_details_pack").html(responseDataNew);
                        clearShipmentNo();

                    },
                    error: function(error) {

                    }
                });
            }

            function confirmPacking(){
                clearShipmentNo();
                var id = $("#_id_for_shopee_order").val();
                if (typeof(id) === "undefined" || id === "") {
                    alert("Order is not valid");
                    return;
                }
                $('#__modalAfterSearchShopeeOrderID').modal('hide');
                
                $.ajax({
                    url: '{{ route("shopee.order.mark_order_as_confirm_pickup") }}',
                    type: "POST",
                    data: {
                        'id': id,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                    beforeSend: function() {
                        $('#form-message').html('Please wait');
                    }
                }).done(function(response) {
                    if (typeof(response.message) !== "undefined" && response.message !== "") {
                        shopeeOrderPurchaseTable.ajax.reload();
                        //reloadOrderStatusList();
                        alert(response.message);
                        $('#pack_order_modal_for_shopee').modal('hide');
                    }
                });

            }

            $(document).on('click', '#btn_sync_order', function() {
                $('.message_sync').html('');
                $("#btn_sync_order").prop("disabled", true);

                let website_ids = $('.shopee_shop_for_order_sync').val();
                if (website_ids.length === 0) {
                    $('.message_sync').html('<div class="alert alert-danger" role="alert">Please select at least 1 shop</div>');
                    $("#btn_sync_order").prop("disabled", false);
                    return;
                }

                $('.message_sync').html('<div class="alert alert-warning" role="alert">Proccesing...Please wait a bit </div>');
                $.ajax({
                    url: '{{ route("shopee.order.orders_sync_data") }}',
                    type: 'POST',
                    data: {
                        'website_ids': JSON.stringify(website_ids),
                        'number_of_orders': $('input[name="number_of_orders"]').val(),
                        '_token': $('meta[name=csrf-token]').attr('content')
                    }
                }).done(function(response) {
                    console.log(response);
                    if (response.success) {
                        $('.message_sync').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('.message_sync').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
                        $("#btn_sync_order").prop("disabled", false);
                    }
                });


                // $('.message_sync').html('');
                // $("#btn_sync_order").prop("disabled", true);
                // var website_id = $('#SyncModalOrder #shop option:selected').val();
                // var shopee_shop_id = $('#SyncModalOrder #shop option:selected').attr('data-shopee_shop_id');
                // var code = $('#SyncModalOrder #shop option:selected').attr('data-code');
                // var number_of_orders = $('input[name="number_of_orders"]').val();
                // if (typeof shopee_shop_id === "undefined") {
                //     $('.message_sync').html('<div class="alert alert-danger" role="alert">Please Select A Shop</div>');
                //     $("#btn_sync_order").prop("disabled", false);
                //     return;
                // }
                // if (code === "") {
                //     $('.message_sync').html('<div class="alert alert-danger" role="alert">Please reset code</div>');
                //     $("#btn_sync_order").prop("disabled", false);
                //     return;
                // }

                // $('.message_sync').html('<div class="alert alert-warning" role="alert">Proccesing...Please wait a bit </div>');
                // $.ajax({
                //     url: '{{ route("shopee.order.orders_sync_data") }}',
                //     type: 'POST',
                //     data: {
                //         'website_id': website_id,
                //         'number_of_orders': number_of_orders,
                //         'shopee_shop_id': shopee_shop_id,
                //         'code': code,
                //         'page': 1,
                //         'limit': 100,
                //         'per_page': 100,
                //         '_token': $('meta[name=csrf-token]').attr('content')
                //     }
                // }).done(function(response) {
                //     if (response.success) {
                //         $('.message_sync').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                //         setTimeout(function() {
                //             location.reload();
                //         }, 1500);
                //     } else {
                //         $('.message_sync').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
                //         $("#btn_sync_order").prop("disabled", false);
                //     }
                // });
            });

            const checkProcessingShopeeOrdersForInit = () => {
                let orders = [];
                $(".processing_init").each(function () {
                    let classes = $(this).attr("class");
                    let class_arr = classes.split(" ");
                    let info = class_arr[class_arr.length-1];
                    let ordersn = info.split("_")[2];
                    orders.push(ordersn);
                });
                $.ajax({
                    url: '{{ route("shopee.order.get_shopee_orders_processing_now_for_init") }}',
                    type: 'POST',
                    data: {
                        'json_data': JSON.stringify(orders),
                        '_token': $('meta[name=csrf-token]').attr('content')
                    }
                }).done(function(response) {
                    if (response.success && typeof(response.data) !== 'undefined') {
                        let reloadStatusFilters = false;
                        if (typeof(response.data.completed) !== 'undefined' && response.data.completed.length > 0) {
                            $.each(response.data.completed, function(index, ordersn) {
                                $(".btn_arrange_shipment_"+ordersn).closest("tr").remove();
                            });
                            reloadStatusFilters = true;
                        }
                        if (typeof(response.data.failed) !== 'undefined' && response.data.failed.length > 0) {
                            $.each(response.data.failed, function(index, ordersn) {
                                $(".processing_init_"+ordersn).remove();
                                $(".btn_arrange_shipment_"+ordersn).addClass("btn-action--green");
                                $(".btn_arrange_shipment_"+ordersn).removeClass("hide");
                            });
                            reloadStatusFilters = true;
                        }
                        if (reloadStatusFilters === true) {
                            reloadOrderStatusList(true);
                        }
                    }
                });
            }

            $("#shipment_no").keypress(function(e) {
            if(e.which == 13) {

            let shipment_id = $("#shipment_no").val();
            //alert(shipment_id);

            $.ajax({
                type: 'GET',
                url: '{{url('checkIfExistsShopeeOrder')}}',
                data: {shipment_id:shipment_id},
                beforeSend: function() {
                    //$("#__btnCloseModalFinalMarkAsShipped").html("Processing...");
                },
                success: function(responseData) {
                // alert(responseData.data);
                 console.log(responseData.shipment_no);
                   
                   if(responseData.shipment_no){
                    var url = "{{ url('/order_management/') }}";
                    var edit_order_redirect_url = url+'/'+responseData.order_id+'/edit';
                    $('#__modalAfterSearchShopeeOrderID').modal('show');
                    //$("#order_shipment_id").val(responseData.shipment_no);
                    $("#order_shipment_id").html('<strong class="text_underline_asif color-blue">#'+responseData.order_id+'</strong>');
                    $("#customer_id_div").html(responseData.customer_name);
                    $("#total_items_div").html(responseData.getTotalItems);
                    $("#shipment_status_div").html(responseData.getStatus);
                    $("#error_found_message").hide();
                
                         $.ajax({
                            type: 'GET',
                            url: '{{url('after_search_modal_content_for_shopee')}}',
                            data: {shipment_id:shipment_id},
                            beforeSend: function() {
                                $("#after_search_modal_content").html("Loading...");
                            },
                            success: function(responseDataNew) {
                                //$("#after_search_modal_content").html("");
                                console.log(responseDataNew);
                                $("#after_search_modal_content").html(responseDataNew);


                            },
                            error: function(error) {

                            }
                        });
                   }
                   else{
                    $("#error_found_message").show();
                    $("#error_found_message").html(responseData);
                   }
              },
              error: function(error) {

              }
            });
            
            }
         });

     function pickOrderCancel(el){
        var shipment_id = $(el).data("id");
        var order_id = $(el).data("order_id");
        $("#shipment_id_for_cancel_pick_order").val(shipment_id);
        $("#order_id_value_for_cancel_pick_order").val(order_id);
        $('#__modalAfterSearchShopeeOrderID').modal('hide');
        $('#pickOrderCancel').modal('show');
     }

     function markAsShippedCancel(el){
        var shipment_id = $(el).data("id");
        var order_id = $(el).data("order_id");
        $("#shipment_id_for_cancel_mark_as_shipped").val(shipment_id);
        $("#order_id_value_for_cancel_mark_as_shipped").val(order_id);
        $('#__modalAfterSearchShopeeOrderID').modal('hide');
        $('#markAsShippedUpdateModal').modal('show');
     }

     $('#__btnCloseModalpickOrderCancel').on('click', function() {
        var shipment_id = $("#shipment_id_for_cancel_pick_order").val();
        var order_id = $("#order_id_value_for_cancel_pick_order").val();
        $.ajax({
            type: 'GET',
            url: '{{url('shipmentPickOrderCancelForShopee')}}',
            data: {shipment_id:shipment_id},
            beforeSend: function() {
                //$("#__btnCloseModalFinalMarkAsShipped").html("Processing...");
            },
            success: function(responseData) {
                $("#pickOrderCancel").modal('hide');
                var url = "{{ url('/all_shipment/') }}";
                window.location.href = url+'/'+$("#shipment_for").val();
            },
          error: function(error) {

          }
        });
     });

        $('#__btnCloseModalMarkAsShippedCancel').on('click', function() {
        var shipment_id = $("#shipment_id_for_cancel_mark_as_shipped").val();
        var order_id = $("#order_id_value_for_cancel_mark_as_shipped").val();
        $.ajax({
            type: 'GET',
            url: '{{url('ShopeeMarkAsShippedCancel')}}',
            data: {shipment_id:shipment_id},
            beforeSend: function() {
                //$("#__btnCloseModalFinalMarkAsShipped").html("Processing...");
            },
            success: function(responseData) {
                $("#markAsShippedUpdateModal").modal('hide');
                var url = "{{ url('/all_shipment/') }}";
                window.location.href = url+'/'+$("#shipment_for").val();
            },
          error: function(error) {

          }
        });
       });

        function clearShipmentNo(){
            $('input[name=shipment_no').val('');  
        }
        </script>

        <script src="{{ asset('pages/seller/shopee/order/index/shopee_status_filter.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
