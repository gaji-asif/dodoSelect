<style type="text/css">
    .order_details_div_pack{
        display: block;
    }
</style>
<div class="order_details_div_pack">
    <h6 class="pt-4"><strong class="order_shipment_color mb-2">Ordered Product Details:</strong></h6>
    @if (isset($getShipmentsProductsDetails))
        <div class="full-card show">
            <table class="table table-responsive">
                <thead class="thead-light">
                <tr >
                    <th>{{ __('translation.Image') }}</th>
                    <th>{{ __('translation.Product Details') }}</th>
                </tr>
                </thead>
                <tbody class="table-body">
                @if (isset($getShipmentsProductsDetails))
                    @foreach ($getShipmentsProductsDetails as $key=>$row)
                        <tr class="new">
                            <input type="hidden" name="product_id[]" value="@if (isset($row->id)){{($row->id)}} @endif">

                            <td>
                                @if (Storage::disk('s3')->exists($row->image) && !empty($row->image))
                              <img src="{{Storage::disk('s3')->url($row->image)}}" height="80" width="80" alt="">
                              @else
                              <img src="{{Storage::disk('s3')->url('uploads/No-Image-Found.png')}}" height="80" width="80"  alt="">
                              @endif
                          </td>
                            <td>
                                @if (isset($row->product_name))
                                    {{($row->product_name)}}
                                @endif
                                <br>
                                <strong>Code:</strong>
                                @if (isset($row->product_code))
                                    {{($row->product_code)}}
                                @endif
                                <br>
                                <strong>Price:</strong>
                                @if (isset($product_price[$key]['price']))
                                    ฿ {{($product_price[$key]['price'])}}
                                    <input type="hidden" class="product_price" value="{{$product_price[$key]['price']}}">
                                @endif
                                <br>
                                <strong>Quantity: </strong>
                                <input type="number" class="ordered_quantity" name="product_quantity[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='{{$row->shipped_qty}}'>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
    @endif
</div>
<script type="text/javascript">
    $("#select_all_orders").click(function(){
        $('input.pack_checkbox').not(this).prop('checked', this.checked);
    });
</script>




