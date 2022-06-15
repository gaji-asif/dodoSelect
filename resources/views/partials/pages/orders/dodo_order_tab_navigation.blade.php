<div class="mb-6">
    <x-card.card-default>
        <x-card.body>
            <div class="flex flex-row overflow-x-auto">
                <x-page.order-management.tab-link :href="route('order_management.index')" :active="request()->Is('order_management')" class="flex flex-row items-center">
                    <i class="bi bi-layers"></i>
                    <div class="ml-3">
                        <div class="font-bold">
                            {{ ucwords(__('translation.dodo_orders')) }}
                        </div>
                        <div class="text-xs">
                            {{ ucwords(__('translation.processing')) }} ({{ number_format($totalProcessingOrders) }})
                        </div>
                    </div>
                </x-page.order-management.tab-link>

                <x-page.order-management.tab-link :href="route('order_management.index', [ 'customerType' => 'dropshipper' ])" :active="request()->is('order_management/dropshipper*')" class="flex flex-row items-center">
                    <i class="bi bi-layers"></i>
                    <div class="ml-3">
                        <div class="font-bold">
                            {{ ucwords(__('translation.dropshipper_orders')) }}
                        </div>
                        <div class="text-xs">
                            {{ ucwords(__('translation.processing')) }} ({{ number_format($totalProcessingDropshipperOrders) }})
                        </div>
                    </div>
                </x-page.order-management.tab-link>
            </div>
        </x-card.body>
    </x-card.card-default>
</div>
