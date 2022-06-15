<x-modal.modal-large id="__modalSetStatusToPackedByMarketplace">
    <x-modal.header>
        <x-modal.title>
            Create Package
        </x-modal.title>
        <x-modal.close-button class="__btnCloseModalHandleSetStatusToPackedByMarketplace" />
    </x-modal.header>
    <x-modal.body>
        <div class="w-full overflow-x-auto mb-10" id="div_dropoff_confirmation">
            <div class="grid grid-cols-2 gap-4 mt-2 dropoff_lazada_param_option__inputs" id="">
                <div class="pt-3">
                    <input type="hidden" id="lazada_selected_order_to_set_status_to_packed_to_marketplace">
                    <strong class="text-blue-500">
                        Shipping Provider
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="lazada_shipping_provider_to_set_status_to_packed_to_marketplace"></x-select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-2 dropoff_lazada_param_option__inputs" id="">
                <div class="pt-3">
                    <strong class="text-blue-500">
                        Delivery Type
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="lazada_delivery_type_to_set_status_to_packed_to_marketplace">
                        <option value="dropship">Dropship</option>
                    </x-select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <p id="set_status_to_packed_to_marketplace_confirmation_message" class="pt-4"></p>
                <button class="btn-action--blue sm:w-1/4 mx-auto" id="set_status_to_packed_to_marketplace_update_btn">Confirm</button>
            </div>
        </div>
    </x-modal.body>
</x-modal.modal-large>

<x-modal.modal-large id="__modalSetStatusToReadyToShip">
    <x-modal.header>
        <x-modal.title>
            Arrange Shipment
        </x-modal.title>
        <x-modal.close-button class="__btnCloseModalHandleSetStatusToReadyToShip" />
    </x-modal.header>
    <x-modal.body>
        <div class="w-full overflow-x-auto mb-10" id="div_dropoff_confirmation">
            <div class="grid grid-cols-2 gap-4 mt-2 dropoff_lazada_param_option__inputs" id="">
                <div class="pt-3">
                    <strong class="text-blue-500">
                        Tracking Number
                    </strong>
                </div>
                <div class="pt-3">
                    <input type="hidden" id="lazada_selected_order_to_set_status_to_ready_to_ship">
                    <input type="text" id="lazada_tracking_number_for_ready_to_ship" readonly>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <p id="set_status_to_ready_to_ship_confirmation_message" class="pt-4"></p>
                <button class="btn-action--blue sm:w-1/4 mx-auto" id="set_status_to_ready_to_ship_update_btn">Confirm</button>
            </div>
        </div>
    </x-modal.body>
</x-modal.modal-large>


