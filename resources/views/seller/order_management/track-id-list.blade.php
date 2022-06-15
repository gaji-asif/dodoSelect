<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name') }} - Track Id List</title>

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

   <form method="POST" action="{{route('make_order_payment')}}" id="customer_order_payment" enctype="multipart/form-data">
          @csrf

          <div class="container">

            <div class="card mb-3 mt-3 col-lg-12 shop_wrapper">
                    <div class="card-body">

                      <h6 class="mb-3 shop_details">
                      <strong>
                        @if (isset($shopDetails->name))
                        Shop Name: {{$shopDetails->name}}
                        @else
                        No Shop Selected
                        @endif
                      </strong><br>
                     @if (isset($shopDetails->logo))
                      <img class="shop_logo shop_img_wrapper" src="@if(isset($shopDetails->logo)) {{ asset($shopDetails->logo) }} @else {{ asset('img/No_Image_Available.jpg') }} @endif " alt=""><br>
                      @endif
                    </h6>
                  </div>
                </div>

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

              <div class="col-lg-12 shipping_address_wrapper">
                <div class="card shipping_address_wraps mb-3 mt-3">
                  <div class="card-body">
            <h6 class="mb-3">
                <strong>Shipping Address</strong>
                @if(isset($editData->payment_status))
                @if($editData->payment_status == 0)
                <button style="float: right; margin-bottom: 5px;" type="button" class="btn btn-primary btn-sm pull-right edit_shipping_btn" data-toggle="modal" data-target="#shipping_address_modal">
                    Edit
                </button>
                @endif
                @endif
            </h6>
            @if(empty($editData->shipping_name) && empty($editData->shipping_name))
            <div class="form-group" id="no_selected">
                No Address Selected Yet
            </div>
            @endif
            <div class="form-group">
                <h6 id="shipping_name_div" style="font-weight: bold;">
                   @if(isset($editData->shipping_name))
                   {{$editData->shipping_name}}
                   @endif
                </h6>
                <font id="shipping_address_div">
                    @if(isset($editData->shipping_address))
                     {{$editData->shipping_address}}
                    @endif
                </font>
                <p style="margin-bottom: 0px;">

                    <span id="dis">
                     @if(isset($editData->shipping_district))
                     {{$editData->shipping_district}},
                     @endif
                    </span>

                    <span id="sub">
                     @if(isset($editData->shipping_sub_district))
                     {{$editData->shipping_sub_district}}
                     @endif
                    </span>
                  </p>
                  <p>
                    <span id="pro">
                     @if(isset($editData->shipping_province))
                       {{$editData->shipping_province}},
                     @endif
                    </span>
                    <span id="postcodes">
                        @if(isset($editData->shipping_postcode))
                        {{$editData->shipping_postcode}}
                       @endif
                    </span>
                </p>
                <p class="mt-2" id="shipping_phone_div" style="font-weight: bold;">
                       @if(isset($editData->shipping_phone))
                        {{$editData->shipping_phone}}
                       @endif
                </p>

                <input type="hidden" name="shop_logo" value="@if(isset($shopDetails->logo)){{asset($shopDetails->logo)}} @else {{asset('img/No_Image_Available.jpg')}}@endif">

                <input type="hidden" name="shop_name" value="@if(isset($orderDetails)){{$orderDetails->name}}@endif">

                <input type="hidden" id="payment_status" value="@if(isset($editData->payment_status)){{$editData->payment_status}}@endif">

                <input type="hidden" id="shipping_name_main" name="shipping_name_main"
                value="@if(isset($editData->shipping_name)){{$editData->shipping_name}}@endif">
                <input type="hidden" id="shipping_address_main" name="shipping_address_main"
                value="@if(isset($editData->shipping_address)){{$editData->shipping_address}}@endif">

                <input type="hidden" id="sub_main" name="sub_main"
                value="@if(isset($editData->shipping_sub_district)){{$editData->shipping_sub_district}}@endif">

                <input type="hidden" id="dis_main" name="dis_main"
                value="@if(isset($editData->shipping_district)){{$editData->shipping_district}}@endif">

                <input type="hidden" id="pro_main" name="pro_main"
                value="@if(isset($editData->shipping_province)){{$editData->shipping_province}}@endif">

                <input type="hidden" id="postcodes_main" name="postcodes_main" value="@if(isset($editData-> shipping_postcode)){{$editData->    shipping_postcode}}@endif">

                <input type="hidden" id="shipping_phone_main" name="shipping_phone_main"
                value="@if(isset($editData->shipping_phone)){{$editData->shipping_phone}}@endif">

                <input type="hidden" id="full_address" name="full_address"
                value="@if(isset($editData->shipping_district)){{$editData->shipping_district.'/'.$editData->shipping_sub_district.'/'.$editData->shipping_province.'/'.$editData->shipping_postcode}}@endif">

            </div>
        </div> <!-- end of .card-body -->
      </div>
   </div>
