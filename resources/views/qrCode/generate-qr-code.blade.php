<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name') }} - All Packages</title>

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

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
  </style>
</head>

<body class="font-sans antialiased">
  <div class="min-h-screen bg-gray-100">


    <!--     // start navigation area -->

    <style type="text/css">
      a:hover{
        text-decoration: none;
      }
    </style>
    <nav x-data="{ mainOpen: false }" class=" bg-white border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
        <div class="relative flex items-center justify-between h-16">
          <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
            <!-- Mobile menu button-->
            <button x-on:click="mainOpen= !mainOpen" type="button"
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
            aria-controls="mobile-menu" aria-expanded="false">
            <span class="sr-only">Open main menu</span>
            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16" />
          </svg>

          <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
          stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <div class="flex-1 flex items-center justify-center sm:items-stretch sm:justify-start">
      <div class="flex-shrink-0 flex items-center">
        <img class="block lg:hidden h-8 w-auto" src="{{ asset('img/dodoselect.png') }}"
        alt="{{ config('app.name') }}">
        <img class="hidden lg:block h-8 w-auto" src="{{ asset('img/dodoselect.png') }}"
        alt="{{ config('app.name') }}">
      </div>

      <div class="hidden sm:block sm:ml-16">
        <div class="flex space-x-4">
            @if (Auth()->user()->role == 'member')

            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('translation.Dashboard') }}
            </x-nav-link>

            {{-- <x-nav-link :href="route('manage tracking')"
                :active="request()->routeIs('manage tracking')">
                {{ __('translation.Manage Tracking') }}
            </x-nav-link> --}}
            <x-nav-link :href="route('product')"
                :active="request()->routeIs('product')">
                {{ __('translation.Product') }}
            </x-nav-link>

            <x-nav-link :href="route('staff.manage')"
                :active="request()->routeIs('staff.manage')">
                {{ __('translation.Manage Staff') }}
            </x-nav-link>
            <x-nav-link :href="route('generate qr code')" :active="request()->routeIs('generate qr code')">
                {{ __('translation.Generate Qr Code') }}
            </x-nav-link>
            <x-nav-link :href="route('inout qr code')" :active="request()->routeIs('inout qr code')">
              {{ __('translation.In / Out') }}
          </x-nav-link>

        @endif
        @if (Auth()->user()->role == 'staff')
            <x-nav-link :href="route('staff dashboard')"
                :active="request()->routeIs('staff dashboard')">
                {{ __('translation.Dashboard') }}
            </x-nav-link>
            <x-nav-link :href="route('quantity update')"
                :active="request()->routeIs('quantity update')">
                {{ __('translation.Check-In / Check-Out') }}
            </x-nav-link>
            <x-nav-link :href="route('generate qr code')" :active="request()->routeIs('generate qr code')">
                {{ __('translation.Generate Qr Code') }}
            </x-nav-link>
            {{-- <x-nav-link :href="route('inout qr code')" :active="request()->routeIs('inout qr code')">
              {{ __('translation.In / Out') }}
          </x-nav-link> --}}
        @endif

        @if (Auth()->user()->role == 'admin')
            <x-nav-link :href="route('admin dashboard')"
                :active="request()->routeIs('admin dashboard')">
                {{ __('translation.Dashboard') }}
            </x-nav-link>

            <x-nav-link :href="route('manage seller')" :active="request()->routeIs('manage seller')">
                {{ __('translation.Manage seller') }}
            </x-nav-link>
            <x-nav-link :href="route('manage shipper')" :active="request()->routeIs('manage shipper')">
                {{ __('translation.Manage Shipper') }}
            </x-nav-link>
            <x-nav-link :href="route('package')" :active="request()->routeIs('manage seller')">
                {{ __('translation.Package') }}
            </x-nav-link>
            <x-nav-link :href="route('user logo')" :active="request()->routeIs('user logo')">
                {{ __('translation.User Logo') }}
            </x-nav-link>
        @endif

  </div>
