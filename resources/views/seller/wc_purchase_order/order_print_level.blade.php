<!DOCTYPE>
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
    font-size: 25px;
    height: 100%;

  }

  .font_weight{
    font-weight: bold;
  }
  html {
    height: 100%;
  }
</style>
<html>
<body>
  <div style="width: 100%;margin-top: 30px;">
    <div style="float: left;">
      <strong>Shipment ID #{{$shipment_id}}</strong>
    </div>
    <div style="float: right;">
      <strong>Order ID #{{$orderDetails->order_id}}</strong>
    </div>
    <br>
    <div style="width: 100%;">
      <div style="float: left; width: 80%;">

        <div class="card shipping_address_wraps mb-3">
          <div class="card-body" style="padding: 10px; border-radius: 5px; border: 1px solid #000000; margin-top: 20px; margin-bottom: 20px;">
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
    

     </div>

   </div>

   <div style="width: 100%; clear: both; margin-top: 20px;">
    <div style="padding: 10px; border-radius: 5px; border: 1px solid #000000; margin-top: 20px !important; margin-bottom: 15px; font-size: 33px;">
      <p class="">
        <strong>To</strong>
      </p>
      @if(empty($shipping['shipping_name']))
      <div class="form-group" id="no_selected">
        No Address Selected Yet
      </div>
      @endif
      <font id="shipping_address_div">
        @if(isset($shipping['shipping_name']))
        <span id="" class="thaiFont">{{ $shipping['shipping_name'] }}</span><br/>
        @endif

        @if(isset($shipping['shipping_company']))
        <span id="" class="thaiFont">{{ $shipping['shipping_company'] }}</span>
        @endif

        @if(isset($shipping['shipping_address_1']))
        <span id="" class="thaiFont"> {{ $shipping['shipping_address_1'] }} </span>
        @endif

        @if(isset($shipping['shipping_address_2']))
        <span id="dis" class="thaiFont"> {{ $shipping['shipping_address_2'] }}, </span><br/>
        @endif

        @if(isset($shipping['shipping_city']))
        <span id="sub" class="thaiFont"> {{ $shipping['shipping_city'] }} </span>
        @endif

        @if(isset($shipping['shipping_state']))
        <span id="sub"  class="thaiFont"> {{ $shipping['shipping_state'] }} </span><br/>
        @endif

        @if(isset($shipping['shipping_postcode']))
        <span id="pro"  class="thaiFont"> {{ $shipping['shipping_postcode'] }} </span>
        @endif
        @if(isset($shipping['shipping_country']))
        <span id="pro"  class="thaiFont"> {{ $shipping['shipping_country'] }} </span>
        @endif
        <br/>
        @if(isset($shipping['shipping_phone']))
        <span id="pro"  class="thaiFont"> {{ $shipping['shipping_phone'] }} </span>
        @endif

      </font><br>
    </div>

    <div style="width: 100%;  font-size: 20px; bottom: 0; height: 60px; display: block; position: absolute;">
      <div style="float: left; width: 33%;">
        SHOP NAME:<br>
        <strong>{{$shopDetails->name}}</strong>
      </div>

      <div style="float: left; width: 33%; ">
        CHANNEL NAME:<br>
        <strong>Woocommerce</strong>
      </div>

      <div style="float: left; width: 33%; ">
        SHIPPING METHOD:<br>
        <strong>{{$shipmentMethod}}</strong>
      </div>
    </div>
  </div>
</div>


<!--   <div style="height: 80px; clear: both;"></div> -->
<!-- Start second page for product Showing -->
<div style="width: 100%;">
  <div class="order_details" style="page-break-before:always;">
      <br />
      <div style="clear: both; width: 100%;">
      <div style="float: left;">
        <div style="clear: both;">
            <strong style="border-bottom: 1px solid #000000;">Shipment ID #{{$shipment_id}}</strong>
        </div>
        <div>
          <strong style="border-bottom: 1px solid #000000;">Order ID #{{$orderDetails->order_id}}</strong>
        </div>
      </div>
      <div style="width: 50%; float: right;">
        
     </div>
   </div>
   <br>
  <!--  @php
   $shipmentProducts = \App\Models\ShipmentProduct::getallShipmentsProductsByShopIdOrderID($shop_id,$orderDetails->order_id);
   @endphp -->
   @if (isset($getShipmentsProductsDetails))
   <div style="width: 100%; clear: both;">
     <div style="float: left; width: 100%;">
      <div class="full-card show" style="margin-top: 40px;">
        <table class="table table-responsive" style="font-size: 20px; width: 100%;">
          <thead class="thead-light">
            <tr style="background-color: #e9ecef; width: 100%;">
              <th style="text-align:center; padding: 5px;">Image</th>
              <th style="text-align: left; padding: 5px;">Product Details</th>

            </tr>
          </thead>
          <tbody class="table-body">
            @if (isset($getShipmentsProductsDetails))
            @foreach ($getShipmentsProductsDetails as $row)
            <tr style="margin-bottom: 20px; border-bottom:1px solid #000000;" class="new">

              <td style="padding: 5px; text-align:center;" valign="top">
                @php
                $images = [];
                if(!empty($row->images)){
                $images = json_decode($row->images);
              }
              @endphp

              @if(isset($images[0]->src))
              <img src="{{ $images[0]->src}}" alt="{{ $row->name }}" height="80" width="80" alt="" style="margin-top: 13px;" >
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

              @if(isset($row->attributes))
              @php $attributes_data = json_decode($row->attributes); @endphp

                  @if(isset($attributes_data))
                  @foreach($attributes_data as $value)
                  <strong>{{$value->name}} : {{$value->option}}</strong>
                  <br>
                  @endforeach
                  @endif
              @endif
              <strong>Price:</strong>
              @if (isset($row->price))
              <font class="thaiFont">฿</font>{{($row->price)}}
              @endif
              <br>
              <strong>Quantity: </strong>
              {{$row->shipped_qty}}
            </td>
          </tr>
          <tr><td colspan="2" style="height: 1px; width: 100%; margin-top: 5px; margin-bottom: 5px;"><hr></td></tr>
          @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif
</div>

<div style="width: 100%;  font-size: 20px; bottom: 0; height: 60px; display: block;">

  <div style="float: left; width: 33%; margin-top: 50px;">
    SHOP NAME:<br>
    <strong>{{$shopDetails->name}}</strong>
  </div>

  <div style="float: left; width: 33%; margin-top: 50px;">
    CHANNEL NAME:<br>
    <strong>Woocommerce</strong>
  </div>

  <div style="float: left; width: 33%; margin-top: 50px;">
    SHIPPING METHOD:<br>
    <strong>{{$shipmentMethod}}</strong>
  </div>

</div>
</div>
</body>
</html>


















