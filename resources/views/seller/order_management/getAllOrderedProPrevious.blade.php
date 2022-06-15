  <style type="text/css">
      .order_details_div_pack{
        display: block;
      }

      #all_shipment_btn{
        display: none;
      }
      #__formCreateShipmentByAddBtn{
        display: none;
      }
  </style>
  <div class="row col-lg-12">
   <!--  <a class="mb-6 btn-xs btn btn-info" id="all_shipment_btn">
          <i class="fa fa-object-ungroup" aria-hidden="true"></i>
          <span class="ml-2">All Shipments</span>
        </a> -->
        @if(count($allShipments) > 0)
        <a class="mb-6 ml-2 btn-xs btn btn-success" id="AddNewShipment">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-3 h-3 bi bi-plus-circle" viewBox="0 0 16 16">
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
              <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
          </svg>
          <span class="ml-2">Add New </span>
     </a>
     @endif
</div>
  <div id="tabs">
    <ul>
        @if(count($allShipments) > 0)
        @foreach($allShipments as $allShipment)
        <li>
          <a href="#tabs-{{$allShipment->id}}">Shipment #{{$allShipment->id}}</a>
        </li>
        @endforeach
        @endif
    </ul>

   <!--  Create New Shipment -->
    @if(isset($allShipments))
    @foreach($allShipments as $allShipment)
    <div id="tabs-{{$allShipment->id}}">
      <!-- <h6><strong>Shipment ID : # {{$allShipment->id}}</strong></h6>
      <h6><strong>Shipment Status : </strong></h6>
      <h6><strong>Shipment Date : {{date('d-M-Y', strtotime($allShipment->shipment_date))}}</strong></h6> -->

      <div class="mb-5">
          <div class="grid grid-cols-1 gap-4">
            <div class="row">
              <div class="col-lg-4">
                <x-label for="shipment_id{{$allShipment->id}}">
                  Shipment ID <x-form.required-mark/>
                </x-label>
                <x-input type="text" id="shipment_id{{$allShipment->id}}" class="bg-gray-200" value="{{$allShipment->id}}" readonly />
              </div>
              <div class="col-lg-4 margin_top_mb">
                <x-label class="mb-2" for="__pending_stockCreateShipment{{$allShipment->id}}">
                  {{ __('translation.Ready to Ship') }} <x-form.required-mark/>
                </x-label>
                <div class="flex flex-row items-center margin_top_12">
                  <div>
                    <x-form.input-radio name="pending_stock" id="__pending_stockCreateShipment_0{{$allShipment->id}}" value="0" checked="true">
                    {{ __('translation.Yes') }}
                  </x-form.input-radio>
                </div>
                <div class="ml-4">
                  <x-form.input-radio name="pending_stock" id="__pending_stockCreateShipment_1{{$allShipment->id}}" value="1">
                  {{ __('translation.No') }}
                </x-form.input-radio>
              </div>
            </div>
          </div>
          <div class="col-lg-4 margin_top_mb" id="__shipment_date_wrapperCreateShipment">
            <x-label for="__shipment_dateCreateShipment{{$allShipment->id}}">
              {{ __('translation.Shipment Date') }}
            </x-label>
            <x-input type="text" name="shipment_date" id="__shipment_dateCreateShipment{{$allShipment->id}}" placeholder="DD-MM-YYYY" />
          </div>
        </div>
        <div id="__pending_stock_info_wrapperCreateShipment{{$allShipment->id}}" class="mt-3 hidden">
          <p class="text-yellow-600">
            The status of the this order will change to <span class="font-bold">Waiting for Stock</span>.
          </p>
        </div>
      </div>
    </div>

      <div class="order_details_div_pack">
        <h6 class="pt-4"><strong class="order_shipment_color mb-2">Shipment Product Details:</strong></h6>

      <div class="full-card show">
        <table class="table table-responsive">
          <thead class="thead-light">
            <tr>
              <th>Image</th>
              <th>Product Details</th>
            </tr>
          </thead>
          <tbody class="table-body">
            @php
            $allShipmentsProducts = \App\Models\Shipment::getallShipmentsProducts($allShipment->id);
            @endphp

            @if(isset($allShipmentsProducts))
            @foreach($allShipmentsProducts as $allShipmentsProduct)

            <tr class="new">
              <td><img alt="" height="80" src="{{asset('No-Image-Found.png')}}" width="80"></td>
              <td>{{$allShipmentsProduct->product_name}}<br>
              <strong>Code :</strong> {{$allShipmentsProduct->product_code}}<br>
              <strong>Price :</strong> ฿{{$allShipmentsProduct->price}}<br>
              <strong>Ordered Qty :</strong> {{$allShipmentsProduct->ordered_qty}}<br>
              <strong>Shipment Qty :</strong> <input type="text" class="form-control shipment_qty" name="" id="" value="{{$allShipmentsProduct->quantity}}">
              <button class="btn btn-success">Update</button>
            </td>
            </tr>

            @endforeach
            @endif
          </tbody>
        </table>
       </div>
     </div>
   </div>
    @endforeach
    @endif

 <!--  <div class="pb-5 text-center">
  <x-button type="reset" color="gray" id="__btnCancelCreateShipment1">
    {{ __('translation.Cancel') }}
  </x-button>
  </div> -->
