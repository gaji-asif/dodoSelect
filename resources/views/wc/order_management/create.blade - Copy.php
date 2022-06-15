<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'DodoTracking') }} - All Packages</title>

  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.0-alpha1/css/bootstrap.min.css" integrity="sha256-IdfIcUlaMBNtk4Hjt0Y6WMMZyMU0P9PN/pH+DFzKxbI=" crossorigin="anonymous" />
  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

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
        right: 34px;
        top: 100px;
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

            <div id="accordion">
              <div class="card">
                <a style="cursor: pointer;" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                  <div class="card-header" id="headingOne">
                    <h5 class="mb-0">
                      <button class="btn btn-link headers_title">
                        Select Order / Quotation
                      </button>
                    </h5>
                  </div>
                </a>

                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                  <div class="card-body ml-3">
                   {{-- <div class="form-group">
                      <label for="email">Select Type:</label><br>

                      <input type="radio" name="check" value="1" id="checkin" checked='true'>
                      <label for="checkin" style="padding-left: 5px">

                        Order

                      </label> 
                      <input type="radio" name="check" value="0" id="checkout" style="margin-left: 10px"><label for="checkout" style="padding-left: 5px"> Quotation</label>


                    </div> --}}

                  <div class="form-group">
                    <label for="email">Select  Channel:</label><br>
                    <input type="radio" name="channel" value="1" id="checkin" @if(isset($editData) && $editData->channel == 1) Checked='true' @endif><label for="checkin" style="padding-left: 5px">Facebook</label> 
                    <input type="radio" name="channel" value="2" id="checkout" style="margin-left: 10px" @if(isset($editData) && $editData->contact_name == 2) Checked='true' @endif><label for="checkout" style="padding-left: 5px"> Line</label>
                    <input type="radio" name="channel" value="3" id="checkout" style="margin-left: 10px" @if(isset($editData) && $editData->channel == 3) Checked='true' @endif><label for="checkout" style="padding-left: 5px"> Phone</label>
                  </div>
                </div>
              </div>
            </div>
            <div class="card">
              <a style="cursor: pointer;" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                <div class="card-header" id="headingTwo">
                  <h5 class="mb-0">
                    <button class="btn btn-link collapsed headers_title">
                      Contact Details
                    </button>
                  </h5>
                </div>
              </a>
              <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                <div class="card-body ml-3">
                  <div class="row">
                   <div class="form-group col-lg-12">
                      <label for="email">Contact Name:</label>
                      <input type="text" name="contact_name"   class="form-control w-full rounded-md shadow-sm border-gray-300" placeholder="Enter Contact Name" id="email" value="@if(isset($editData)) {{$editData->contact_name}} @endif" >
                    </div>
                  {{-- <div class="form-group col-lg-12">
                    <label for="pwd">Contact Phone:</label>
                    <input type="password" class="form-control w-full rounded-md shadow-sm border-gray-300" placeholder="Enter password" id="pwd">
                  </div> --}}
                </div>
                <div class="row">
                 <div class="form-group col-lg-12">
                  <label for="email">Shipping Address</label>
                  <textarea style="width: 100%;" name='shipping_address' class="form-control w-full rounded-md shadow-sm border-gray-300" id="exampleFormControlTextarea1" rows="3">@if(isset($editData)) {{$editData->shipping_address}} @endif</textarea>
                </div>
                
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <a style="cursor: pointer;" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
            <div class="card-header" id="headingThree">
              <h5 class="mb-0">
                <button class="btn btn-link collapsed headers_title">
                  Product Details
                </button>
              </h5>
            </div>
          </a>
          <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
            <div class="card-body">
             <div class="form-group">
              <label for="exampleFormControlSelect2">Select Product</label>
              <select class="form-control" id="" style="margin-bottom: 32px;"> 
                <option>product 1</option>
                <option>product 2</option>

              </select>
            </div>


        <table class="table mt-3" style="margin-top: 15px;">
              <thead class="" style="background-color: #D4EDDA !important;">
                <tr style="background-color: #D4EDDA;" style="color: #FFFFFF;">
                  <th>Name</th>
                  <th>Available</th>
                  <th width="5%">Quantity</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>Laptop - L101</td> 
                <td>100</td>
                <td width="5%"><input width="100%" type="text" value="2"></td>
                <td>Delete</td>  
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card">
          <a style="cursor: pointer;" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
            <div class="card-header" id="headingFour">
              <h5 class="mb-0">
                <button class="btn btn-link collapsed headers_title">
                  Generate Url for Customer
                </button>
              </h5>
            </div>
          </a>
          <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordion">
            <div class="card-body">
             

               <div class="form-group col-lg-12">
                    <label for="email">Your Url</label>
                   http://localhost/dodoStock/public/order_management/12ADA@@@#
                  </div>
        
          </div>
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
          <h4><strong>Product List</strong></h4>
        </div>
        <div class="text-right">
          <button type="button" class=" btn  btn-warning reset-button">Reset</button>
        </div>  
          <div>
            <div class="loading" id="loading2"></div>
            <div class="form-group">
              {{-- <label for="exampleInputEmail1"><strong>Qr Code</strong></label> --}}
              <input type="text" class="qr-code w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="employee_search" aria-describedby="emailHelp" placeholder="Enter Qr Code">
              
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
                    <th>Quantity</th>
                    <th>Order Quantity</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody class="table-body">
                  @if (isset($editData->orderProductDetails))
                  @foreach ($editData->orderProductDetails as $row)
                  <tr class="new" id='@if (isset($row->product)){{($row->product->product_code)}} @endif ' >
                    <input type="hidden" name="product_id[]" value="@if (isset($row->product)){{($row->product->id)}} @endif">
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
                      @if (isset($row->product->getQuantity))
                        {{($row->product->getQuantity->quantity)}}
                      @endif
                    </td>
                    <td>          
                      <input type="number" class="here_quantity" name="product_quantity[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='{{$row->quantity}}'>
                    </td>
                    <td>
                      <div class="text-center mt-2">
                        <button type="button" class="btn btn-sm btn-danger mt-1" data-product_code="{{($row->product->product_code)}}"  ><i class="fa fa-times"></i></button>
                      </div>
                  </td>
                  </tr>  
                  @endforeach
                @endif
                </tbody>
                
              </table>
              <div class="text-right">
                <button type="submit" class="btn btn-success">Submit</button>
              </div>
            </form>
          </div>
      </div>
    </div>
  </div>
