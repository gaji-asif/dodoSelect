<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name') }} - All Packages</title>
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

  <!-- Styles -->
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <!-- Scripts -->
  <script src="{{ asset('js/app.js') }}" defer></script>
  <style type="text/css">
    a:hover{
      text-decoration: none;
    }

    @media screen and (max-width: 600px) {
  .pricing_title{
    margin-top: 60px;
  }
}

.card-header h2{
    font-size: 22px;
    font-weight: bold !important;
  }

  .lead{
    font-size: 18px;
  }

  @media print {
    h1 {page-break-before: always;}
  }
   a:hover{
      text-decoration: none;
   }
   .qr_code_wrapper{
    padding: 15px;
    border: 1px solid gray;
    /*height: 122px;*/
  }
  .row{
    margin-bottom: 10px;
  }
  .margin_bottom_30px{
    margin-bottom: 30px;
  }
  .qr_code_new{
   float: right;
   max-height: 100px;
   width: auto;
  }
  </style>
</head>

<body class="font-sans antialiased">
  <div class="min-h-screen">
   @include('layouts.navigation')
<div class="container">
<div class="row mt-2 mb-2">
<input class="btn btn-success" style="margin: 0 auto; padding: 5px 10px;" type="button" onclick="printDiv('printableArea')" value="Print" />
</div>
<script type="text/javascript">
  function printDiv(divName) {
     var printContents = document.getElementById(divName).innerHTML;
     var originalContents = document.body.innerHTML;
     document.body.innerHTML = printContents;
     window.print();
     document.body.innerHTML = originalContents;
  }
</script>

 <div id="printableArea">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
@php $i = 0; @endphp
  @if(isset($products))
  @foreach($products as $key=>$product)
  <div style="width: 47%; float: left;">
    <div class="qr_code_wrapper" style="border: 1px solid #000000; padding: 5px 5px; height: 113px;">

      <div style="float: left; width: 20%; height: auto; margin-right: 2%;">
        @if(isset($product->image_url))
        <img width="100%" style="max-height: 100px; width: auto;" src="{{asset($product->image_url)}}">
        @else
        <img width="100%" style="max-height: 100px; width: auto;" src="{{asset('img/No_Image_Available.jpg')}}">
        @endif
      </div>
      <div style="float: left; width: 58%; font-size: 14px;">
         <strong>{{$product->product_name}}</strong> <br>
         <font style="font-weight: bold;">Code:</font> {{$product->product_code}} <br>
        <font style="font-weight: bold;">Pieces/Pack:</font> {{$product->pack}}
      </div>
      <div style="float: right; width: 20%;">
        <div class="qr_code_new">
          {!! QrCode::size(100)->generate(utf8_encode($product->product_code)); !!}
        </div>
      </div>
    </div>
  </div>
<?php

if($i==26 || $i==52 || $i==78){
  echo  '<div style="height: 2px; width: 100%; clear: both; margin-bottom: 15px; padding-bottom: 15px;"></div>';
 }
 ?>
@endforeach
@endif
</div>
</div>
</body>
</html>
