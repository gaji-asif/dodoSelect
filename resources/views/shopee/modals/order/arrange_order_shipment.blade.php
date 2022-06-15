<x-modal.modal-large id="__modalArrangeOrderShipment">
    <x-modal.header>
        <x-modal.title>
            Arrange Order Shipment
        </x-modal.title>
        <x-modal.close-button class="__btnCloseModalHandleOrderShipment" />
    </x-modal.header>
    <x-modal.body>
        <div class="w-full overflow-x-auto mb-10" id="div_delivery_method">
            <div class="mb-2">
                <div class="grid grid-cols-1 gap-4">
                    <p class="pt-2" id="shipment_method_basic_info">Select one of the following shipping methods</p>
                    <input type="hidden" id="selected_order_id">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-4 text-center pt-6 pb-6 card card-option" data-shipment_method="dropoff">
                        <strong class="text-blue-500">
                            Drop-off at post office
                        </strong>
                    </div>
                    <div class="col-span-4 text-center pt-6 pb-6 card card-option" data-shipment_method="pickup">
                        <strong class="text-blue-500">
                            Pick-up at delivery address
                        </strong>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <p id="selected_shipment_message" class="pt-4"></p>
                    <button class="btn-action--blue sm:w-1/4 mx-auto" id="select_shipment_method_btn" disabled>Confirm</button>
                </div>
            </div>
        </div>

        <div class="w-full overflow-x-auto mb-10 hide" id="div_pickup_confirmation">
            <div class="grid grid-cols-2 gap-4">
                <div class="pt-3">
                    <strong class="text-blue-500">
                        Address
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="pickup_address"></x-select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-2">
                <div class="pt-3">
                    <strong class="text-blue-500">
                        Time Slot
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="pickup_time"></x-select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <p id="pickup_confirmation_message" class="pt-4"></p>
                <button class="btn-action--blue sm:w-1/4 mx-auto" id="update_pickup_info_btn" disabled>Confirm</button>
            </div>
        </div>

        <div class="w-full overflow-x-auto mb-10 hide" id="div_dropoff_confirmation">
            <div class="grid grid-cols-2 gap-4">
                <div class="pt-3">
                    <strong class="text-blue-500">
                        Option
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="dropoff_shopee_param_option">
                        <option value="branch_id">Branch</option>
                        <option value="tracking_no">Tracking Number</option>
                        <option value="sender_real_name">Sender Real Name</option>
                    </x-select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-2 dropoff_shopee_param_option__inputs" id="dropoff_shopee_param_option__branch_id">
                <div class="pt-3">
                    <strong class="text-blue-500">
                        State
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="dropoff_shopee_branch_states"></x-select>
                </div>

                <div class="pt-3">
                    <strong class="text-blue-500">
                        City
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="dropoff_shopee_branch_cities"></x-select>
                </div>

                <div class="pt-3">
                    <strong class="text-blue-500">
                        Branches
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="dropoff_shopee_branches"></x-select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-2 dropoff_shopee_param_option__inputs hide" id="dropoff_shopee_param_option__tracking_no">
                <div class="pt-3">
                    <strong class="text-blue-500">
                        Tracking Number
                    </strong>
                </div>
                <div>
                    <input type="text" class="" id="dropoff_shopee_tracking_no" placeholder="Enter the tracking number">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-2 dropoff_shopee_param_option__inputs hide" id="dropoff_shopee_param_option__sender_real_name">
                <div class="pt-3">
                    <strong class="text-blue-500">
                        Sender Real Name
                    </strong>
                </div>
                <div>
                    <input type="text" class="" id="dropoff_shopee_sender_real_name" placeholder="Enter sender real name">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <p id="dropoff_confirmation_message" class="pt-4"></p>
                <button class="btn-action--blue sm:w-1/4 mx-auto" id="update_dropoff_info_btn" disabled>Confirm</button>
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
    let selected_single_shipping_method = "";
    let arrangeOrderShipment = (el) => {
        selected_single_shipping_method = "";
        var id = $(el).data('id');
        if (typeof(id) === "undefined") {
            return;
        }

        $("#selected_order_id").val(id);
        $('#__modalArrangeOrderShipment').doModal('open');

        selected_single_shipping_method = $("#shopee_shipment_method_filter").find("option:selected").val();
        if (typeof(selected_single_shipping_method) !== 'undefined' && selected_single_shipping_method !== "" && ["pickup", "dropoff_branch_id", "dropoff_tracking_no"].includes(selected_single_shipping_method)) {
            let target_card = 1;
            if (["dropoff_branch_id", "dropoff_tracking_no"].includes(selected_single_shipping_method)) {
                target_card = 0;
            }
            $(".card-option")[target_card].click();
            $(".card").each(function() {
                $(this).prop("disabled", true);
            });
        }
    }

    let method;
    $(".card-option").on("click", function() {
        method = $(this).data("shipment_method");
        if (typeof(method) === "undefined" || method === "") {
            retrun;
        }
        $(".card").each(function() {
            $(this).removeClass("card-active");
        });
        var basic_info = "";
        var message = "";
        if (method=="dropoff") {
            basic_info = "Drop-off basic info goes here.";
            message = '<div class="alert alert-warning" role="alert">Drop-off selected. Checking if pick-up is allowed for this order.</div>';
        } else {
            basic_info = "Pick-up basic info goes here.";
            message = '<div class="alert alert-warning" role="alert">Pick-up selected. Checking if pick-up is allowed for this order.</div>';
        }
        $(this).addClass("card-active");
        $("#shipment_method_basic_info").html(basic_info);
        $("#selected_shipment_message").html(message);

        var id = $("#selected_order_id").val();
        if (typeof(id) !== "undefined" && id !== "") {
            checkShopeeLogisticInfo(id, method);
        }
    });

    function checkShopeeLogisticInfo(id, method) {
        $.ajax({
            url: '{{ route("shopee.order.get_logistic_info") }}',
            type: "POST",
            data: {
                'id': id,
                'shipping_method': method,
                '_token': $('meta[name=csrf-token]').attr('content')
            }
        }).done(function(response) {
            if (response.success) {
                if (method=="pickup") {
                    let message = "";
                    if (typeof(response.data) !== "undefined" && typeof(response.data[0]) !== "undefined" && response.data[0] === "address_id") {
                        $("#select_shipment_method_btn").prop("disabled", false);
                        message = '<div class="alert alert-success" role="alert">Pick-up is allowed for this order. Please proceed by clicking the "confirm" button.</div>';
                    } else {
                        message = '<div class="alert alert-danger" role="alert">Pick-up is not allowed for this order.</div>';
                    }
                    $("#selected_shipment_message").html(message);
                } else if (method=="dropoff") {
                    let message = "";
                    if (typeof(response.data) !== "undefined") {
                        $("#select_shipment_method_btn").prop("disabled", false);
                        message = '<div class="alert alert-success" role="alert">Drop-off is allowed for this order. Please proceed by clicking the "confirm" button.</div>';
                    } else {
                        message = '<div class="alert alert-danger" role="alert">Drop-off is not allowed for this order.</div>';
                    }
                    $("#selected_shipment_message").html(message);
                }
            }
        });
    }

    $('.__btnCloseModalHandleOrderShipment').on('click', function() {
        $('#__modalArrangeOrderShipment').doModal('close');
        resetHtmlModalHandleOrderShipment();
    });

    const resetHtmlModalHandleOrderShipment = () => {
        $(".card").each(function() {
            $(this).removeClass("card-active");
        });
        $("#shipment_method_basic_info").html("Select one of the following shipping methods");
        $("#selected_shipment_message").html("");
        $("#select_shipment_method_btn").prop("disabled", true);
        $("#div_delivery_method").removeClass("hide");

        $("#div_pickup_confirmation").addClass("hide");
        $("#update_pickup_info_btn").prop("disabled", false);
        $("#pickup_address").html("");
        $("#pickup_time").html("");
        $('#pickup_confirmation_message').html("");

        $("#div_dropoff_confirmation").addClass("hide"); 
        $(".dropoff_shopee_param_option__inputs").each(function() {
            $(this).addClass("hide");
        });
        $("#dropoff_shopee_param_option__branch_id").removeClass("hide");
        $("#update_dropoff_info_btn").prop("disabled", false);
        $('#dropoff_shopee_param_option').prop("selectedIndex", 0);
        $('#dropoff_shopee_branch_states').prop("selectedIndex", 0);
        $("#dropoff_shopee_branch_cities").html("");
        $("#dropoff_shopee_branches").html("");
        $("#dropoff_shopee_tracking_no").val("");
        $("#dropoff_shopee_sender_real_name").val("");
        $('#dropoff_confirmation_message').html("");
    }

    $("#select_shipment_method_btn").on("click", function() {
        if (method=="pickup") {
            $("#div_delivery_method").addClass("hide");
            $("#div_pickup_confirmation").removeClass("hide");
            var id = $("#selected_order_id").val();
            if (typeof(id) === "undefined") {
                return;
            }
            $("#pickup_address").html("<option value='-1'>Loading...</option>");
            getShopeePickupAddress(id);
        } else if (method=="dropoff") {
            $("#div_delivery_method").addClass("hide");
            $("#div_dropoff_confirmation").removeClass("hide");
            var id = $("#selected_order_id").val();
            if (typeof(selected_single_shipping_method) !== 'undefined' && selected_single_shipping_method !== "" && ["dropoff_branch_id", "dropoff_tracking_no"].includes(selected_single_shipping_method)) {
                if (selected_single_shipping_method === 'dropoff_tracking_no') {
                    $("#dropoff_shopee_param_option").prop("selectedIndex", 1).change();
                    return;
                }
            }
            $("#dropoff_shopee_branchess").html("<option value='-1'>Loading...</option>");
            getShopeeBranchStates(id);
            $("#update_dropoff_info_btn").prop("disabled", false);
        }
    });

    $(document).on('change', '#dropoff_shopee_param_option', function(e) {
        var id = $("#selected_order_id").val();
        var val = $(this).find("option:selected").val();
        if (typeof(id) === "undefined" || typeof(val) === "undefined") {
            return;
        }

        $(".dropoff_shopee_param_option__inputs").each(function() {
            $(this).addClass("hide");
        });
        if (val === "branch_id") {
            $("#dropoff_shopee_param_option__branch_id").removeClass("hide");
        } else if (val === "tracking_no") {
            $("#dropoff_shopee_param_option__tracking_no").removeClass("hide");
        }  else if (val === "sender_real_name") {
            $("#dropoff_shopee_param_option__sender_real_name").removeClass("hide");
        } 
    });

    $(document).on('change', '#dropoff_shopee_branch_states', function(e) {
        var id = $("#selected_order_id").val();
        var state = $(this).find("option:selected").val();
        if (typeof(id) === "undefined" || typeof(state) === "undefined") {
            return;
        }
        getShopeeBranchCities(id, state);
    });

    $(document).on('change', '#dropoff_shopee_branch_cities', function(e) {
        var id = $("#selected_order_id").val();
        var state = $("#dropoff_shopee_branch_states").find("option:selected").val();
        var city = $(this).find("option:selected").val();
        if (typeof(id) === "undefined" || typeof(state) === "undefined" || typeof(city) === "undefined") {
            return;
        }
        getShopeeBranch(id, state, city);
    });

    $('#update_dropoff_info_btn').on('click', function() {
        var id = $("#selected_order_id").val();
        var val = $("#dropoff_shopee_param_option").find("option:selected").val();
        if (typeof(id) !== "undefined" && typeof(val) !== "undefined") {
            let branch_id = "";
            let tracking_no = "";
            let sender_real_name = "";
            if (val === "branch_id") {
                branch_id = $("#dropoff_shopee_branches").find("option:selected").val();
                if (typeof(branch_id) === "undefined" || branch_id === "") {
                    $("#dropoff_confirmation_message").html('<div class="alert alert-danger" role="alert">Select a branch first.</div>');
                    return;
                }
            } else if (val === "tracking_no") {
                tracking_no = $("#dropoff_shopee_tracking_no").val();
                if (typeof(tracking_no) === "undefined" || tracking_no === "") {
                    $("#dropoff_confirmation_message").html('<div class="alert alert-danger" role="alert">Enter a valid tracking number.</div>');
                    return;
                }
            }  else if (val === "sender_real_name") {
                sender_real_name = $("#dropoff_shopee_sender_real_name").val();
                if (typeof(sender_real_name) === "undefined" || sender_real_name === "") {
                    $("#dropoff_confirmation_message").html('<div class="alert alert-danger" role="alert">Enter valid sender real name.</div>');
                    return;
                }
            } 
            $("#update_dropoff_info_btn").prop("disabled", true);
            setShopeeLogisticInfo(id, "", "", branch_id, tracking_no, sender_real_name, "dropoff");
        }
    });

    function getShopeePickupAddress(id) {
        $.ajax({
            url: '{{ route("shopee.order.get_pickup_address") }}',
            type: "POST",
            data: {
                'id': id,
                '_token': $('meta[name=csrf-token]').attr('content')
            }
        }).done(function(response) {
            var html = "<option value='-1'>Select an address</option>";
            var address_selected = -1;
            if (typeof(response.data) !== "undefined") {
                response.data.forEach(function(address, index) {
                    is_selected = "";
                    if (address_selected === -1) {
                        address_selected = address.address_id;
                        $("#pickup_time").html("<option value='-1'>Loading...</option>");
                        setTimeout(function() {
                            getShopeePickupTimeSlot(id, address.address_id);
                        }, 2000);
                        is_selected = "selected";
                    }
                    html += "<option value='"+address.address_id+"' "+is_selected+">"+address.address+", "+address.city+"</option>";
                });
            }
            $("#pickup_address").html(html);
        });
    }

    $(document).on('change', '#pickup_address', function(e) {
        var id = $("#selected_order_id").val();
        var address_id = $(this).find("option:selected").val();
        if (typeof(id) === "undefined" || typeof(address_id) === "undefined") {
            return;
        }
        if (address_id == -1) {
            return;
        }
        $("#pickup_time").html("<option value='-1'>Loading...</option>");
        getShopeePickupTimeSlot(id, address_id);
    });

    function getShopeePickupTimeSlot(id, address_id) {
        $.ajax({
            url: '{{ route("shopee.order.get_pickup_time_slot") }}',
            type: "POST",
            data: {
                'id': id,
                'address_id': address_id,
                '_token': $('meta[name=csrf-token]').attr('content')
            }
        }).done(function(response) {
            var html = "<option value='-1'>Select a date</option>";
            if (typeof(response.data) !== "undefined") {
                response.data.forEach(function(time, index) {
                    html += "<option value='"+time.id+"'>";
                    if (typeof(time.time_text) !== "undefined" && time.time_text.length !== 0) {
                        html += time.date+" ("+time.time_text+")</option>";
                    } else {
                        html += time.date+"</option>";
                    }
                });
                $("#update_pickup_info_btn").prop("disabled", false);
            }
            $("#pickup_time").html(html);
        });
    }

    function getShopeeBranch(id, state, city) {
        $.ajax({
            url: '{{ route("shopee.order.get_branch_info") }}',
            type: "POST",
            data: {
                'id': id,
                'state': state,
                'city': city,
                '_token': $('meta[name=csrf-token]').attr('content')
            }
        }).done(function(response) {
            var branch_html = "<option class='shopee_branch_li' value=''>Select a branch</option>";
            if (typeof(response.data) !== "undefined") {
                response.data.forEach(function(branch, index) {
                    if (typeof(branch.branch_id) !== "undefined" && typeof(branch.address) !== "undefined" && branch.address !== "") {
                        branch_html += "<option class='shopee_branch_li' value='"+branch.branch_id+"'>"+branch.address+"</option>";
                    }
                });
                $("#update_pickup_info_btn").prop("disabled", false);
            }
            $("#dropoff_shopee_branches").html(branch_html);
        });
    }

    function getShopeeBranchStates(id) {
        $.ajax({
            url: '{{ route("shopee.order.get_branch_states_info") }}',
            type: "POST",
            data: {
                'id': id,
                '_token': $('meta[name=csrf-token]').attr('content')
            }
        }).done(function(response) {
            var state_html = "<option class='shopee_branch_city_li' value=''>Select a state</option>";
            if (typeof(response.data) !== "undefined") {
                response.data.forEach(function(state, index) {
                    if (typeof(state.state) !== "undefined" && state.state !== "") {
                        state_html += "<option class='shopee_branch_state_li' value='"+state.state+"'>"+state.state+"</option>";
                    }
                });
            }
            $("#dropoff_shopee_branch_states").html(state_html);
        });
    }

    function getShopeeBranchCities(id, state) {
        $.ajax({
            url: '{{ route("shopee.order.get_branch_cities_info") }}',
            type: "POST",
            data: {
                'id': id,
                'state': state,
                '_token': $('meta[name=csrf-token]').attr('content')
            }
        }).done(function(response) {
            var city_html = "<option class='shopee_branch_city_li' value=''>Select a city</option>";
            if (typeof(response.data) !== "undefined") {
                response.data.forEach(function(city, index) {
                    if (typeof(city.city) !== "undefined" && city.city !== "") {
                        city_html += "<option class='shopee_branch_city_li' value='"+city.city+"'>"+city.city+"</option>";
                    }
                });
            }
            $("#dropoff_shopee_branch_cities").html(city_html);
        });
    }

    function setShopeeLogisticInfo(id, address_id, time_id, branch_id, tracking_no, sender_real_name, shipment_method) {
        let data;
        if (shipment_method==="pickup") {
            data = {
                'id': id,
                'address_id': address_id,
                'time_id': time_id,
                'time_text': $("#pickup_time").find(":selected").text(),
                'shipping_method': shipment_method,
                '_token': $('meta[name=csrf-token]').attr('content')
            };
        } else if (shipment_method==="dropoff") {
            data = {
                'id': id,
                'branch_id': branch_id,
                'tracking_no': tracking_no,
                'sender_real_name': sender_real_name,
                'shipping_method': shipment_method,
                '_token': $('meta[name=csrf-token]').attr('content')
            };
        } else {
            return;
        }
        $.ajax({
            url: '{{ route("shopee.order.set_logistic_info") }}',
            type: "POST",
            data: data
        }).done(function(response) {
            if (typeof(response.data.order_id) !== "undefined" && response.data.order_id !== "") {
                $(".btn_arrange_shipment_"+response.data.order_id).closest("tr").remove();
                reloadOrderStatusListForShopee();
                updateLocalStorageProcessingNowShopeeOrderInfo([response.data.order_id]);
            }
            if (shipment_method==="pickup") {
                $("#update_pickup_info_btn").prop("disabled", false);
                if (response.success) {
                    $('#pickup_confirmation_message').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                    setTimeout(function() {
                        $('.__btnCloseModalHandleOrderShipment').click();
                    }, 2000);
                } else {
                    $('#pickup_confirmation_message').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
                }
            } else if (shipment_method==="dropoff") {
                $("#update_dropoff_info_btn").prop("disabled", false);
                if (response.success) {
                    $('#dropoff_confirmation_message').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                    setTimeout(function() {
                        $('.__btnCloseModalHandleOrderShipment').click();
                    }, 2000);
                } else {
                    $('#dropoff_confirmation_message').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
                }
            }
        });
    }

    $('#update_pickup_info_btn').on('click', function() {
        var id = $("#selected_order_id").val();
        var address_id = $("#pickup_address").find("option:selected").val();
        var time_id = $("#pickup_time").find("option:selected").val();;
        if (typeof(id) !== "undefined" || typeof(address_id) !== "undefined" || typeof(time_id) !== "undefined") {
            $("#update_pickup_info_btn").prop("disabled", false);
            setShopeeLogisticInfo(id, address_id, time_id, "", "", "", "pickup");
        }
    });

    /* Keep track of processing now shopee orders. */
    const processing_now_orders_key = "processing_now_shopee_orders";
    const processing_now_started_at_key = "processing_now_shopee_orders_started_at";
    const updateLocalStorageProcessingNowShopeeOrderInfo = (new_orders=[]) => {
        checkPassedTimeOfStoringProcessedOrderInfoInStorage();

        if (!localStorage.hasOwnProperty(processing_now_started_at_key)) {
            localStorage.setItem(processing_now_started_at_key, new Date());
        }

        let orders = [];
        if (localStorage.hasOwnProperty('processing_now_shopee_orders')) {
            orders = JSON.parse(localStorage.getItem("processing_now_shopee_orders"));
            $.each(new_orders, function(index, order_id) {
                orders.push(order_id);
            })
        } else {
            orders = new_orders;
        }
        localStorage.setItem("processing_now_shopee_orders", JSON.stringify(orders));
    }

    const removeArrangeShipmentForProcessingNowShopeeOrders = () => {
        if (localStorage.hasOwnProperty('processing_now_shopee_orders')) {
            orders = JSON.parse(localStorage.getItem("processing_now_shopee_orders"));
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
        if (localStorage.hasOwnProperty('processing_now_shopee_orders')) {
            localStorage.removeItem('processing_now_shopee_orders');
        }
    }
</script>
@endpush