</div>
    <script type="text/javascript">
      $(function() {
        $( "#tabs" ).tabs();
      });
    </script>

    @if(count($allShipments) == 0)
    <form action="{{ route('shipment.store') }}" method="post" id="__formCreateShipment">
      <input type="hidden" name="order_id" id="__order_idCreateShipment">


        <div class="mb-5">
          <div class="grid grid-cols-1 gap-4">
            <div class="row">
              <div class="col-lg-4">
                <x-label for="__order_idCreateShipment">
                  {{ __('translation.Order ID') }} <x-form.required-mark/>
                </x-label>
                <x-input type="text" id="__order_id_displayCreateShipment" class="bg-gray-200" readonly />
              </div>
              <div class="col-lg-4 margin_top_mb">
                <x-label class="mb-2" for="__pending_stockCreateShipment">
                  {{ __('translation.Ready to Ship') }} <x-form.required-mark/>
                </x-label>
                <div class="flex flex-row items-center margin_top_12">
                  <div>
                    <x-form.input-radio name="pending_stock" id="__pending_stockCreateShipment_0" value="0" checked="true">
                    {{ __('translation.Yes') }}
                  </x-form.input-radio>
                </div>
                <div class="ml-4">
                  <x-form.input-radio name="pending_stock" id="__pending_stockCreateShipment_1" value="1">
                  {{ __('translation.No') }}
                </x-form.input-radio>
              </div>
            </div>
          </div>
          <div class="col-lg-4 margin_top_mb" id="__shipment_date_wrapperCreateShipment">
            <x-label for="__shipment_dateCreateShipment">
              {{ __('translation.Shipment Date') }}
            </x-label>
            <x-input type="text" name="shipment_date" id="__shipment_dateCreateShipment" placeholder="DD-MM-YYYY" />
          </div>
        </div>
        <div id="__pending_stock_info_wrapperCreateShipment" class="mt-3 hidden">
          <p class="text-yellow-600">
            The status of the this order will change to <span class="font-bold">Waiting for Stock</span>.
          </p>
        </div>
      </div>
    </div>
    <div id="all_ordered_products">
      <div class="order_details_div_pack">
       <h6 class="pt-4"><strong class="order_shipment_color mb-2">Ordered Product Details:</strong></h6>
       @if (isset($editData->orderProductDetails))
       <div class="full-card show">
        <table class="table table-responsive">
         <thead class="thead-light">
          <tr>
           <th>Image</th>
           <th>Product Details</th>
         </tr>
        </thead>
   <tbody class="table-body">
    <input type="hidden" id="total_count" value="{{count($editData->orderProductDetails)}}">

    @if (isset($editData->orderProductDetails))
    @foreach ($editData->orderProductDetails as $key=>$row)
    <tr class="new" id='@if (isset($row->product)){{($row->product->product_code)}} @endif ' >
     <input type="hidden" name="product_id[]" value="@if (isset($row->product)){{($row->product->id)}} @endif">

     <td>
      @if (!empty($row->product->image))
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

      <input type="hidden" class="ordered_quantity" name="product_quantity[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='{{$row->quantity}}'>
      <br>
      <strong>Shipment Qty :</strong>
      <input type="number" name="shipment_qty[]" placeholder="0" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 shipment_qty">
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
  <x-button type="reset" color="gray" id="__btnCancelCreateShipment">
    {{ __('translation.Cancel') }}
  </x-button>
  <x-button type="submit" color="blue" id="__btnSubmitCreateShipment">
    {{ __('translation.Create Shipment') }}
  </x-button>
</div>
</form>
@endif

<form action="{{ route('shipment.store') }}" method="post" id="__formCreateShipmentByAddBtn">
    <input type="hidden" name="order_id" id="order_id">
    <input type="hidden" name="byAddBtn" id="byAddBtn" value="1">
    <input name="pending_stock" value="0">
    <input name="shipment_date" value="{{date('d-m-Y')}}">

    <div id="all_ordered_products">
      <div class="order_details_div_pack">
       <h6 class="pt-4"><strong class="order_shipment_color mb-2">Ordered Product Details:</strong></h6>
       @if (isset($editData->orderProductDetails))
       <div class="full-card show">
        <table class="table table-responsive">
         <thead class="thead-light">
          <tr>
           <th>Image</th>
           <th>Product Details</th>
         </tr>
        </thead>
   <tbody class="table-body">


    @if (isset($editData->orderProductDetails))
    @foreach ($editData->orderProductDetails as $key=>$row)
    <tr class="new">
     <input type="hidden" name="product_id_by_add_btn[]" value="@if (isset($row->product)){{($row->product->id)}} @endif">

     <td>
      @if (!empty($row->product->image))
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
      <input type="hidden" class="product_price" value="{{$product_price[$key]['price']}}">
      @endif
      <br>
      <strong>Ordered Qty : </strong>{{$row->quantity}}

      <input type="hidden" class="ordered_quantity" name="product_quantity_by_add_btn[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='{{$row->quantity}}'>
      <br>
      <strong>Shipment Qty :</strong>
      <input type="number" name="shipment_qty_by_add_btn[]" placeholder="0" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 shipment_qty" value="0">
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

