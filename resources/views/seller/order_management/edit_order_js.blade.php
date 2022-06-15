<script>
  $(document).ready(function() {

    $('.js-example-basic-single2').select2({
      placeholder: "Select A Shipping Address",
      allowClear: true,
      async:true
    });

    $('.js-example-basic-single3').select2({
      placeholder: "Select A Sub Address",
      allowClear: true,
      async:true
    });
  });

 // open discount modal
 function discount_pricess(product_code){

    $("#discount_price_modal").modal('show');
    $("#discount_price").val(null);
   
    var product_orginal_price = $("#product_price_"+product_code).val();
    var product_discount_price = $("#product_discount_"+product_code).val();

    if(product_orginal_price > 0){
      $("#current_price").val(product_orginal_price);
      $("#current_product").val(product_code);
    }

    if(product_discount_price > 0){

        $("#current_price").val(product_orginal_price);
        $("#discount_price").val(product_discount_price);
        $("#current_product").val(product_code);

    }

}

 // add discount price from modal
 function add_discount_price(){

  var current_price = $("#current_price").val();
  var discount_price_amount = $("#discount_price").val();
  var current_product = $("#current_product").val();


  if(Number(discount_price_amount) > Number(current_price) || Number(discount_price_amount) === Number(current_price)){
    alert("you need to put less amount than current price");
  }
  else{

   $("#edit_discount_show_"+current_product).text('');
   $(".pr_"+current_product).text('Discount :  ฿'+discount_price_amount);

    $(".current_pric_"+current_product).addClass('line_throuh');
    $("#product_discount_"+current_product).val(discount_price_amount);
    $("#discount_price_modal").modal('hide');
    calculateDiscount();
  }

}

function calculateDiscount() {
  const discount = $('.new').map((idx, val) => calculateTotalDiscount(val)).get();
  const total = discount.reduce((a, v) => a + Number(v), 0);

  in_total = $('#in_total').val() - total;
  $('#in_total').val(in_total);
  $('.in_total_text').text('฿ '+Number(in_total).toFixed(2));

  $('.total_discount_text').text('฿ '+Number(total).toFixed(2));
  $('#total_discount').val(Number(total));
}

function calculateTotalDiscount(row) {
        const $row = $(row);
        const input = $row.find('.discountInputField').val();
        const input1 = $row.find('.product_price').val();
        
         if(input.length != 0){
            const product_need = $row.find('.product_need').val();
            const subtotalDiscount = (input1 - input)*product_need;
            return subtotalDiscount;
         }
       
      }




