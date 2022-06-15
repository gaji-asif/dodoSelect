@if(!empty($products))
@foreach($products as $value)
<div class="product_ind col-lg-3 col-sm-3">
	<div class="card">
		<img  class="card-img-top" src="{{asset($value->image)}}" alt="Card image cap">
		<input type="" class="product_codes" name="" value="{{$value->product_code}}">
		<div class="card-body">
			<h5 class="card-title">{{$value->product_name}} </h5>
			{{$value->product_code}}
			<a href="#" data-id="{{$value->product_name}} ({{$value->product_code}})" class="btn btn-primary btn-sm add_product_to_cart">Add</a>
		</div>
	</div>
</div>
@endforeach
<!-- {{ $products->links() }} -->
@endif


