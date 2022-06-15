<x-app-layout>
    @section('title', 'Shop')

    @push('top_css')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">

        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link rel="stylesheet" href="{{ asset('css/typeaheadjs.css') }}">
    @endpush

    @if(session('roleName') != 'dropshipper')
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
                @if (session('success'))
                    <x-alert-success>{{ session('success') }}</x-alert-success>
                @endif
                @if (session('danger'))
                    <x-alert-danger>{{ session('danger') }}</x-alert-danger>
                @endif
                @if (session('error'))
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
                    <x-button color="green" id="BtnInsert" data-toggle="modal"{{-- data-target="#createModal"--}}>
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
                    <table class="w-full" id="datatable">
                        <thead>
                        <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                            <th>Id</th>
                            <th>Logo</th>
                            <th>Name</th>
                            <th>Phone Number</th>
                            <th>Address Details</th>
                            <th>Manage</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </card>
    </x-card>
    @endif

    {{-- insert modal --}}
    <x-modal.modal-small id="__modalInsert" class="modal-update modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Add Shop') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalInsert" />
        </x-modal.header>
        <x-modal.body>
            <form method="POST" action="{{ route('shop.store') }}" id="form-insert" enctype="multipart/form-data">
            </form>
        </x-modal.body>
    </x-modal.modal-small>

    {{-- update modal --}}
    <x-modal.modal-small id="__modalUpdate" class="modal-update modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Shop') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalUpdate" />
        </x-modal.header>
        <x-modal.body>
            <form method="POST" action="{{ route('shop.update') }}" id="form-update" enctype="multipart/form-data">
            </form>
        </x-modal.body>
    </x-modal.modal-small>

    @push('bottom_js')
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>

        <script>
            $(document).ready(function() {
                dataTables("{{ route('shops.data') }}?date=" + $(this).val());

                var datatable;
                $('#inputDate').change(function() {
                    datatable.destroy();
                    dataTables("{{ route('shops.data') }}?date=" + $(this).val());
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
                            [0, 'asc']
                        ],
                        "bDeferRender": true,
                        ajax: url,
                        columns: [
                            {
                                name: 'id',
                                data: 'id',
                                render: function (data, type, row, meta) {
                                    return meta.row + meta.settings._iDisplayStart + 1
                                }
                            },
                            {
                                name: 'image',
                                data: 'image'
                            },
                            {
                                name: 'str_name_code',
                                data: 'str_name_code',
                                searchable: false,
                                orderable: true
                            },
                            {
                                name: 'phone',
                                data: 'phone'
                            },
                            {
                                name: 'address_detail',
                                data: 'address_detail'
                            },
                            {
                                name: 'manage',
                                data: 'manage'
                            }
                        ],

                    });
                }

                $(document).on('click', '#BtnInsert', function(event) {
                    event.preventDefault();
                    $('#__modalInsert').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('shop.create') }}',
                        beforeSend: function() {
                            $('#form-insert').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-insert').html(result);
                    });
                });

                $(document).on('click', '#closeModalInsert', function() {
                    $('#__modalInsert').addClass('modal-hide');
                });

                $(document).on('click', '#BtnUpdate', function(event) {
                    event.preventDefault();
                    $('#__modalUpdate').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('shops.data') }}?id=' + $(this).data('id'),
                        beforeSend: function() {
                            $('#form-update').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-update').html(result);
                    });
                });

                $(document).on('click', '#closeModalUpdate', function() {
                    $('#__modalUpdate').addClass('modal-hide');
                });

                $(document).on('click', '#BtnDelete', function() {
                    let drop = confirm('Are you sure you want to delete this shop?');

                    if (drop) {
                        $.ajax({
                            url: '{{ route("shop.delete") }}',
                            type: 'post',
                            data: {
                                'id': $(this).data('id'),
                                '_token': $('meta[name=csrf-token]').attr('content')
                            },
                            beforeSend: function() {
                            }
                        }).done(function(result) {
                            if (result.status === 1) {
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
