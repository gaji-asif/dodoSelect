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
            .lazada_shop_for_order_sync_form .select2-selection--multiple {
                height: 120px !important;
                overflow-x: scroll !important;
            } 
        </style>
    @endpush

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Lazada - Order'))
        <div class="col-span-12">

            {{-- @include('partials.pages.order_management.lazada.tab_navigation') --}}
            @include('partials.pages.orders.marketplace_order_tab_navigation')

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

                            @include('lazada.order.top_nav_custom_status_filter')

                            <div class="flex flex-col sm:flex-row sm:justify-between items-start mb-4">
                                <div class="w-full md:w-1/2 lg:w-1/2 mb-6 sm:mb-0">
                                    <div class="flex flex-col sm:flex-row">
                                        <div class="sm:ml-2">
                                            <x-button type="button" color="green" id="btn_sync_selected_order" class="mb-2 sync_selected">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                                                    <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                                                    <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                                                </svg>
                                                <span class="ml-2">{{__("lazada.order.sync_selected")}}</span>
                                            </x-button>
                                        </div>
                                        <div class="sm:ml-2 pt-1" id="lazada_missing_awb_url_message_div">
                                            <span class="text-sm text-red-800"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="w-full md:w-1/2 lg:w-1/2 mb-6 sm:mb-0">
                                    <div class="flex flex-col sm:flex-row sm:justify-end" id="lazada_shipping_method_div" >
                                        <div class="sm:ml-2">
                                            <x-button type="button" color="green" id="btn_batch_init_selected_order" class="mb-2 sync_selected">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                                                    <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                                                    <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                                                </svg>
                                                <span class="ml-2">{{__("translation.batch init")}}</span>
                                            </x-button>
                                        </div>
                                        <div class="sm:ml-2">
                                            <x-select class="text-sm hide" name="lazada_shipment_method_filter" id="lazada_shipment_method_filter">
                                                @foreach ($shippingMethodForLazada as $key => $data)
                                                    <option value="{{ $data['id'] }}"> {{ $data['text'] }} <span id="{{ $data['id'] }}_orders_count">( {{ $data['count'] }} )</span></option>
                                                @endforeach
                                            </x-select>
                                        </div>
                                        <div class="sm:ml-2">
                                            <x-select class="text-sm" name="lazada_processing_status_filter" id="lazada_processing_status_filter">
                                                <option disabled value="" selected>- Select Status -</option>    
                                                <option id="lazada_pending_orders_option" value="PENDING">Pending </option>
                                                <option id="lazada_packed_orders_option" value="PACKED">Packed</option>
                                            </x-select>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row sm:justify-end mt-3" id="lazada_airway_bills_div" >
                                        <div class="sm:ml-2">
                                            <x-button type="button" color="green" id="btn_bulk_download_awb" class="mb-2 sync_selected">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                                                    <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                                                    <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                                                </svg>
                                                <span class="ml-2">Batch Print <span id="oaib_downloadable_pdf_count"></span></span>
                                            </x-button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="w-full overflow-x-auto">
                                <table class="w-full" id="__lazadaOrderPurchaseTable">
                                    <thead>
                                    <tr class="bg-blue-500">
                                        <th class="px-4 py-2 text-white"></th>
                                        <th class="px-4 py-2 text-white text-center">
                                            {{__("lazada.order.datatable.th.order_data")}}
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

    <div class="modal fade" id="SyncModalOrderLazada" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><strong>{{__("lazada.order.orders_sync_data.sync_purchase_order_modal_title")}}</strong></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="col-lg-12">
                        <div class="form-group lazada_shop_for_order_sync_form">
                            <label for="exampleInputEmail1">{{__("lazada.order.orders_sync_data.shop")}}</label>
                            <select class="lazada_shop_for_order_sync" id="shop" name="shop[]" multiple="multiple" style="width: 100%; margin-bottom: 15px;" class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 js-example-basic-single3" name="shop_id" required>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="exampleInputEmail1">{{__("lazada.order.orders_sync_data.sync_total_records")}}</label>
                            <input class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="number_of_orders" name="number_of_orders" placeholder="{{__("lazada.order.orders_sync_data.sync_total_records_placeholder")}}" type="text" />
                        </div>
                    </div>
                    <div class="col-lg-12 message_sync"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">{{__("lazada.close")}}</button>
                    <button id="btn_sync_order_lazada" type="submit" class="btn btn-success">{{__("lazada.load")}}</button>
                </div>
            </div>
        </div>
    </div>

    <x-modal.modal-small class="modal-hide modal-message">
        <x-modal.header>
            <x-modal.title>
                {{__("lazada.order.product.processing")}}
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
                {{__("lazada.order.product.ordered_products_modal_title")}}
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalProductsOrder" />
        </x-modal.header>
        <x-modal.body>
            <div class="mb-5">
                <div class="mb-4">
                    <div class="grid grid-cols-9 gap-4">
                        <div class="col-span-3">
                            {{__("lazada.order.product.order_id")}}
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
                            {{__("lazada.order.product.image")}}
                        </th>
                        <th class="text-center">
                            {{__("lazada.order.product.details")}}
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <div class="text-center pb-5 hidden" id="__actionButtonWrapperProductsOrder">
                <x-button type="button" color="blue" class="__btnCloseModalProductsOrder">
                    {{__("lazada.close")}}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.modal-large>

    @include('lazada.modals.order.arrange_order_shipment')

    @include('lazada.modals.order.arrange_batch_order_set_packed')

    @include('lazada.modals.order.airway_bill_download')

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
                {{ __("lazada.order.customer_shipping_address") }}
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

    @push('bottom_js')
        <script src="{{ asset('js/jquery.validate.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.12.1/jquery-ui.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>

        <script>
            var selectedStatusIds = '{{ $firstStatusOrderId }}';
            var selectedShopId;
            var selectedParentStatusId;
            var selectedOrderStatusType;
            var lazadaOrderPurchaseTable = '';
            var checkLazadaOrdersInitStatusInterval;
            var totalProductProductsOrder = 0;

            $(document).ready(function() {
                let prevSelectedFilterInfo = getSelectedShopAndStatusFilterInfo();
                if (prevSelectedFilterInfo.selectedStatusIds !== null &&  
                    prevSelectedFilterInfo.selectedShopId !== null) {
                    selectedStatusIds = prevSelectedFilterInfo.selectedStatusIds;  
                    selectedShopId = prevSelectedFilterInfo.selectedShopId;      
                    loadOrderManagementTable(selectedStatusIds, selectedShopId, -1, getAdditionalData());
                } else {
                    loadOrderManagementTable(selectedStatusIds);
                }
                toggelLazadaShippingMethodFilter(selectedStatusIds);

                if (prevSelectedFilterInfo.selectedShopId !== null &&  
                    prevSelectedFilterInfo.selectedParentStatusId !== null && 
                    prevSelectedFilterInfo.selectedOrderStatusType !== null) {
                    selectedShopId = prevSelectedFilterInfo.selectedShopId;
                    selectedParentStatusId = prevSelectedFilterInfo.selectedParentStatusId;
                    selectedOrderStatusType = prevSelectedFilterInfo.selectedOrderStatusType;
                    updateClassesForLazadaTopNavbar(selectedOrderStatusType, selectedParentStatusId);
                    loadOrderStatusList(selectedParentStatusId, selectedShopId, true);
                }

                loadLazadaShopsForBulkSyncModal();
            });


            const loadLazadaShopsForBulkSyncModal = () => {
                $(".lazada_shop_for_order_sync").select2({
                    ajax: {
                        url: '{{ route("lazada.order.shops_for_bulk_order_syncing") }}',
                        dataType: 'json',
                        type: 'POST',
                        processResults: function(response) {
                            return {
                                results: response.data
                            };
                        },
                        cache: true
                    },
                    maximumSelectionSize: 20,
                    allowClear: true,
                    placeholder: 'Select a shop',
                    escapeMarkup: function(markup) {
                        return markup;
                    },
                    templateResult: function(data) {
                        return data.html;
                    },
                    templateSelection: function(data) {
                        return data.text;
                    }
                });
            }


            const loadOrderManagementTable = (statusIds = -1, lazadaId = -1, shippingMethod = -1, additionalData = []) => {
                let derivedStatus = "";
                if (typeof(additionalData.derived_status) !== "undefined") {
                    derivedStatus = additionalData.derived_status;
                }
                lazadaOrderPurchaseTable = $('#__lazadaOrderPurchaseTable').DataTable({
                    dom: '<<"datatable_buttons"><rt>lip>',
                    bDestroy: true,
                    processing: true,
                    serverSide: true,
                    lengthMenu: [[10, 25, 50, 100, 300],[10, 25, 50, 100, 300]],
                    pageLength: 50,
                    ajax: {
                        type: 'GET',
                        url: '{{ route("lazada.order.data") }}',
                        data: {
                            status: statusIds,
                            lazada_id: lazadaId,
                            shipping_method: shippingMethod,
                            derived_status: derivedStatus
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
                        clearInterval(checkLazadaOrdersInitStatusInterval);
                        let status = statusIds.toLowerCase();
                        // if (status === "processing") {
                        //     if (derivedStatus !== "") {
                        //         removeArrangeShipmentForProcessingNowLazadaOrders(derivedStatus.toLowerCase());
                        //         checkProcessingLazadaOrdersForInit();
                        //         checkLazadaOrdersInitStatusInterval = setInterval(function() {
                        //             checkProcessingLazadaOrdersForInit();
                        //         }, 20000);
                        //     } else {
                        //         getLazadaOrdersCountForFilter($("#__btnShopFilterLazada").find("option:selected").val());
                        //     }
                        // }

                        if (status === "processing") {
                            removeArrangeShipmentForProcessingNowLazadaOrders(derivedStatus.toLowerCase());
                            checkProcessingLazadaOrdersForInit();
                            checkLazadaOrdersInitStatusInterval = setInterval(function() {
                                checkProcessingLazadaOrdersForInit();
                            }, 20000);
                            getLazadaOrdersCountForFilter($("#__btnShopFilterLazada").find("option:selected").val());
                        }
                    }
                });
            }


            const checkProcessingLazadaOrdersForInit = () => {
                let derivedStatus = $("#lazada_processing_status_filter").find("option:selected").val().toLowerCase();
                let orders = [];
                $(".processing_init").each(function () {
                    let classes = $(this).attr("class");
                    let class_arr = classes.split(" ");
                    let info = class_arr[class_arr.length-1];
                    let ordersn = info.split("_")[2];
                    if (typeof(ordersn) !== "undefined") {
                        orders.push(ordersn);
                    }
                });
                if (orders.length == 0) {
                    clearArrangeShipmentForProcessingNowLazadaOrders();
                    return;
                }
                $.ajax({
                    url: '{{ route("lazada.order.get_lazada_orders_processing_now") }}',
                    type: 'POST',
                    data: {
                        'json_data': JSON.stringify(orders),
                        '_token': $('meta[name=csrf-token]').attr('content')
                    }
                }).done(function(response) {
                    if (response.success && typeof(response.data) !== 'undefined') {
                        let reloadStatusFilters = false;
                        if (typeof(response.data.completed) !== 'undefined' && response.data.completed.length > 0) {
                            // $.each(response.data.completed, function(index, ordersn) {
                            //     if (derivedStatus === "pending") {
                            //         $(".btn_create_package_"+ordersn).closest("tr").remove();
                            //     } else if (derivedStatus === "packed") {
                            //         $(".btn_arrange_shipment_"+ordersn).closest("tr").remove();
                            //     }
                            // });
                            reloadStatusFilters = true;
                        }
                        if (typeof(response.data.failed) !== 'undefined' && response.data.failed.length > 0) {
                            // $.each(response.data.failed, function(index, ordersn) {
                            //     $(".processing_init_"+ordersn).remove();
                            //     if (derivedStatus === "pending") {
                            //         $(".btn_create_package_"+ordersn).addClass("btn-action--green");
                            //         $(".btn_create_package_"+ordersn).removeClass("hide");
                            //     } else if (derivedStatus === "packed") {
                            //         $(".btn_arrange_shipment_"+ordersn).addClass("btn-action--green");
                            //         $(".btn_arrange_shipment_"+ordersn).removeClass("hide");
                            //     }
                            // });
                            reloadStatusFilters = true;
                        }
                        if (reloadStatusFilters === true) {
                            lazadaOrderPurchaseTable.ajax.reload();
                            reloadOrderStatusList(true);
                            getLazadaOrdersCountForFilter($("#__btnShopFilterLazada").find("option:selected").val());
                        }
                    }
                });
            }


            const toggelLazadaShippingMethodFilter = (selectedStatusIds = -1) => {
                let status = selectedStatusIds.toLowerCase();
                if (status === 'processing') {
                    let target = $("#lazada_shipping_method_div");
                    if (target.hasClass("hide")) {
                        target.removeClass("hide");
                    }
                } else {
                    let target = $("#lazada_shipping_method_div");
                    if (!target.hasClass("hide")) {
                        $('#lazada_shipment_method_filter').prop("selectedIndex", 0);
                        target.addClass("hide");
                    }
                }

                let target_download_btn = $("#btn_bulk_download_awb");
                /* Remove "shipped" status from condition */
                if (["printed", "not_printed", "shipped"].includes(status)) {
                    if (target_download_btn.hasClass("hide")) {
                        target_download_btn.removeClass("hide");
                    }
                } else {
                    if (!target_download_btn.hasClass("hide")) {
                        target_download_btn.addClass("hide");
                    }
                }
                
                target = $("#lazada_airway_bills_div");
                /* Remove "shipped" status from condition */
                if (status == 'ready_to_ship_awb' || status == 'printed' || status == 'not_printed' || status == 'retry_ship' || status == 'shipped') {
                    if (target.hasClass("hide")) {
                        target.removeClass("hide");
                    }
                } else {
                    if (!target.hasClass("hide")) {
                        target.addClass("hide");
                    }
                }
            }


            const removeLazadaOrdersCountMissingAwburlMessage = () => {
                $("#lazada_missing_awb_url_message_div").find("span").html("");
            }


            $(document).on('click', '#refresh_lazada_datatable_btn', function(el) {
                el.preventDefault();
                $("#lazada_missing_awb_url_message_div").find("span").html("");
                lazadaOrderPurchaseTable.ajax.reload();
                getLazadaOrdersCountMissingAwburlAndTrackingNumber();
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
                        url: '{{ route("lazada.order.products") }}',
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
                cancelLazadaOrder(id, reason);
            });


            // function cancelLazadaOrder(id, reason) {
            //     $.ajax({
            //         url: '{{ route("shopee.order.cancel_specific_order") }}',
            //         type: "POST",
            //         data: {
            //             'id': id,
            //             'reason': reason,
            //             '_token': $('meta[name=csrf-token]').attr('content')
            //         }
            //     }).done(function(response) {
            //         $("#cancel_order_btn").prop("disabled", false);
            //         if (response.success) {
            //             $('#selected_order_cancellation_message').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
            //             setTimeout(function() {
            //                 $('#__modalOrderCancellation').doModal('close');
            //                 location.reload();
            //             }, 2000);
            //         } else {
            //             $('#selected_order_cancellation_message').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
            //         }
            //     });
            // }


            $('.__btnCancelModalAddress').on('click', function() {
                $('.modal-address').doModal('close');
            });


            $(document).on('click', '#BtnAddress', function() {
                $('.modal-address').removeClass('modal-hide');
                $.ajax({
                    url: '{{ route("lazada.display_customer_address") }}?id=' + $(this).data('id'),
                    beforeSend: function() {
                        $('#form-address').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-address').html(result);
                });
            });

            $(document).on('click', '#btn_bulk_print_awb', function(e) {
                var status = $(this).val();
                var rows_selected = lazadaOrderPurchaseTable.column(0).checkboxes.selected();

                var arr = [];
                $.each(rows_selected, function(index, row_id) {
                    arr[index] = row_id;
                });

                if (arr.length === 0) {
                    alert("Please Select At Least 1 Row");
                    return;
                }

                var json_data = JSON.stringify(arr);

                // $.ajax({
                //     url: '{{ route("shopee.order.print_airway_bill_in_bulk") }}',
                //     type: "POST",
                //     data: {
                //         'json_data': json_data,
                //         '_token': $('meta[name=csrf-token]').attr('content')
                //     },
                //     beforeSend: function() {
                //         $('#form-message').html('Please wait');
                //     }
                // }).done(function(response) {
                //     if (typeof(response.message) !== "undefined" && response.message !== "") {
                //         lazadaOrderPurchaseTable.ajax.reload();
                //         reloadOrderStatusList();
                //         alert(response.message);
                //     }
                // });
            });


            $(document).on("click", ".btn_update_wearhouse_shipped_status", function() {
                var id = $(this).data("id");
                if (typeof(id) === "undefined" || id === "") {
                    alert("Order is not valid");
                    return;
                }

                let conf = confirm("Please confirm your order will be \"Marked As Shipped\"");
                if (!conf) {
                    return;
                }

                $.ajax({
                    url: '{{ route("lazada.order.mark_order_as_shipped_to_warehouse") }}',
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
                        lazadaOrderPurchaseTable.ajax.reload();
                        reloadOrderStatusList();
                        alert(response.message);
                    }
                });
            });


            $(document).on("click", ".btn_update_pickup_confirm_status", function() {
                var id = $(this).data("id");
                if (typeof(id) === "undefined" || id === "") {
                    alert("Order is not valid");
                    return;
                }

                let conf = confirm("Please confirm you will \"Pick Confirm\" this order.");
                if (!conf) {
                    return;
                }

                $.ajax({
                    url: '{{ route("lazada.order.mark_order_as_confirm_pickup") }}',
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
                        lazadaOrderPurchaseTable.ajax.reload();
                        reloadOrderStatusList();
                        alert(response.message);
                    }
                });
            });

            
            $(document).on('click', '#btn_sync_order_lazada', function() {
                $('.message_sync').html('');
                $("#btn_sync_order_lazada").prop("disabled", true);

                let website_ids = $('.lazada_shop_for_order_sync').val();
                if (website_ids.length === 0) {
                    $('.message_sync').html('<div class="alert alert-danger" role="alert">Please select at least 1 shop</div>');
                    $("#btn_sync_order_lazada").prop("disabled", false);
                    return;
                }


                $('.message_sync').html('<div class="alert alert-warning" role="alert">Proccesing...Please wait a bit </div>');
                $.ajax({
                    url: '{{ route("lazada.order.orders_sync_data") }}',
                    type: 'POST',
                    data: {
                        'website_ids': JSON.stringify(website_ids),
                        'number_of_orders': $('input[name="number_of_orders"]').val(),
                        '_token': $('meta[name=csrf-token]').attr('content')
                    }
                }).done(function(response) {
                    if (response.success) {
                        $('.message_sync').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                        setTimeout(function() {
                            location.reload();
                        }, 2500);
                    } else {
                        $('.message_sync').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
                        $("#btn_sync_order_lazada").prop("disabled", false);
                    }
                });
            });


            const getLazadaOrdersCountForFilter = (shopId) => {
                $.ajax({
                    url: '{{ route("lazada.order.get_lazada_orders_count") }}',
                    type: "POST",
                    data: {
                        'id': shopId,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    }
                }).done(function(response) {
                    if (typeof(response.data) !== "undefined") {
                        if (typeof(response.data.pending) !== "undefined") {
                            $("#lazada_pending_orders_option").html("Pending ("+response.data.pending+")");
                        } else {
                            $("#lazada_pending_orders_option").html("Pending (0)");
                        }
                        if (typeof(response.data.packed) !== "undefined") {
                            $("#lazada_packed_orders_option").html("Packed ("+response.data.packed+")");
                        } else {
                            $("#lazada_packed_orders_option").html("Packed (0)");
                        }
                    }
                });
            }
        </script>

        <script src="{{ asset('pages/seller/lazada/order/index/lazada_status_filter.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
