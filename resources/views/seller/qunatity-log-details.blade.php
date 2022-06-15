<x-app-layout>
    @section('title', 'Check-in/Check-Out')

    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">
    @endpush

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Product'))
        <x-card title="{{ $product->product_name }} - Quantity Log ({{ number_format($quantityLogCount) }})">
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

            <x-alert-success class="alert hidden" id="__alertSuccessTable">
                <div id="__contentAlertSuccessTable"></div>
            </x-alert-success>
        </div>

        <div class="flex flex-row justify-end mb-5">
            <x-button type="button" color="red" id="__btnBulkDelete">
                {{ __('translation.Bulk Delete') }}
            </x-button>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full" id="__quantityLogTable">
                <thead class="bg-blue-500">
                    <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                        <th class="px-4 py-2"></th>
                        <th class="px-4 py-2">
                            {{ __('translation.ID') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.Type') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.Quantity') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.Datetime') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.User') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @if (isset($quantityLogs))
                        @foreach ($quantityLogs as $row)
                            <tr>
                                <td>{{$row->id}}</td>
                                <td>
                                    @if($row->check_in_out == 1)
                                        <span class="rounded-full px-4 mr-2 bg-green-300 text-green-800 p-1 text-xs leading-none">
                                            {{ $row->str_in_out }}
                                        </span>
                                    @else
                                        <span class="rounded-full px-4 mr-2 bg-red-300 text-red-800 p-1 text-xs leading-none">
                                            {{ $row->str_in_out }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{$row->quantity}}</td>
                                <td>{{ date('Y-m-d H:i A',strtotime($row->date))}}</td>
                                @if (empty($row->staff))
                                    <td>{{ $row->seller->name }}</td>
                                @else
                                    <td>{{ $row->staff->name }}</td>
                                @endif
                                @if (Auth::user()->role == "member")
                                    <td>
                                        <span x-on:click=" showEditModal=true"class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer" data-id="{{ $row->id}}" id="BtnUpdate"><i class="fas fa-pencil-alt"></i></span>
                                        <span class="bg-red-500 text-white rounded px-2 py-1 mr-4 capitalize cursor-pointer" data-id="{{$row->id }}" id="BtnDelete"><i class="fas fa-trash-alt"></i></span>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @endif --}}
                </tbody>
            </table>
        </div>

    </x-card>
    @endif

    <x-modal.modal-small class="modal-hide modal-update">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Quantity') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalUpdate" />
        </x-modal.header>
        <x-modal.body>
            <form method="POST" action="{{ route('update quantity log') }}" id="form-update"></form>
        </x-modal.body>
    </x-modal.modal-small>


    <x-modal.alert class="hidden" id="__modalConfirmBulkDelete">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Confirm') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger class="alert hidden" id="__alertDangerDeleteModal">
                <div id="__contentAlertDangerDeleteModal"></div>
            </x-alert-danger>

            <div class="mb-4">
                <p class="text-center">
                    {{ __('translation.Are you sure to delete the selected data?') }}
                </p>
            </div>
            <div class="text-center pb-6">
                <x-button type="button" color="gray" id="__btnNoConfirmBulkDelete">
                    {{ __('translation.No, Close') }}
                </x-button>
                <x-button type="button" color="red" id="__btnYesConfirmBulkDelete">
                    {{ __('translation.Yes Delete') }}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.alert>


    <x-modal.alert class="hidden" id="__modalNoBulkDelete">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Warning') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <div class="mb-4">
                <p class="text-center">
                    {{ __('translation.Please select at least one row.') }}
                </p>
            </div>
            <div class="text-center pb-6">
                <x-button type="button" color="green" id="__closeModalNoBulkDelete">
                    {{ __('translation.Ok, Understand') }}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.alert>


    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>

        <script>
            const productId = {{ $product->id }};
            const dataTableUrl = '{{ route('seller-quantity-details-datatable') }}';
            const deleteBulkUrl = '{{ route('delete quantity log bulk') }}';

            var selectedRows = [];


            var quantityLogTable = $('#__quantityLogTable').DataTable({
                                        serverSide: true,
                                        processing: true,
                                        ajax: {
                                            type: 'GET',
                                            url: dataTableUrl,
                                            data: {
                                                productId: productId
                                            }
                                        },
                                        columnDefs: [
                                            {
                                                targets: [0],
                                                orderable: false,
                                                checkboxes: {
                                                    selectRow: true
                                                }
                                            },
                                            {
                                                targets: [6],
                                                orderable: false
                                            }
                                        ],
                                        order: [
                                            [ 4, 'desc' ]
                                        ],
                                        paginationType: 'numbers',
                                        select: {
                                            style: 'multiple'
                                        }
                                    });


            const editHistory = (el) => {
                let id = el.getAttribute('data-id');

                $('.modal-update').removeClass('modal-hide');
                $('body').addClass('modal-open');

                $.ajax({
                    url: '{{ route('date quantity log') }}?id=' + id,
                    beforeSend: function() {
                        $('#form-update').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-update').html(result);
                });
            }

            const deleteHistory = (el) => {
                let id = el.getAttribute('data-id');
                let drop = confirm('Are you sure?');

                if (drop) {
                    $.ajax({
                        url: '{{ route('delete quantity log') }}',
                        type: 'post',
                        data: {
                            'id': id,
                            '_token': $('meta[name=csrf-token]').attr('content')
                        }
                    }).done(function(result) {
                        if (result.status === 1) {
                            alert('Data deleted successfully');
                            location.reload();

                        } else {
                            alert(result.message);

                        }
                    });
                }
            }

            $(document).ready(function() {
                $(document).on('click', '#closeModalUpdate', function() {
                    $('.modal-update').addClass('modal-hide');
                    $('body').removeClass('modal-open');
                });
            });


            $('#__btnBulkDelete').click(function() {
                selectedRows = quantityLogTable.column(0).checkboxes.selected();

                if (selectedRows.length == 0) {
                    $('#__modalNoBulkDelete').removeClass('hidden');
                    $('body').addClass('modal-open');
                } else {
                    $('#__modalConfirmBulkDelete').removeClass('hidden');
                    $('body').addClass('modal-open');
                }
            });


            $('#__closeModalNoBulkDelete').click(function() {
                $('#__modalNoBulkDelete').addClass('hidden');
                $('body').removeClass('modal-open');
            });


            $('#__btnNoConfirmBulkDelete').click(function() {
                $('#__modalConfirmBulkDelete').addClass('hidden');
                $('body').removeClass('modal-open');
                $('.alert').addClass('hidden');
            });


            $('#__btnYesConfirmBulkDelete').click(function() {
                var bulkDeleteIds = [];

                let formData = new FormData();
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                $.each(selectedRows, function(index, rowId) {
                    formData.append('id[]', rowId);
                });

                $.ajax({
                    type: 'POST',
                    url: deleteBulkUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#__btnNoConfirmBulkDelete').attr('disabled', true);
                        $('#__btnYesConfirmBulkDelete').attr('disabled', true).html('{{ __('translation.Deleting') }}');
                        $('.alert').addClass('hidden');
                    },
                    success: function(responseJson) {
                        $('#__btnNoConfirmBulkDelete').attr('disabled', false);
                        $('#__btnYesConfirmBulkDelete').attr('disabled', false).html('{{ __('translation.Yes, Delete') }}');

                        $('html, body').animate({
                            scrollTop: 0
                        }, 500);

                        quantityLogTable.ajax.reload(null);

                        $('#__contentAlertSuccessTable').html(null);
                        $('#__contentAlertSuccessTable').html(responseJson.message);
                        $('#__alertSuccessTable').removeClass('hidden');

                        $('#__modalConfirmBulkDelete').addClass('hidden');
                        $('body').removeClass('modal-open');
                    },
                    error: function(error) {
                        let responseJson = error.responseJSON;

                        $('#__btnNoConfirmBulkDelete').attr('disabled', false);
                        $('#__btnYesConfirmBulkDelete').attr('disabled', false).html('{{ __('translation.Yes, Delete') }}');

                        $('#__contentAlertDangerDeleteModal').html(null);

                        if (error.status == 422) {
                            let errorFields = Object.keys(responseJson.errors);
                            errorFields.map(field => {
                                $('#__content_alertDanger').append(
                                    $('<p/>', {
                                        html: responseJson.errors[field][0]
                                    })
                                );
                            });

                        } else {
                            $('#__contentAlertDangerDeleteModal').html(responseJson.message);
                        }

                        $('#__alertDangerDeleteModal').removeClass('hidden');
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
