<div class="mb-6">
    <x-card.card-default>
        <x-card.body>
            <div class="flex flex-row overflow-x-auto">

                @if (in_array('Can access menu: Shopee - Order', session('assignedPermissions')))
                    <x-partials.tab-link :href="route('shopee.order.index')" :active="request()->routeIs('shopee.order.index')" class="flex flex-row items-center">
                        <div>
                            <i class="bi bi-bag-check"></i>
                        </div>
                        <div class="ml-3">
                            <span class="font-bold">
                                {{ ucfirst(__('translation.Shopee')) }}
                            </span>
                            <div class="text-xs">
                                {{ ucfirst(__('translation.processing')) }} ({{ $totalProcessingShopee??'' }})
                                
                            </div>
                        </div>
                    </x-partials.tab-link>
                @endif

                @if (in_array('Can access menu: Lazada - Order', session('assignedPermissions')))
                    <x-partials.tab-link :href="route('lazada.order.index')" :active="request()->routeIs('lazada.order.index')" class="flex flex-row items-center">
                        <div>
                            <i class="bi bi-bag-check"></i>
                        </div>
                        <div class="ml-3">
                            <span class="font-bold">
                                {{ ucfirst(__('translation.Lazada')) }}
                            </span>
                            <div class="text-xs">
                                {{ ucfirst(__('translation.processing')) }} ({{ $totalProcessingLazada??'' }})
                            </div>
                        </div>
                    </x-partials.tab-link>
                @endif

            </div>
        </x-card.body>
    </x-card.card-default>
</div>