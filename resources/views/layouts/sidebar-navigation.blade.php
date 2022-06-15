<header class="w-full xl:fixed top-0 left-0 bg-white xl:bg-transparent xl:pointer-events-none shadow-md xl:shadow-none z-20">
    <nav class="w-full 2xl:max-w-7xl 2xl:mx-auto flex flex-row xl:flex-col items-center xl:items-start justify-between" x-data="{ sidebarOpen: false }">
        <div class="w-full h-full fixed inset-0 z-20 transition-opacity duration-300 opacity-0 pointer-events-none"
             :class="{ '': sidebarOpen === true, 'opacity-0 pointer-events-none': sidebarOpen === false }"
             x-on:click="sidebarOpen = false">
            <div class="absolute w-full h-full bg-gray-900 bg-opacity-50 xl:bg-opacity-0 z-30"></div>
        </div>
        <div class="transform -translate-x-full xl:translate-x-0 ease-in-out transition-all duration-300 fixed top-0 left-0 2xl:left-auto xl:mt-32 2xl:mx-auto pb-20 w-3/5 sm:w-60 2xl:w-full 2xl:max-w-7xl h-full bg-white 2xl:bg-transparent z-40 xl:z-10 2xl:pointer-events-none"
             :class="{ 'left-sidebar--open' : sidebarOpen === true }">
            <div class="w-full 2xl:w-60 bg-transparent 2xl:bg-white 2xl:rounded-md 2xl:shadow 2xl:pointer-events-auto py-4 xl:py-5">
                <div class="mb-6 block xl:hidden">
                    <div class="w-full h-full flex items-center justify-center">
                        @if(session('roleName') == 'dropshipper')
                            <a href="{{ route('order_management.index') }}">
                                <img src="{{ asset('img/dodoselect.png') }}" alt="{{ config('app.name') }}" class="h-9 xl:h-10 w-auto">
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}">
                                <img src="{{ asset('img/dodoselect.png') }}" alt="{{ config('app.name') }}" class="h-9 xl:h-10 w-auto">
                            </a>
                        @endif
                    </div>
                </div>
                <div class="w-full xl:pointer-events-auto" id="__sidebarNavigationWrapper">
                    <div class="px-3">
                        <x-sidebar.nav-wrapper>

                            @if (Auth()->user()->role == 'member' || Auth()->user()->role == 'staff' || Auth()->user()->role == 'dropshipper')
                                <div class="p-2 mb-1 bg-gray-200 bg-opacity-80 rounded-md">
                                    @if (in_array('Can access menu: Product', session('assignedPermissions')))
                                        <x-sidebar.nav-item :active="request()->routeIs('product') OR request()->routeIs('product.show') OR request()->routeIs('seller quantity details') OR request()->routeIs('product.inventory_sync')">
                                            <x-sidebar.nav-link href="{{ route('product') }}" :active="request()->routeIs('product') OR request()->routeIs('product.show') OR request()->routeIs('seller quantity details') OR request()->routeIs('product.inventory_sync')">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-4 h-4 bi bi-archive" viewBox="0 0 16 16">
                                                    <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1V2zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5H2zm13-3H1v2h14V2zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                                                </svg>
                                                <span class="ml-2 relative top-[0.05rem]">
                                                    Catalog
                                                </span>
                                            </x-sidebar.nav-link>
                                        </x-sidebar.nav-item>
                                    @endif

                                    @if (in_array('Can access menu: Order Management', session('assignedPermissions')) || in_array('Can access menu: Shopee - Order', session('assignedPermissions')) OR in_array('Can access menu: Lazada - Order', session('assignedPermissions')))
                                        <x-sidebar.nav-item :active="request()->is('order_management*') OR request()->is('wc-order-purchase*') OR request()->routeIs('shopee.order.index') OR request()->routeIs('lazada.order.index')" x-data="{ subnavOpen: false }">
                                            <x-sidebar.nav-link href="#" :active="request()->is('order_management*') OR request()->is('wc-order-purchase*') OR request()->routeIs('shopee.order.index') OR request()->routeIs('lazada.order.index')" x-on:click="subnavOpen = !subnavOpen">
                                                <i class="bi bi-bounding-box"></i>
                                                <span class="ml-2 relative top-[0.05rem]">
                                                    {{ ucwords(__('translation.orders')) }}
                                                </span>
                                                <span class="absolute left-auto right-4">
                                                    <i class="bi bi-caret-down text-xs"></i>
                                                </span>
                                            </x-sidebar.nav-link>
                                            <x-sidebar.subnav-wrapper :active="request()->is('order_management*') OR request()->is('wc-order-purchase*') OR request()->routeIs('shopee.order.index') OR request()->routeIs('lazada.order.index')" x-show="subnavOpen">

                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('order_management.index') }}" :active="request()->is('order_management*')">
                                                        {{ ucwords(__('translation.dodo')) }}
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('wc-order-purchase.index') }}" :active="request()->is('wc-order-purchase*')">
                                                        {{ ucwords(__('translation.website')) }}
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>

                                                @if (in_array('Can access menu: Shopee - Order', session('assignedPermissions')) OR in_array('Can access menu: Lazada - Order', session('assignedPermissions')))
                                                    <x-sidebar.subnav-item>
                                                        <x-sidebar.subnav-link href="{{ route('shopee.order.index') }}" :active="request()->routeIs('shopee.order.index') OR request()->routeIs('lazada.order.index')">
                                                            {{ ucwords(__('translation.marketplace')) }}
                                                        </x-sidebar.subnav-link>
                                                    </x-sidebar.subnav-item>
                                                @endif

                                            </x-sidebar.subnav-wrapper>
                                        </x-sidebar.nav-item>
                                    @endif

                                    @if (in_array('Can access menu: All Shipment', session('assignedPermissions')))
                                        <x-sidebar.nav-item :active="request()->is('all_shipment*')">
                                            <x-sidebar.nav-link href="{{ route('all_shipment_index', 1) }}" :active="request()->is('all_shipment*')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16">
                                                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                                                </svg>
                                                <span class="ml-2 relative top-[0.05rem]">
                                                    Shipments
                                                </span>
                                            </x-sidebar.nav-link>
                                        </x-sidebar.nav-item>
                                    @endif
                                </div>

                                @if (in_array('Can access menu: Stock Adjust - Adjustment', session('assignedPermissions')) || in_array('Can access menu: Stock Adjust - History', session('assignedPermissions')) || in_array('Can access menu: Stock Adjust - Defect Stock', session('assignedPermissions')))
                                    <x-sidebar.nav-item :active="request()->routeIs('inout qr code') OR request()->routeIs('in-out-history') OR request()->is('defect-stock*')" x-data="{ subnavOpen: false }">
                                        <x-sidebar.nav-link href="#" :active="request()->routeIs('inout qr code') OR request()->routeIs('in-out-history') OR request()->is('defect-stock*')" x-on:click="subnavOpen = !subnavOpen">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-4 h-4 bi bi-arrow-down-up" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M11.5 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L11 2.707V14.5a.5.5 0 0 0 .5.5zm-7-14a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L4 13.293V1.5a.5.5 0 0 1 .5-.5z"/>
                                            </svg>
                                            <span class="ml-2 relative top-[0.05rem]">
                                            {{ __('translation.Stock Adjust') }}
                                        </span>
                                            <span class="absolute left-auto right-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-3 h-3 bi bi-caret-down" viewBox="0 0 16 16">
                                                <path d="M3.204 5h9.592L8 10.481 3.204 5zm-.753.659 4.796 5.48a1 1 0 0 0 1.506 0l4.796-5.48c.566-.647.106-1.659-.753-1.659H3.204a1 1 0 0 0-.753 1.659z"/>
                                            </svg>
                                        </span>
                                        </x-sidebar.nav-link>
                                        <x-sidebar.subnav-wrapper :active="request()->routeIs('inout qr code') OR request()->routeIs('in-out-history') OR request()->is('defect-stock*')" x-show="subnavOpen">

                                            @if (in_array('Can access menu: Stock Adjust - Adjustment', session('assignedPermissions')))
                                            <x-sidebar.subnav-item>
                                                <x-sidebar.subnav-link href="{{ route('inout qr code') }}" :active="request()->routeIs('inout qr code')">
                                                    {{ __('translation.Adjustment') }}
                                                </x-sidebar.subnav-link>
                                            </x-sidebar.subnav-item>
                                            @endif

                                            @if (in_array('Can access menu: Stock Adjust - History', session('assignedPermissions')))
                                            <x-sidebar.subnav-item>
                                                <x-sidebar.subnav-link href="{{ route('in-out-history') }}" :active="request()->routeIs('in-out-history')">
                                                    {{ __('translation.History') }}
                                                </x-sidebar.subnav-link>
                                            </x-sidebar.subnav-item>
                                            @endif

                                            @if (in_array('Can access menu: Stock Adjust - Defect Stock', session('assignedPermissions')))
                                            <x-sidebar.subnav-item>
                                                <x-sidebar.subnav-link href="{{ route('defect-stock') }}" :active="request()->is('defect-stock*')">
                                                    {{ __('translation.Defect Stock') }}
                                                </x-sidebar.subnav-link>
                                            </x-sidebar.subnav-item>
                                            @endif

                                        </x-sidebar.subnav-wrapper>
                                    </x-sidebar.nav-item>
                                @endif

                                @if (in_array('Can access menu: WooCommerce - Product', session('assignedPermissions')) || in_array('Can access menu: WooCommerce - Settings', session('assignedPermissions')) || in_array('Can access menu: Shopee - Product', session('assignedPermissions')) || in_array('Can access menu: Lazada - Product', session('assignedPermissions')))
                                    <x-sidebar.nav-item :active="request()->routeIs('wc_products.index') OR request()->routeIs('woo-settings') OR request()->routeIs('shopee.product.index') OR request()->routeIs('lazada.product.index')" x-data="{ subnavOpen: false }">
                                        <x-sidebar.nav-link href="#" :active="request()->routeIs('wc_products.index') OR request()->routeIs('woo-settings') OR request()->routeIs('shopee.product.index') OR request()->routeIs('lazada.product.index')" x-on:click="subnavOpen = !subnavOpen">
                                            <i class="bi bi-tags text-base"></i>
                                            <span class="ml-2">
                                                {{ ucwords(__('translation.products')) }}
                                            </span>
                                            <span class="absolute left-auto right-4">
                                                <i class="bi bi-caret-down text-xs"></i>
                                            </span>
                                        </x-sidebar.nav-link>
                                        <x-sidebar.subnav-wrapper :active="request()->routeIs('wc_products.index') OR request()->routeIs('woo-settings') OR request()->routeIs('shopee.product.index') OR request()->routeIs('lazada.product.index')" x-show="subnavOpen">

                                            @if (in_array('Can access menu: WooCommerce - Product', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('wc_products.index') }}" :active="request()->routeIs('wc_products.index')">
                                                        {{ ucwords(__('translation.WooCommerce')) }}
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif

                                            @if (in_array('Can access menu: Shopee - Product', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('shopee.product.index') }}" :active="request()->routeIs('shopee.product.index')">
                                                        {{ ucwords(__('translation.shopee')) }}
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif

                                            @if (in_array('Can access menu: Lazada - Product', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('lazada.product.index') }}" :active="request()->routeIs('lazada.product.index')">
                                                        {{ ucwords(__('translation.lazada')) }}
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif

                                            @if (in_array('Can access menu: WooCommerce - Settings', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('woo-settings') }}" :active="request()->routeIs('woo-settings')">
                                                        Settings
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif

                                        </x-sidebar.subnav-wrapper>
                                    </x-sidebar.nav-item>
                                @endif

                                @if (in_array('Can access menu: Purchase Order - Purchase Order', session('assignedPermissions')) || in_array('Can access menu: Purchase Order - PO Shipments', session('assignedPermissions')) || in_array('Can access menu: Purchase Order - Product Cost', session('assignedPermissions')) || in_array('Can access menu: Purchase Order - Cost Analysis', session('assignedPermissions')) || in_array('Can access menu: Purchase Order - Order Analysis', session('assignedPermissions')) || in_array('Can access menu: Purchase Order - Settings', session('assignedPermissions')))
                                    <x-sidebar.nav-item :active="request()->is('order_purchase*') OR request()->is('po_shipments*')  OR request()->is('product_cost*') OR request()->is('cost_analysis*') OR request()->is('order_analysis*') OR request()->is('po_settings*')" x-data="{ subnavOpen: false }">
                                        <x-sidebar.nav-link href="#" :active="request()->is('order_purchase*') OR request()->is('po_shipments*')  OR request()->is('product_cost*') OR request()->is('cost_analysis*') OR request()->is('order_analysis*') OR request()->is('po_settings*')" x-on:click="subnavOpen = !subnavOpen">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-4 h-4 bi bi-cart2" viewBox="0 0 16 16">
                                                <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5zM3.14 5l1.25 5h8.22l1.25-5H3.14zM5 13a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0z"/>
                                            </svg>
                                            <span class="ml-2 relative top-[0.05rem]">
                                            {{ __('translation.Purchase Order') }}
                                        </span>
                                            <span class="absolute left-auto right-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-3 h-3 bi bi-caret-down" viewBox="0 0 16 16">
                                                <path d="M3.204 5h9.592L8 10.481 3.204 5zm-.753.659 4.796 5.48a1 1 0 0 0 1.506 0l4.796-5.48c.566-.647.106-1.659-.753-1.659H3.204a1 1 0 0 0-.753 1.659z"/>
                                            </svg>
                                        </span>
                                        </x-sidebar.nav-link>
                                        <x-sidebar.subnav-wrapper :active="request()->is('order_purchase*')  OR request()->is('po_shipments*') OR request()->is('product_cost*') OR request()->is('cost_analysis*') OR request()->is('order_analysis*') OR request()->is('po_settings*')" x-show="subnavOpen">

                                            @if (in_array('Can access menu: Purchase Order - Purchase Order', session('assignedPermissions')))
                                            <x-sidebar.subnav-item>
                                                <x-sidebar.subnav-link href="{{ route('order_purchase.index') }}" :active="request()->is('order_purchase*')">
                                                    {{ __('translation.Purchase Order') }}
                                                </x-sidebar.subnav-link>
                                            </x-sidebar.subnav-item>
                                            @endif


                                            @if (in_array('Can access menu: Purchase Order - PO Shipments', session('assignedPermissions')))
                                            <x-sidebar.subnav-item>
                                                <x-sidebar.subnav-link href="{{ route('po_shipments') }}" :active="request()->is('po_shipments*')">
                                                    {{ __('translation.PO Shipments') }}
                                                </x-sidebar.subnav-link>
                                            </x-sidebar.subnav-item>
                                            @endif

                                            @if (in_array('Can access menu: Purchase Order - Product Cost', session('assignedPermissions')))
                                            <x-sidebar.subnav-item>
                                                <x-sidebar.subnav-link href="{{ route('product_cost') }}" :active="request()->is('product_cost*')">
                                                    {{ __('translation.Product Cost') }}
                                                </x-sidebar.subnav-link>
                                            </x-sidebar.subnav-item>
                                            @endif

                                            @if (in_array('Can access menu: Purchase Order - Cost Analysis', session('assignedPermissions')))
                                            <x-sidebar.subnav-item>
                                                <x-sidebar.subnav-link href="{{ route('cost_analysis') }}" :active="request()->is('cost_analysis*')">
                                                    {{ __('translation.Cost Analysis') }}
                                                </x-sidebar.subnav-link>
                                            </x-sidebar.subnav-item>
                                            @endif

                                            @if (in_array('Can access menu: Purchase Order - Order Analysis', session('assignedPermissions')))
                                            <x-sidebar.subnav-item>
                                                <x-sidebar.subnav-link href="{{ route('order_analysis') }}" :active="request()->is('order_analysis*')">
                                                    {{ __('translation.Order Analysis') }}
                                                </x-sidebar.subnav-link>
                                            </x-sidebar.subnav-item>
                                            @endif


                                            @if (in_array('Can access menu: Purchase Order - Settings', session('assignedPermissions')))
                                            <x-sidebar.subnav-item>
                                                <x-sidebar.subnav-link href="{{ route('po_settings') }}" :active="request()->is('po_settings*')">
                                                <strong> {{ __('translation.Settings') }} </strong>
                                                </x-sidebar.subnav-link>
                                            </x-sidebar.subnav-item>
                                            @endif

                                        </x-sidebar.subnav-wrapper>
                                    </x-sidebar.nav-item>
                                @endif

                                @if (in_array('Can access menu: Report - Stock', session('assignedPermissions')) || in_array('Can access menu: Report - Stock Movements', session('assignedPermissions')) || in_array('Can access menu: Report - Activity Log', session('assignedPermissions')) || in_array('Can access menu: Tax Invoices', session('assignedPermissions')) || in_array('Can access menu: Report - Shopee Transaction', session('assignedPermissions')) || in_array('Can access menu: Report - Stock Value', session('assignedPermissions')))
                                    <x-sidebar.nav-item :active="request()->routeIs('product_report') OR request()->routeIs('stock_movement_report') OR request()->routeIs('activity_log') OR request()->is('tax-invoice*') OR request()->is('shopee-transaction*') OR request()->is('shopee-order*') OR request()->is('report/stock-value*')" x-data="{ subnavOpen: false }">
                                        <x-sidebar.nav-link href="#" :active="request()->routeIs('product_report') OR request()->routeIs('stock_movement_report') OR request()->routeIs('activity_log') OR request()->is('tax-invoice*') OR request()->is('shopee-transaction*') OR request()->is('shopee-order*') OR request()->is('report/stock-value*')" x-on:click="subnavOpen = !subnavOpen">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-4 h-4 bi bi-bar-chart" viewBox="0 0 16 16">
                                                <path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5v12h-2V2h2zm-2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-2zM6 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm-5 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3z"/>
                                            </svg>
                                            <span class="ml-2 relative top-[0.05rem]">
                                                Report
                                            </span>
                                                <span class="absolute left-auto right-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-3 h-3 bi bi-caret-down" viewBox="0 0 16 16">
                                                    <path d="M3.204 5h9.592L8 10.481 3.204 5zm-.753.659 4.796 5.48a1 1 0 0 0 1.506 0l4.796-5.48c.566-.647.106-1.659-.753-1.659H3.204a1 1 0 0 0-.753 1.659z"/>
                                                </svg>
                                            </span>
                                        </x-sidebar.nav-link>
                                        <x-sidebar.subnav-wrapper :active="request()->routeIs('product_report') OR request()->routeIs('stock_movement_report') OR request()->routeIs('activity_log') OR request()->is('tax-invoice*') OR request()->is('shopee-transaction*') OR request()->is('shopee-order*') OR request()->is('report/stock-value*')" x-show="subnavOpen">

                                            @if (in_array('Can access menu: Report - Stock', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('product_report') }}" :active="request()->routeIs('product_report')">
                                                        {{ __('translation.Stock') }}
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif

                                            @if (in_array('Can access menu: Report - Stock Movements', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('stock_movement_report') }}" :active="request()->routeIs('stock_movement_report')">
                                                        {{ __('translation.Stock Movements') }}
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif

                                            @if (in_array('Can access menu: Report - Stock Value', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('report.stock-value.index') }}" :active="request()->is('report/stock-value*')">
                                                        {{ __('translation.Stock Value') }}
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif

                                            @if (in_array('Can access menu: Report - Activity Log', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('activity_log') }}" :active="request()->routeIs('activity_log')">
                                                        {{ __('translation.Activity Log') }}
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif

                                            @if (in_array('Can access menu: Tax Invoices', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('tax-invoice.index') }}" :active="request()->is('tax-invoice*')">
                                                        Tax Invoices
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif

                                            @if (in_array('Can access menu: Report - Shopee Transaction', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('shopee-transaction.index') }}" :active="request()->is('shopee-transaction*') OR request()->is('shopee-order*')">
                                                        {{ __('translation.Shopee Transaction') }}
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif

                                        </x-sidebar.subnav-wrapper>
                                    </x-sidebar.nav-item>
                                @endif

                                @if (in_array('Can access menu: TPK Packing Data', session('assignedPermissions')))
                                    <x-sidebar.nav-item :active="request()->is('sheet-docs*') OR request()->is('sheet-data-tpks*')" x-data="{ subnavOpen: false }">
                                        <x-sidebar.nav-link href="#" :active="request()->is('sheet-docs*') OR request()->is('sheet-data-tpks*')" x-on:click="subnavOpen = !subnavOpen">
                                            <i class="bi bi-file-earmark-spreadsheet"></i>
                                            <span class="ml-2 relative top-[0.05rem]">
                                                {{ __('translation.TPK Packing') }}
                                            </span>
                                                <span class="absolute left-auto right-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-3 h-3 bi bi-caret-down" viewBox="0 0 16 16">
                                                    <path d="M3.204 5h9.592L8 10.481 3.204 5zm-.753.659 4.796 5.48a1 1 0 0 0 1.506 0l4.796-5.48c.566-.647.106-1.659-.753-1.659H3.204a1 1 0 0 0-.753 1.659z"/>
                                                </svg>
                                            </span>
                                        </x-sidebar.nav-link>
                                        <x-sidebar.subnav-wrapper :active="request()->is('sheet-docs*') OR request()->is('sheet-data-tpks*')" x-show="subnavOpen">

                                            <x-sidebar.subnav-item>
                                                <x-sidebar.subnav-link href="{{ route('sheet-data-tpks.index') }}" :active="request()->routeIs('sheet-data-tpks.index')">
                                                    {{ __('translation.Data') }}
                                                </x-sidebar.subnav-link>
                                            </x-sidebar.subnav-item>
                                            <x-sidebar.subnav-item>
                                                <x-sidebar.subnav-link href="{{ route('sheet-data-tpks.order-analysis') }}" :active="request()->routeIs('sheet-data-tpks.order-analysis')">
                                                    {{ __('translation.Order Analysis') }}
                                                </x-sidebar.subnav-link>
                                            </x-sidebar.subnav-item>
                                            <x-sidebar.subnav-item>
                                                <x-sidebar.subnav-link href="{{ route('sheet-docs.index') }}" :active="request()->is('sheet-docs*')">
                                                    {{ __('translation.Manage') }}
                                                </x-sidebar.subnav-link>
                                            </x-sidebar.subnav-item>

                                        </x-sidebar.subnav-wrapper>
                                    </x-sidebar.nav-item>
                                @endif

                                @if (in_array('Can access menu: CRM - Customers', session('assignedPermissions')))
                                    <x-sidebar.nav-item :active="request()->is('customer*')" x-data="{ subnavOpen: false }">
                                        <x-sidebar.nav-link href="#" :active="request()->is('customer*')" x-on:click="subnavOpen = !subnavOpen">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus" viewBox="0 0 16 16">
                                                <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H1s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                                                <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z"/>
                                            </svg>
                                            <span class="ml-2 relative top-[0.05rem]">
                                            {{ __('translation.CRM') }}
                                        </span>
                                            <span class="absolute left-auto right-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-3 h-3 bi bi-caret-down" viewBox="0 0 16 16">
                                                <path d="M3.204 5h9.592L8 10.481 3.204 5zm-.753.659 4.796 5.48a1 1 0 0 0 1.506 0l4.796-5.48c.566-.647.106-1.659-.753-1.659H3.204a1 1 0 0 0-.753 1.659z"/>
                                            </svg>
                                        </span>
                                        </x-sidebar.nav-link>
                                        <x-sidebar.subnav-wrapper :active="request()->is('customer*')" x-show="subnavOpen">

                                            @if (in_array('Can access menu: CRM - Customers', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('customer') }}" :active="request()->is('customer*')">
                                                        {{ __('translation.Customers') }}
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif

                                        </x-sidebar.subnav-wrapper>
                                    </x-sidebar.nav-item>
                                @endif
                            @endif

                            @if (Auth()->user()->role == 'woo')
                                <x-sidebar.nav-item :active="request()->routeIs('dashboard')">
                                    <x-sidebar.nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-4 h-4 bi bi-speedometer2" viewBox="0 0 16 16">
                                            <path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10zm9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.389.389 0 0 0-.029-.518z"/>
                                            <path fill-rule="evenodd" d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.253-1.845 1.602-2.932 1.25C11.309 13.488 9.475 13 8 13c-1.474 0-3.31.488-4.615.911-1.087.352-2.49.003-2.932-1.25A7.988 7.988 0 0 1 0 10zm8-7a7 7 0 0 0-6.603 9.329c.203.575.923.876 1.68.63C4.397 12.533 6.358 12 8 12s3.604.532 4.923.96c.757.245 1.477-.056 1.68-.631A7 7 0 0 0 8 3z"/>
                                        </svg>
                                        <span class="ml-2 relative top-[0.05rem]">
                                            {{ __('translation.Dashboard') }}
                                        </span>
                                    </x-sidebar.nav-link>
                                </x-sidebar.nav-item>

                                <x-sidebar.nav-item :active="request()->routeIs('woocommerce')">
                                    <x-sidebar.nav-link :href="route('woocommerce')" :active="request()->routeIs('woocommerce')">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-4 h-4 bi bi-bag-check" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                                            <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5z"/>
                                        </svg>
                                        <span class="ml-2 relative top-[0.05rem]">
                                            {{ __('translation.Woocomerce') }}
                                        </span>
                                    </x-sidebar.nav-link>
                                </x-sidebar.nav-item>
                            @endif

                            @if (Auth()->user()->role == 'admin')
                                <x-sidebar.nav-item :active="request()->routeIs('admin dashboard')">
                                    <x-sidebar.nav-link :href="route('admin dashboard')" :active="request()->routeIs('admin dashboard')">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-4 h-4 bi bi-speedometer2" viewBox="0 0 16 16">
                                            <path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10zm9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.389.389 0 0 0-.029-.518z"/>
                                            <path fill-rule="evenodd" d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.253-1.845 1.602-2.932 1.25C11.309 13.488 9.475 13 8 13c-1.474 0-3.31.488-4.615.911-1.087.352-2.49.003-2.932-1.25A7.988 7.988 0 0 1 0 10zm8-7a7 7 0 0 0-6.603 9.329c.203.575.923.876 1.68.63C4.397 12.533 6.358 12 8 12s3.604.532 4.923.96c.757.245 1.477-.056 1.68-.631A7 7 0 0 0 8 3z"/>
                                        </svg>
                                        <span class="ml-2 relative top-[0.05rem]">
                                            {{ __('translation.Dashboard') }}
                                        </span>
                                    </x-sidebar.nav-link>
                                </x-sidebar.nav-item>

                                <x-sidebar.nav-item :active="request()->routeIs('manage seller')">
                                    <x-sidebar.nav-link :href="route('manage seller')" :active="request()->routeIs('manage seller')">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-4 h-4 bi bi-shop" viewBox="0 0 16 16">
                                            <path d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.371 2.371 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976l2.61-3.045zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0zM1.5 8.5A.5.5 0 0 1 2 9v6h1v-5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v5h6V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5zM4 15h3v-5H4v5zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3zm3 0h-2v3h2v-3z"/>
                                        </svg>
                                        <span class="ml-2 relative top-[0.05rem]">
                                            {{ __('translation.Manage Seller') }}
                                        </span>
                                    </x-sidebar.nav-link>
                                </x-sidebar.nav-item>

                                <x-sidebar.nav-item :active="request()->routeIs('package')">
                                    <x-sidebar.nav-link :href="route('package')" :active="request()->routeIs('package')">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-4 h-4 bi bi-box" viewBox="0 0 16 16">
                                            <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5 8 5.961 14.154 3.5 8.186 1.113zM15 4.239l-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                                        </svg>
                                        <span class="ml-2 relative top-[0.05rem]">
                                            {{ __('translation.Package') }}
                                        </span>
                                    </x-sidebar.nav-link>
                                </x-sidebar.nav-item>

                                <x-sidebar.nav-item :active="request()->routeIs('user logo')">
                                    <x-sidebar.nav-link :href="route('user logo')" :active="request()->routeIs('user logo')">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-4 h-4 bi bi-person" viewBox="0 0 16 16">
                                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                                        </svg>
                                        <span class="ml-2 relative top-[0.05rem]">
                                            {{ __('translation.User Avatar') }}
                                        </span>
                                    </x-sidebar.nav-link>
                                </x-sidebar.nav-item>
                            @endif

                            @if (Auth()->user()->role == 'member' || Auth()->user()->role == 'staff')
                                <x-sidebar.nav-item :active="request()->routeIs('categories.index') OR request()->routeIs('suppliers') OR request()->routeIs('shops') OR request()->routeIs('channels.index') OR request()->routeIs('exchange-rates.index') OR request()->routeIs('product-tags.index') OR request()->routeIs('ship-types.index') OR request()->routeIs('cronReport') OR request()->routeIs('tax-rate-settings.index') OR request()->routeIs('company-info-settings.index') OR request()->is('manage-shipper*') OR request()->is('add-cost*')">
                                    <x-sidebar.nav-link href="{{ route('categories.index') }}" :active="request()->routeIs('categories.index') OR request()->routeIs('suppliers') OR request()->routeIs('shops') OR request()->routeIs('channels.index') OR request()->routeIs('exchange-rates.index') OR request()->routeIs('product-tags.index') OR request()->routeIs('ship-types.index') OR request()->routeIs('cronReport') OR request()->routeIs('tax-rate-settings.index') OR request()->routeIs('company-info-settings.index') OR request()->is('manage-shipper*') OR request()->is('add-cost*')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-gear" viewBox="0 0 16 16">
                                            <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                                            <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
                                        </svg>
                                        <span class="ml-2 relative top-[0.05rem]">
                                            {{ __('translation.Settings') }}
                                        </span>
                                    </x-sidebar.nav-link>
                                </x-sidebar.nav-item>
                            @endif

                            @if (Auth()->user()->role != 'admin')
                                <!-- Shopee -->
                                @if (in_array('Can access menu: Shopee - Settings', session('assignedPermissions')))
                                    <x-sidebar.nav-item :active="request()->routeIs('shopee.settings') OR request()->routeIs('shopee.product.boost.index')" x-data="{ subnavOpen: false }">
                                        <x-sidebar.nav-link href="#" :active="request()->routeIs('shopee.settings')" x-on:click="subnavOpen = !subnavOpen">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart-check" viewBox="0 0 16 16">
                                                <path d="M11.354 6.354a.5.5 0 0 0-.708-.708L8 8.293 6.854 7.146a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z"/>
                                                <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                            </svg>
                                            <span class="ml-2 relative top-[0.05rem]">
                                                Shopee
                                            </span>
                                            <span class="absolute left-auto right-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-3 h-3 bi bi-caret-down" viewBox="0 0 16 16">
                                            <path d="M3.204 5h9.592L8 10.481 3.204 5zm-.753.659 4.796 5.48a1 1 0 0 0 1.506 0l4.796-5.48c.566-.647.106-1.659-.753-1.659H3.204a1 1 0 0 0-.753 1.659z"/>
                                        </svg>
                                    </span>
                                        </x-sidebar.nav-link>
                                        <x-sidebar.subnav-wrapper :active="request()->routeIs('shopee.settings') OR request()->routeIs('shopee.product.boost.index') OR request()->routeIs('shopee.product.discount.index') OR request()->routeIs('shopee.order.order_history_analysis')" x-show="subnavOpen">
                                            @if (in_array('Can access menu: Shopee - Settings', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('shopee.settings') }}" :active="request()->routeIs('shopee.settings')">
                                                        Settings
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif
                                            @if (in_array('Can access menu: Shopee - Settings', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('shopee.product.boost.index') }}" :active="request()->routeIs('shopee.product.boost.index')">
                                                        Boost Product
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif
                                            @if (in_array('Can access menu: Shopee - Settings', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('shopee.product.discount.index') }}" :active="request()->routeIs('shopee.product.discount.index')">
                                                        Discount
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif
                                            @if (in_array('Can access menu: Shopee - Settings', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('shopee.order.order_history_analysis') }}" :active="request()->routeIs('shopee.order.order_history_analysis')">
                                                        Order Analysis
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif
                                        </x-sidebar.subnav-wrapper>
                                    </x-sidebar.nav-item>
                                @endif
                                <!-- End Shopee -->

                                <!-- Lazada -->
                                @if (in_array('Can access menu: Lazada - Settings', session('assignedPermissions')))
                                    <x-sidebar.nav-item :active="request()->routeIs('lazada.settings')" x-data="{ subnavOpen: false }">
                                        <x-sidebar.nav-link href="#" :active="request()->routeIs('lazada.settings')" x-on:click="subnavOpen = !subnavOpen">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart-check" viewBox="0 0 16 16">
                                                <path d="M11.354 6.354a.5.5 0 0 0-.708-.708L8 8.293 6.854 7.146a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z"/>
                                                <path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                            </svg>
                                            <span class="ml-2 relative top-[0.05rem]">
                                                Lazada
                                            </span>
                                            <span class="absolute left-auto right-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-3 h-3 bi bi-caret-down" viewBox="0 0 16 16">
                                                    <path d="M3.204 5h9.592L8 10.481 3.204 5zm-.753.659 4.796 5.48a1 1 0 0 0 1.506 0l4.796-5.48c.566-.647.106-1.659-.753-1.659H3.204a1 1 0 0 0-.753 1.659z"/>
                                                </svg>
                                            </span>
                                        </x-sidebar.nav-link>
                                        <x-sidebar.subnav-wrapper :active="request()->routeIs('lazada.settings')" x-show="subnavOpen">

                                            @if (in_array('Can access menu: Lazada - Settings', session('assignedPermissions')))
                                                <x-sidebar.subnav-item>
                                                    <x-sidebar.subnav-link href="{{ route('lazada.settings') }}" :active="request()->routeIs('lazada.settings')">
                                                        Settings
                                                    </x-sidebar.subnav-link>
                                                </x-sidebar.subnav-item>
                                            @endif

                                        </x-sidebar.subnav-wrapper>
                                    </x-sidebar.nav-item>
                                @endif
                                <!-- End Lazada -->

                                <!-- Facebook -->
                                @if (in_array('Can access menu: Facebook - Settings', session('assignedPermissions')))
                                    <x-sidebar.nav-item :active="request()->routeIs('facebook.index')">
                                        <x-sidebar.nav-link href="{{ route('facebook.index') }}" :active="request()->routeIs('facebook.index')">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-4 h-4 bi bi-archive" viewBox="0 0 16 16">
                                                <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1V2zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5H2zm13-3H1v2h14V2zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                                            </svg>
                                            <span class="ml-2 relative top-[0.05rem]">
                                                Facebook
                                            </span>
                                        </x-sidebar.nav-link>
                                    </x-sidebar.nav-item>
                                @endif
                                <!-- End Facebook -->
                            @endif

                            @if (Auth::user()->role == 'admin')
                                <x-sidebar.nav-item :active="request()->routeIs('translation.index')">
                                    <x-sidebar.nav-link :href="route('translation.index')" :active="request()->routeIs('translation.index')">
                                        <i class="bi bi-translate text-base"></i>
                                        <span class="ml-2 relative top-[0.05rem]">
                                            Translation
                                        </span>
                                    </x-sidebar.nav-link>
                                </x-sidebar.nav-item>
                            @endif

                        </x-sidebar.nav-wrapper>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-1/3 xl:hidden">
            <button type="button" class="px-6 py-4 border-0 bg-white text-gray-800 outline-none focus:outline-none" x-on:click="sidebarOpen = true">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
            </button>
        </div>
        <div class="w-1/3 xl:w-60 xl:z-30 xl:pointer-events-auto xl:bg-white">
            <div class="w-full h-full flex items-center justify-center">
                @if(session('roleName') == 'dropshipper')
                    <a href="{{ route('order_management.index') }}" class="py-2">
                        <img src="{{ asset('img/dodoselect.png') }}" alt="{{ config('app.name') }}" class="h-9 w-auto">
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="py-2">
                        <img src="{{ asset('img/dodoselect.png') }}" alt="{{ config('app.name') }}" class="h-9 w-auto">
                    </a>
                @endif
            </div>
        </div>
        <div class="w-1/3 xl:w-60 xl:z-30 relative xl:pt-4 xl:pointer-events-auto xl:bg-white" x-data="{ dropdownOpen: false }">
            <div class="w-full flex justify-end xl:justify-start">
                <button type="button" class="xl:w-full h-9 xl:h-auto px-7 xl:px-5 py-2 bg-transparent xl:hover:bg-blue-100 border-0 inline-flex xl:flex-col items-center xl:items-start outline-none focus:outline-none cursor-pointer" x-on:click="dropdownOpen = !dropdownOpen" x-on:click.away="dropdownOpen = false">
                    <div class="xl:inline-flex xl:items-center">
                        <div class="relative">
                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-8 xl:w-10 h-8 xl:h-10 rounded-full">
                        </div>
                        <div class="hidden xl:block ml-2 xl:ml-4 text-left">
                            <span class="block text-gray-800 font-bold whitespace-nowrap">
                                {{ Str::limit(ucwords(Auth::user()->name), 20) }}
                            </span>
                            <span class="text-gray-500 text-xs">
                                {{ ucwords(Auth::user()->role) }}
                            </span>
                        </div>
                    </div>
                    <div class="hidden xl:w-full xl:flex xl:justify-center xl:mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="w-4 bi bi-chevron-compact-down" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1.553 6.776a.5.5 0 0 1 .67-.223L8 9.44l5.776-2.888a.5.5 0 1 1 .448.894l-6 3a.5.5 0 0 1-.448 0l-6-3a.5.5 0 0 1-.223-.67z"/>
                        </svg>
                    </div>
                </button>
            </div>

            <div class="absolute xl:relative top-10 xl:top-0 right-8 xl:right-0 2xl:right-0 left-auto w-48 xl:w-full py-1 border border-solid border-gray-300 shadow-lg bg-white hidden z-10" :class="{ 'hidden' : dropdownOpen === false }">
                <a href="{{ route('profile') }}" class="block px-5 py-2 text-gray-800 hover:bg-gray-100 no-underline">
                    {{ __('translation.Your Profile') }}
                </a>
                @if (Auth::user()->role == 'member')
                    {{-- <a href="{{ route('your_packages') }}" class="block px-5 py-2 text-gray-800 hover:bg-gray-100 no-underline">
                        {{ __('translation.Your Package') }}
                    </a> --}}
                    <a href="{{ route('staff.manage') }}" class="block px-5 py-2 text-gray-800 hover:bg-gray-100 no-underline">
                        {{ __('translation.Manage Users') }}
                    </a>
                    <a href="{{ route('dropshippers') }}" class="block px-5 py-2 text-gray-800 hover:bg-gray-100 no-underline">
                        {{ __('translation.Manage Dropshipper') }}
                    </a>
                @endif
                <hr class="w-full border border-r-0 border-b-0 border-l-0 border-gray-200 my-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="#" onClick="event.preventDefault(); this.closest('form').submit();" class="block px-5 py-2 text-gray-800 hover:bg-gray-100 no-underline">
                        Sign Out
                    </a>
                </form>
            </div>
        </div>
    </nav>
</header>
