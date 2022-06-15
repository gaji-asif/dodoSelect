<x-app-layout>
    @section('title', 'Customers')

    @if (in_array('Can access menu: CRM - Customers', session('assignedPermissions')))
        <x-card title="Customers">
            <div class="mt-6">
                @if(session('success'))
                    <x-alert-success>{{ session('success') }}</x-alert-success>
                @endif
                @if(session('danger'))
                    <x-alert-danger>{{ session('danger') }}</x-alert-danger>
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
                    {{ __('translation.Add Customer') }}
                </span>
                </x-button>
            </div>

            <div class="flex justify-between flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full" id="datatable">
                        <thead>
                        <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                            <th>{{ __('translation.ID') }}</th>
                            <th>{{ __('translation.Customer Name') }}</th>
                            <th>{{ __('translation.Contact Phone') }}</th>
                            <th>{{ __('translation.Total Orders') }}</th>
                            <th>{{ __('translation.Last Date of Order') }}</th>
                            <th>{{ __('translation.Action') }}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </x-card>
    @endif

<!-- insert modal -->
    <x-modal.modal-small class="modal-hide modal-insert">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Add Customer') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalInsert" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form method="POST" action="{{ route('customer.store') }}" id="form-insert">
                    @csrf
                    <div>
                        <div class="form-group font-weight-bold">
                            <label for="customer_name">Customer Name</label>
                            <input type="text" class="form-control" name="customer_name" id="customer_name" value="{{ old('customer_name') }}" required autocomplete="off">
                        </div>
                        <div class="form-group font-weight-bold">
                            <label for="contact_phone">Contact Phone</label>
                            <input type="number" class="form-control" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}" required autocomplete="off">
                        </div>
                    </div>

                    <div class="flex justify-end py-6">
                        <x-button type="reset" color="gray" class="mr-1" id="cancelModalInsert">
                            {{ __('translation.Cancel') }}
                        </x-button>
                        <x-button type="submit" color="blue">
                            {{ __('translation.Submit') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>

    {{-- update modal --}}
    <x-modal.modal-small class="modal-hide modal-update">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Customer') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalUpdate" />
        </x-modal.header>
        <x-modal.body>
            <div class="pb-6">
                <form style="max-height:90vh" method="POST" action="{{ route('customer.update') }}" id="form-update"></form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>


    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script>
            $(document).ready(function() {
                dataTables("{{ route('customer.data') }}?date=" + $(this).val());

                var datatable;
                $('#inputDate').change(function() {
                    datatable.destroy();
                    dataTables("{{ route('customer.data') }}?date=" + $(this).val());
                });

                function dataTables(url) {
                    // Datatable
                    datatable = $('#datatable').DataTable({
                        processing: true,
                        serverSide: true,
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
                                name: 'customer_name',
                                data: 'customer_name'
                            },
                            {
                                name: 'contact_phone',
                                data: 'contact_phone'
                            },
                            {
                                name: 'total_orders',
                                data: 'total_orders'
                            },
                            {
                                name: 'last_order',
                                data: 'last_order'
                            },
                            {
                                name: 'action',
                                data: 'action'
                            }
                        ],
                        columnDefs: [
                            {
                                targets: 0,
                                searchable: false,
                                orderable: false
                            }
                        ]
                    });
                }

                $('#BtnInsert').click(function() {
                    $('.modal-insert').removeClass('modal-hide');
                });

                $(document).on('click', '#closeModalUpdate', function() {
                    $('.modal-update').addClass('modal-hide');
                });

                $(document).on('click', '#BtnUpdate', function() {
                    $('.modal-update').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('customer.data') }}?id=' + $(this).data('id'),
                        beforeSend: function() {
                            $('#form-update').html('Loading');
                        }
                    }).done(function(result) {
                        $('#form-update').html(result);
                    });
                });

                $('#closeModalInsert').click(function() {
                    $('.modal-insert').addClass('modal-hide');
                });

                $('#cancelModalInsert').click(function() {
                    $('.modal-insert').addClass('modal-hide');
                });

                $(document).on('click', '#BtnDelete', function() {
                    let drop = confirm('Are you sure?');
                    if (drop) {
                        $.ajax({
                            url: '{{ route('customer.delete') }}',
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
