<x-modal.modal-large id="__modalArrangeBatchOrderShipmentMltpShop">
    <x-modal.header>
        <x-modal.title>
            Arrange Order Shipment In Batch
        </x-modal.title>
        <x-modal.close-button class="__btnCloseModalHandleBatchOrderShipmentMltpShop" />
    </x-modal.header>
    <x-modal.body>
        <div class="w-full overflow-x-auto" id="div_delivery_method_oaib">
            <div class="mb-2">
                
                <div class="grid grid-cols-1 gap-4 hide">
                    <p class="pt-2" id="shipment_method_basic_info_oaib">Select one of the following shipping methods</p>
                    <input type="hidden" id="selected_order_id">
                </div>

                <div class="grid grid-cols-2 gap-4 hide">
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
                    <p id="oaib_selected_shipment_mltp_shops_message" class="mb-0"></p>
                </div>
            </div>
        </div>

        <div class="w-full overflow-x-auto mb-10 hide" id="div_pickup_confirmation_oaib_mltp_shop">
            <div class="grid grid-cols-2 gap-4 mt-2 mb-5" id="div_pickup_confirmation_oaib_mltp_shop_timeslot__0"></div>

            <div class="grid grid-cols-1 gap-4">
                <p id="pickup_confirmation_message_oaib_mltp_shop" class="pt-4"></p>
                <button class="btn-action--blue sm:w-1/4 mx-auto" id="update_pickup_info_oaib_mltp_shop_btn" disabled>Confirm</button>
            </div>
        </div>

        <div class="w-full overflow-x-auto mb-10 hide" id="div_dropoff_confirmation_branch_oaib_mltp_shop">
            <div class="grid grid-cols-2 gap-4 mt-2 dropoff_shopee_param_option_oaib__inputs" id="dropoff_shopee_param_option_oaib_mltp_shop__branch_id">
                <div class="pt-3">
                    <strong class="text-blue-500">
                        State
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="dropoff_shopee_branch_states_oaib_mltp_shop"></x-select>
                </div>

                <div class="pt-3">
                    <strong class="text-blue-500">
                        City
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="dropoff_shopee_branch_cities_oaib_mltp_shop"></x-select>
                </div>

                <div class="pt-3">
                    <strong class="text-blue-500">
                        Branches
                    </strong>
                </div>
                <div>
                    <x-select class="text-sm" id="dropoff_shopee_branches_oaib_mltp_shop"></x-select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <p id="dropoff_confirmation_message_branch_oaib_mltp_shop" class="pt-4"></p>
                <button class="btn-action--blue sm:w-1/4 mx-auto" id="update_dropoff_branch_info_oaib_mltp_shop_btn" disabled>Confirm</button>
            </div>
        </div>

        <div class="w-full overflow-x-auto mb-10 hide" id="div_dropoff_confirmation_tracking_no_oaib_mltp_shop">
        </div>
    </x-modal.body>
</x-modal.modal-large>


