<x-card.header>
    <x-card.back-button href="{{ route('order_management.index') }}" id="left_pad" />
    <x-card.title>
        Shipment Details #{{$order_id}}
    </x-card.title>
</x-card.header>

<div class="col-12 tabs">
    <div class="col-lg-12 mt-4">
        <x-section.title>
            Shipment
        </x-section.title>
        <h6 class="mt-4"><strong>Shipment Products : </strong></h6>
        <div class="w-full overflow-x-auto">
            <table class="table text-center tbl_border" id="shipments_table">
                <thead>
                <tr class="bg-blue-500 text-white align-self-sm-baseline">
                    <th>{{ __('translation.Product Name') }}</th>
                    <th>{{ __('translation.Ordered') }}</th>
                    <th>{{ __('translation.Quantity') }}</th>
                    <th>{{ __('translation.Remaining') }}</th>
                </tr>
                </thead>
                <tbody class="bg-white">
                
                @if(isset($getProductDetails))
                    @foreach($getProductDetails as $value)
                        <tr>
                            <td class="text-left">
                                <div>
                                    <span>{{$value->name}}</span> <br>
                                    <span class="text-blue-500">{{$value->sku}}</span>
                                </div>
                            </td>
                            <td>{{$value->quantity}}

                            </td>
                            @php
                           

                                $getAllQtyByReadyToShip = \App\Models\WooOrderPurchase::getAllQtyByStatus($website_id,$order_id, $value->product_id, \App\Models\Shipment::SHIPMENT_STATUS_READY_TO_SHIP);
                                $getAllQtyByWaitingStock = \App\Models\WooOrderPurchase::getAllQtyByStatus($website_id,$order_id, $value->product_id, \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK);
                                $getAllQtyByCancelled = \App\Models\WooOrderPurchase::getAllQtyByStatus($website_id,$order_id, $value->product_id, \App\Models\Shipment::SHIPMENT_STATUS_CANCEL);
                                $getAllQtyByShipped = \App\Models\WooOrderPurchase::getAllQtyByStatus($website_id,$order_id, $value->product_id, \App\Models\Shipment::SHIPMENT_STATUS_SHIPPED);
                            @endphp
                            <td>
                                @if(isset($getAllQtyByReadyToShip) && $getAllQtyByReadyToShip > 0)
                                    <div>
                                        Ready to Ship : <strong>{{$getAllQtyByReadyToShip}}</strong>
                                    </div>
                                @endif
                                @if(isset($getAllQtyByWaitingStock) && $getAllQtyByWaitingStock > 0)
                                    <div>
                                        Wait for Stock : <strong>{{$getAllQtyByWaitingStock}}</strong>
                                    </div>
                                @endif
                                @if(isset($getAllQtyByCancelled) && $getAllQtyByCancelled > 0)
                                    <div>
                                        Cancelled : <strong>{{$getAllQtyByCancelled}}</strong>
                                    </div>
                                @endif
                                @if(isset($getAllQtyByShipped) && $getAllQtyByShipped > 0)
                                    <div>
                                        Shipped : <strong>{{$getAllQtyByShipped}}</strong>
                                    </div>
                                @endif
                                @if($getAllQtyByReadyToShip <= 0 && $getAllQtyByWaitingStock <= 0 && $getAllQtyByCancelled <= 0 && $getAllQtyByShipped <= 0)
                                    <div>
                                        <strong>-</strong>
                                    </div>
                                @endif
                            </td>
                            <td>
                                {{$value->quantity - ($getAllQtyByReadyToShip + $getAllQtyByWaitingStock + $getAllQtyByCancelled + $getAllQtyByShipped)}}
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
        
        @if($remaining_ship_quantity > 0)
            <div id="add_new_shipment_div" class="text-center mb-8">
                <a id="add_new_shipment" class="h-8 relative top-[0.10rem] inline-flex items-center justify-center px-3 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-600 outline-none focus:outline-none focus:border-green-600 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"  color="green">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
                        <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"></path>
                    </svg>
                    <span class="ml-2">
                        Add New Shipment
                    </span>
                </a>
            </div>
        @endif
    </div>

    <div class="col-lg-12">
        @if(isset($allShipments) && count($allShipments)>0)
            <h6 class="mt-4"><strong>Shipments : </strong></h6>
            @foreach($allShipments as $rows)
                <div class="w-full overflow-x-auto">
                    <table class="table tbl_border mb-6 text-center">
                        <thead>
                        <tr class="bg-gray-200">
                            <th scope="col">ID #{{$rows->id}}</th>
                            <th scope="col">
                                @if($rows->shipment_status)
                                    <?php $status = \App\Models\Shipment::getShipmentStatusStr($rows->shipment_status) ?>
                                    {{ $status }}
                                @endif
                            </th>
                            <th scope="col">
                                @if( $rows->shipment_status != \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK )
                                {{ __('translation.Ship on') }} : {{date('d-M-Y', strtotime($rows->shipment_date))}}
                                @endif
                            </th>
                            <th scope="col">{{ __('translation.Action') }}</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white">
                        <tr>
                            <td>{{ __('translation.Shipped By') }} : <br><strong>{{ isset($shippingMethod) ? $shippingMethod : '' }}</strong></td>
                            <td>
                                {{ __('translation.Cust. Name') }} : <strong>{{$getCustomerDetails->first_name}} {{$getCustomerDetails->last_name}}</strong><br>
                                {{ __('translation.Total Items') }} : <strong>
                                   
                                    @if(isset($getProductDetails))
                                <a data-shop_id='{{$website_id}}' data-id="{{$rows->id}}" id="see_total_items" class="total_items">
                                    {{count($getProductDetails)}}
                                </a>
                                
                                @endif
                            
                            </strong><br>
                            </td>
                            <td>
                                @if($rows->print_status == 0)
                                    <button type="button" class="btn btn-outline-success btn-sm mb-2" id="printLevel" shop-id="{{$website_id}}" order-id="{{$rows->order_id}}" data-id="{{$rows->id}}">Print Label</button>
                                    <br>
                                @else
                                    <button type="button" class="btn btn-outline-warning btn-sm mb-2" shop-id="{{$website_id}}" order-id="{{$rows->order_id}}" data-id="{{$rows->id}}">Printed</button>
                                    <br>
                                @endif

                                @if($rows->pack_status == 0)
                                    <button type="button" class="btn btn-outline-success btn-sm" id="packOrder" shop-id="{{$website_id}}" order-id="{{$rows->order_id}}" data-id="{{$rows->id}}">Pack Order</button>
                                @else
                                    <button type="button" class="btn btn-outline-warning btn-sm" id="" shop-id="{{$website_id}}" order-id="{{$rows->order_id}}" data-id="{{$rows->id}}">Packed</button>
                                @endif
                                <br>
                            </td>
                            <td>
                                <button type="button" data-shop_id="{{$rows->shop_id}}"  data-id="{{$rows->id}}" id="edit_new_shipment" class="modal-open btn-action--yellow">
                                    <i class="fas fa-pencil-alt"></i>
                                </button><br>
                                <button type="button" data-shop_id="{{$rows->shop_id}}"  data-id="{{$rows->id}}" class="btn btn-danger btn-sm" id="delete_new_shipment">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            @endforeach
        @endif
    </div>

    <x-modal.modal-large id="__modalCreateShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.New Shipment') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerCreateShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentCreateShipmentForOrder"></div>
            </x-alert-danger>

            <div id="modal_content_create_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>


    <x-modal.modal-large id="__modalEditShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Edit Shipment') }}
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalShipment"/>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerEditShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentEditShipmentForOrder"></div>
            </x-alert-danger>

            <div id="modal_content_edit_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>


    <x-modal.modal-large id="__modalViewShipmentForOrder" class="modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.View Shipment') }}
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalViewShipment"/>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger id="__alertDangerViewShipmentForOrder" class="alert mb-5 hidden">
                <div id="__alertDangerContentViewShipmentForOrder"></div>
            </x-alert-danger>

            <div id="modal_content_view_shipment_for_order"></div>
        </x-modal.body>
    </x-modal.modal-large>


    <x-modal.modal-small class="modal-hide" id="__modalCancelShipment">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Confirm') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <div class="mb-5">
                <p class="text-center">
                    {{ __('order-management.shipment_delete_msg') }}
                </p>
                <input type="hidden" id="shipment_id_value">
                <input type="hidden" id="shop_id_value">
                
            </div>
            <div class="text-center pb-5">
                <x-button type="button" color="gray" id="__btnCloseModalCancelShipment">
                    {{ __('translation.No, Close') }}
                </x-button>
                <x-button-link color="red" id="__btnCloseModalFinalDeleleShipment">
                    {{ __('translation.Yes, Continue') }}
                </x-button-link>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

    <div class="modal" tabindex="-1" role="dialog" id="print_level_modal">
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
                            <strong id="order_id_div"></strong>
                            <strong style="float: right;" id="shipment_id_div"></strong>
                        </h6>
                        <div id="order_details"></div>
                    </div>
                    <div class="mt-4 text-center">
                        <form method="POST" action="{{route('WCprintLabelPrint')}}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="shipment_id_input_val" name="shipment_id_input_val">
                            <input type="hidden" id="order_id_input_val" name="order_id_input_val">
                            <input type="hidden" id="shop_id_input_val" name="shop_id">
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

    <div class="modal" tabindex="-1" role="dialog" id="pack_order_modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <strong>Create Order Packed </strong>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="printableArea_pack">
                        <h6 class="order_shipment_color">
                            <strong id="order_id_div_pack"></strong>
                            <strong style="float: right;" id="shipment_id_div_pack"></strong>
                        </h6>
                        <div id="order_details_pack"></div>

                        <div class="mt-4 text-center">
                            <input class="btn btn-success" style="margin: 0 auto; padding: 5px 10px;" type="button" onclick="confirmPacking()" value="Confirm Packing" />
                            <form method="POST" action="{{route('WCprintLabelPrint')}}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="shipment_id_input_val_pack" name="shipment_id_input_val_pack">
                                <input type="hidden" id="order_id_input_val_pack" name="order_id_input_val_pack">
                                <input type="hidden" id="shop_id_input_val" name="shop_id">
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    
        $(document).on('click', '#add_new_shipment', function() {
            var orderId = $("#id").val();
            var website_id = $("#website_id").val();
         
            $('#__modalCreateShipmentForOrder').doModal('open');
            $.ajax({
                type: 'GET',
                url: '{{url('getAllWCOrderedProductForOrShipment')}}',
                data: {orderId:orderId,website_id:website_id,disable_edit_quantity:'0'},
                beforeSend: function() {
                    $("#modal_content_create_shipment_for_order").html("Loading...");
                },
                success: function(responseData) {
                    //$("#modal_content_create_shipment_for_order").html("");
                    $("#modal_content_create_shipment_for_order").html(responseData);
                },
                error: function(error) {

                }
            });
        });

        $(document).on('click', '#edit_new_shipment', function() {
            var orderId = $("#id").val();
            var shop_id = $(this).data("shop_id");
            var shipment_id = $(this).data('id');

            $('#__modalEditShipmentForOrder').doModal('open');
            $.ajax({
                type: 'GET',
                url: '{{url('getAllWCOrderedProForOrderEdit')}}',
                data: {shop_id:shop_id,orderId:orderId, shipment_id:shipment_id},
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


        $(document).on('click', '#see_total_items', function() {
         var orderId = $("#id").val();
         var shipment_id = $(this).data('id');
         var shop_id = $(this).data('shop_id');
         var use_for = "view";
         $('#__modalViewShipmentForOrder').doModal('open');
            $.ajax({
                type: 'GET',
                url: '{{url('getAllWCOrderedProForOrderEdit')}}',
                data: {orderId:orderId, shop_id:shop_id,shipment_id:shipment_id, use_for:use_for},
                beforeSend: function() {
                    $("#modal_content_view_shipment_for_order").html("Loading...");
                },
                success: function(responseData) {
                    $("#modal_content_view_shipment_for_order").html("");
                    $("#modal_content_view_shipment_for_order").html(responseData);
                },
                error: function(error) {

                }
            });
        });

        
        $('.__btnCloseModalViewShipment').on('click', function() {
            $('#__modalViewShipmentForOrder').doModal('close');
        });

        $(document).on('click', '#printLevel', function() {
            $("#pack_order_modal").modal('hide');
            $("#print_level_modal").modal('show');
            var shop_id = $(this).attr('shop-id');
            var shipment_id = $(this).data('id');
            var order_id = $(this).attr('order-id');
            
            $("#shop_id_input_val").val(shop_id);
            $("#shipment_id_input_val").val(shipment_id);
            $("#order_id_input_val").val(order_id);
            $("#order_id_div").text('Order ID #'+order_id);
            $("#shipment_id_div").text('Shipment ID #'+shipment_id);
            $.ajax
            ({
                type: 'GET',
                data: {shipment_id:shipment_id, order_id:order_id,shop_id:shop_id},
                url: '{{url('getWCCustomerOrderHistory')}}',
                success: function(result)
                {
                    $("#order_details").html(result);
                }
            });
        });

        $(document).on('click', '#packOrder', function() {
            $("#pack_order_modal").modal('show');
            $("#print_level_modal").modal('hide');
            var shop_id = $(this).attr('shop-id');
            var shipment_id = $(this).data('id');
            var order_id = $(this).attr('order-id');
            
            $("#shop_id_input_val").val(shop_id);
            $("#shipment_id_input_val").val(shipment_id);
            $("#order_id_input_val").val(order_id);
            $("#order_id_div_pack").text('Order ID #'+order_id);
            $("#shipment_id_div_pack").text('Shipment ID #'+shipment_id);
            $.ajax
            ({
                type: 'GET',
                data: {shipment_id:shipment_id, order_id:order_id,shop_id:shop_id},
                url: '{{url('getWCCustomerOrderHistoryForPack')}}',
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
            
            //if(Number(chk_arr) === Number(total)){
                var shipment_id = $("#shipment_id_input_val_pack").val();
                var order_id = $("#order_id_input_val_pack").val();
                var website_id = $("#__order_id_website_id").val();
                $.ajax
                ({
                    type: 'POST',
                    data: {shipment_id:shipment_id, order_id:order_id},
                    url: '{{url('updateWCShipmentStatus')}}',
                    success: function(result)
                    {
                        if(result === 'ok'){
                            $("#pack_order_modal").modal('hide');
                            alert("Your Order has been successfully packed");
                            $("#shipment_details_wrapper").html("");
                            $.ajax({
                                type: 'GET',
                                data: {
                                    order_id: order_id,
                                    website_id : $("#__order_id_website_id").val()
                                },
                                url: '{{ url('getWCShipmentDetailsData') }}',
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
            //}
            //else{
             //   alert("Please checked all the product from here");
           // }
        }

        $('#__btnCloseModalCancelShipment').on('click', function() {
            $('#__modalCancelShipment').doModal('close');
        });
    </script>
