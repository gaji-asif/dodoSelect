 <style type="text/css">
 	.order_details_div{
 		display: none;
 	}
  #print_div{
    display: none;
  }

  .margin_10{
    margin-top:200px !important;
    padding-top: 200px;
  }
</style>
<div class="row customers_details_div">
  <div class="col-6">
   <div class="card shipping_address_wraps mb-3">
    <div class="card-body">
     <h6 class="mb-3">
      <strong>From</strong>
    </h6>
    @if(empty($userDetails->name))
    <div class="form-group" id="no_selected">
      No Info Selected Yet
    </div>
    @endif

    <div class="form-group">
      <h6 id="shipping_name_div" style="font-weight: bold;">
       @if(isset($userDetails->name))
       {{$userDetails->name}}
       @endif
     </h6>
     <font id="shipping_address_div">
       @if(isset($userDetails->email))
       {{$userDetails->email}}
       @endif
     </font>
     <p style="margin-bottom: 0px;">

       <span id="dis">
        @if(isset($userDetails->address))
        {{$userDetails->address}},
        @endif
      </span>
    </p>
    <p class="mt-2" id="shipping_phone_div" style="font-weight: bold;">
     @if(isset($userDetails->phone))
     {{$userDetails->phone}}
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
 		@if(empty($editData->shipping_name) && empty($editData->shipping_name))
 		<div class="form-group" id="no_selected">
 			No Address Selected Yet
 		</div>
 		@endif

 		<div class="form-group">
 			<h6 id="shipping_name_div" style="font-weight: bold;">
 				@if(isset($editData->shipping_name))
 				{{$editData->shipping_name}}
 				@endif
 			</h6>
 			<font id="shipping_address_div">
 				@if(isset($editData->shipping_address))
 				{{$editData->shipping_address}}
 				@endif
 			</font>
 			<p style="margin-bottom: 0px;">

 				<span id="dis">
 					@if(isset($editData->shipping_district))
 					{{$editData->shipping_district}},
 					@endif
 				</span>

 				<span id="sub">
 					@if(isset($editData->shipping_sub_district))
 					{{$editData->shipping_sub_district}}
 					@endif
 				</span>
 			</p>
 			<p>
 				<span id="pro">
 					@if(isset($editData->shipping_province))
 					{{$editData->shipping_province}},
 					@endif
 				</span>
 				<span id="postcodes">
 					@if(isset($editData->shipping_postcode))
 					{{$editData->shipping_postcode}}
 					@endif
 				</span>
 			</p>
 			<p class="mt-2" id="shipping_phone_div" style="font-weight: bold;">
 				@if(isset($editData->shipping_phone))
 				{{$editData->shipping_phone}}
 				@endif
 			</p>
 		</div>
 	</div> <!-- end of .card-body -->
 </div>
</div>
</div>

<div class="order_details_div">
 <h6 class="pt-4"><strong class="order_shipment_color mb-2">Ordered Product Details:</strong></h6>
 @if (isset($editData->orderProductDetails))
 <div class="full-card show">
 	<table class="table table-responsive">
 		<thead class="thead-light">
 			<tr >
 				<th>Image</th>
 				<th>Product Details</th>

 			</tr>
 		</thead>
 		<tbody class="table-body">
 			@if (isset($editData->orderProductDetails))
 			@foreach ($editData->orderProductDetails as $key=>$row)
 			<tr class="new" id='@if (isset($row->product)){{($row->product->product_code)}} @endif ' >
 				<input type="hidden" name="product_id[]" value="@if (isset($row->product)){{($row->product->id)}} @endif">

 				<td>
 					@if (!empty($row->product->image))
 					<img src="{{asset($row->product->image)}}" height="80" width="80" alt="">
 					@else
 					<img src="{{asset('No-Image-Found.png')}}" height="80" width="80"  alt="">
 					@endif
 				</td>
 				<td>
 					@if (isset($row->product))
 					{{($row->product->product_name)}}
 					@endif
 					<br>
 					<strong>Code:</strong>
 					@if (isset($row->product))
 					{{($row->product->product_code)}}
 					@endif
 					<br>
 					<strong>Price:</strong>
 					@if (isset($product_price))
 					฿{{($product_price[$key]['price'])}}
 					<input type="hidden"class="product_price" value="{{$product_price[$key]['price']}}">
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



