<x-app-layout>
    @section('title', 'Custom Orders List')

    <div class="col-span-12">

        @include('partials.pages.customers.tab_navigation')

        <x-card title="{{ $customer->customer_name }} - Custom Order Log ({{ number_format($customOrderCount) }})">
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
            </div>

            <div class="w-full overflow-x-auto">
                <table class="w-full" id="ordersTable">
                    <thead class="bg-blue-500">
                    <tr class="rounded-lg text-sm font-medium text-gray-700 text-left">
                        <th class="px-4 py-2">
                            {{ __('translation.Order ID') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.Order Date') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.Products Quantity') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.Total Price') }}
                        </th>
                        <th class="px-4 py-2">
                            {{ __('translation.Order Status') }}
                        </th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </x-card>

    </div>

    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>

        <script>
            const customerId = {{ $customer->id }};
            const dataTableUrl = '{{ route('customer.custom_order_datatable') }}';

            var ordersTable = $('#ordersTable').DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    type: 'GET',
                    url: dataTableUrl,
                    data: {
                        customerId: customerId
                    }
                },
                order: [
                    [ 1, 'desc' ]
                ],
                paginationType: 'numbers',
            });

        </script>
    @endpush
</x-app-layout>
