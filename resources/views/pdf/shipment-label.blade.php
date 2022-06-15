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
    html{
      height: 100%;
    }
  </style>

  <div style="float: left;">
    <strong>Shipment ID #{{ $orderManagement->shipment->id }}</strong>
  </div>
  <div style="float: right;">
    <strong>Order ID #{{ $orderManagement->id }}</strong>
  </div>
  <br>


    <div style="clear: both; width: 100%; margin-bottom: 20px;">
    <div style="float: left; width: 80%;">

      <div class="card shipping_address_wraps mb-3" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 10px; border-radius: 5px; border: 1px solid #000000; margin-top: 20px;">
          <p class="">
            <strong>From</strong>
          </p>
          @if(empty($orderManagement->shop->name) && empty($orderManagement->shop->phone))
          <div class="form-group" id="no_selected1">
            No Address Selected Yet
          </div>
          @endif

          @if(isset($orderManagement->shop->name))
          <font id="shipping_name_div1" class="font_weight thaiFont">
            {{$orderManagement->shop->name}}
          </font>
          <br>
          @endif
          <font>
            @if(isset($orderManagement->shop->address))
            <span id="" class="thaiFont">{{$orderManagement->shop->address}}</span>
            @endif
           
          </font><br>

          <font>
            @if(isset($orderManagement->shop->district))
            <span id="dis1" class="thaiFont">{{$orderManagement->shop->district}},</span>
            @endif
            @if(isset($orderManagement->shop->sub_district))
            <span id="sub1" class="thaiFont">{{$orderManagement->shop->sub_district}}</span>
            @endif
          </font><br>
          <font class="thaiFont">
            @if(isset($orderManagement->shop->province))
            <span id="pro1">{{$orderManagement->shop->province}}</span>
            @endif
            @if(isset($orderManagement->shop->postcode))
            <span id="postcodes" class="thaiFont">{{$orderManagement->shop->postcode}}</span>@endif
          </font><br><br>
          <font class="font_weight" id="shipping_phone_div1">
            @if(isset($orderManagement->shop->phone))
            {{$orderManagement->shop->phone}}
            @endif
          </font>
        </div> 
      </div>

    </div>
    <div style="float: left; width: 19%; margin-top: 30px;">
      <img style="float: right;" src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(100)->generate($orderManagement->shipment->id)) !!} ">
    </div>
  </div>
<div style="width: 100%; clear: both;">
  <div style="padding: 10px; border-radius: 5px; border: 1px solid #000000;  margin-bottom: 15px; font-size: 37px;">
        <p class="">
          <strong>To</strong>
        </p>
        @if(empty($orderManagement->shipping_name) && empty($orderManagement->shipping_name))
        <div class="form-group" id="no_selected">
          No Address Selected Yet
        </div>
        @endif

        <font id="shipping_name_div" class="font_weight">
          @if(isset($orderManagement->shipping_name))
          {{$orderManagement->shipping_name}}
          @endif
        </font><br>
         <font>
            @if(isset($orderManagement->shipping_address))
            <span id="" class="thaiFont">{{$orderManagement->shipping_address}}</span>
            @endif
           
          </font><br>
        <font>

          <span id="dis" class="thaiFont">
            @if(isset($orderManagement->shipping_district)){{$orderManagement->shipping_district}},
            @endif
          </span>

          <span id="sub" class="thaiFont">
            @if(isset($orderManagement->shipping_sub_district)){{$orderManagement->shipping_sub_district}}
            @endif
          </span>
        </font><br>
        <font class="thaiFont">
          <span id="pro">
            @if(isset($orderManagement->shipping_province)){{$orderManagement->shipping_province}}
            @endif
          </span>
          <span id="postcodes" class="thaiFont">
            @if(isset($orderManagement->shipping_postcode)){{$orderManagement->shipping_postcode}}
            @endif
          </span>
        </font><br><br>
        <font id="shipping_phone_div" class="font_weight">
          @if(isset($orderManagement->shipping_phone))
          {{$orderManagement->shipping_phone}}
          @endif
        </font>
      </div>
      <div style="width: 100%; clear: both; font-size: 20px; margin-top: 50px; position: absolute; bottom: 0; height: 60px;">
        <div style="float: left; width: 33%">
          SHOP NAME:<br>
          <strong>{{$shopName}}</strong>
        </div>

        <div style="float: left; width: 33%">
          CHANNEL NAME:<br>
          <strong>{{$channelName}}</strong>
        </div>

        <div style="float: left; width: 33%">
          SHIPPING METHOD:<br>
          <strong>{{$shippingMethod}}</strong>
        </div>
      </div> 
