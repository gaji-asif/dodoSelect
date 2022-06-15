<x-app-layout>
    @section('title')
        {{ __('translation.Category') }}
    @endsection

    @push('bottom_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    @endpush

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Shopee - Settings'))
        <div class="col-span-12">
        @if(!empty(session('msg')))
            <div class="row">
                <div class="col-lg-12 mt-3">
                    <div class="w-full  col-span-12 md:col-span-12">
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 my-2 rounded relative" role="alert">
                            <strong class="font-bold">Success!</strong>
                            <div class="alert-content">{{ session('msg') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-lg-12 mt-3">
                <div class="card">
                    <div class="card-body">
                        <div class="w-full lg:w-1/4 mb-6 lg:mb-3">
                            <x-button color="green" id="BtnAuth">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="ml-2">
                                    {{ __('translation.Authorize Shop') }}
                                </span>
                            </x-button>
                        </div>
                        <div class="card-title" style="margin-bottom: 25px;">
                            <h4>
                                <strong>List Of Shop @if (isset($data)) ({{count($data)}}) @endif</strong>
                            </h4>
                        </div>

                        <div class="w-full overflow-x-auto">
                            <table class="table-auto border-collapse w-full border mt-4" id="shopee_shop">
                                <thead class="border bg-gray-500">
                                <tr>
                                    <th class="px-4 py-2 border-2">Id</th>
                                    <th class="px-4 py-2 border-2">Name</th>
                                    <th class="px-4 py-2 border-2">Code</th>
                                    <th class="px-4 py-2 border-2" style="width: 170px;">Manage</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if (isset($shops))
                                    @foreach ($shops as $key=>$row)
                                        <tr>
                                            <td class="pl-1">
                                                {{ $row->shop_id }}
                                            </td>
                                            <td>
                                                {{ $row->shop_name }}
                                            </td>
                                            <td>
                                                {{ $row->code }}
                                            </td>
                                            <td>
                                                <button type="button"  class="btn btn-sm bg-green-500 text-white rounded px-2 py-1  capitalize cursor-pointer"  data-toggle="modal" data-target="#editModal{{$row->id}}"><i class="fas fa-pencil-alt"></i></button>
                                                <button type="button" class="btn btn-sm bg-red-500 text-white rounded px-2 py-1 mr-4 capitalize cursor-pointer" data-toggle="modal" data-target="#deleteModal{{$row->id}}" id="BtnDelete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>

                                                <div class="modal fade" id="editModal{{$row->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <form method="POST" action="{{ route('shopee.update.shop',['id'=>$row->id]) }}" id="form-import" enctype="multipart/form-data">
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
                                                                            <label for="site_url" class="font-weight-bold">
                                                                                Shop name <x-form.required-mark/>
                                                                            </label>
                                                                            <input type="text" name="shop_name"  class="form-control" id="shop_name" placeholder="Shop name" required  value="{{ $row->shop_name }}">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn bg-gray-500 text-white" data-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal fade" id="deleteModal{{$row->id}}" tabindex="-1" role="dialog" aria-hidden="true">
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
                                                                <form method="POST" action="{{ route('shopee.delete.shop', [$row->id]) }}">
                                                                    @csrf
                                                                    <button type="submit" class="btn bg-red-500 text-white">Yes</button>
                                                                </form>
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
    @endif

    <!-- Add shop modal -->
    <div class="modal-insert @if(!request()->get('code')&& !request()->get('shop_id')) modal-hide @endif">
        <div style="background-color: rgba(0,0,0,0.5)"
             class="overflow-auto fixed inset-0 z-10 flex items-center justify-center">
            <div class="bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="flex justify-between items-center pb-3">
                    <p class="text-2xl font-bold">@if(isset($selected_shop)) Update shop @else Add shop @endif</p>
                    <div class="cursor-pointer z-50" id="closeModalInsert">
                        <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18"
                             height="18" viewBox="0 0 18 18">
                            <path
                                d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">
                            </path>
                        </svg>
                    </div>
                </div>

                <form method="POST" action="{{ $shop_authorization_route }}" id="form-insert">
                    @csrf
                    <div>
                        <x-label> Shop name </x-label>
                        <x-input type="text" name="shop_name" id="shop_name" value="{{isset($selected_shop)?$selected_shop->shop_name:old('shop_name')}}" required></x-input>
                    </div>
                    <x-input type="hidden" name="code" id="code" :value="request()->get('code')" required></x-input>
                    <x-input type="hidden" name="shop_id" id="shop_id" :value="request()->get('shop_id')" required></x-input>
                    <div class="flex justify-end mt-4">
                        <x-button color="blue">Save</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                $('#BtnAuth').click(function() {
                    window.location = '{{route('shopee.authorization')}}'
                });

                $('.js-example-basic-single').select2({
                    placeholder: "Select a Shop"
                });
            });
        </script>
        <script>
            $(document).ready(function() {
                $('#shopee_shop').DataTable({
                    processing: true,
                    order: [
                        [0, "asc"]
                    ]
                });
            });
        </script>
    @endpush

</x-app-layout>
