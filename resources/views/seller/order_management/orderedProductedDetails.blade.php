<tr class="new" id="product_{{($editData->id)}}">
	<input type="hidden" name="product_id[]" value="@if (isset($editData)){{($editData->id)}} @endif">

	<td>
		@if (Storage::disk('s3')->exists($editData->image) && !empty($editData->image))
       <img src="{{Storage::disk('s3')->url($editData->image)}}" height="80" width="80" alt="">
       @else
       <img src="{{Storage::disk('s3')->url('uploads/No-Image-Found.png')}}" height="80" width="80"  alt="">
       @endif

	</td>
	<td>
		<p class="mb-1">
		@if (isset($editData))
		{{($editData->product_name)}}
		@endif
		</p>
		<p class="mb-1">
		<strong>{{__('translation.code')}} :</strong>
		@if (isset($editData))
		{{($editData->product_code)}}
		@endif
		</p>
		<p class="mb-1">
		<strong>{{__('translation.Price')}} :</strong>
		@if (isset($editData))
		฿{{$editData->price}}
		<input type="hidden"class="product_price" value="{{$editData->price}}">
		@endif
		</p>
		<div class="row">
			<div class="col-lg-4 col-sm-12">
				<strong>{{__('translation.New Shipment Qty')}} : </strong> 
				<input type="text" id="shipment_qty_{{$editData->id}}" name="shipment_qty[]" data-id="{{$editData->id}}" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 shipment_qty">
		   </div>
		   <div class="col-lg-4 col-sm-12">
		   	<button type="button" class="btn btn-danger font_size_12 margin_top_re" onclick="removeProductWrapper('{{$editData->id}}');">
		   		REMOVE
		   	</button>
		   </div>
		</div>
	</td>
</tr>  