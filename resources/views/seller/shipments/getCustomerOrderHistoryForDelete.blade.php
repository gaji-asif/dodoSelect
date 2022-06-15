<style type="text/css">
  .order_details_div_pack{
    display: block;
  }
</style>
<input type="hidden" id="pack_status" value="{{$shipmentDetails->pack_status}}">
@if(isset($shipmentDetails))
@if($shipmentDetails->pack_status == 1)
<p class="mt-2 mb-2" style="padding: 10px; border: 1px solid red;">
  <strong style="font-size: 18px;">For this Shipment, Already all ordered product are packed. Before delete, you must be unpacked all product by Select All.</strong>
</p>

<div class="order_details_div_pack">
 <h6 class="pt-4"><strong class="order_shipment_color mb-2">Ordered Product Details:</strong></h6>
 @if (isset($editData->orderProductDetails))
 <div class="full-card show">
 	<table class="table table-responsive">
 		<thead class="thead-light">
 			<tr>
        <th width="14%">
        	<input type="checkbox" id="select_all_orders_for_delete"> All
        </th>
 				<th>Image</th>
 				<th>Product Details</th>

 			</tr>
 		</thead>
 		<tbody class="table-body">
    <input type="hidden" 
    id="total_count_for_del" value="{{count($editData->orderProductDetails)}}">
    
 			@if (isset($editData->orderProductDetails))
 			@foreach ($editData->orderProductDetails as $key=>$row)
 			<tr class="new" id='@if (isset($row->product)){{($row->product->product_code)}} @endif ' >
 				<input type="hidden" name="product_id[]" value="@if (isset($row->product)){{($row->product->id)}} @endif">

 				<td><input type="checkbox" class="pack_checkbox" value="pro_{{$row->product->id}}" id="d_pro_{{$row->product->id}}" name="chekecked_product_id[]"></td>
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
@endif
@endif
<script type="text/javascript">
	$("#select_all_orders_for_delete").click(function(){
       $('input.pack_checkbox').not(this).prop('checked', this.checked);
    });
</script>




