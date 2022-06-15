 @if (isset($editData->orderProductDetails))
 <div class="full-card show">

 		<h6 class="mt-3 pt-3">
 			<strong>Order ID # {{$editData->id}}</strong>- Processing
 			<font style="float: right;"><strong>Order Date:</strong> {{date('d-M-Y', strtotime($editData->created_at))}}</font>
 		</h6>
 		<input type="hidden" name="order_id" value="{{($editData->id)}}">
 		<h6 class="pt-4"><strong class="text_underline_asif ">Ordered Product Details:</strong></h6>
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
 				<tr class="new" id='@if (isset($row->product)){{($row->product->product_code.$priceAndShop[$key]['shop'])}} @endif ' >
 					<input type="hidden" name="product_id[]" value="@if (isset($row->product)){{($row->product->id)}} @endif">
 					<input type="hidden" name="shop_id[]" value="@if (isset($priceAndShop)){{$priceAndShop[$key]['shop']}} @endif">
 					<td>
 						@if (!empty($row->product->image))
 						<img src="{{asset($row->product->image)}}" class="" height="80" width="80" alt="">
 						@else
 						<img src="{{asset('No-Image-Found.png')}}" height="80" width="80" class="cutome_image" alt="">
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
 						@if (isset($priceAndShop))
 						{{($priceAndShop[$key]['price'])}}
 						<input type="hidden"class="product_price" value="{{$priceAndShop[$key]['price']}}">
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
  </div>
  @endif
