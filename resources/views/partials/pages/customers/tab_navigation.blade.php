<div class="mb-6">
    <x-card.card-default>
        <x-card.body>
            <x-page.order-management.tab-link :href="route('customer.order_list', [ 'id' => $customer->id ])" :active="request()->routeIs('customer.order_list', [ 'id' => $customer->id ])">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-layers" viewBox="0 0 16 16">
                    <path d="M8.235 1.559a.5.5 0 0 0-.47 0l-7.5 4a.5.5 0 0 0 0 .882L3.188 8 .264 9.559a.5.5 0 0 0 0 .882l7.5 4a.5.5 0 0 0 .47 0l7.5-4a.5.5 0 0 0 0-.882L12.813 8l2.922-1.559a.5.5 0 0 0 0-.882l-7.5-4zm3.515 7.008L14.438 10 8 13.433 1.562 10 4.25 8.567l3.515 1.874a.5.5 0 0 0 .47 0l3.515-1.874zM8 9.433 1.562 6 8 2.567 14.438 6 8 9.433z"/>
                </svg>
                <span class="ml-1">
                    {{ __('translation.Orders') }}
                </span>
            </x-page.order-management.tab-link>
            <x-page.order-management.tab-link :href="route('customer.custom_order_list', [ 'id' => $customer->id ])" :active="request()->routeIs('customer.custom_order_list', [ 'id' => $customer->id ])">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-layers-half" viewBox="0 0 16 16">
                    <path d="M8.235 1.559a.5.5 0 0 0-.47 0l-7.5 4a.5.5 0 0 0 0 .882L3.188 8 .264 9.559a.5.5 0 0 0 0 .882l7.5 4a.5.5 0 0 0 .47 0l7.5-4a.5.5 0 0 0 0-.882L12.813 8l2.922-1.559a.5.5 0 0 0 0-.882l-7.5-4zM8 9.433 1.562 6 8 2.567 14.438 6 8 9.433z"/>
                </svg>
                <span class="ml-1">
                    {{ __('translation.Custom Orders') }}
                </span>
            </x-page.order-management.tab-link>
        </x-card.body>
    </x-card.card-default>
</div>
