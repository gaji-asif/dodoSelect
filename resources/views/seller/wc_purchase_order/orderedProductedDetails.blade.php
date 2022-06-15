<tr class="new" id="product_{{($product->product_id)}}">
	<input type="hidden" name="product_id[]" value="@if (isset($product)){{($product->product_id)}} @endif">

	<td>
		@if (!empty($product->image))
		<img src="{{asset($product->image)}}" height="80" width="80" alt="">
		@else
		<img src="{{asset('No-Image-Found.png')}}" height="80" width="80"  alt="">
		@endif

	</td>
	<td>
		<p class="mb-1">
		@if (isset($product))
		{{($product->product_name)}}
		@endif
		</p>
		<p class="mb-1">
		<strong>{{__('translation.code')}} :</strong>
		@if (isset($product))
		{{($product->product_code)}}
		@endif
		</p>
		<p class="mb-1">
		<strong>{{__('translation.Price')}} :</strong>
		@if (isset($product))
		฿{{$product->price}}
		<input type="hidden"class="product_price" value="{{$product->price}}">
		@endif
		</p>
		<div class="row">
			<div class="col-lg-4 col-sm-12">
				<strong>{{__('translation.New Shipment Qty')}} : </strong> 
				<input type="text" id="shipment_qty_{{$product->product_id}}" name="shipment_qty[]" data-id="{{$product->product_id}}" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 shipment_qty">
		   </div>
		   <div class="col-lg-4 col-sm-12">
		   	<button type="button" class="btn btn-danger font_size_12 margin_top_re" onclick="removeProductWrapper('{{$product->product_id}}');">
		   		REMOVE
		   	</button>
		   </div>
		</div>
	</td>
</tr>  