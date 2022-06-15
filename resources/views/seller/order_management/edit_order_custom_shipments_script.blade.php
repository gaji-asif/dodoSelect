<script type="text/javascript">
    $(document).ready(function() {
        // get the order id from edit page
        let order_id = $("#id").val();
        let is_custom = 1;
        dataTables("{{ route('all_shipment_list_order') }}?order_id=" + order_id+'&is_custom='+is_custom);
        var datatable;
    });

    function dataTables(url) {
        // Datatable
        datatable = $('#orders_custom_shipments_details_datatable').DataTable({
            processing: true,
            // responsive: true,
            serverSide: true,
            columnDefs : [
            {
                'targets': 0,
                'checkboxes': {
                    'selectRow': true
                }
            }
            ],
            order: [[1, 'desc']],
            ajax: url,
            columns: [
            {
                name: 'checkbox',
                data: 'checkbox'
            },

            {
                data: 'details',
                name: 'details',
                orderable: true,
                searchable: true
            },
            ]
            });
    }

    $(document).on('click', '#add_new_custom_shipment', function() {
            var orderId = $("#id").val();
        $('#__modalCreateCustomShipmentForOrder').doModal('open');
            $.ajax({
                type: 'GET',
                url: '{{url('getModalContentForCustomShipment')}}',
                data: {orderId:orderId},
                beforeSend: function() {
                    $("#modal_content_create_custom_shipment_for_order").html("Loading...");
                },
                success: function(responseData) {
                    $("#modal_content_create_custom_shipment_for_order").html("");
                    console.log(responseData);
                    $("#modal_content_create_custom_shipment_for_order").html(responseData);


                },
                error: function(error) {

                }
            });
            });

      $(document).on('click', '#edit_new_shipment_custom', function() {
         var orderId = $("#id").val();
         var shipment_id = $(this).data('id');
         var use_for = "edit";

         $('#__modalEditCustomShipmentForOrder').doModal('open');
            $.ajax({
                type: 'GET',
                url: '{{url('getModalContentForEditCustomShipment')}}',
                data: {orderId:orderId, shipment_id:shipment_id, use_for:use_for},
                beforeSend: function() {
                    $("#modal_content_edit_custom_shipment_for_order").html("Loading...");
                },
                success: function(responseData) {
                    $("#modal_content_edit_custom_shipment_for_order").html("");
                    console.log(responseData);
                    $("#modal_content_edit_custom_shipment_for_order").html(responseData);


                },
                error: function(error) {

                }
            });
            });

        $(document).on('click', '#delete_new_shipment_custom', function() {
          var orderId = $("#id").val();
          var shipment_id = $(this).data('id');
          $('#__modalCancelCustomShipment').doModal('open');
          $("#shipment_id_value_custom").val(shipment_id);
          $('#__btnCloseModalCancelCustomShipment').removeClass('hidden');
          });

         $('#__btnCloseModalCancelCustomShipment').on('click', function() {
                $('#__modalCancelCustomShipment').doModal('close');
                $('#__btnCloseModalCancelCustomShipment').addClass('hidden');
         });

         
         $(document).on('click', '#__btnCloseModalFinalDeleleCustomShipment', function() {
         var orderId = $("#id").val();
         var shipment_id = $("#shipment_id_value_custom").val();
         $.ajax({
                type: 'GET',
                url: '{{url('deleteShipmentForOrder')}}',
                data: {orderId:orderId, shipment_id:shipment_id},
                beforeSend: function() {
                    $("#__btnCloseModalFinalDeleleCustomShipment").html("Processing...");
                },
                success: function(responseData) {
                //alert(responseData);
                        
                        $.ajax({
                        type: 'GET',
                        data: {
                            order_id: orderId
                        },
                        url: '{{ url('getCustomShipmentDetailsData') }}',
                        beforeSend: function() {
                         $("#custom_shipment_details_wrapper").html("loading ......");   
                        },
                        success: function(result) {
                          
                          Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: 'Succcess',
                            text: 'Shipment Deleted',
                            timerProgressBar: true,
                            timer: 2000,
                            position: 'top-end'
                        });
                        $("#custom_shipment_details_wrapper").html("");
                        $("#custom_shipment_details_wrapper").html(result);
                        },
                        error: function() {
                            alert('something went wrong');
                        }
                    });
                        
                $('#__modalCancelCustomShipment').doModal('close');
                console.log(responseData);
              },
              error: function(error) {

              }
            });
            });

    function getUpdatedShipmentDetailsData(order_id){
        $("#custom_shipment_details_wrapper").show();
        $("#custom_shipment_details_wrapper").html("");
        $.ajax({
            type: 'GET',
            data: {
                order_id: order_id
            },
            url: '{{ url('getCustomShipmentDetailsData') }}',
            beforeSend: function() {
                $("#custom_shipment_details_wrapper").html("loading ......");
            },
            success: function(result) {
                $("#custom_shipment_details_wrapper").html(result);
                Swal.fire({
                    toast: true,
                    icon: 'success',
                    title: 'Succcess',
                    text: 'Success',
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

    function see_total_items(shipment_id, order_id){
         var use_for = "view";
         $('#__modalViewCustomShipmentForOrder').doModal('open');
            $.ajax({
                type: 'GET',
                url: '{{url('getModalContentForEditCustomShipment')}}',
                data: {orderId:order_id, shipment_id:shipment_id, use_for:use_for},
                beforeSend: function() {
                    $("#modal_content_view_custom_shipment_for_order").html("Loading...");
                },
                success: function(responseData) {
                    $("#modal_content_view_custom_shipment_for_order").html("");
                    console.log(responseData);
                    $("#modal_content_view_custom_shipment_for_order").html(responseData);
                },
                error: function(error) {

                }
            });
         }

function printLevel(shipment_id, order_id){
    $("#pack_order_modal").modal('hide');
    $("#print_level_modal").modal('show');
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
            console.log(result);
            $("#order_details").html(result);
        }
    });
}

function packOrder(shipment_id, order_id){
    $("#pack_order_modal").modal('show');
    $("#print_level_modal").modal('hide');
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
                //alert(result);
                console.log(result);
                $("#order_details_pack").html('');
                $("#order_details_pack").html(result);
                clearShipmentNo();
            }
        });
    }

 function confirmPacking(){
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
                    getUpdatedShipmentDetailsData(order_id);
                }
            }
        });
    }

    function pickOrderCancel(shipment_id, order_id){
        $("#shipment_id_for_cancel_pick_order").val(shipment_id);
        $("#order_id_value_for_cancel_pick_order").val(order_id);
        $('#pickOrderCancel').modal('show');
     }


     $('#__btnCloseModalpickOrderCancel').on('click', function() {
        var shipment_id = $("#shipment_id_for_cancel_pick_order").val();
        var order_id = $("#order_id_value_for_cancel_pick_order").val();
        $.ajax({
            type: 'GET',
            url: '{{url('shipmentPickOrderCancel')}}',
            data: {shipment_id:shipment_id},
            beforeSend: function() {
                //$("#__btnCloseModalFinalMarkAsShipped").html("Processing...");
            },
            success: function(responseData) {
                $("#pickOrderCancel").modal('hide');
                getUpdatedShipmentDetailsData(order_id);
            },
          error: function(error) {

          }
        });
     });

    function markAsShipped(shipment_id, order_id){
          $("#shipment_id_value_MarkAsShipped").val(shipment_id);
          $("#order_id_value_MarkAsShipped").val(order_id);
          $('#__modalMarkAsShipped').doModal('open');
          $('#__btnCloseModalCancelMarkAsShipped').removeClass('hidden');
          
      }

     $('#__btnCloseModalCancelMarkAsShipped').on('click', function() {
        $('#__modalMarkAsShipped').doModal('close');
        $('#__btnCloseModalCancelMarkAsShipped').addClass('hidden');
      
     });



     $('#__btnCloseModalFinalMarkAsShipped').on('click', function() {
     var orderId = $("#order_id_value_MarkAsShipped").val();
     var shipment_id = $("#shipment_id_value_MarkAsShipped").val();
     $.ajax({
            type: 'GET',
            url: '{{url('markAsShipped')}}',
            data: {orderId:orderId, shipment_id:shipment_id},
            beforeSend: function() {
                $("#__btnCloseModalFinalMarkAsShipped").html("Processing...");
            },
            success: function(responseData) {
                $('#__modalCancelCustomShipment').doModal('close');
                 getUpdatedShipmentDetailsData(orderId);
          },
          error: function(error) {

          }
        });
    }); 


    function markAsShippedUpdate(shipment_id, order_id){
        $("#shipment_id_for_mark_as_shipped_update").val(shipment_id);
        $("#order_id_value_for_mark_as_shipped_update").val(order_id);
        $('#markAsShippedUpdateModal').modal('show');
     }


     $('#__btnCloseModalMarkAsShippedUpdate').on('click', function() {
        var shipment_id = $("#shipment_id_for_mark_as_shipped_update").val();
        var order_id = $("#order_id_value_for_mark_as_shipped_update").val();
        var shipment_status_update = $("#shipment_status_update").val();
        $.ajax({
            type: 'GET',
            url: '{{url('shipment_status_update')}}',
            data: {shipment_id:shipment_id, shipment_status_update:shipment_status_update},
            beforeSend: function() {
                //$("#__btnCloseModalFinalMarkAsShipped").html("Processing...");
            },
            success: function(responseData) {
                $("#markAsShippedUpdateModal").modal('hide');
                getUpdatedShipmentDetailsData(order_id);
            },
          error: function(error) {

          }
        });
     });
</script>


