<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name') }} - Order Purchase</title>

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
  <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
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
</head>

<body class="font-sans antialiased">
  <div class="min-h-screen bg-gray-100">


    <!--     // start navigation area -->

    <style type="text/css">
      a:hover{
        text-decoration: none;
      }
    </style>

    @include('layouts.navigation')


<div class="container">
  <div class="row">
    <div class="col-lg-12 mt-5">


      <div class="card ">

        <div class="card-body">
        <div class="card-title">
          <h4><strong>Purchase Order @if (isset($data)) ({{count($data)}}) @endif</strong></h4>
        </div>
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
            <a href="{{route('order_purchase.create')}}">
              <x-button class="mb-6" color="green" id="BtnInsert">
                <p class="mr-1">Purchase Order</p>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="18px" height="18px">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z" />
                </svg>
              </x-button>
            </a>


            <table class="table-auto border-collapse w-100  border mt-4" id="datatable_1">

              <thead class="border bg-green-300">
                  <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                      <th class="px-4 py-2 border-2 text-center">Id</th>
                      <th class="px-4 py-2 border-2">Order Date</th>
                      {{-- <th class="px-4 py-2 border-2">Product Name</th>
                      <th class="px-4 py-2 border-2 text-center">Product Code</th> --}}
                      {{-- <th class="px-4 py-2 border-2">Quantity</th> --}}
                      <th class="px-4 py-2 border-2 text-center">Supplier Name</th>
                      {{-- <th class="px-4 py-2 border-2">Estimated Date From</th>
                      <th class="px-4 py-2 border-2">Estimated Date To</th> --}}
                      <th width="20%" class="px-4 py-2 border-2 text-center">Estimated Arrival</th>

                      <th class="px-4 py-2 border-2 text-center">Details</th>

                      <th class="px-4 py-2 border-2 text-center">Status</th>
                      <th class="text-center" width="20%" class="px-4 py-2 border-2">Manage</th>
                  </tr>
              </thead>
              <tbody >
                 @if (isset($data))
                     @foreach ($data as $key=>$row)
                     <tr>
                         <!-- <td class="pl-1 text-center">{{++$key}}</td> -->
                         <td class="pl-1 text-center">{{$row->id}}</td>
                         <td class="pl-1 text-center">{{date('d-M-Y', strtotime($row->order_date))}}</td>
                         {{-- <td>
                          @if(isset($row->product))
                            {{$row->product->product_name}}
                          @endif
                        </td> --}}
                         {{-- <td>
                            @if(isset($row->product))
                              {{$row->product->product_code}}
                            @endif
                         </td> --}}
                         {{-- <td>{{$row->quantity}}</td> --}}
                         <td class="text-center">
                           @if(isset($row->supplier))
                            {{$row->supplier->supplier_name}}
                           @endif
                         </td>
                         {{-- <td>{{$row->e_d_f}}</td>
                         <td>{{$row->e_d_t}}</td> --}}
                         <td class="text-center">

                              <strong>From:</strong>
                              @if(isset($row->e_a_d_f))
                               {{date('d-M-Y', strtotime($row->e_a_d_f))}}
                              @else

                              @endif
                             <br>
                              <strong>To :</strong>
                              @if(isset($row->e_a_d_f))
                               {{date('d-M-Y', strtotime($row->e_a_d_t))}}
                              @else

                              @endif


                        </td>

                         <td class="text-right">

                            @if($row->supply_from == 1)
                            <strong> Type:</strong> Import <br>
                            <strong>Factory Tracking:</strong> {{$row->factory_tracking}}<br>
                            <strong>Cargo Reference:</strong> {{$row->cargo_ref}}<br>
                            <strong>No. Cartoons:</strong> {{$row->number_of_cartons}}<br>
                            <strong>Domestic Logistics:</strong> {{$row->domestic_logistics}}
                            @endif
                            @if($row->supply_from == 2)
                            <strong>Type:</strong> Domestic<br>
                           <strong>No. Cartoons:</strong>  {{$row->number_of_cartons1}}<br>
                            <strong>Domestic Logistics:</strong> {{$row->domestic_logistics1}}

                            @endif

                         </td>


                         <td class="text-center">
                            @if($row->status == 'arrive')
                            <span class="badge badge-primary">Arrive</span>
                            @elseif($row->status == 'close')
                            <span class="badge badge-danger">Close</span>
                            @else
                            <span class="badge badge-success">Open</span>
                            @endif
                            <form method="POST" action="{{url('change_otder_purchase_status/')}}" id="form-importss{{$row->id}}" enctype="multipart/form-data">

                              <div class="modal fade" id="changeModal{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Change Status</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                              <label for="">Do you want to change this order purchase status ?</label>

                                                @csrf
                                                <input type="hidden" name="id" value="{{$row->id}}">
                                                <div class="row" style="margin-top: 0px; margin: 0 auto;">
                                                  <div class="col-lg-4"></div>
                                                  <div class="col-lg-4">
                                                  <select class="form-control" id="exampleFormControlSelect1" name='status'>
                                                    @if ($row->status == 'arrive')
                                                    <option value="arrive" selected>Arrived</option>
                                                    @else
                                                    <option value="arrive" >Arrived</option>
                                                    @endif
                                                    @if ($row->status == 'close')
                                                    <option value="close" selected>Close</option>
                                                    @else
                                                    <option value="close" >Close</option>
                                                    @endif
                                                    @if ($row->status == 'open')
                                                    <option value="open" selected >Open</option>
                                                    @else
                                                    <option value="open" >Open</option>
                                                    @endif
                                                  </select>
                                                </div>
                                                <div class="col-lg-4"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">Close</button>

                                                <button type="submit" class="btn bg-red-500 text-white">Yes</button>

                                        </div>
                                    </div>
                                </div>
                              </div>
                            </form>
                         </td>
                          <td class="text-center">
                            <button type="button" class="btn btn-sm bg-blue-500 text-white rounded px-2 py-1  capitalize cursor-pointer"  data-toggle="modal" data-target="#changeModal{{$row->id}}" id="BtnDelete"><i class="fas fa-shipping-fast"></i></button>
                            <a href="{{url('order_purchase/'.$row->id.'/edit')}}" class= "btn btn-sm bg-green-500 text-white rounded px-2 py-1 capitalize cursor-pointer"  ><i class="fas fa-pencil-alt"></i></a>
                            <button type="button" class="btn btn-sm bg-red-500 text-white rounded px-2 py-1  capitalize cursor-pointer"  data-toggle="modal" data-target="#deleteModal{{$row->id}}" id="BtnDelete"><i class="fas fa-trash-alt"></i></button>
                            <button type="button" class="btn btn-sm bg-blue-500 text-white rounded px-2 py-1 capitalize cursor-pointer"  data-toggle="modal" data-target="#productModal{{$row->id}}" id="BtnDelete"><i class="fas fa-box"></i></button>

                            <div class="modal fade" id="productModal{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                              <div class="modal-dialog" role="document">
                                  <div class="modal-content">
                                      <div class="modal-header">
                                          <h5 class="modal-title" id="exampleModalLabel">Product Details</h5>
                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                              <span aria-hidden="true">&times;</span>
                                          </button>
                                      </div>
                                      <div class="modal-body">
                                          <div>
                                            <table class="table mt-3" style="margin-top: 15px;">
                                              <thead class="" style="background-color: #D4EDDA !important;">
                                                <tr style="background-color: #D4EDDA;" style="color: #FFFFFF;">
                                                  <th>Product Name</th>
                                                  <th>Product Code</th>
                                                  <th width="5%">Quantity</th>

                                                </tr>
                                              </thead>
                                              <tbody>
                                                <tr>
                                                @if (isset($row->orderProductDetails))
                                                  @foreach ($row->orderProductDetails as $item)
                                                    <tr>
                                                      <td>@if (isset($item->product)){{$item->product->product_name}} @endif</td>
                                                      <td>@if (isset($item->product)){{$item->product->product_code}} @endif</td>
                                                      <td>{{$item->quantity}}</td>
                                                    </tr>
                                                  @endforeach
                                                @endif
                                                </tr>
                                              </tbody>
                                            </table>
                                          </div>
                                      </div>
                                      <div class="modal-footer">
                                          <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">Close</button>

                                      </div>
                                  </div>
                              </div>
                            </div>
                          <div class="modal fade" id="deleteModal{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div>
                                            <h6>Do you want to delete this Item</h6>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">Close</button>
                                        <a href="{{url('order_purchase-delete/'.$row->id)}}">
                                            <button type="button" class="btn bg-red-500 text-white">Yes</button>
                                        </a>
                                    </div>
                                </div>
                            </div>
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
<script>
     $(document).ready(function() {
                datatable = $('#datatable_1').DataTable({
                processing: true,
                order: [[ 0, "asc" ]]
                });
            });
</script>
</body>

</html>
