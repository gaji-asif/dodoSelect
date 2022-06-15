<style type="text/css">
    .order_details_div_pack{
        display: block;
    }
</style>
<!-- <input type="text" id="shopee_order_primary_id" value="{{$shipments->id}}"> -->
<div class="row"> 
    <div class="col-lg-4 mb-2">
        @if (isset($shipments->awb_printed_at) and !empty($shipments->awb_printed_at)) 
        <div class="action_butn_style">
            <button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Printed On :<strong><br>
                {{date('d/m/Y h:i a', strtotime($shipments->awb_printed_at))}}
                <br></strong> <font class="text-blue-500">
                  @if(isset($shipments->print_by) AND !empty($shipments->print_by))
                  @php $by = \App\Models\Shipment::getActionBy($shipments->print_by);
                  @endphp
                  @if(isset($by))
                  ( {{$by}} )
                  @endif
                  @endif
                </font></button>
                <br>
                <button data-airway_bill_url="" data-order_id="{{$shipments->order_id}}" data-id="{{$shipments->id}}" onclick="getAirwayBillForSpecificOrder(this)" type="button" class="btn btn-warning btn-block">
                  <i class="fa fa-print mr-1" aria-hidden="true"></i>
              {{__('translation. Print Label')}}</button>
          </div>


          @else
          <button data-airway_bill_url="" data-order_id="{{$shipments->order_id}}" data-id="{{$shipments->id}}" onclick="getAirwayBillForSpecificOrder(this)" type="button" class="btn btn-warning btn-block">
           <i class="fa fa-print mr-1" aria-hidden="true"></i>
       {{__('translation. Print Label')}}</button>
       @endif
   </div>
   <div class="col-lg-4 mb-2">
       @if(isset($shipments->pickup_confirmed_at) and !empty($shipments->pickup_confirmed_at))
       <div class="action_butn_style">
        <button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Pick Confirm On :<strong><br>
            {{date('d/m/Y h:i a', strtotime($shipments->pickup_confirmed_at))}}
            <br></strong> <font class="text-blue-500">
              @if(isset($shipments->packed_by) AND !empty($shipments->packed_by))
                  @php $by = \App\Models\Shipment::getActionBy($shipments->packed_by);
                  @endphp
                  @if(isset($by))
                  ( {{$by}} )
                  @endif
                  @endif
            </font></button>
            <br>
            <button data-order_id="{{$shipments->order_id}}" data-id="{{$shipments->id}}" onclick="pickOrderCancel(this)" type="button" class="btn btn-danger btn-block">
              <i class="fa fa-trash mr-1" aria-hidden="true"></i> 
          {{__('translation.Cancel')}}</button>
      </div>


      @else
      <button data-order_id="{{$shipments->order_id}}" data-id="{{$shipments->id}}" onclick="btnUpdatePickupConfirmStatus(this)" type="button" class="btn btn-danger btn-block">
       <i class="fa fa-truck-pickup mr-1" aria-hidden="true"></i>
   {{__('translation. Pick Confirm')}}</button>
   @endif
</div>
<div class="col-lg-4 mb-2">
   @if(isset($shipments->mark_as_shipped_at) and !empty($shipments->mark_as_shipped_at))
   <div class="action_butn_style">
    <button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Marked Shipped On :<strong><br>
        {{date('d/m/Y h:i a', strtotime($shipments->mark_as_shipped_at))}}
        <br></strong> <font class="text-blue-500">
          @if(isset($shipments->mark_as_shipped_by) AND !empty($shipments->mark_as_shipped_by))
                  @php $by = \App\Models\Shipment::getActionBy($shipments->mark_as_shipped_by);
                  @endphp
                  @if(isset($by))
                  ( {{$by}} )
                  @endif
                  @endif
        </font></button>
        <br>
        <button data-order_id="{{$shipments->order_id}}" data-id="{{$shipments->id}}" onclick="markAsShippedCancel(this)" type="button" class="btn btn-danger btn-block">
         <i class="fa fa-trash mr-1" aria-hidden="true"></i>
      {{__('translation.Cancel')}}</button>
  </div>


  @else
  <button data-order_id="{{$shipments->order_id}}" data-id="{{$shipments->id}}" onclick="btn_update_wearhouse_shipped_status(this)"  type="button" class="btn btn-info btn-block">
   <i class="fa fa-shipping-fast mr-1" aria-hidden="true"></i>
{{__('translation.Mark as Wirehouse Shipped')}}</button>
@endif
</div>
</div>

