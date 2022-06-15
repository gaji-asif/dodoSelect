<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DodoStock') }} - Category</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

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
            height:   42px;
            /* border-color: rgba(209,213,219,var(--tw-border-opacity)); */
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered{
            line-height: 42px;
        }
        .card-title{
            font-size: 18px;
            margin-bottom: 10px;
        }
    </style>

    @include('layouts.navigation')

    <div class="container">
        <div class="row">
            <div class="col-lg-12 mt-3">
                <div class="card">
                    <div class="card-body">
                        @include('settings.menu')
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mt-3">
                <div class="card">

                    <div class="card-body">
                        <div class="card-title" style="margin-bottom: 25px;">
                            @if (isset($editData))
                                <h4><strong>Edit Category</strong></h4>
                            @else
                                <h4><strong>Add Sub Category</strong></h4>
                            @endif

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
                            <form method="POST" action="{{url('sub_categories_update/'.$editData->id)}}" id="form-import" enctype="multipart/form-data">
                                @else
                                    <form method="POST" action="{{route('sub_categories.store')}}" id="form-import" enctype="multipart/form-data">
                                        @endif
                                        @csrf

                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Sub Category Name</label>
                                            <input type="text" name='cat_name' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Sub Category Name" required value="@if(isset($editData)){{$editData->cat_name}}@else{{old('cat_name')}}@endif">
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Select Category</label>
                                            <select style="width: 100%;" class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 js-example-basic-single" name="parent_category_id">
                                                <option></option>

                                                @if (isset($editData))
                                                    @if (isset($categories))
                                                        @foreach ($categories as $cateroy)
                                                            <option value="{{$cateroy->id}}" @if($editData->parent_category_id == $cateroy->id) selected @endif>{{$cateroy->cat_name}}</option>
                                                        @endforeach
                                                    @endif
                                                @else
                                                    @if (isset($categories))
                                                        @foreach ($categories as $cateroy)
                                                            <option value="{{$cateroy->id}}">{{$cateroy->cat_name}}</option>
                                                        @endforeach
                                                    @endif
                                                @endif
                                            </select>
                                            {{-- <input type="text" name='product_code' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Product Code" required value="{{old('product_code')}}"> --}}
                                        </div>
                                        <div class="form-group">
                                            <label class="exampleInputEmail12">
                                                Upload Image
                                            </label>
                                            <input type="file" onchange="previewFile(this);" class="block mt-1 w-full" name="sub_category_image" id="sub_category_image" required="required">
                                        </div>
                                        @if(!empty($user->image))
                                            <img id="previewImg" style="margin-top: 15px;" width="180" height="180" src="{{asset($user->image)}}" alt="Placeholder">
                                        @else
                                            <img id="previewImg" style="margin-top: 15px;" width="180" height="180" src="{{asset('img/No_Image_Available.jpg')}}" alt="Placeholder">
                                        @endif
                                        <div class="text-right">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </form>

                    </div>
                </div>
            </div>
            <div class="col-lg-8 mt-3">


                <div class="card">

                    <div class="card-body">
                        <div class="card-title" style="margin-bottom: 25px;">
                            <h4><strong>List Of Sub Category @if (isset($data)) ({{count($data)}}) @endif</strong></h4>
                        </div>
                        <div class="flex justify-between flex-col">
                            <div class="">
                                <table class="table-auto border-collapse w-full border mt-4" id="datatable_1">
                                    <thead class="border bg-green-300">
                                    <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                                        <th class="px-4 py-2 border-2">Id</th>
                                        <th class="px-4 py-2 border-2">Image</th>
                                        <th class="px-4 py-2 border-2">Sub Category</th>
                                        <th class="px-4 py-2 border-2">Parent Category</th>
                                        <th class="px-4 py-2 border-2">Manage</th>
                                    </tr>
                                    </thead>
                                    <tbody >
                                    @if (isset($data))
                                        @foreach ($data as $key=>$row)
                                            <tr >
                                                <td class="pl-1">{{$row->id}}</td>
                                                <td>

                                                    @if(!empty($row->image))
                                                        <img  width="80" height="80" src="{{asset($row->image)}}" alt="Placeholder">
                                                    @else
                                                        <img width="80" height="80" src="{{asset('img/No_Image_Available.jpg')}}" alt="Placeholder">
                                                    @endif
                                                </td>
                                                <td>{{$row->cat_name}}</td>
                                                <td>
                                                    @if (isset($row->children))
                                                        {{$row->children->cat_name}}
                                                    @endif
                                                </td>
                                                <td>
                                                    {{-- <a href="{{url('categories/'.$row->id.'/edit')}}" class= "btn btn-sm bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer"  ><i class="fas fa-pencil-alt"></i></a> --}}
                                                    <button type="button"  class="btn btn-sm bg-green-500 text-white rounded px-2 py-1  capitalize cursor-pointer"  data-toggle="modal" data-target="#editModal{{$row->id}}"><i class="fas fa-pencil-alt"></i></button>
                                                    <button type="button"  class="btn btn-sm bg-red-500 text-white rounded px-2 py-1 mr-4 capitalize cursor-pointer"  data-toggle="modal" data-target="#deleteModal{{$row->id}}" id="BtnDelete"><i class="fas fa-trash-alt"></i></button>

                                                    <div class="modal fade" id="editModal{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <form method="POST" action="{{url('sub_categories_update/'.$row->id)}}" id="form-import" enctype="multipart/form-data">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="exampleModalLabel">Edit</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    @csrf
                                                                    <input type="hidden" name="from" value="2">
                                                                    <div class="modal-body">
                                                                        <div class="form-group">
                                                                            <label for="exampleInputEmail1">Sub Category Name</label>
                                                                            <input type="text" name='cat_name' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Sub Category Name" required value="@if(isset($row)){{$row->cat_name}}@else{{old('cat_name')}}@endif">
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="exampleInputEmail1">Select Category</label>
                                                                            <select style="width: 100%;" class="form-control block font-medium w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 js-example-basic-single" name="parent_category_id" required>
                                                                                <option></option>

                                                                                @if (isset($row))
                                                                                    @if (isset($categories))
                                                                                        @foreach ($categories as $cateroy)
                                                                                            <option value="{{$cateroy->id}}" @if($row->parent_category_id == $cateroy->id) selected @endif>{{$cateroy->cat_name}}</option>
                                                                                        @endforeach
                                                                                    @endif
                                                                                @else
                                                                                    @if (isset($categories))
                                                                                        @foreach ($categories as $cateroy)
                                                                                            <option value="{{$cateroy->id}}">{{$cateroy->cat_name}}</option>
                                                                                        @endforeach
                                                                                    @endif
                                                                                @endif
                                                                            </select>
                                                                            {{-- <input type="text" name='product_code' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Product Code" required value="{{old('product_code')}}"> --}}
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label class="exampleInputEmail12">
                                                                                Upload Image
                                                                            </label>
                                                                            <input type="file" onchange="previewFile2(this);" class="block mt-1 w-full" name="sub_category_image" id="image2">
                                                                        </div>
                                                                        @if(!empty($row->image))
                                                                            <img id="previewImg2" style="margin-top: 15px;" width="180" height="180" src="{{asset($row->image)}}" alt="Placeholder">
                                                                        @else
                                                                            <img id="previewImg2" style="margin-top: 15px;" width="180" height="180" src="{{asset('img/No_Image_Available.jpg')}}" alt="Placeholder">
                                                                        @endif

                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">Close</button>
                                                                        <button type="submit" class="btn btn-primary">Submit</button>
                                                                    </div>
                                                                </form>
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
                                                                    <a href="{{url('categories-delete/'.$row->id)}}">
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
            </div>

        </div>

    </div>


</div>
<script>
    $(document).ready(function() {
        $('.js-example-basic-single').select2({
            placeholder: "Select a Parent Category"
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

    function previewFile(input){
        var file = $("#sub_category_image").get(0).files[0];

        if(file){
            var reader = new FileReader();

            reader.onload = function(){
                $("#previewImg").attr("src", reader.result);
            }

            reader.readAsDataURL(file);
        }
    }

    function previewFile2(input){
        var file = $("#image2").get(0).files[0];

        if(file){
            var reader = new FileReader();

            reader.onload = function(){
                $("#previewImg2").attr("src", reader.result);
            }

            reader.readAsDataURL(file);
        }
    }
</script>
</body>

</html>
