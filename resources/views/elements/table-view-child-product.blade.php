<?php
if (isset($shops)){
foreach ($shops as $shop){
  $arr_shop[$shop->id]=$shop->name;
}
}


?>
      @if (isset($data))
        @foreach ($data as $item)
        <tr id="{{$item['id']}}" website_id="{{$item['website_id']}}" quantity="{{$item['quantity']}}" product_code="{{$item['product_code']}}" class="tr_{{$item['id']}}" style="width:100%; padding-bottom:20px;vertical-align: middle;border-bottom:2px solid #e3e3e3;">
          <td style="width:30px;"></td>
          <td style="width:70px;">
               <?php
                 echo $item['product_id'];
                 ?>
          </td>


          <td style="width:100px">
               <?php if(isset($item['created_at'])){ echo $item['created_at']; } ?>
          </td>

          <td style="width:100px">
            <div style="font-size: 14px;text-align: left;display: inline-block;vertical-align: middle;padding-left: 10px;padding-right: 10px;">@if (isset($item['product_name'])){{$item['product_name']}} @endif</div>
          </td>


          <td style="width:100px">
              <div style="font-size: 14px;text-align: left;display: inline-block;vertical-align: top;padding-left: 10px;padding-right: 10px;">@if (isset($item['product_code'])){{$item['product_code']}} @endif</div>
          </td>



          <td style="width:100px">
              <div style="text-align: center;font-size: 14px;width:100%">@if (isset($item['quantity'])){{$item['quantity']}} @endif</div>
          </td>

          <td style="width:100px">
              <div style="text-align: center;font-size: 14px;width:100%">@if (isset($item['price'])){{$item['price']}} @endif</div>
          </td>


          <td style="width:100px">
            <span class="bg-red-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer BtnDelete" data-id="{{$item['id']}}"><i class="fas fa-trash-alt"></i></span>
          </td>

        </tr>
        @endforeach
      @endif
