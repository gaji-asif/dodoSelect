<x-modal.modal-large id="__modalSetStatusPackedInBatchForLazadaOrders">
    <x-modal.header>
        <x-modal.title>
            <span id="lazada_bulk_init_modal_title"></span>
        </x-modal.title>
        <x-modal.close-button class="__btnCloseModalHandleSetStatusPackedInBatchForLazadaOrders" />
    </x-modal.header>
    <x-modal.body>
        <div class="w-full overflow-x-auto mb-10" id="div_packed_to_marketplace_options">
            <div class="grid grid-cols-2 gap-4 mt-2 dropoff_lazada_param_option__inputs" id="">
                <div class="pt-3">
                    <strong class="text-blue-500">
                        Shipping Provider
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="lazada_shipping_provider_to_set_status_to_packed_to_marketplace_in_bulk"></x-select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-2 dropoff_lazada_param_option__inputs" id="">
                <div class="pt-3">
                    <strong class="text-blue-500">
                        Delivery Type
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="lazada_delivery_type_to_set_status_to_packed_to_marketplace_in_bulk">
                        <option value="dropship">Dropship</option>
                    </x-select>
                </div>
            </div>
        </div>

        <div class="w-full overflow-x-auto mb-10" id="div_delivery_method_oaib">
            <div class="mb-2">
                <div class="grid grid-cols-1 gap-4">
                    <p><span id="modal_lazada_selected_orders_count"></span></p>
                    <p id="oaib_selected_shipment_message" class="pt-4"></p>
                </div>
                <div class="grid grid-cols-1 gap-4 text-center">
                    <p id="pickup_confirmation_message_oaib" class="pt-4"></p>
                    <button class="btn-action--blue sm:w-1/4 mx-auto" id="update_packed_to_marketplace_in_bulk_btn">Confirm</button>
                </div>
            </div>
        </div>
    </x-modal.body>
</x-modal.modal-large>


