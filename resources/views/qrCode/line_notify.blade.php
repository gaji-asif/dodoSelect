@if(isset($current_status))
!!! {{$current_status}} ALERT !!!@endif 

@if(isset($product_name)){{$product_name}}@endif        
@if(isset($product_code)){{$product_code}}@endif        
Qty:@if(isset($current_stock)) {{$current_stock}} @endif        

@if (count($incoimg_products) > 0)Incoming:  
@foreach($incoimg_products as $key=>$purchase)
PO#@if(isset($purchase->order_purchase_id)){{$purchase->order_purchase_id}} @endif        
QTY:@if(isset($purchase->quantity)){{$purchase->quantity}} @endif        
@if(isset($purchase->e_a_d_f)){{$purchase->e_a_d_f}}@endif  to @if(isset($purchase->e_a_d_t)){{$purchase->e_a_d_t}}@endif     

@endforeach
'---- UPDATE COMPLETE -​---'​
@endif
