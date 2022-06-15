<x-app-layout>
    @section('title', 'Manage Seller')
        <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
        <style>
            [x-cloak] {
                display: none;
            }

            .duration-300 {
                transition-duration: 300ms;
            }

            .ease-in {
                transition-timing-function: cubic-bezier(0.4, 0, 1, 1);
            }

            .ease-out {
                transition-timing-function: cubic-bezier(0, 0, 0.2, 1);
            }

            .scale-90 {
                transform: scale(.9);
            }

            .scale-100 {
                transform: scale(1);
            }

            .modal-hide {
                display: none !important;
            }

        </style>
        <x-card title="Manage Seller">
            <div class="mt-6">
                @if(session('success'))
                <x-alert-success>{{ session('success') }}</x-alert-success>
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
                <x-button class="mb-6" color="green" id="BtnInsert">
                    <p class="mr-1">Add Seller</p>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="18px" height="18px">
                        <path d="M0 0h24v24H0z" fill="none" />
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z" />
                    </svg>
                </x-button>

                <!-- insert modal -->
                <div class="modal-insert modal-hide">
                    <div style="background-color: rgba(0,0,0,0.5)"
                        class="overflow-auto fixed inset-0 z-10 flex items-center justify-center">
                        <div class="bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6"
                            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                            x-transition:enter-end="opacity-100 scale-100">

                            <div class="flex justify-between items-center pb-3">
                                <p class="text-2xl font-bold">Insert Seller</p>
                                {{-- tombol close --}}
                                <div class="cursor-pointer z-50" id="closeModalInsert">
                                    <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18"
                                        height="18" viewBox="0 0 18 18">
                                        <path
                                            d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">
                                        </path>
                                    </svg>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('insert seller') }}" id="form-insert">
                                @csrf
                                <div>
                                    <x-label>
                                        Shop ID
                                    </x-label>
                                    <x-input type="text" name="shop_id" id="shop_id" :value="old('shop_id')" required>
                                    </x-input>
                                </div>
                                <div class="mt-6">
                                    <x-label>
                                        Name
                                    </x-label>
                                    <x-input type="text" name="name" id="name" :value="old('name')" required>
                                    </x-input>
                                </div>
                                <div class="mt-6">
                                    <x-label>
                                        Email
                                    </x-label>
                                    <x-input type="text" name="email" id="email" :value="old('email')" required>
                                    </x-input>
                                </div>

                                <div class="mt-6">
                                    <x-label>
                                        Password
                                    </x-label>
                                    <x-input type="password" name="password" id="password" :value="old('password')">
                                    </x-input>
                                </div>

                                <div class="flex justify-end mt-4">
                                    <x-button color="blue">Save</x-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- update modal --}}
                <div class="modal-update modal-hide">
                    <div style="background-color: rgba(0,0,0,0.5)"
                        class="overflow-auto fixed  inset-0 z-10 flex items-center justify-center">
                        <div class="bg-white w-11/12 md:max-w-md overflow-y-auto mx-auto rounded shadow-lg pt-4 pb-6 text-left px-6"
                            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                            x-transition:enter-end="opacity-100 scale-100">

                            <div class="flex justify-between items-center pb-3">
                                <p class="text-2xl font-bold">Update Seller</p>
                                {{-- tombol close --}}
                                <div class="cursor-pointer z-50" id="closeModalUpdate">
                                    <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18"
                                        height="18" viewBox="0 0 18 18">
                                        <path
                                            d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">
                                        </path>
                                    </svg>
                                </div>
                            </div>

                            <form style="max-height:90vh" method="POST" action="{{ route('update seller') }}"
                                id="form-update"></form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-between flex-col">
                <div class="overflow-x-auto">
                    <table class="table-auto border-collapse w-full border mt-4" id="datatable">
                        <thead class="border bg-green-300">
                            <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                                <th class="px-4 py-2">Registered at</th>
                                <th class="px-4 py-2">Shop Id</th>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Email</th>
                                <th class="px-4 py-2">Total Orders</th>
                                <th class="px-4 py-2">Manage</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </x-card>


        <script>
            $(document).ready(function() {
                dataTables("{{ route('data seller') }}?date=" + $(this).val());
                var datatable;
                $('#inputDate').change(function() {
                    datatable.destroy();
                    // console.log($(this).val());
                    dataTables("{{ route('data seller') }}?date=" + $(this).val());
                });

                function dataTables(url) {
                    // Datatable
                    datatable = $('#datatable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: url,
                        columns: [{
                                name: 'Registerd at',
                                data: 'created_at'
                            },
                            {
                                name: 'shop_id',
                                data: 'shop_id'
                            },
                            {
                                name: 'name',
                                data: 'name'
                            },
                            {
                                name: 'email',
                                data: 'email'
                            },
                            {
                                name: 'orders_total',
                                data: 'orders_total'
                            },
                            {
                                name: 'manage',
                                data: 'manage'
                            }
                        ]
                    });
                }
                $('#BtnInsert').click(function() {
                    $('.modal-insert').removeClass('modal-hide');
                });
                $(document).on('click', '#BtnUpdate', function() {
                    $('.modal-update').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('data seller') }}?id=' + $(this).data('id'),
                        beforeSend: function() {
                            $('#form-update').html('Loadin');
                        }
                    }).done(function(result) {
                        $('#form-update').html(result);
                    });
                });
                $(document).on('click', '#closeModalUpdate', function() {
                    $('.modal-update').addClass('modal-hide');
                });
                $('#closeModalInsert').click(function() {
                    $('.modal-insert').addClass('modal-hide');
                });
                $(document).on('click', '#BtnDelete', function() {
                    let drop = confirm('Are you sure?');
                    if (drop) {
                        $.ajax({
                            url: '{{ route('delete seller') }}',
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

                                alert(result.message);

                            }
                        });
                    }
                });
            });

        </script>

    </x-app-layout>
