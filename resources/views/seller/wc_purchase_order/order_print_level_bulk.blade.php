 <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
  <style type="text/css">
  @font-face {
    font-family: 'THSarabunNew';
    font-style: normal;
    font-weight: normal;
    src: url("{{ asset('fonts/Sarabun/Sarabun-Bold.ttf') }}");
  }
  .thaiFont {
    font-family: "THSarabunNew";
  }

  body{
    font-family: sans-serif;
    font-size: 24px;
    height: 100%;
  }

  .font_weight{
    font-weight: bold;
  }
  .order_details{
   /* margin-bottom: 20px;*/
    clear: both;
  }
  .shipping_address_wraps{
    clear: both;
  }
  html {
    height: 100%;
  }
</style>

@if(isset($allOrdersData))
@foreach($allOrdersData as $key=>$value)
@if(isset($all_shipment_ids[$key]))
<div style="width: 100%; clear: both; page-break-after: always;">

<div style="clear: both; width: 100%;">
<div style="float: left;">
  <strong>Shipment ID # {{$all_shipment_ids[$key]}}</strong>
</div>
<div style="float: right;">
  <strong>Order ID #{{$value->order_id}}</strong>
</div>
</div>
<br>

@php
  $shopDetails = \App\Models\WooOrderPurchase::getShopDetailsbyShopId($value->website_id);
  $shipping = $allShippings[$value->id];
@endphp

  <div style="clear: both; width: 100%;">
    <div style="float: left; width: 80%;">

      <div class="card shipping_address_wraps mb-3">
        <div class="card-body" style="padding: 10px; border-radius: 5px; border: 1px solid #000000; margin-top: 20px; margin-bottom: 25px;">
          <p class="">
            <strong>From</strong>
          </p>
          @if(empty($shopDetails->name) && empty($shopDetails->phone))
          <div class="form-group" id="no_selected1">
            No Address Selected Yet
          </div>
          @endif

          @if(isset($shopDetails->name))
          <font id="shipping_name_div1" class="font_weight thaiFont">
            {{$shopDetails->name}}
          </font>
          <br>
          @endif
          <font>
            @if(isset($shopDetails->address))
            <span id="" class="thaiFont">{{$shopDetails->address}}</span>
            @endif
           
          </font><br>

          <font>
            @if(isset($shopDetails->district))
            <span id="dis1" class="thaiFont">{{$shopDetails->district}},</span>
            @endif
            @if(isset($shopDetails->sub_district))
            <span id="sub1" class="thaiFont">{{$shopDetails->sub_district}}</span>
            @endif
          </font><br>
          <font class="thaiFont">
            @if(isset($shopDetails->province))
            <span id="pro1">{{$shopDetails->province}}</span>
            @endif
            @if(isset($shopDetails->postcode))
            <span id="postcodes" class="thaiFont">{{$shopDetails->postcode}}</span>@endif
          </font><br><br>
          <font class="font_weight" id="shipping_phone_div1">
            @if(isset($shopDetails->phone))
            {{$shopDetails->phone}}
            @endif
          </font>
        </div> 
      </div>

    </div>
    <div style="float: left; width: 19%; margin-top: 30px;">
         <img style="float: right;" src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(100)->generate($value->id)) !!} ">
    </div>

 </div>

 <div style="width: 100%; clear: both; margin-top: 20px;">
      <div style="padding: 10px; border-radius: 5px; border: 1px solid #000000; margin-top: 20px !important; margin-bottom: 15px; font-size: 33px;">
        <p class="">
          <strong>To</strong>
        </p>
		@php
    $orderDetails = \App\Models\WooOrderPurchase::getDetails($value->order_id);
		$shipping = $allShippings[$value->id];
		@endphp

        @if(empty($shipping['shipping_name']) && empty($shipping))
          <div class="form-group" id="no_selected">
            No Address Selected Yet
          </div>
        @endif
          @if(isset($shipping['shipping_name']))
          <span id="" class="thaiFont">{{ $shipping['shipping_name'] }}</span>
          @endif

          @if(isset($shipping['shipping_company']))
            <span id="" class="thaiFont">{{ $shipping['shipping_company'] }}</span>
          @endif

          @if(isset($shipping['shipping_address_1']))
            <span id="" class="thaiFont"> {{ $shipping['shipping_address_1'] }} </span>
          @endif

          @if(isset($shipping['shipping_address_2']))
          <span id="dis" class="thaiFont"> {{ $shipping['shipping_address_2'] }}, </span>
          @endif
          <br/>
          @if(isset($shipping['shipping_city']))
            <span id="sub" class="thaiFont"> {{ $shipping['shipping_city'] }} </span>
          @endif
          
          @if(isset($shipping['shipping_state']))
          <span id="sub"  class="thaiFont"> {{ $shipping['shipping_state'] }} </span>
          @endif
          <br/>
          @if(isset($shipping['shipping_postcode']))
          <span id="pro"  class="thaiFont"> {{ $shipping['shipping_postcode'] }} </span>
          @endif
          @if(isset($shipping['shipping_country']))
          <span id="pro"  class="thaiFont"> {{ $shipping['shipping_country'] }} </span>
          @endif
          <br>
          @if(isset($shipping['shipping_phone']))
          <span id="pro"  class="thaiFont"> {{ $shipping['shipping_phone'] }} </span>
          @endif
      <br>
                      
      </div> 
  </div>

   <div style="width: 100%;  font-size: 20px; bottom: 0; height: 60px; display: block; position: absolute;">
     <div style="float: left; width: 33%">
        SHOP NAME:<br>
        <strong>{{$shopDetails->name}}</strong>
      </div>
      <div style="float: left; width: 33%">
        CHANNEL NAME:<br>
        <strong>
         WooCommerce
       </strong>
      </div>
      <div style="float: left; width: 33%">
        SHIPPING METHOD:<br>
        <strong>
        {{$orderDetails['shipmentMethod']}}
      </strong>
      </div>
    </div> 
