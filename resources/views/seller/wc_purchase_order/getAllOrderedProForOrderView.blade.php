<style type="text/css">
    .order_details_div_pack{
        display: block;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
<form action="{{ route('shipment.WCshipmentUpdateForOrder') }}" method="post" id="__formUpdateShipment">
    <input type="hidden" name="order_id" id="__order_idCreateShipment" value="{{$orderManagement->order_id}}">
    <input type="hidden" name="shipment_id" id="shipment_id" value="{{$shipmentDetails->id}}">
    <div class="mb-3">
        <div class="grid grid-cols-1 gap-4">
            <div class="row">
                <div class="col-lg-4 mb-3">
                    <x-label for="__order_idCreateShipment">
                        {{ __('translation.Order ID') }} <x-form.required-mark/>
                    </x-label>
                    <strong>{{$orderManagement->order_id}}</strong>
                </div>

                <x-alert-danger id="__alertDangerViewShipmentForOrder" class="alert mb-5 hidden">
                    <div id="__alertDangerContentViewShipmentForOrder"></div>
                </x-alert-danger>


                <div class="col-lg-4">
                    <x-label for="__pending_stockCreateShipment">
                        Select Status <x-form.required-mark/>
                    </x-label>
                    <div class="form-group">
                        <strong>
                            @if($shipmentDetails->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK) Wait For Stock @endif

                            @if($shipmentDetails->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_READY_TO_SHIP) Ready to Ship @endif

                            @if($shipmentDetails->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_SHIPPED) Shipped @endif

                            @if($shipmentDetails->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_CANCEL) Cancelled @endif
                        </strong>
                    </div>
                </div>
                <div class="col-lg-4 @if($shipmentDetails->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK) hidden @endif" id="__shipment_date_wrapperCreateShipment">
                    <x-label for="__shipment_dateCreateShipment">
                        {{ __('translation.Shipment Date') }}
                    </x-label>
                    <strong>
                        @if(isset($shipmentDetails->shipment_date))
                            {{date('d-M-Y', strtotime($shipmentDetails->shipment_date))}}
                        @endif
                </strong>
                </div>
            </div>
            <div id="__pending_stock_info_wrapperCreateShipment" class="hidden">
                <p class="text-yellow-600 mb-0">
                    The status of the this order will change to <span class="font-bold">Waiting for Stock</span>.
                </p>
            </div>
        </div>
    </div>

    <div id="all_ordered_products">
        <div class="order_details_div_pack">
            <h6 class="pt-4"><strong class="order_shipment_color mb-2">Ordered Product & Shipment Details:</strong></h6>
            @if (isset($orderProductDetails))
                <div class="full-card show">
                    <table class="table table-responsive">
                        <thead class="thead-light">
                        <tr>
                            <th>{{ __('translation.Image') }}</th>
                            <th>{{ __('translation.Product Details') }}</th>
                        </tr>
                        </thead>
                        <tbody class="table-body">
                        <input type="hidden" id="total_count" value="{{count($orderProductDetails)}}">

                        @if (isset($orderProductDetails))
                            @foreach ($orderProductDetails as $key=>$row)
                                <tr class="new" id='@if (isset($row->product)){{($row->sku)}} @endif ' >
                                    <input type="hidden" name="product_id[]" value="@if (isset($row->product_id)){{($row->product_id)}} @endif">
                                     <td>
                                    @php 
                                    $images = [];
                                    if(!empty($arrProductImageWithID[$row->product_id])){
                                        $product = $arrProductImageWithID[$row->product_id];
                                        $images = json_decode($product->images);
                                    }
                                    @endphp

                                    @if(isset($images[0]->src))
                                        <img src="{{ $images[0]->src}}" alt="{{ $row->name }}" height="80" width="80" alt="" style="margin-top: 13px;" >
                                    @else
                                        <img src="{{asset('No-Image-Found.png')}}" height="80" width="80" alt="" style="margin-top: 13px;" >
                                    @endif
                                    </td>
                                    <td>
                                        <div class="grid grid-cols-1 lg:grid-cols-3">
                                            <div class="lg:col-span-3">
                                                <div class="grid grid-cols-3">
                                                    <div class="col-span-3 sm:col-span-2">
                                                        <div class="text-left">
                                                            @if (isset($row->name))
                                                                <div class="mb-1">
                                                                    <span>{{$row->name}}</span>
                                                                </div>
                                                            @endif
                                                            @if (isset($row->sku))
                                                                <div class="mb-1">
                                                                    <span class="text-blue-500">{{$row->sku}}</span>
                                                                </div>
                                                            @endif
                                                            @if (isset($row->price))
                                                                <div class="mb-2">
                                                                    Price :
                                                                    <span class="font-bold">฿{{($row->price)}}</span>
                                                                    <input type="hidden"class="product_price" value="{{$row->price}}">
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-span-3 sm:col-span-1 sm:ml-2">
                                                        <div class="text-left">
                                                            <div class="mb-1">
                                                                Ordered Qty :
                                                                <span class="text-gray-600 font-bold">{{$row->quantity}}</span>
                                                                <input type="hidden" class="ordered_quantity" name="ordered_quantity[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='{{$row->quantity}}'>
                                                            </div>
                                                            <div class="mb-1">
                                                                variation {{$row->variation_id}}
                                                            @php $shipped_qty = 0 @endphp
                                                            @if (isset($arrProductWiseTotalShippedQty[$row->product_id]))
                                                                @php 
                                                                    $shipped_qty = $arrProductWiseTotalShippedQty[$row->product_id] 
                                                                @endphp
                                                            @endif
                                                            Ready To Shipped Qty : {{$shipped_qty}}
                                                            </div>
                                                            <div class="mb-2">
                                                                Remaining Qty :
                                                                <font class="text-red-500 text_underline_asif font-bold">{{$row->quantity-$shipped_qty}}</font>
                                                                <input type="hidden" id="avaliable_qty_input_{{$row->id}}" value="{{$row->quantity}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="lg:col-span-3">
                                                    <div class="text-left">
                                                        <div class="mb-1">
                                                            Shipment Qty :
                                                            <p id="shipment_qty_{{$row->id}}" > @if(isset($shipmentQtyPerPro)){{$shipmentQtyPerPro[$key]['shipment_qty']}}@endif </p>
                                                            <input type="hidden" id="edit_shipment_qty_{{$row->id}}" value="@if(isset($shipmentQtyPerPro)){{$shipmentQtyPerPro[$key]['shipment_qty']}}@endif">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    <div class="pb-5 text-center">
        <x-button type="reset" color="gray" id="__btnCancelViewShipmentForOrder">
            {{ __('Cancel') }}
        </x-button>
    </div>
</form>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
<script type="text/javascript">

    $('#__shipment_dateCreateShipment').datepicker({
        dateFormat: 'dd-mm-yy'
    });


    $('#__btnCancelViewShipmentForOrder').on('click', function() {
        $('.alert').addClass('hidden');
        $('#__alertDangerContentViewShipmentForOrder').html(null);

        $('#__modalViewShipmentForOrder').doModal('close');
    });

    $(".shipment_qty").blur(function(){
        var shipment_qty = $(this).val();
        var order_manage_id = $(this).data("id");
        var available_qty = $("#avaliable_qty_input_"+order_manage_id).val();
        var edit_shipment_qty = $("#edit_shipment_qty_"+order_manage_id).val();
        if(Number(shipment_qty) > (Number(available_qty) + Number(edit_shipment_qty))){
            alert("Must be shipment Quantity less than available Quantity");
            $("#shipment_qty_"+order_manage_id).val(edit_shipment_qty);
        }
    });

</script>




