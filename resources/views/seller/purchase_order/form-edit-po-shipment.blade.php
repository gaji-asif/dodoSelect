@csrf
@if(!empty($po_shipments))
		@foreach ($po_shipments as $po_shipment)
<div id="__templateShipmentEditForm_{{ $id }}">
    <div class="mt-4" style="width: 100%; float: left;  margin-bottom: 10px; margin-right: 1%;">
        <input type="hidden" name="id[]" id="id" value="{{ $id }}">
        <x-input type="hidden" name="po_shipment_id[]" class="mb-2" value="{{$id}}" />
    </div>


            <div class="mb-3 mt-3">
                <h2 class="block text-gray-600 text-base font-bold">
                    {{ __('translation.Supply Info') }}
                </h2>
                <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
            </div>

            <div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-x-8">
                    <div class="imports">
                        <x-label>
                            {{ __('translation.Factory Tracking') }}
                        </x-label>
                            <x-input type="text" name="factory_tracking[{{$id}}]" id="factory_tracking" value="{{ $po_shipment['factory_tracking'] }}" />
                    </div>


                    <div>
                        <x-label>
                            {{ __('translation.Number Of Cartons') }}
                        </x-label>
                        <x-input type="text" name="number_of_cartons[{{$id}}]" id="number_of_cartons" value="{{ $po_shipment['number_of_cartons'] }}" />
                    </div>

                    <div>
                        <x-label>
                            {{ __('translation.Ship Date') }}
                        </x-label>
                        <x-input type="text" name="ship_date[{{$id}}]" id="ship_date1" class="datepicker-1" value="{{ !empty($po_shipment['ship_date']) ? date('d-m-Y', strtotime($po_shipment['ship_date'])) : '' }}" />
                    </div>

                    <div>
                        <x-label>
                            {{ __('translation.Estimated Arrival Date') }} <span class="font-bold">{{ __('translation.From') }}</span>
                        </x-label>
                        <x-input type="text" name="e_a_d_f[{{$id}}]" id="e_a_d_f1" class="datepicker-1" value="{{ !empty($po_shipment['e_a_d_f']) ? date('d-m-Y', strtotime($po_shipment['e_a_d_f'])) : '' }}" />
                    </div>
                    <div>
                        <x-label>
                            {{ __('translation.Estimated Arrival Date') }} <span class="font-bold">{{ __('translation.To') }}</span>
                        </x-label>
                        <x-input type="text" name="e_a_d_t[{{$id}}]" id="e_a_d_t1" class="datepicker-1" value="{{ !empty($po_shipment['e_a_d_t']) ? date('d-m-Y', strtotime($po_shipment['e_a_d_t'])) : '' }}" />
                    </div>

                    <div>
                        <x-label>
                            {{ __('translation.Status') }}
                        </x-label>

                        <select class="block w-full h-10 px-2 py-2 rounded-md shadow-sm border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 bg-white form-control" id="exampleFormControlSelect1" name="status[{{$id}}]">
                        <option value="open" @if($po_shipment['status'] == 'open') selected @endif >Open</option>
                        <option value="arrive" @if($po_shipment['status'] == 'arrive') selected @endif>Arrived</option>
                        <option value="close" @if($po_shipment['status'] == 'close') selected @endif>Close</option>
                        </select>
                    </div>

                </div>
                <div class="imports">
                    <div class="flex flex-row items-center justify-between mb-3 mt-3">
                        <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                            {{ __('translation.Cargo Information') }}
                        </h2>
                        <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">
                        <div>
                            <x-label>
                                <strong>{{ __('translation.Shipping Type') }} :</strong> <span id="label_shipping_type">****</span>
                            </x-label>

                            <x-label>
                            <strong>{{ __('translation.Shipping Ref') }} :</strong> <span id="label_shipping_mark">*****</span>
                            </x-label>

                        </div>

                        <div>
                            <x-label>
                                {{ __('translation.Cargo Reference') }}
                            </x-label>
                            <x-input type="text" name="cargo_ref[{{$id}}]" id="cargo_ref" value="{{ $po_shipment['cargo_ref'] }}" />
                        </div>
                    </div>
                </div>

                <div class="flex flex-row items-center justify-between mb-3 mt-3">
                    <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                        {{ __('translation.Last Mile') }}
                    </h2>
                    <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">


                <div>
                    <x-label>
                        {{ __('translation.Domestic Shipper') }}
                    </x-label>
                    <x-select name="domestic_shipper_id[{{$id}}]" id="domestic_shipper_id" style="width: 100%">
                    <option selected value="0">
                            - {{ __('translation.All Domestic Shipper') }} -
                        </option>
                        @foreach ($domesticShippers as $domesticShipper)
                            <option value="{{ $domesticShipper->id }}" @if($po_shipment['domestic_shipper_id'] == $domesticShipper->id) selected @endif>
                                {{ $domesticShipper->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                    <div>
                        <x-label>
                            {{ __('translation.Domestic Tracking') }}
                        </x-label>
                        <x-input type="text" name="domestic_logistics[{{$id}}]" id="domestic_logistics" value="{{ $po_shipment['domestic_logistics'] }}" />
                    </div>

                </div>



            </div>




            <div class="__templateShippedProductItem">


            @if(!empty($po_shipment['po_shipment_details']))

            @foreach ($po_shipment['po_shipment_details'] as $key=>$pos_detail)
                <div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200 __row_ProductItem_{{$pos_detail['product']['product_code']}}">
                <x-input type="hidden" name="shipping_product_id[{{$pos_detail['po_shipment_id']}}][]" class="mb-2" value="{{$pos_detail['product']['id']}}" />

                    <div class="w-1/4 sm:w-1/4 md:w-1/6 mb-4 md:mb-0">
                        <div class="mb-4">
                        @if (!empty($pos_detail['image']))
                        <img src="{{asset($pos_detail['image'])}}"  width="80"  class="w-full h-auto rounded-sm">
                        @else
                        <img src="{{asset('No-Image-Found.png')}}" width="80"  class="w-full h-auto rounded-sm">
                        @endif
                        </div>
                    </div>

                    <div class="w-3/4 sm:w-3/4 md:w-5/6 ml-4 sm:ml-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-2 gap-4 sm:gap-x-6 lg:pt-1">
                            <div>
                                <div class="mb-2 xl:mb-4 lg:col-span-2 xl:col-span-3">
                                    <label class="hidden lg:block mb-0">
                                        {{ __('translation.Product Name') }} :
                                    </label>
                                    <p class="font-bold">
                                    {{$pos_detail['product']['product_name']}} <br>
                                        <span class="text-blue-500">{{$pos_detail['product']['product_code']}} </span>
                                    </p>
                                </div>
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                                    <div>
                                        <label class="mb-0">
                                            {{ __('translation.Order (Packs)') }} :
                                        </label>
                                        <span class="font-bold lg:block" id="order_qty_{{$pos_detail['product']['product_code']}}">
                                            {{$po_shipment['order_purchase_details'][$key]['quantity']}}
                                        </span>
                                    </div>
                                    <div>
                                        <label class="mb-0">
                                            {{ __('translation.Shipped') }} :
                                        </label>
                                        <span  class="font-bold lg:block" id="shipped_qty_{{$pos_detail['product']['product_code']}}">
                                            @if($pos_detail['get_shipped'])
                                                @foreach ($pos_detail['get_shipped'] as $item)
                                                    @if($item['product_id']==$pos_detail['product']['id'])
                                                        {{$item['totalShipped']}}
                                                        @php $total_shipped_qty = $item['totalShipped']; @endphp
                                                    @endif
                                                @endforeach
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <label class="mb-0">
                                            {{ __('translation.Available Qty') }} :
                                        </label>
                                        <span class="font-bold lg:block" id="available_qty_{{$pos_detail['product']['product_code']}}">
                                            {{$po_shipment['order_purchase_details'][$key]['quantity']-$total_shipped_qty}}
                                        </span>
                                    </div>
                                </div>

                                <hr>




                            </div>
                            <div class="grid grid-cols-1 lg:grid-cols-1 gap-3">

                                <div class="mb-4 lg:mb-2 " style="border: 1px solid #e6e6e6;padding:20px;">
                                    <label class="mb-0">
                                        {{ __('translation.Shipped') }} (Packs) <x-form.required-mark/> :
                                    </label>
                                    <div class="w-full md:w-full md:pr-2">
                                    <span hidden id="pieces_per_pack_{{$pos_detail['product']['product_code']}}">{{$pos_detail['product']['product_cost_details']['pieces_per_pack']}} </span>
                                        <x-input type="number" name="ship_quantity[{{$pos_detail['po_shipment_id']}}][]" class="ship_quantity mb-2" data-old-qty="{{$pos_detail['ship_quantity']}}" value="{{$pos_detail['ship_quantity']}}" min="0" data-code="{{$pos_detail['product']['product_code']}}" />
                                        <strong>{{ __('translation.Total Pieces') }} : <span class='res_pieces'>{{$pos_detail['product']['product_cost_details']['pieces_per_pack'] * $pos_detail['ship_quantity']}}</span></strong>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @endif
        </div>

            @endforeach
            @endif

</div>

    <div class="justify-end py-4 ">
        <x-button type="button" color="green" class="relative -top-1 reset-filter" data-po-id="{{ $id }}" id="__confirmEditPOShipment">
            {{ __('translation.Confirm') }}
        </x-button>
    </div>


<script type="text/javascript">
    $(".datepicker-1").datepicker({
        dateFormat: 'dd-mm-yy'
    });

    $('#__confirmEditPOShipment').click(function() {
        let poID = $(this).attr('data-po-id');
        // Remove Existing Element
        $('#__WrapperShipmentEditedForms #__templateShipmentEditForm_'+poID).remove();
        let __templateShipmentEditForm = $('#__templateShipmentEditForm_'+poID).clone();
        $("#__WrapperShipmentEditedForms").prepend(__templateShipmentEditForm);
        $('.edit-modal-shipment-info').addClass('modal-hide');
        $('body').removeClass('modal-open');
    });

</script>



