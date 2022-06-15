<x-app-layout>
    @section('title')
        {{ __('translation.Stock Movements') }}
    @endsection

    @push('top_css')
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    @endpush

    @if (\App\Models\Role::checkRolePermissions('Can access menu: Report - Stock Movements'))

        <div class="col-span-12">
            <x-card.card-default>
                <x-card.header>
                    <x-card.title>
                        {{ __('translation.Stock Movements') }}
                    </x-card.title>
                </x-card.header>
                <x-card.body>
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
                    </div>

                    <div class="flex flex-col sm:flex-row sm:justify-between items-start mb-4">
                        <div class="w-full mb-6 sm:mb-0">
                            <div class="flex flex-col lg:flex-row">
                                <div class="w-full mb-6">
                                    <div class="grid grid-cols-2 md:gap-x-5 sm:gap-2">
                                        <x-input type="text" name="filter_fromDate" id="filter_fromDate" class="datepicker-1" value="{{ old('filter_fromDate') }}" placeholder="Enter From Date" autocomplete="off"/>
                                        <x-input type="text" name="filter_toDate" id="filter_toDate" class="datepicker-1" value="{{ old('filter_toDate') }}" placeholder="Enter To Date" autocomplete="off"/>
                                    </div>
                                </div>
                                <div class="w-full md:w-3/4 lg:w-4/5 ml-2 sm:justify-end lg:mt-1">
                                    <x-button type="button" color="blue" id="filter_button" class="mb-2">
                                        <span class="ml-2">Filter</span>
                                    </x-button>

                                    <x-button type="button" color="gray" id="reset_button">
                                        <span class="ml-2">Reset</span>
                                    </x-button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full" id="datatable">
                            <thead>
                            <tr>
                                <th>{{ __('translation.ID') }}</th>
                                <th>{{ __('translation.Image') }}</th>
                                <th>{{ __('translation.Product Name') }}</th>
                                <th>{{ __('translation.Product Code') }}</th>
                                <th>{{ __('translation.Added') }}</th>
                                <th>{{ __('translation.Removed') }}</th>
                                <th>{{ __('translation.Net Change') }}</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </x-card.body>
            </x-card.card-default>
        </div>
    @endif

    <x-modal.modal-large class="modal-excel modal-hide">
        <x-modal.header>
            <x-modal.title>
                Excel Export
            </x-modal.title>
            <x-modal.close-button id="closeModalproduct" />
        </x-modal.header>
        <x-modal.body>
            <div id="excel-wrapper"></div>
        </x-modal.body>
    </x-modal.modal-large>


    @push('bottom_js')
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
        <script>

            $(".datepicker-1").datepicker({
                dateFormat: 'dd-mm-yy'
            });
            $(document).ready(function() {
                load_data();
            });
            const reportTableUrl = '{{ route('data_stock_movement_report') }}';

            function load_data(from_date = '', to_date = '') {
                $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    bDestroy: true,
                    ajax: {
                        type: 'GET',
                        url: reportTableUrl,
                        data: {
                            from_date: from_date,
                            to_date: to_date
                        },
                        dataSrc: function ( json ) {
                            return json.data;
                        }
                    },
                    order: [
                        [0, 'desc']
                    ],
                    columns: [
                        {
                            name: 'id',
                            data: 'id'
                        },
                        {
                            name: 'image',
                            data: 'image'
                        },
                        {
                            name: 'product_name',
                            data: 'product_name'
                        },
                        {
                            name: 'product_code',
                            data: 'product_code'
                        },

                        {
                            name: 'added',
                            data: 'added'
                        },
                        {
                            name: 'removed',
                            data: 'removed'
                        },
                        {
                            name: 'net_change',
                            data: 'net_change'
                        },
                    ],
                    columnDefs: [
                        {
                            targets: [1, 5],
                            orderable: true
                        },
                        {
                            targets: [4, 5],
                            className: 'text-center'
                        },
                    ],
                    paginationType: 'numbers'
                });
            }

            $('#reset_button').on('click',function() {
                $('#filter_fromDate').val('');
                $('#filter_toDate').val('');
                $('#datatable').DataTable().destroy();
                load_data();
            });

            $('#filter_button').click(function() {
                var from_date = $('#filter_fromDate').datepicker({dateFormat: 'yyyy-mm-dd'}).val();
                var to_date = $('#filter_toDate').datepicker({dateFormat: 'yyyy-mm-dd'}).val();
                if (from_date != '' && to_date != '') {
                    $('#datatable').DataTable().destroy();
                    load_data(from_date, to_date);
                } else if (from_date != '' && to_date == '') {
                    $('#datatable').DataTable().destroy();
                    load_data(from_date, from_date);
                } else if (from_date == '' && to_date != '') {
                    $('#datatable').DataTable().destroy();
                    load_data(to_date, to_date);
                } else {
                    alert('Please enter both dates.');
                }
            });

        </script>
    @endpush

</x-app-layout>