</div>

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

    <input type="hidden" name="place_order_time_with" id="place_order_time_with" value="@if(isset($editData->place_order_time)){{ \Carbon\Carbon::parse($editData->place_order_time)->format('d-m-y h:i:s')}}@endif">

    <input type="hidden" name="place_order_time_with_5" id="place_order_time_with_5" value="@if(isset($editData->place_order_time)){{ \Carbon\Carbon::parse($editData->place_order_time)->addMinutes(5)->format('dmyhis')}}@endif">

    <input type="hidden" name="place_order_time_with_5_countdown" id="place_order_time_with_5_countdown" value="@if(isset($editData->place_order_time)){{\Carbon\Carbon::parse($editData->place_order_time)->addMinutes(5)->format('d M Y H:i:s')}}@endif">


    <input type="hidden" name="current_time" id="current_time" value="@if(isset($editData)){{\Carbon\Carbon::now()->format('dmyhis')}}@endif">
    <div class="row">
        <div class="col-lg-12">
          <div class="card">
           <div class="card-body">
            <div class="card-title">
              <h5 class="mb-3 text-center"><strong>Order Details</strong>
              </h5>
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
                      <th>Product Details</th>

                    </tr>
                  </thead>
                  <tbody class="table-body">
                    @if (isset($editData->orderProductDetails))
                    @foreach ($editData->orderProductDetails as $key=>$row)
                    <tr class="new" id='@if (isset($row->product)){{($row->product->product_code.$priceAndShop[$key]['shop'])}} @endif ' >
                      <input type="hidden" name="product_id[]" value="@if (isset($row->product)){{($row->product->id)}} @endif">
                      <input type="hidden" name="shop_id[]" value="@if (isset($priceAndShop)){{$priceAndShop[$key]['shop']}} @endif">
                      <td>@if (!empty($row->product->image))
                        <img src="{{asset($row->product->image)}}" class="cutome_images" height="80" width="80" alt="">
                        @else
                        <img src="{{asset('No-Image-Found.png')}}" height="80" width="80" class="cutome_images" alt="">
                        @endif
                      </td>
                      <td>@if (isset($row->product))
                        {{($row->product->product_name)}}
                        @endif
                        <br>
                        <strong>Code:</strong>
                        @if (isset($row->product))
                        {{($row->product->product_code)}}
                        @endif
                        <br>
                        <strong>Price:</strong>
                        <?php
                        $price = 0;
                        if (!empty($row->discount_price)) {
                          $price = $row->discount_price;
                          echo '฿'.$row->discount_price;
                        }
                        ?>
                        <?php
                        if (!empty($row->product->price) && empty($row->discount_price)) {
                          $price = $row->product->price;
                          echo '฿'.$row->product->price;
                        }
                        ?>
                        <br>
                        <strong>Ordered Quantity: </strong>
                        {{$row->quantity}}
                        <!--  <input  class="ordered_quantity_public" name="product_quantity[]" min='1' style="width:50px" class="form-control rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" value='{{$row->quantity}}'> -->
                      </td>

                      <!-- <td>
                        @if (isset($row->product->getQuantity))
                        {{($row->product->getQuantity->quantity)}}
                        @endif
                      </td> -->


                      <!--   <div class="text-center mt-2">
                          <button type="button" class="btn btn-sm btn-danger" data-product_code="{{($row->product->product_code)}}" data-shop_id="@if (isset($priceAndShop)){{$priceAndShop[$key]['shop']}}@endif" ><i class="fa fa-times"></i></button>
                        </div> -->

                      </tr>
                      @endforeach
                      @endif
                    </tbody>

                  </table>

                </div>
              </div>
            </div>


            <div class="row">

            <!-- <div class="col-lg-4">
              <div class="card mb-3 mt-3">
               <div class="card-body">
                <div class="form-group">
                  <label for="email"><strong>Contact Name:</strong></label>
                  <input type="text" name="contact_name"   class="form-control w-full rounded-md shadow-sm border-gray-300" placeholder="Enter Contact Name" id="email" value="@if(isset($editData)) {{$editData->contact_name}} @endif" >
                </div>

                <div class="form-group">
                  <label for="email"><strong>Shipping Address</strong></label>


                  <input type="text" name="shipping_address"   class="form-control w-full rounded-md shadow-sm border-gray-300" placeholder="Enter Shipping Address" id="email" value="@if(isset($editData)) {{$editData->shipping_address}} @endif" >
                </div>
              </div>
            </div>
          </div> -->
          <div class="col-lg-6">
            <div class="card mb-3 mt-3">
              <div class="card-body">

                <div class="form-group">
                  <label class="margin_bottom_10" for="email"><strong>Shipping Methods:</strong></label><br>


                  @if(is_array($shipping_methodss) || is_object($shipping_methodss))
                  @foreach($shipping_methodss as $shipping_methods)
                  <input type="radio" class="shipping_type" value="{{$shipping_methods->id}}" name="shipping_methodss" data-price="{{$shipping_methods->price}}"
                  @if($shipping_methods->id == $editData->shipping_methods)
                  checked
                  @endif
                  >
                  {{$shipping_methods->shippers_name}} ({{$shipping_methods->name}}) - ฿{{$shipping_methods->price}}
                  <br>
                  @endforeach
                  @endif
                  </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card mb-3 mt-3">
              <div class="card-body">


                <div class="form-group">
                  <label class="margin_bottom_10" for="email"><strong>Cart Totals:</strong></label><br>

                  Sub Total: <font style="text-align: right; float: right;" class="pull-right sub_total_text" >
                    @if(!empty($editData->sub_total))
                    ฿ {{$editData->sub_total}}.00
                    @else
                    ฿ 0.00
                    @endif
                  </font><br>
                  <input type="hidden" id="sub_total" name="sub_total"
                  value="@if(!empty($editData->sub_total)){{$editData->sub_total}}
                  @endif ">

                  Shipping Cost: <font style="text-align: right; float: right;" class="pull-right shipping_cost_text">

                   @if(!empty($editData->shipping_cost))
                   ฿ {{$editData->shipping_cost}}.00
                   @else
                   ฿ 0.00
                   @endif
                 </font><br>

                 <input type="hidden" id="shipping_cost" name="shipping_cost" value="@if(!empty($editData->shipping_cost)){{$editData->shipping_cost}}
                 @endif
                 ">

                 Total Discount: <font style="text-align: right; float: right;" class="pull-right">

                   @if(!empty($editData->total_discount))
                   ฿ {{$editData->total_discount}}.00
                   @else
                   ฿ 0.00
                   @endif
                 </font><br>
                 <input type="hidden" id="total_discount" name="total_discount" value="@if(!empty($editData->total_discount)){{$editData->total_discount}}
                 @endif
                 ">

                 <div class="margin_top_10">
                  <strong class="padding_top_10">In Total:</strong> <font style="text-align: right; float: right; font-weight: bold;" class="pull-right in_total_text">
                    @if(!empty($editData->in_total))
                    ฿ {{$editData->in_total}}.00
                    @else
                    ฿ 0.00
                    @endif
                  </font>
                  <input type="hidden" name="in_total" id="in_total" value="@if(!empty($editData->in_total)){{$editData->in_total}}
                  @endif ">
                </div>
              </div>
            </div>
          </div>
        </div>


        @if(isset($editData->payment_status))
        @if($editData->payment_status == 0 AND empty($paymentDetails))

        <div class="col-lg-12">
          <div class="card mb-3 mt-3">
            <div class="card-body">
              <div class="form-group">
                <label class="margin_bottom_15" for="email"><strong>Select Payment Method:</strong></label><br>

                <div class="row margin_top_10">
                  <div class="col-lg-12">
                    <input type="radio" class="payment_method" value="2" name="payment_method" checked> &nbsp;
                    Payment Gateway (Instant Confirmation)
                  </div>

                  <div id="available_methods" class="col-lg-12">
                    <div class="card shipping_address_wraps mb-3 mt-3">
                      <div class="card-body">
                        <h6 class="mb-5">
                          <strong>Available Methods</strong>
                        </h6>
                        <div class="row margin_top_10">
                          <div class="col-xs-6 col-sm-4 col-6">
                           <img class="img-responsive" src="{{asset('img/alipay.png')}}">
                         </div>
                         <div class="col-xs-6 col-sm-4 col-6">
                          <img class="img-responsive" style="width: inherit;" src="{{asset('img/promtpay.png')}}">
                        </div>
                        <div class="col-xs-6 col-sm-4 col-6">
                          <img class="img-responsive" src="{{asset('img/shopeepay.png')}}">
                        </div>
                        <div class="col-xs-6 col-sm-4 col-6">
                          <img class="img-responsive" style="width: inherit;" src="{{asset('img/truemoney.png')}}">
                        </div>
                        <div class="col-xs-6 col-sm-4 col-6">
                          <img class="img-responsive" src="{{asset('img/wechatpay.png')}}">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-lg-12">
                  <input type="radio"  value="1" class="payment_method" name="payment_method"> &nbsp;
                  Bank Transfer
                </div>

                <div id="bank_transfer_wrapper" class="col-lg-12">
                  <div class="card shipping_address_wraps mb-3 mt-3">
                    <div class="card-body">
                      <h6 class="mb-5">
                        <strong>Direct Bank Transfer</strong>
                      </h6>

                      <div class="col-lg-12 mb-3 row">
                        <div class="mb-3 col-lg-12 row">สนใจสั่งซื้อ</div>

                        <div class="bold_asif mb-3 col-lg-12 row">
                          ธนาคารกสิกรไทย <br>
                          เลขบัญชี : 023-3-85884-6<br>
                          AC Plus Global Co., Ltd
                        </div>

                        <div class="mb-3 col-lg-12 row">Please allow upto 24 hours for your payment to confirm</div>
                      </div>

            <!-- <div class="row mb-3">
              <div class="col-lg-12">
                <label><strong>Select Bank Name</strong></label>
                <select class="form-control">
                  <option value="">Bank Name</option>
                  <option>National Bank</option>
                  <option>Thailand Bank</option>

                </select>
              </div>
            </div> -->
            <div class="row mb-3">
              <div class="col-6 mb-3">
                <label><strong>Select Date</strong></label>
                <x-input type="text" name="payment_date" id="payment_date" value="{{ date('d-m-Y') }}" />
              </div>

              <div class="col-6 mb-3">
                <label><strong>Select Time</strong></label>
                <x-input type="time" name="payment_time" id="payment_time" value="" />
              </div>

              <div class="col-lg-6 mb-3">
                <label><strong>Total Amount</strong></label>
                <input style="height: 38px;" class="form-control input_totals" type="text" name="in_total_inp" id="in_total_inp"
                value="฿{{$orderDetails->in_total}}" readonly>

                <x-input type="hidden" name="in_total_inputs" id="in_total_inputs"
                value="{{$orderDetails->in_total}}"/>
              </div>

              <div class="col-lg-6 mb-3">
                <label><strong>Upload Payment Slip</strong></label>
                <x-input type="file" name="payment_slip" id="payment_slip" value="" />
              </div>
            </div>


          </div>
        </div>
      </div>


                 </div>
               </div>

          </div>
        </div>
      </div>

      @endif
      @endif
    </div>
    <div class="modal" tabindex="-1" role="dialog" id="unpaid_information_modal" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><strong>Payment Process in Progress</strong></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-lg-12 text_center_asif">
            <p class="text_center_asif timer_font">

              <span>Time Left : </span>

              <span id="timer">
                  <span id="days"></span>
                  <span id="hours"></span>
                  <span id="minutes"></span>
                  <span id="seconds"></span>
            </span>
            </p>
            <button type="submit" class="btn btn-success col-lg-3 text_center_asif mt-3">Pay Now</button><br><br>

            <p>If you have already completed the payment, please refresh this page.</p>
            <br>
            <div class="alert alert-warning" role="alert">
  Still need to edit the Order ? Click Edit Button.<br>
   <a class="btn btn-warning col-lg-3 text_center_asif mt-3" data-dismiss="modal" aria-label="Close">Edit</a>

