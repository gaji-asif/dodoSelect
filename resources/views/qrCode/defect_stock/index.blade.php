<x-app-layout>
    @section('title', 'Defect Stock')

    @push('top_css')
        <link href="https://cdn.jsdelivr.net/npm/dropzone@5.9.2/dist/min/dropzone.min.css" rel="stylesheet">
    @endpush

    <style>
        label{
            margin-top: .5rem;
            margin-bottom: .1rem;
        }
        .BtnResult{
            cursor: pointer;
            text-decoration: underline;
        }
    </style>

    @if (in_array('Can access menu: Stock Adjust - Defect Stock', session('assignedPermissions')))
    <x-card title="Defect Stock">
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

            <div class="flex flex-col sm:flex-row sm:justify-between items-start mb-4">
                <div class="w-full mb-6 sm:mb-0">
                    <div class="flex flex-col sm:flex-row">
                        <div class="sm:w-1/3 lg:w-1/4 mb-3 sm:mb-0 sm:mr-3 xl:mr-0">
                            <x-button-link href="{{ route('defect_stock.create') }}" color="green" class="mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="ml-2">Add Defect Product</span>
                            </x-button-link>
                        </div>
                        <div class="sm:w-1/3 lg:w-1/4">
                            <x-select id="__sortByToolbar" class="xl:relative xl:-left-5">
                                <option disabled selected>
                                    - {{ __('translation.Sort by') }} -
                                </option>
                                <option value="status_asc">
                                    {{ __('translation.Status') . ' (' . __('translation.Close -> Open') . ')' }}
                                </option>
                                <option value="status_desc">
                                    {{ __('translation.Status') . ' (' . __('translation.Open -> Close') . ')' }}
                                </option>
                            </x-select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full" id="__defectStockTable">
                <thead>
                    <tr class="bg-blue-500">
                        <th class="px-4 py-2 text-center text-white">
                            {{ __('translation.Product Detail') }}
                        </th>
                        <th class="px-4 py-2 text-center text-white">
                            {{ __('translation.Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </x-card>
    @endif

    {{--image modal--}}
    <x-modal.modal-small class="modal-hide modal_image">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Defect Product Images') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalImage" />
        </x-modal.header>
        <x-modal.body>
            <div id="show-image"></div>
        </x-modal.body>
    </x-modal.modal-small>

    {{--result modal--}}
    <x-modal.modal-small class="modal-hide modal_result">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Defect Product Result') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalResult" />
        </x-modal.header>
        <x-modal.body>
            <div id="show-result"></div>
        </x-modal.body>
    </x-modal.modal-small>

    {{-- update modal --}}
    <x-modal.modal-small class="modal-hide modal_update">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Defect Product') }}
            </x-modal.title>
            <x-modal.close-button id="closeModalUpdate" />
        </x-modal.header>
        <x-modal.body>
            <form method="POST" action="{{ route('defect_stock.update') }}" id="form-update" enctype="multipart/form-data"></form>
        </x-modal.body>
    </x-modal.modal-small>


    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.2/dist/min/dropzone.min.js"></script>

        <script>
            const defectStockDatatableUrl = '{{ route('defect_stock.data') }}';
            const defectStockDetailUrl = '{{ route('defect_stock.show') }}';
            const defectStockResultUrl = '{{ route('defect_stock.show_result') }}';
            const defectStockDeleteUrl = '{{ route('defect_stock.delete') }}';


            const defectStockTable = $('#__defectStockTable').DataTable({
                serverSide: true,
                processing: true,
                select: {
                    style: 'multi'
                },
                order: [
                    [0, 'asc']
                ],
                ajax: {
                    type: 'GET',
                    url: defectStockDatatableUrl
                },
                columns: [
                    {
                        name: 'details',
                        data: 'details',
                        orderable: false
                    },
                    {
                        name: 'actions',
                        data: 'actions',
                        orderable: false
                    }
                ]
            });


            const sortDefectStockTable = sortBy => {
                switch (sortBy) {
                    case 'status_asc':
                        defectStockTable.order([1, 'asc']).draw();
                        break;

                    case 'status_desc':
                        defectStockTable.order([1, 'desc']).draw();
                        break;

                    default:
                        defectStockTable.order([0, 'asc']).draw();
                        break;
                }
            }


            $('#__sortByToolbar').on('change', function() {
                let selectedSortBy = $(this).val();
                sortDefectStockTable(selectedSortBy);
            });


            $('body').on('click', '#BtnImage', function() {
                let defectStockid =  $(this).data('id');

                $.ajax({
                    url: `${defectStockDetailUrl}?id=${defectStockid}`,
                    beforeSend: function() {
                        $('#show-image').html('Loading');
                    }
                }).done(function(result) {
                    $('.modal_image').removeClass('modal-hide');
                    $('#show-image').html(result);
                });
            });


            $('body').on('click', '#BtnResult', function() {
                let defectStockId = $(this).data('id');

                $.ajax({
                    url: `${defectStockResultUrl}?id=${defectStockId}`,
                    beforeSend: function() {
                        $('#show-result').html('Loading');
                    }
                }).done(function(result) {
                    $('.modal_result').removeClass('modal-hide');
                    $('#show-result').html(result);
                });
            });


            $('body').on('click', '#closeModalImage', function() {
                $('.modal_image').addClass('modal-hide');
            });


            $('body').on('click', '#BtnUpdate', function() {
                let defectStockId = $(this).data('id');

                $.ajax({
                    url: `${defectStockDatatableUrl}?id=${defectStockId}`,
                    beforeSend: function() {
                        $('#form-update').html('Loading');
                    }
                }).done(function(result) {
                    $('.modal_update').removeClass('modal-hide');
                    $('#form-update').html(result);
                });
            });


            $('body').on('click', '#closeModalUpdate', function() {
                $('.modal_update').addClass('modal-hide');
            });


            $('body').on('click', '#BtnDelete', function() {
                let drop = confirm('Are you sure?');
                let defectStockId = $(this).data('id');

                if (drop) {
                    $.ajax({
                        type: 'POST',
                        url: defectStockDeleteUrl,
                        data: {
                            'id': defectStockId,
                            '_token': $('meta[name=csrf-token]').attr('content')
                        }
                    }).done(function(result) {
                        if (result.status === 1) {
                            alert('Data deleted successfully');
                            defectStockTable.ajax.reload(null, false);

                        } else {
                            alert(result.message);

                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
