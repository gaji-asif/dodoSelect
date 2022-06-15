<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css">
            #shipping_label .cn-html-body {
                width: 100% !important;
                height: 500px !important;
                margin: 0 auto;
            }
            @media print {
               .delivery-note {
                    page-break-after: always;
                }
            }
        </style>
    </head>
    <body>
        <table>
            @if (!empty($invoice_html))
            <tr>
                <td>{!! $invoice_html !!}</td>
            </tr>
            @endif

            @if (!empty($shipping_label_html))
            <tr id="shipping_label">
                <td>{!! $shipping_label_html !!}</td>
            </tr>
            @endif

            @if (!empty($carrier_manifest_html))
            <tr>
                <td>{!! $carrier_manifest_html !!}</td>
            </tr>
            @endif
        </table>
    </body>
</html>