<style type="text/css">
  .order_details_div_pack{
    display: block;
  }
</style>
<div class="row"> 
  <div class="col-lg-4 mb-2">
    @if($shipments->print_status == '1')
    <div class="action_butn_style">
      <button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Printed On :<strong><br>
        {{date('d/m/Y h:i a', strtotime($shipments->print_date_time))}}
        <br></strong> (<font class="text-blue-500">
          @if(isset($shipments->print_by) AND !empty($shipments->print_by))
          @php $by = \App\Models\Shipment::getActionBy($shipments->print_by);
          @endphp
          @if(isset($by))
          {{$by}}
          @endif
          @endif
        </font>)</button>
        <br>
        <button id="printLabelBtnAfterSearch" type="button" class="btn btn-warning btn-block">
          <i class="fa fa-print mr-1" aria-hidden="true"></i>
        {{__('translation. Print Label')}}</button>
      </div>


      @else
      <button id="printLabelBtnAfterSearch" type="button" class="btn btn-warning btn-block">
       <i class="fa fa-print mr-1" aria-hidden="true"></i>
     {{__('translation. Print Label')}}</button>
     @endif
   </div>
   <div class="col-lg-4 mb-2">
     @if($shipments->pack_status == '1')
     <div class="action_butn_style">
      <button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Pick Confirm On :<strong><br>
        {{date('d/m/Y h:i a', strtotime($shipments->packed_date_time))}}
        <br></strong> (<font class="text-blue-500">
          @if(isset($shipments->packed_by) AND !empty($shipments->packed_by))
          @php $by = \App\Models\Shipment::getActionBy($shipments->packed_by);
          @endphp
          @if(isset($by))
          {{$by}}
          @endif
          @endif
        </font>)</button>
        <br>
        <button onclick="pickOrderCancel(<?php echo $shipments->id.','.$shipments->order_id;?>)" type="button" class="btn btn-danger btn-block">
          <i class="fa fa-trash mr-1" aria-hidden="true"></i> 
        {{__('translation.Cancel')}}</button>
      </div>


      @else
      <button id="pickConfirmBtnAfterSearch" type="button" class="btn btn-danger btn-block">
       <i class="fa fa-truck-pickup mr-1" aria-hidden="true"></i>
     {{__('translation. Pick Confirm')}}</button>
     @endif
   </div>
   <div class="col-lg-4 mb-2">
     @if($shipments->mark_as_shipped_status == '1')
     <div class="action_butn_style">
      <button disabled class="shipment_btns  btn-sm  mb-1 mt-1 action_btns btn_status_after">Marked Shipped On :<strong><br>
        {{date('d/m/Y h:i a', strtotime($shipments->mark_as_shipped_date_time   ))}}
        <br></strong> (<font class="text-blue-500">
          @if(isset($shipments->mark_as_shipped_by) AND !empty($shipments->mark_as_shipped_by))
          @php $by = \App\Models\Shipment::getActionBy($shipments->mark_as_shipped_by);
          @endphp
          @if(isset($by))
          {{$by}}
          @endif
          @endif
        </font>)</button>
        <br>
        <button onclick="markAsShippedUpdate(<?php echo $shipments->id.','.$shipments->order_id;?>)" type="button" class="btn btn-success btn-block">
         <i class="fa fa-shipping-fast mr-1" aria-hidden="true"></i>
       {{__('translation.Update')}}</button>
     </div>


     @else
     <button id="markAsShippedBtnAfterSearch" type="button" class="btn btn-info btn-block">
       <i class="fa fa-shipping-fast mr-1" aria-hidden="true"></i>
     {{__('translation.Mark as Shipped')}}</button>
     @endif
   </div>
 </div>

 <div class="row">
  <div class="col-lg-12"><hr></div>
  <div class="col-lg-2"></div>
  <div class="col-lg-8">
    <div class="order_details_div_pack">
      <h6 class="pt-4"><strong class="order_shipment_color mb-2">Shipment Products Details  </strong>
        <font class="pull-right float_right color-green font_bold">Total Items : {{count($getShipmentsProductsDetails)}}</font>
      </h6>
      @if (isset($getShipmentsProductsDetails))
      <div class="full-card show">
        <table class="table table-responsive">
          <thead class="thead-light">
            <tr >
              <th>{{ __('translation.Image') }}</th>
              <th>{{ __('translation.Product Details') }}</th>
            </tr>
          </thead>
          <tbody class="table-body new_products_table" id="new_products_table_for_edit">
            @if(isset($getShipmentsProductsDetails))
            @foreach($getShipmentsProductsDetails as $value)
            <tr class="new" id="product_{{($value->id)}}">
              <input type="hidden" name="product_id[]" value="{{($value->id)}}">

              <td>
                @if (!empty($value->image))
                <img src="{{asset($value->image)}}" height="80" width="80" alt="">
                @else
                <img src="{{asset('No-Image-Found.png')}}" height="80" width="80"  alt="">
                @endif

              </td>
              <td>
                <p class="mb-1">
                 {{($value->product_name)}}
               </p>
               <p class="mb-1">
                <strong>{{__('translation.code')}} :</strong>
                <font class="text-blue-500">{{($value->product_code)}}</font>
              </p>
              <p class="mb-1">
                <strong>{{__('translation.Price')}} :</strong>

                ฿{{$value->price}}
                <input type="hidden"class="product_price" value="{{$value->price}}">
              </p>
              <div class="row">
                <div class="col-lg-12 col-sm-12">
                  <strong>{{__('translation.Shipment Qty')}} : </strong>
                  <strong class="text-blue-500">{{$value->quantity}}</strong>
                </div>

              </div>
            </td>
          </tr>
          @endforeach 
          @else
          <tr class="no_product_wrapper">
           <td colspan="2" class="text-center">
             {{__('translation.No added Products Yet')}}
           </td>
         </tr>
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
  $('#printLabelBtnAfterSearch').on('click', function() {
    var order_id = $("#order_id").val();
    var shipment_id =  $("#shipment_id_value_after_search").val();
    $('#__modalAfterSearchShipmentID').modal('hide');
    printLevel(shipment_id, order_id);
  });

  $('#pickConfirmBtnAfterSearch').on('click', function() {
    var order_id = $("#order_id").val();
    var shipment_id =  $("#shipment_id_value_after_search").val();
    $('#__modalAfterSearchShipmentID').modal('hide');
    packOrder(shipment_id, order_id);
  });

  $('#markAsShippedBtnAfterSearch').on('click', function() {
    var order_id = $("#order_id").val();
    var shipment_id =  $("#shipment_id_value_after_search").val();
    $('#__modalAfterSearchShipmentID').modal('hide');
    markAsShipped(shipment_id, order_id);
  });

  function pickOrderCancel(shipment_id, order_id){
    $("#shipment_id_for_cancel_pick_order").val(shipment_id);
    $("#order_id_value_for_cancel_pick_order").val(order_id);
    $('#__modalAfterSearchShipmentID').modal('hide');
    $('#pickOrderCancel').modal('show');
  }

  function markAsShippedUpdate(shipment_id, order_id){
    $("#shipment_id_for_mark_as_shipped_update").val(shipment_id);
    $("#order_id_value_for_mark_as_shipped_update").val(order_id);
    $('#__modalAfterSearchShipmentID').modal('hide');
    $('#markAsShippedUpdateModal').modal('show');
  }


</script>