
<!-- Export to PDF -->
<!-- Export to PDF -->
<!-- Export to PDF -->
<!DOCTYPE html>
<html lang="th">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>PO Sheet #{{ $orderPurchase->id }}</title>
    <style>
    @font-face {
        font-family: 'Sarabun-Regular';
        font-style: normal;
        font-weight: normal;
        src: url("{{ asset('fonts/Sarabun/Sarabun-Regular.ttf') }}") format('truetype');
    }
    body {
        font-family: 'Sarabun-Regular';
    }
    </style>
</head>

<div class="mb-10">
                    <div class="flex flex-row items-center justify-between mb-3">
                        <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                            {{ __('translation.Purchase Order Info') }} #{{ $orderPurchase->id }}
                        </h2>
                        <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-x-8">
                        <div>
                            <x-label>
                               <strong> {{ __('translation.Supplier Name') }}: </strong> {{ $orderPurchase->supplier->supplier_name }}
                            </x-label>
                        </div>
                        <div>
                            <x-label>
                            <strong>{{ __('translation.Order Date') }}: </strong>
                            </x-label>
                            @if (!empty($orderPurchase->order_date))
                                {{ $orderPurchase->order_date->format('d M Y') }}
                            @else
                                -
                            @endif
                            </div>
                        </div>

                        <div>
                            <x-label>
                            <strong> {{ __('translation.Ship Type') }}: </strong>
                            </x-label>
                                @foreach ($shiptypes as $shiptype)
                                    @if($orderPurchase->shipping_type_id == $shiptype->id)
                                        {{ $shiptype->name }}
                                    @endif
                                @endforeach
                        </div>

                        <div>
                            <x-label>
                            <strong> {{ __('translation.Cargo') }}: </strong>
                            </x-label>
                                @foreach ($cargos as $cargo)
                                    @if($orderPurchase->agent_cargo_id == $cargo->id)
                                        {{ $cargo->name }}
                                    @endif
                                @endforeach
                        </div>

                        <div>
                            <x-label>
                            <strong> {{ __('translation.Shipping Mark') }}: </strong>
                            </x-label>
                                @foreach ($shipping_marks as $shipping_mark)
                                    @if($orderPurchase->shipping_mark_id == $shipping_mark->id)
                                        {{ $shipping_mark->shipping_mark }}
                                    @endif
                                @endforeach
                        </div>



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
                    <table>

                        @if ($orderPurchase->order_purchase_details->isEmpty())

                        @else
                            @foreach ($orderPurchase->order_purchase_details as $detail)
                                <tr style="float:left;width:720px;margin-bottom:50px;border-bottom:1px solid #999;">
                                    <td style="float:left;width:200px;height:240px;padding-bottom:20px;" class="w-1/4 sm:w-1/4 md:w-1/6 xl:w-1/4 mb-4 md:mb-0">
                                        <img style="float:left;width:200px;margin-top:15px;" src="{{ $detail->product->image_url }}" alt="{{ $detail->product->name }}">
                                    </td>
                                    <td style="float:left;width:500px;padding-left:40px;padding-bottom:20px;">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 lg:gap-x-5 lg:pt-1">
                                            <div class="mb-2 xl:mb-4 lg:col-span-2">
                                                <div>
                                                    <label class="hidden lg:block mb-0">
                                                      <strong>  {{ __('translation.Product Name') }} : </strong>
                                                    </label>
                                                    <p class="font-bold" style="font-family: 'Sarabun-Regular';">
                                                        {{ $detail->product->product_name }} <br>
                                                        <span class="text-blue-500">
                                                            {{ $detail->product->product_code }}
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-2">

                                                    <div>
                                                        <label class="mb-0">
                                                            {{ __('translation.Order(Packs)') }} :
                                                        </label>
                                                        <span class="font-bold lg:block">
                                                            @if (!empty($detail->quantity))
                                                                {{ number_format($detail->quantity) }}
                                                            @else
                                                                0
                                                            @endif
                                                        </span>
                                                    </div>

                                                    <div>
                                                        <label class="mb-0">
                                                        {{ __('translation.Order(Pieces)') }} :
                                                        </label>
                                                        <span class="font-bold lg:block">
                                                            {{ number_format($detail->product->pack*$detail->quantity) }}
                                                        </span>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <br/>
                            @endforeach
                        @endif
                    </table>
                    </div>
                </div>
            </html>


