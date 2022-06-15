<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
  <form action="{{ route('shipment.updateForCustomShipment') }}" method="post" id="__formCreateShipment">
    <input type="hidden" name="order_id" id="__order_idCreateShipment" value="{{$editData->id}}">
    <input type="hidden" name="for_edit" id="for_edit" value="1">
    <input type="hidden" name="shipment_id" id="shipment_id" value="{{$shipmentDetails->id}}">
    <div class="mb-2">
      <div class="grid grid-cols-1 gap-4">
        <div class="row">
          <div class="col-lg-4">
            <x-label for="__order_idCreateShipment">
              {{ __('Order ID') }} <x-form.required-mark/>
            </x-label>
            <x-input type="text" value="{{$editData->id}}" id="__order_id_displayCreateShipment" class="bg-gray-200" readonly />
          </div>
          <div class="col-lg-4 margin_top_mb">
            <x-label class="" for="__pending_stockCreateShipment">
              Select Status <x-form.required-mark/>
            </x-label>
            <div class="form-group">
                  <x-select class="form-control relative top-1" id="shipment_status_for_edit" name="shipment_status">
                      <option value="{{ \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK }}" @if($shipmentDetails->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_PENDING_STOCK) selected @endif>Wait For Stock</option>
                      <option value="{{ \App\Models\Shipment::SHIPMENT_STATUS_READY_TO_SHIP }}" @if($shipmentDetails->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_READY_TO_SHIP) selected @endif>Ready to Ship</option>
                      <option value="{{ \App\Models\Shipment::SHIPMENT_STATUS_SHIPPED }}" @if($shipmentDetails->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_SHIPPED) selected @endif>Shipped</option>
                      <option value="{{ \App\Models\Shipment::SHIPMENT_STATUS_CANCEL }}" @if($shipmentDetails->shipment_status == \App\Models\Shipment::SHIPMENT_STATUS_CANCEL) selected @endif>Cancelled</option>
                  </x-select>
             </div>
        </div>
        <div class="col-lg-4" id="__shipment_date_wrapperCreateShipment">
          <x-label for="__shipment_dateCreateShipment">
            {{ __('Shipment Date') }}
          </x-label>
          @if(isset($shipmentDetails->shipment_date))
          <x-input type="text" name="shipment_date" id="__shipment_dateCreateShipment" value="{{date('d-m-Y', strtotime($shipmentDetails->shipment_date))}}"></x-input>
          @else
          <x-input type="text" name="shipment_date" id="__shipment_dateCreateShipment" value=""></x-input>
          @endif
          
        </div>
      </div>
      <div id="__pending_stock_info_wrapperCreateShipment" class="hidden">
        <p class="text-yellow-600">
          The status of the this order will change to <span class="font-bold">Waiting for Stock</span>.
        </p>
      </div>
    </div>
  </div>
  <div class="row mb-2">
     @if (isset($editData->orderProductDetails))
      <div class="col-lg-7 margin_top_mb">
            <x-label class="">
              <strong>Select Product</strong> <x-form.required-mark/>
            </x-label>
            <div class="form-group padding_top_1">
             <x-select class="form-control" id="product_list_edit" name="product_id">
              <option value="">Select Product</option>
              @foreach($editData->orderProductDetails as $key=>$row)
              <option value="{{$row->product->id}}">{{$row->product->product_name}}</option>
              @endforeach
            </x-select>

          </div>
        </div>
     @endif
  </div>
  <div id="ordered_products">
     <table class="table table-responsive">
       <thead class="thead-light">
        <tr>
         <th>Image</th>
         <th>Product Details</th>
       </tr>
     </thead>
     <tbody class="table-body new_products_table" id="new_products_table_for_edit">
      @if(isset($getShipmentsProductsDetails))
      @foreach($getShipmentsProductsDetails as $value)
      <tr class="new" id="product_{{($value->id)}}">
        <input type="hidden" name="product_id[]" value="{{($value->id)}}">

        <td>
          @if (Storage::disk('s3')->exists($value->image) && !empty($value->image))
          <img src="{{Storage::disk('s3')->url($value->image)}}" height="80" width="80" alt="">
          @else
          <img src="{{Storage::disk('s3')->url('uploads/No-Image-Found.png')}}" height="80" width="80"  alt="">
          @endif

        </td>
        <td>
          <p class="mb-1">
           {{($value->product_name)}}
          </p>
          <p class="mb-1">
          <strong>{{__('translation.code')}} :</strong>
          {{($value->product_code)}}
          </p>
          <p class="mb-1">
          <strong>{{__('translation.Price')}} :</strong>
          
           ฿{{$value->price}}
          <input type="hidden"class="product_price" value="{{$value->price}}">
          
          </p>
          <div class="row">
            <div class="col-lg-4 col-sm-12">
              <strong>{{__('translation.Shipment Qty')}} : </strong> 
              <input type="text" id="shipment_qty_{{$value->id}}" name="shipment_qty[]" data-id="{{$value->id}}" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 shipment_qty" value="{{$value->quantity}}">
             </div>
             <div class="col-lg-4 col-sm-12">
              <button type="button" class="btn btn-danger font_size_12 margin_top_re" onclick="removeProductWrapperForEdit('{{$value->id}}');">
                REMOVE
              </button>
             </div>
          </div>
        </td>
      </tr>
      @endforeach 
      @else
      <tr class="no_product_wrapper">
         <td colspan="2" class="text-center">
         {{__('translation.No added Products Yet')}}
         </td>
       </tr>
      @endif 
       
     </tbody>
  </table>
  </div>
