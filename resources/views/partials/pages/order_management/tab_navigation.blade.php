<div class="mb-6">
    <x-card.card-default>
        <x-card.body>
            <div class="flex flex-row overflow-x-auto">
                <x-page.order-management.tab-link :href="route('order_management.index')" :active="request()->Is('order_management')" class="flex flex-row items-center">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-layers" viewBox="0 0 16 16">
                            <path d="M8.235 1.559a.5.5 0 0 0-.47 0l-7.5 4a.5.5 0 0 0 0 .882L3.188 8 .264 9.559a.5.5 0 0 0 0 .882l7.5 4a.5.5 0 0 0 .47 0l7.5-4a.5.5 0 0 0 0-.882L12.813 8l2.922-1.559a.5.5 0 0 0 0-.882l-7.5-4zm3.515 7.008L14.438 10 8 13.433 1.562 10 4.25 8.567l3.515 1.874a.5.5 0 0 0 .47 0l3.515-1.874zM8 9.433 1.562 6 8 2.567 14.438 6 8 9.433z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <div class="font-bold">
                            {{ __('translation.Dodo Orders') }}
                        </div>
                        <div class="text-xs">
                            Processing ({{ number_format($totalProcessingOrders) }})
                        </div>
                    </div>
                </x-page.order-management.tab-link>

                <x-page.order-management.tab-link :href="url('order_management/dropshipper')" :active="request()->Is('order_management/dropshipper')" class="flex flex-row items-center">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-layers-fill" viewBox="0 0 16 16">
                            <path d="M7.765 1.559a.5.5 0 0 1 .47 0l7.5 4a.5.5 0 0 1 0 .882l-7.5 4a.5.5 0 0 1-.47 0l-7.5-4a.5.5 0 0 1 0-.882l7.5-4z"/>
                            <path d="m2.125 8.567-1.86.992a.5.5 0 0 0 0 .882l7.5 4a.5.5 0 0 0 .47 0l7.5-4a.5.5 0 0 0 0-.882l-1.86-.992-5.17 2.756a1.5 1.5 0 0 1-1.41 0l-5.17-2.756z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <div class="font-bold">
                            {{ __('translation.Dropshipper Orders') }}
                        </div>
                        <div class="text-xs">
                            Processing ({{ number_format($totalProcessingDropshipperOrders) }})
                        </div>
                    </div>
                </x-page.order-management.tab-link>

                <x-page.order-management.tab-link :href="route('wc-order-purchase.index')" :active="request()->routeIs('wc-order-purchase.index')" class="flex flex-row items-center">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-bag-check" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                            <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <div class="font-bold">
                            {{ __('translation.WooCommerce') }}
                        </div>
                        <div class="text-xs">
                            Processing ({{ number_format($totalProcessingWooCommerce) }})
                        </div>
                    </div>
                </x-page.order-management.tab-link>
            </div>
        </x-card.body>
    </x-card.card-default>
</div>
