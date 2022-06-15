<div class="mb-5">
    <div class="mb-4">
        <div class="grid grid-cols-9 gap-4">
            <div class="col-span-3">
                Order ID
            </div>
            <div class="col-span-6">
                :
                <strong class="ml-2 text-blue-500" id="__orderIdOutputProductsOrder">
                    #{{$data->order_id}}
                </strong>
            </div>
        </div>
    </div>
</div>
<div class="w-full overflow-x-auto mb-10 border">
    <table class="w-full" id="__tblProductProductsOrder">
        <thead>
        <tr>
            <th class="text-center">
                {{ __('translation.Image') }}
            </th>
            <th class="text-center">
                {{ __('translation.Product Details') }}
            </th>
        </tr>
        </thead>
        <tbody>
        <?php if (isset($data))
            $line_items = json_decode($data->line_items);
        ?>
        @if (isset($line_items))
            @foreach ($line_items as $row)
                <tr class="new mt-4">
                    <td>
                        <?php
                            $product_id = $row->variation_id ?? $row->product_id;
                            if(!empty($arr_images[$product_id])){
                                $arr_img =  json_decode($arr_images[$product_id]);
                            }
                        ?>
                        @if (!empty($arr_img))
                            <img src="{{asset($arr_img[0]->src)}}" height="80" width="80" alt="">
                        @else
                            <img src="{{asset('No-Image-Found.png')}}" height="80" width="80"  alt="">
                        @endif
                    </td>
                    <td>
                        @if (isset($row->name))
                            {{($row->name)}}
                        @endif
                        <br>
                        <strong>Code :</strong>
                        @if (isset($row->sku))
                            {{($row->sku)}}
                        @endif
                        <br>
                        <strong>Price :</strong>
                        @if (isset($row->price))
                            ฿{{($row->price)}}
                        @endif
                        <br>
                        <strong>Ordered Qty : </strong>{{$row->quantity}}
                    </td>
                </tr>
            @endforeach
        @endif
        </tbody>
    </table>
</div>
<div class="text-center pb-5">
    <x-button type="button" color="gray" class="__btnCloseModalProductsOrder" id="__btnCancelProductsOrder">
        {{ __('translation.Close') }}
    </x-button>
</div>

<script>
    $('#__btnCancelProductsOrder').on('click', function() {
        $('.modal-products').doModal('close');
    });
</script>
