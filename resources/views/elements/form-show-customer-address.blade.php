@php
    $arr_country = array();
    foreach ($countries as $country){
        $arr_country[$country->code] = $country->name;
    }
    $arr_state = array();
    foreach ($states as $state){
        $arr_state[$state->code] = $state->name;
    }

    $billing = json_decode($data->billing);
    $shipping = json_decode($data->shipping);
    $billing_name = "";
    $billing_email = "";
    $billing_phone = "";
    $billing_company = "";
    $billing_address_1 = "";
    $billing_address_2 = "";
    $billing_city = "";
    $billing_state = "";
    $billing_postcode = "";
    $billing_country = "";
    $shipping_name = "";
    $shipping_email = "";
    $shipping_phone = "";
    $shipping_company = "";
    $shipping_address_1 = "";
    $shipping_address_2 = "";
    $shipping_city = "";
    $shipping_state = "";
    $shipping_postcode = "";
    $shipping_country = "";

    if (isset($platform) and !empty($platform) and in_array($platform, ["shopee", "lazada"])) {
        if ($platform == "shopee") {
            if (isset($shipping)) {
                $shipping_name = $billing_name = isset($shipping->name)?$shipping->name ." ":"";
            }
            $shipping_phone = $billing_phone = isset($shipping->phone)?$shipping->phone:"";
            $shipping_country = $billing_country = $arr_country[$shipping->country];
            $shipping_address_1 = $billing_address_1 = $shipping->full_address;
            $shipping_city = $billing_city = $shipping->city;
            $shipping_state = $billing_state = $shipping->state;
            $shipping_postcode = $billing_postcode = $shipping->zipcode;
            $shipping_country = $billing_country = $arr_country[$shipping->country];
        } else {
            if (isset($billing)) {
                $billing_name = isset($billing->first_name)?$billing->first_name ." ":"";
                $billing_name .= isset($billing->last_name)?$billing->last_name:"";
                $billing_phone = isset($billing->phone)?$billing->phone:"";
                $billing_country = isset($billing->country)?$billing->country:"";
                $billing_address_1 = isset($billing->address_1)?$billing->address_1:"";
                $billing_city = isset($billing->city)?$billing->city:"";
                $billing_state = isset($billing->state)?$billing->state:"";
                $billing_postcode = isset($billing->post_code)?$billing->post_code:"";
            }
            if (isset($shipping)) {
                $shipping_name = isset($shipping->first_name)?$shipping->first_name ." ":"";
                $shipping_name .= isset($shipping->last_name)?$shipping->last_name:"";
                $shipping_phone = isset($shipping->phone)?$shipping->phone:"";
                $shipping_country = isset($shipping->country)?$shipping->country:"";
                $shipping_address_1 = isset($shipping->address_1)?$shipping->address_1:"";
                $shipping_city = isset($shipping->city)?$shipping->city:"";
                $shipping_state = isset($shipping->state)?$shipping->state:"";
                $shipping_postcode = isset($shipping->post_code)?$shipping->post_code:"";
            }
        }
    } else {
        /* This is for "WooCommerce" */
        $billing_name = $billing->first_name." ".$billing->last_name;
        $billing_email = $billing->email;
        $billing_phone = $billing->phone;
        $billing_company = $billing->company;
        $billing_address_1 = $billing->address_1;
        $billing_address_2 = $billing->address_2;
        $billing_city = $billing->city;
        $billing_state = $arr_state[$billing->state];
        $billing_postcode = $billing->postcode;
        $billing_country = $arr_country[$billing->country];

        $shipping_name = $shipping->first_name." ".$shipping->last_name;
        if(isset($shipping->email)){$shipping_email = $shipping->email;}else{ $shipping_email = $billing_email; }
        if(isset($shipping->phone)){$shipping_phone = $shipping->phone;}else{ $shipping_phone = $billing_phone; }
        $shipping_company = $shipping->company;
        $shipping_address_1 = $shipping->address_1;
        $shipping_address_2 = $shipping->address_2;
        $shipping_city = $shipping->city;
        $shipping_state = $arr_state[$shipping->state];
        $shipping_postcode = $shipping->postcode;
        $shipping_country = $arr_country[$shipping->country];
    }
@endphp

<table width="378" style="margin: 0 auto; padding: 10px; width: 100%;">
        <tbody>
        <tr>
            <td>
                <div class="font-bold text-gray-900 text-uppercase">{{__("shopee.order.billing_to")}}:</div>
            </td>
        </tr>
        <tr>
            <td>
                <div>{{ $billing_name }}</div>
            </td>
        </tr>

        <tr class="@if(empty($billing_email)) hide @endif">
            <td>
                <div>{{ $billing_company }}</div>
            </td>
        </tr>

        <tr>
            <td>
                <div>{{ $billing_address_1 }}</div>
            </td>
        </tr>
        <tr class="@if(empty($billing_address_2)) hide @endif">
            <td>
                <div>{{ $billing_address_2 }}</div>
            </td>
        </tr>

        <tr>
            <td>
                <div>
                    {{ $billing_city }}
                    {{ $billing_state }}
                    {{ $billing_postcode }}
                    {{ $billing_country }}
                </div>
            </td>
        </tr>

        <tr class="@if(empty($billing_email))  hide @endif">
            <td>
                <div><span class="font-bold">{{__("shopee.email")}}: </span>{{ $billing_email }}</div>
            </td>
        </tr>

        <tr>
            <td>
                <div><span class="font-bold">{{__("shopee.phone")}}: </span>{{ $billing_phone }}</div>
            </td>
        </tr>

        <tr></tr>
        <tr></tr>

        <tr>
            <td>
                <div class="font-bold text-gray-900 text-uppercase mt-6">{{__("shopee.order.shipping_to")}}:</div>
            </td>
        </tr>

        <tr>
            <td>
                <div>{{$shipping_name}}1</div>
            </td>
        </tr>

        <tr class="@if(empty($billing_email)) hide @endif">
            <td>
                <div>{{ $shipping_company }}-2</div>
            </td>
        </tr>
        <tr>
            <td>
                <div>{{ $shipping_address_1 }}-3</div>
            </td>
        </tr>
        <tr class="@if(empty($shipping_address_2)) hide @endif">
            <td>
                <div>{{ $shipping_address_2 }}-4</div>
            </td>
        </tr>

        <tr>
            <td>
                <div>
                  {{ $shipping_city }}-5 District
                  {{ $shipping_state }}-6
                  {{ $shipping_postcode }}-7
                  {{ $shipping_country }}-8
                </div>
            </td>
        </tr>

        <tr class="@if(empty($shipping_email)) hide @endif">
            <td>
                <div><strong>{{__("shopee.email")}}: </strong>{{ $shipping_email }}-9</div>
            </td>
        </tr>

        <tr>
            <td>
                <div><strong>{{__("shopee.phone")}}: </strong>{{ $shipping_phone }}-10</div>
            </td>
        </tr>
      </tbody>
</table>
<div class="flex justify-center py-6">
    <x-button type="reset" color="blue" class="mr-1 btnCloseModalAddress" id="btnCancelModalAddress">
        {{__("shopee.close")}}
    </x-button>
</div>

<script>
    $(document).ready(function() {
        $('.btnCloseModalAddress').click(function() {
            $('.modal-address').doModal('close');
        });
    });
</script>
