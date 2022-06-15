<x-app-layout>
    @section('title', 'Supplier')

    @push('top_css')
        <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    @endpush

    @if(session('roleName') != 'dropshipper')
        <x-card class="mt-0">
        <div class="" style="margin-top: -2rem">
            @include('settings.menu')
        </div>
        <hr>

        <card>
            <div class="card-title my-4">
                <h4><strong>List Of Suppliers @if (isset($data)) ({{count($data)}}) @endif</strong></h4>
            </div>
            <p id="menu-title" hidden>Supplier</p>
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
                    <x-button color="green" id="BtnInsert" data-toggle="modal" data-target="#createModal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="ml-2">
                            {{ __('translation.Create Supplier') }}
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
                            <th>Name</th>
                            <th>Contact Channel</th>
                            <th>Address</th>
                            <th>Note</th>
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
    <x-modal.modal-large id="__modalInsert" class="modal-update modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Add Supplier') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalInsert" />
        </x-modal.header>
        <x-modal.body>
            <form method="POST" action="{{ route('store supplier') }}" id="form-insert" enctype="multipart/form-data">
                @csrf
                <div>
                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Supplier Name') }} <x-form.required-mark />
                        </x-label>
                        <x-input type="text" name="supplier_name" value="{{ old('supplier_name')}}" required/>
                    </div>

                    <div class="flex flex-col md:flex-row md:gap-x-5">
                        <div class="mb-5 md:w-2/5">
                            <x-label>
                                {{ __('translation.Contact Channel') }}
                            </x-label>
                            <x-input type="text" name="contact_channel[]" id="contact_channel"/>
                        </div>
                        <div class="mb-5 md:w-2/5">
                            <x-label>
                                {{ __('translation.Contact') }}
                            </x-label>
                            <x-input type="text" name="contact[]" id="contact"/>
                        </div>
                        <div class="mb-8 md:mb-5 md:pt-7 md:w-1/5">
                            <x-button type="button" color="green" class="w-full" id="__btnAddNewContact">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                <span class="ml-2">
                                   {{ __('translation.Add') }}
                               </span>
                            </x-button>
                        </div>
                    </div>

                    <div id="__wrapperAdditionalContact"></div>

                    <div class="hide" id="__newContactTemplate">
                        <div class="additional-contact flex flex-col md:flex-row md:gap-x-5">
                            <div class="mb-5 md:w-2/5">
                                <x-label>
                                    {{ __('translation.Contact Channel') }}
                                </x-label>
                                <x-input type="text" name="contact_channel[]"/>
                            </div>
                            <div class="mb-5 md:w-2/5">
                                <x-label>
                                    {{ __('translation.Contact') }}
                                </x-label>
                                <x-input type="text" name="contact[]"/>
                            </div>
                            <div class="mb-8 md:mb-5 md:pt-7 md:w-1/5">
                                <x-button type="button" color="red" class="w-full __btnRemoveContact">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span class="ml-2">
                                       {{ __('translation.Remove') }}
                                   </span>
                                </x-button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Address') }}
                        </x-label>
                        <x-form.textarea name="address" rows="3">{{ old('address') }}</x-form.textarea>
                    </div>

                    <div class="mb-5">
                        <x-label>
                            {{ __('translation.Note') }}
                        </x-label>
                        <x-form.textarea name="note" rows="3">{{ old('note') }}</x-form.textarea>
                    </div>
                </div>
                <div class="flex justify-end py-6">
                    <x-button type="reset" color="gray" class="mr-1" id="__btnCancelModalInsert">
                        {{ __('translation.Cancel') }}
                    </x-button>
                    <x-button type="submit" color="blue">
                        {{ __('translation.Save') }}
                    </x-button>
                </div>
            </form>
        </x-modal.body>
    </x-modal.modal-large>


    {{-- update modal --}}
    <x-modal.modal-large id="__modalUpdate" class="modal-update modal-hide">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Supplier') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalUpdate" />
        </x-modal.header>
        <x-modal.body>
            <form method="POST" action="{{ route('update supplier') }}" id="form-update" enctype="multipart/form-data">
            </form>
        </x-modal.body>
    </x-modal.modal-large>


    @push('bottom_js')
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>

        <script>
            $(document).ready(function() {
                dataTables("{{ route('data suppliers') }}?date=" + $(this).val());

                var datatable;
                $('#inputDate').change(function() {
                    datatable.destroy();
                    dataTables("{{ route('data suppliers') }}?date=" + $(this).val());
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
                        bDeferRender: true,
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
                                name: 'supplier_name',
                                data: 'supplier_name'
                            },
                            {
                                name: 'supplier_contact',
                                data: 'supplier_contact'
                            },
                            {
                                name: 'address',
                                data: 'address'
                            },
                            {
                                name: 'note',
                                data: 'note'
                            },
                            {
                                name: 'manage',
                                data: 'manage'
                            }
                        ],

                    });
                }


                $('#BtnInsert').click(function() {
                    $('body').addClass('modal-open');
                    $('#__modalInsert').removeClass('modal-hide');
                });

                $('#closeModalInsert').click(function() {
                    $('body').removeClass('modal-open');
                    $('#__modalInsert').addClass('modal-hide');
                });

                $('#__btnCancelModalInsert').click(function() {
                    $('body').removeClass('modal-open');
                    $('#__modalInsert').addClass('modal-hide');
                });


                $(document).on('click', '#BtnUpdate', function() {
                    $('#__modalUpdate').removeClass('modal-hide');
                    $.ajax({
                        url: '{{ route('data suppliers') }}?id=' + $(this).data('id'),
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
                    let drop = confirm('Are you sure you want to delete this supplier?');

                    if (drop) {
                        $.ajax({
                            url: '{{ route("delete supplier") }}',
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

        <script>
            $('#__btnAddNewContact').click(function() {
                let newContactTemplate = $('#__newContactTemplate').html();
                $('#__wrapperAdditionalContact').append(newContactTemplate);

                initialRemoveContactButton();
            });

            const initialRemoveContactButton = () => {
                $('.__btnRemoveContact').click(function() {
                    $(this).parents(".additional-contact").remove();
                });
            }
        </script>
    @endpush
</x-app-layout>
