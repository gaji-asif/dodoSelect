@if(isset($time))
{{asset('stock')}}/low-stock-{{$time}}.xlsx View Low Stock   
{{asset('stock')}}/out-of-stock-{{$time}}.xlsx View Out Of Stock 
@endif