$(document).ready(function() {
    // console.log('jquery is working');
    $('body').on('click','.tt-suggestion',function(){
      $('.qr-code1').keyup();
    });
    $('.qr-code1').keyup(function(event) {
      // alert("asif");
                 // console.log(event);
                 var currentUrl = window.location.origin;
                 var shop_id = $('.shop_id').val();
                 event.preventDefault();

                 if (event.keyCode !== 13) {
                  if($(this).val() !== "" )
                  {
                    $.ajax({
                      type: 'GET',
                      data: {product_code:$(this).val(),from:1},
                // async:false,
                url: '{{route('get_qr_code_product_order_purchase')}}',
              })

                    .done(function(data) {

                      console.log(data);
                      if(data.product_code !== '')
                      {
                    //$('.qr-code1').val('');

                    if(!!data.price && !!data.product_code){
                      var price_code = data.price+'/'+data.product_code;
                    }
                    else{
                      var price_code = 0;
                    }
                    
                    //alert(price_code);
                    

                    let table = $('.full-card');
                    if($(table).hasClass('hide'))
                    {
                      $(table).removeClass('hide');
                      $(table).addClass('show');

                    }  


                    let tableBody = $('.table-body');
                    let product_name = data.product_name; 
                    if(data.product_name === null )
                    {
                      product_name = '';
                    }

                    let product_image = data.image; 
                    if(data.image === null )
                    {
                      product_image = 'No-Image-Found.png';
                    }

                  inputform = `<input type="number" class="order_quantity product_need" name="product_quantity[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='1'>`;

                    discountInput = `<input type="hidden" class="discountInputField" id='product_discount_${data.product_code}' name="product_discount[]" style="width:62px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1">`;

                    weightInput = `<input type="hidden" class="weight" value="${data.weight}" id='weight_${data.product_code}' name="weight[]" style="width:62px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1">`;

                    $data = `
                    <tr class="new" id='${data.product_code}' >

                    <input type="hidden" name="product_id[]" value="${data.id}">
                    <input type="hidden" class="product_code" name="product_code[]" value="${data.product_code}">

                    <input type="hidden"class="product_price" value="${data.shop_price}">
                    <td width="90px;"><img height="70" width="70" src="${currentUrl}/public/${product_image}" class="cutome_image_new" alt="">
                    </td>

                    <td>
                    <font class="bold_asif">${product_name}</font>
                    <br>
                    <font class="bold_asif">Code:</font> ${data.product_code}
                    <br>
                    <span class="bold_asif">Price: </span>
                    <span class="current_pric_${data.product_code}">฿${data.shop_price}</span>&nbsp;&nbsp;
                    <span>
                    <a class="discount_prices pr_${data.product_code}" onclick="discount_pricess('${price_code}');">
                    <strong>Add Discount</strong>
                    
                    </a>
                    </span>
                    ${discountInput}
                    <br>
                    <span class="bold_asif discount_price"></span>
                    <span class="bold_asif">Available Qty:</span> ${data.get_quantity.quantity}
                    <br>
                    ${inputform}
                    ${weightInput}
                    
                    <button type="button" class="btn btn-sm btn-danger" data-product_code="${data.product_code}" data-shop_id=""  >Remove</button>
                    </td>



                    </tr>`;
                    tableBody.append($data);
                    calculateTotals();
                    checkShippingMethods();
                          // console.log(data.product_name);
                        }

                        $('.qr-code1').val('');


                      })
                // .fail(function() {
                //     console.log("error");
                // })
                // .always(function() {
                //     console.log("complete");
                // });
              }
            }

          });



$(document).ready(function(){
  checkShippingMethods();
});




function checkShippingMethods(){

// alert($("#in_total").val());
// console.log($("#in_total").val());
if($("#in_total").val() === '0'){

       // alert("empty");

       $(".shipping_methods_wrapper_not").show();
       $(".shipping_methods_wrapper").hide();
     }

     if($("#in_total").val() > 0){



      $(".shipping_methods_wrapper_not").hide();
      $(".shipping_methods_wrapper").show();
    }

  }


  $('.shipping_type').on('change',function(){
    let shipping_cost = $(this).data('price');

    if($("#in_total").val()>0){
      $('#shipping_cost').val(shipping_cost);
      $('.shipping_cost_text').text(`฿ ${Number(shipping_cost).toFixed(2)}`);
      calculateTotals();
    }
      // else{
      //   alert("you must to select a product first.");
      //   this.checked = false;
      // }

    });

  // $('table').on('mouseup keyup', 'input[type=number]', () => calculateTotals());

  $('table').on('mouseup keyup', 'input[type=number]', function(){
    calculateTotals();
      //alert("asif");

      $('.qr-code1').val('');
    });

  $('table').on('mouseup keyup', function(){
    $('.qr-code1').val('');
  });





// $('table').on('mouseup keyup', '.discountInputField', function(){

//         var sub = $('#total_discount').val();

//         $(".total_discount_text").each(function() {




//             var subtotal = $(this).val();
//             //$(this).find(".total").val(subtotal);

//             if(!isNaN(subtotal))
//             sub+=subtotal;

//         });
//         $(".total_discount_text").text(sub);
//         console.log(sub);


//   });

function calculateTotals() {
  const subtotals = $('.new').map((idx, val) => calculateSubtotal(val)).get();
  const total = subtotals.reduce((a, v) => a + Number(v), 0);



  const subDiscounts = $('.new').map((idx, val) => calculateSubDiscount(val)).get();
  const totalDiscounts = subDiscounts.reduce((a, v) => a + Number(v), 0);

  const weights = $('.new').map((idx, val) => calculateWeight(val)).get();
  const totalWeights = weights.reduce((a, v) => a + Number(v), 0);

  if(totalWeights>0){

      $('#total_product_weight').val(totalWeights);
      $.ajax
        ({ 
          type: 'GET',
          data: {totalWeights:totalWeights},
          url: '{{url('get_all_shipping_methods')}}',
          success: function(result)
          {
            console.log(result);
            $('.shipping_metthod_wrapper').html('');
            $('.shipping_metthod_wrapper').html(result);
            
          }
      });
    }

  $('#sub_total ').val(total);
  $('#total_discount').val(totalDiscounts);
  $('.sub_total_text').text(formatAsCurrency(total));
  $('.total_discount_text').text(formatAsCurrency(totalDiscounts));
  let shippingCost = $('#shipping_cost').val();
  if(shippingCost !== ''){
              // in_total = parseInt(total) + parseInt(shippingCost);
              in_total = Number(total) + Number(shippingCost);
              $('#in_total').val(in_total);
              $('.in_total_text').text(formatAsCurrency(in_total));
            }else{
              in_total = Number(total);
              $('#in_total').val(in_total);
              $('.in_total_text').text(formatAsCurrency(in_total));
            }

            if(totalDiscounts){
              in_total = $('#in_total').val() - totalDiscounts;
              $('#in_total').val(in_total);
              $('.in_total_text').text(formatAsCurrency(in_total));
            }





          }

          function calculateSubtotal(row) {
            const $row = $(row);
            const input = $row.find('.product_need').val();

            const input1 = $row.find('.product_price').val();
            const subtotal = input * input1;

            //alert('quantity'+input);
            //alert('product_price'+input1);

            // $row.find('td:last').text(formatAsCurrency(subtotal));
            return subtotal;
          }

          function calculateSubDiscount(row){
            const $row = $(row);
            const input = $row.find('.discountInputField').val();
            const input1 = $row.find('.product_price').val();

            if(input.length != 0){
              const product_need = $row.find('.product_need').val();
              const subtotalDiscount = (input1 - input)*product_need;
              return subtotalDiscount;
            }
          }

        function calculateWeight(row) {
          const $row = $(row);
          const input = $row.find('.weight').val();
          const input_count = $row.find('.product_need').val();
          total = input*input_count;

          if(total > 0){
            return total;
          }
          else{
             return 0;
          }
         
          }

          function formatAsCurrency(amount) {
            return `฿ ${Number(amount).toFixed(2)}`;
          }




          $("body").on("click",".btn-danger",function(){ 
            let product_code = $(this).data('product_code');
            // let shop_id = $(this).data('shop_id');

            $main_node = $(this).parents(".new");
            const input = $main_node.find('.product_need').val()
            const input1 = $main_node.find('.product_price').val();
            const discount_price = $main_node.find('.discountInputField').val();

            const subtotal = input * input1;
            if(discount_price>0){

              var total_discount_price = (input1 - discount_price)*input;

            }
            else{
              var total_discount_price = 0;
            }


            
            let total = $('#sub_total').val(); 
            let total_discount = $('#total_discount').val();
            var final_discount = total_discount - total_discount_price;
            // alert(final_discount);
            $('#total_discount').val(final_discount);
            $('.total_discount_text').text('฿ '+Number(final_discount).toFixed(2));

            total = total - subtotal;
            $('#sub_total ').val(total);
            $('.sub_total_text').text(formatAsCurrency(total));


            let in_total =  $('#in_total').val();
            in_total =  in_total - subtotal;
            $('#in_total').val(in_total);
            $('.in_total_text').text(formatAsCurrency(in_total));

            if(total_discount_price){
              //alert(in_total);
              //alert(total_discount_price);
              in_totals =  in_total + total_discount_price;
              $('#in_total').val(in_totals);
              $('.in_total_text').text(formatAsCurrency(in_totals));
            }

            // start for Shipping methods calculation for Remove
            const product_weight = $main_node.find('.weight').val();
            const signle_product_total_weight = product_weight*input;
            var total_product_weight = $("#total_product_weight").val();
            if(total_product_weight > 0){
              weight_after_remove = total_product_weight - signle_product_total_weight;
              $("#total_product_weight").val(weight_after_remove);

               $.ajax
                      ({ 
                        type: 'GET',
                        data: {totalWeights:weight_after_remove},
                        url: '{{url('get_all_shipping_methods')}}',
                        success: function(result)
                        {
                          $('.shipping_metthod_wrapper').html(result);
                          
                        }
                    });
            }
            // End for Shipping methods calculation for Remove

            $(this).parents(".new").remove();

            let item = $('.btn-danger');

            if(item.length <= 0)
            {
              let table = $('.full-card');
              if($(table).hasClass('show'))
              {
                $(table).removeClass('show');
                $(table).addClass('hide');
              }  
            }
            $.ajax({
              type: 'GET',
              data:{product_code:product_code},
              url: '{{route('delete_session_product2')}}',    
            }).done(function(data) {

            })

          });

        });
      </script>

      <script>
        $(document).ready(function() {


          $("body").on('submit','.inout-form',function (event) {
            var frm = $('.inout-form');
            event.preventDefault();
              // e.stopImmediatePropagation();
              if (event.keyCode === 13) {
                event.preventDefault();
                $.ajaxSetup({
                  headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
                });
                $.ajax({
                  type: frm.attr('method'),
                  url: frm.attr('action'),
                  data: frm.serialize(),
                  success: function (data) {
                    if($data)
                    {
                      if($('.alert-message').hasClass('hide'))
                      {
                        $('.alert-message').removeClass('hide')
                        $('.alert-message').addClass('show')
                      }
                      // $('.shop_id').val(null).trigger('change');
                      $(".table-body").html('');
                      let table = $('.full-card');
                      if($(table).hasClass('show'))
                      {
                        $(table).removeClass('show');
                        $(table).addClass('hide');

                      }  
                    }
                              // console.log(data);
                            },
                            error: function (data) {
                              // console.log('An error occurred.');
                              // console.log(data);
                            },
                          });
              }
            });

          $("body").on('click','.reset-button',function () {

            $(".table-body").html('');  $('.qr-code1').val('');
            // $('.shop_id').val(null).trigger('change');


            let shipping_cost = $('#shipping_cost').val(); 
            let total = 0;
            $('#sub_total').val(total);
            $('.sub_total_text').text(formatAsCurrency(total));

            $('#shipping_cost').val(total);
            $('.shipping_cost_text').text(formatAsCurrency(total));

            $('#in_total').val(total);
            $('.in_total_text').text(formatAsCurrency(total));

            
            $(".total_discount_text").text(formatAsCurrency(0));
            $(".total_discount").val(0);

            function formatAsCurrency(amount) {
              return `฿ ${Number(amount).toFixed(2)}`;
            }
            let table = $('.full-card');
            if($(table).hasClass('show'))
            {
              $(table).removeClass('show');
              $(table).addClass('hide');

            }  

            $.ajax({
              type: 'GET',
              url: '{{route('reset_session_product')}}',    
            }).done(function(data) {})
          });


        });
      </script>

      <script>
        $(document).ready(function(){
          var availableTags = {!! $products !!}

          var substringMatcher = function(strs) {
            return function findMatches(q, cb) {
              var matches, substringRegex;

    // an array that will be populated with substring matches
    matches = [];

    // regex used to determine if a string contains the substring `q`
    substrRegex = new RegExp(q, 'i');

    // iterate through the pool of strings and for any string that
    // contains the substring `q`, add it to the `matches` array
    $.each(strs, function(i, str) {
      if (substrRegex.test(str)) {
        matches.push(str);
      }
    });

    cb(matches);
  };
};

$('#the-basics .typeahead').typeahead({
  hint: true,
  highlight: true,
  minLength: 1
},
{
  name: 'states',
  cache: false,
  source: substringMatcher(availableTags)
}); 
});

