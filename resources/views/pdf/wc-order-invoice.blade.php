<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        Quotation #{{ $orderManagement->order_id }}
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
            @if ($orderManagement->tax_enable == $taxEnableYes)
                <div style="float: left; width: 30%">
                    <div style="position: relative; top: 35px;">
                        @if (!empty($taxRateSetting->company_logo) && file_exists(storage_path('app/public/' . $taxRateSetting->company_logo)))
                            <img src="{{ storage_path('app/public/' . $taxRateSetting->company_logo) }}" alt="{{ $taxRateSetting->company_name }}" style="width: 8rem; height: auto">
                        @else
                            <img src="{{ public_path('No-Image-Found.png') }}" alt="{{ $taxRateSetting->company_name }}" style="width: 8rem; height: auto" />
                        @endif
                    </div>
                </div>
                <div style="float: left; width: 70%">
                    <div style="text-align: right">
                        <h1 style="position: relative; top: .75rem; font-size: 2.5rem;">
                            Quotation
                        </h1>
                        <div style="margin-top: 1rem; text-align: right">
                            <p style="color: #999; font-size: 12px">
                                {{ $taxRateSetting->company_name }} <br>
                                {{ $taxRateSetting->company_address }}<br>
                                {{ $taxRateSetting->company_district . ', ' . $taxRateSetting->company_sub_district . ', ' }}
                                {{ $taxRateSetting->company_province . ' ' . $taxRateSetting->company_postcode }}<br>
                                {{ 'Tax ID:' . ' ' . $taxRateSetting->tax_number }}
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div style="float: left; width: 30%">
                    <div style="position: relative; top: 35px;">
                        @if (!empty($shopDetails->logo) && file_exists(public_path($shopDetails->logo)))
                            <img src="{{ public_path($shopDetails->logo) }}" alt="{{ $shopDetails->name }}" style="width: 8rem; height: auto">
                        @else
                            <img src="{{ public_path('No-Image-Found.png') }}" alt="{{ $shopDetails->name }}" style="width: 8rem; height: auto" />
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
                                {{ $shopDetails->name }} <br>
                                {{ $shopDetails->address }}<br>
                                {{ $shopDetails->district . ', ' . $shopDetails->sub_district . ', ' }}
                                {{ $shopDetails->province . ' ' . $shopDetails->postcode }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
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
                                            {{ $billing_details['billing_name'] }}<br>
                                            {{ $billing_details['billing_address_1'] }}<br>
                                            {{ $billing_details['billing_city'] . ', ' . $billing_details['billing_state'] }}<br>
                                            {{ $billing_details['billing_postcode'] . ' ' . $billing_details['billing_country'] }}<br>
                                            {{ 'Phone : ' . $billing_details['billing_phone'] }}
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
                                            #INV{{ $orderManagement->order_id }}
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
                                            Total Amount:
                                        </td>
                                        <td style="padding: .15rem">
                                            {{ currency_symbol('THB') . currency_number($orderManagement->total, 3) }}
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
                        @foreach ($orderProductDetails as $detail)
                            <tr>
                                <td style="padding: .5rem 1rem; border-bottom: 1px dashed #ccc;">
                                    {{ $detail->name }}<br>
                                    ({{ $detail->sku }})
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
                                {{ currency_number($orderManagement->total, 3) }}
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
                        @if ($detail->total_tax)
                            <tr>
                                <td colspan="2" style="padding: .5rem 1rem; text-align: right;">
                                    {{ $taxRateSetting->tax_name }} ({{ currency_number($detail->total_tax, 2) . '%' }})
                                </td>
                                <td style="padding: .5rem 1rem; text-align: right;">
                                    <span>
                                        &nbsp;
                                    </span>
                                    {{ currency_symbol('THB') }}
                                </td>
                                <td style="padding: .5rem 1rem; text-align: right;">
                                    {{ currency_number($detail->subtotal_tax, 3) }}
                                </td>
                            </tr>
                        @endif
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
                                {{ currency_number($orderManagement->total, 3) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

</body>
</html>