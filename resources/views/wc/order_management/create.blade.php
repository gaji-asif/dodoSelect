<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name') }} - All Packages</title>

  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.0-alpha1/css/bootstrap.min.css" integrity="sha256-IdfIcUlaMBNtk4Hjt0Y6WMMZyMU0P9PN/pH+DFzKxbI=" crossorigin="anonymous" />

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

  <!-- Styles -->
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <!-- Scripts -->
  <script src="{{ asset('js/app.js') }}" defer></script>
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
  </style>
</head>

<body class="font-sans antialiased">
  <div class="min-h-screen bg-gray-100">


    <!--     // start navigation area -->

    <style type="text/css">
      a:hover{
        text-decoration: none;
      }
      .cutome_image{
        height: 70px;
        width: 100px
      }
    </style>

    @include('layouts.navigation')


      @if(\App\Models\Role::checkRolePermissions('Can access menu: WooCommerce - Order'))
          <div class="container">
      <div class="row">
        <div class="col-lg-12 mt-5">
         @if(session()->has('error'))
         <div class="alert alert-danger mb-3 background-danger" role="alert">
          {{ session()->get('error') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        @if(session()->has('success'))
        <div class="alert alert-success mb-3 background-success" role="alert">
          {{ session()->get('success') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        @if (isset($editData))
        <form method="POST" action="{{url('order_management_update/'.$editData->id)}}" id="form-import" enctype="multipart/form-data">
          @else
          <form method="POST" action="{{route('order_management.store')}}" id="form-import" enctype="multipart/form-data">
            @endif
            @csrf
            <div class="row">
              <div class="col-lg-5">


                <div class="card mb-3">
                 <div class="card-body">
                  <div class="card-title">
                    <h4 class="mb-3"><strong>Public Url</strong>
                      <button style="float: right; margin-bottom: 5px;" type="button" class="btn btn-primary btn-sm pull-right">Copy</button>
                    </h4>
                  </div>
                  <div class="form-group">
                    <!-- <label for="email">Your Url</label> -->
                    http://localhost/dodoStock/public/order_management/12ADA@@@#

                  </div>
                </div>
              </div>

              <div class="card mb-3">
               <div class="card-body">

                <div class="form-group">
                  <label for="email"><strong>Select  Channel:</strong></label><br>
                  <input type="radio" name="channel" value="1" id="checkin" @if(isset($editData) && $editData->channel == 1) Checked='true' @endif><label for="checkin" style="padding-left: 5px">Facebook</label>
                  <input type="radio" name="channel" value="2" id="checkout" style="margin-left: 10px" @if(isset($editData) && $editData->contact_name == 2) Checked='true' @endif><label for="checkout" style="padding-left: 5px"> Line</label>
                  <input type="radio" name="channel" value="3" id="checkout" style="margin-left: 10px" @if(isset($editData) && $editData->channel == 3) Checked='true' @endif><label for="checkout" style="padding-left: 5px"> Phone</label>
                </div>

                <div class="form-group">
                  <label for="email"><strong>Contact Name:</strong></label>
                  <input type="text" name="contact_name"   class="form-control w-full rounded-md shadow-sm border-gray-300" placeholder="Enter Contact Name" id="email" value="@if(isset($editData)) {{$editData->contact_name}} @endif" >
                </div>
              </div>
            </div>

            <div class="card">
             <div class="card-body">



              <div class="form-group">
                <label for="email"><strong>Shipping Address</strong></label>
                <textarea style="width: 100%;" name='shipping_address' class="form-control w-full rounded-md shadow-sm border-gray-300" id="exampleFormControlTextarea1" rows="3">@if(isset($editData)) {{$editData->shipping_address}} @endif</textarea>
              </div>
            </div>
          </div>


          <div class="row mt-3" style="margin: 0 auto;">
            <button type="submit" class="btn btn-primary col-lg-5 text-center" style="margin: 0 auto;">Sent to Customer</button>
          </div>

        <!-- <div class="card">
         <div class="card-body">




          <div class="card-title">
            <h4><strong>Coming Soon</strong></h4>
          </div>

          <form action="/action_page.php">



            <div class="form-group form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox"> Remember me
              </label>
            </div>

          </form>


        </div>
      </div> -->
    </div>
    <div class="col-lg-7">
      <div class="card">
       <div class="card-body">
        <div class="card-title">
          <h4 class="mb-3"><strong>Product List</strong>
           <button style="float: right; margin-top: -10px;" type="button" class=" btn  btn-warning reset-button">Reset</button></h4>
        </div>

       <div class="row">
         <div class="col-lg-6">

          <div class="loading" id="loading2"></div>
          <div class="form-group">
            {{-- <label for="exampleInputEmail1"><strong>Qr Code</strong></label> --}}
            <input type="text" class="qr-code w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="employee_search" aria-describedby="emailHelp" placeholder="Enter Qr Code">

          </div>
        </div>

         </div>
         <div class="col-lg-6">


          <div class="form-group">

            <select style="width: 100%; margin-bottom: 15px;" class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 js-example-basic-single2 shop_id" name="shop_id" >

              <option></option>

              @if (isset($shops))
              @foreach ($shops as $shop)
              <option value="{{$shop->id}}">{{$shop->name}}</option>
              @endforeach
              @endif

            </select>



          </div>
         </div>
       </div>


              @if (isset($editData->orderProductDetails))
                <div class="full-card show">
              @else
                <div class="full-card hide">
              @endif
              <table class="table table-responsive">
                <thead class="thead-light">
                  <tr >
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Product Code</th>
                    <th>Product Price</th>
                    <th>Available</th>
                    <th>Quantity</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody class="table-body">
                  @if (isset($editData->orderProductDetails))
                    @foreach ($editData->orderProductDetails as $key=>$row)
                    <tr class="new" id='@if (isset($row->product)){{($row->product->product_code.$priceAndShop[$key]['shop'])}} @endif ' >
                      <input type="hidden" name="product_id[]" value="@if (isset($row->product)){{($row->product->id)}} @endif">
                      <input type="hidden" name="shop_id[]" value="@if (isset($priceAndShop)){{$priceAndShop[$key]['shop']}} @endif">
                      <td>
                        @if (!empty($row->product->image))
                        <img src="{{asset($row->product->image)}}" class="cutome_image" alt="">
                        @else
                        <img src="{{asset('No-Image-Found.png')}}" class="cutome_image" alt="">
                        @endif
                      </td>
                      <td>
                        @if (isset($row->product))
                        {{($row->product->product_name)}}
                        @endif
                      </td>
                      <td>
                        @if (isset($row->product))
                        {{($row->product->product_code)}}
                        @endif
                      </td>
                      <td>
                        @if (isset($priceAndShop))
                        {{($priceAndShop[$key]['price'])}}
                        @endif
                      </td>
                      <td>
                        @if (isset($row->product->getQuantity))
                        {{($row->product->getQuantity->quantity)}}
                        @endif
                      </td>
                      <td>
                        <input type="number" class="here_quantity order_quantity" name="product_quantity[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='{{$row->quantity}}'>
                      </td>
                      <td>
                        <div class="text-center mt-2">
                          <button type="button" class="btn btn-sm btn-danger" data-product_code="{{($row->product->product_code)}}" data-shop_id="@if (isset($priceAndShop)){{$priceAndShop[$key]['shop']}}@endif" ><i class="fa fa-times"></i></button>
                        </div>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>

              </table>

      </div>
    </div>


    <div class="row">
    <div class="col-lg-5">
       <div class="card mb-3 mt-3">
               <div class="card-body">

                <div class="form-group">
                  <label for="email"><strong>Shipping Methods:</strong></label><br>

                  @foreach($shippers as $shipper)
                  <input type="radio" name="shipping_methods" value="{{$shipper->id}}" id="checkin{{$shipper->id}}" @if(isset($editData) && $editData->shipping_methods == $shipper->id) Checked='true' @endif><label for="checkin{{$shipper->id}}" style="padding-left: 5px"> &nbsp;{{$shipper->name}}</label> <br>
                  @endforeach

                </div>


              </div>
            </div>
    </div>

    <div class="col-lg-7">
       <div class="card mb-3 mt-3">
               <div class="card-body">

                <div class="form-group">
                  <label for="email"><strong>Cart Totals:</strong></label><br>

                </div>


              </div>
            </div>
    </div>
  </div>

  <div class="text-center">
    <button type="submit" class="btn btn-success">Submit</button>
  </div>
</form>

      </div>
  </div>
 </div>
      @endif
</div>
<script>
    $(document).ready(function() {

          $('.js-example-basic-single2').select2({
            placeholder: "Select A Shop Name",
            allowClear: true
          });
        });
  $(document).ready(function() {
    // console.log('jquery is working');
    $('body').on('click','.ui-menu-item-wrapper',function(){
        $('.qr-code').keyup();
      });
    $('.qr-code').keyup(function(event) {
          // console.log(event);
          var currentUrl = window.location.origin;
          var shop_id = $('.shop_id').val();
          event.preventDefault();
          if (event.keyCode !== 13) {
            if($(this).val() !== "" )
            {
              $.ajax({
                type: 'GET',
                data: {product_code:$(this).val(),shop_id:shop_id,from:1},
                async:false,
                url: '{{route('get_qr_code_product_order_purchase')}}',
              })

              .done(function(data) {

                  // console.log(data);
                  if(data !== '')
                  {
                    $('.qr-code').val('');

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

                    inputform = `<input type="number" class="here_quantity order_quantity" name="product_quantity[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='1'>`;

                    $data = `
                    <tr class="new" id='${data.product_code}' >

                    <input type="hidden" name="product_id[]" value="${data.id}">
                    <input type="hidden" name="shop_id[]" value="${shop_id}">

                    <td><img src="${currentUrl}/public/${product_image}" class="cutome_image" alt=""></td>
                    <td>${product_name}</td>
                    <td>${data.product_code}</td>
                    <td>${data.shop_price}</td>
                    <td>${data.get_quantity.quantity}</td>
                    <td>
                    <div class="input-group mb-3">
                    ${inputform}
                    </div>
                    </td>

                    <td>
                    <div class="text-center mt-2">
                    <button type="button" class="btn btn-sm btn-danger" data-product_code="${data.product_code}" data-shop_id="${shop_id}"  ><i class="fa fa-times"></i></button>
                    </div>
                    </td>
                    </tr>`;
                    tableBody.append($data);
                          // console.log(data.product_name);
                        }


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

    $("body").on("click",".btn-danger",function(){
      let product_code = $(this).data('product_code');
      let shop_id = $(this).data('shop_id');
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
</script>
</body>

</html>