</div>

<!-- // start shipment Product details -->
<div style="clear: both; width: 100%;">
  <div class="order_details">

    <div style="clear: both; width: 100%;">
      <div style="float: left;">
        <div style="border-bottom: 1px solid #000000;">
        <strong>Shipment ID #@if(isset($all_shipment_ids[$key])) {{$all_shipment_ids[$key]}} @endif </strong>
        </div>
      <div style="border-bottom: 1px solid #000000;">
            <strong>Order ID #{{$value->order_id}}</strong>
      </div>
      </div>
      
      <div style="width: 50%; float: right;">
        <img style="float: right;" src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(100)->generate($value->id)) !!} ">
      </div>
    
    </div>
    <br>
  @php
  $shipmentProducts = \App\Models\ShipmentProduct::getallShipmentsProductsByShopIdOrderID($value->website_id,$value->order_id);
  @endphp

    @if (!empty($shipmentProducts))
    <div style="width: 100%; clear: both; page-break-after: always;">
     <div style="float: left; width: 100%;">
    <div class="full-card show" style="margin-top: 40px;">
      <table class="table table-responsive" style="font-size: 20px; width: 100%">
        <thead class="thead-light">
          <tr style="background-color: #e9ecef; width: 100%;">
            <th style="text-align: center;  padding: 5px;">Image</th>
            <th style="text-align: left; float: left;   padding: 5px;">Product Details</th>

          </tr>
        </thead>
        <tbody class="table-body">
          @if (!empty($shipmentProducts))
          @foreach ($shipmentProducts as $row)
          <tr style="margin-bottom: 20px; border-bottom:1px solid #000000;" class="new" id='@if (isset($row->product_id)){{($row->product_id)}} @endif ' >
            <input type="hidden" name="product_id[]" value="@if (isset($row->product_id)){{($row->product_id)}} @endif">

            <td style="padding: 5px; text-align: center;" valign="top">
            @php 
            $images = [];
            if(!empty($row->images)){
                $images = json_decode($row->images);
            }
            @endphp

            @if(isset($images[0]->src))
                <img src="{{ $images[0]->src}}" alt="{{ $row->product_name }}" height="80" width="80" alt="" style="margin-top: 13px;" >
            @else
                <img src="{{asset('No-Image-Found.png')}}" height="80" width="80" alt="" style="margin-top: 13px;" >
            @endif
            </td>
            <td style=" padding-left: 20px;  padding: 5px; margin-left: 10px;">
              <font class="thaiFont">@if (isset($row->product_name))
                {{($row->product_name)}}
                @endif
              </font>
              <br>
              <strong>SKU:</strong>
              @if (isset($row->product_code))
              {{($row->product_code)}}
              @endif
              <br>
              <strong>Price:</strong>
              @if (isset($row->price))
              <font class="thaiFont">฿</font>{{($row->price)}}
              @endif
              <br>
              <strong>Ordered Quantity: </strong>
              {{$row->quantity}}
            </td>
          </tr> 
          <tr><td colspan="2" style="height: 1px; width: 100%; margin-top: 5px; margin-bottom: 5px;"><hr></td></tr> 
          @endforeach
          @endif
        </tbody>

      </table>

    </div>
  </div>
<!-- <div style="width: 100%; clear: both; font-size: 20px; margin-top: 50px; position: absolute; bottom: 0; height: 60px;"> -->
      <div style="width: 100%;  font-size: 20px; bottom: 0; height: 60px; display: block;"> 
      <div style="float: left; width: 33%; margin-top: 50px;">
        SHOP NAME:<br>
        <strong>{{$shopDetails->name}}</strong>
      </div>

      <div style="float: left; width: 33%; margin-top: 50px;">
        CHANNEL NAME:<br>
        <strong>
        Woocommerce
      </strong>
      </div>
      <div style="float: left; width: 33%; margin-top: 50px;">
        SHIPPING METHOD:<br>
        <strong>        
        {{$orderDetails['shipmentMethod']}}    
       </strong>
      </div>
    </div> 
</div>
    @endif
</div>
</div>
@endif 
@endforeach
@endif





