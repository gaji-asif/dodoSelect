<!DOCTYPE html>
<html lang="en">
<head>
  <title>CSS Template</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!--   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> -->


  <style>
table {
    border-left: 0.01em solid #ccc;
    border-right: 0;
    border-top: 0.01em solid #ccc;
    border-bottom: 0;
    border-collapse: collapse;
}
table td,
table th {
    border-left: 0;
    border-right: 0.01em solid #ccc;
    border-top: 0;
    border-bottom: 0.01em solid #ccc;
    padding: 10px;

}



/*table td{
  width: 40%;
}*/
  </style>

</head>
<body>
<div style="width: 580px;">
  <h2 style="text-align: center;">Product QR Codes</h2>
  <!-- <div style="width: 600px !important;"> -->

    @foreach($products as $product)
    {{-- <div class="col-2">
      <div class="p-3">
        <img src="{{asset('qrcodes/'.$product->product_code.'.svg')}}" alt="">
      </div>
    </div> --}}


  <!--     <div class="row">
        
          <div class="col-lg-4">asdadsasd</div>
          <div class="col-lg-4">asdadsasd</div>
          <div class="col-lg-4">asdadsasd</div>
        </div>
      </div> -->

      <!-- <div style="float: left; width: 200px; border: 1px solid #000000; height: auto;">
        asdasdadsasd asdasdadsasda
      </div> -->

      <table style="width:50%; margin-bottom: 10px;">

        <tr>
          <td valign="top"> 
            <img width="20%" src="{{asset($product->image)}}">
          </td>
          <td valign="top"> 
         <font style="font-weight: bold;">Name:</font> {{$product->product_name}}<br>
            <font style="font-weight: bold;">Code:</font> {{$product->product_code}}<br>
               <font style="font-weight: bold;">Pieces/pack:</font> {{$product->pack}} 
               
             </td>
          <td valign="top" style="text-align:right">
            <img alt="qr code" style="float: right;" width="20%" src="{{asset('qrcodes/'.$product->product_code.'.svg')}}">
          </td>
        </tr>
     
      </table>






      
        <!-- <div style="float: left; width: 200px;">
          <div style="padding: 15px; border: 1px solid #000000; height: 250px; float: left;">

            <div style="float: left; width: 70px;">
              <img width="70%" src="{{asset($product->image)}}">
            </div>
            <div style="float: left; width: 200px;">
              <p> <font style="font-weight: bold;">Name:</font> {{$product->product_name}}</p>
              <p> <font style="font-weight: bold;">Code:</font> {{$product->product_code}} </p>
              <p> <font style="font-weight: bold;">Pieces/pack:</font> {{$product->pack}} </p>
            </div>
            <div style="float: right; width: 30%;">
              <img style="float: right;" width="70%" src="{{asset('qrcodes/'.$product->product_code.'.svg')}}">
            </div>
          
      

          </div>
        </div> -->



        @endforeach
      </div>

        <!--    </div> -->
       

      </body>
      </html>