</div>
</div>
<div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">

  <!-- Profile dropdown -->
  <div class="ml-3 relative" x-data="{ open : false }">
    <div>
      <button x-on:click="open = true" type="button"
      class="bg-gray-800 flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white"
      id="user-menu" aria-expanded="false" aria-haspopup="true">
      <span class="sr-only">Open user menu</span>
      <img class="h-8 w-8 rounded-full" src="{{ asset('img/male-avatar.svg') }}" alt="">
    </button>
  </div>

  <div x-show="open" x-on:click.away="open = false"
  class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
  role="menu" aria-orientation="vertical" aria-labelledby="user-menu"
  x-transition:enter="transition ease-out duration-100"
  x-transition:enter-start="transform opacity-0 scale-95"
  x-transition:enter-end="transform opacity-100 scale-100"
  x-transition:leave="transition ease-in duration-75"
  x-transition:leave-start="transform opacity-100 scale-100"
  x-transition:leave-end="transform opacity-0 scale-95">
  <a href="{{ route('profile') }}"
  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Your
Profile</a>

<a href="{{ route('your_packages') }}"
class="block px-4 py-2 text-sm text-gray-700" role="menuitem">Your
Packages</a>

<form method="POST" action="{{ route('logout') }}">
  @csrf
  <a onclick="event.preventDefault();
  this.closest('form').submit();" href=" {{ route('logout') }}"
  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Sign
out</a>
</form>
</div>
</div>
</div>
</div>
</div>

<!-- Mobile menu, show/hide based on menu state. -->
<div class="sm:hidden" id="mobile-menu">
  <div x-show="mainOpen" class=" px-2 pt-2 pb-3 space-y-1" x-transition:enter="transition ease-out duration-100"
  x-transition:enter-start="transform opacity-0 scale-95"
  x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
  x-transition:leave-start="transform opacity-100 scale-100"
  x-transition:leave-end="transform opacity-0 scale-95">
  @if (Auth()->user()->role == 'member')
  <x-mobile-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
      {{ __('translation.Dashboard') }}
  </x-mobile-nav-link>
  <x-mobile-nav-link :href="route('product')" :active="request()->routeIs('product')">
      {{ __('translation.Product') }}
  </x-mobile-nav-link>
  <x-mobile-nav-link :href="route('staff.manage')" :active="request()->routeIs('staff.manage')">
      {{ __('translation.Manage Staff') }}
  </x-mobile-nav-link>
  <x-mobile-nav-link :href="route('generate qr code')" :active="request()->routeIs('generate qr code')">
      {{ __('translation.Generate Qr Code') }}
  </x-mobile-nav-link>
  <x-mobile-nav-link :href="route('inout qr code')" :active="request()->routeIs('inout qr code')">
    {{ __('translation.In / Out') }}
</x-mobile-nav-link>
@endif
@if (Auth()->user()->role == 'admin')
  <x-mobile-nav-link :href="route('admin dashboard')" :active="request()->routeIs('admin dashboard')">
      {{ __('translation.Dashboard') }}
  </x-mobile-nav-link>
  <x-mobile-nav-link :href="route('manage seller')" :active="request()->routeIs('manage seller')">
      {{ __('translation.Manage seller') }}
  </x-mobile-nav-link>

  <x-mobile-nav-link :href="route('manage shipper')" :active="request()->routeIs('manage shipper')">
      {{ __('translation.Manage Shipper') }}
  </x-mobile-nav-link>

  <x-mobile-nav-link :href="route('package')" :active="request()->routeIs('manage seller')">
      {{ __('translation.Package') }}
  </x-mobile-nav-link>
  <x-mobile-nav-link :href="route('user logo')" :active="request()->routeIs('user logo')">
      {{ __('translation.User Logo') }}
  </x-mobile-nav-link>
