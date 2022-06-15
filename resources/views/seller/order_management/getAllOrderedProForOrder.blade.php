<style type="text/css">
    .order_details_div_pack{
        display: block;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
<form action="{{ route('shipment.storeForOrder') }}" method="post" id="__formCreateShipment">
    <input type="hidden" name="order_id" id="__order_idCreateShipment" value="{{$editData->id}}">
    <div class="mb-3">
        <div class="grid grid-cols-1 gap-4">
            <div class="row">
                <div class="col-lg-4 mb-3">
                    <x-label for="__order_idCreateShipment">
                        {{ __('Order ID') }} <x-form.required-mark/>
                    </x-label>
                    <x-input type="text" value="{{$editData->id}}" id="__order_id_displayCreateShipment" class="bg-gray-200" readonly />
                </div>
                <div class="col-lg-4">
                    <x-label for="__pending_stockCreateShipment">
                        Select Status <x-form.required-mark/>
                    </x-label>
                    <div class="form-group ">
                        <x-select class="form-control relative top-1" id="shipment_status" name="shipment_status">
                            <option value="{{ \App\Models\Shipment::SHIPMENT_STATUS_READY_TO_SHIP }}">Ready to Ship</option>
                            <option value="{{ \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK }}">Wait For Stock</option>
                        </x-select>
                    </div>
                </div>
                <div class="col-lg-4" id="__shipment_date_wrapperCreateShipment">
                    <x-label for="__shipment_dateCreateShipment">
                        {{ __('Shipment Date') }}
                    </x-label>
                    <x-input type="text" name="shipment_date" id="__shipment_dateCreateShipment" placeholder="DD-MM-YYYY" autocomplete="off"/>
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
            @if (isset($editData->orderProductDetails))
                <div class="full-card show">
                    <table class="table table-responsive">
                        <thead class="thead-light">
                        <tr>
                            <th>{{ __('translation.Image') }}</th>
                            <th>{{ __('translation.Product Details') }}</th>
                        </tr>
                        </thead>
                        <tbody class="table-body">
                        <input type="hidden" id="total_count" value="{{count($editData->orderProductDetails)}}">

                        @if (isset($editData->orderProductDetails))
                            @foreach ($editData->orderProductDetails as $key=>$row)
                                <tr class="new" id='@if (isset($row->product)){{($row->product->product_code)}} @endif ' >
                                    <input type="hidden" name="product_id[]" value="@if (isset($row->product)){{($row->product->id)}} @endif">
                                    <td>
                                        @if (Storage::disk('s3')->exists($row->product->image) && !empty($row->product->image))
                                            <img src="{{Storage::disk('s3')->url($row->product->image)}}" height="80" width="80" alt="">
                                        @else
                                            <img src="{{Storage::disk('s3')->url('uploads/No-Image-Found.png')}}" height="80" width="80" alt="">
                                        @endif
                                    </td>
                                    <td>
                                        <div class="grid grid-cols-1 lg:grid-cols-3">
                                            <div class="lg:col-span-3">
                                                <div class="grid grid-cols-3">
                                                    <div class="col-span-3 sm:col-span-2">
                                                        <div class="text-left">
                                                            @if (isset($row->product))
                                                                <div class="mb-1">
                                                                    <span>{{$row->product->product_name}}</span>
                                                                </div>
                                                            @endif
                                                            @if (isset($row->product))
                                                                <div class="mb-1">
                                                                    <span class="text-blue-500">{{$row->product->product_code}}</span>
                                                                </div>
                                                            @endif
                                                            @if (isset($product_price))
                                                                <div class="mb-2">
                                                                    Price :
                                                                    <span class="font-bold">฿{{($product_price[$key]['price'])}}</span>
                                                                    <input type="hidden"class="product_price" value="{{$product_price[$key]['price']}}">
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
                                                                Shipped Qty :
                                                                @if (isset($product_price))
                                                                    <font class="text-blue-500 text_underline_asif font-bold">{{$product_price[$key]['shipped_qty']}}</font>
                                                                @endif
                                                            </div>
                                                            <div class="mb-2">
                                                                Remaining Qty :
                                                                <font class="text-red-500 text_underline_asif font-bold">{{$row->quantity - $product_price[$key]['shipped_qty']}}</font>
                                                                <input type="hidden" id="avaliable_qty_input_{{$row->id}}" value="{{$row->quantity - $product_price[$key]['shipped_qty']}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="lg:col-span-3">
                                                    <div class="text-left">
                                                        <div class="mb-1">
                                                            New Shipment Qty :<input type="number" id="shipment_qty_{{$row->id}}" name="shipment_qty[]" data-id="{{$row->id}}" value="{{$row->quantity - $product_price[$key]['shipped_qty']}}" placeholder="0" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 shipment_qty">
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
        <x-button type="reset" color="gray" id="__btnCancelCreateShipmentForOrder">
            {{ __('Cancel') }}
        </x-button>
        <x-button type="submit" color="blue" id="__btnSubmitCreateShipmentForOrder">
            {{ __('Create Shipment') }}
        </x-button>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>

<script type="text/javascript">
    $('#__btnSubmitCreateShipmentForOrder').on('click', function(event) {
        event.preventDefault();
        const shipmentStoreUrl = $("#__formCreateShipment").attr('action');
        const formData = new FormData($("#__formCreateShipment")[0]);

        $.ajax({
            type: 'POST',
            url: shipmentStoreUrl,
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('.alert').addClass('hidden');
                $('#__alertDangerContentCreateShipmentForOrder').html(null);
            },
            success: function(responseData) {
                let alertMessage = responseData.message;
                $('#__modalCreateShipmentForOrder').doModal('close');

                $('#__formCreateShipment')[0].reset();
                $('#__pending_stock_info_wrapperCreateShipment').addClass('hidden');

                Swal.fire({
                    toast: true,
                    icon: 'success',
                    title: 'Succcess',
                    text: alertMessage,
                    timerProgressBar: true,
                    timer: 2000,
                    position: 'top-end'
                });
                // redirect order list page after successful create shipment
                var url = "{{ url('/order_management') }}";
                window.location.href = url;
                if(responseData){
                    var order_id = $("#__order_id_displayCreateShipment").val();
                    $("#shipment_details_wrapper").html("");
                    $.ajax({
                        type: 'GET',
                        data: {
                            order_id: order_id
                        },
                        url: '{{ url('getShipmentDetailsData') }}',
                        beforeSend: function() {
                            $("#shipment_details_wrapper").html("Loading ......");
                        },
                        success: function(result) {
                            $("#shipment_details_wrapper").html(result);
                            loadOrderManagementTable(selectedStatusIds);
                        },
                        error: function() {
                            alert('Something went wrong');
                        }
                    });
                }
            },

            error: function(error) {
                let responseJson = error.responseJSON;
                if (error.status == 422) {
                    let errorFields = Object.keys(responseJson.errors);
                    errorFields.map(field => {
                        $('#__alertDangerContentCreateShipmentForOrder').append(
                            $('<span/>', {
                                class: 'block mb-1',
                                html: `- ${responseJson.errors[field][0]}`
                            })
                        );
                    });

                } else {
                    $('#__alertDangerContentCreateShipmentForOrder').html(responseJson.message);
                }

                $('#__alertDangerCreateShipmentForOrder').removeClass('hidden');
            }
        });

        return false;
    });

    $('#__shipment_dateCreateShipment').datepicker({
        dateFormat: 'dd-mm-yy'
    });

    $(document).on('change', '#shipment_status', function() {
        var shipment_status = $("#shipment_status").val();
        if(shipment_status == {{ \App\Models\Shipment::SHIPMENT_STATUS_READY_TO_SHIP }}){
            $('#__pending_stock_info_wrapperCreateShipment').addClass('hidden');
            $('#__shipment_date_wrapperCreateShipment').removeClass('hidden');
        }
        if(shipment_status == {{ \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK }}){
            $('#__shipment_date_wrapperCreateShipment').addClass('hidden');
            $('#__pending_stock_info_wrapperCreateShipment').removeClass('hidden');
        }
    });

    $('#__btnCancelCreateShipmentForOrder').on('click', function() {
        $('.alert').addClass('hidden');
        $('#__alertDangerContentCreateShipment').html(null);

        $('#__modalCreateShipmentForOrder').doModal('close');
    });

    $(".shipment_qty").blur(function(){
        var shipment_qty = $(this).val();
        var order_manage_id = $(this).data("id");
        var available_qty = $("#avaliable_qty_input_"+order_manage_id).val();
        if(Number(shipment_qty)>Number(available_qty)){
            alert("Must be shipment Quantity less than available Quantity");
            $("#shipment_qty_"+order_manage_id).val('');
        }
    });
</script>