// $(document).keypress(
//   function(event){
//     if (event.which == '13') {
//       event.preventDefault();
//     }
// });


$(document).ready(function(){
  $("#open_grid_product_modal").click(function(){
    $("#grid_product").modal('show');
    $(".products_grid").hide();
    $(".sub_category_image").show();
    
  });
});



</script>
<script type="text/javascript">
  $(document).ready(function(){

    $('body').on('click','.add_product_to_cart',function(){
      var currentUrl = window.location.origin;
      event.preventDefault();
      if($(this).data('id') !== "" )
      {

        $.ajax({
          type: 'GET',
          data: {product_code:$(this).data('id'),from:1},
                // async:false,
                url: '{{route('get_qr_code_product_order_purchase')}}',
              })

        .done(function(data) {

          console.log(data);
          $("#grid_product").modal('hide');
          if(data.product_code !== '')
          {
                    //$('.qr-code1').val('');

                    if(!!data.price && !!data.product_code){
                      var price_code = data.price+'/'+data.product_code;
                    }
                    else{
                      var price_code = 0;
                    }
                    
                    //alert(price_code);
                    

                    let table = $('.full-card');
                    if($(table).hasClass('hide'))
                    {
                      $(table).removeClass('hide');
                      $(table).addClass('show');

                    }  


                    let tableBody = $('.table-body');
                    let product_name = data.product_name; 
                    if(data.product_name === null )
                    {
                      product_name = '';
                    }

                    let product_image = data.image; 
                    if(data.image === null )
                    {
                      product_image = 'No-Image-Found.png';
                    }

                  inputform = `<input type="number" class="order_quantity product_need" name="product_quantity[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='1'>`;

                    discountInput = `<input type="hidden" class="discountInputField" id='product_discount_${data.product_code}' name="product_discount[]" style="width:62px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1">`;

                    weightInput = `<input type="hidden" class="weight" value="${data.weight}" id='weight_${data.product_code}' name="weight[]" style="width:62px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1">`;

                    $data = `
                    <tr class="new" id='${data.product_code}' >

                    <input type="hidden" name="product_id[]" value="${data.id}">
                    <input type="hidden" class="product_code" name="product_code[]" value="${data.product_code}">

                    <input type="hidden" class="product_price" value="${data.shop_price}">
                    <td width="90px;"><img height="70" width="70" src="${currentUrl}/public/${product_image}" class="cutome_image_new" alt="">
                    </td>

                    <td>
                    <font class="bold_asif">${product_name}</font>
                    <br>
                    <font class="bold_asif">Code:</font> ${data.product_code}
                    <br>
                    <span class="bold_asif">Price: </span>
                    <span class="current_pric_${data.product_code}">฿${data.shop_price}</span>&nbsp;&nbsp;
                    <span>
                    <a class="discount_prices pr_${data.product_code}" onclick="discount_pricess('${price_code}');">
                    <strong>Add Discount</strong>
                    
                    </a>
                    </span>
                    ${discountInput}
                    <br>
                    <span class="bold_asif discount_price"></span>
                    <span class="bold_asif">Available Qty:</span> ${data.get_quantity.quantity}
                    <br>
                    ${inputform}
                    ${weightInput}
                    
                    <button type="button" class="btn btn-sm btn-danger" data-product_code="${data.product_code}" data-shop_id=""  >Remove</button>
                    </td>



                    </tr>`;
                    tableBody.append($data);
                    calculateTotals1();
                    checkShippingMethods1();
                          // console.log(data.product_name);
                        }

                        $('.qr-code1').val('');


                      })

      }


    });


function calculateTotals1() {
  const subtotals = $('.new').map((idx, val) => calculateSubtotal1(val)).get();
  const total = subtotals.reduce((a, v) => a + Number(v), 0);

  const subDiscounts = $('.new').map((idx, val) => calculateSubDiscount1(val)).get();
  const totalDiscounts = subDiscounts.reduce((a, v) => a + Number(v), 0);

  const weights = $('.new').map((idx, val) => calculateWeight1(val)).get();
  const totalWeights = weights.reduce((a, v) => a + Number(v), 0);

  if(totalWeights>0){

      $('#total_product_weight').val(totalWeights);
      $.ajax
        ({ 
          type: 'GET',
          data: {totalWeights:totalWeights},
          url: '{{url('get_all_shipping_methods')}}',
          success: function(result)
          {
            console.log(result);
            $('.shipping_metthod_wrapper').html('');
            $('.shipping_metthod_wrapper').html(result);
            
          }
      });
    }

  $('#sub_total ').val(total);
  $('#total_discount').val(totalDiscounts);
  $('.sub_total_text').text(formatAsCurrency1(total));
  $('.total_discount_text').text(formatAsCurrency1(totalDiscounts));
  let shippingCost = $('#shipping_cost').val();
  if(shippingCost !== ''){
              // in_total = parseInt(total) + parseInt(shippingCost);
              in_total = Number(total) + Number(shippingCost);
              $('#in_total').val(in_total);
              $('.in_total_text').text(formatAsCurrency1(in_total));
            }else{
              in_total = Number(total);
              $('#in_total').val(in_total);
              $('.in_total_text').text(formatAsCurrency1(in_total));
            }

            if(totalDiscounts){
              in_total = $('#in_total').val() - totalDiscounts;
              $('#in_total').val(in_total);
              $('.in_total_text').text(formatAsCurrency1(in_total));
            }





          }

          function calculateSubtotal1(row) {
            const $row = $(row);
            const input = $row.find('.product_need').val();
            //alert(input);
            const input1 = $row.find('.product_price').val();
            const subtotal = input * input1;
            // $row.find('td:last').text(formatAsCurrency(subtotal));
            return subtotal;
          }

          function calculateSubDiscount1(row){
            const $row = $(row);

            const input = $row.find('.discountInputField').val();
            const input1 = $row.find('.product_price').val();

            if(input.length != 0){
              const product_need = $row.find('.product_need').val();
              const subtotalDiscount = (input1 - input)*product_need;
              return subtotalDiscount;
            }
          }

          function calculateWeight1(row) {
          const $row = $(row);
          const input = $row.find('.weight').val();
          const input_count = $row.find('.product_need').val();
          total = input*input_count;

          if(total > 0){
            return total;
          }
          else{
             return 0;
          }
         
          }

          function formatAsCurrency1(amount) {
            return `฿ ${Number(amount).toFixed(2)}`;
          }

          function checkShippingMethods1(){

            if($("#in_total").val() === '0'){
              $(".shipping_methods_wrapper_not").show();
              $(".shipping_methods_wrapper").hide();
            }

            if($("#in_total").val() > 0){
              $(".shipping_methods_wrapper_not").hide();
              $(".shipping_methods_wrapper").show();
            }

          }

        });

function copyPublicUrl() {
  /* Get the text field */
  var copyText = document.getElementById("copyUrl");

  /* Select the text field */
  copyText.select();
  //copyText.setSelectionRange(0, 99999); /* For mobile devices */

  /* Copy the text inside the text field */
  document.execCommand("copy");

  /* Alert the copied text */
  alert("Copied the Public Url: " + copyText.value);
}
      </script>