<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
  <form action="{{ route('shipment.storeForWCCustomShipment') }}" method="post" id="__formCreateShipment">
    <input type="hidden" name="order_id" id="__order_idCreateShipment" value="{{$order_id}}">
    <input type="hidden" name="shop_id" id="__shop_idCreateShipment" value="{{$website_id}}">
    <input type="hidden" name="for_edit" id="for_edit" value="0">
    <div class="mb-2">
      <div class="grid grid-cols-1 gap-4">
        <div class="row">
          <div class="col-lg-4">
            <x-label for="__order_idCreateShipment">
              {{ __('Order ID') }} <x-form.required-mark/>
            </x-label>
            <x-input type="text" value="{{$order_id}}" id="__order_id_displayCreateShipment" class="bg-gray-200" readonly />
          </div>
          <div class="col-lg-4 margin_top_mb">
            <x-label class="" for="__pending_stockCreateShipment">
              Select Status <x-form.required-mark/>
            </x-label>
            <div class="form-group padding_top_1">
             <x-select class="form-control" id="shipment_status" name="shipment_status">
              <option value="11">{{__('translation.Ready to Ship')}}</option>
              <option value="10">{{__('translation.Wait For Stock')}}</option>
            </x-select>

          </div>
        </div>
        <div class="col-lg-4" id="__shipment_date_wrapperCreateShipment">
          <x-label for="__shipment_dateCreateShipment">
            {{ __('Shipment Date') }}
          </x-label>
          <x-input type="text" name="shipment_date" id="__shipment_dateCreateShipment" placeholder="DD-MM-YYYY" />
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
     @if (isset($products))
      <div class="col-lg-7 margin_top_mb">
            <x-label class="">
              <strong>Select Product</strong> <x-form.required-mark/>
            </x-label>
            <div class="form-group padding_top_1">
             <x-select class="form-control" id="product_list" name="product_id">
              <option value="">Select Product</option>
              @foreach($products as $product)
              <option value="{{$product->product_id}}">{{$product->product_name}}</option>
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
     <tbody class="table-body new_products_table" id="new_products_table">
       <tr class="no_product_wrapper">
         <td colspan="2" class="text-center">
         {{__('translation.No added Products Yet')}}
         </td>
       </tr>
     </tbody>
  </table>
  </div>
<div class="pb-5 text-center">
  <x-button type="reset" color="gray" id="__btnCancelCustomShipmentForOrder">
    {{ __('Cancel') }}
  </x-button>
  <x-button type="submit" color="blue" id="__btnSubmitCustomShipmentForOrder">
    {{ __('Create Shipment') }}
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
                              var website_id = $("#__shop_idCreateShipment").val();
                              $("#custom_shipment_details_wrapper").html("");
                              $.ajax({
                              type: 'GET',
                              data: {
                                website_id: website_id,
                                  order_id: order_id
                              },
                              url: '{{ url('getWCCustomShipmentDetailsData') }}',
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

  $(document).on('change', '#shipment_status', function() {
    var shipment_status = $("#shipment_status").val();
    if(shipment_status === '11'){
      $('#__pending_stock_info_wrapperCreateShipment').addClass('hidden');
      $('#__shipment_date_wrapperCreateShipment').removeClass('hidden');
    }
    if(shipment_status === '10'){
      $('#__shipment_date_wrapperCreateShipment').addClass('hidden');
      $('#__pending_stock_info_wrapperCreateShipment').removeClass('hidden');
    }

  });

  $('#__btnCancelCustomShipmentForOrder').on('click', function() {
    $('.alert').addClass('hidden');
    $('#__alertDangerContentCustomShipment').html(null);

    $('#__modalCreateCustomShipmentForOrder').doModal('close');
  });

  $('#product_list').on('change', function(event) {
    var orderId = $("#id").val();
    let product_id = $("#product_list").val();
    var website_id = $("#__shop_idCreateShipment").val();
    if(product_id){
          $.ajax({
          type: 'GET',
          data: {
              product_id: product_id,
              order_id: order_id,
              website_id : website_id
          },
          url: '{{ url('getWOOProductDetails') }}',
          success: function(result) {
            $(".no_product_wrapper").hide();
            $("#new_products_table").append(result);
          },
          error: function() {
              alert('something went wrong');
          }
      });
      }
    })

     
     $('#product_list').select2({
            width: '100%',
            ajax: {
                type: 'GET',
                url: '{{ route('WCproduct_list.select') }}',
                data: function(params) {
                    return {
                        website_id: $("#__shop_idCreateShipment").val(),
                        search: params.term,
                        page: params.page || 1
                    };
                },
                delay: 500
            }
        }); 

      function removeProductWrapper(product_id){
          if(product_id){
              $("#product_"+product_id).remove();
          }
      }

</script>




