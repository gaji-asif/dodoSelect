<style type="text/css">
    .order_details_div_pack{
        display: block;
    }
</style>

@if(count($allShipments) == 0)
    <form action="{{ route('shipment.store') }}" method="post" id="__formCreateShipment">
        <input type="hidden" name="order_id" id="__order_idCreateShipment">
        <div class="mb-3">
            <div class="grid grid-cols-1 gap-4">
                <div class="row">
                    <div class="col-lg-4 mb-3">
                        <x-label for="__order_idCreateShipment">
                            {{ __('translation.Order ID') }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="text" id="__order_id_displayCreateShipment" class="bg-gray-200" readonly />
                    </div>
                    <div class="col-lg-4">
                        <x-label for="__pending_stockCreateShipment">
                            Select Status <x-form.required-mark/>
                        </x-label>
                        <div class="form-group">
                            <x-select class="form-control relative top-1" id="shipment_status" name="shipment_status">
                                <option value="{{ \App\Models\Shipment::SHIPMENT_STATUS_READY_TO_SHIP }}">Ready to Ship</option>
                                <option value="{{ \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK }}">Wait For Stock</option>
                            </x-select>

                        </div>
                    </div>
                    <div class="col-lg-4" id="__shipment_date_wrapperCreateShipment">
                        <x-label for="__shipment_dateCreateShipment">
                            {{ __('translation.Shipment Date') }}
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
                                            @if (!empty($row->product->image) && file_exists($row->product->image))
                                                <img src="{{asset($row->product->image)}}" height="80" width="80" alt="">
                                            @else
                                                <img src="{{asset('No-Image-Found.png')}}" height="80" width="80"  alt="">
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($row->product))
                                                {{($row->product->product_name)}}
                                            @endif
                                            <br>
                                            <strong>Code :</strong>
                                            @if (isset($row->product))
                                                {{($row->product->product_code)}}
                                            @endif
                                            <br>
                                            <strong>Price :</strong>
                                            @if (isset($product_price))
                                                ฿{{($product_price[$key]['price'])}}
                                                <input type="hidden"class="product_price" value="{{$product_price[$key]['price']}}">
                                            @endif
                                            <br>
                                            <strong>Ordered Qty : </strong>{{$row->quantity}}

                                            <input type="hidden" name="ordered_quantity[]" min='1' style="width:50px" class="ordered_quantity form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='{{$row->quantity}}'>
                                            <br>
                                            <strong>Shipment Qty : <font class="text_underline_asif">{{$row->quantity}}</font></strong>
                                            <input type="hidden" name="shipment_qty[]" placeholder="0" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 shipment_qty" value="{{$row->quantity}}">
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
            <x-button type="reset" color="gray" id="__btnCancelCreateShipment" class="__btnCancelCreateShipment">
                {{ __('translation.Cancel') }}
            </x-button>
            <x-button type="submit" color="blue" id="__btnSubmitCreateShipment">
                {{ __('translation.Create Shipment') }}
            </x-button>
        </div>
    </form>
@else
    <div class="mb-3 text-center">
        <h5 class="pt-4"><strong class="order_shipment_color mb-2"> Shipments have already been arranged. </strong></h5>
    </div>
    <div class="pb-5 text-center">
        <x-button type="reset" color="gray" class="__btnCancelCreateShipment">
            {{ __('translation.Close') }}
        </x-button>
    </div>
@endif

<script type="text/javascript">
    $('#__btnSubmitCreateShipment').on('click', function(event) {
        event.preventDefault();
        //const shipmentStoreUrl = $(this).attr('action');
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
                $('#__alertDangerContentCreateShipment').html(null);

                $('#__btnCancelCreateShipment').attr('disabled', true);
                $('#__btnSubmitCreateShipment').attr('disabled', true).html(textProcessing);
            },
            success: function(responseData) {
                let alertMessage = responseData.message;

                loadStatusList();
                loadOrderManagementTable(selectedStatusIds);

                $('#__modalCreateShipment').doModal('close');

                $('#__formCreateShipment')[0].reset();
                $('#__pending_stock_info_wrapperCreateShipment').addClass('hidden');

                $('#__btnCancelCreateShipment').attr('disabled', false);
                $('#__btnSubmitCreateShipment').attr('disabled', false).html(textCreateShipment);

                Swal.fire({
                    toast: true,
                    icon: 'success',
                    title: 'Succcess',
                    text: alertMessage,
                    timerProgressBar: true,
                    timer: 2000,
                    position: 'top-end'
                });

            },

            error: function(error) {
                let responseJson = error.responseJSON;

                $('#__btnCancelCreateShipment').attr('disabled', false);
                $('#__btnSubmitCreateShipment').attr('disabled', false).html(textCreateShipment);

                if (error.status == 422) {
                    let errorFields = Object.keys(responseJson.errors);
                    errorFields.map(field => {
                        $('#__alertDangerContentCreateShipment').append(
                            $('<span/>', {
                                class: 'block mb-1',
                                html: `- ${responseJson.errors[field][0]}`
                            })
                        );
                    });
                }
                else {
                    $('#__alertDangerContentCreateShipment').html(responseJson.message);
                }
                $('#__alertDangerCreateShipment').removeClass('hidden');
            }
        });

        return false;
    });

    $('#__shipment_dateCreateShipment').datepicker({
        dateFormat: 'dd-mm-yy'
    });

    $(document).on('change', '#shipment_status', function() {
        $('#__pending_stock_info_wrapperCreateShipment').addClass('hidden');
        $('#__shipment_date_wrapperCreateShipment').removeClass('hidden');

        if ($(this).val() == {{ \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK }}) {
            $('#__shipment_date_wrapperCreateShipment').addClass('hidden');
            $('#__pending_stock_info_wrapperCreateShipment').removeClass('hidden');
        };
    });

    $('.__btnCancelCreateShipment').on('click', function() {
        $('.alert').addClass('hidden');
        $('#__alertDangerContentCreateShipment').html(null);

        $('#__modalCreateShipment').doModal('close');
    });

</script>