<div class="modal" tabindex="-1" role="dialog" id="print_level_modal_print">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <strong>Print View</strong>
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <div id="printableAreaNew">
          <h6 class="order_shipment_color">
            <strong id="order_id_div_print">
              
            </strong> 
            <strong style="float: right;" id="shipment_id_div_print">
             
            </strong>
          </h6>
          <div id="print_div">
            <div class="row customers_details_div_print">
              <div class="col-6">
                <div class="card shipping_address_wraps mb-3">
                  <div class="card-body">
                    <h6 class="mb-3">
                      <strong>From</strong>
                    </h6>
                    @if(empty($userDetails->name))
                    <div class="form-group" id="no_selected">
                      No Info Selected Yet
                    </div>
                    @endif

                    <div class="form-group">
                      <h6 id="shipping_name_div" style="font-weight: bold;">
                        @if(isset($userDetails->name))
                        {{$userDetails->name}}
                        @endif
                      </h6>
                      <font id="shipping_address_div">
                        @if(isset($userDetails->email))
                        {{$userDetails->email}}
                        @endif
                      </font>
                      <p style="margin-bottom: 0px;">

                        <span id="dis">
                          @if(isset($userDetails->address))
                          {{$userDetails->address}},
                          @endif
                        </span>
                      </p>
                      <p class="mt-2" id="shipping_phone_div" style="font-weight: bold;">
                        @if(isset($userDetails->phone))
                        {{$userDetails->phone}}
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
                    @if(empty($editData->shipping_name) && empty($editData->shipping_name))
                    <div class="form-group" id="no_selected">
                      No Address Selected Yet
                    </div>
                    @endif

                    <div class="form-group">
                      <h6 id="shipping_name_div" style="font-weight: bold;">
                        @if(isset($editData->shipping_name))
                        {{$editData->shipping_name}}
                        @endif
                      </h6>
                      <font id="shipping_address_div">
                        @if(isset($editData->shipping_address))
                        {{$editData->shipping_address}}
                        @endif
                      </font>
                      <p style="margin-bottom: 0px;">

                        <span id="dis">
                          @if(isset($editData->shipping_district))
                          {{$editData->shipping_district}},
                          @endif
                        </span>

                        <span id="sub">
                          @if(isset($editData->shipping_sub_district))
                          {{$editData->shipping_sub_district}}
                          @endif
                        </span>
                      </p>
                      <p>
                        <span id="pro">
                          @if(isset($editData->shipping_province))
                          {{$editData->shipping_province}},
                          @endif
                        </span>
                        <span id="postcodes">
                          @if(isset($editData->shipping_postcode))
                          {{$editData->shipping_postcode}}
                          @endif
                        </span>
                      </p>
                      <p class="mt-2" id="shipping_phone_div" style="font-weight: bold;">
                        @if(isset($editData->shipping_phone))
                        {{$editData->shipping_phone}}
                        @endif
                      </p>
                    </div>
                  </div> <!-- end of .card-body -->
                </div>
              </div>
            </div>

            <div class="order_details_div_print">
             <h6 class="pt-4"><strong class="order_shipment_color mb-2">Ordered Product Details:</strong></h6>
             @if (isset($editData->orderProductDetails))
             <div class="full-card show">
              <table class="table table-responsive">
                <thead class="thead-light">
                  <tr >
                    <th>Image</th>
                    <th>Product Details</th>

                  </tr>
                </thead>
                <tbody class="table-body">
                  @if (isset($editData->orderProductDetails))
                  @foreach ($editData->orderProductDetails as $key=>$row)
                  <tr class="new" id='@if (isset($row->product)){{($row->product->product_code)}} @endif ' >
                    <input type="hidden" name="product_id[]" value="@if (isset($row->product)){{($row->product->id)}} @endif">

                    <td>
                      @if (!empty($row->product->image))
                      <img src="{{asset($row->product->image)}}" height="80" width="80" alt="">
                      @else
                      <img src="{{asset('No-Image-Found.png')}}" height="80" width="80"  alt="">
                      @endif
                    </td>
                    <td>
                      @if (isset($row->product))
                      {{($row->product->product_name)}}
                      @endif
                      <br>
                      <strong>Code:</strong>
                      @if (isset($row->product))
                      {{($row->product->product_code)}}
                      @endif
                      <br>
                      <strong>Price:</strong>
                      @if (isset($product_price))
                      ฿{{($product_price[$key]['price'])}}
                      <input type="hidden"class="product_price" value="{{$product_price[$key]['price']}}">
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
        </div>
      </div>
      <div class="mt-4 text-center">
       <input class="btn btn-success" style="margin: 0 auto; padding: 5px 10px;" type="button" onclick="printDiv('printableAreaNew')" value="Print" />

     </div>
   </div>
   <div class="modal-footer">

    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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