<div class="row">
  <div class="col-lg-12"><hr></div>
  <div class="col-lg-2"></div>
  <div class="col-lg-8">
  <div class="order_details_div_pack">
    <h6 class="pt-4"><strong class="order_shipment_color mb-2">Ordered Product Details  </strong>
    <font class="pull-right float_right color-green font_bold">Total Items : 
    @if(isset($orderDetails)) {{count($orderDetails)}} @endif
    </font>
    </h6>
   @if (isset($orderDetails))
        <div class="full-card show">
            <table class="table table-responsive">
                <thead class="thead-light">
                <tr >
                    <th>{{ __('translation.Image') }}</th>
                    <th>{{ __('translation.Product Details') }}</th>
                </tr>
                </thead>
                <tbody class="table-body">
                  @if (isset($orderDetails))
                    @foreach ($orderDetails as $item)
                        <?php
                        $image_url = asset('No-Image-Found.png');
                        if (isset($item->item_sku) and !empty($item->item_sku)) {
                              $product = \App\Models\ShopeeOrderPurchase::getProductDetails($item->item_sku);
                            if (isset($product) and isset($product->images) and !empty($product->images)) {
                                $images = json_decode($product->images);
                                if (!empty($images[0])) {
                                    $image_url = $images[0];
                                }
                            }
                        }

                        $currency_symbol = '';
                        if(isset($item->currency_symbol) and !empty($item->currency_symbol) and strlen($item->currency_symbol) === 3) {
                            $currency_symbol = currency_symbol($item->currency_symbol);
                        } else {
                            $currency_symbol = currency_symbol('THB');
                        }
                        ?>
                      <tr class="new">
                           <td>
                              <div class="mb-2">
                                <img src="{{$image_url}}" height="90" width="90" class="" />
                              </div>
                                
                                <div class="whitespace-nowrap text-blue-500 text-center mt-2 pt-2 margin_top_10">
                                    {{__("shopee.order.product.id")}} : <strong>{{$item->item_id}}</strong>
                                </div>
                              
                            </td>
                            <td>
                                <div class="mb-2">@if (isset($item->item_name))
                                    {{($item->item_name)}}
                                @endif
                                </div>
                                <div>
                                  {{$item->variation_name}}
                                </div>
                                <div class="mb-1">
                                  Item SKU : <strong class="text-blue-500">{{$item->item_sku}}</strong><br/>
                                  Variation SKU : <strong class="text-blue-500">{{$item->variation_sku}}</strong>
                              </div>
                               <div class="margin-bottom-3">
                                <strong>Price:  
                                <?php echo 
                                $currency_symbol . number_format(floatval($item->variation_original_price), 2);
                                ?></strong>
                               
                               </div>
                                <div class="margin-bottom-3">
                                <strong>Ordered Quantity: </strong>
                                <input type="number" class="ordered_quantity" name="product_quantity[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='{{number_format($item->variation_quantity_purchased)}}'>
                              </div>
                              <div class="mb-1">
                                <div class="whitespace-nowrap">
                                    <label class="text-gray-700 font-bold">{{__("shopee.total_price")}} : </label>
                                    <strong class="">฿ {{number_format(floatval($item->variation_discounted_price), 2)}}</strong>
                                </div>
                            </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
    @endif
</div>
</div>
<div class="col-lg-2"></div>
</div>

