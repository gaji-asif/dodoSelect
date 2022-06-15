<x-app-layout>
    @section('title', 'Manage Shipper')

    @if (session('assignedPermissions') == 'all' || in_array('Can access menu: Manage Shipper', session('assignedPermissions')))
    <div class="col-span-12">
        <x-card.card-default>
            <x-card.body>
                @include('settings.menu')
            </x-card.body>
        </x-card.card-default>

        <x-card.card-default>
            <x-card.header>
                <x-card.title>
                    {{ __('translation.Manage Shipper') }}
                </x-card.title>
            </x-card.header>
            <x-card.body>
                <div class="mb-6">
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

                    <x-button color="green" id="BtnInsert">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="ml-2">Add</span>
                    </x-button>
                </div>

                <div class="w-full overflow-x-auto">
                    <table class="w-full" id="datatable">
                        <thead>
                            <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                                <th class="px-4 py-2">
                                    {{ __('translation.ID') }}
                                </th>
                                <th class="px-4 py-2">
                                    {{ __('translation.Name') }}
                                </th>
                                <th class="px-4 py-2">
                                    {{ __('translation.Total Packages') }}
                                </th>
                                <th width="20%" class="px-4 py-2">
                                    {{ __('translation.Action') }}
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </x-card.body>
        </x-card.card-default>
    </div>
    @endif

    <!-- insert modal -->
    <x-modal.modal-small class="modal-hide modal-insert">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Add Shipper') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalInsert" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form method="POST" action="{{ route('insert shipper') }}" id="form-insert">
                    @csrf
                    <div>
                        <x-label>
                            Name
                        </x-label>
                        <x-input type="text" name="name" id="name" :value="old('name')" required>
                        </x-input>
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
                {{ __('translation.Update Shipper') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalUpdate" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form method="POST" action="{{ route('update shipper') }}" id="form-update"></form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>


    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script>
            $(document).ready(function() {

                dataTables("{{ route('data shipper') }}");

                var datatable;

                function dataTables(url) {
                    // Datatable
                    datatable = $('#datatable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: url,
                        columns: [
                            {
                                name: 'id',
                                data: 'id'
                            },
                            {
                                name: 'name',
                                data: 'name'
                            },
                            {
                                name: 'totalPackage',
                                data: 'totalPackage'
                            },
                            {
                                name: 'manage',
                                data: 'manage'
                            }
                        ],
                        columnDefs: [
                            {
                                targets: [3],
                                orderable: false
                            }
                        ],
                        paginationType: 'numbers'
                    });
                }

                $('#BtnInsert').click(function() {
                    $('.modal-insert').removeClass('modal-hide');
                });

                $(document).on('click', '#BtnUpdate', function() {
                    $('.modal-update').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route("data shipper") }}?id=' + $(this).data('id'),
                        beforeSend: function() {
                            $('#form-update').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-update').html(result);
                    });
                });

                $('#closeModalUpdate').click(function() {
                    $('.modal-update').addClass('modal-hide');
                });

                $('#closeModalInsert').click(function() {
                    $('.modal-insert').addClass('modal-hide');
                });

                $(document).on('click', '#BtnDelete', function() {
                    let drop = confirm('Are you sure?');

                    if (drop) {
                        $.ajax({
                            url: '{{ route("delete shipper") }}',
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
