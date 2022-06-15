<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
  <form action="{{ route('shipment.updateForCustomShipment') }}" method="post" id="__formCreateShipment">

    <div class="mb-2" id="headers_details_about_shipments">
      <div class="grid grid-cols-1 gap-4">
        <div class="row">
          <div class="col-lg-4">
            <x-label for="__order_idCreateShipment">
              {{__('translation.Shipment ID')}} <x-form.required-mark/>
            </x-label>
            <strong>{{$shipments->id}}</strong>
          </div>
          <div class="col-lg-4 margin_top_mb">
            <x-label class="" for="__pending_stockCreateShipment">
             Shipment Status <x-form.required-mark/>
            </x-label>
            <div class="form-group">
              <strong>
              @if($shipments->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK) Wait For Stock @endif

              @if($shipments->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_READY_TO_SHIP) Ready to Ship @endif

              @if($shipments->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_SHIPPED) Shipped @endif

              @if($shipments->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_CANCEL) Cancelled @endif
                </strong>
             </div>
        </div>
        <div class="col-lg-4" id="__shipment_date_wrapperCreateShipment">
          <x-label for="__shipment_dateCreateShipment">
            {{ __('Shipment Date') }}
          </x-label>
          <strong>
              @if(isset($shipments->shipment_date))
              {{date('d-M-Y', strtotime($shipments->shipment_date))}}
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
     <tbody class="table-body">
          <input type="hidden" id="total_count" value="{{count($editData->shipment_products)}}">

          @if (isset($editData->WOOshipment_products))
              @foreach ($editData->WOOshipment_products as $key=>$row)
                  <tr class="new">
                    <td>
                       <?php
                         $image_url = '';
                         if (!empty($row->woo_product->images)) {
                            $images = json_decode($row->woo_product->images);
                            $image_url = $images[0]->src;
                        }
                        ?>
                      @if (!empty($row->woo_product->images))
                              <img src="{{asset($image_url)}}" height="80" width="80" alt="">
                          @else
                              <img src="{{asset('No-Image-Found.png')}}" height="80" width="80"  alt="">
                          @endif

                          <div class="text-center mt-2">
                            ID: 
                            @if (isset($row->woo_product->product_id))
                              {{($row->woo_product->product_id)}}
                          @endif
                          </div>
                      </td>
                      <td>
                          @if (isset($row->woo_product))
                              {{($row->woo_product->product_name)}}
                          @endif
                          <br>
                          <span class="text-blue-500">
                          @if (isset($row->woo_product))
                              {{($row->woo_product->product_code)}}
                          @endif
                          </span>
                          <br>
                          <strong>Price:</strong>
                          @if (isset($row->woo_product))
                              ฿{{($row->woo_product->price)}}
                          @endif
                          <br>
                          <strong>Shipped Quantity: </strong>
                          @if (isset($product_shipped_quantity))
                              {{($product_shipped_quantity[$key]['quantity'])}}
                           @endif

                        </td>
                  </tr>
              @endforeach
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




