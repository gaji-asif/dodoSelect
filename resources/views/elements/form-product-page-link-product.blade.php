<style>
  th{
    padding: 4px;
  }
</style>


<div class="flex justify-between items-center pb-3">
  <p class="text-2xl font-bold"> {{$product_code}}</p>
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
<?php //dd($shops);

if (isset($shops)){
foreach ($shops as $shop){
  $arr_shop[$shop->id]=$shop->name;
}
}
//echo "<pre>"; print_r($data);echo "</pre>";
?>
<div id="InVmessageStatus">

   {{ csrf_field() }}

<table id='Table_Products' width="378" style="font-family: Arial;margin: 0 auto;padding: 10px; 15px">
      <thead>
        <th>Inventory ID</th>
      </thead>
      <tbody>
      <?php


      $i=0;
      ?>
      @if (isset($data))
        @foreach ($data as $item)

          <tr id="{{$item['id']}}" product_id="{{$item['product_id']}}" website_id="{{$item['website_id']}}" quantity="{{$item['quantity']}}"  product_code="{{$item['product_code']}}" class="tr_{{$item['id']}}" style="width:100%; padding-bottom:20px;vertical-align: middle;border-bottom:2px solid #e3e3e3;">
           <td>
                <?php
                  echo $id = $item['id'];
                  ?>
           </td>
        </tr>

        <?php $i++; ?>
        @endforeach
      @endif

    </tbody></table>

    </div>
