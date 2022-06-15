<x-page.settings.button-nav href="{{ route('categories.index') }}" :active="request()->routeIs('categories.index')">
    Categories
</x-page.settings.button-nav>

<x-page.settings.button-nav href="{{ route('sub_categories') }}" :active="request()->routeIs('sub_categories')">
    Sub Categories
</x-page.settings.button-nav>

<x-page.settings.button-nav href="{{ route('suppliers') }}" :active="request()->routeIs('suppliers')">
    Supplier
</x-page.settings.button-nav>

<x-page.settings.button-nav href="{{ route('shops') }}" :active="request()->routeIs('shops')">
    Shops
</x-page.settings.button-nav>

<x-page.settings.button-nav href="{{ route('woo-settings') }}" :active="request()->routeIs('woo-settings')">
    Woo Shops
</x-page.settings.button-nav>


<x-page.settings.button-nav href="{{ route('channels.index') }}" :active="request()->routeIs('channels.index')">
    Channels
</x-page.settings.button-nav>

<x-page.settings.button-nav href="{{ route('exchange-rates.index') }}" :active="request()->routeIs('exchange-rates.index')">
    Exchange Rate
</x-page.settings.button-nav>

<x-page.settings.button-nav href="{{ route('product-tags.index') }}" :active="request()->routeIs('product-tags.index')">
    Product Tag
</x-page.settings.button-nav>

<x-page.settings.button-nav href="{{ route('ship-types.index') }}" :active="request()->routeIs('ship-types.index')">
    Ship Types
</x-page.settings.button-nav>

<x-page.settings.button-nav href="{{ route('cronReport') }}" :active="request()->routeIs('cronReport')">
    Cron Report
</x-page.settings.button-nav>

<x-page.settings.button-nav href="{{ route('tax-rate-settings.index') }}" :active="request()->routeIs('tax-rate-settings.index')">
    Tax Rate
</x-page.settings.button-nav>

<x-page.settings.button-nav href="{{ route('company-info-settings.index') }}" :active="request()->routeIs('company-info-settings.index')">
    Company Info
</x-page.settings.button-nav>

@if (in_array('Can access menu: Manage Shipper', session('assignedPermissions')))
    <x-page.settings.button-nav href="{{ route('manage shipper') }}" :active="request()->is('manage-shipper*') OR request()->is('add-cost*') OR request()->is('shipping-cost-edit*')">
        Manage Shipper
    </x-page.settings.button-nav>
@endif

<x-page.settings.button-nav href="{{ route('dodochat.log') }}" :active="request()->routeIs('dodochat.log')">
    DoDoChat Log
</x-page.settings.button-nav>

<x-page.settings.button-nav href="{{ route('inventory_qty_sync_error_log_index') }}" :active="request()->routeIs('inventory_qty_sync_error_log_index')">
    Inventory Qty Sync Error Log
</x-page.settings.button-nav>
