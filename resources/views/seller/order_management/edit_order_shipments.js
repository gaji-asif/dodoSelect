 $(document).on('click', '#add_new_shipment', function() {
            var orderId = $("#id").val();
            alert(orderId);
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
                },
                error: function(error) {

                }
            });
        });

        $(document).on('click', '#edit_new_shipment', function() {
            var orderId = $("#id").val();
            var shipment_id = $(this).data('id');

            $('#__modalEditShipmentForOrder').doModal('open');
            $.ajax({
                type: 'GET',
                url: '{{url('getAllOrderedProForOrderEdit')}}',
                data: {orderId:orderId, shipment_id:shipment_id},
                beforeSend: function() {
                    $("#modal_content_edit_shipment_for_order").html("Loading...");
                },
                success: function(responseData) {
                    $("#modal_content_edit_shipment_for_order").html("");
                    $("#modal_content_edit_shipment_for_order").html(responseData);
                },
                error: function(error) {

                }
            });
        });

        $('.__btnCloseModalShipment').on('click', function() {
            $('#__modalEditShipmentForOrder').doModal('close');
        });

        $(document).on('click', '#delete_new_shipment', function() {
            var orderId = $("#id").val();
            var shipment_id = $(this).data('id');
            $('#__modalCancelShipment').doModal('open');
            $("#shipment_id_value").val(shipment_id);
        });

        $('#__btnCloseModalCancelShipment').on('click', function() {
            $('#__modalCancelShipment').doModal('close');
            $('#__btnCloseModalCancelShipment').addClass('hidden');
        });

        $(document).on('click', '#__btnCloseModalFinalDeleleShipment', function() {
            var orderId = $("#id").val();
            var shipment_id = $("#shipment_id_value").val();
            $.ajax({
                type: 'GET',
                url: '{{url('deleteShipmentForOrder')}}',
                data: {orderId:orderId, shipment_id:shipment_id},
                beforeSend: function() {
                    $("#__btnCloseModalFinalDeleleShipment").html("Processing...");
                },
                success: function(responseData) {
                    var order_id = $("#__order_id_displayCreateShipment").val();
                    $("#shipment_details_wrapper").html("");
                    $.ajax({
                        type: 'GET',
                        data: {
                            order_id: orderId
                        },
                        url: '{{ url('getShipmentDetailsData') }}',
                        beforeSend: function() {
                            $("#shipment_details_wrapper").html("Loading ......");
                        },
                        success: function(result) {
                            $("#shipment_details_wrapper").html(result);
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: 'Success',
                                text: 'Shipment Deleted',
                                timerProgressBar: true,
                                timer: 2000,
                                position: 'top-end'
                            });
                        },
                        error: function() {
                            alert('Something went wrong');
                        }
                    });

                    $('#__modalCancelShipment').doModal('close');
                },
                error: function(error) {
                    alert('Something went wrong');
                }
            });
        });
        $(document).on('click', '#printLevel', function() {
            $("#pack_order_modal").modal('hide');
            $("#print_level_modal").modal('show');
            var shipment_id = $(this).data('id');
            var order_id = $(this).attr('order-id');
            $("#shipment_id_input_val").val(shipment_id);
            $("#order_id_input_val").val(order_id);
            $("#order_id_div").text('Order ID #'+order_id);
            $("#shipment_id_div").text('Shipment ID #'+shipment_id);
            $.ajax
            ({
                type: 'GET',
                data: {shipment_id:shipment_id, order_id:order_id},
                url: '{{url('getCustomerOrderHistory')}}',
                success: function(result)
                {
                    $("#order_details").html(result);
                }
            });
        });

        $(document).on('click', '#packOrder', function() {
            $("#pack_order_modal").modal('show');
            $("#print_level_modal").modal('hide');
            var shipment_id = $(this).data('id');
            var order_id = $(this).attr('order-id');
            $("#shipment_id_input_val_pack").val(shipment_id);
            $("#order_id_input_val_pack").val(order_id);
            $("#order_id_div_pack").text('Order ID #'+order_id);
            $("#shipment_id_div_pack").text('Shipment ID #'+shipment_id);
            $.ajax
            ({
                type: 'GET',
                data: {shipment_id:shipment_id, order_id:order_id},
                url: '{{url('getCustomerOrderHistoryForPack')}}',
                success: function(result)
                {
                    $("#order_details_pack").html('');
                    $("#order_details_pack").html(result);
                }
            });
        });

        function confirmPacking(){

            var chk_arr = $('input[name="chekecked_product_id[]"]:checked').length;
            var total = $("#total_count").val();

            if(Number(chk_arr) === Number(total)){
                var shipment_id = $("#shipment_id_input_val_pack").val();
                var order_id = $("#order_id_input_val_pack").val();
                $.ajax
                ({
                    type: 'POST',
                    data: {shipment_id:shipment_id, order_id:order_id},
                    url: '{{url('updateShipmentStatus')}}',
                    success: function(result)
                    {
                        if(result === 'ok'){
                            $("#pack_order_modal").modal('hide');
                            alert("Your Order has been successfully packed");
                            $("#shipment_details_wrapper").html("");
                            $.ajax({
                                type: 'GET',
                                data: {
                                    order_id: order_id
                                },
                                url: '{{ url('getShipmentDetailsData') }}',
                                beforeSend: function() {
                                    $("#shipment_details_wrapper").html("loading ......");
                                },
                                success: function(result) {
                                    $("#shipment_details_wrapper").html(result);
                                    Swal.fire({
                                        toast: true,
                                        icon: 'success',
                                        title: 'Succcess',
                                        text: 'Shipment Deleted',
                                        timerProgressBar: true,
                                        timer: 2000,
                                        position: 'top-end'
                                    });
                                },
                                error: function() {
                                    alert('Something went wrong');
                                }
                            });
                        }
                    }
                });
            }
            else{
                alert("Please checked all the product from here");
            }
        }