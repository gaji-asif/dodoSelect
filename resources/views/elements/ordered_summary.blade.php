 <div class="modal-content">
        <div class="modal-header">

          <h5 class="modal-title"><strong>Your order created successfully</strong></h5>
         <!--  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button> -->
        </div>
        <div class="modal-body">
          <div class="row" style="padding: 20px;">
            <div class="card mb-3 mt-3 width_100">
              <div class="card-body">
                <div class="form-group">
                  <label for="email" class="margin_bottom_10"><strong>Order ID: #</strong>
                  	 @if(isset($orders_details))
                    {{$orders_details->id}}
                    @endif
                  </label><br>

                  Sub Total: <font style="text-align: right; float: right;" class="pull-right " >฿

                    @if(isset($orders_details))
                    {{$orders_details->sub_total}}.00
                    @endif
                    </font><br>


                  Shipping Cost: <font style="text-align: right; float: right;" class="pull-right ">฿ 
                  	@if(isset($orders_details))
                    {{$orders_details->shipping_cost}}.00
                    @endif
                  </font>

                  <p style="margin-bottom: 15px;">Total Discount: <font style="text-align: right; float: right;" class="pull-right ">฿ 
                    @if(isset($orders_details))
                    {{$orders_details->total_discount}}.00
                    @endif
                  </font></p>

                  
                  <p><strong>In Total:</strong> <font style="text-align: right; float: right; font-weight: bold;" class="pull-right ">฿ 
                  	@if(isset($orders_details))
                    {{$orders_details->in_total}}.00
                    @endif
                  </font></p>
                  
                </div> 
              </div>
            </div>
          </div>

          <div class="row" style="padding: 20px;">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th scope="col">Image</th>
                  <th scope="col">Product Name</th>
                  <th scope="col">Code</th>
                  <th scope="col">Quantity</th>
                </tr>
              </thead>
              <tbody>
                @if(isset($ordered_product_details))
                @foreach($ordered_product_details as $value)
                <tr>
                	@php
                	$image_loc = asset($value->image);
                    $no_image = asset('img/No_Image_Available.jpg');
                    @endphp
                    @if(!empty($value->image))
                     <th scope="row"><img src="{{$image_loc}}" width="70" height="70"></th>
                    @else
                      <th scope="row"><img src="{{$no_image}}" width="70" height="70"></th>

                    @endif
                 
                  <td>{{$value->product_name}}</td>
                  <td>{{$value->product_code}}</td>
                  <td>{{$value->quantity}}</td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>

          <div class="row" style="margin: 0 auto;">
          	<a class="btn btn-primary col-sm-4" id="BtnUpdate" data-id="@if(isset($orders_details)){{$orders_details->id}}@endif">Edit Orders</a>
            <a class="btn btn-success col-sm-4" id="BtnUpdate" href="{{ url('order_management') }} ">All Orders</a>
          	<a class="btn btn-warning col-sm-4">Share Links</a>
          </div>
        </div>
      </div>



<script type="text/javascript">
   $(document).on('click', '#BtnUpdate', function() {

        //alert('Row index: ' + $(this).closest('tr').index());
        //$('.modal-producut').removeClass('modal-hide');
        // alert($(this).attr('data-id'));
        var order_id = $(this).attr('data-id');
        var url = "{{ url('/order_management/') }}";
        //alert(url+order_id+'/edit');
        window.location.href = url+'/'+order_id+'/edit';
        // order_management/'.$row->id.'/edit

    });
</script>

<script type="text/javascript">
        $('#ordered_summary').modal({
            backdrop: 'static',
            keyboard: false
        })
    </script>