<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name') }} - Payment</title>

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  {{-- datatable --}}
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>


  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

  <!-- Styles -->
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <!-- Scripts -->
  <script src="{{ asset('js/app.js') }}" defer></script>

  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">


  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <link rel="stylesheet" href="{{ asset('css/custom_css.css') }}">
  <style type="text/css">
    a:hover{
      text-decoration: none;
    }

    @media screen and (max-width: 600px) {
      .pricing_title{
        margin-top: 60px;
      }
    }

    .card-header h2{
      font-size: 22px;
      font-weight: bold !important;
    }

    .lead{
      font-size: 18px;
    }
  </style>

  <style type="text/css">
    a:hover{
      text-decoration: none;
    }

    @media screen and (max-width: 600px) {
      .pricing_title{
        margin-top: 60px;
      }
    }

    .card-header h2{
      font-size: 22px;
      font-weight: bold !important;
    }

    .headers_title{
      font-weight: bold;
      color: #000000;
      text-decoration: none;
    }

    .headers_title:hover{
      text-decoration: none;
    }

    .lead{
      font-size: 18px;
    }

    [type=text] {
      width: 100%;
      height: 32px;
    }

    .loading {
      display: inline-block;
      vertical-align: middle;
      width: 16px;
      height: 16px;
      /* background-color: #F0F0F0; */
      position: absolute;
      right: 28px;
      top: 12px;
    }
    /* Example #1 */
    #autocomplete.ui-autocomplete-loading ~ #loading1 {
      background-image: url(//cdn.rawgit.com/salmanarshad2000/demos/v1.0.0/jquery-ui-autocomplete/loading.gif);
    }
    /* Example #2 */
    #loading2.isloading {
      background-image: url(//cdn.rawgit.com/salmanarshad2000/demos/v1.0.0/jquery-ui-autocomplete/loading.gif);
    }
    .hide{
      display: none;
    }

    .order_quantity{
      width: 53px;
      border: 1px solid;
      padding: 2px;
      text-align: center;
      padding-left: 7px;
    }
    .select2-container .select2-selection--single{
      height:   33px;
      /* border-color: rgba(209,213,219,var(--tw-border-opacity)); */
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered{
      line-height: 33px;
    }
  </style>
</head>

<body class="font-sans antialiased">
  <div class="min-h-screen bg-gray-100">
    <style type="text/css">
      a:hover{
        text-decoration: none;
      }
    </style>
    <div class="container">
      <div class="row">
        <div class="col-lg-2 col-sm-12"></div>
        <div class="col-lg-8 col-sm-12">
         <div class="row" style="padding-top: 3%;">
          <div class="col-lg-4"></div>
          <div class="col-lg-4">
            <div class="flex justify-center" style="margin: 0 auto; padding-bottom: 2%;">
              @if (session()->has('userLogo'))
              <img class="logo_buyer" src="{{ asset(session()->get('userLogo')) }}  " alt="">
              @else
              <img class="logo_buyer" src="@if(isset($userLogo)) {{ asset($userLogo) }} @else {{ asset('img/dodoselect.png') }} @endif " alt="">
              @endif

            </div>
          </div>
        </div>
        <form method="POST" action="{{route('make_order_payment')}}" id="customer_order_payment" enctype="multipart/form-data">
          @csrf
          <div class="container">
            <div class="row">
              <h6 class="track_text mt-3">Order Id ( #@if(isset($editData)){{$editData->id}} @endif ) -
                <font id="order_status_div">
                  @if($editData->order_status == 1) Pending  @endif
                  @if($editData->order_status == 2) Processing  @endif
                  @if($editData->order_status == 3) Ready to ship  @endif
                  @if($editData->order_status == 4) Shipped  @endif
                </font>
              </h6>
            </div>
            @if(isset($editData->payment_status))
            <div class="row mb-2">
              <p class="text_center_asif col-lg-12 payment_status_div" id="payment_status_div">Payment Status -
                @if($editData->payment_status == 0)
                <strong class="">Not Paid</strong></p><br>
                @endif
                @if($editData->payment_status == 1)
                <strong>Paid</strong></p><br>

                <p class="text_center_asif col-lg-12">Payment Method - <strong id="payment_method_div">
                  @if(isset($editData->payment_channel_from_ksher))
                  {{$editData->payment_channel_from_ksher}}
                  @endif
                </strong></p><br>
                <p class="text_center_asif col-lg-12">Payment Confirmed - <strong id="payment_date_div">
                 @if (isset($editData))
                 {{date('d-M-Y h:i:a', strtotime($editData->payment_date))}}
                 @endif
               </strong></p><br>
               @endif
             </div>
             @endif
             @if (session()->has('success'))
             <div class="col-lg-12 mt-2">
              <div class="alert alert-success row" role="alert">
                Your Payment is successful for this Order.
              </div>
            </div>
            @endif
            @if (session()->has('danger'))
            <div class="col-lg-12 mt-2">
              <div class="alert alert-danger row" role="alert">
                **** {{ Session::get('danger') }}
              </div>
            </div>
            @endif


            <div class="row">
              <div class="col-lg-12 mt-2">
               @if(session()->has('error'))
               <div class="alert alert-danger mb-3 background-danger" role="alert">
                {{ session()->get('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              @endif

              <input type="hidden" name="order_id" id="order_id" value="@if(isset($editData->id)){{($editData->id)}}@endif">

              <input type="hidden" name="encryted_order_id" id="encryted_order_id" value="@if(isset($editData)){{($editData->order_id)}} @endif">

              <div class="flex flex-row items-center justify-between mb-2 mt-4">
    <h2 class="block whitespace-nowrap text-yellow-500 text-base font-bold">
        Direct Bank Transfer
    </h2>
    <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-yellow-300">
</div>

              <div class="row mb-3">
                <div class="col-lg-12">
                  <label><strong>Select Bank Name</strong></label>
                  <select class="form-control">
                    <option value="">Bank Name</option>
                    <option>National Bank</option>
                    <option>Thailand Bank</option>

                  </select>
                </div>


            </div>

            <div class="row mb-3">
              <div class="col-lg-6 mb-3">
                <label><strong>Select Date</strong></label>
                <x-input type="text" name="payment_date" id="payment_date" value="{{ date('d-m-Y') }}" />
              </div>

                <div class="col-lg-6 mb-3">
                <label><strong>Total Amount</strong></label>
                <x-input disable type="text" name="in_total" id="in_total"
                placeholder="฿{{$orderDetails->in_total}}"/>
              </div>
            </div>

            <div class="row mb-3 pb-3">
              <div class="col-lg-12">
                <label><strong>Select Proof of Payments</strong></label>
                <x-input type="file" class="form-control" name="proof_payments" id="proof_payments"/>
              </div>
            </div>


            @if(isset($editData->payment_status))
            @if($editData->payment_status == 0)
            <div class="text-center mb-3 place_order_btn">
              <button type="submit" class="btn btn-success col-lg-3">Make Payment</button>
            </div>
            @endif
            @endif

            @if(isset($editData->payment_status))
            @if($editData->payment_status == 1)
            <div class="text-center mb-3 place_order_btn">
              <a class="btn btn-success col-lg-5">This Order is Already Paid</a>
            </div>
            @endif
            @endif
          </form>

        </div>
      </div>
    </div>
  </div>

</div>
<div class="col-lg-2 col-sm-12"></div>
</div>


<div class="container-fluid">
  <div class="text-center p-3 footer_asif col-lg-12" style="background-color: rgba(0, 0, 0, 0.2);">
    Powered By
    <a class="text-dark" href="https://dodoselect.com/">Dodoselect.com</a>
  </div>
</div>
@include('elements.shipping_address_modal')

<script>
  $(document).ready(function() {

    $('.js-example-basic-single2').select2({
      placeholder: "Select A Shop Name",
      allowClear: true
    });
  });
  $(document).ready(function() {
    // console.log('jquery is working');


    function calculateTotals() {
      const subtotals = $('.new').map((idx, val) => calculateSubtotal(val)).get();
      const total = subtotals.reduce((a, v) => a + Number(v), 0);
      $('#sub_total ').val(total);
      $('.sub_total_text').text(formatAsCurrency(total));
      let shippingCost = $('#shipping_cost').val();
      if(shippingCost !== ''){
              // in_total = parseInt(total) + parseInt(shippingCost);
              in_total = Number(total) + Number(shippingCost);
            }else{
              in_total = Number(total);
            }
          //  console.log(in_total);
          $('#in_total').val(in_total);
          $('.in_total_text').text(formatAsCurrency(in_total));

        }

        function calculateSubtotal(row) {
          const $row = $(row);
          const input = $row.find('.product_need').val();
          const input1 = $row.find('.product_price').val();
          const subtotal = input * input1;
            // $row.find('td:last').text(formatAsCurrency(subtotal));
            return subtotal;
          }

          function formatAsCurrency(amount) {
            return `฿ ${Number(amount).toFixed(2)}`;
          }

          $("body").on("click",".btn-danger",function(){
            let product_code = $(this).data('product_code');
            let shop_id = $(this).data('shop_id');


            $main_node = $(this).parents(".new");
            const input = $main_node.find('.product_need').val()
            const input1 = $main_node.find('.product_price').val();
            const subtotal = input * input1;
            let total = $('#sub_total').val();
            total = total - subtotal;
            $('#sub_total ').val(total);
            $('.sub_total_text').text(formatAsCurrency(total));


            let in_total =  $('#in_total').val();
            in_total =  in_total - subtotal;
            $('#in_total').val(in_total);
            $('.in_total_text').text(formatAsCurrency(in_total));

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
              data:{product_code:product_code,shop_id:shop_id},
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
              if (event.keyCode != 13) {
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
                      $('.shop_id').val(null).trigger('change');
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
            $(".table-body").html('');  $('.qr-code').val('');
            $('.shop_id').val(null).trigger('change');


            let shipping_cost = $('#shipping_cost').val();
            let total = 0;
            $('#sub_total').val(total);
            $('.sub_total_text').text(formatAsCurrency(total));

            $('#in_total').val(shipping_cost);
            $('.in_total_text').text(formatAsCurrency(shipping_cost));

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
      <script type="text/javascript">

    // CSRF Token
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $(document).ready(function(){

      $( "#employee_search" ).autocomplete({
        search: function () {
          $("#loading2").addClass("isloading");
        },
        source: function( request, response ) {
        // console.log(1);
          // Fetch data
          $('.spinner').show();
          $.ajax({
            url:"{{route('Autocomplte.getAutocomplte')}}",
            type: 'post',
            dataType: "json",
            data: {
              _token: CSRF_TOKEN,
              search: request.term
            },
            success: function( data ) {
              $('.spinner').hide();
              response( data );
            }
          });
        },

        response: function () {
          $("#loading2").removeClass("isloading");
        },
        select: function (event, ui) {
          $('.qr-code').val(ui.item.label);
          $('.qr-code').val(ui.item.value);
          return false;
        }
      });

    });

    $( document ).ready(function() {
      $('.shipping_type').on('change',function(){
        let shipping_cost = $(this).data('price');
        //alert(shipping_cost);


        if($("#in_total").val()>0){
          $('#shipping_cost').val(shipping_cost);
          $('.shipping_cost_text').text(`฿ ${Number(shipping_cost).toFixed(2)}`);
          //calculateTotals_shippers();


          var total  = Number(shipping_cost)+Number($("#sub_total").val());

          var after_discount_total = Number(total)- Number($("#total_discount").val());

          $('#in_total').val(after_discount_total);
          $('.in_total_text').text('฿'+Number(after_discount_total).toFixed(2));
        }
      });
    });

    // $(function(){
    //     $("#customer_order_payment").submit(function(event){
    //         event.preventDefault();

    //             if($("#shipping_name_main").val().length === 0){
    //                 alert("please add your Shipping details");
    //                 return false;
    //             }

    //            var shipping_methods_val = $('input[name="shipping_methodss"]:checked').val();


    //            if(typeof shipping_methods_val === "undefined" || !shipping_methods_val){
    //                 alert("please add your Shipping methods");
    //                 return false;
    //             }

    //            $.ajax({
    //                 url: '{{route('make_order_payment')}}',
    //                 type:'POST',
    //                 data:$(this).serialize(),
    //                 success:function(result){
    //                   // alert(result);

    //               }

    //         });

    //       });
    // });

    $(document).ready(function(){
      isPaymentSuccess();
    });

    function isPaymentSuccess(){
      if($("#payment_status").val() === '0'){
        $.ajax
        ({
          type: 'GET',
          data: {order_id:$("#order_id").val()},
          url: '{{url('isPaymentSuccess')}}',
          success: function(result)
          {

            var data = jQuery.parseJSON(result);
            //alert(data.payment_status);
            if(data.payment_status === 1){
              $("#unpaid_information_modal").modal('hide');
              //alert(data.payment_status);

              if(data.order_status === 1){
                order_status = 'Pending';
              }
              if(data.order_status === 2){
               order_status = 'Processing';
             }
             if(data.order_status === 3){
               order_status = 'Ready to ship';
             }
             if(data.order_status === 4){
               order_status = 'Shipped';
             }

             if(data.payment_status === 0){
              payment_status = 'Not paid';
            }
            if(data.payment_status === 1){
             payment_status = 'Paid';
             $(".place_order_btn").hide();
             $(".edit_shipping_btn").hide();
           }

           $("#order_status_div").text(order_status);
           $("#payment_status_div").text('');
           $("#payment_status_div").text('Payment Status - '+payment_status);
           $("#payment_date_div").text(data.payment_date);
           $("#payment_method_div").text(data.payment_channel_from_ksher);
         }

         if(data.payment_status === 0){

          setTimeout(function() { your_func(); }, 296000);
          var place_order_time_with_5 = $("#place_order_time_with_5").val();
          var current_time = $("#current_time").val();

          if(current_time < place_order_time_with_5){
           $("#unpaid_information_modal").modal('show');
           setInterval(function() { makeTimer(); }, 1000);
         }

         if(current_time > place_order_time_with_5){
           $("#unpaid_information_modal").modal('hide');
         }

         if(data.order_status === 1){
          order_status = 'Pending';
        }
        if(data.order_status === 2){
         order_status = 'Processing';
       }
       if(data.order_status === 3){
         order_status = 'Ready to ship';
       }
       if(data.order_status === 4){
         order_status = 'Shipped';
       }

       if(data.payment_status === 0){
        payment_status = 'Not paid';
      }
      if(data.payment_status === 1){
       payment_status = 'Paid';
       $(".place_order_btn").hide();
       $(".edit_shipping_btn").hide();
     }

     $("#order_status_div").text(order_status);
     $("#payment_status_div").text('');
     $("#payment_status_div").text('Payment Status - '+payment_status);
     $("#payment_date_div").text(data.payment_date);
     $("#payment_method_div").text(data.payment_channel_from_ksher);
   }
 }
});
      }
    }

$('#payment_date').datepicker({
                dateFormat: 'yy-mm-dd',
            });



  </script>
</body>
</html>
