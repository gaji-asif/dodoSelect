<x-modal.modal-large id="__modalArrangeBatchOrderShipment">
    <x-modal.header>
        <x-modal.title>
            Arrange Order Shipment In Batch
        </x-modal.title>
        <x-modal.close-button class="__btnCloseModalHandleBatchOrderShipment" />
    </x-modal.header>
    <x-modal.body>
        <div class="w-full overflow-x-auto mb-10" id="div_delivery_method_oaib">
            <div class="mb-2">

                <div class="grid grid-cols-2 gap-4" id="div_shopee_shipment_shop_filter">
                    <div class="pt-3">
                        <strong class="text-blue-500">
                            Shop <span id="modal_shopee_selected_orders_count"></span>
                        </strong>
                    </div>
                    <div>
                        <x-select id="shopee_shop_oaib" style="width: 100%; margin-bottom: 15px;" class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 js-example-basic-single3" required>
                            <option value="">{{__("shopee.please_select_a_shop")}}</option>
                            @if (isset($shops))
                                @foreach ($shops as $shop)
                                    <option data-site_url="{{$shop->site_url}}" data-shopee_shop_id="{{$shop->shop_id}}" data-code="{{$shop->code}}" value="{{$shop->id}}">{{$shop->shop_name}}</option>
                                @endforeach
                            @endif
                        </x-select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-4">
                    <p class="pt-2" id="shipment_method_basic_info_oaib">Select one of the following shipping methods</p>
                    <input type="hidden" id="selected_order_id">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-4 text-center pt-6 pb-6 card card-option-oaib" data-shipment_method="dropoff">
                        <strong class="text-blue-500">
                            Drop-off at post office
                        </strong>
                    </div>
                    <div class="col-span-4 text-center pt-6 pb-6 card card-option-oaib" data-shipment_method="pickup">
                        <strong class="text-blue-500">
                            Pick-up at delivery address
                        </strong>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <p id="oaib_selected_shipment_message" class="pt-4"></p>
                </div>
                <div class="grid grid-cols-1 gap-4 text-center">
                    <button class="btn-action--blue sm:w-1/4 mx-auto" id="select_shipment_method_oaib_btn" disabled>Confirm</button>
                </div>
            </div>
        </div>

        <div class="w-full overflow-x-auto mb-10 hide" id="div_pickup_confirmation_oaib">
            <div class="grid grid-cols-2 gap-4">
                <div class="pt-3">
                    <strong class="text-blue-500">
                        Address
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="pickup_address_oaib"></x-select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-2">
                <div class="pt-3">
                    <strong class="text-blue-500">
                        Time Slot
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="pickup_time_oaib"></x-select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <p id="pickup_confirmation_message_oaib" class="pt-4"></p>
                <button class="btn-action--blue sm:w-1/4 mx-auto" id="update_pickup_info_oaib_btn" disabled>Confirm</button>
            </div>
        </div>

        <div class="w-full overflow-x-auto mb-10 hide" id="div_dropoff_confirmation_branch_oaib">
            <div class="grid grid-cols-2 gap-4 mt-2 dropoff_shopee_param_option_oaib__inputs" id="dropoff_shopee_param_option_oaib__branch_id">
                <div class="pt-3">
                    <strong class="text-blue-500">
                        State
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="dropoff_shopee_branch_states_oaib"></x-select>
                </div>

                <div class="pt-3">
                    <strong class="text-blue-500">
                        City
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="dropoff_shopee_branch_cities_oaib"></x-select>
                </div>

                <div class="pt-3">
                    <strong class="text-blue-500">
                        Branches
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="dropoff_shopee_branches_oaib"></x-select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <p id="dropoff_confirmation_message_branch_oaib" class="pt-4"></p>
                <button class="btn-action--blue sm:w-1/4 mx-auto" id="update_dropoff_branch_info_oaib_btn" disabled>Confirm</button>
            </div>
        </div>


        <div class="w-full overflow-x-auto mb-10 hide" id="div_dropoff_confirmation_tracking_no_oaib">
        </div>
    </x-modal.body>
</x-modal.modal-large>


