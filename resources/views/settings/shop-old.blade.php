<x-app-layout>
    @section('title', 'Shop')

    @push('top_css')
        <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    @endpush

    {{--    @if (session('assignedPermissions') == 'all' || in_array('Can access menu: Manage Users - Users', session('assignedPermissions')))--}}
    <x-card class="mt-0">
        <div class="" style="margin-top: -2rem">
            @include('settings.menu')
        </div>
        <hr>

        <card>
            <div class="card-title my-4">
                <h4><strong>List Of Shops @if (isset($data)) ({{count($data)}}) @endif</strong></h4>
            </div>
            <p id="menu-title" hidden>shop</p>
            <div class="mt-6">
                @if(session('success'))
                    <x-alert-success>{{ session('success') }}</x-alert-success>
                @endif
                @if (session()->has('error'))
                    <x-alert-danger>{{ session('error') }}</x-alert-danger>
                @endif
                @if ($errors->any())
                    <x-alert-danger>
                        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert-danger>
                @endif

                <div class="w-full lg:w-1/4 mb-6 lg:mb-3">
                    <x-button color="green" id="BtnInsert" data-toggle="modal" data-target="#createModal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="ml-2">
                            {{ __('translation.Create Shop') }}
                        </span>
                    </x-button>
                </div>
            </div>

            <div class="flex justify-between flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full" id="datatable_1">
                        <thead class="border bg-green-300">
                        <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                            <th class="px-4 py-2 border-2">Id</th>
                            <th class="px-4 py-2 border-2">Logo</th>
                            <th class="px-4 py-2 border-2">Name</th>
                            <th class="px-4 py-2 border-2">Phone Number</th>
                            <th class="px-4 py-2 border-2">Address Details</th>
                            <th class="px-4 py-2 border-2">Manage</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if (isset($data))
                            @foreach ($data as $key=>$row)
                                <tr>
                                    <td class="pl-1">{{++$key}}</td>
                                    <td><img src="{{ $row->logo == null ? asset('No-Image-Found.png') : $row->logo }}" alt="" style="width:40px;height:40px"></td>
                                    <td>{{$row->name}}</td>
                                    <td>{{$row->phone}}</td>
                                    <td>
                                        <span class="font-weight-bold text-gray-500">Address: </span>{{$row->address}} <br>
                                        <span class="font-weight-bold text-gray-500">District: </span>{{$row->district}},
                                        <span class="font-weight-bold text-gray-500">Sub-district: </span>{{$row->sub_district}} <br>
                                        <span class="font-weight-bold text-gray-500">Province: </span>{{$row->province}},
                                        <span class="font-weight-bold text-gray-500">Post Code: </span>{{$row->postcode}}
                                    </td>
                                    <td>
                                        <button type="button"  class="btn btn-sm bg-green-500 text-white rounded px-2 py-1  capitalize cursor-pointer"  data-toggle="modal" data-target="#editModal{{$row->id}}"><i class="fas fa-pencil-alt"></i></button>
                                        <button type="button"  class="btn btn-sm bg-red-500 text-white rounded px-2 py-1 mr-4 capitalize cursor-pointer"  data-toggle="modal" data-target="#deleteModal{{$row->id}}" id="BtnDelete"><i class="fas fa-trash-alt"></i></button>
                                        <div class="modal fade" id="editModal{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{url('shops_update/'.$row->id)}}" id="form-import" enctype="multipart/form-data">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title font-bold text-lg" id="editModalLabel">Edit Shop</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div>
                                                                @csrf
                                                                <div class="form-group">
                                                                    <label for="name" class="font-weight-bold">Shop Name</label>
                                                                    <input type="text" name='name' class="form-control" required value="{{ $row->name ?? old('name') }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="address" class="font-weight-bold">Address</label>
                                                                    <input type="text" name='address' class="form-control" value="{{ $row->address ?? old('address') }}">
                                                                </div>

                                                                <div class="grid md:grid-cols-2 md:gap-x-5">
                                                                    <div class="form-group">
                                                                        <label for="district" class="font-weight-bold">District</label>
                                                                        <input type="text" name='district' class="form-control" value="{{ $row->district ?? old('district') }}" autocomplete="on">
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="sub_district" class="font-weight-bold">Sub-district</label>
                                                                        <input type="text" name='sub_district' class="form-control" value="{{ $row->sub_district ?? old('sub_district') }}" autocomplete="on">
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="province" class="font-weight-bold">Province</label>
                                                                        <input type="text" name='province' class="form-control" value="{{ $row->province ?? old('province') }}" autocomplete="on">
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label for="postcode" class="font-weight-bold">Post Code</label>
                                                                        <input type="text" name='postcode' class="form-control" value="{{ $row->postcode ?? old('postcode') }}" autocomplete="on">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="phone" class="font-weight-bold">Phone Number</label>
                                                                    <input type="number" name='phone' class="form-control" value="{{ $row->phone ?? old('phone') }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="logo" class="font-weight-bold">Upload Logo</label>
                                                                    <input type="file" onchange="previewFile2(this);" class="form-control" name="logo" id="logo2" style="height: auto">
                                                                </div>
                                                                @if(!empty($row->logo))
                                                                    <img id="previewImg2" class="mt-2" width="180" height="180" src="{{asset($row->logo)}}" alt="image">
                                                                @else
                                                                    <img id="previewImg2" class="mt-2" width="180" height="180" src="{{asset('img/No_Image_Available.jpg')}}" alt="image">
                                                                @endif

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn bg-gray-500 text-white" data-dismiss="modal">Close</button>
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
                                                            <h6>Do you want to delete this shop?</h6>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn bg-gray-500 text-white" data-dismiss="modal">Close</button>
                                                        <a href="{{url('shops-delete/'.$row->id)}}">
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
        </card>
    </x-card>

    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{route('shops.store')}}" id="form-create" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h3 class="modal-title font-bold text-lg">
                            Add Shop
                        </h3>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="text-xl">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            @csrf
                            <div class="form-group">
                                <label for="name" class="font-weight-bold">Shop Name</label>
                                <input type="text" name='name' class="form-control" required value="{{old('name')}}" placeholder="Enter Shop Name">
                            </div>
                            <div class="form-group">
                                <label for="address" class="font-weight-bold">Address</label>
                                <textarea name="address" id="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                            </div>
                            <div class="grid md:grid-cols-2 md:gap-x-5">
                                <div class="form-group">
                                    <label for="district" class="font-weight-bold">District</label>
                                    <input type="text" name='district' class="form-control" value="{{old('district')}}" autocomplete="on" placeholder="Enter District">
                                </div>
                                <div class="form-group">
                                    <label for="sub_district" class="font-weight-bold">Sub-district</label>
                                    <input type="text" name='sub_district' class="form-control" value="{{old('sub_district')}}" autocomplete="on" placeholder="Enter Sub-district">
                                </div>
                                <div class="form-group">
                                    <label for="province" class="font-weight-bold">Province</label>
                                    <input type="text" name='province' class="form-control" value="{{old('province')}}" autocomplete="on" placeholder="Enter Province">
                                </div>
                                <div class="form-group">
                                    <label for="postcode" class="font-weight-bold">Post Code</label>
                                    <input type="text" name='postcode' class="form-control" value="{{old('postcode')}}" autocomplete="on" placeholder="Enter Post Code">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="phone" class="font-weight-bold">Phone Number</label>
                                <input type="number" name='phone' class="form-control" value="{{old('phone')}}" placeholder="Enter Phone Number">
                            </div>
                            <div class="form-group">
                                <label for="logo" class="font-weight-bold">Upload Logo</label>
                                <input type="file" onchange="previewFile(this);" class="form-control" name="logo" id="logo" style="height: auto">
                            </div>
                            <img id="previewImg" class="mt-2" width="180" height="180" src="{{asset('img/No_Image_Available.jpg')}}" alt="image">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gray-500 text-white" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('layouts.thailand_address')

    {{--    @endif--}}
    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>

        <script>
            $(document).ready(function() {
                $('#datatable_1').DataTable({
                    processing: true,
                    order: [[ 0, "asc" ]]
                });
            });
        </script>

        <script type="text/javascript">
            function previewFile(input){
                var file = $("#logo").get(0).files[0];

                if(file){
                    var reader = new FileReader();
                    reader.onload = function(){
                        $("#previewImg").attr("src", reader.result);
                    }
                    reader.readAsDataURL(file);
                }
            }
            function previewFile2(input){
                var file = $("#logo2").get(0).files[0];

                if(file){
                    var reader = new FileReader();
                    reader.onload = function(){
                        $("#previewImg2").attr("src", reader.result);
                    }
                    reader.readAsDataURL(file);
                }
            }
        </script>

    @endpush
</x-app-layout>