</div>
          </div>
        </div>
      </div>

  </div>
</div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="transaction_timeout" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><strong>Transaction Timeout</strong></h5>

      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-lg-12 text_center_asif">
            <a class="btn btn-success col-lg-3 text_center_asif mt-3" href="{{ url('order_management_buyer/'.$editData->order_id) }}">Return Now</a><br><br>
          </div>
        </div>
      </div>

  </div>
</div>
</div>
   @if(isset($editData->payment_status))
   @if($editData->payment_status == 0 AND empty($paymentDetails))
    <div class="text-center mb-3 place_order_btn">
      <button type="submit" class="btn btn-success col-lg-3">Place Order</button>
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


  @if(isset($paymentDetails))
   @if($paymentDetails->is_confirmed == 0)
    <div class="text-center mb-3 place_order_btn">
      <a class="btn btn-warning col-lg-8">This Order is pending for confirmation</a>
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
                 //$("#unpaid_information_modal").modal('show');
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


function makeTimer() {
      // var endTime1 = new Date("29 April 2020 9:56:00");
      // endTime1 = (Date.parse(endTime) / 1000);

       var place_order_time_with_5_countdown = $("#place_order_time_with_5_countdown").val();

      var endTime = new Date(place_order_time_with_5_countdown);
      endTime = (Date.parse(endTime) / 1000);

      var now = new Date();
      now = (Date.parse(now) / 1000);

      var timeLeft = endTime - now;
      var days = Math.floor(timeLeft / 86400);
      var hours = Math.floor((timeLeft - (days * 86400)) / 3600);
      var minutes = Math.floor((timeLeft - (days * 86400) - (hours * 3600 )) / 60);
      var seconds = Math.floor((timeLeft - (days * 86400) - (hours * 3600) - (minutes * 60)));

      if (hours < "10") { hours = "0" + hours; }
      if (minutes < "10") { minutes = "0" + minutes; }
      if (seconds < "10") { seconds = "0" + seconds; }

      $("#days").html(days + "<span>Days</span>");
      $("#hours").html(hours + "<span>Hours</span>");
      $("#minutes").html(minutes + "<span> minutes</span>");
      $("#seconds").html(seconds + "<span> seconds</span>");

  }

  function your_func(){
    $("#unpaid_information_modal").modal('hide');
    //isPaymentSuccess();
    //$("#transaction_timeout").modal('show');
  }

    $("body").on("click",".payment_method",function(){
        var payment_method_val = $('input[name="payment_method"]:checked').val();
        if(payment_method_val === '1'){
          $("#bank_transfer_wrapper").show();
          $("#available_methods").hide();
        }
        if(payment_method_val === '2'){
          $("#bank_transfer_wrapper").hide();
          $("#available_methods").show();
        }

    });

    $(document).ready(function() {
        $('#payment_date').datepicker({
            dateFormat: 'dd-mm-yy',
        });
    });

</script>
<!-- <script src="{{asset('js/jquery.datetimepicker.js')}}"></script> -->
</body>
</html>