@push('bottom_js')
<script>
    let oaib_selected_rows = [];
    let oaib_shipping_method = "";
    $(document).on('click', '#btn_batch_init_selected_order', function(e) {
        $("#modal_shopee_selected_orders_count").html("");
        oaib_shipping_method = $('#shopee_shipment_method_filter').find("option:selected").val();
        if (typeof(oaib_shipping_method) === "undefined" || oaib_shipping_method === "") {
            alert("Please select a shipping method");
            return;
        }
        oaib_selected_rows = []
        var rows_selected = shopeeOrderPurchaseTable.column(0).checkboxes.selected();
        $.each(rows_selected, function(index, row_id) {
            oaib_selected_rows[index] = row_id;
        });
        if (oaib_selected_rows.length === 0) {
            alert("Please Select At Least 1 Row");
            return;
        }
        $("#modal_shopee_selected_orders_count").html("( Selected Orders : "+oaib_selected_rows.length+" )");

        oaibHandleArrangeBatchOrderCardOption();
        $('#__modalArrangeBatchOrderShipment').doModal('open');
    });

    const oaibHandleArrangeBatchOrderCardOption = () => {
        $(".card").each(function() {
            $(this).removeClass("card-active");
        });
        if (oaib_shipping_method === "pickup") {
            $(".card-option-oaib:last-child").addClass("card-active");
            if ($("#div_shopee_shipment_shop_filter").hasClass("hide")) {
                $("#div_shopee_shipment_shop_filter").removeClass("hide");
            }
        } else if (oaib_shipping_method === "dropoff_branch_id" || oaib_shipping_method === "dropoff_tracking_no") {
            $(".card-option-oaib:first-child").addClass("card-active");
            if (!$("#div_shopee_shipment_shop_filter").hasClass("hide")) {
                $("#div_shopee_shipment_shop_filter").addClass("hide");
            }
        } else {
            return;
        }
        $("#select_shipment_method_oaib_btn").prop("disabled", false);
    }

    /* Arrange Shipment Order */
    let oaid_ordersn_list = [];
    let oaib_dropoff_tracking_number_list = [];
    let oaib_method;
    $(".card-option-oaib").on("click", function() {
        return;

        oaib_method = $(this).data("shipment_method");
        if (typeof(oaib_method) === "undefined" || oaib_method === "") {
            retrun;
        }
        $(".card").each(function() {
            $(this).removeClass("card-active");
        });
        var basic_info = "";
        var message = "";
        if (oaib_method=="dropoff") {
            basic_info = "Drop-off basic info goes here.";
        } else {
            basic_info = "Pick-up basic info goes here.";
        }
        $(this).addClass("card-active");
        $("#shipment_method_basic_info_oaib").html(basic_info);
        $("#select_shipment_method_oaib_btn").prop("disabled", true);
        $("#validate_shipment_method_oaib_btn").prop("disabled", false);
    });

    $('.__btnCloseModalHandleBatchOrderShipment').on('click', function() {
        $('#__modalArrangeBatchOrderShipment').doModal('close');
        resetHtmlForModalHandleBatchOrderShipment();
    });

    const resetHtmlForModalHandleBatchOrderShipment = () => {
        $(".card").each(function() {
            $(this).removeClass("card-active");
        });
        $("#shipment_method_basic_info_oaib").html("Select one of the following shipping methods");
        $("#oaib_selected_shipment_message").html("");
        $("#select_shipment_method_oaib_btn").prop("disabled", true);
        $("#validate_shipment_method_oaib_btn").prop("disabled", true);
        $("#div_delivery_method_oaib").removeClass("hide");

        $("#div_pickup_confirmation_oaib").addClass("hide");
        $("#update_pickup_info_oaib_btn").prop("disabled", false);
        $("#pickup_address_oaib").html("");
        $("#pickup_time_oaib").html("");
        $('#pickup_confirmation_message_oaib').html("");
        $("#shopee_shop_oaib").prop("selectedIndex", 0);

        $("#div_dropoff_confirmation_branch_oaib").addClass("hide"); 
        $("#div_dropoff_confirmation_tracking_no_oaib").addClass("hide"); 
        $(".dropoff_shopee_param_option_oaib__inputs").each(function() {
            $(this).addClass("hide");
        });
        $("#dropoff_shopee_param_option_oaib__branch_id").removeClass("hide");
        $("#update_dropoff_branch_info_oaib_btn").prop("disabled", false);
        $('#dropoff_shopee_param_option_oaib').prop("selectedIndex", 0);
        $('#dropoff_shopee_branch_states_oaib').prop("selectedIndex", 0);
        $("#dropoff_shopee_branch_cities_oaib").html("");
        $("#dropoff_shopee_branches_oaib").html("");
        $("#dropoff_shopee_tracking_no_oaib").val("");
        $("#dropoff_shopee_sender_real_name_oaib").val("");
        $('#dropoff_confirmation_message_branch_oaib').html("");
        $("#div_dropoff_confirmation_tracking_no_oaib").html("");
    }

    $("#validate_shipment_method_oaib_btn").on("click", function() {
        var shop_id = $("#shopee_shop_oaib").find("option:selected").val();
        if (typeof(shop_id) === "undefined" || shop_id.length === 0) {
            alert("Select a shop first");
            return;
        }
        if (oaib_method=="pickup") {
            oaibValidateShopeeLogisticInfo("pickup");
        } else if (oaib_method=="dropoff") {
            let shipping_method = $("#shopee_shipment_method_filter").find("option:selected").val();
            if (["dropoff_branch_id", "dropoff_tracking_no"].includes(shipping_method)) {
                oaibValidateShopeeLogisticInfo(shipping_method);
            } else {
                alert("Invlaid shipping method.");
            }
        } else {
            alert("Select a method first.");
        }
    });

    $("#select_shipment_method_oaib_btn").on("click", function() {
        var shop_id = $("#shopee_shop_oaib").find("option:selected").val();
        if (typeof(oaib_shipping_method) === "undefined" || oaib_shipping_method === "") {
            alert("Invalid shipping method");
            return;
        }
        if (oaib_shipping_method=="pickup") {
            if (typeof(shop_id) === "undefined" || shop_id.length === 0) {
                alert("Select a shop first");
                return;
            }
            $("#div_delivery_method_oaib").addClass("hide");
            $("#div_pickup_confirmation_oaib").removeClass("hide");
            if (typeof(shop_id) === "undefined") {
                return;
            }
            $("#pickup_address_oaib").html("<option value='-1'>Loading...</option>");
            oaibGetShopeePickupAddress(shop_id);
        } else if (oaib_shipping_method === "dropoff_branch_id") {
            $("#div_delivery_method_oaib").addClass("hide");
            $("#div_dropoff_confirmation_tracking_no_oaib").addClass("hide");
            $("#div_dropoff_confirmation_branch_oaib").removeClass("hide");
            var id = $("#selected_order_id").val();
            $("#dropoff_shopee_branches_oaibs").html("<option value='-1'>Loading...</option>");

            var state = $("#dropoff_shopee_branch_states_oaib").find("option:selected").val();
            if (typeof(state) === "undefined") {
                state = "";
            }
            var city = $("#dropoff_shopee_branch_cities_oaib").find("option:selected").val();
            if (typeof(city) === "undefined") {
                city = "";
            }
            oaibGetShopeeBranchStates(id);
            $("#update_dropoff_branch_info_oaib_btn").prop("disabled", false);
        } else if (oaib_shipping_method === "dropoff_tracking_no") {
            $("#div_delivery_method_oaib").addClass("hide");
            $("#div_dropoff_confirmation_branch_oaib").addClass("hide");
            $("#div_dropoff_confirmation_tracking_no_oaib").removeClass("hide");

            oaib_ordersn_list = [];
            $.each(oaib_selected_rows, function(index, order_info) {
                oaib_ordersn_list.push(order_info.split("*")[2]); 
            });
            $("#div_dropoff_confirmation_tracking_no_oaib").html(getTrackingNumberFormForBatchInit(oaib_ordersn_list));
        }
    });

    function getTrackingNumberFormForBatchInit($order_list) {
        if ($order_list.length < 0) {
            return "";
        }
        var shop = $("#shopee_shop_oaib").find("option:selected").text();
        let html = '<div class="w-full dropoff_shopee_param_option_oaib__inputs">';
        $.each($order_list, function(index, order) {
            let d_html = '<div class="grid grid-cols-2 gap-4 mt-2 pt-3">';
            let customer_name = $("#cn_"+order).html();
            if (typeof(customer_name) === "undefined") {
                customer_name = "";
            }
            d_html += '<div>';
            d_html += '<strong class="text-blue-500">';
            d_html += '<span>Order no. '+order+'</span>';
            d_html += '</strong><br/>';
            d_html += '<span>Customer name : '+customer_name+'</span><br/>';
            d_html += '<span>'+shop+'</span>';
            d_html += '<input type="hidden" value="'+order+'" class="dropoff_confirmation_tn_ordersn_oaib" id="dropoff_confirmation_tn_ordersn_oaib__'+order+'" required/>';
            d_html += '</div>';
            d_html += '<div>';
            d_html += '<strong class="text-gray-900">Tracking No.</strong>';
            d_html += '<input type="text" value="" class="dropoff_confirmation_tn_tracking_no_oaib" id="dropoff_confirmation_tn_tracking_no_oaib__'+order+'" required/>';
            d_html += '<div id="div_dropoff_confirmation_tracking_no__'+order+'" class="oaib_dropoff_tn_error_message"></div>';
            d_html += '</div>';
            d_html += '</div>';
            d_html += '<div class="grid grid-cols-1 gap-4"><hr/></div>';

            html += d_html;
        });
        html += '</div>';

        html += '<div class="grid grid-cols-1 gap-4">';
        html += '<p id="dropoff_confirmation_message_tracking_no_oaib" class="pt-4"></p>';
        html += '<button class="btn-action--blue sm:w-1/4 mx-auto" id="update_dropoff_tracking_no_info_oaib_btn">Confirm</button>';
        html += '</div>';
        return html;
    }

    $(document).on('change', '#dropoff_shopee_param_option_oaib', function(e) {
        var id = $("#selected_order_id").val();
        var val = $(this).find("option:selected").val();
        if (typeof(id) === "undefined" || typeof(val) === "undefined") {
            return;
        }

        $(".dropoff_shopee_param_option_oaib__inputs").each(function() {
            $(this).addClass("hide");
        });
        if (val === "branch_id") {
            $("#dropoff_shopee_param_option_oaib__branch_id").removeClass("hide");
        }
    });

    $(document).on('change', '#dropoff_shopee_branch_states_oaib', function(e) {
        var id = $("#selected_order_id").val();
        var state = $(this).find("option:selected").val();
        if (typeof(id) === "undefined" || typeof(state) === "undefined") {
            return;
        }
        oaibGetShopeeBranchCities(id, state);
    });

    $(document).on('change', '#dropoff_shopee_branch_cities_oaib', function(e) {
        var id = $("#selected_order_id").val();
        var state = $("#dropoff_shopee_branch_states_oaib").find("option:selected").val();
        var city = $(this).find("option:selected").val();
        if (typeof(id) === "undefined" || typeof(state) === "undefined" || typeof(city) === "undefined") {
            return;
        }
        oaibGetShopeeBranch(id, state, city);
    });

    $('#update_dropoff_branch_info_oaib_btn').on('click', function() {
        var shop_id = $("#shopee_shop_oaib").find("option:selected").data("shopee_shop_id");
        branch_id = $("#dropoff_shopee_branches_oaib").find("option:selected").val();
        if (typeof(branch_id) === "undefined" || branch_id === "") {
            $("#dropoff_confirmation_message_branch_oaib").html('<div class="alert alert-danger" role="alert">Select a branch first.</div>');
            return;
        }
        $("#dropoff_confirmation_message_branch_oaib").html('');
        $("#update_dropoff_branch_info_oaib_btn").prop("disabled", true);
        oaibSetShopeeLogisticInfo(shop_id, "", "", branch_id, [], "dropoff_branch_id");
    });

    $(document).on('click', '#update_dropoff_tracking_no_info_oaib_btn', function() {
        var shop_id = $("#shopee_shop_oaib").find("option:selected").data("shopee_shop_id");
        // var val = $("#dropoff_shopee_param_option_oaib").find("option:selected").val();
        // if (typeof(shop_id) !== "undefined" && typeof(val) !== "undefined") {
        if (typeof(shop_id) !== "undefined") {
            let has_missing_tracking_no = false;
            oaib_dropoff_tracking_number_list = {};
            $.each(oaib_ordersn_list, function(index, value) {
                let tracking_number = $("#dropoff_confirmation_tn_tracking_no_oaib__"+value).val();
                if(typeof (tracking_number) !== "undefined") {
                    if (tracking_number !== "" || tracking_number.length >= 6) {
                        oaib_dropoff_tracking_number_list[value] = tracking_number;
                        $("#div_dropoff_confirmation_tracking_no__"+value).html("");
                    } else {
                        has_missing_tracking_no = true;
                        $("#div_dropoff_confirmation_tracking_no__"+value).html("<p class='alert alert-danger mt-2'>Missing Tracking Number</p>");
                    }
                }
            });
            if (has_missing_tracking_no) {
                return;
            }
            $("#update_dropoff_tracking_no_info_oaib_btn").prop("disabled", true);
            oaibSetShopeeLogisticInfo(shop_id, "", "", "", oaib_dropoff_tracking_number_list, "dropoff_tracking_no");
        }
    });

    function oaibGetShopeePickupAddress(shop_id) {
        $.ajax({
            url: '{{ route("shopee.order.get_pickup_address") }}',
            type: "POST",
            data: {
                'shop_id': shop_id,
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
                        $("#pickup_time_oaib").html("<option value='-1'>Loading...</option>");
                        is_selected = "selected";
                        setTimeout(function() {
                            oaibGetShopeePickupTimeSlot();
                        }, 2000);
                    }
                    html += "<option value='"+address.address_id+"' "+is_selected+">"+address.address+", "+address.city+"</option>";
                });
            }
            $("#pickup_address_oaib").html(html);
        });
    }

    $(document).on('change', '#pickup_address_oaib', function(e) {
        var address_id = $(this).find("option:selected").val();
        if (address_id == -1) {
            return;
        }
        oaibGetShopeePickupTimeSlot();
    });

    function oaibGetFirstSelectedOrder() {
        if (oaib_selected_rows.length == 0) {
            return null;
        }
        return oaib_selected_rows[0];
    }

    function oaibGetShopeePickupTimeSlot() {
        var address_id = $("#pickup_address_oaib").find("option:selected").val();
        if (address_id == -1) {
            return;
        }
        var order = oaibGetFirstSelectedOrder();
        if (order === null) {
            return;
        }
        order = order.split("*");
        var shop_id = order[0];
        var ordersn = order[2];
        $("#pickup_time_oaib").html("<option value='-1'>Loading...</option>");
        $.ajax({
            url: '{{ route("shopee.order.get_pickup_time_slot") }}',
            type: "POST",
            data: {
                'ordersn': ordersn,
                'shop_id': shop_id,
                'address_id': address_id,
                '_token': $('meta[name=csrf-token]').attr('content')
            }
        }).done(function(response) {
            if (!response.success) {
                var address_id = $("#pickup_address_oaib").find("option:selected").val();
                if (address_id == -1) {
                    return;
                }
                oaibGetShopeePickupTimeSlot();
                return;
            }
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
                $("#update_pickup_info_oaib_btn").prop("disabled", false);
            }
            $("#pickup_time_oaib").html(html);
        });
    }

    function oaibGetShopeeBranch(id, state, city) {
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
                $("#update_pickup_info_oaib_btn").prop("disabled", false);
            }
            $("#dropoff_shopee_branches_oaib").html(branch_html);
        });
    }

    function oaibGetShopeeBranchStates(id) {
        var shopee_shop_id = $("#shopee_shop_oaib").find("option:selected").data("shopee_shop_id");
        $.ajax({
            url: '{{ route("shopee.order.get_branch_states_info") }}',
            type: "POST",
            data: {
                'shopee_shop_id': shopee_shop_id,
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
            $("#dropoff_shopee_branch_states_oaib").html(state_html);
        });
    }

    function oaibGetShopeeBranchCities(id, state) {
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
            $("#dropoff_shopee_branch_cities_oaib").html(city_html);
        });
    }

    function oaibValidateShopeeLogisticInfo(shipment_method) {
        let json_data = JSON.stringify(oaib_selected_rows);
        var shop_id = $("#shopee_shop_oaib").find("option:selected").data("shopee_shop_id");
        if (typeof(shop_id) === "undefined" || shop_id.length === 0) {
            return;
        }
        if (!["pickup", "dropoff_branch_id", "dropoff_tracking_no"].includes(shipment_method)) {
            return;
        }
        $("#validate_shipment_method_oaib_btn").prop("disabled", true);
        $("#select_shipment_method_oaib_btn").prop("disabled", true);
        
        $.ajax({
            url: '{{ route("shopee.order.validate_orders_batch_logistic_info") }}',
            type: "POST",
            data: {
                'shopee_shop_id': shop_id,
                'json_data': json_data,
                'shipping_method': shipment_method,
                '_token': $('meta[name=csrf-token]').attr('content')
            }
        }).done(function(response) {
            $("#validate_shipment_method_oaib_btn").prop("disabled", false);
            $('#oaib_selected_shipment_message').html("");
            if (response.success) {
                if (typeof(response.message) !== "undefined") {
                    $('#oaib_selected_shipment_message').append('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                }
            } else {
                if (typeof(response.message) !== "undefined") {
                    $('#oaib_selected_shipment_message').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
                }
            }
        });
    }

    function oaibSetShopeeLogisticInfo(shop_id, address_id, time_id, branch_id, tracking_nums=[], shipment_method="pickup") {
        let data;
        let json_data = JSON.stringify(oaib_selected_rows);
        if (shipment_method==="pickup") {
            data = {
                'shopee_shop_id': shop_id,
                'address_id': address_id,
                'time_id': time_id,
                'time_text': $("#pickup_time_oaib").find(":selected").text(),
                'json_data': json_data,
                'shipping_method': shipment_method,
                '_token': $('meta[name=csrf-token]').attr('content')
            };
        } else if (shipment_method==="dropoff_branch_id") {
            data = {
                'branch_id': branch_id,
                'json_data': json_data,
                'shipping_method': shipment_method,
                '_token': $('meta[name=csrf-token]').attr('content')
            };
        } else if (shipment_method==="dropoff_tracking_no") {
            data = {
                'json_data': json_data,
                'tracking_nums': JSON.stringify(tracking_nums),
                'shipping_method': shipment_method,
                '_token': $('meta[name=csrf-token]').attr('content')
            };
        } else {
            return;
        }
        $.ajax({
            url: '{{ route("shopee.order.set_batch_logistic_info") }}',
            type: "POST",
            data: data
        }).done(function(response) {
            if (typeof(response.data) !== "undefined" && typeof(response.data.ordersn_list) !== "undefined") {
                updateLocalStorageProcessingNowShopeeOrderInfo(response.data.ordersn_list);
                setTimeout(function() {
                    removeArrangeShipmentForProcessingNowShopeeOrders();
                }, 2000);
            }
            if (shipment_method==="pickup") {
                $("#update_pickup_info_oaib_btn").prop("disabled", false);
                if (response.success) {
                    $('#pickup_confirmation_message_oaib').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                    setTimeout(function() {
                        $('#__modalArrangeBatchOrderShipment').doModal('close');
                        location.reload();
                    }, 2000);
                } else {
                    $('#pickup_confirmation_message_oaib').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
                }
            } else if (shipment_method==="dropoff_branch_id") {
                $("#update_dropoff_branch_info_oaib_btn").prop("disabled", false);
                if (response.success) {
                    $('#dropoff_confirmation_message_branch_oaib').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                    setTimeout(function() {
                        $('#__modalArrangeBatchOrderShipment').doModal('close');
                        location.reload();
                    }, 2000);
                } else {
                    $('#dropoff_confirmation_message_branch_oaib').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
                }
            } else if (shipment_method==="dropoff_tracking_no") {
                $("#update_dropoff_tracking_no_oaib_btn").prop("disabled", false);
                if (response.success) {
                    $('#dropoff_confirmation_message_tracking_no_oaib').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                    setTimeout(function() {
                        $('#__modalArrangeBatchOrderShipment').doModal('close');
                        location.reload();
                    }, 2000);
                } else {
                    $('#dropoff_confirmation_message_tracking_no_oaib').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
                }
            }
        });
    }

    $('#update_pickup_info_oaib_btn').on('click', function() {
        var shop_id = $("#shopee_shop_oaib").find("option:selected").data("shopee_shop_id");
        var address_id = $("#pickup_address_oaib").find("option:selected").val();
        var time_id = $("#pickup_time_oaib").find("option:selected").val();;
        if (typeof(shop_id) !== "undefined" || typeof(address_id) !== "undefined" || typeof(time_id) !== "undefined") {
            $("#update_pickup_info_oaib_btn").prop("disabled", false);
            oaibSetShopeeLogisticInfo(shop_id, address_id, time_id, "", [], "pickup");
        }
    });
</script>
@endpush