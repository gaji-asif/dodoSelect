<div class="mb-6">
    <x-card.card-default>
        <x-card.body>
            <div class="flex flex-row overflow-x-auto">
                <x-tab-link :href="route('shopee-transaction.index')" :active="request()->Is('shopee-transaction*')" class="flex flex-row items-center">
                    <i class="bi bi-wallet2"></i>
                    <div class="ml-3">
                        <div class="font-bold">
                            {{ ucwords(__('translation.Wallet Transaction')) }}
                        </div>
                    </div>
                </x-tab-link>
                <x-tab-link :href="route('shopee-order.index')" :active="request()->Is('shopee-order*')" class="flex flex-row items-center">
                    <i class="bi bi-cart3"></i>
                    <div class="ml-3">
                        <div class="font-bold">
                            {{ ucwords(__('translation.Orders')) }}
                        </div>
                    </div>
                </x-tab-link>
                {{-- <x-tab-link :href="route('shopee-transaction.index')" :active="false" class="flex flex-row items-center">
                    <i class="bi bi-journal-check"></i>
                    <div class="ml-3">
                        <div class="font-bold">
                            {{ ucwords(__('translation.Report')) }}
                        </div>
                    </div>
                </x-tab-link> --}}
            </div>
        </x-card.body>
    </x-card.card-default>
</div>