@push('bottom_js')
<script>
    let lazada_orders_selected_rows = [];
    let lazada_derived_status = "";
    $(document).on('click', '#btn_batch_init_selected_order', function(e) {
        let website_id = $('#__btnShopFilterLazada').val();
        if (typeof(website_id) === "undefined" || website_id === "0") {
            alert('Choose a specific shop first.');
            return;
        }
        $("#lazada_bulk_init_modal_title").val("");
        $("#modal_lazada_selected_orders_count").html("");
        lazada_derived_status = $('#lazada_processing_status_filter').find("option:selected").val();
        if (typeof(lazada_derived_status) === "undefined" || lazada_derived_status === "") {
            alert('Please select either "Pending" or "Packed" from the dropdown.');
            return;
        }

        lazada_orders_selected_rows = []
        var rows_selected = lazadaOrderPurchaseTable.column(0).checkboxes.selected();
        $.each(rows_selected, function(index, row_id) {
            lazada_orders_selected_rows[index] = row_id;
        });
        if (lazada_orders_selected_rows.length === 0) {
            alert("Please Select At Least 1 Row");
            return;
        }
        
        if (lazada_derived_status.toLocaleLowerCase() === "pending") {
            $("#lazada_bulk_init_modal_title").html("Order Set To \"Packed\" In Batch");
            $("#modal_lazada_selected_orders_count").html("Selected Orders (Pending) : "+lazada_orders_selected_rows.length);
        
            getShipmentProvidersInBulkPackedToMarketPlaceForLazada(website_id);
        } else if (lazada_derived_status.toLocaleLowerCase() === "packed") {
            $("#div_packed_to_marketplace_options").hide();
            $("#lazada_bulk_init_modal_title").html("Order Set To \"Ready To Ship\" In Batch");
            $("#modal_lazada_selected_orders_count").html("Selected Orders (Packed) : "+lazada_orders_selected_rows.length);
        }

        $('#__modalSetStatusPackedInBatchForLazadaOrders').doModal('open');
    });

    $('.__btnCloseModalHandleSetStatusPackedInBatchForLazadaOrders').on('click', function() {
        $('#__modalSetStatusPackedInBatchForLazadaOrders').doModal('close');
    });

    $('#update_packed_to_marketplace_in_bulk_btn').on('click', function() {
        let derived_status = lazada_derived_status.toLocaleLowerCase();
        let data;
        let url = "";
        let json_data = JSON.stringify(lazada_orders_selected_rows);
        if (derived_status==="pending") {
            url = '{{ route("lazada.status.set_status_to_packed_to_marketplace_in_bulk") }}';
            data = {
                'shipping_provider': $("#lazada_shipping_provider_to_set_status_to_packed_to_marketplace_in_bulk option:selected").val(),
                'delivery_type': $("#lazada_delivery_type_to_set_status_to_packed_to_marketplace_in_bulk option:selected").val(),
                'json_data': json_data,
                '_token': $('meta[name=csrf-token]').attr('content')
            };
        } else if (derived_status==="packed") {
            url = '{{ route("lazada.status.set_status_to_ready_to_ship_in_bulk") }}';
            data = {
                'json_data': json_data,
                '_token': $('meta[name=csrf-token]').attr('content')
            };
        } else {
            $('#__modalSetStatusPackedInBatchForLazadaOrders').doModal('close');
            return;
        }
        if (url === "") {
            $('#__modalSetStatusPackedInBatchForLazadaOrders').doModal('close');
            return;
        }
        $.ajax({
            url: url,
            type: "POST",
            data: data
        }).done(function(response) {
            if (typeof(response.data) !== "undefined" && typeof(response.data.ordersn_list) !== "undefined") {
                updateLocalStorageProcessingNowLazadaOrderInfo(response.data.ordersn_list, derived_status);
                setTimeout(function() {
                    removeArrangeShipmentForProcessingNowLazadaOrders(derived_status);
                }, 2000);
            }
            $("#update_packed_to_marketplace_in_bulk_btn").prop("disabled", false);
            if (response.success) {
                $('#pickup_confirmation_message_oaib').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                setTimeout(function() {
                    $('#__modalSetStatusPackedInBatchForLazadaOrders').doModal('close');
                    location.reload();
                }, 2000);
            } else {
                $('#pickup_confirmation_message_oaib').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
            }
        });
    });

    const getShipmentProvidersInBulkPackedToMarketPlaceForLazada = (website_id) => {
        $("#lazada_shipping_provider_to_set_status_to_packed_to_marketplace_in_bulk").html("<option value='-1'>Loading...</option>");
        $.ajax({
            url: '{{ route("lazada.logistics.get_shipment_providers") }}',
            type: 'POST',
            data: {
                'website_id': website_id,
                '_token': $('meta[name=csrf-token]').attr('content')
            }
        }).done(function(response) {
            if (typeof(response.data) !== "undefined" && typeof(response.data.shipment_providers) !== "undefined" && response.data.shipment_providers.length > 0) {
                let html = "";
                response.data.shipment_providers.forEach(function(provider, index) {
                    is_selected = "";
                    if (index === 0) {
                        is_selected = "selected";
                    }
                    html += "<option value='"+provider.name+"' "+is_selected+">"+provider.name+"</option>";
                });
                $("#lazada_shipping_provider_to_set_status_to_packed_to_marketplace_in_bulk").html(html);
            }
        });
    }

    /* Keep track of processing now lazada orders. */
    const processing_now_lazada_orders_key = "processing_now_lazada_orders";
    const processing_now_lazada_orders_started_at_key = "processing_now_lazada_orders_started_at";
    const updateLocalStorageProcessingNowLazadaOrderInfo = (new_orders=[]) => {
        checkPassedTimeOfStoringProcessedLazadaOrderInfoInStorage();

        if (!localStorage.hasOwnProperty(processing_now_lazada_orders_started_at_key)) {
            localStorage.setItem(processing_now_lazada_orders_started_at_key, new Date());
        }

        let orders = [];
        if (localStorage.hasOwnProperty('processing_now_lazada_orders')) {
            orders = JSON.parse(localStorage.getItem("processing_now_lazada_orders"));
            $.each(new_orders, function(index, order_id) {
                orders.push(order_id);
            })
        } else {
            orders = new_orders;
        }
        localStorage.setItem("processing_now_lazada_orders", JSON.stringify(orders));
    }


    const removeArrangeShipmentForProcessingNowLazadaOrders = () => {
        if (localStorage.hasOwnProperty('processing_now_lazada_orders')) {
            orders = JSON.parse(localStorage.getItem("processing_now_lazada_orders"));
            $.each(orders, function(index, order_id) {
                $(".btn_arrange_shipment_"+order_id).remove();
                $(".btn_create_package_"+order_id).remove();
            })
        }
    }


    const checkPassedTimeOfStoringProcessedLazadaOrderInfoInStorage = () => {
        var oldDate;
        if (localStorage.hasOwnProperty(processing_now_lazada_orders_started_at_key)) {
            oldDate = localStorage.getItem(processing_now_lazada_orders_started_at_key, new Date());
        }
        if (typeof(oldDate) !== "undefined") {
            if (((new Date() - new Date(oldDate)) / 60000) > 5) {
                clearArrangeShipmentForProcessingNowLazadaOrders();
                localStorage.removeItem(processing_now_lazada_orders_started_at_key);
            }
        }
    }


    const clearArrangeShipmentForProcessingNowLazadaOrders = () => {
        if (localStorage.hasOwnProperty('processing_now_lazada_orders')) {
            localStorage.removeItem('processing_now_lazada_orders');
        }
    }
</script>
@endpush