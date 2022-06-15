<x-app-layout>
    @section('title', 'Sub Category')

    @push('top_css')
        <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
                <h4><strong>List Of Sub Categories @if (isset($data)) ({{count($data)}}) @endif</strong></h4>
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
                            {{ __('translation.Create Sub-Category') }}
                        </span>
                    </x-button>
                </div>
            </div>

            <div class="flex justify-between flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full" id="datatable">
                        <thead class="border bg-green-300">
                        <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                            <th class="px-4 py-2">Id</th>
                            <th class="px-4 py-2">Image</th>
                            <th class="px-4 py-2">Sub Category</th>
                            <th class="px-4 py-2">Parent Category</th>
                            <th class="px-4 py-2">Manage</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </card>
    </x-card>
    @endif

    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{route('store sub category')}}" id="form-create" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h3 class="modal-title font-bold text-lg">
                            Add Sub Category
                        </h3>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="text-xl">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            @csrf
                            <div class="form-group">
                                <label for="cat_name" class="font-weight-bold">Sub Category Name</label>
                                <input type="text" name='cat_name' class="form-control" required value="{{old('cat_name')}}">
                            </div>
                            <div class="form-group">
                                <label for="parent_category_id" class="font-weight-bold">Select Category</label>
                                <select class="form-control w-full js-example-basic-single" name="parent_category_id">
                                    <option></option>
                                    @if (isset($categories))
                                        @foreach ($categories as $cateroy)
                                            <option value="{{$cateroy->id}}">{{$cateroy->cat_name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sub_category_image" class="font-weight-bold">Upload Image</label>
                                <input type="file" onchange="previewFile(this);" class="form-control" name="sub_category_image" id="sub_category_image" style="height: auto">
                            </div>
                            <img id="previewImg" style="margin-top: 15px;" width="180" height="180" src="{{asset('img/No_Image_Available.jpg')}}" alt="Placeholder">
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

    {{-- update modal --}}
    <div class="modal-update modal-hide">
        <div style="background-color: rgba(0,0,0,0.5)" class="overflow-auto fixed  inset-0 z-10 flex items-center justify-center">
            <div class="bg-white w-11/12 md:max-w-md overflow-y-auto mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex justify-between items-center pb-3">
                    <p class="text-2xl font-bold">Update Sub Category</p>
                    {{-- tombol close --}}
                    <div class="cursor-pointer z-50" id="closeModalUpdate">
                        <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="16" height="18" viewBox="0 0 18 18">
                            <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">
                            </path>
                        </svg>
                    </div>
                </div>
                <form style="max-height:90vh" method="POST" action="{{ route('update sub category') }}" id="form-update-sub-category" enctype="multipart/form-data"></form>
            </div>
        </div>
    </div>
    {{--    @endif--}}
    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>

        <script>
            $(document).ready(function() {
                $('#datatable_1').DataTable({
                    processing: true,
                    order: [[ 0, "asc" ]]
                });

                $('.js-example-basic-single').select2({
                    placeholder: "Select a Parent Category"
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
        </script>

        <script>
            $(document).ready(function() {
                dataTables("{{ route('data sub category') }}?date=" + $(this).val());

                var datatable;
                $('#inputDate').change(function() {
                    datatable.destroy();
                    dataTables("{{ route('data sub category') }}?date=" + $(this).val());
                });

                function dataTables(url) {
                    // Datatable
                    datatable = $('#datatable').DataTable({
                        processing: true,
                        serverSide: true,
                        pageLength: 10,
                        columnDefs: [{
                            'targets': 0,
                        }],
                        select: {
                            style: 'multi'
                        },
                        order: [
                            [1, 'asc']
                        ],
                        "bDeferRender": true,
                        ajax: url,
                        aoColumns: [
                            {
                                name: 'id',
                                data: 'id'
                            },
                            {
                                name: 'image',
                                data: 'image'
                            },
                            {
                                name: 'cat_name',
                                mData: 'cat_name'
                            },
                            {
                                name: 'children',
                                mData: 'children'
                            },
                            {
                                name: 'manage',
                                data: 'manage'
                            }
                        ]
                    });
                }

                $(document).on('click', '#BtnUpdate', function(event) {
                    event.preventDefault();
                    $('.modal-update').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('data sub category') }}?id=' + $(this).data('id'),
                        beforeSend: function() {
                            $('#form-update-sub-category').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-update-sub-category').html(result);
                    });
                });

                $(document).on('click', '#closeModalUpdate', function() {
                    $('.modal-update').addClass('modal-hide');
                });

                $(document).on('click', '#BtnDelete', function() {
                    let drop = confirm('Are you sure?');

                    if (drop) {
                        $.ajax({
                            url: '{{ route("delete sub category") }}',
                            type: 'post',
                            data: {
                                'id': $(this).data('id'),
                                '_token': $('meta[name=csrf-token]').attr('content')
                            },
                            beforeSend: function() {
                                // Pesan yang muncul ketika memproses delete
                            }
                        }).done(function(result) {
                            if (result.status === 1) {
                                // Pesan jika data berhasil di hapus
                                alert('Data deleted successfully');
                                $('#datatable').DataTable().ajax.reload();
                            } else {
                                alert(result.message)
                            }
                        });
                    }
                });
            });
        </script>

    @endpush
</x-app-layout>
