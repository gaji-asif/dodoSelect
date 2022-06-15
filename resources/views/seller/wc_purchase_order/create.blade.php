<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name') }} - Add Order Purchase</title>


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

    .lead{
      font-size: 18px;
    }

    .order_quantity{
        width: 53px;
        border: 1px solid;
        padding: 2px;
        text-align: center;
        padding-left: 7px;
    }
  </style>

  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>

@if(\App\Models\Role::checkRolePermissions('Can access menu: WooCommerce - Order'))
<body class="font-sans antialiased">
  <div class="min-h-screen bg-gray-100">


    <!--     // start navigation area -->

    <style type="text/css">
      a:hover{
        text-decoration: none;
      }
      .hide{
        display: none;
      }
      .cutome_image{
        height: 70px;
        width: 100px
      }
      .select2-container .select2-selection--single{
        height:   36px;
        /* border-color: rgba(209,213,219,var(--tw-border-opacity)); */
      }
      .select2-container--default .select2-selection--single .select2-selection__rendered{
        line-height: 36px;
      }
      .card-title{
        font-size: 18px;
        margin-bottom: 10px;
      }
      .loading {
        display: inline-block;
        vertical-align: middle;
        width: 16px;
        height: 16px;
        /* background-color: #F0F0F0; */
        position: absolute;
        right: 34px;
        top: 75px;
      }
      /* Example #1 */
      #autocomplete.ui-autocomplete-loading ~ #loading1 {
        background-image: url(//cdn.rawgit.com/salmanarshad2000/demos/v1.0.0/jquery-ui-autocomplete/loading.gif);
      }
      /* Example #2 */
      #loading2.isloading {
        background-image: url(//cdn.rawgit.com/salmanarshad2000/demos/v1.0.0/jquery-ui-autocomplete/loading.gif);
      }
    </style>

    @include('layouts.navigation')

    <div class="container">
      @if (isset($editData))
      <form method="POST" action="{{url('order_purchase_update/'.$editData->id)}}" id="form-import" enctype="multipart/form-data">
        @else
        <form method="POST" action="{{route('order_purchase.store')}}" id="form-import" enctype="multipart/form-data">
          @endif
          @csrf
          <div class="row">

            <div class="col-lg-4 mt-5">
              <div class="card">
                <div class="card-body">
                  <div class="card-title">
                    <h4 class="col-lg-12 mb-3"><strong>New Purchase Order</strong></h4>
                  </div>
                  <div class="col-lg-12">
                    <div class="form-group">
                      <label for="exampleInputEmail1">Supplier Name</label>
                      <select style="width: 100%; margin-bottom: 15px;" class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 js-example-basic-single2" name="supplier_id" required>
                        <option></option>

                        @if (isset($editData))
                        @if (isset($suppliers))
                        @foreach ($suppliers as $supplier)
                        <option value="{{$supplier->id}}" @if($editData->supplier_id == $supplier->id) selected @endif>{{$supplier->supplier_name}}</option>
                        @endforeach
                        @endif
                        @else
                        @if (isset($suppliers))
                        @foreach ($suppliers as $supplier)
                        <option value="{{$supplier->id}}">{{$supplier->supplier_name}}</option>
                        @endforeach
                        @endif
                        @endif
                      </select>



                    </div>

                      <div class="form-group">
                          <label for="e_d_f">Order Date</label>
                          <input type="text" name='order_date' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 datepicker-1" id="order_date" aria-describedby="emailHelp" placeholder="Order Date" required value="@if(isset($editData->order_date)){{$editData->order_date}}@else{{old('order_date')}}@endif">
                        </div>

                  </div>
                  <div class="col-lg-12 checkboxs" style="margin-top: 15px;">
                    <input type="radio" name="check" value="1" id="checkin" ><label for="checkin" style="padding-left: 5px"> <strong>Import</strong></label>
                    <input type="radio" name="check" value="2" id="checkout" style="margin-left: 10px"><label for="checkout" style="padding-left: 5px"> <strong>Domestic</strong></label> <br>
                    @if (isset($editData->supply_from) )
                    <input type="hidden" class="getSupplier_from" value="{{$editData->supply_from}}">
                    @endif
                  </div>
                  <div class="hide" id="imports">
                    <div class="col-lg-12 mt-3">
                      <div class="form-group">
                        <label for="factory_tracking">Factory Tracking</label>
                        <input type="text" name='factory_tracking' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="factory_tracking" aria-describedby="emailHelp" placeholder="Factory Tracking" value="@if(isset($editData)){{$editData->factory_tracking}}@else{{old('factory_tracking')}}@endif">
                      </div>
                    </div>
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label for="cargo_ref">Cargo Reference</label>
                        <input type="text" name='cargo_ref' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="cargo_ref" aria-describedby="emailHelp" placeholder="Cargo Reference" value="@if(isset($editData)){{$editData->cargo_ref}}@else{{old('cargo_ref')}}@endif">
                      </div>
                    </div>
                    <div class="col-lg-12 mt-3">
                      <div class="form-group">
                        <label for="number_of_cartons">Number Of Cartons</label>
                        <input type="number"  name='number_of_cartons' class="form-control w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="number_of_cartons" aria-describedby="emailHelp" placeholder="Number Of Cartons" value="@if(isset($editData)){{$editData->number_of_cartons}}@else{{old('number_of_cartons')}}@endif">
                      </div>
                    </div>
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label for="domestic_logistics">Domestic Logistics</label>
                        <input type="text" name='domestic_logistics' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="domestic_logistics" aria-describedby="emailHelp" placeholder="Domestic Logistics"  value="@if(isset($editData)){{$editData->domestic_logistics}}@else{{old('domestic_logistics')}}@endif">
                      </div>
                    </div>
                  </div>

                  <div class="hide" id="domestic">
                    <div class="col-lg-12 mt-3">
                      <div class="form-group">
                        <label for="number_of_cartons1">Number Of Cartons</label>
                        <input type="number"  name='number_of_cartons1' class="form-control w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="number_of_cartons1" aria-describedby="emailHelp" placeholder="Number Of Cartons"  value="@if(isset($editData)){{$editData->number_of_cartons1}}@else{{old('number_of_cartons1')}}@endif">
                      </div>
                    </div>
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label for="domestic_logistics">Domestic Logistics</label>
                        <input type="text" name='domestic_logistics1' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="domestic_logistics1" aria-describedby="emailHelp" placeholder="Domestic Logistics"  value="@if(isset($editData)){{$editData->domestic_logistics1}}@else{{old('domestic_logistics1')}}@endif">
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
            <div class="col-lg-8 mt-5 ">
              <div class="row">
                <div class="col-lg-12">
                  <div class="card">
                    <div class="card-body">
                      <div class="card-title row">
                        <h4 class="col-lg-12" style="margin-bottom: 7px;"><strong>Date</strong></h4>
                      </div>
                      <div class="row">
                        {{-- <div class="col-lg-6">
                          <div class="form-group">
                            <label for="e_d_f">Estimate Date From</label>
                            <input type="text" name='e_d_f' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 datepicker-1" id="e_d_f" aria-describedby="emailHelp" placeholder="Estimate Date From" value="@if(isset($editData)){{$editData->e_d_f}}@else{{old('e_d_f')}}@endif">
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group">
                            <label for="e_d_t">Estimate Date To</label>
                            <input type="text" name='e_d_t' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 datepicker-1" id="e_d_t" aria-describedby="emailHelp" placeholder="Estimate Date To" value="@if(isset($editData)){{$editData->e_d_t}}@else{{old('e_d_t')}}@endif">
                          </div>
                        </div> --}}
                        <div class="col-lg-6">
                          <div class="form-group">
                            <label for="e_a_d_f">Estimate Arrival Date From</label>
                            <input type="text" name='e_a_d_f' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 datepicker-1" id="e_a_d_f" aria-describedby="emailHelp" placeholder="Estimate Arrival Date From"  value="@if(isset($editData->e_a_d_f)){{date('m/d/Y',strtotime($editData->e_a_d_f))}}@else{{old('e_a_d_f')}}@endif">
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group">
                            <label for="e_a_d_t">Estimate Arrival Date To</label>
                            <input type="text" name='e_a_d_t' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 datepicker-1" id="e_a_d_t" aria-describedby="emailHelp" placeholder="Estimate Arrival Date To" value="@if(isset($editData->e_a_d_t)){{date('m/d/Y',strtotime($editData->e_a_d_t))}}@else{{old('e_a_d_t')}}@endif">
                          </div>
                        </div>
                        {{-- <div class="col-lg-12 pb-3">
                          <label for="exampleInputEmail1">Note</label>
                          <div class="input-group">
                            <textarea name="note" class="form-control" aria-label="With textarea">@if(isset($editData)){{$editData->note}}@else{{old('note')}}@endif</textarea>
                          </div>
                        </div>   --}}
                    </div>

                  </div>
                </div>

              </div>

              <div class="col-lg-12 mt-4">
                <div class="card ">


                  <div class="card-body">
                    <div class="card-title row">
                      <h4 class="col-lg-12 "><strong>Add Product</strong>
                        <button type="button" class="text-right  btn-warning reset-button pull-right" style="float: right; padding: 2px ​12px important;">
                        <font style="padding: 4px 10px !important; border-radius: 4px;">Reset</font>
                      </button></h4>
                      </div>

                      <div>
                        <div class="loading" id="loading2"></div>
                        <div class="form-group">
                          {{-- <label for="exampleInputEmail1"><strong>Qr Code</strong></label> --}}
                          <input type="text" class="qr-code w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="employee_search" aria-describedby="emailHelp" placeholder="Enter Product Code">

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
                                <th>Available</th>
                                <th>Order Qty</th>
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
                                  <input type="number" class="here_quantity order_quantity" name="product_quantity[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='{{$row->quantity}}'>
                                </td>
                                <td>
                                  <div class="text-center">
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
        </form>
      </div>



      <script>
        $(document).ready(function() {
          $('.js-example-basic-single').select2({
            placeholder: "Select A Product Name"
          });
          $('.js-example-basic-single2').select2({
            placeholder: "Select A Supplier Name"
          });
        });
      </script>
      <script>

        $( function() {
          $( ".datepicker-1" ).datepicker();
        } );
        $(document).on('change','.checkboxs',function(){

          let imports = $('#imports');
          let domestic = $('#domestic');

          let factory_tracking = $('#factory_tracking');
          let cargo_ref = $('#cargo_ref');
          let number_of_cartons = $('#number_of_cartons');
          let domestic_logistics = $('#domestic_logistics');

          let number_of_cartons1 = $('#number_of_cartons1');
          let domestic_logistics1 = $('#domestic_logistics1');

          if($('#checkin')[0].checked)
          {
            if($(imports).hasClass('hide'))
            {
              $(imports).removeClass('hide');
              $(imports).addClass('show');

              //$(factory_tracking).prop('required',true);
             // $(cargo_ref).prop('required',true);
              //$(number_of_cartons).prop('required',true);
             // $(domestic_logistics).prop('required',true);
            }
            if($(domestic).hasClass('show'))
            {
              $(domestic).removeClass('show');
              $(domestic).addClass('hide');

             // $(number_of_cartons1).prop('required',false);
              //$(domestic_logistics1).prop('required',false);
            }
          }
          if($('#checkout')[0].checked)
          {
        // console.log(2);
        if($(imports).hasClass('show'))
        {
          $(imports).removeClass('show');
          $(imports).addClass('hide');

          //$(number_of_cartons1).prop('required',true);
          //$(domestic_logistics1).prop('required',true);
        }
        if($(domestic).hasClass('hide'))
        {
          $(domestic).removeClass('hide');
          $(domestic).addClass('show');

          //$(factory_tracking).prop('required',false);
          //$(cargo_ref).prop('required',false);
          //$(number_of_cartons).prop('required',false);
          //$(domestic_logistics).prop('required',false);
        }

      }
    });
        $(document).ready(function(){
          let imports = $('#imports');
          let domestic = $('#domestic');

          let factory_tracking = $('#factory_tracking');
          let cargo_ref = $('#cargo_ref');
          let number_of_cartons = $('#number_of_cartons');
          let domestic_logistics = $('#domestic_logistics');

          let number_of_cartons1 = $('#number_of_cartons1');
          let domestic_logistics1 = $('#domestic_logistics1');

          let getVal = $('.getSupplier_from').val();
          if(getVal == 1)
          {

            $('#checkin').prop('checked',true);
            if($(imports).hasClass('hide'))
            {
              $(imports).removeClass('hide');
              $(imports).addClass('show');

              //$(factory_tracking).prop('required',true);
             // $(cargo_ref).prop('required',true);
              //$(number_of_cartons).prop('required',true);
              //$(domestic_logistics).prop('required',true);
            }
            if($(domestic).hasClass('show'))
            {
              $(domestic).removeClass('show');
              $(domestic).addClass('hide');

              //$(number_of_cartons1).prop('required',false);
              //$(domestic_logistics1).prop('required',false);
            }
          }
          if(getVal == 2)
          {
            $('#checkout').prop('checked',true);
            if($(imports).hasClass('show'))
            {
              $(imports).removeClass('show');
              $(imports).addClass('hide');

              //$(number_of_cartons1).prop('required',true);
             // $(domestic_logistics1).prop('required',true);
            }
            if($(domestic).hasClass('hide'))
            {
              $(domestic).removeClass('hide');
              $(domestic).addClass('show');

             // $(factory_tracking).prop('required',false);
             // $(cargo_ref).prop('required',false);
              //$(number_of_cartons).prop('required',false);
             // $(domestic_logistics).prop('required',false);
            }
          }
        });


      </script>

      <script>
        $(document).ready(function() {
          $('body').on('click','.ui-menu-item-wrapper',function(){
        $('.qr-code').keyup();
      });
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
                data: {product_code:$(this).val(),from:2},
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

                    <td><img src="${currentUrl}/public/${product_image}" class="cutome_image" alt=""></td>
                    <td>${product_name}</td>
                    <td>${data.product_code}</td>
                    <td>${data.get_quantity.quantity}</td>
                    <td>
                    <div class="input-group mb-3">
                    ${inputform}
                    </div>
                    </td>

                    <td>
                    <div class="text-center">
                    <button type="button" class="btn btn-sm btn-danger mt-1" data-product_code="${data.product_code}" ><i class="fa fa-times"></i></button>
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
        $('.qr-code').val(ui.item.label);
        $('.qr-code').val(ui.item.value);
        return false;
      }
    });

  });
</script>
</body>
@endif

</html>
