<style type="text/css">
  #print_div{
    display: none;
  }

  .margin-top-100{
    margin-top:900px !important;
    padding-top: 500px;
  }
</style>




<div class="order_details_div" id="order_details_div">
 <h6 class="pt-4"><strong class="order_shipment_color mb-2">Ordered Product Details:</strong></h6>

 @if (isset($orderProductDetails))
 <div class="full-card show">
 	<table class="table table-responsive">
 		<thead class="thead-light">
 			<tr >
 				<th>Image</th>
 				<th>Product Details</th>

 			</tr>
 		</thead>
 		<tbody class="table-body">
 			@if (isset($orderProductDetails))
 			@foreach ($orderProductDetails as $key=>$row)
 			<tr class="new" id='@if (isset($row->product_id)){{($row->product_id)}} @endif ' >
 				<input type="hidden" name="product_id[]" value="@if (isset($row->product_id)){{($row->product_id)}} @endif">

 				<td>
         @php 
          $images = [];
          if(!empty($arrProductImageWithID[$row->product_id])){
              $product = $arrProductImageWithID[$row->product_id];
              $images = json_decode($product->images);
          }
          @endphp

          @if(isset($images[0]->src))
              <img src="{{ $images[0]->src}}" alt="{{ $row->name }}" height="80" width="80" alt="" style="margin-top: 13px;" >
          @else
              <img src="{{asset('No-Image-Found.png')}}" height="80" width="80" alt="" style="margin-top: 13px;" >
          @endif
 				</td>
 				<td>
 					@if (isset($row->name))
 					{{($row->name)}}
 					@endif
 					<br>
 					<strong>SKU:</strong>
 					@if (isset($row->sku))
 					{{($row->sku)}}
 					@endif
 					<br>
 					<strong>Price:</strong>
 					@if (isset($row->price))
 					฿{{($row->price)}} 2
 					<input type="hidden"class="product_price" value="{{$row->price}}">
 					@endif
 					<br>
 					<strong>Ordered Quantity: </strong>
 					<input type="number" class="ordered_quantity" name="product_quantity[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='{{$row->quantity}}'>
 				</td>
 			</tr>  
 			@endforeach
 			@endif
 		</tbody>

 	</table>

 </div>
 @endif
</div>

<div id="print_div">
  <h6 class="order_shipment_color">
        <strong id="order_id_div_print" class="order_id_div_print"></strong> 
        <strong style="float: right;" class="shipment_id_div_print" id="shipment_id_div_print"></strong>
                </h6>
    <div class="row customers_details_div_print">
      
    <div class="col-6">
        <div class="card shipping_address_wraps mb-3">
            <div class="card-body">
          <h6 class="mb-3">
            <strong>From</strong>
          </h6>
          @if(empty($shopDetails->name) && empty($shopDetails->name))
          <div class="form-group" id="no_selected_from">
            No Address Selected Yet
          </div>
          @endif

          <div class="form-group">
            <h6 id="shipping_name_div_from" style="font-weight: bold;">
              @if(isset($shopDetails->name))
              {{$shopDetails->name}}
              @endif
            </h6>
            <font id="shipping_address_div_from">
              @if(isset($shopDetails->address))
              {{$shopDetails->address}}
              @endif
            </font>
            <p style="margin-bottom: 0px;">

              <span id="district">
                @if(isset($shopDetails->district))
                {{$shopDetails->district}},
                @endif
              </span>

              <span id="sub_district">
                @if(isset($shopDetails->sub_district))
                {{$shopDetails->sub_district}}
                @endif
              </span>
            </p>
            <p>
              <span id="province">
                @if(isset($shopDetails->province))
                {{$shopDetails->province}},
                @endif
              </span>
              <span id="postcode">
                @if(isset($shopDetails->postcode))
                {{$shopDetails->postcode}}
                @endif
              </span>
            </p>
            <p class="mt-2" id="shop_phone" style="font-weight: bold;">
              @if(isset($shopDetails->phone))
              {{$shopDetails->phone}}
              @endif
            </p>
          </div>
        </div>
      </div>
      </div>
      <div class="col-6">
      <div class="card shipping_address_wraps mb-3">
        <div class="card-body">
          <h6 class="mb-3">
            <strong>To</strong>
          </h6>
          @if(empty($shipping['shipping_name']))
          <div class="form-group" id="no_selected">
            No Address Selected Yet
          </div>
          @endif

          <div class="form-group">
            <h6 id="shipping_name_div" style="font-weight: bold;">
            <font id="shipping_address_div">@if(isset($shipping['shipping_name']))
              {{ $shipping['shipping_name'] }}
              @endif
            </font>
            </h6>
            <br/>
            @if(isset($shipping['shipping_company']))
              <span id="" class="thaiFont">{{ $shipping['shipping_company'] }}</span>
            @endif

            @if(isset($shipping['shipping_address_1']))
              <span id="" class="thaiFont"> {{ $shipping['shipping_address_1'] }} </span>
            @endif

            @if(isset($shipping['shipping_address_2']))
            <span id="dis" class="thaiFont"> {{ $shipping['shipping_address_2'] }}, </span>
            @endif
            <br/>
            @if(isset($shipping['shipping_city']))
              <span id="sub" class="thaiFont"> {{ $shipping['shipping_city'] }} </span>
            @endif
            
            @if(isset($shipping['shipping_state']))
            <span id="sub"  class="thaiFont"> {{ $shipping['shipping_state'] }} </span>
            @endif
            <br/>
            @if(isset($shipping['shipping_postcode']))
            <span id="pro"  class="thaiFont"> {{ $shipping['shipping_postcode'] }} </span>
            @endif
            @if(isset($shipping['shipping_country']))
            <span id="pro"  class="thaiFont"> {{ $shipping['shipping_country'] }} </span>
            @endif
            <br>
            @if(isset($shipping['shipping_phone']))
            <span id="pro"  class="thaiFont"> {{ $shipping['shipping_phone'] }} </span>
            @endif
          </font><br>
          
          </div>
        </div> <!-- end of .card-body -->
      </div>
      
      </div>

    </div>
</div>


<script type="text/javascript">
 $(document).ready(function(){
  $("#customers_details_btn").click(function(){

    $(".customers_details_div").show();
    $(".order_details_div").hide();
  });

  $("#order_details_btn").click(function(){
   
    $(".customers_details_div").hide();
    $(".order_details_div").show();
  });
});
</script>