<div class="pb-5 text-center">
  <x-button type="reset" color="gray" id="__btnCancelEditCustomShipmentForOrder">
    {{ __('Cancel') }}
  </x-button>
  <x-button type="submit" color="blue" id="__btnSubmitCustomShipmentForOrder">
    {{ __('translation.Update Shipment') }}
  </x-button>
</div>
</form>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript">
      $('#__btnSubmitCustomShipmentForOrder').on('click', function(event) {
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
                    $('#__alertDangerContentCreateCustomShipmentForOrder').html(null);


                  },
                  success: function(responseData) {
                    console.log(responseData);
                    
                        let alertMessage = responseData.message;
                        $('#__modalCreateCustomShipmentForOrder').doModal('close');

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

                        if(responseData){
                              var order_id = $("#__order_id_displayCreateShipment").val();
                              $("#custom_shipment_details_wrapper").html("");
                              $.ajax({
                              type: 'GET',
                              data: {
                                  order_id: order_id
                              },
                              url: '{{ url('getCustomShipmentDetailsData') }}',
                              beforeSend: function() {
                               $("#custom_shipment_details_wrapper").html("loading ......");   
                              },
                              success: function(result) {
                                $("#custom_shipment_details_wrapper").html(result);
                              },
                              error: function() {
                                  alert('something went wrong');
                              }
                          });
                        }

                      },

                      error: function(error) {
                        let responseJson = error.responseJSON;
                         if (error.status == 422) {
                          let errorFields = Object.keys(responseJson.errors);
                          errorFields.map(field => {
                            $('#__alertDangerContentCreateCustomShipmentForOrder').append(
                              $('<span/>', {
                                class: 'block mb-1',
                                html: `- ${responseJson.errors[field][0]}`
                              })
                              );
                          });

                        } else {
                          $('#__alertDangerContentCreateCustomShipmentForOrder').html(responseJson.message);
                        }

                        $('#__alertDangerCreateCustomShipmentForOrder').removeClass('hidden');
                      }
                    });

                return false;
              });

  $('#__shipment_dateCreateShipment').datepicker({
    dateFormat: 'dd-mm-yy'
  });

  $(document).on('change', '#shipment_status_for_edit', function() {
    var shipment_status = $("#shipment_status_for_edit").val();
    if(shipment_status === '11'){
      $('#__pending_stock_info_wrapperCreateShipment').addClass('hidden');
      $('#__shipment_date_wrapperCreateShipment').removeClass('hidden');
    }
    if(shipment_status === '10'){
      $('#__shipment_date_wrapperCreateShipment').addClass('hidden');
      $('#__pending_stock_info_wrapperCreateShipment').removeClass('hidden');
    }

  });

  $('#__btnCancelEditCustomShipmentForOrder').on('click', function() {
    $('.alert').addClass('hidden');
    $('#__alertDangerEditCustomShipmentForOrder').html(null);

    $('#__modalEditCustomShipmentForOrder').doModal('close');
  });

  $('#product_list_edit').on('change', function(event) {
    var orderId = $("#id").val();
    let product_id = $("#product_list_edit").val();

    if(product_id){
          $.ajax({
          type: 'GET',
          data: {
              product_id: product_id,
              order_id: order_id
          },
          url: '{{ url('getOrderedProductDetails') }}',
          success: function(result) {
            $(".no_product_wrapper").hide();
            $("#new_products_table_for_edit").append(result);
          },
          error: function() {
              alert('something went wrong');
          }
      });
      }
    })

     
     $('#product_list_edit').select2({
            width: '100%',
            ajax: {
                type: 'GET',
                url: '{{ route('product_list.select') }}',
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                delay: 500
            }
        }); 

      function removeProductWrapperForEdit(product_id){
          if(product_id){
              $("#product_"+product_id).remove();
          }
      }

</script>




