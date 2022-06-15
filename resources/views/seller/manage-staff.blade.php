<x-app-layout>
    @section('title', 'Manage Staff')

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Manage Users - Users'))
        <x-card class="mt-0">
            <div class="" style="margin-top: -2rem">
                @include('seller.staff_roles.menu')
            </div>
            <hr>

            <card class="bg-gray-500 ">
                <div class="card-title my-4">
                    <h4><strong>Manage Staff</strong></h4>
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
                        <span class="ml-2">{{ __('translation.Add Staff') }}</span>
                    </x-button>
                </div>

                <div class="flex justify-between flex-col">
                    <div class="overflow-x-auto">
                        <table class="w-full" id="datatable">
                            <thead>
                            <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                                <th class="px-4 py-2 border-2">Registered at</th>
                                <th class="px-4 py-2 border-2">Full Name</th>
                                <th class="px-4 py-2 border-2">Phone</th>
                                <th class="px-4 py-2 border-2">Email</th>
                                <th class="px-4 py-2 border-2">Address</th>
                                <th class="px-4 py-2 border-2">Role</th>
                                <th class="px-4 py-2 border-2">Manage</th>
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
                {{ __('translation.Add Staff') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalInsert" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form method="POST" action="{{ route('staff.insert') }}" id="form-insert">
                    @csrf
                    <div >
                        <x-label>
                            Full Name
                        </x-label>
                        <x-input type="text" name="name" id="name" :value="old('name')" required>
                        </x-input>
                    </div>
                    <div class="mt-6">
                        <x-label>
                            Phone
                        </x-label>
                        <x-input type="text" name="phone" id="phone" :value="old('phone')" required>
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
                        <x-input type="password" name="password" id="password" :value="old('password')" required>
                        </x-input>
                    </div>
                    <div class="mt-6">
                        <x-label>
                            Role
                        </x-label>
                        <x-select name="role" id="role" required>
                            <option disabled selected value="0">
                                {{ __('translation.Select Role') }}
                            </option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"> {{ $role->name }} </option>
                            @endforeach
                        </x-select>
                    </div>
                    <div class="mt-6">
                        <x-label>
                            Address
                        </x-label>
                        <x-textarea name="address" id="address">{{old('address')}}</x-textarea>
                    </div>
                    <div class="flex justify-end mt-4">
                        <x-button color="blue">Save</x-button>
                    </div>
                </form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

    {{-- assign shop modal --}}
    <x-modal.modal-small class="modal-hide modal-assign-shop">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Assign Shop') }}
            </x-modal.title>
            <x-modal.close-button id="closeAssignShopModal" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form method="POST" action="{{ route('staff.update') }}"
                      id="form-assign-shop"></form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

    {{-- update modal --}}
    <x-modal.modal-small class="modal-hide modal-update">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Staff') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalUpdate" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form method="POST" action="{{ route('staff.update') }}"
                      id="form-update"></form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script>
            $(document).ready(function() {
                dataTables("{{ route('staff.data') }}?date=" + $(this).val());
                var datatable;
                $('#inputDate').change(function() {
                    datatable.destroy();
                    // console.log($(this).val());
                    dataTables("{{ route('staff.data') }}?date=" + $(this).val());
                });

                function dataTables(url) {
                    // Datatable
                    datatable = $('#datatable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: url,
                        columns: [{
                            name: 'Registered at',
                            data: 'created_at'
                        },
                            {
                                name: 'name',
                                data: 'name'
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
                                name: 'address',
                                data: 'address'
                            },
                            {
                                name: 'staff_role',
                                data: 'staff_role'
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

                $(document).on('click', '#BtnShopUpdate', function() {
                    $('.modal-assign-shop').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('staff.data') }}?assign_shop=true&id='+$(this).data('id'),
                        beforeSend: function() {
                            $('#form-assign-shop').html('Loading...');
                        }
                    }).done(function(result) {
                        $('#form-assign-shop').html(result);
                    });
                });

                $(document).on('click', '#closeAssignShopModal', function() {
                    $('.modal-assign-shop').addClass('modal-hide');
                });

                $(document).on('click', '#BtnUpdate', function() {
                    $('.modal-update').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('staff.data') }}?id=' + $(this).data('id'),
                        beforeSend: function() {
                            $('#form-update').html('Loading...');
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
                            url: '{{ route('staff.delete') }}',
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
