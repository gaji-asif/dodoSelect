<style>
  th{
    padding: 4px;
  }
</style>
<div class="flex justify-between items-center pb-3">
  <p class="text-2xl font-bold">@if(isset($product)){{$product->product_name}}@endif - (@if(isset($product->getQuantity)){{$product->getQuantity->quantity}}@endif)</p>
  {{-- tombol close --}}
  <div class="cursor-pointer z-50" id="closeModalproduct">
      <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18"
          height="18" viewBox="0 0 18 18">
          <path
              d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">
          </path>
      </svg>
  </div>
</div>
<table class="table mt-3" style="margin-top: 15px;">
  <thead class="" style="background-color: #D4EDDA !important;">
    <tr style="background-color: #D4EDDA;" style="color: #FFFFFF;">
      <th width="15%">PO ID</th>
      <th>Incoming Qty</th>
      <th >Estimated Arrival Date From</th>
      <th >Estimated Arrival Date To</th>
      
    </tr>
  </thead>
  <tbody>
    @if (isset($data))
      @foreach ($data as $item)
        <tr>
          <td>@if (isset($item)){{$item->order_purchase_id}} @endif</td>
          <td>@if (isset($item)){{$item->quantity}} @endif</td>
          <td>@if (isset($item->orderPurchase)){{$item->orderPurchase->e_a_d_f}} @endif</td>
          <td>@if (isset($item->orderPurchase)){{$item->orderPurchase->e_a_d_t}} @endif</td>
    
        </tr>
      @endforeach
    @endif
  
  </tbody>
</table>