<x-app-layout>
    @section('title', 'Orders List')

    <div class="col-span-12">

        @include('partials.pages.customers.tab_navigation')

    <x-card title="{{ $customer->customer_name }} - Order Log ({{ number_format($orderCount) }})">
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
                <tr class="rounded-lg text-sm font-medium text-gray-700 text-center">
                    <th>
                        {{ __('translation.Order ID') }}
                    </th>
                    <th>
                        {{ __('translation.Order Date') }}
                    </th>
                    <th>
                        {{ __('translation.Products Quantity') }}
                    </th>
                    <th>
                        {{ __('translation.Total Price') }}
                    </th>
                    <th>
                        {{ __('translation.Order Status') }}
                    </th>
                </tr>
                </thead>
                <tbody class="text-center"></tbody>
            </table>
        </div>

    </x-card>

    </div>

    {{--    Ordered Producnts List Modal--}}
    <x-modal.modal-large id="__modalProductsOrdered">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Ordered Products') }}
            </x-modal.title>
            <x-modal.close-button class="__btnCloseModalProductsOrder" />
        </x-modal.header>
        <x-modal.body>
            <div class="mb-5">
                <div class="mb-4">
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-1">
                            Order ID
                        </div>
                        <div class="col-span-2">
                            :
                            <strong class="ml-2 text-blue-500" id="__orderIdOutputProductsOrder">
                                -
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full overflow-x-auto mb-10">
                <table class="w-full" id="__tblProductProductsOrder">
                    <thead>
                    <tr>
                        <th class="w-24 md:w-36 text-center">
                            {{ __('translation.Image') }}
                        </th>
                        <th class="text-center">
                            {{ __('translation.Product Details') }}
                        </th>
                    </tr>
                    </thead>
                </table>
            </div>
            <div class="text-center pb-5 hidden" id="__actionButtonWrapperProductsOrder">
                <x-button type="button" color="gray" class="__btnCloseModalProductsOrder">
                    {{ __('translation.Close') }}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.modal-large>


@push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>

        <script>
            const customerId = {{ $customer->id }};
            const dataTableUrl = '{{ route('customer.order_datatable') }}';
            const dodoOrderedProductDataUrl = '{{ route('get_ordered_dodo_products') }}';

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

            const productsOrdered = (el) => {
                const orderId = el.getAttribute('data-order-id');
                const shipmentId = el.getAttribute('data-shipment-id');

                $('#__orderIdOutputProductsOrder').html(`#${orderId}`);

                $('#__tblProductProductsOrder').DataTable().destroy();
                const productTable = $('#__tblProductProductsOrder').DataTable({
                    ajax: {
                        type: 'GET',
                        url: dodoOrderedProductDataUrl,
                        data: {
                            orderId: orderId,
                        }
                    },
                    columnDefs : [
                        {
                            targets: [0],
                            orderable: false
                        },
                        {
                            targets: [1],
                            className: 'text-left'
                        }
                    ],
                    paging: false,
                    filter : false,
                    info : false,
                });

                productTable.on('draw', function() {
                    const { recordsTotal } = productTable.page.info();
                    totalDodoProductsOrdered = recordsTotal;

                    if (recordsTotal > 0) {
                        $('#__actionButtonWrapperProductsOrder').removeClass('hidden');
                    }
                });

                $('#__modalProductsOrdered').doModal('open');
            }

            $('.__btnCloseModalProductsOrder').on('click', function() {
                $('#__modalProductsOrdered').doModal('close');
            });

        </script>
    @endpush
</x-app-layout>
