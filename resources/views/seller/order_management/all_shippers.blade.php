@if(count($shippers)>0)
<label for="email"><strong>Shipping Methods:</strong></label><br>
<table class="table table-borderless row">
  <thead>
    <tr>
      <th scope="col">Name</th>
      <th scope="col">Cost</th>
      <th scope="col">Enabled</th>
    </tr>
  </thead>
  <tbody>
    @foreach($shippers as $shipper)
    <tr>
      <td>{{$shipper->name}}</td>
      <td>
        <font class="shipping_current_pric_{{$shipper->id}}">฿{{$shipper->price}}</font>
      <a class="discount_shipping_cost sc_{{$shipper->id}}" onclick="discount_shipping_cost('{{$shipper->price}}/{{$shipper->id}}');">
        <strong style="cursor: pointer;">Discount</strong>
      </a>
      <span class="bold_asif discount_shipping_price"></span>

      <input type="hidden" name="shipping_cost_id[]" value="{{$shipper->id}}">
      <input type="hidden" name="price[]" value="{{$shipper->price}}">
      <input type="hidden" class="shippingDiscountInputField" id='shipping_product_discount_{{$shipper->id}}' name="shipping_product_discount[]">

      <script type="text/javascript">
        
          function discount_shipping_cost(price){
            
            $("#shipping_discount_price_modal").modal('show');
            $("#shipping_discount_price").val(null);
            $("#shipping_current_price").val(null);
            var price_code = price.split("/");
            $("#shipping_current_price").val(price_code[0]);
            $("#shipping_current_product").val(price_code[1]);
          }

          function add_shipping_discount_price(){

            var shipping_current_price = $("#shipping_current_price").val();
            var shipping_discount_price_amount = $("#shipping_discount_price").val();
            var shipping_current_product = $("#shipping_current_product").val();

            if(Number(shipping_discount_price_amount) > Number(shipping_current_price) || Number(shipping_discount_price_amount) === Number(shipping_current_price)){
              alert("you need to put less amount than current price");
            }
            else{
              $(".sc_"+shipping_current_product).text('Discount : ฿'+shipping_discount_price_amount);

              $(".shipping_current_pric_"+shipping_current_product).addClass('line_throuh');
              $("#shipping_product_discount_"+shipping_current_product).val(shipping_discount_price_amount);
              $("#shipping_discount_price_modal").modal('hide');
              //calculateDiscount();
            }

          }
       
      </script>
      </td>
      <td>
        <select name="enable_status[]" id="enable_status" class="custom-select custom-select-sm form-control form-control-sm col-xs-6">
          <option value="1">Yes</option>
          <option value="0">No</option>
        </select>
      </td>
    </tr>
    @endforeach  
  </tbody>
</table>

 <!-- <div class="form-group">
  <label for="email"><strong>Shipping Methods:</strong></label><br>
  @foreach($shippers as $shipper)
  <input type="radio" class="shipping_type" name="shipping_methods" value="{{$shipper->id}}" id="checkin-{{$shipper->id}}"  @if(isset($editData)) data-price={{$editData->price}}  @else data-price={{$shipper->price}}  @endif  @if(isset($editData) && $editData->shipping_methods == $shipper->id) Checked='true' @endif> &nbsp;

  {{$shipper->shippers_name}} ({{$shipper->name}} ) - <strong>฿{{$shipper->price}} </strong>
  <br>
  @endforeach  
  @else
</div> -->
<div class="form-group">
    <label for="email"><strong>Shipping Methods:</strong></label><br>
    <p>No Shipping Method</p>
</div>
@endif

<script type="text/javascript">

  $( document ).ready(function() {
      $('.shipping_type').on('change',function(){
    let shipping_cost = $(this).data('price');


    if($("#in_total").val()>0){
      $('#shipping_cost').val(shipping_cost);
      $('.shipping_cost_text').text(`฿ ${Number(shipping_cost).toFixed(2)}`);
      calculateTotals_shippers();
    }
     });


    function calculateTotals_shippers() {
    const subtotals = $('.new').map((idx, val) => calculateSubtotal_shippers(val)).get();
    const total = subtotals.reduce((a, v) => a + Number(v), 0);

    const subDiscounts = $('.new').map((idx, val) => calculateSubDiscount_shippers(val)).get();
    const totalDiscounts = subDiscounts.reduce((a, v) => a + Number(v), 0);

    const weights = $('.new').map((idx, val) => calculateWeight_shippers(val)).get();
    const totalWeights = weights.reduce((a, v) => a + Number(v), 0);

    if(totalWeights>0){
      $.ajax
        ({ 
          type: 'GET',
          data: {totalWeights:totalWeights},
          url: '{{url('get_all_shipping_methods')}}',
          success: function(result)
          {
            var checked_value = $('input[name="shipping_methods"]:checked').val();
            if(checked_value > 0){

            }
            else{
              $('.shipping_metthod_wrapper').html(result);
            }
            
            
          }
      });
    }

    $('#sub_total ').val(total);
    $('#total_discount').val(totalDiscounts);
    $('.sub_total_text').text(formatAsCurrency_shippers(total));
    $('.total_discount_text').text(formatAsCurrency_shippers(totalDiscounts));
    let shippingCost = $('#shipping_cost').val();
    if(shippingCost !== ''){
              // in_total = parseInt(total) + parseInt(shippingCost);
              in_total = Number(total) + Number(shippingCost);
              $('#in_total').val(in_total);
              $('.in_total_text').text(formatAsCurrency_shippers(in_total));
            }else{
              in_total = Number(total);
              $('#in_total').val(in_total);
              $('.in_total_text').text(formatAsCurrency_shippers(in_total));
            }

            if(totalDiscounts){
              in_total = $('#in_total').val() - totalDiscounts;
              $('#in_total').val(in_total);
              $('.in_total_text').text(formatAsCurrency_shippers(in_total));
            }

        }

        function calculateSubtotal_shippers(row) {
          const $row = $(row);
          const input = $row.find('.product_need').val();
          const input1 = $row.find('.product_price').val();
          const subtotal = input * input1;
            // $row.find('td:last').text(formatAsCurrency(subtotal));
            return subtotal;
          }

           function calculateSubDiscount_shippers(row){
              const $row = $(row);
              const input = $row.find('.discountInputField').val();
              const input1 = $row.find('.product_price').val();
        
               if(input.length != 0){
                  const product_need = $row.find('.product_need').val();
                  const subtotalDiscount = (input1 - input)*product_need;
                  return subtotalDiscount;
               }
          }

          function calculateWeight_shippers(row) {
          const $row = $(row);
          const input = $row.find('.weight').val();
          if(input > 0){
            return input;
          }
          else{
             return 0;
          }
         
          }

    function formatAsCurrency_shippers(amount) {
            return `฿ ${Number(amount).toFixed(2)}`;
    }

  });
  
</script>