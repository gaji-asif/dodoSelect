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

          <x-nav-link :href="route('manage tracking')"
          :active="request()->routeIs('manage tracking')">
          {{ __('translation.Manage Tracking') }}
        </x-nav-link>

        <x-nav-link :href="route('track page')"
        :active="request()->routeIs('track page')">
        {{ __('translation.Tracking') }}
      </x-nav-link>

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
  <x-mobile-nav-link :href="route('manage tracking')" :active="request()->routeIs('manage tracking')">
    {{ __('translation.Manage Tracking') }}
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
  @endif
</div>
</div>
</nav>

<div class="container">

  <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
     <h1 class="pricing_title" style="font-size: 35px; font-weight: bold;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
      Pricing List


</font></font></h1>


    <p class="lead" style="margin-top: 10px; margin-bottom: 25px;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
      From Our Pricing plan, you can choose any of them
    </font></font></p>
  </div>
    <div class="card-deck mb-3 text-center">

      @foreach ($packages as $package)

      <div class="card mb-4 shadow-sm col-xs-12">
      <div class="card-header">
        <h2 class="my-0 font-weight-normal">
          {{$package->package_name}}
        </h2>
      </div>

      <div class="card-body">
        <h2 class="pricing-card-title" style="font-size: 28px;">
          ${{$package->price}} <small class='text-muted'>/ @if ($package->package_type == 1)
          Daily
          @else
          Monthly
        @endif</small>
        </h2>


        <ul class="list-unstyled mt-3 mb-4">
          <li>Total Tracking - {{$package->max_limit}}</li>

        </ul>
        <button type="button" class="btn btn-lg btn-block btn-primary">Get started</button>
      </div>


    </div>
 @endforeach

  </div>

</div>


</div>
</body>

</html>
