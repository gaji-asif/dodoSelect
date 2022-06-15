<x-app-layout>

    @section('title')
        {{ __('translation.Purchase Order Detail : ') . $orderPurchase->id }}
    @endsection

    @push('top_css')
        {{-- Your Css here --}}
    @endpush
    @if(\App\Models\Role::checkRolePermissions('Can access menu: Purchase Order - Purchase Order'))

        <div class="col-span-12">
            <x-card.card-default>
                <x-card.header>
                    <x-card.back-button href="{{ route('order_purchase.index') }}" />
                    <x-card.title>
                        {{ __('translation.Purchase Order Detail : ') . $orderPurchase->id }}
                    </x-card.title>
                    <div class="d-flex justify-content-end mt-2" style="width: 155px;">
                        <a class="btn btn-primary" href="{{ URL::to('/po/pdf/'.$orderPurchase->id) }}">Export to PDF</a>
                    </div>
                </x-card.header>
                <x-card.body>

                    <div class="mb-10">
                        <div class="flex flex-row items-center justify-between mb-3">
                            <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                                {{ __('translation.Purchase Order Info') }}
                            </h2>
                            <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">
                            <div>
                                <x-label>
                                    {{ __('translation.Supplier Name') }}
                                </x-label>
                                <div class="w-full h-10 py-2 font-bold">
                                    {{ $orderPurchase->supplier->supplier_name }}
                                </div>
                            </div>
                            <div>
                                <x-label>
                                    {{ __('translation.Order Date') }}
                                </x-label>
                                <div class="w-full h-10 py-2 font-bold">
                                    @if (!empty($orderPurchase->order_date))
                                        {{ $orderPurchase->order_date->format('d M Y') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div>
                                <x-label>
                                    {{ __('translation.Created Date') }}
                                </x-label>
                                <div class="w-full h-10 py-2 font-bold">
                                    @if (!empty($orderPurchase->created_at))
                                        {{ $orderPurchase->created_at->format('d M Y') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div>
                                <x-label>
                                    {{ __('translation.Ship Date') }}
                                </x-label>
                                <div class="w-full h-10 py-2 font-bold">
                                    @if (!empty($orderPurchase->ship_date))
                                        {{ $orderPurchase->ship_date->format('d M Y') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div>
                                <x-label>
                                    {{ __('translation.Status') }}
                                </x-label>
                                <div class="w-full h-10 py-2 font-bold">
                                <span class="badge-status--yellow">
                                    {{ $orderPurchase->status }}
                                </span>
                                </div>
                            </div>
                            <div>
                                <x-label>
                                    {{ __('translation.Estimated Arrival Date From') }}
                                </x-label>
                                <div class="w-full h-10 py-2 font-bold">
                                    @if (!empty($orderPurchase->e_a_d_f))
                                        {{ $orderPurchase->e_a_d_f->format('d M Y') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div>
                                <x-label>
                                    {{ __('translation.Estimated Arrival Date To') }}
                                </x-label>
                                <div class="w-full h-10 py-2 font-bold">
                                    @if (!empty($orderPurchase->e_a_d_t))
                                        {{ $orderPurchase->e_a_d_t->format('d M Y') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div>
                                <x-label>
                                    {{ __('translation.Author Name') }}
                                </x-label>
                                <div class="w-full h-10 py-2 font-bold">
                                    {{ $orderPurchase->author->name }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-10">
                        <div class="flex flex-row items-center justify-between mb-3">
                            <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                                {{ __('translation.Supply Info') }}
                            </h2>
                            <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">

                            @if ($orderPurchase->supply_from == $supplyFromImport)
                                <div class="lg:col-span-4">
                                    <x-label>
                                        {{ __('translation.Supply From') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        {{ $supplyFroms[$orderPurchase->supply_from] ?? '-' }}
                                    </div>
                                </div>
                                <div>
                                    <x-label>
                                        {{ __('translation.Factory Tracking') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        {{ $orderPurchase->factory_tracking }}
                                    </div>
                                </div>
                                <div>
                                    <x-label>
                                        {{ __('translation.Cargo References') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        {{ $orderPurchase->cargo_ref }}
                                    </div>
                                </div>
                                <div>
                                    <x-label>
                                        {{ __('translation.Number of Cartons') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        {{ $orderPurchase->number_of_cartons }}
                                    </div>
                                </div>
                                <div>
                                    <x-label>
                                        {{ __('translation.Domestic Logistics') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        {{ $orderPurchase->domestic_logistics }}
                                    </div>
                                </div>
                            @else
                                <div>
                                    <x-label>
                                        {{ __('translation.Supply From') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        {{ $supplyFroms[$orderPurchase->supply_from] ?? '-' }}
                                    </div>
                                </div>
                                <div>
                                    <x-label>
                                        {{ __('translation.Number of Cartons') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        {{ $orderPurchase->number_of_cartons1 }}
                                    </div>
                                </div>
                                <div>
                                    <x-label>
                                        {{ __('translation.Domestic Logistics') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        {{ $orderPurchase->domestic_logistics1 }}
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>



                    <div class="mb-10">
                        <div class="flex flex-row items-center justify-between mb-3">
                            <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                                {{ __('translation.Payment Info') }}
                            </h2>
                            <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">

                            @if($payment)

                                <div>
                                    <x-label>
                                        {{ __('translation.Amount Invoice') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        {{ $payment->amount }}
                                    </div>
                                </div>

                                <div>
                                    <x-label>
                                        {{ __('translation.Paid') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        {{ $payment->paid }}
                                    </div>
                                </div>

                                <div>
                                    <x-label>
                                        {{ __('translation.Currency') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        {{ $payment->name }}
                                    </div>
                                </div>
                                <div>
                                    <x-label>
                                        {{ __('translation.Bank Account') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        {{ $payment->bank_account }}
                                    </div>
                                </div>

                                <div>
                                    <x-label>
                                        {{ __('translation.Note') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        {{ $payment->notes }}
                                    </div>
                                </div>

                                <div>
                                    <x-label>
                                        {{ __('translation.Invoice') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        <img src="{{asset($payment->file_invoice)}}" style="max-width:75px;">
                                    </div>
                                </div>
                                <div>
                                    <x-label>
                                        {{ __('translation.Payment') }}
                                    </x-label>
                                    <div class="w-full h-10 py-2 font-bold">
                                        <img src="{{asset($payment->file_payment)}}" style="max-width:75px;">
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>

                    <div class="mb-10">
                        <div class="flex flex-row items-center justify-between mb-3">
                            <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                                {{ __('translation.Products') }}
                            </h2>
                            <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                        </div>
                        <div>

                            @if ($orderPurchase->order_purchase_details->isEmpty())
                                <div>

                                </div>
                            @else
                                @foreach ($orderPurchase->order_purchase_details as $detail)
                                    <div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                                        <div class="w-1/4 sm:w-1/4 md:w-1/6 xl:w-1/4 mb-4 md:mb-0">
                                            <div class="mb-4">
                                                <img src="{{ $detail->product->image_url }}" alt="{{ $detail->product->name }}" class="w-full h-auto rounded-lg">
                                            </div>
                                        </div>
                                        <div class="w-3/4 sm:w-3/4 md:w-5/6 xl:w-3/4 ml-4 sm:ml-6">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 lg:gap-x-5 lg:pt-1">
                                                <div class="mb-2 xl:mb-4 lg:col-span-2">
                                                    <div>
                                                        <label class="hidden lg:block mb-0">
                                                            {{ __('translation.Product Name') }} :
                                                        </label>
                                                        <p class="font-bold">
                                                            {{ $detail->product->product_name }} <br>
                                                            <span class="text-blue-500">
                                                            {{ $detail->product->product_code }}
                                                        </span>
                                                        </p>
                                                    </div>
                                                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-2">
                                                        <div>
                                                            <label class="mb-0">
                                                                {{ __('translation.Price') }} :
                                                            </label>
                                                            <span class="font-bold lg:block">
                                                            {{ currency_symbol('THB') }}
                                                                {{ number_format($detail->product->price, 2) }}
                                                        </span>
                                                        </div>
                                                        <div>
                                                            <label class="mb-0">
                                                                {{ __('translation.Pieces/Pack') }} :
                                                            </label>
                                                            <span class="font-bold lg:block">
                                                            {{ number_format($detail->product->pack) }}
                                                        </span>
                                                        </div>
                                                        <div>
                                                            <label class="mb-0">
                                                                {{ __('translation.Available Qty') }} :
                                                            </label>
                                                            <span class="font-bold lg:block">
                                                            @if (!empty($detail->product->get_quantity))
                                                                    {{ number_format($detail->product->get_quantity->quantity) }}
                                                                @else
                                                                    0
                                                                @endif
                                                        </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-2 xl:mb-4 lg:col-span-1">
                                                    <div class="grid grid-cols-1 gap-2">
                                                        <div>
                                                            <label class="mb-0">
                                                                {{ __('translation.Cost Per Piece') }} :
                                                            </label>
                                                            <span class="font-bold lg:block">
                                                            {{ currency_symbol('THB') }}
                                                                {{ number_format($detail->product_price, 3) }}
                                                        </span>
                                                        </div>
                                                        <div>
                                                            <label class="mb-0">
                                                                {{ __('translation.Currency') }} :
                                                            </label>
                                                            <span class="font-bold lg:block">
                                                            @if (!empty($detail->exchange))
                                                                    {{ $detail->exchange_rate->name }}
                                                                    ({{ $detail->exchange_rate_value }})
                                                                @else
                                                                    -
                                                                @endif
                                                        </span>
                                                        </div>
                                                        <div>
                                                            <label class="mb-0">
                                                                {{ __('translation.Order Qty') }} :
                                                            </label>
                                                            <span class="font-bold lg:block">
                                                            @if (!empty($detail->quantity))
                                                                    {{ number_format($detail->quantity) }}
                                                                @else
                                                                    0
                                                                @endif
                                                        </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="text-center pb-5">
                        <x-button-link href="{{ route('order_purchase.index') }}" color="green">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-arrow-left" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                            </svg>
                            <span class="ml-2">
                            {{ __('translation.Back') }}
                        </span>
                        </x-button-link>
                    </div>

                </x-card.body>
            </x-card.card-default>
        </div>

    @endif
</x-app-layout>
