<x-app-layout>
    @section('title', 'Product Tags')

    @push('top_css')
        <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    @endpush

    @if(session('roleName') != 'dropshipper')
        <x-card class="mt-0">
        <div class="" style="margin-top: -2rem">
            @include('settings.menu')
        </div>
        <hr>

        <card class="bg-gray-500 ">
            <div class="card-title my-4">
                <h4><strong>List Of Product Tags @if (isset($data)) ({{count($data)}}) @endif</strong></h4>
            </div>
            <div class="mt-6 ">
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
                            {{ __('translation.Create Product Tag') }}
                        </span>
                    </x-button>
                </div>
            </div>

            <div class="flex justify-between flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full" id="datatable">
                        <thead class="border bg-green-300">
                        <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                            <th class="px-4 py-2 border-2">Id</th>
                            <th class="px-4 py-2 border-2">Name</th>
                            <th class="px-4 py-2 border-2">Manage</th>
                        </tr>
                        </thead>
                        <tbody >
                        @if (isset($data))
                            @foreach ($data as $key=>$row)
                                <tr>
                                    <td class="pl-1">{{++$key}}</td>
                                    <td>{{$row->name}}</td>
                                    <td>
                                        <button type="button"  class="btn btn-sm bg-green-500 text-white rounded px-2 py-1  capitalize cursor-pointer"  data-toggle="modal" data-target="#editModal{{$row->id}}"><i class="fas fa-pencil-alt"></i></button>
                                        <button type="button"  class="btn btn-sm bg-red-500 text-white rounded px-2 py-1 mr-4 capitalize cursor-pointer"  data-toggle="modal" data-target="#deleteModal{{$row->id}}" id="BtnDelete"><i class="fas fa-trash-alt"></i></button>

                                       {{-- update modal--}}
                                        <div class="modal fade" id="editModal{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{url('product-tags-update/'.$row->id)}}" id="form-update" enctype="multipart/form-data">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title font-bold text-lg">
                                                                Edit Product Tag
                                                            </h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div>
                                                                @csrf
                                                                <div class="form-group">
                                                                    <label for="name" class="font-weight-bold">Product Tag</label>
                                                                    <input type="text" name='name' class="form-control" required value="@if(isset($row)){{$row->name}}@else{{old('name')}}@endif">
                                                                </div>
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

                                        {{-- delete modal--}}
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
                                                            <h6>Do you want to delete this product tag?</h6>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn bg-gray-500 text-white" data-dismiss="modal">Close</button>
                                                        <a href="{{url('product-tags-delete/'.$row->id)}}">
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
    @endif

    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{route('product-tags.store')}}" id="form-create" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h3 class="modal-title font-bold text-lg">
                            Add Product Tag
                        </h3>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="text-xl">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            @csrf
                            <div class="form-group">
                                <label for="name" class="font-weight-bold">Product Tag</label>
                                <input type="text" name='name' class="form-control" required value="{{old('name')}}">
                            </div>
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
    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

        <script>
            $(document).ready(function() {
                $('#datatable').DataTable({
                    processing: true,
                    order: [[ 0, "asc" ]]
                });
            });
        </script>
    @endpush
</x-app-layout>