@endif
@if (Auth()->user()->role == 'staff')
      <x-mobile-nav-link :href="route('staff dashboard')"
          :active="request()->routeIs('staff dashboard')">
          {{ __('translation.Dashboard') }}
      </x-mobile-nav-link>
      <x-mobile-nav-link :href="route('quantity update')"
          :active="request()->routeIs('quantity update')">
          {{ __('translation.Check-In / Check-Out') }}
      </x-mobile-nav-link>
      <x-mobile-nav-link :href="route('generate qr code')" :active="request()->routeIs('generate qr code')">
          {{ __('translation.Generate Qr Code') }}
      </x-mobile-nav-link>
      {{-- <x-mobile-nav-link :href="route('inout qr code')" :active="request()->routeIs('inout qr code')">
        {{ __('translation.In / Out') }}
    </x-mobile-nav-link> --}}

@endif
</div>
</div>
</nav>

<div class="container">
  <div class="row">
    <div class="col-lg-4 mt-5">


      <div class="card">

        <div class="card-body">
        <div class="card-title">
          <h4><strong>Add Product Code</strong></h4>
        </div>
            @if(session()->has('error'))
            <div class="alert alert-danger mb-3 background-danger" role="alert">
              {{ session()->get('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif
            @if(session()->has('success'))
            <div class="alert alert-success mb-3 background-success" role="alert">
              {{ session()->get('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif
            @if ($errors->any())
            <div class="alert alert-danger mb-3 background-danger" role="alert">
                <ul class=" list-disc list-inside text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
              </div>
            @endif
          <form method="POST" action="{{route('add product_code')}}" id="form-import" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
              <label for="exampleInputEmail1"><strong>Product Name</strong></label>
              <input type="text" name='product_name' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Product Name" required value="{{old('product_name')}}">
            </div>
            <div class="form-group">
              <label for="exampleInputEmail1"><strong>Product Code</strong></label>
              <input type="text" name='product_code' class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Product Code" required value="{{old('product_code')}}">
            </div>
            <div class="text-right">
              <button type="submit" class="btn btn-primary">Generate Qr Code </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-lg-8 mt-5">

      <div class="card">
        @if(session()->has('danger'))
        <div class="alert alert-danger mb-3 background-danger" role="alert">
          {{ session()->get('danger') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        @endif
        <div class="card-body">
          <div class="card-title">
            <h4><strong>List Of Product's Qr Codes</strong></h4>
          </div>
        <div class="">

          <div class="text-right">
            <form method="POST" action="{{route('print qr code')}}" id="form-import" enctype="multipart/form-data">
              @csrf
          <div class="pb-2">
            <button class="btn btn-success"style="margin-buttom: 5px;" type="submit" style="margin-right: 5px;">Print</button>
          </div>

          </div>
          <table class="table pt-1">
                <thead class="thead-light">
                  <tr style="background-color: #F7941E; color: #FFFFFF;">
                    <th><input type="checkbox" class="checkbox1" id="mother-checkbox"  style=" height: 17px; width: 17px; margin-top: 10px; padding-top: 10px !important;"></th>
                    <th>SL</th>
                    <th>Product Code</th>
                    <th>Qr Code</th>
                  </tr>
                </thead>
                <tbody>

                      @if (isset($product))
                        @foreach ($product as $key=>$item)

                          <tr>
                            <td><input type="checkbox" class="checkbox" name="product_code[]" value="{{$item->product_code}}" style=" height: 17px; width: 17px; margin-top: 10px; padding-top: 10px !important;"></td>
                            <td>{{++$key}}</td>
                            <td>{{$item->product_code}}</td>
                            <td> {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)->margin(2)->generate($item->product_code) !!}</td>
                          </tr>
                        @endforeach

                      @endif
                </form>

                </tbody>
              </table>
      </div>
    </div>
  </div>

</div>


</div>
<script type="text/javascript">

  $('#mother-checkbox').on('change',function(){
      if($(this)[0].checked)
      {
        $('.checkbox').each(function(){
           this.checked = true;
       });
      }
      else{
        $('.checkbox').each(function(){
           this.checked = false;
       });
      }
   });
  // $('#select_all').on('click',function(){

  //  });
  //  $('#clear_all').on('click',function(){

  //  });
</script>
</body>

</html>
