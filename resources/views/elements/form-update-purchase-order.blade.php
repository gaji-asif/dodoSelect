<div class="mb-5">
    <label class="text-gray-600 block mb-1">
        Product Name:
    </label>
    <p class="text-lg font-bold">
        {{ $product->product_name ?? '' }}
    </p>
</div>
<div class="pb-8">
    <label class="text-gray-600 block mb-2">
        Incoming Orders (Not shipped):
    </label>
    <table class="w-full">
        <thead class="bg-blue-500">
            <tr>
                <th class="px-3 py-2 text-white">
                    {{ __('translation.ID') }}
                </th>
                <th class="px-3 py-2 text-white">
                    {{ __('translation.Incoming Qty') }}
                </th>
                <th class="px-3 py-2 text-white">
                    {{ __('translation.Order Date') }}
                </th>
              
            </tr>
        </thead>
        <tbody>
            @php

            @endphp

            @if (!empty($orderPurchaseDetails))
                @foreach ($orderPurchaseDetails as $detail)
                    @if($show_incoming_qty_not_shipped != 'y')
                    @if(number_format($detail->tQty) >0)
                    <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                        <td class="px-3 py-2">
                            PO ID: <a href="{{ route('order_purchase.edit', [ 'order_purchase' => $detail->order_purchase_id ]) }}" target="_blank" class="underline text-blue-500 font-bold">
                                {{ $detail->order_purchase_id }}
                            </a>
                        </td>
                        <td class="px-3 py-2">
                            {{ number_format($detail->tQty) }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $detail->order_date ? date('d-m-Y', strtotime($detail->order_date)) : '-' }}
                        </td>
                     
                    </tr>
                    @endif
                    @endif
                @endforeach
            @else
                <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                    <td colspan="4" class="px-3 py-2">
                        {{ __('translation.No data') }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>


<div class="mb-5">
</div>
<div class="pb-8">
    <label class="text-gray-600 block mb-2">
        Incoming Orders (Shipped):
    </label>
    <table class="w-full">
        <thead class="bg-blue-500">
            <tr>
                <th class="px-3 py-2 text-white">
                    {{ __('translation.ID') }}
                </th>
                <th class="px-3 py-2 text-white">
                    {{ __('translation.Ship Qty') }}
                </th>
                <th class="px-3 py-2 text-white">
                    {{ __('translation.Order Date') }}
                </th>
                <th class="px-3 py-2 text-white">
                    {{ __('translation.Ship Date') }}
                </th>
                <th class="px-3 py-2 text-white">
                    {{ __('translation.Estimated Arrival') }}<br>
                    {{ __('translation.Date From') }}
                </th>
                <th class="px-3 py-2 text-white">
                    {{ __('translation.Estimated Arrival') }}<br>
                    {{ __('translation.Date To') }}
                </th>
            </tr>
        </thead>
        <tbody>
            @if ($poShipmentDetails->isNotEmpty())
                @foreach ($poShipmentDetails as $detail)
                    <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                        <td class="px-3 py-2">
                            Ship ID: <a href="{{ route('order_purchase.edit', [ 'order_purchase' => $detail->order_purchase_id ]) }}" target="_blank" class="underline text-blue-500 font-bold">
                                {{ $detail->po_shipment_id }}
                            </a>

                        </td>
                        <td class="px-3 py-2">
                            {{ number_format($detail->ship_quantity) }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $detail->order_date ? date('d-m-Y', strtotime($detail->order_date)) : '-' }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $detail->ship_date ? date('d-m-Y', strtotime($detail->ship_date)) : '-' }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $detail->e_a_d_f ? date('d-m-Y', strtotime($detail->e_a_d_f)) : '-' }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $detail->e_a_d_t ? date('d-m-Y', strtotime($detail->e_a_d_t)) : '-' }}
                        </td>
                    </tr>
                @endforeach
            @else
                <tr class="border border-solid border-t-0 border-r-0 border-l-0 border-gray-200">
                    <td colspan="4" class="px-3 py-2">
                        {{ __('translation.No data') }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