@push('bottom_js')
<script>
    let oaib_selected_mltp_shopee_shop = [];
    let shopee_shops = {!! json_encode($shops) !!};

    let oaib_selected_rows_mltp_shop = [];
    let oaib_shipping_method_mltp_shop = "";

    let oaib_check_batch_init_processing_interval; 

    $(document).ready(function() {
        $("#btn_batch_init_selected_order_mltp_shop").prop("disabled", true);
        checkIfStillProcessingForBatchInit();
        if (typeof(oaib_check_batch_init_processing_interval) !== "undefined") {
            clearInterval(oaib_check_batch_init_processing_interval);
        }
        oaib_check_batch_init_processing_interval = setInterval(function() {
            checkIfStillProcessingForBatchInit();
        }, 30000);
        
        setTimeout(function() {
            $("#__modalArrangeBatchOrderShipmentMltpShop").children("div").children("div").removeClass("lg:max-w-2xl");
            $("#__modalArrangeBatchOrderShipmentMltpShop").children("div").children("div").addClass("lg:max-w-3xl");
        }, 2500);
    });


    const checkIfStillProcessingForBatchInit = () => {
        $.ajax({
            url: '{{ route("shopee.order.check_processing_batch_init") }}',
            type: "POST",
            data: {
                '_token': $('meta[name=csrf-token]').attr('content')
            }
        }).done(function(response) {
            if (response.success && typeof(response.data.processing) !== "undefined") {
                let target = $("#btn_batch_init_selected_order_mltp_shop");
                if (response.data.processing) {
                    target.prop("disabled", true);
                    target.find("span").text("STILL PROCESSING SHIPMENT");
                } else {
                    target.prop("disabled", false);
                    target.find("span").text("BATCH INIT");
                    // target.find("span").text("BULK ARRANGE SHIPMENT");
                }
            }   
        });
    } 


    $(document).on('click', '#btn_batch_init_selected_order_mltp_shop', function(e) {
        oaib_selected_mltp_shopee_shop = [];
        $(".pickup_time_oaib_mltp_shop").remove();

        $("#oaib_selected_shipment_mltp_shops_message").html("");
        oaib_shipping_method_mltp_shop = $('#shopee_shipment_method_filter').find("option:selected").val();
        if (typeof(oaib_shipping_method_mltp_shop) === "undefined" || oaib_shipping_method_mltp_shop === "") {
            alert("Please select a shipping method");
            return;
        }

        oaib_selected_rows_mltp_shop = []
        var rows_selected = shopeeOrderPurchaseTable.column(0).checkboxes.selected();
        $.each(rows_selected, function(index, row_id) {
            let data = row_id.split("*");
            if (typeof(data[0]) !== "undefined" && !oaib_selected_mltp_shopee_shop.includes(data[0])) {
                oaib_selected_mltp_shopee_shop.push(data[0]);
            }
            oaib_selected_rows_mltp_shop[index] = row_id;
        });

        if (oaib_selected_rows_mltp_shop.length === 0) {
            alert("Please Select At Least 1 Row");
            return;
        }
        $("#oaib_selected_shipment_mltp_shops_message").html("Selected Orders : "+oaib_selected_rows_mltp_shop.length);

        oaibHandleArrangeBatchOrderCardOptionMltpShop();

        $('#__modalArrangeBatchOrderShipmentMltpShop').doModal('open');
    });


    let oaid_ordersn_mltp_shop_list = [];
    let oaib_dropoff_tracking_number_mltp_shop_list = [];
    let oaib_method_mltp_shop;
    const oaibHandleArrangeBatchOrderCardOptionMltpShop = () => {
        $(".card").each(function() {
            $(this).removeClass("card-active");
        });
        
        if (oaib_shipping_method_mltp_shop === "pickup") {
            $(".card-option-oaib:last-child").addClass("card-active");
            $("#div_shopee_shipment_shop_filter_mltp_shop").removeClass("hide");
            $("#div_pickup_confirmation_oaib_mltp_shop").removeClass("hide");
            $("#div_dropoff_confirmation_branch_oaib_mltp_shop").addClass("hide");

            $("#pickup_address_oaib_mltp_shop").html("<option value='-1'>Loading...</option>");

            let html = `<div class="grid grid-cols-2 gap-4 mt-2 pickup_time_oaib_mltp_shop">`;
            $.each(oaib_selected_mltp_shopee_shop, function(index, shop_id) {
                let shop = shopee_shops.filter(shop => shop.id == shop_id);
                
                let shop_name = "";
                if (typeof(shop[0]) !== "undefined" && typeof(shop[0].shop_name) !== "undefined") {
                    shop_name = shop[0].shop_name;
                }

                html += `<div class="grid grid-cols-2 gap-4 mt-2 pickup_time_oaib_mltp_shop card card-option-oaib card-active p-2" id="div_pickup_confirmation_oaib_mltp_shop__`+shop_id+`">
                    <div class="pt-3 col-span-2 text-center">
                        <strong class="text-blue-500">`+shop_name+`</strong>
                        <hr/>
                    </div>
                    <div class="pt-3">
                        <label class="text-blue-500 px-2">Address</label>
                    </div>
                    <div>
                        <x-select class="text-sm pickup_time_oaib_mltp_shop pickup_time_oaib_mltp_shop_address" id="pickup_address_oaib_mltp_shop__`+shop_id+`" data-shop_id="`+shop_id+`"><option value="">Loading ...</option></x-select>
                    </div>
                    <div class="pt-3 pb-5">
                        <label class="text-blue-500 px-2">Time Slot</label>
                    </div>
                    <div>
                        <x-select class="text-sm pickup_time_oaib_mltp_shop" id="pickup_time_oaib_mltp_shop__`+shop_id+`"><option value="">Loading ...</option></x-select>
                    </div>
                    <div class="error_message col-span-2"></div>
                </div>`;
            });
            html += `</div>`;
            $("#div_pickup_confirmation_oaib_mltp_shop_timeslot__0").after(html);
            $.each(oaib_selected_mltp_shopee_shop, function(index, shop_id) {
                setTimeout(function() {
                    oaibGetShopeePickupSpecificAddressSpecificShop(shop_id);
                }, 1000);
            });
        } else if (oaib_shipping_method_mltp_shop === "dropoff_branch_id") {
            $(".card-option-oaib:first-child").addClass("card-active");
            $("#div_shopee_shipment_shop_filter_mltp_shop").addClass("hide");
            $("#div_pickup_confirmation_oaib_mltp_shop").addClass("hide");   
            $("#div_dropoff_confirmation_branch_oaib_mltp_shop").removeClass("hide");
            $("#div_dropoff_confirmation_tracking_no_oaib_mltp_shop").addClass("hide");

            $("#dropoff_shopee_branches_oaib_mltp_shops").html("<option value='-1'>Loading...</option>");

            var state = $("#dropoff_shopee_branch_states_oaib_mltp_shop").find("option:selected").val();
            if (typeof(state) === "undefined") {
                state = "";
            }
            var city = $("#dropoff_shopee_branch_cities_oaib_mltp_shop").find("option:selected").val();
            if (typeof(city) === "undefined") {
                city = "";
            }

            oaibGetShopeeBranchStatesMltpShop();
            $("#update_dropoff_branch_info_oaib_mltp_shop_btn").prop("disabled", false);
        } else if (oaib_shipping_method_mltp_shop === "dropoff_tracking_no") {
            $("#div_delivery_method_oaib").addClass("hide");
            $("#div_dropoff_confirmation_branch_oaib_mltp_shop").addClass("hide");
            $("#div_dropoff_confirmation_tracking_no_oaib_mltp_shop").removeClass("hide");

            oaib_ordersn_list = [];
            $.each(oaib_selected_rows_mltp_shop, function(index, order_info) {
                oaib_ordersn_list.push(order_info.split("*")[2]); 
            });
            $("#div_dropoff_confirmation_tracking_no_oaib_mltp_shop").html(getTrackingNumberFormForBatchInit(oaib_ordersn_list));
            retun;
        } else {
            return;
        }
        $("#select_shipment_method_oaib_mltp_shop_btn").prop("disabled", false);
    }


    $('.__btnCloseModalHandleBatchOrderShipmentMltpShop').on('click', function() {
        $('#__modalArrangeBatchOrderShipmentMltpShop').doModal('close');
        resetHtmlForModalHandleBatchOrderShipmentMltpShop();
    });


    const resetHtmlForModalHandleBatchOrderShipmentMltpShop = () => {
        $(".card").each(function() {
            $(this).removeClass("card-active");
        });
        $("#shipment_method_basic_info_oaib").html("Select one of the following shipping methods");
        $("#oaib_selected_shipment_mltp_shops_message").html("");
        $("#select_shipment_method_oaib_mltp_shop_btn").prop("disabled", true);
        $("#div_delivery_method_oaib").removeClass("hide");

        $("#div_pickup_confirmation_oaib_mltp_shop").addClass("hide");
        $("#update_pickup_info_oaib_mltp_shop_btn").prop("disabled", false);
        $("#pickup_address_oaib_mltp_shop").html("");
        $("#pickup_time_oaib_mltp_shop").html("");
        $('#pickup_confirmation_message_oaib_mltp_shop').html("");
        $("#shopee_shop_oaib_mltp_shop").prop("selectedIndex", 0);

        $("#div_dropoff_confirmation_branch_oaib_mltp_shop").addClass("hide"); 
        $("#div_dropoff_confirmation_tracking_no_oaib_mltp_shop").addClass("hide"); 
        $(".dropoff_shopee_param_option_oaib__inputs").each(function() {
            $(this).addClass("hide");
        });
        $("#dropoff_shopee_param_option_oaib_mltp_shop__branch_id").removeClass("hide");
        $("#update_dropoff_branch_info_oaib_mltp_shop_btn").prop("disabled", false);
        $('#dropoff_shopee_branch_states_oaib_mltp_shop').prop("selectedIndex", 0);
        $("#dropoff_shopee_branch_cities_oaib_mltp_shop").html("");
        $("#dropoff_shopee_branches_oaib_mltp_shop").html("");
        $("#dropoff_shopee_tracking_no_oaib").val("");
        $("#dropoff_shopee_sender_real_name_oaib").val("");
        $('#dropoff_confirmation_message_branch_oaib_mltp_shop').html("");
        $("#div_dropoff_confirmation_tracking_no_oaib_mltp_shop").html("");
    }


    function getTrackingNumberFormForBatchInit($order_list) {
        if ($order_list.length < 0) {
            return "";
        }
        var shop = $("#shopee_shop_oaib_mltp_shop").find("option:selected").text();
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


    $(document).on('change', '#dropoff_shopee_branch_states_oaib_mltp_shop', function(e) {
        var state = $(this).find("option:selected").val();
        if (typeof(state) === "undefined") {
            return;
        }
        oaibGetShopeeBranchCitiesMltpShop(state);
    });

    $(document).on('change', '#dropoff_shopee_branch_cities_oaib_mltp_shop', function(e) {
        var state = $("#dropoff_shopee_branch_states_oaib_mltp_shop").find("option:selected").val();
        var city = $(this).find("option:selected").val();
        if (typeof(state) === "undefined" || typeof(city) === "undefined") {
            return;
        }
        oaibGetShopeeBranchMltpShop(state, city);
    });


    $('#update_dropoff_branch_info_oaib_mltp_shop_btn').on('click', function() {
        var shop_id = $("#shopee_shop_oaib_mltp_shop").find("option:selected").data("shopee_shop_id");
        branch_id = $("#dropoff_shopee_branches_oaib_mltp_shop").find("option:selected").val();
        if (typeof(branch_id) === "undefined" || branch_id === "") {
            $("#dropoff_confirmation_message_branch_oaib_mltp_shop").html('<div class="alert alert-danger" role="alert">Select a branch first.</div>');
            return;
        }
        $("#dropoff_confirmation_message_branch_oaib_mltp_shop").html('');
        $("#update_dropoff_branch_info_oaib_mltp_shop_btn").prop("disabled", true);
        oaibSetShopeeLogisticInfoMltpShop("", "", branch_id, [], "dropoff_branch_id");
    });


    $(document).on('click', '#update_dropoff_tracking_no_info_oaib_btn', function() {
        var shop_id = $("#shopee_shop_oaib_mltp_shop").find("option:selected").data("shopee_shop_id");
        if (typeof(shop_id) !== "undefined") {
            let has_missing_tracking_no = false;
            oaib_dropoff_tracking_number_mltp_shop_list = {};
            $.each(oaib_ordersn_list, function(index, value) {
                let tracking_number = $("#dropoff_confirmation_tn_tracking_no_oaib__"+value).val();
                if(typeof (tracking_number) !== "undefined") {
                    if (tracking_number !== "" || tracking_number.length >= 6) {
                        oaib_dropoff_tracking_number_mltp_shop_list[value] = tracking_number;
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
            oaibSetShopeeLogisticInfoMltpShop("", "", "", oaib_dropoff_tracking_number_mltp_shop_list, "dropoff_tracking_no");
        }
    });


    function oaibGetShopeePickupAddressMltpShop(shop_id) {
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
                        $("#pickup_time_oaib_mltp_shop__"+shop_id).html("<option value='-1'>Loading...</option>");
                        is_selected = "selected";
                    }
                    html += "<option value='"+address.address_id+"' "+is_selected+">"+address.address+", "+address.city+"</option>";
                });
                
                $.each(oaib_selected_mltp_shopee_shop, function(index, shop_id) {
                    setTimeout(function() {
                        oaibGetShopeePickupTimeSlotMltpShop(shop_id);
                    }, 1000);
                });
            }
            $("#pickup_address_oaib_mltp_shop").html(html);
        });
    }


    
    function oaibGetShopeePickupSpecificAddressSpecificShop(shop_id) {
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
                        // address_selected = address.address_id;
                        // $("#pickup_time_oaib_mltp_shop__"+shop_id).html("<option value='-1'>Loading...</option>");
                        $("#pickup_time_oaib_mltp_shop__"+shop_id).html("<option value='-1'>Loading...</option>");
                        is_selected = "selected";
                        oaibGetShopeePickupSpecificTimeSlotMltpShop(shop_id)
                    }
                    html += "<option value='"+address.address_id+"' "+is_selected+">"+address.address+", "+address.city+"</option>";
                });
            }
            $("#pickup_address_oaib_mltp_shop__"+shop_id).html(html);
        });
    }


    $(document).on('change', '#pickup_address_oaib_mltp_shop', function(e) {
        var address_id = $(this).find("option:selected").val();
        if (address_id == -1) {
            return;
        }
        $.each(oaib_selected_mltp_shopee_shop, function(index, shop_id) {
            setTimeout(function() {
                oaibGetShopeePickupTimeSlotMltpShop(shop_id);
            }, 1000);
        });
    });


    function oaibGetFirstSelectedOrderMltpShop(shop_id) {
        if (oaib_selected_rows_mltp_shop.length == 0) {
            return null;
        }
        let matched_orders = [];
        $.each(oaib_selected_rows_mltp_shop, function(index, order_info) {
            let data = order_info.split("*");
            if (parseInt(data[0]) === parseInt(shop_id)) {
                matched_orders.push(order_info);
            }
        });

        if (typeof(matched_orders[0]) !== "undefined") {
            return matched_orders[0];
        }
        return null;
    }


    function oaibGetShopeePickupTimeSlotMltpShop(shop_id) {
        var address_id = $("#pickup_address_oaib_mltp_shop").find("option:selected").val();
        if (address_id == -1) {
            return;
        }
        var order = oaibGetFirstSelectedOrderMltpShop(shop_id);
        if (order === null) {
            return;
        }
        order = order.split("*");
        var ordersn = order[2];
        $("#pickup_time_oaib_mltp_shop__"+shop_id).html("<option value='-1'>Loading...</option>");
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
                var address_id = $("#pickup_address_oaib_mltp_shop").find("option:selected").val();
                if (address_id == -1) {
                    return;
                }
                oaibGetShopeePickupTimeSlotMltpShop(shop_id);
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
                $("#update_pickup_info_oaib_mltp_shop_btn").prop("disabled", false);
            }
            $("#pickup_time_oaib_mltp_shop__"+shop_id).html(html);
        });
    }


    function oaibGetShopeePickupSpecificTimeSlotMltpShop(shop_id) {
        var address_id = $("#pickup_address_oaib_mltp_shop__"+shop_id).find("option:selected").val();
        if (address_id == -1) {
            return;
        }
        var order = oaibGetFirstSelectedOrderMltpShop(shop_id);
        if (order === null) {
            return;
        }
        order = order.split("*");
        var ordersn = order[2];
        $("#pickup_time_oaib_mltp_shop__"+shop_id).html("<option value='-1'>Loading...</option>");
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
                var address_id = $("#pickup_address_oaib_mltp_shop__"+shop_id).find("option:selected").val();
                if (address_id == -1) {
                    return;
                }
                oaibGetShopeePickupSpecificTimeSlotMltpShop(shop_id);
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
                $("#update_pickup_info_oaib_mltp_shop_btn").prop("disabled", false);
            }
            $("#pickup_time_oaib_mltp_shop__"+shop_id).html(html);
        });
    }


    function oaibGetShopeeBranchMltpShop(state, city) {
        $.ajax({
            url: '{{ route("shopee.order.get_branch_info_1") }}',
            type: "POST",
            data: {
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
                $("#update_pickup_info_oaib_mltp_shop_btn").prop("disabled", false);
            }
            $("#dropoff_shopee_branches_oaib_mltp_shop").html(branch_html);
        });
    }


    function oaibGetShopeeBranchStatesMltpShop() {
        var shopee_shop_id = $("#shopee_shop_oaib_mltp_shop").find("option:selected").data("shopee_shop_id");
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
            $("#dropoff_shopee_branch_states_oaib_mltp_shop").html(state_html);
        });
    }

    function oaibGetShopeeBranchCitiesMltpShop(state) {
        $.ajax({
            url: '{{ route("shopee.order.get_branch_cities_info") }}',
            type: "POST",
            data: {
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
            $("#dropoff_shopee_branch_cities_oaib_mltp_shop").html(city_html);
        });
    }


    function oaibSetShopeeLogisticInfoMltpShop(address_data, time_data, branch_id, tracking_nums=[], shipment_method="pickup") {
        let data;
        let json_data = JSON.stringify(oaib_selected_rows_mltp_shop);
        if (shipment_method==="pickup") {
            data = {
                'address_data': address_data,
                'time_data': time_data,
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
            url: '{{ route("shopee.order.set_batch_logistic_info_mltp_shop") }}',
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
                $("#update_pickup_info_oaib_mltp_shop_btn").prop("disabled", false);
                if (response.success) {
                    $('#pickup_confirmation_message_oaib_mltp_shop').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                    setTimeout(function() {
                        $('#__modalArrangeBatchOrderShipmentMltpShop').doModal('close');
                        updateSelectedShopAndStatusFilterHtmlAfterBatchInitForShopee();
                        // location.reload();
                    }, 2000);
                } else {
                    $('#pickup_confirmation_message_oaib_mltp_shop').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
                }
            } else if (shipment_method==="dropoff_branch_id") {
                $("#update_dropoff_branch_info_oaib_mltp_shop_btn").prop("disabled", false);
                if (response.success) {
                    $('#dropoff_confirmation_message_branch_oaib_mltp_shop').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                    setTimeout(function() {
                        $('#__modalArrangeBatchOrderShipmentMltpShop').doModal('close');
                        updateSelectedShopAndStatusFilterHtmlAfterBatchInitForShopee();
                        // location.reload();
                    }, 2000);
                } else {
                    $('#dropoff_confirmation_message_branch_oaib_mltp_shop').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
                }
            } else if (shipment_method==="dropoff_tracking_no") {
                $("#update_dropoff_tracking_no_oaib_btn").prop("disabled", false);
                if (response.success) {
                    $('#dropoff_confirmation_message_tracking_no_oaib').html('<div class="alert alert-success" role="alert">'+response.message+'</div>');
                    setTimeout(function() {
                        $('#__modalArrangeBatchOrderShipmentMltpShop').doModal('close');
                        updateSelectedShopAndStatusFilterHtmlAfterBatchInitForShopee();
                        // location.reload();
                    }, 2000);
                } else {
                    $('#dropoff_confirmation_message_tracking_no_oaib').html('<div class="alert alert-danger" role="alert">'+response.message+'</div>');
                }
            }
        });
    }


    $('#update_pickup_info_oaib_mltp_shop_btn').on('click', function() {
        $(".error_message").each(function(index, el) {
            $(el).html("");
        });
        var address_data = [];
        var time_data = [];
        var has_error = false;
        $.each(oaib_selected_mltp_shopee_shop, function(index, shop_id) {
            let error_target = $("#div_pickup_confirmation_oaib_mltp_shop__"+shop_id).children(".error_message");
            let address_id = $("#pickup_address_oaib_mltp_shop__"+shop_id).find("option:selected").val();
            if (address_id === "-1") {
                error_target.append("<p class='alert alert-danger'>Please selecte an address</p>");
                has_error = true;
            }

            let time_id = $("#pickup_time_oaib_mltp_shop__"+shop_id).find("option:selected").val();
            let time_text = $("#pickup_time_oaib_mltp_shop__"+shop_id).find("option:selected").text();
            
            if (time_id === "-1") {
                error_target.append("<p class='alert alert-danger'>Please selecte a time slot</p>");
                has_error = true;
            }
            time_data.push({
                "shop_id": shop_id,
                "time_id": time_id,
                "time_text": time_text
            });
            address_data.push({
                "shop_id": shop_id,
                "address_id": address_id
            });
        });

        if (!has_error && address_data.length > 0 && time_data.length > 0) {
            $("#update_pickup_info_oaib_mltp_shop_btn").prop("disabled", false);
            oaibSetShopeeLogisticInfoMltpShop(JSON.stringify(address_data), JSON.stringify(time_data), "", [], "pickup");
        }
    });

    $(document).on("change", ".pickup_time_oaib_mltp_shop_address", function() {
        let shop_id = $(this).data("shop_id");
        if (typeof(shop_id) !== "undefined") {
            oaibGetShopeePickupSpecificTimeSlotMltpShop(shop_id);
        }
    });
</script>
@endpush