<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        Order Invoice #{{ $orderManagement->id }}
    </title>

    <style>
        * {
            margin: 0px;
            padding: 0px;
        }

        *::before,
        *::after {
            border-box-sizing: border-box;
        }

        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url('./fonts/Sarabun/Sarabun-Regular.ttf');
        }

        body {
            font-family: 'THSarabunNew', sans-serif;
            color: #222;
            font-size: 14px;
        }

        table tbody td {
            vertical-align: top;
        }
    </style>
</head>
<body>

    <div style="padding: 1cm;">
        <div style="clear: both">
            <div style="float: left; width: 30%">
                <div style="position: relative; top: 35px;">
                    @if (!empty($orderManagement->shop->logo) && file_exists(public_path($orderManagement->shop->logo)))
                        <img src="{{ public_path($orderManagement->shop->logo) }}" alt="{{ $orderManagement->shop->name }}" style="width: 8rem; height: auto">
                    @else
                        <img src="{{ public_path('No-Image-Found.png') }}" alt="{{ $orderManagement->shop->name }}" style="width: 8rem; height: auto" />
                    @endif
                </div>
            </div>
            <div style="float: left; width: 70%">
                <div style="text-align: right">
                    <h1 style="position: relative; top: .75rem; font-size: 2.5rem;">
                        Invoice
                    </h1>
                    <div style="margin-top: 1rem; text-align: right">
                        <p style="color: #999; font-size: 12px">
                            {{ $orderManagement->shop->name }} <br>
                            {{ $orderManagement->shop->address }}<br>
                            {{ $orderManagement->shop->district . ', ' . $orderManagement->shop->sub_district . ', ' }}
                            {{ $orderManagement->shop->province . ' ' . $orderManagement->shop->postcode }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="clear: both; padding-top: 1rem; padding-left: 1cm; padding-right: 1cm;">
        <hr style="position: relative; width: 100%; border: none; border-top: 4px solid #222;">
        <hr style="position: relative; margin-top: 2px; width: 100%; border: none; border-top: 2px solid #222;">

        <div style="margin-top: 20px">
            <table style="width: 100%; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <td style="width: 60%; border: 1px solid transparent;">
                            <table style="width: 100%; border-collapse: collapse">
                                <tbody>
                                    <tr>
                                        <td style="padding: .15rem; text-align: right; color: #555">
                                            <span style="display: block; white-space: nowrap">
                                                Bill To:
                                            </span>
                                        </td>
                                        <td style="padding: .15rem">
                                            {{ $orderManagement->shipping_name }}<br>
                                            {{ $orderManagement->shipping_address }}<br>
                                            {{ $orderManagement->shipping_district . ', ' . $orderManagement->shipping_sub_district }}<br>
                                            {{ $orderManagement->shipping_province . ' ' . $orderManagement->shipping_postcode }}<br>
                                            Phone : {{ $orderManagement->shipping_phone }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td style="width: 40%; border: 1px solid transparent;">
                            <table style="width: 100%; border-collapse: collapse">
                                <tbody>
                                    <tr>
                                        <td style="padding: .15rem; text-align: right; color: #555">
                                            Invoice Date:
                                        </td>
                                        <td style="padding: .15rem">
                                            {{ strftime('%d %b %Y', strtotime($orderManagement->created_at)) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: .15rem; text-align: right; color: #555">
                                            Invoice ID:
                                        </td>
                                        <td style="padding: .15rem">
                                            #INV{{ $orderManagement->id }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: .15rem; text-align: right; color: #555">
                                            Currency:
                                        </td>
                                        <td style="padding: .15rem">
                                            THB ({{ currency_symbol('THB') }})
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: .15rem; text-align: right; color: #555">
                                            Amount Due:
                                        </td>
                                        <td style="padding: .15rem">
                                            {{ currency_symbol('THB') . currency_number($orderManagement->in_total, 3) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            <div style="margin-top: 10px;">
                <table class="table-charge-details" style="width: 100%; border-collapse: collapse">
                    <thead>
                        <tr>
                            <th colspan="3" style="padding: 1rem; border-top: 1px solid #222; border-bottom: 1px solid #222; text-transform: uppercase; text-align: left;">
                                Description
                            </th>
                            <th style="width: 70px; padding: 1rem; border-top: 1px solid #222; border-bottom: 1px solid #222; text-transform: uppercase; text-align: right;">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orderManagement->order_management_details as $detail)
                            <tr>
                                <td style="padding: .5rem 1rem; border-bottom: 1px dashed #ccc;">
                                    {{ $detail->product->product_name }}<br>
                                    ({{ $detail->product->product_code }})
                                </td>
                                <td style="width: 20px; padding: .5rem 1rem; border-bottom: 1px dashed #ccc; text-align: right;">
                                    x{{ number_format($detail->quantity) }}
                                </td>
                                <td style="width: 30px; padding: .5rem 1rem; border-bottom: 1px dashed #ccc; text-align: right;">
                                    <span>
                                        &nbsp;
                                    </span>
                                    {{ currency_symbol('THB') }}
                                </td>
                                <td style="padding: .5rem 1rem; border-bottom: 1px dashed #ccc; text-align: right;">
                                    @php
                                        $totalPrice = $detail->price * $detail->quantity;
                                    @endphp
                                    {{ currency_number($totalPrice, 3) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" style="padding: .5rem 1rem; border-top: 1px solid #222; text-align: right;">
                                Sub Total
                            </td>
                            <td style="padding: .5rem 1rem; border-top: 1px solid #222; text-align: right;">
                                <span>
                                    &nbsp;
                                </span>
                                {{ currency_symbol('THB') }}
                            </td>
                            <td style="padding: .5rem 1rem; border-top: 1px solid #222; text-align: right;">
                                {{ currency_number($orderManagement->sub_total, 3) }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding: .5rem 1rem; text-align: right;">
                                Shipping Cost
                            </td>
                            <td style="padding: .5rem 1rem; text-align: right;">
                                <span>
                                    &nbsp;
                                </span>
                                {{ currency_symbol('THB') }}
                            </td>
                            <td style="padding: .5rem 1rem; text-align: right;">
                                {{ currency_number($orderManagement->shipping_cost, 3) }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding: .5rem 1rem; text-align: right;">
                                Discount
                            </td>
                            <td style="padding: .5rem 1rem; text-align: right;">
                                <span>
                                    -
                                </span>
                                {{ currency_symbol('THB') }}
                            </td>
                            <td style="padding: .5rem 1rem; text-align: right;">
                                {{ currency_number($orderManagement->amount_discount_total, 3) }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding: .5rem 1rem; border-top: 1px solid #ccc; text-align: right;">
                                Total
                            </td>
                            <td style="padding: .5rem 1rem; border-top: 1px solid #ccc; text-align: right;">
                                <span>
                                    &nbsp;
                                </span>
                                {{ currency_symbol('THB') }}
                            </td>
                            <td style="padding: .5rem 1rem; border-top: 1px solid #ccc; text-align: right;">
                                {{ currency_number($orderManagement->in_total, 3) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

</body>
</html>