<script type="text/javascript">
 
     /* Get airway bill specific order */
            function getAirwayBillForSpecificOrder(el) {
                var id = $(el).data('id');
                // alert(id);
                // alert("asif");
                if (typeof(id) === "undefined" || id === "") {
                    return;
                }

                $('#__modalAfterSearchShopeeOrderID').modal('hide');
                var url = $(el).data('airway_bill_url');
                if (typeof(url) !== "undefined" && url !== "") {
                    window.open(url, '_blank');
                    return;
                }
                $(el).prop("disabled", true);
                $.ajax({
                    url: '{{ route("shopee.order.get_specific_order_airway_bill") }}',
                    type: "POST",
                    data: {
                        'id': id,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    }
                }).done(function(response) {
                    $(el).prop("disabled", false);
                    if (response.success && typeof(response.data) !== "undefined" && typeof(response.data.url) !== "undefined") {

                        $(el).data('airway_bill_url', response.data.url);
                        window.open(response.data.url, '_blank');
                    } else {
                        var message = "Failed to retrieve url for airway bill."
                        if (typeof(response.message) !== "undefined" && response.message !== "") {
                            message = response.message;
                        }
                        alert(message);
                    }
                });
            }

    // function btnUpdatePickupConfirmStatus(el){
    //         var id = $(el).data('id');
    //         if (typeof(id) === "undefined" || id === "") {
    //             alert("Order is not valid");
    //             return;
    //         }

    //         let conf = confirm("Please confirm you will \"Pick Confirm\" this order.");
    //         if (!conf) {
    //             return;
    //         }
    //         $('#__modalAfterSearchShopeeOrderID').modal('hide');
    //         $.ajax({
    //             url: '{{ route("shopee.order.mark_order_as_confirm_pickup") }}',
    //             type: "POST",
    //             data: {
    //                 'id': id,
    //                 '_token': $('meta[name=csrf-token]').attr('content')
    //             },
    //             beforeSend: function() {
    //                 $('#form-message').html('Please wait');
    //             }
    //         }).done(function(response) {
    //             if (typeof(response.message) !== "undefined" && response.message !== "") {
    //                 shopeeOrderPurchaseTable.ajax.reload();
    //                 reloadOrderStatusList();
    //                 alert(response.message);

    //             }
    //         });
    //     }

   // function btn_update_wearhouse_shipped_status(el){
   //              var id = $(el).data("id");
   //              if (typeof(id) === "undefined" || id === "") {
   //                  alert("Order is not valid");
   //                  return;
   //              }

   //              let conf = confirm("Please confirm your order will be \"Marked As Shipped\"");
   //              if (!conf) {
   //                  return;
   //              }

   //              $.ajax({
   //                  url: '{{ route("shopee.order.mark_order_as_shipped_to_warehouse") }}',
   //                  type: "POST",
   //                  data: {
   //                      'id': id,
   //                      '_token': $('meta[name=csrf-token]').attr('content')
   //                  },
   //                  beforeSend: function() {
   //                      $('#form-message').html('Please wait');
   //                  }
   //              }).done(function(response) {
   //                  if (typeof(response.message) !== "undefined" && response.message !== "") {
   //                      $('#__modalAfterSearchShopeeOrderID').modal('hide');
   //                      shopeeOrderPurchaseTable.ajax.reload();
   //                      reloadOrderStatusList();
   //                      alert(response.message);
   //                  }
   //              });
   //          }

    function pickOrderCancel(el){
        var shipment_id = $(el).data("id");
        var order_id = $(el).data("order_id");
        $("#shipment_id_for_cancel_pick_order").val(shipment_id);
        $("#order_id_value_for_cancel_pick_order").val(order_id);
        $('#__modalAfterSearchShopeeOrderID').modal('hide');
        $('#pickOrderCancel').modal('show');
     }

    function markAsShippedCancel(el){
        var shipment_id = $(el).data("id");
        var order_id = $(el).data("order_id");
        $("#shipment_id_for_cancel_mark_as_shipped").val(shipment_id);
        $("#order_id_value_for_cancel_mark_as_shipped").val(order_id);
        $('#__modalAfterSearchShopeeOrderID').modal('hide');
        $('#markAsShippedUpdateModal').modal('show');
     }

  </script>