</div>


</div>



</div>
<script>
  $(document).ready(function() {
    // console.log('jquery is working');
        $('.qr-code').keyup(function(event) {
          // console.log(event);
          var currentUrl = window.location.origin;
            event.preventDefault();
            if (event.keyCode !== 13) {
              if($(this).val() !== "" )
              {
                $.ajax({
                  type: 'GET',
                  data: {product_code:$(this).val()},
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
                  
                          inputform = `<input type="number" class="here_quantity" name="product_quantity[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='1'>`;
                 
                        $data = `
                        <tr class="new" id='${data.product_code}' >
                        
                          <input type="hidden" name="product_id[]" value="${data.id}">
                  
                          <td><img src="${currentUrl}/dodostock/public/${product_image}" class="cutome_image" alt=""></td>
                          <td>${product_name}</td>
                          <td>${data.product_code}</td>
                          <td>${data.get_quantity.quantity}</td>
                          <td>
                            <div class="input-group mb-3">
                              ${inputform}
                            </div>
                          </td>
                          
                        <td>
                            <div class="text-center mt-2">
                              <button type="button" class="btn btn-sm btn-danger mt-1" data-product_code="${data.product_code}"  ><i class="fa fa-times"></i></button>
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
              url: '{{route('delete_session_product')}}',    
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
        $('#employee_search').val(ui.item.label);
        $('#employeeid').val(ui.item.value); 
        return false;
      }
    });

  });
</script>
</body>

</html>