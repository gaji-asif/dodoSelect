<x-app-layout>
    @section('title', 'Dropshipper Orders')

    @push('top_css')
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    @endpush

    @if (\App\Models\Role::checkRolePermissions('Can access menu: Dropshippers - Dropshipper Orders'))
        <x-card class="mt-0">
            <div style="margin-top: -2rem">
                @include('dropshipper.menu')
            </div>
            <hr>

            <card class="bg-gray-500 ">
                <div class="card-title my-4">
                    <h4><strong>Dropshipper Orders</strong></h4>
                </div>
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
                </div>

                <div class="w-full overflow-x-auto">
                    <table class="w-full table" id="datatable">
                        <thead>
                        <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                            <th>{{ __('translation.ID') }}</th>
                            <th>{{ __('translation.Shop Name') }}</th>
                            <th>{{ __('translation.Contact Name') }}</th>
                            <th>{{ __('translation.Role') }}</th>
                            <th>{{ __('translation.Total Orders') }}</th>
                            <th>{{ __('translation.Total Amount') }} ({{ currency_symbol('THB') }})</th>
                            <th>{{ __('translation.Action') }}</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </card>
        </x-card>
    @endif

    @push('bottom_js')
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>

        <script>
            $(".datepicker-1").datepicker({
                dateFormat: 'dd-mm-yy'
            });

            $(document).ready(function() {
                load_data();
            });

            const overviewTableUrl = '{{ route('dropshipper.orders_datatable') }}';

            function load_data() {
                $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    bDestroy: true,
                    ajax: {
                        type: 'GET',
                        url: overviewTableUrl,
                        dataSrc: function ( json ) {
                            return json.data;
                        }
                    },

                    order: [
                        [0, 'desc']
                    ],
                    columns: [
                        {
                            name: 'customer_id',
                            data: 'customer_id'
                        },
                        {
                            name: 'shop_name',
                            data: 'shop_name'
                        },
                        {
                            name: 'contactname',
                            data: 'contactname'
                        },
                        {
                            name: 'dropshipper_role',
                            data: 'dropshipper_role'
                        },

                        {
                            name: 'total_orders',
                            data: 'total_orders'
                        },
                        {
                            name: 'total_amount',
                            data: 'total_amount'
                        },
                        {
                            name: 'action',
                            data: 'action'
                        },
                    ],
                    columnDefs: [
                        {
                            targets: [6],
                            orderable: false
                        },
                        {
                            targets: [4, 5],
                            className: 'text-center'
                        },
                    ],
                    paginationType: 'numbers'
                });
            }

        </script>
    @endpush
</x-app-layout>
