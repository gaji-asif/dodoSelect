<x-app-layout>

    @section('title')
        {{ __('translation.Category') }}
    @endsection

    @push('bottom_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    @endpush


    <div class="col-span-12">
        <div class="row">

            <div class="col-lg-12 mt-3">
                <div class="card">
                    <div class="card-body">
                         <div class="">
                            @include('settings.menu')
                        </div>
                        <hr>
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
                        <div class="card-title" style="margin-bottom: 25px;">
                            <h4>
                                <strong>List Of Shop @if (isset($data)) ({{count($data)}}) @endif</strong>
                            </h4>
                        </div>

                        <div class="w-full overflow-x-auto">
                            <table class="table-auto border-collapse w-full border mt-4" id="datatable_1">
                                <thead class="border bg-gray-500">
                                    <tr>
                                        <th class="px-4 py-2 border-2">Id</th>
                                        <th class="px-4 py-2 border-2">Name</th>
                                        <th class="px-4 py-2 border-2">Site URL</th>
                                        <th class="px-4 py-2 border-2" style="width: 170px;">Manage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($data))
                                        @foreach ($data as $key=>$row)
                                            <tr>
                                                <td class="pl-1">
                                                    {{ ++$key }}
                                                </td>
                                                <td>
                                                    {{ $row->name }}
                                                </td>
                                                <td>
                                                    {{ $row->site_url }}
                                                </td>
                                                <td>
                                                    <a href="{{ url('shops-refresh/'.$row->id) }}" class="btn btn-sm bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer">
                                                        <i class="fas fa-sync"></i>
                                                    </a>
                                                    <button type="button"  class="btn btn-sm bg-green-500 text-white rounded px-2 py-1  capitalize cursor-pointer"  data-toggle="modal" data-target="#editModal{{$row->id}}"><i class="fas fa-pencil-alt"></i></button>
                                                    <button type="button" class="btn btn-sm bg-red-500 text-white rounded px-2 py-1 mr-4 capitalize cursor-pointer" data-toggle="modal" data-target="#deleteModal{{$row->id}}" id="BtnDelete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>

                                                    <div class="modal fade" id="editModal{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                        <form method="POST" action="{{ url('shops_update/' . $row->id) }}" id="form-import" enctype="multipart/form-data">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title font-bold text-lg" id="exampleModalLabel">Edit Shop</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div>
                                                                        @csrf
                                                                        <div class="form-group">
                                                                            <label for="name" class="font-weight-bold">Name</label>
                                                                            <select class="form-control w-full js-example-basic-single" name="shop_id" style="width:100% !important">
                                                                                <option></option>
                                                                                @if (isset($shops))
                                                                                    @foreach ($shops as $shop)
                                                                                        <option value="{{$shop->id}}" @if($shop->id ==  $row->shop_id) selected @endif >{{$shop->name}}</option>
                                                                                    @endforeach
                                                                                @endif
                                                                            </select>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label for="site_url" class="font-weight-bold">
                                                                                Website URL <x-form.required-mark/>
                                                                            </label>
                                                                            <input type="text" name="site_url"  class="form-control" id="site_url" placeholder="Website URL" required  value="@if(isset($row)){{$row->site_url}}@else{{old('site_url')}}@endif">
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="rest_api_key" class="font-weight-bold">
                                                                                REST API Key <x-form.required-mark/>
                                                                            </label>
                                                                            <input type="text" name="rest_api_key" class="form-control" id="rest_api_key" placeholder="REST API Secrete" required  value="@if(isset($row)){{$row->rest_api_key}}@else{{old('rest_api_key')}}@endif">
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="rest_api_secrete" class="font-weight-bold">
                                                                                REST API Secrete <x-form.required-mark/>
                                                                            </label>
                                                                            <input type="text" name="rest_api_secrete" class="form-control" id="rest_api_secrete" placeholder="REST API Secrete" required  value="@if(isset($row)){{$row->rest_api_secrete}}@else{{old('rest_api_secrete')}}@endif">
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
                                                                    <button type="button" class="btn bg-green-500 text-white" data-dismiss="modal">
                                                                        Close
                                                                    </button>
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
                </div>
            </div>
        </div>
    </div>


       <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <form method="POST" action="{{ route('wc-shops.store') }}" id="form-import" enctype="multipart/form-data">
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
                                <label for="shop_name">
                                    Shop Name <x-form.required-mark/>
                                </label>
                                <select class="form-control w-full js-example-basic-single" name="shop_id" style="width:100% !important">
                                    <option></option>
                                    @if (isset($shops))
                                        @foreach ($shops as $shop)
                                            <option value="{{$shop->id}}">{{$shop->name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>


                            <div class="form-group">
                                <label for="site_url">
                                    Website URL <x-form.required-mark/>
                                </label>
                                <x-input type="text" name="site_url" id="site_url" placeholder="Website URL" required value="{{ old('site_url') }}" />

                            </div>
                            <div class="form-group">
                                <label for="rest_api_key">
                                    REST API Key <x-form.required-mark/>
                                </label>
                                <x-input type="text" name="rest_api_key" id="rest_api_key" placeholder="REST API Secrete" required value="{{ old('rest_api_key') }}" />

                            </div>
                            <div class="form-group">
                                <label for="rest_api_secrete">
                                    REST API Secrete <x-form.required-mark/>
                                </label>
                                    <x-input type="text" name="rest_api_secrete" id="rest_api_secrete" placeholder="REST API Secrete" required value="{{ old('rest_api_secrete') }}" />
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gray-500 text-white" data-dismiss="modal">Close</button>
                        <x-button type="submit" color="blue">Submit</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                $('.js-example-basic-single').select2({
                    placeholder: "Select a Shop"
                });
            });
        </script>
        <script>
            $(document).ready(function() {
                $('#datatable_1').DataTable({
                    processing: true,
                    order: [
                        [0, "asc"]
                    ]
                });
            });
        </script>
    @endpush

</x-app-layout>
