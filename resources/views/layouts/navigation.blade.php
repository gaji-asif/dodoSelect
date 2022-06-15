<style type="text/css">
    a:hover {
        text-decoration: none;
    }
    ul {
        padding-left: 0px;
        list-style-type: none;
    }
</style>
<nav x-data="{ mainOpen: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
        <div class="relative flex items-center justify-between h-16">
            <div class="absolute inset-y-0 left-0 flex items-center lg:hidden">
                <!-- Mobile menu button-->
                <button x-on:click="mainOpen= !mainOpen" type="button" class="bg-white border-0 inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 outline-none focus:outline-none" aria-controls="mobile-menu" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>

                    <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 flex items-center justify-center lg:items-stretch lg:justify-start">
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{route('dashboard')}}">
                        <img class="h-8 w-auto" src="{{ asset('img/dodoselect.png') }}" alt="{{ config('app.name') }}">
                    </a>
                </div>

                <div class="hidden lg:block sm:ml-16">
                    <ul class="flex space-x-4 mb-0">
                        @if (Auth()->user()->role == 'member' || Auth()->user()->role == 'staff')
                            <x-nav-item>
                                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                    {{ __('translation.Dashboard') }}
                                </x-nav-link>
                            </x-nav-item>

                            {{-- <x-nav-item>
                                <x-nav-link :href="route('manage tracking')"
                                    :active="request()->routeIs('manage tracking')">
                                    {{ __('translation.Manage Tracking') }}
                                </x-nav-link>
                            </x-nav-item> --}}

                            <x-nav-item>
                                <x-nav-link :href="route('product')" :active="request()->routeIs('product') OR request()->routeIs('seller quantity details')">
                                    {{ __('translation.Product') }}
                                </x-nav-link>
                            </x-nav-item>

                            {{-- <x-nav-item>
                                <x-nav-link :href="route('staff.manage')"
                                    :active="request()->routeIs('staff.manage')">
                                    {{ __('translation.Manage Staff') }}
                                </x-nav-link>
                            </x-nav-item> --}}

                            {{-- <x-nav-item>
                                <x-nav-link :href="route('generate qr code')" :active="request()->routeIs('generate qr code')">
                                    {{ __('translation.Genereate Qr Code') }}
                                </x-nav-link>
                            </x-nav-item> --}}

                            <x-nav-item x-data="{ subMenuOpen: false }">
                                <x-nav-link :active="request()->routeIs('inout qr code') OR request()->routeIs('in-out-history') OR request()->routeIs('defect-stock')" x-on:click="subMenuOpen = !subMenuOpen">
                                    {{ __('translation.Stock Adjust') }}
                                    <span class="ml-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </x-nav-link>

                                <ul class="absolute z-30 bg-white shadow-lg rounded-md mt-1 py-1" x-show="subMenuOpen" x-on:click.away="subMenuOpen = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" x-cloak>
                                    <x-nav-item>
                                        <x-subnav-link :href="route('inout qr code')" :active="request()->routeIs('inout qr code')">
                                            {{ __('translation.Adjustment') }}
                                        </x-subnav-link>
                                    </x-nav-item>
                                    <x-nav-item>
                                        <x-subnav-link :href="route('in-out-history')" :active="request()->routeIs('in-out-history')">
                                            {{ __('translation.History') }}
                                        </x-subnav-link>
                                    </x-nav-item>
                                    <x-nav-item>
                                        <x-subnav-link :href="route('defect-stock')" :active="request()->routeIs('defect-stock')">
                                            {{ __('translation.Defect Stock') }}
                                        </x-subnav-link>
                                    </x-nav-item>
                                </ul>
                            </x-nav-item>

                            <x-nav-item x-data="{ subMenuOpen: false }">
                                <x-nav-link :active="request()->is('order_purchase*') OR request()->routeIs('cost_analysis')" x-on:click="subMenuOpen = !subMenuOpen">
                                    {{ __('translation.Purchase Order') }}
                                    <span class="ml-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </x-nav-link>

                                <ul class="absolute z-30 bg-white shadow-lg rounded-md mt-1 py-1" x-show="subMenuOpen" x-on:click.away="subMenuOpen = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" x-cloak>
                                    <x-nav-item>
                                        <x-subnav-link :href="route('order_purchase.index')" :active="request()->is('order_purchase*')">
                                            {{ __('translation.Purchase Order') }}
                                        </x-subnav-link>
                                    </x-nav-item>
                                    <x-nav-item>
                                        <x-subnav-link :href="route('product_cost')" :active="request()->routeIs('order_purchase*')">
                                            {{ __('translation.Cost Analysis') }}
                                        </x-subnav-link>
                                    </x-nav-item>
                                </ul>
                            </x-nav-item>

                            <x-nav-item>
                                <x-nav-link :href="route('order_management.index')" :active="request()->routeIs('order_management.index')">
                                    Order Management
                                </x-nav-link>
                            </x-nav-item>

                            <x-nav-item>
                                <x-nav-link :href="route('manage shipper')" :active="request()->routeIs('manage shipper')">
                                    {{ __('translation.Manage Shipper') }}
                                </x-nav-link>
                            </x-nav-item>

                            <x-nav-item x-data="{ subMenuOpen: false }">
                                <x-nav-link :active="request()->routeIs('product_report') OR request()->routeIs('stock_movement_report')" x-on:click="subMenuOpen = !subMenuOpen">
                                    {{ __('translation.Report') }}
                                    <span class="ml-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </x-nav-link>

                                <ul class="absolute z-30 bg-white shadow-lg rounded-md mt-1 py-1" x-show="subMenuOpen" x-on:click.away="subMenuOpen = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" x-cloak>
                                    <x-nav-item>
                                        <x-subnav-link :href="route('product_report')" :active="request()->routeIs('product_report')">
                                            {{ __('translation.Stock Report') }}
                                        </x-subnav-link>
                                    </x-nav-item>
                                    <x-nav-item>
                                        <x-subnav-link :href="route('stock_movement_report')" :active="request()->routeIs('stock_movement_report')">
                                            {{ __('translation.Stock Movements') }}
                                        </x-subnav-link>
                                    </x-nav-item>
                                </ul>
                            </x-nav-item>

                            <x-nav-item x-data="{ subMenuOpen: false }">
                                <x-nav-link :active="request()->routeIs('staff.manage') OR request()->routeIs('staff.permissions')" x-on:click="subMenuOpen = !subMenuOpen">
                                    {{ __('translation.Manage Users') }}
                                    <span class="ml-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </x-nav-link>

                                <ul class="absolute z-30 bg-white shadow-lg rounded-md mt-1 py-1" x-show="subMenuOpen" x-on:click.away="subMenuOpen = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" x-cloak>
                                    <x-nav-item>
                                        <x-subnav-link :href="route('staff.manage')" :active="request()->routeIs('staff.manage')">
                                            {{ __('translation.Users') }}
                                        </x-subnav-link>
                                    </x-nav-item>

                                    <x-nav-item>
                                        <x-subnav-link :href="route('staff.permissions')" :active="request()->routeIs('staff.permissions')">
                                            {{ __('translation.Permissions') }}
                                        </x-subnav-link>
                                    </x-nav-item>
                                </ul>
                            </x-nav-item>

                        @endif

                        @if (Auth()->user()->role == 'woo')
                            <x-nav-item>
                                <x-nav-link :href="route('staff dashboard')" :active="request()->routeIs('staff dashboard')">
                                    {{ __('translation.Dashboard') }}
                                </x-nav-link>
                            </x-nav-item>

                            <x-nav-item>
                                <x-nav-link :href="route('woocommerce')" :active="request()->routeIs('inout qr code')">
                                    {{ __('translation.woocommerce') }}
                                </x-nav-link>
                            </x-nav-item>
                        @endif

                        @if (Auth()->user()->role == 'admin')
                            <x-nav-item>
                                <x-nav-link :href="route('admin dashboard')" :active="request()->routeIs('admin dashboard')">
                                    {{ __('translation.Dashboard') }}
                                </x-nav-link>
                            </x-nav-item>

                            <x-nav-item>
                                <x-nav-link :href="route('manage seller')" :active="request()->routeIs('manage seller')">
                                    {{ __('translation.Manage seller') }}
                                </x-nav-link>
                            </x-nav-item>

                            <x-nav-item>
                                <x-nav-link :href="route('package')" :active="request()->routeIs('manage seller')">
                                    {{ __('translation.Package') }}
                                </x-nav-link>
                            </x-nav-item>

                            <x-nav-item>
                                <x-nav-link :href="route('user logo')" :active="request()->routeIs('user logo')">
                                    {{ __('translation.User Logo') }}
                                </x-nav-link>
                            </x-nav-item>
                        @endif

                    </ul>
                </div>
            </div>
            <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">

                <!-- Profile dropdown -->
                <div class="ml-3 relative" x-data="{ open : false }">
                    <div>
                        <button style="float: left;" x-on:click="open = true" type="button" class="bg-transparent border-0 flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2" id="user-menu" aria-expanded="false" aria-haspopup="true">
                            <span class="sr-only">Open user menu</span>
                            <span>
                                @if(!empty(Auth()->user()->logo))
                                    <img class="h-8 w-8 rounded-ful" src="{{asset(Auth()->user()->logo)}}" alt="" style="border-radius: 16px;">
                                @else
                                    <img class="h-8 w-8 rounded-full" src="{{ asset('img/male-avatar.svg') }}" alt="">
                                @endif
                            </span>
                            <span class="hidden xl:block" style="float: left; margin-left: 8px; margin-top: 4px;">
                                {{ Auth()->user()->name }}
                            </span>
                        </button>
                    </div>

                    <div x-show="open" x-on:click.away="open = false" class="origin-top-right absolute top-11 right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white outline-none focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" style="z-index: 999;" x-cloak>
                        <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                            Your Profile
                        </a>
                    <!--  @if (Auth::user()->role == 'member')
                        <a href="{{ route('your_packages') }}" class="block px-4 py-2 text-sm text-gray-700" role="menuitem">Your Packages</a>
                        @endif -->
                        @if (Auth::user()->role == 'member')
                            <a href="{{ route('staff.manage') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                Manage Staff
                            </a>
                        @endif

                        <a href="{{ route('categories.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                            Settings
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a onclick="event.preventDefault(); this.closest('form').submit();" href=" {{ route('logout') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                Sign out
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div class="block lg:hidden" id="mobile-menu" x-cloak>
        <ul x-show="mainOpen" class="px-2 pt-2 pb-3 space-y-1" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95">

            @if (Auth()->user()->role == 'member' || Auth()->user()->role == 'staff')
                <x-mobile-nav-item>
                    <x-mobile-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('translation.Dashboard') }}
                    </x-mobile-nav-link>
                </x-mobile-nav-item>

                <x-mobile-nav-item>
                    <x-mobile-nav-link :href="route('product')" :active="request()->routeIs('product') OR request()->routeIs('seller quantity details')">
                        {{ __('translation.Product') }}
                    </x-mobile-nav-link>
                </x-mobile-nav-item>

                {{-- <x-mobile-nav-item>
                    <x-mobile-nav-link :href="route('generate qr code')" :active="request()->routeIs('generate qr code')">
                        {{ __('translation.generate Qr Code') }}
                    </x-mobile-nav-link>
                </x-mobile-nav-item> --}}

                <x-mobile-nav-item x-data="{ subMenuOpen: false }">
                    <x-mobile-nav-link x-on:click="subMenuOpen = !subMenuOpen" :active="request()->routeIs('inout qr code') OR request()->routeIs('in-out-history') OR request()->routeIs('defect-stock')" :hasSubMenu="true">
                        {{ __('translation.Stock Adjust') }}
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rotate-90--mobile" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </x-mobile-nav-link>

                    <ul class="w-11/12 mx-auto" x-show="subMenuOpen" x-cloak>
                        <x-mobile-nav-item>
                            <x-mobile-nav-link :href="route('inout qr code')" :active="request()->routeIs('inout qr code')">
                                {{ __('translation.Adjustment') }}
                            </x-mobile-nav-link>
                        </x-mobile-nav-item>
                        <x-mobile-nav-item>
                            <x-mobile-nav-link :href="route('in-out-history')" :active="request()->routeIs('in-out-history')">
                                {{ __('translation.History') }}
                            </x-mobile-nav-link>
                        </x-mobile-nav-item>
                        <x-mobile-nav-item>
                            <x-mobile-nav-link :href="route('defect-stock')" :active="request()->routeIs('defect-stock')">
                                {{ __('translation.Defect Stock') }}
                            </x-mobile-nav-link>
                        </x-mobile-nav-item>
                    </ul>
                </x-mobile-nav-item>

                <x-mobile-nav-item x-data="{ subMenuOpen: false }">
                    <x-mobile-nav-link x-on:click="subMenuOpen = !subMenuOpen" :active="request()->is('order_purchase*') OR request()->routeIs('product_cost')" :hasSubMenu="true">
                        {{ __('translation.Purchase Order') }}
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rotate-90--mobile" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </x-mobile-nav-link>

                    <ul class="w-11/12 mx-auto" x-show="subMenuOpen" x-cloak>
                        <x-mobile-nav-item>
                            <x-mobile-nav-link :href="route('order_purchase.index')" :active="request()->is('order_purchase*')">
                                Purchase Order
                            </x-mobile-nav-link>
                        </x-mobile-nav-item>
                        <x-mobile-nav-item>
                            <x-mobile-nav-link :href="route('product_cost')" :active="request()->routeIs('product_cost')">
                            Cost Analysis
                            </x-mobile-nav-link>
                        </x-mobile-nav-item>

                        <x-mobile-nav-item>
                            <x-mobile-nav-link :href="route('order_analysis')" :active="request()->routeIs('order_analysis')">
                            Order Analysis
                            </x-mobile-nav-link>
                        </x-mobile-nav-item>

                    </ul>
                </x-mobile-nav-item>

                <x-mobile-nav-item>
                    <x-mobile-nav-link :href="route('order_management.index')" :active="request()->routeIs('order_management.index')">
                        Order Management
                    </x-mobile-nav-link>
                </x-mobile-nav-item>

                <x-mobile-nav-item>
                    <x-mobile-nav-link :href="route('manage shipper')" :active="request()->routeIs('manage shipper')">
                        {{ __('translation.Manage Shipper') }}
                    </x-mobile-nav-link>
                </x-mobile-nav-item>

                <x-mobile-nav-item x-data="{ subMenuOpen: false }">
                    <x-mobile-nav-link x-on:click="subMenuOpen = !subMenuOpen" :active="request()->routeIs('product_report') OR request()->routeIs('stock_movement_report')" :hasSubMenu="true">
                        {{ __('translation.Report') }}
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 rotate-90--mobile" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </x-mobile-nav-link>

                    <ul class="w-11/12 mx-auto" x-show="subMenuOpen" x-cloak>
                        <x-mobile-nav-item>
                            <x-mobile-nav-link :href="route('product_report')" :active="request()->routeIs('product_report')">
                                {{ __('translation.Stock Report') }}
                            </x-mobile-nav-link>
                        </x-mobile-nav-item>
                        <x-mobile-nav-item>
                            <x-mobile-nav-link :href="route('stock_movement_report')" :active="request()->routeIs('stock_movement_report')">
                                {{ __('translation.Stock Movements') }}
                            </x-mobile-nav-link>
                        </x-mobile-nav-item>
                    </ul>
                </x-mobile-nav-item>
            @endif

            @if (Auth()->user()->role == 'admin')
                <x-mobile-nav-item>
                    <x-mobile-nav-link :href="route('admin dashboard')" :active="request()->routeIs('admin dashboard')">
                        {{ __('translation.Dashboard') }}
                    </x-mobile-nav-link>
                </x-mobile-nav-item>

                <x-mobile-nav-item>
                    <x-mobile-nav-link :href="route('manage seller')" :active="request()->routeIs('manage seller')">
                        {{ __('translation.Manage seller') }}
                    </x-mobile-nav-link>
                </x-mobile-nav-item>

                <x-mobile-nav-item>
                    <x-mobile-nav-link :href="route('package')" :active="request()->routeIs('manage seller')">
                        {{ __('translation.Package') }}
                    </x-mobile-nav-link>
                </x-mobile-nav-item>

                <x-mobile-nav-item>
                    <x-mobile-nav-link :href="route('user logo')" :active="request()->routeIs('user logo')">
                        {{ __('translation.User Logo') }}
                    </x-mobile-nav-link>
                </x-mobile-nav-item>
            @endif

            {{-- @if (Auth()->user()->role == 'staff')
                <x-mobile-nav-item>
                    <x-mobile-nav-link :href="route('staff dashboard')"
                    :active="request()->routeIs('staff dashboard')">
                        {{ __('translation.Dashboard') }}
                    </x-mobile-nav-link>
                </x-mobile-nav-item>

                <x-mobile-nav-item>
                    <x-mobile-nav-link :href="route('quantity update')" :active="request()->routeIs('quantity update')">
                        {{ __('translation.Check-In / Check-Out') }}
                    </x-mobile-nav-link>
                </x-mobile-nav-item>

                <x-mobile-nav-item>
                    <x-mobile-nav-link :href="route('generate qr code')" :active="request()->routeIs('generate qr code')">
                        {{ __('translation.Generate Qr Code') }}
                    </x-mobile-nav-link>
                </x-mobile-nav-item>

                <x-mobile-nav-item>
                    <x-mobile-nav-link :href="route('inout qr code')" :active="request()->routeIs('inout qr code')">
                        {{ __('translation.Stock Adjust') }}
                    </x-mobile-nav-link>
                </x-mobile-nav-item>
            @endif --}}

        </ul>
    </div>
</nav>
