<x-app-layout>

    @section('title')
        Tax Invoices
    @endsection

    @push('top_css')
        {{--  --}}
    @endpush

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Tax Invoices'))
        <div class="col-span-12">
        <x-card.card-default>
            <x-card.header>
                <x-card.title>
                    Tax Invoices
                </x-card.title>
            </x-card.header>
            <x-card.body>
                <table class="w-full" id="__taxInvoiceTable">
                    <thead>
                        <tr>
                            <th class="px-2 py-4 bg-blue-500 text-white">
                                #
                            </th>
                            <th class="px-2 py-4 bg-blue-500 text-white">
                                Order/Tax ID <br>
                                Status
                            </th>
                            <th class="px-2 py-4 bg-blue-500 text-white">
                                Company Info
                            </th>
                            <th class="px-2 py-4 bg-blue-500 text-white">
                                Order Date
                            </th>
                            <th class="px-2 py-4 bg-blue-500 text-white">
                                Total Amount
                            </th>
                            <th class="px-2 py-4 bg-blue-500 text-white">
                                Action
                            </th>
                        </tr>
                    </thead>
                </table>
            </x-card.body>
        </x-card.card-default>
    </div>
    @endif

    @push('bottom_js')
        <script>
            const taxInvoiceTableUrl = '{{ route('tax-invoice.datatable') }}';
        </script>
        <script src="{{ asset('pages/seller/tax-invoice/index/datatable.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
