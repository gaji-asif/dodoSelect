<x-app-layout>
    @section('title', 'Dropshippers')

    @push('top_css')
        <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
        <link rel="stylesheet" href="{{ asset('css/typeaheadjs.css') }}">
    @endpush

    @if (in_array('Can access menu: Dropshippers - Manage Dropshippers', session('assignedPermissions')))
        <x-card class="mt-0">
            <div class="" style="margin-top: -2rem">
                @include('dropshipper.menu')
            </div>
            <hr>

            <card class="bg-gray-500 ">
                <div class="card-title my-4">
                    <h4><strong>Manage Dropshipper</strong></h4>
                </div>
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

                    <x-button class="mb-6" color="green" id="BtnInsert">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="ml-2">
                    {{ __('translation.Add Dropshipper') }}
                </span>
                    </x-button>
                </div>

                <div class="flex justify-between flex-col">
                    <div class="overflow-x-auto">
                        <table class="w-full" id="datatable">
                            <thead>
                            <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                                <th>Logo</th>
                                <th>Shop Name</th>
                                <th>Contact Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Role</th>
                                <th>Registered at</th>
                                <th>Manage</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </card>
        </x-card>
    @endif

<!-- insert modal -->
    <x-modal.modal-large class="modal-hide modal-insert">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Add Dropshipper') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalInsert" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form method="POST" action="{{ route('dropshipper.store') }}" id="form-insert" enctype="multipart/form-data"></form>
            </div>
        </x-modal.body>
    </x-modal.modal-large>

    {{-- update modal --}}
    <x-modal.modal-large class="modal-hide modal-update">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Dropshipper') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalUpdate" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form method="POST" action="{{ route('dropshipper.update') }}" id="form-update" enctype="multipart/form-data"></form>
            </div>
        </x-modal.body>
    </x-modal.modal-large>

    {{-- update password --}}
    <x-modal.modal-small class="modal-hide modal-password">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Change Password') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalPassword" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form style="max-height:90vh" method="POST" action="{{ route('staff.change_password') }}" id="form-password"></form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>


    @push('bottom_js')
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>

        <script>
            $(document).ready(function() {
                dataTables("{{ route('dropshipper.data') }}?date=" + $(this).val());
                var datatable;
                $('#inputDate').change(function() {
                    datatable.destroy();
                    dataTables("{{ route('dropshipper.data') }}?date=" + $(this).val());
                });

                function dataTables(url) {
                    datatable = $('#datatable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: url,
                        columns: [
                            {
                                name: 'logo',
                                data: 'logo'
                            },
                            {
                                name: 'name',
                                data: 'name'
                            },
                            {
                                name: 'contactname',
                                data: 'contactname'
                            },
                            {
                                name: 'phone',
                                data: 'phone'
                            },
                            {
                                name: 'email',
                                data: 'email'
                            },
                            {
                                name: 'address_detail',
                                data: 'address_detail'
                            },
                            {
                                name: 'dropshipper_role',
                                data: 'dropshipper_role'
                            },
                            {
                                name: 'Registered at',
                                data: 'created_at'
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
                    $.ajax({
                        url: '{{ route('dropshipper.create') }}',
                        beforeSend: function() {
                            $('#form-insert').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-insert').html(result);
                    });
                });

                $('#closeModalInsert').click(function() {
                    $('.modal-insert').addClass('modal-hide');
                });

                $(document).on('click', '#BtnUpdate', function() {
                    $('.modal-update').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('dropshipper.data') }}?id=' + $(this).data('id'),
                        beforeSend: function() {
                            $('#form-update').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-update').html(result);
                    });
                });

                $(document).on('click', '#closeModalUpdate', function() {
                    $('.modal-update').addClass('modal-hide');
                });


                $(document).on('click', '#BtnPasswordChange', function() {
                    $('.modal-password').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('staff.change_password_modal') }}?id=' + $(this).data('id'),
                        beforeSend: function() {
                            $('#form-password').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-password').html(result);
                    });
                });

                $(document).on('click', '#BtnDelete', function() {
                    let drop = confirm('Are you sure?');
                    if (drop) {
                        $.ajax({
                            url: '{{ route('dropshipper.delete') }}',
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
    @endpush

</x-app-layout>
