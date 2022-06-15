<style>
  th{
    padding: 4px;
  }
</style>


<div class="flex justify-between items-center pb-3">
  <p class="text-2xl font-bold">Inventory</p>
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
//echo "<pre>"; print_r($arr_shop);echo "</pre>";
?>
<div id="InVmessageStatus">
<div class="form-group">
    <label for="search">Search Product</label>
    <input type="text" name="product_sku" id="product_sku" class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" placeholder="Enter SKU" />
    <div id="productList"></div>
   </div>

   {{ csrf_field() }}

<table id='Table_Products' width="378" style="font-family: Arial;margin: 0 auto;padding: 10px; 15px">
      <thead>
        <th>Shop</th>
        <th></th>
        <th>Name</th>
        <th>SKU</th>
        <th hidden>Available</th>
        <th></th>
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
                  $website_id = $item['website_id'];
                  if(isset($arr_shop[$website_id])){ echo $arr_shop[$website_id]; }
                  ?>
           </td>

            <td style="width:20%;">
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

        <?php $i++; ?>
        @endforeach
      @endif

    </tbody></table>

    <div class="justify-end py-4">
        <x-button color="blue" class='addToInventory'>Add To Inventory</x-button>
    </div>
    </div>

    <script>
    $(document).ready(function(){

     $('#product_sku').keyup(function(){
            var query = $(this).val();
            if(query != '')
            {
             var _token = $('input[name="_token"]').val();
             $.ajax({
              url:"{{ route('autocomplete.fetch') }}",
              method:"POST",
              data:{query:query, _token:_token},
              success:function(data){
               $('#productList').fadeIn();
               $('#productList').html(data);
              }
             });
            }
        });

        $(document).on('click', 'li', function(event){
          event.preventDefault();
          $(this).prop('disabled', true);
          var product_code =  $(this).text();
           $('#product_sku').val(product_code);
           $('#productList').fadeOut();

            $.ajax({
                url: '{{ route('data add_product_by_sku') }}',
                type: "POST",
                data: {
                    'product_code': product_code,
                    '_token': $('meta[name=csrf-token]').attr('content')
                },
                beforeSend: function() {
                    //$('#messageStatus').html("Please wait...");
                }
            })
            .done(function(result) {
                //console.log(result);
                $('#Table_Products tbody').append(result);
                $("#product_sku").val("");
            });
        });

        $(document).on('click', '.BtnRemove', function(){
            var id = $(this).data('id');
          $('.tr_'+id).remove();
        });



    });
    </script>
