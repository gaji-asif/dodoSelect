<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name') }} - Add Order Purchase</title>

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


  <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
  <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">


  <link href = "https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css"
  rel = "stylesheet">
<script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

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
    </style>

    @include('layouts.navigation')

<div class="container">
  <div class="row">
    <div class="col-lg-2"></div>
    <div class="col-lg-8 mt-5 ">
      <div class="card mb-5">

        <div class="card-body">
            <div class="card-title" style="margin-bottom: 25px;">
              <h4><strong>Add Order Purchase</strong></h4>
            </div>
            @if(session()->has('success'))
            <div class="alert alert-success mb-3 background-success" role="alert">
              {{ session()->get('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif
            @if(session()->has('danger'))
            <div class="alert alert-danger mb-3 background-danger" role="alert">
              {{ session()->get('danger') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif
              @if (isset($editData))
                <form method="POST" action="{{url('order_purchase_update/'.$editData->id)}}" id="form-import" enctype="multipart/form-data">
              @else
                <form method="POST" action="{{route('order_purchase.store')}}" id="form-import" enctype="multipart/form-data">
              @endif
              @csrf


              <div class="row">
                <div class="col-lg-6 mt-1">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Product Name</label>
                    <select style="width: 100%;" class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 js-example-basic-single" name="product_id" required>
                    <option></option>

                      @if (isset($editData))
                        @if (isset($products))
                          @foreach ($products as $product)
                            <option value="{{$product->id}}" @if($editData->product_id == $product->id) selected @endif>{{$product->product_name}}</option>
                          @endforeach
                        @endif
                      @else
                        @if (isset($products))
                          @foreach ($products as $product)
                            <option value="{{$product->id}}">{{$product->product_name}}</option>
                          @endforeach
                        @endif
                      @endif
                    </select>

                    {{-- <input type="text" name='product_code' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Product Code" required value="{{old('product_code')}}"> --}}
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Product Quantity</label>
                    <input type="number" name='quantity'  min='1' class="form-control w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Product Quantity" required value="@if(isset($editData)){{$editData->quantity}}@else{{old('quantity')}}@endif">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Tracking Number</label>
                    <input type="text" name='tracking_number' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Tracking Number" value="@if(isset($editData)){{$editData->tracking_number}}@else{{old('tracking_number')}}@endif">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Reference</label>
                    <input type="text" name='reference' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Reference" required value="@if(isset($editData)){{$editData->reference}}@else{{old('reference')}}@endif">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="e_d_f">Estimate Date From</label>
                    <input type="text" name='e_d_f' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 datepicker-1" id="e_d_f" aria-describedby="emailHelp" placeholder="Estimate Date From" required value="@if(isset($editData)){{$editData->e_d_f}}@else{{old('e_d_f')}}@endif">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="e_d_t">Estimate Date To</label>
                    <input type="text" name='e_d_t' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 datepicker-1" id="e_d_t" aria-describedby="emailHelp" placeholder="Estimate Date To" required value="@if(isset($editData)){{$editData->e_d_t}}@else{{old('e_d_t')}}@endif">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="e_a_d_f">Estimate Arrival Date From</label>
                    <input type="text" name='e_a_d_f' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 datepicker-1" id="e_a_d_f" aria-describedby="emailHelp" placeholder="Estimate Arrival Date From" required value="@if(isset($editData)){{$editData->e_a_d_f}}@else{{old('e_a_d_f')}}@endif">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="e_a_d_t">Estimate Arrival Date To</label>
                    <input type="text" name='e_a_d_t' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 datepicker-1" id="e_a_d_t" aria-describedby="emailHelp" placeholder="Estimate Arrival Date To" required value="@if(isset($editData)){{$editData->e_a_d_t}}@else{{old('e_a_d_t')}}@endif">
                  </div>
                </div>

                <div class="col-lg-6 mt-1">
                  <div class="form-group">
                    <label for="exampleInputEmail1">Supplier Name</label>
                    <select style="width: 100%;" class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 js-example-basic-single2" name="supplier_id" required>
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
                </div>
                <div class="col-lg-12 pb-3">
                  <label for="exampleInputEmail1">Note</label>
                  <div class="input-group">
                    <textarea name="note" class="form-control" aria-label="With textarea">@if(isset($editData)){{$editData->note}}@else{{old('note')}}@endif</textarea>
                  </div>
                </div>

                <div class="col-lg-12 pb-2 checkboxs">
                  <input type="radio" name="check" value="1" id="checkin" ><label for="checkin" style="padding-left: 5px"> <strong>Import</strong></label>
                  <input type="radio" name="check" value="2" id="checkout" style="margin-left: 10px"><label for="checkout" style="padding-left: 5px"> <strong>Domestic</strong></label> <br>
                </div>

              </div>
              <div class="col-lg-12 pb-2 checkboxs">
                @if (isset($editData->supply_from) )
                  <input type="hidden" class="getSupplier_from" value="{{$editData->supply_from}}">
                @endif
              <div class="row hide" id="imports">
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="factory_tracking">Factory Tracking</label>
                    <input type="text" name='factory_tracking' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="factory_tracking" aria-describedby="emailHelp" placeholder="Factory Tracking" value="@if(isset($editData)){{$editData->factory_tracking}}@else{{old('factory_tracking')}}@endif">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="cargo_ref">Cargo Reference</label>
                    <input type="text" name='cargo_ref' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="cargo_ref" aria-describedby="emailHelp" placeholder="Cargo Reference" value="@if(isset($editData)){{$editData->cargo_ref}}@else{{old('cargo_ref')}}@endif">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="number_of_cartons">Number Of Cartons</label>
                    <input type="number" min='1' name='number_of_cartons' class="form-control w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="number_of_cartons" aria-describedby="emailHelp" placeholder="Number Of Cartons" value="@if(isset($editData)){{$editData->number_of_cartons}}@else{{old('number_of_cartons')}}@endif">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="domestic_logistics">Domestic Logistics</label>
                    <input type="text" name='domestic_logistics' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="domestic_logistics" aria-describedby="emailHelp" placeholder="Domestic Logistics"  value="@if(isset($editData)){{$editData->domestic_logistics}}@else{{old('domestic_logistics')}}@endif">
                  </div>
                </div>
              </div>

              <div class="row hide" id="domestic">
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="number_of_cartons1">Number Of Cartons</label>
                    <input type="number" min='1' name='number_of_cartons1' class="form-control w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="number_of_cartons1" aria-describedby="emailHelp" placeholder="Number Of Cartons"  value="@if(isset($editData)){{$editData->number_of_cartons1}}@else{{old('number_of_cartons1')}}@endif">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label for="domestic_logistics">Domestic Logistics</label>
                    <input type="text" name='domestic_logistics1' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="domestic_logistics1" aria-describedby="emailHelp" placeholder="Domestic Logistics"  value="@if(isset($editData)){{$editData->domestic_logistics1}}@else{{old('domestic_logistics1')}}@endif">
                  </div>
                </div>
              </div>

              <div class="text-right">
                <button type="submit" class="btn btn-primary">Submit</button>
              </div>
            </form>

        </div>
      </div>
    </div>


  </div>

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
   $(document).ready(function() {
    $('#datatable_1').DataTable({
        processing: true,
        order: [[ 0, "asc" ]]
    });
  });

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

          $(factory_tracking).prop('required',true);
          $(cargo_ref).prop('required',true);
          $(number_of_cartons).prop('required',true);
          $(domestic_logistics).prop('required',true);
        }
        if($(domestic).hasClass('show'))
        {
          $(domestic).removeClass('show');
          $(domestic).addClass('hide');

          $(number_of_cartons1).prop('required',false);
          $(domestic_logistics1).prop('required',false);
        }
      }
      if($('#checkout')[0].checked)
      {
        // console.log(2);
        if($(imports).hasClass('show'))
        {
          $(imports).removeClass('show');
          $(imports).addClass('hide');

          $(number_of_cartons1).prop('required',true);
          $(domestic_logistics1).prop('required',true);
        }
        if($(domestic).hasClass('hide'))
        {
          $(domestic).removeClass('hide');
          $(domestic).addClass('show');

          $(factory_tracking).prop('required',false);
          $(cargo_ref).prop('required',false);
          $(number_of_cartons).prop('required',false);
          $(domestic_logistics).prop('required',false);
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

          $(factory_tracking).prop('required',true);
          $(cargo_ref).prop('required',true);
          $(number_of_cartons).prop('required',true);
          $(domestic_logistics).prop('required',true);
        }
        if($(domestic).hasClass('show'))
        {
          $(domestic).removeClass('show');
          $(domestic).addClass('hide');

          $(number_of_cartons1).prop('required',false);
          $(domestic_logistics1).prop('required',false);
        }
    }
    if(getVal == 2)
    {
        $('#checkout').prop('checked',true);
      if($(imports).hasClass('show'))
        {
          $(imports).removeClass('show');
          $(imports).addClass('hide');

          $(number_of_cartons1).prop('required',true);
          $(domestic_logistics1).prop('required',true);
        }
        if($(domestic).hasClass('hide'))
        {
          $(domestic).removeClass('hide');
          $(domestic).addClass('show');

          $(factory_tracking).prop('required',false);
          $(cargo_ref).prop('required',false);
          $(number_of_cartons).prop('required',false);
          $(domestic_logistics).prop('required',false);
        }
    }
  });


</script>
</body>

</html>
