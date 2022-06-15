	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <x-card.header>
        <x-card.back-button href="{{ route('order_management.index') }}" id="left_pad" />
        <x-card.title>
            {{ __('translation.Shipment Details') }} #{{$order_id}}
        </x-card.title>
    </x-card.header>
    <div class="custom_ship_content">
    <div class="col-lg-12 tabs mb-3">
      <div id="add_new_custom_shipment_div" class="text-center mb-2">
          <a id="add_new_custom_shipment" class="h-8 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-600 outline-none focus:outline-none focus:border-green-600 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"  color="green">
           <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
            <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path>
          </svg>
          <span class="ml-2">
            {{__('translation. Add New Shipment')}}
          </span>
        </a>
      </div>
    </div>
    <x-section.title>
    {{__('translation. Custom Shipments')}}
    </x-section.title>
    
    <div class="row custom_padding_left_right_15">
        @if(isset($allShipments) && count($allShipments)>0)
            <h6 class="mt-4"><strong>All Custom Shipments : </strong></h6>
            @foreach($allShipments as $rows)
                <div class="w-full overflow-x-auto">
                    <table class="table tbl_border mb-6 text-center">
                        <thead>
                        <tr class="bg-gray-200">
                            <th scope="col">ID #{{$rows->id}}</th>
                            <th scope="col">
                                @if($rows->shipment_status == 10)
                                    {{ __('translation.Wanting for Stock') }}
                                @endif
                                @if($rows->shipment_status == 11)
                                    {{ __('translation.Ready To ship') }}
                                @endif
                                @if($rows->shipment_status == 12)
                                    {{ __('translation.Shipped') }}
                                @endif
                                @if($rows->shipment_status == 13)
                                    {{ __('translation.Cancelled') }}
                                @endif
                            </th>
                            <th scope="col">{{ __('translation.Ship on') }} : 
                            @if(isset($rows->shipment_date))
                            {{date('d-M-Y', strtotime($rows->shipment_date))}}
                            @endif
                            </th>
                            <th scope="col">{{ __('translation.Action') }}</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white">
                        <tr>
                            <td>{{ __('translation.Shipped By') }} : <br><strong>EMS</strong></td>
                            <td>
                            {{ __('translation.Cust. Name') }} : <strong>{{$getCustomerDetails->first_name}} {{$getCustomerDetails->last_name}}</strong><br>
                                {{ __('translation.Total Items') }} : <strong>
                                @php
                                 $getShipmentProductsQty = \App\Models\OrderManagement::getShipmentProductsQty($order_id, $rows->id, 1);
                                @endphp

                                @if(isset($getShipmentProductsQty))
                                <a data-shop_id='{{$website_id}}' data-id="{{$rows->id}}" id="see_total_items" class="total_items_custom">{{count($getShipmentProductsQty)}}</a>
                                
                                @endif
                                
                             </strong><br>
                            </td>
                            <td>
                                <div class="shipment_action_btn  action_btns btn_status_after  btn-sm  mb-1 mt-1 shipment_btns">
                                    @if($rows->print_status == 1)
                                   Printed On :<strong><br>{{$rows->print_date_time}}<br></strong> (<font class="text-blue-500">{{$rows->printer->name}}</font>)<br>
                                    <button type="button" class="btn btn-outline-warning btn-sm mb-2" id="printLevelCustom" shop-id="{{$website_id}}" order-id="{{$rows->order_id}}" data-id="{{$rows->id}}">Printed</button>
                                    @else
                                    <button type="button" class="btn btn-outline-success btn-sm mb-2" id="printLevelCustom" shop-id="{{$website_id}}" order-id="{{$rows->order_id}}" data-id="{{$rows->id}}">Print Label</button>
                                    @endif
                                </div>
                                

                                <div class="shipment_action_btn  action_btns btn_status_after  btn-sm  mb-1 mt-1 shipment_btns">
                                    @if($rows->shipment_status == 12)
                                    Mark Shipped On :<strong><br>{{$rows->mark_as_shipped_date_time}}<br></strong> (<font class="text-blue-500">{{$rows->shipper->name}}</font>)<br>
                                    <button type="button" class="shipment_btns mb-1 mt-1 btn btn-info btn-sm action_btns" id="markAsShipped" shop-id="{{$website_id}}" order-id="{{$rows->order_id}}" data-id="{{$rows->id}}"><i class="fa fa-shipping-fast mr-1" aria-hidden="true"></i>Update</button>
                                    @else
                                    <button type="button" class="btn btn-outline-success btn-sm" id="markAsShipped" shop-id="{{$website_id}}" order-id="{{$rows->order_id}}" data-id="{{$rows->id}}">
                                    <i class="fa fa-shipping-fast mr-1"></i>{{__('translation.Mark as Shipped')}}</button>
                                    @endif
                                </div>
                                <br>
                            </td>
                            <td>
                                <button type="button" data-id="{{$rows->id}}" id="edit_new_shipment_custom" class="modal-open btn-action--yellow" id="">
                                    <i class="fas fa-pencil-alt"></i>
                                </button><br>
                                <button type="button" data-id="{{$rows->id}}" class="btn btn-danger btn-sm" id="delete_new_shipment_custom">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            @endforeach
            @else
           <div class="text-center col-lg-12"> -- No Shipment created -- </div>
            @endif
    </div>
    <x-modal.modal-large id="__modalCreateCustomShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{__('translation. New Custom Shipment')}}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerCreateCustomShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentCreateCustomShipmentForOrder"></div>
            </x-alert-danger>

           <div id="modal_content_create_custom_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>

    <x-modal.modal-large id="__modalEditCustomShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{__('translation. Edit Custom Shipment')}}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerEditCustomShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentEditCustomShipmentForOrder"></div>
            </x-alert-danger>

           <div id="modal_content_edit_custom_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>

     <x-modal.modal-large id="__modalViewCustomShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{__('translation. View shipped Details')}}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerViewCustomShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentViewCustomShipmentForOrder"></div>
            </x-alert-danger>

           <div id="modal_content_view_custom_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>


    <x-modal.modal-large id="__modalEditCustomShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                Edit Shipment
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerEditCustomShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentEditCustomShipmentForOrder"></div>
            </x-alert-danger>

           <div id="modal_content_edit_custom_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>

    <x-modal.modal-small class="modal-hide" id="__modalCancelCustomShipment">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Confirm') }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>
                <div class="mb-5">
                    <p class="text-center">
                        {{ __('translation.Are your Sure Your want to delete the shipment?') }}
                    </p>
                    <input type="hidden" id="shipment_id_value_custom">
                </div>
                <div class="text-center pb-5">
                    <x-button type="button" color="gray" id="__btnCloseModalCancelCustomShipment">
                        {{ __('translation.No, Close') }}
                    </x-button>
                    <x-button-link color="red" id="__btnCloseModalFinalDeleleCustomShipment">
                        {{ __('translation.Yes, Continue') }}
                    </x-button-link>
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
                    <input type="hidden" id="shipment_id_value_MarkAsShipped">
                </div>
                <div class="text-center pb-5">
                    <x-button type="button" color="gray" id="__btnCloseModalCancelMarkAsShipped">
                        {{ __('translation.No, Close') }}
                    </x-button>
                    <x-button-link color="red" id="__btnCloseModalFinalMarkAsShipped">
                        {{ __('translation.Yes, Continue') }}
                    </x-button-link>
                </div>
            </x-modal.body>
        </x-modal.modal-small>

        <div class="modal" tabindex="-1" role="dialog" id="print_level_modal_custom">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <strong>{{ __('translation.Create Print Label') }}</strong>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <button type="button" class="btn btn-success" id="customers_details_btn">Customer details</button>
                        <button type="button" class="btn btn-warning" id="order_details_btn">Order details</button>
                    </div>
                    <div id="printableArea">
                        <h6 class="order_shipment_color">
                            <strong id="order_id_div_custom"></strong>
                            <strong style="float: right;" id="shipment_id_div_custom"></strong>
                        </h6>
                        <div id="order_details_custom"></div>
                    </div>
                    <div class="mt-4 text-center">
                        <form method="POST" action="{{route('WCprintLabelPrint')}}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="shop_id_input_val" name="shop_id_input_val">
                            <input type="hidden" id="shipment_id_input_val_custom" name="shipment_id_input_val">
                            <input type="hidden" id="order_id_input_val_custom" name="order_id_input_val">
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
    </div>

  <script type="text/javascript">
    	$(document).on('click', '#add_new_custom_shipment', function() {
    		var orderId = $("#id").val();
            var website_id = $("#website_id").val();
        $('#__modalCreateCustomShipmentForOrder').doModal('open');
            $.ajax({
                type: 'GET',
                url: '{{url('getModalContentForWCCustomShipment')}}',
                data: {orderId:orderId,website_id:website_id},
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
         var website_id = $("#website_id").val();
         var shipment_id = $(this).data('id');
         var use_for = "edit";

         $('#__modalEditCustomShipmentForOrder').doModal('open');
            $.ajax({
                type: 'GET',
                url: '{{url('getModalContentForEditWCCustomShipment')}}',
                data: {orderId:orderId, website_id:website_id,shipment_id:shipment_id, use_for:use_for},
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

       $(document).on('click', '#see_total_items', function() {
         var orderId = $("#id").val();
         var website_id = $("#website_id").val();
         var shipment_id = $(this).data('id');
         var use_for = "view";

         $('#__modalViewCustomShipmentForOrder').doModal('open');
            $.ajax({
                type: 'GET',
                url: '{{url('getModalContentForEditWCCustomShipment')}}',
                data: {orderId:orderId, website_id:website_id,shipment_id:shipment_id, use_for:use_for},
                beforeSend: function() {
                    $("#modal_content_view_custom_shipment_for_order").html("Loading...");
                },
                success: function(responseData) {
                    $("#modal_content_view_custom_shipment_for_order").html("");
                    //console.log(responseData);
                    $("#modal_content_view_custom_shipment_for_order").html(responseData);


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
                url: '{{url('deleteWCShipmentForOrder')}}',
                data: {orderId:orderId, shipment_id:shipment_id},
                beforeSend: function() {
                    $("#__btnCloseModalFinalDeleleCustomShipment").html("Processing...");
                },
                success: function(responseData) {
                //alert(responseData);
                        
                        $.ajax({
                        type: 'GET',
                        data: {
                            order_id: orderId,
                            website_id : $("#website_id").val(),
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

         $(document).on('click', '#markAsShipped', function() {
          var orderId = $("#id").val();
          var shipment_id = $(this).data('id');
          $("#shipment_id_value_MarkAsShipped").val(shipment_id);
          $('#__modalMarkAsShipped').doModal('open');
        });

         $('#__btnCloseModalCancelMarkAsShipped').on('click', function() {
            $('#__modalMarkAsShipped').doModal('close');
            $('#__btnCloseModalCancelMarkAsShipped').addClass('hidden');
         });

         
         $(document).on('click', '#__btnCloseModalFinalMarkAsShipped', function() {
         var orderId = $("#id").val();
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
                            order_id: orderId,
                            website_id : $("#website_id").val(),
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
                console.log(responseData);
              },
              error: function(error) {

              }
            });
            });

         
         $(document).on('click', '#printLevelCustom', function() {
            $("#pack_order_modal").modal('hide');
            $("#print_level_modal_custom").modal('show');
            var shop_id = $(this).attr('shop-id');
            var shipment_id = $(this).data('id');
            var order_id = $(this).attr('order-id');
            $("#shop_id_input_val").val(shop_id);
            $("#shipment_id_input_val_custom").val(shipment_id);
            $("#order_id_input_val_custom").val(order_id);
            $("#order_id_div_custom").text('Order ID #'+order_id);
            $("#shipment_id_div_custom").text('Shipment ID #'+shipment_id);
            $.ajax
            ({
                type: 'GET',
                data: {shipment_id:shipment_id, order_id:order_id,shop_id:shop_id},
                url: '{{url('getWCCustomerOrderHistoryForCustomShipment')}}',
                success: function(result)
                {
                    //console.log(result);
                    $("#order_details_custom").html(result);
                }
            });
        });

  $(document).on('click', '#packOrder', function() {
    $("#pack_order_modal").modal('show');
    $("#print_level_modal").modal('hide');
    var shop_id = $(this).attr('shop-id');
    var shipment_id = $(this).data('id');
    var order_id = $(this).attr('order-id');
    $("#shipment_id_input_val_pack").val(shipment_id);
    $("#order_id_input_val_pack").val(order_id);
    $("#order_id_div_pack").text('Order ID #'+order_id);
    $("#shipment_id_div_pack").text('Shipment ID #'+shipment_id);
    $.ajax
    ({
        type: 'GET',
        data: {shipment_id:shipment_id,shop_id:shop_id, order_id:order_id},
        url: '{{url('getWCCustomerOrderHistoryForPackAndCustomShipment')}}',
        success: function(result)
        {
                  //alert(result);
                  //console.log(result);
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
                        // alert(result);
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
                                alert('something went wrong');
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>