<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name') }} - Payment Fail</title>

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  {{-- datatable --}}
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>


  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

  <!-- Styles -->
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <!-- Scripts -->
  <script src="{{ asset('js/app.js') }}" defer></script>

  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">


  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <link rel="stylesheet" href="{{ asset('css/custom_css.css') }}">
  <style type="text/css">
    .footer{
      position: fixed;
      bottom: 0;
    }
    .alert_wrong{
      margin-top: 40px;
    }
    .font-20{
      font-size: 25px;
      font-weight: bold;
    }
  </style>
  </head>

  <body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">

      <div class="container">
        <div class="row">
          <div class="col-lg-2 col-sm-12"></div>
          <div class="col-lg-8 col-sm-12">
           <div class="row" style="padding-top: 3%;">
            <div class="col-lg-4"></div>
            <div class="col-lg-4">
              <div class="flex justify-center" style="margin: 0 auto; padding-bottom: 2%;">
                @if (session()->has('userLogo'))
                <img class="logo_buyer" src="{{ asset(session()->get('userLogo')) }}  " alt="">
                @else
                <img class="logo_buyer" src="@if(isset($userLogo)) {{ asset($userLogo) }} @else {{ asset('img/dodoselect.png') }} @endif " alt="">
                @endif

              </div>
            </div>
          </div>

<div style="margin-top: 50px;" class="alert alert-danger text-center col-lg-12 mt-4 alert_wrong" role="alert">
  <i class='fas fa-exclamation-triangle mb-3' style='font-size:48px;color:black; text-align: center;'></i><br>
  <font class="font-20">Your Payment was unsuccessful.</font>
</div>

</div>

</div>
<div class="col-lg-2 col-sm-12"></div>
</div>

<div class="container-fluid footer">
  <div class="text-center p-3 footer_asif col-lg-12" style="background-color: rgba(0, 0, 0, 0.2);">
    Powered By
    <a class="text-dark" href="https://dodoselect.com/">Dodoselect.com</a>
  </div>
</div>



</body>

</html>
