<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
  <form action="{{ route('shipment.updateForCustomShipment') }}" method="post" id="__formCreateShipment">

    <div class="mb-2">
      <div class="grid grid-cols-1 gap-4">
        <div class="row">
          <div class="col-lg-4">
            <x-label for="__order_idCreateShipment">
              {{__('translation.Shipment ID')}} <x-form.required-mark/>
            </x-label>
            <strong>{{$shipmentDetails->id}}</strong>
          </div>
          <div class="col-lg-4 margin_top_mb">
            <x-label class="" for="__pending_stockCreateShipment">
             Shipment Status <x-form.required-mark/>
            </x-label>
            <div class="form-group">
              <strong>
              @if($shipmentDetails->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK) Wait For Stock @endif

              @if($shipmentDetails->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_READY_TO_SHIP) Ready to Ship @endif

              @if($shipmentDetails->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_SHIPPED) Shipped @endif

              @if($shipmentDetails->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_CANCEL) Cancelled @endif
                </strong>
             </div>
        </div>
        <div class="col-lg-4" id="__shipment_date_wrapperCreateShipment">
          <x-label for="__shipment_dateCreateShipment">
            {{ __('Shipment Date') }}
          </x-label>
          <strong>
              @if(isset($shipmentDetails->shipment_date))
              {{date('d-M-Y', strtotime($shipmentDetails->shipment_date))}}
              @endif
          </strong>
        </div>
      </div>
    
    </div>
  </div>
  
  <div id="ordered_products" class="mt-4">
   <div class="flex flex-row items-center justify-between mb-2">
    <h2 class="block whitespace-nowrap text-yellow-500 text-base font-bold">
        {{__('translation.Shipment Products Details')}}
    </h2>
    <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-yellow-300">
</div>
     <table class="table table-responsive">
       <thead class="thead-light">
        <tr>
         <th>Image</th>
         <th>Product Details</th>
       </tr>
     </thead>
     <tbody class="table-body new_products_table" id="new_products_table_for_edit">
      @if(isset($getShipmentsProductsDetails))
      @foreach($getShipmentsProductsDetails as $row)
      <tr class="new" id="product_{{($row->id)}}">
        <input type="hidden" name="product_id[]" value="{{($row->id)}}">

        <td>
          @php 
          $images = [];
          if(!empty($row->images)){
              $images = json_decode($row->images);
          }
          @endphp

          @if(isset($images[0]->src))
              <img src="{{ $images[0]->src}}" alt="{{ $row->name }}" height="80" width="80" alt="" style="margin-top: 13px;" >
          @else
              <img src="{{asset('No-Image-Found.png')}}" height="80" width="80" alt="" style="margin-top: 13px;" >
          @endif
        </td>
        <td>
          <p class="mb-1">
           {{($row->product_name)}}
          </p>
          <p class="mb-1">
          <strong>{{__('translation.code')}} :</strong>
          <font class="text-blue-500">{{($row->product_code)}}</font>
          </p>
          <p class="mb-1">
          <strong>{{__('translation.Price')}} :</strong>
          
           ฿{{$row->price}}
          <input type="hidden"class="product_price" value="{{$row->price}}">
          
          </p>
          <div class="row">
            <div class="col-lg-4 col-sm-12">
              <strong>{{__('translation.Shipment Qty')}} : </strong> 
              <font class="text-blue-500"><strong>{{$row->quantity}}</strong></font>
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
<div class="pb-5 text-center">
  <x-button type="reset" color="gray" id="__btnCancelViewCustomShipmentForOrder">
    {{ __('Cancel') }}
  </x-button>

</div>
</form>
<script type="text/javascript">
      $('#__btnCancelViewCustomShipmentForOrder').on('click', function() {
    $('.alert').addClass('hidden');
    $('#__alertDangerViewCustomShipmentForOrder').html(null);

    $('#__modalViewCustomShipmentForOrder').doModal('close');
  });

</script>