</div>
<div style="height: 80px; clear: both;"></div>

 <div style="clear: both; width: 100%; page-break-before: always;">
  <div class="order_details">

    <div style="clear: both; width: 100%;">
      <div style="float: left;">
        <div style="border-bottom: 1px solid #000000;">
          <strong>Shipment ID #{{ $orderManagement->shipment->id }}</strong>
        </div>
       <div style="border-bottom: 1px solid #000000;">
          <strong>Order ID #{{ $orderManagement->id }}</strong>
      </div>
      </div>
      <div style="width: 50%; float: right;">
       <img style="float: right;" src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(100)->generate($orderManagement->shipment->id)) !!} ">
      </div>
    </div>
    <br>

    @if (isset($getShipmentsProductsDetails))
    <div style="width: 100%; clear: both;">
     <div style="float: left; width: 100%;">
    <div class="full-card show" style="margin-top: 40px;">
      <table class="table table-responsive" style="font-size: 20px; width: 100%;">
        <thead class="thead-light">
          <tr style="background-color: #e9ecef;">
            <th style="text-align: left; float: center;  padding: 5px;">Image</th>
            <th style="text-align: left; float: left;   padding: 5px;">Product Details</th>

          </tr>
        </thead>
        <tbody class="table-body">
          @if (isset($getShipmentsProductsDetails))
          @foreach ($getShipmentsProductsDetails as $key=>$row)
          <tr style="margin-bottom: 20px; border-bottom:1px solid #000000;" class="new">
            <td style="padding: 5px;" valign="top">
              @if (Storage::disk('s3')->exists($row->image) && !empty($row->image))
              <img src="{{Storage::disk('s3')->url($row->image)}}" height="80" width="80" alt="" style="margin-top: 13px;" >
              @else
              <img src="{{Storage::disk('s3')->url('uploads/No-Image-Found.png')}}" height="80" width="80"  alt="" style="margin-top: 13px;" >
              @endif
            </td>
            <td style=" padding-left: 20px;  padding: 5px; margin-left: 10px;">
              <font class="thaiFont">@if (isset($row->product_name))
                {{($row->product_name)}}
                @endif
              </font>
              <br>
              <strong>Code:</strong>
              @if (isset($row->product_code))
              {{($row->product_code)}}
              @endif
              <br>
            
              <strong>Quantity: </strong>
              
              @if (isset($row->shipped_qty))
              {{($row->shipped_qty)}}
              @endif
            </td>
          </tr> 
          <tr><td colspan="2" style="height: 1px; width: 100%; margin-top: 5px; margin-bottom: 5px;"><hr></td></tr> 
          @endforeach
          @endif
        </tbody>

      </table>

    </div>
  </div>
  
  <div style="width: 100%; clear: both; font-size: 20px; margin-top: 50px; bottom: 0; height: 60px;">
        <div style="float: left; width: 33%; margin-top: 50px;">
          SHOP NAME:<br>
          <strong>{{$shopName}}</strong>
        </div>

        <div style="float: left; width: 33%; margin-top: 50px;">
          CHANNEL NAME:<br>
          <strong>{{$channelName}}</strong>
        </div>

        <div style="float: left; width: 33%; margin-top: 50px;">
          SHIPPING METHOD:<br>
          <strong>{{$shippingMethod}}</strong>
        </div>
      </div> 
</div>
    @endif
  </div>
</div>