@push('bottom_js')
<script>
    $(document).ready(function() {
        checkPassedTimeOfStoringProcessedOrderInfoInStorage();
    });


    /* Arrange Shipment Order */
    let setStatusToPackedByMarketplace = (el) => {
        var order_id = $(el).data('order_id');
        if (typeof(order_id) === "undefined") {
            return;
        }
        $("#lazada_selected_order_to_set_status_to_packed_to_marketplace").val(order_id);
        var website_id = $(el).data('website_id');
        if (typeof(website_id) === "undefined") {
            return;
        }

        getShipmentProvidersForLazada(website_id);
        $('#__modalSetStatusToPackedByMarketplace').doModal('open');
    }


    const getShipmentProvidersForLazada = (website_id) => {
        $("#lazada_shipping_provider_to_set_status_to_packed_to_marketplace").html("<option value='-1'>Loading...</option>");
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
                $("#lazada_shipping_provider_to_set_status_to_packed_to_marketplace").html(html);
            }
        });
    }


    $('.__btnCloseModalHandleSetStatusToPackedByMarketplace').on('click', function() {
        $('#__modalSetStatusToPackedByMarketplace').doModal('close');
        resetHtmlModalHandleSetStatusToPackedByMarketplace();
    });


    const resetHtmlModalHandleSetStatusToPackedByMarketplace = () => {
        $('#set_status_to_packed_to_marketplace_confirmation_message').html("");
    }


    $(document).on('click', '#set_status_to_packed_to_marketplace_update_btn', function() {
        $.ajax({
            url: '{{ route("lazada.status.set_status_to_packed_to_marketplace") }}',
            type: 'POST',
            data: {
                'order_id': $("#lazada_selected_order_to_set_status_to_packed_to_marketplace").val(),
                'shipping_provider': $("#lazada_shipping_provider_to_set_status_to_packed_to_marketplace option:selected").val(),
                'delivery_type': $("#lazada_delivery_type_to_set_status_to_packed_to_marketplace option:selected").val(),
                '_token': $('meta[name=csrf-token]').attr('content')
            }
        }).done(function(response) {
            if (typeof(response.message) !== "undefined" && response.message !== "") {
                $('#set_status_to_packed_to_marketplace_confirmation_message').html('<div class="alert '+(response.success?"alert-success":"alert-danger")+'" role="alert">'+response.message+'</div>');
            }
            setTimeout(function() {
                $('#__modalSetStatusToPackedByMarketplace').doModal('close');
                location.reload();
            }, 2000);
        });
    });


    /* Keep track of processing now lazada orders. */
    const processing_now_orders_key = "processing_now_lazada_orders";
    const processing_now_started_at_key = "processing_now_lazada_orders_started_at";
    const updateLocalStorageProcessingNowShopeeOrderInfo = (new_orders=[]) => {
        checkPassedTimeOfStoringProcessedOrderInfoInStorage();

        if (!localStorage.hasOwnProperty(processing_now_started_at_key)) {
            localStorage.setItem(processing_now_started_at_key, new Date());
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


    const removeArrangeShipmentForProcessingNowShopeeOrders = () => {
        if (localStorage.hasOwnProperty('processing_now_lazada_orders')) {
            orders = JSON.parse(localStorage.getItem("processing_now_lazada_orders"));
            $.each(orders, function(index, order_id) {
                $(".btn_arrange_shipment_"+order_id).remove();
            })
        }
    }


    const checkPassedTimeOfStoringProcessedOrderInfoInStorage = () => {
        var oldDate;
        if (localStorage.hasOwnProperty(processing_now_started_at_key)) {
            oldDate = localStorage.getItem(processing_now_started_at_key, new Date());
        }
        if (typeof(oldDate) !== "undefined") {
            if (((new Date() - new Date(oldDate)) / 60000) > 15) {
                clearArrangeShipmentForProcessingNowShopeeOrders();
                localStorage.removeItem(processing_now_started_at_key);
            }
        }
    }


    const clearArrangeShipmentForProcessingNowShopeeOrders = () => {
        if (localStorage.hasOwnProperty('processing_now_lazada_orders')) {
            localStorage.removeItem('processing_now_lazada_orders');
        }
    }


    /* Set status to ready to ship */
    let setStatusToReadyToShip = (el) => {
        var order_id = $(el).data('order_id');
        if (typeof(order_id) === "undefined") {
            return;
        }
        var tracking_no = $(el).data('tracking_no');
        if (typeof(tracking_no) === "undefined") {
            return;
        }
        $("#lazada_selected_order_to_set_status_to_ready_to_ship").val(order_id);
        $("#lazada_tracking_number_for_ready_to_ship").val(tracking_no);
        $('#__modalSetStatusToReadyToShip').doModal('open');
    }

    $(document).on('click', '#set_status_to_ready_to_ship_update_btn', function() {
        $.ajax({
            url: '{{ route("lazada.status.set_status_to_ready_to_ship") }}',
            type: 'POST',
            data: {
                'order_id': $("#lazada_selected_order_to_set_status_to_ready_to_ship").val(),
                'tracking_number': $("#lazada_tracking_number_for_ready_to_ship").val(),
                '_token': $('meta[name=csrf-token]').attr('content')
            }
        }).done(function(response) {
            if (typeof(response.success) !== "undefined" && typeof(response.message) !== "undefined" && response.message !== "") {
                $('#set_status_to_ready_to_ship_confirmation_message').html('<div class="alert '+(response.success?"alert-success":"alert-danger")+'" role="alert">'+response.message+'</div>');
            }
            setTimeout(function() {
                $('#__modalSetStatusToReadyToShip').doModal('close');
                location.reload();
            }, 2000);
        });
    });

    $('.__btnCloseModalHandleSetStatusToReadyToShip').on('click', function() {
        $('#__modalSetStatusToReadyToShip').doModal('close');
        resetHtmlModalHandleSetStatusToReadyToShip();
    });


    const resetHtmlModalHandleSetStatusToReadyToShip = () => {
        $('#set_status_to_ready_to_ship_confirmation_message').html("");
    }
</script>
@endpush