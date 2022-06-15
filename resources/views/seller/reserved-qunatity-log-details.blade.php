<x-app-layout>
    @section('title', 'Reserve Quantity Log')

    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">
    @endpush

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Product'))
        <x-card title="{{ $product->product_name }} - Reserve Quantity Log ({{ number_format($quantityLogCount) }})">
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

        <div class="w-full overflow-x-auto">
            <table class="w-full" id="__quantityLogTable">
                <thead class="bg-blue-500">
                    <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                        <th class="px-4 py-2"></th>
                        <th class="px-4 py-2">
                            {{ __('translation.Platform') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.Order ID') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.Shop') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.Quantity') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.Status') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.Created At') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>

    </x-card>
    @endif

    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>

        <script>
            const productId = "{{ $product->id }}";
            const dataTableUrl = "{{ route('seller-reserved-quantity-details-datatable') }}";

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
                        targets: [1,2,3,4],
                        orderable: false
                    }
                ],
                // order: [
                //     [ 4, 'desc' ]
                // ],
                paginationType: 'numbers',
                select: {
                    style: 'multiple'
                }
            });

        </script>
    @endpush
</x-app-layout>
