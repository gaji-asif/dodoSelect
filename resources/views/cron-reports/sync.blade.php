<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'DodoStock') }} - Sync Order Purchase</title>


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

          @csrf
          <div class="row">

            <div class="col-lg-12 mt-5">
              <div class="card">
                <div class="card-body row">
                  <div class="card-title">
                    <h4 class="col-lg-12 mb-3"><strong>Sync Purchase Order</strong></h4>
                  </div>
                  <div class="col-lg-4">
                    <div class="form-group">
                      <label for="exampleInputEmail1">Shop</label>

                      <?php //echo "<pre>"; print_r($shops); echo "</pre>"; ?>
                      <select id="shop" style="width: 100%; margin-bottom: 15px;" class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 js-example-basic-single3" name="shop_id" required>
                        <option></option>

                        @if (isset($editData))
                        @if (isset($shops))
                        @foreach ($shops as $shop)
                        <option data-site_url="{{$shop->site_url}}" data-key="{{$shop->rest_api_key}}"  data-secrete="{{$shop->rest_api_secrete}}" value="{{$shop->id}}" @if($editData->shop_id == $shop->id) selected @endif>{{$shop->name}}</option>
                        @endforeach
                        @endif
                        @else
                        @if (isset($shops))
                        @foreach ($shops as $shop)
                        <option data-site_url="{{$shop->site_url}}" data-key="{{$shop->rest_api_key}}"  data-secrete="{{$shop->rest_api_secrete}}" value="{{$shop->id}}">{{$shop->name}}</option>
                        @endforeach
                        @endif
                        @endif
                      </select>
                    </div>

                  </div>

                  <div class="col-lg-2">
                    <div class="form-group">
                      <label  for="exampleInputEmail1">&nbsp;</label>
                      <div class="text-left">
                      <button id="btn_sync_order" type="submit" class="btn btn-success">Load</button>
                      </div>
                    </div>
                  </div>



                      @if (isset($editData->orderProductDetails))
                      <div class="full-card show">
                        @else
                        <div class="full-card">
                          @endif
                          <table class="table table-bordered mb-5 table-auto border-collapse w-full border mt-4">

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


                      </div>


                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

      </div>



      <script>
        $(document).ready(function() {
          $('.js-example-basic-single3').select2({
            placeholder: "Select A Shop Name"
          });
        });
      </script>


      <script type="text/javascript">
      $( document ).ready(function() {
          $('#btn_sync_order').click(function () {
              var website_id = $('#shop option:selected').val();
              var site_url = $('#shop option:selected').attr('data-site_url');
              var consumer_key = $('#shop option:selected').attr('data-key');
              var consumer_secret = $('#shop option:selected').attr('data-secrete');
              if (typeof consumer_key === "undefined") {
                  $('tbody').html("<tr><th>Please Select A Shop</th></tr>");
                  return;
              }
              if (consumer_key === "") {
                  $('tbody').html("<tr><th>Please add REST API Consumer Key</th></tr>");
                  return;
              }

              if (consumer_secret === "") {
                  $('tbody').html("<tr><th>Please add REST API Consumer Secrete</th></tr>");
                  return;
              }

              $(".page-item").removeClass("active");
              $(this).parents("li").addClass("active");
              $('tbody').html("<tr><th>Proccesing...</th></tr>");
              $.ajax({
                  url: '{{ route('wc_orders_pagination') }}',
                  type: 'post',
                  data: {
                      'website_id': website_id,
                      'site_url': site_url,
                      'consumer_key': consumer_key,
                      'consumer_secret': consumer_secret,
                      'page': 1,
                      'limit': 100,
                      'per_page': 100,
                      '_token': $('meta[name=csrf-token]').attr('content')
                  },
                  success: function (data){
                     //console.log(data);
                    //$('tbody').html(data);
                    $('tbody').html("<tr><th colspan=11>Orders are Synchronized successfully...</th></tr>");



                  }
              });
            });
          });

</script>

</body>

</html>
