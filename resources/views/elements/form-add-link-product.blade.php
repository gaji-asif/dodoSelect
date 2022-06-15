  <?php
  if (isset($shops)){
  foreach ($shops as $shop){
    $arr_shop[$shop->id]=$shop->name;
  }
  }

  if (isset($currentTableData)){
    //$arr_cData = json_decode($currentTableData);
    foreach($currentTableData as $cdata){
      $arr_c_product_id[] =  $cdata->product_id;
      $arr_c_product_id[] = $cdata->website_id;
    }
  }

  $sessionData = Session::get('add_linked_product');


  $array = array(3, 5, 2, 5, 3, 9);
  $duplicates = array_duplicates($sessionData);

  function array_duplicates(array $sessionData)
  {
      return array_diff_assoc($sessionData, array_unique($sessionData));
  }
  //echo "<pre>"; print_r($duplicates); echo "</pre>";

      $i=0;
      ?>
      @if (isset($data))
        @foreach ($data as $item)
        <?php



        if(in_array($item['id'], $duplicates) == false){

       ?>
        <tr id="{{$item['id']}}" website_id="{{$item['website_id']}}" quantity="{{$item['quantity']}}" product_code="{{$item['product_code']}}" class="tr_{{$item['id']}}" style="width:100%; padding-bottom:20px;vertical-align: middle;border-bottom:2px solid #e3e3e3;">
          <td>
               <?php
                 $website_id = $item['website_id'];
                 if(isset($arr_shop[$website_id])){ echo $arr_shop[$website_id]; }
                 ?>
          </td>
          <td style="width:30%;">

            <?php
            $website_id = $item['website_id'];
            $product_id = $item['product_id'];
            if(!empty($arr_images[$product_id])){
              $arr_img =  json_decode($arr_images[$product_id]);
            }else{
              $arr_img ='';
            }

            //  dd($arr_img);
            ?>
           <img style="width:60px;height: 60px;display: inline-block;vertical-align: middle;" src="<?php if(!empty($arr_img[0])){ echo $arr_img[0]->src;}?>">
          </td>

          <td style="width:55%;padding-top:10px">
            <div style="font-size: 14px;text-align: left;display: inline-block;vertical-align: middle;padding-left: 10px;padding-right: 10px;">@if (isset($item['product_name'])){{$item['product_name']}} @endif</div>
          </td>


          <td style="width:55%;padding-top:10px">
              <div style="font-size: 14px;text-align: left;display: inline-block;vertical-align: top;padding-left: 10px;padding-right: 10px;">@if (isset($item['product_code'])){{$item['product_code']}} @endif</div>
          </td>



          <td hidden style="width:10%">
              <div style="text-align: center;font-size: 14px;width:100%">@if (isset($item['quantity'])){{$item['quantity']}} @endif</div>
          </td>

          <td>
            <span class="bg-red-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer BtnRemove" data-id="{{$item['id']}}"><i class="fas fa-trash-alt"></i></span>
          </td>

        </tr>

        <?php $i++;
      } ?>
        @endforeach
      @endif
