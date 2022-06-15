<x-app-layout>
    @section('title', 'Manage Product Cost')

    @if(\App\Models\Role::checkRolePermissions('Can access menu:Purchase Order - Cost Analysis'))
        <x-card title="Product ({{count($data)}})">
            <div class="mt-6">
                @if (session('success'))
                    <x-alert-success>
                        {{ session('success') }}
                    </x-alert-success>
                @endif

                @if (session('danger'))
                    <x-alert-danger>
                        {{ session('danger') }}
                    </x-alert-danger>
                @endif

                @if (session('error'))
                    <x-alert-danger>
                        {{ session('error') }}
                    </x-alert-danger>
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

                @if (session('bulk_cost_analysis'))
                    {{ Session::forget('bulk_cost_analysis')}}
                @endif
            </div>

            <div class="flex justify-between flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full" id="datatable">
                        <thead>
                        <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                            <th class="px-4 py-2 text-center">Details</th>
                            <th class="px-4 py-2 text-center"></th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="modal-producut modal-hide">
                <div style="background-color: rgba(0,0,0,0.5)" class="overflow-auto fixed  inset-0 z-10 flex items-center justify-center">
                    <div class="bg-white w-11/12  overflow-y-auto mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" style="width: 60%;">
                        <div style="max-height:90vh" id="form-producut"></div>
                    </div>
                </div>
            </div>

            {{-- update modal --}}
            <div class="modal-update modal-hide">
                <div style="background-color: rgba(0,0,0,0.5)" class="overflow-auto fixed  inset-0 z-10 flex items-center justify-center">
                    <div class="bg-white w-11/12 md:max-w-md overflow-y-auto mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                        <div class="flex justify-between items-center pb-3">
                            <p class="text-2xl font-bold">Update Product</p>
                            {{-- tombol close --}}
                            <div class="cursor-pointer z-50" id="closeModalUpdate">
                                <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="16" height="18" viewBox="0 0 18 18">
                                    <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <form style="max-height:90vh" method="POST" action="{{ route('update product cost') }}" id="form-update-product-cost" enctype="multipart/form-data"></form>
                    </div>
                </div>
            </div>
        </x-card>
    @endif

    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script>
            $(document).ready(function() {
                dataTables("{{ route('datatable product cost analysis') }}?date=" + $(this).val());

                var datatable;
                $('#inputDate').change(function() {
                    datatable.destroy();
                    dataTables("{{ route('datatable product cost analysis') }}?date=" + $(this).val());
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
                        columns: [
                            {
                                name: 'details',
                                data: 'details',
                                orderable: false
                            },
                            {
                                name: 'manage',
                                data: 'manage',
                                orderable: false
                            }
                        ],
                    });
                }

                $(document).on('click', '#BtnUpdate', function(event) {
                    event.preventDefault();
                    $('.modal-update').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('datatable product cost analysis') }}?id=' + $(this).data('id'),
                        beforeSend: function() {
                            $('#form-update-product-cost').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-update-product-cost').html(result);
                    });
                });

                $(document).on('click', '#closeModalUpdate', function() {
                    $('.modal-update').addClass('modal-hide');
                });
            });
        </script>
    @endpush
</x-app-layout>
