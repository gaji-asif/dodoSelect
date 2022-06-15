<x-app-layout>
    @section('title', 'Dropshipper Role Management')

    @if (in_array('Can access menu: Dropshippers - Role Management', session('assignedPermissions')))
        <x-card class="mt-0">
            <div class="" style="margin-top: -2rem">
                @include('dropshipper.menu')
            </div>
            <hr>

            <card class="bg-gray-500 ">
                <div class="card-title my-4">
                    <h4><strong>Dropshipper Role Management</strong></h4>
                </div>
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="ml-2">
                    {{ __('translation.Add Role') }}
                </span>
                    </x-button>
                </div>

                <div class="flex justify-between flex-col">
                    <div class="overflow-x-auto">
                        <table class="w-full" id="datatable">
                            <thead>
                            <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                                <th class="px-4 py-2 border-2">Role Name</th>
                                <th class="px-4 py-2 border-2">Description</th>
                                <th class="px-4 py-2 border-2">Actions</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </card>
        </x-card>
    @endif

<!-- insert modal -->
    <x-modal.modal-small class="modal-hide modal-insert">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Add Role') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalInsert" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form method="POST" action="{{ route('role.insert') }}" id="form-insert">
                    @csrf
                    <x-input type="text" hidden name="user_type" :value=1></x-input>
                    <div >
                        <x-label>Role Name</x-label>
                        <x-input type="text" name="name" id="name" :value="old('name')" required autocomplete="off"></x-input>
                    </div>
                    <div class="mt-6">
                        <x-label>Description</x-label>
                        <x-textarea name="description" id="description">{{old('description')}}</x-textarea>
                    </div>
                    <div class="flex justify-end mt-4">
                        <x-button color="blue">Save</x-button>
                    </div>
                </form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

    {{-- update modal --}}
    <x-modal.modal-small class="modal-hide modal-update">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Role') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalUpdate" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form style="max-height:90vh" method="POST" action="{{ route('role.update') }}" id="form-update"></form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script>
            $(document).ready(function() {
                dataTables("{{ route('dropshipper.role_data') }}?date=" + $(this).val());

                var datatable;
                $('#inputDate').change(function() {
                    datatable.destroy();
                    dataTables("{{ route('dropshipper.role_data') }}?date=" + $(this).val());
                });

                function dataTables(url) {
                    // Datatable
                    datatable = $('#datatable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: url,
                        columns: [
                            {
                                name: 'name',
                                data: 'name'
                            },
                            {
                                name: 'description',
                                data: 'description'
                            },
                            {
                                name: 'action',
                                data: 'action'
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
                        url: '{{ route('dropshipper.role_data') }}?id=' + $(this).data('id'),
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

                $('#closeModalInsert').click(function() {
                    $('.modal-insert').addClass('modal-hide');
                });

                $(document).on('click', '#BtnDelete', function() {
                    let drop = confirm('Are you sure?');
                    if (drop) {
                        $.ajax({
                            url: '{{ route('role.delete') }}',
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

                $(document).on('click', '#BtnAssign', function() {
                    var role_id = $(this).attr('data-id');
                    var url = "{{ url('roles/assign/') }}";
                    window.location.href = url+'/'+role_id;
                });
            });

        </script>
    @endpush

</x-app-layout>