</form>
<script type="text/javascript">
    $(document).on('click', '#add_new_shipment', function() {
      $("#__formCreateShipment").show();
      $("#tabs").hide();
      $("#all_shipment_btn").show();
      $("#__btnCancelCreateShipment1").hide();
    });

    $(document).on('click', '#all_shipment_btn', function() {
      $("#all_shipment_btn").hide();
      $("#tabs").show();
      $("#__formCreateShipment").hide();
      $("#__btnCancelCreateShipment1").show();
    });


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
                        order_id = responseData;
                        //let alertMessage = responseData.message;

                        loadStatusList();
                        loadOrderManagementTable(selectedStatusIds);

                        //$('#__modalCreateShipment').doModal('close');

                        $('#__formCreateShipment')[0].reset();
                        $('#__pending_stock_info_wrapperCreateShipment').addClass('hidden');
                        //$('#__shipment_date_wrapperCreateShipment').removeClass('hidden');

                        $('#__btnCancelCreateShipment').attr('disabled', false);
                        $('#__btnSubmitCreateShipment').attr('disabled', false).html(textCreateShipment);

                        $("#order_id").val(order_id);

                        $.ajax({
                          type: 'GET',
                          url: '{{url('getAllOrderedPro')}}',
                          data: {orderId:order_id},
                          beforeSend: function() {
                              $("#modal_content_create_shipment").html("Loading...");
                          },
                          success: function(responseData) {
                              $("#modal_content_create_shipment").html("");
                              $("#modal_content_create_shipment").html(responseData);
                              $('#__order_idCreateShipment').val($("#order_idd").val());
                              $('#__order_id_displayCreateShipment').val(`#${$("#order_idd").val()}`);
                              $("#order_id").val(order_id);
                          },
                          error: function(error) {

                          }
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

                        } else {
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

            $('input[name="pending_stock"]').on('change', function() {
                $('#__pending_stock_info_wrapperCreateShipment').addClass('hidden');
                $('#__shipment_date_wrapperCreateShipment').removeClass('hidden');

                if ($(this).val() == waitingForStockYes) {
                    $('#__shipment_date_wrapperCreateShipment').addClass('hidden');
                    $('#__pending_stock_info_wrapperCreateShipment').removeClass('hidden');
                };
            });

            $('#__btnCancelCreateShipment').on('click', function() {
                $('.alert').addClass('hidden');
                $('#__alertDangerContentCreateShipment').html(null);

                $('#__modalCreateShipment').doModal('close');
            });

            $('#__btnCancelCreateShipment1').on('click', function() {
                $('.alert').addClass('hidden');
                $('#__alertDangerContentCreateShipment').html(null);

                $('#__modalCreateShipment').doModal('close');
            });


             $('#AddNewShipment').on('click', function(event) {
                event.preventDefault();
                const shipmentStoreUrl = "{{ route('shipment.store') }}";
                const formData = new FormData($("#__formCreateShipmentByAddBtn")[0]);
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
                        order_id = responseData;
                        let alertMessage = responseData.message;
                        loadStatusList();
                        loadOrderManagementTable(selectedStatusIds);

                        //$('#__modalCreateShipment').doModal('close');

                        $('#__formCreateShipmentByAddBtn')[0].reset();
                        //$('#__pending_stock_info_wrapperCreateShipment').addClass('hidden');
                        //$('#__shipment_date_wrapperCreateShipment').removeClass('hidden');

                        $('#__btnCancelCreateShipment').attr('disabled', false);
                        $('#__btnSubmitCreateShipment').attr('disabled', false).html(textCreateShipment);
                        $("#order_id").val(order_id);

                        $.ajax({
                          type: 'GET',
                          url: '{{url('getAllOrderedPro')}}',
                          data: {orderId:order_id},
                          beforeSend: function() {
                              $("#modal_content_create_shipment").html("Loading...");
                          },
                          success: function(responseData) {

                              $("#modal_content_create_shipment").html("");
                              $("#modal_content_create_shipment").html(responseData);
                              $('#__order_idCreateShipment').val($("#order_idd").val());
                              $('#__order_id_displayCreateShipment').val(`#${$("#order_idd").val()}`);
                              $("#order_id").val(order_id);
                          },
                          error: function(error) {

                          }
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

                        } else {
                            $('#__alertDangerContentCreateShipment').html(responseJson.message);
                        }

                        $('#__alertDangerCreateShipment').removeClass('hidden');
                    }
                });

                return false;
            });

</script>




