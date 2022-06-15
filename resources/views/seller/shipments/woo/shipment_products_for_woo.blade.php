<div class="mt-4">
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
          <input type="hidden" id="total_count_woo_ship_prod" value="{{count($editData->WOOshipment_products)}}">

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





