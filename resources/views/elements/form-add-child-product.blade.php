<?php
if (isset($shops)){
foreach ($shops as $shop){
  $arr_shop[$shop->id]=$shop->name;
}
}
//echo "<pre>"; print_r($arr_shop); echo "</pre>"; die;

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
          <td style="width:80px;">

            <?php
            $website_id = $item['website_id'];
            $product_id = $item['product_id'];
            if(!empty($item['images'])){
              $arr_img =  json_decode($item['images']);
            }else{
              $arr_img ='';
            }

            //  echo "<pre>"; print_r($arr_img); echo "</pre>"; die;
            ?>
           <img style="width:80px;vertical-align: middle;" src="<?php if(!empty($arr_img[0])){ echo $arr_img[0]->src;}?>">
          </td>



          <td style="width:100px">
                <strong>@if (isset($arr_shop[$website_id])){{$arr_shop[$website_id]}} @endif</strong>
          </td>

          <td style="width:100px">
               <?php

                 if(isset($item['type'])){ echo $item['type']; }
                 ?>
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
              @php
                  $inv = \App\Models\Inventory::find($item['inventory_id']);
             if (!empty($inv) && isset($item['inventory_id'])){
                 echo $inv['inventory_name']."(".$inv['inventory_code'].")";
             }else{
                 echo '<span x-on:click="showEditModal=true" class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer addProduct" data-id="'.$item['id'].'" data-product_code="'.$item['product_code'].'" ><i class="fas fa-bezier-curve"></i></span>';
             }
              @endphp
          </td>


          <td style="width:100px">
            <span x-on:click="showEditModal=true" class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer" data-id="{{$item['id']}}" id="BtnUpdate"><i class="fas fa-pencil-alt"></i></span><span class="bg-red-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer BtnDelete" data-id="'.$row->id.'"><i class="fas fa-trash-alt"></i></span>


          </td>

        </tr>
        @endforeach
      @endif
