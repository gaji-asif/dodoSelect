<div class="mb-6">
    <x-card.card-default>
        <x-card.body>
            <div class="flex flex-row overflow-x-auto">
                <x-page.order-management.tab-link :href="route('wc-order-purchase.index')" :active="request()->routeIs('wc_order_purchase.index')" class="flex flex-row items-center">
                    <i class="bi bi bi-bag-check"></i>
                    <div class="ml-3">
                        <div class="font-bold">
                            {{ ucwords(__('translation.WooCommerce')) }}
                        </div>
                        <div class="text-xs">
                            {{ ucwords(__('translation.processing')) }} ({{ number_format($totalProcessingWooCommerce) }})
                        </div>
                    </div>
                </x-page.order-management.tab-link>
            </div>
        </x-card.body>
    </x-card.card-default>
</div>
