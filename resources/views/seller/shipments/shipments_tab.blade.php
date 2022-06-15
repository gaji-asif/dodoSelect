<div class="col-span-12">
    <div class="w-full bg-white rounded-md shadow py-5 mb-6">
        <div class="px-5">
            <div class="flex flex-row overflow-x-auto">
                <a href="{{ route('all_shipment_index', 1) }}" class="px-4 py-2 mr-2 rounded-md  hover:bg-blue-500 hover:text-white transition-all duration-300 whitespace-nowrap flex flex-row items-center {{ request()->is('all_shipment/1') ? 'bg-blue-500 text-white' : 'bg-white text-blue-500' }}">
                    <i class="bi bi-layers"></i>
                    <div class="ml-3">
                        <div class="font-bold">
                            {{__('translation.Dodo Shipments')}}
                        </div>
                        <div class="text-xs">
                            @php
                            $total = \App\Models\Shipment::getAllShipmentsCount(1);
                            @endphp
                            {{__('translation.Total')}} ({{count($total)}})
                     </div>
                 </div>
             </a>
             <a href="{{ route('all_shipment_index', 2) }}" class="px-4 py-2 mr-2 rounded-md  hover:bg-blue-500  hover:text-white transition-all duration-300 whitespace-nowrap flex flex-row items-center {{ request()->is('all_shipment/2') ? 'bg-blue-500 text-white' : 'bg-white text-blue-500' }}">
                    <i class="bi bi-layers"></i>
                    <div class="ml-3">
                        <div class="font-bold">
                            {{__('translation.Dropships Shipments')}}
                        </div>
                        <div class="text-xs">
                            @php
                            $total = \App\Models\Shipment::getAllShipmentsCount(2);
                            @endphp
                            {{__('translation.Total')}} ({{count($total)}})
                     </div>
                 </div>
             </a>
             
             @foreach ($statusMainSchema as $idx => $status)
             <a href="{{ route('all_shipment_index', 3) }}" data-id="{{ $status['id'] }}" data-sub-status-id="{{ array_column($status['sub_status'], 'id')[0] }}" data-status-ids="{{ implode(',', array_column($status['sub_status'], 'id')) }}" data-status-type="top" id="status-filter__{{ $idx }}" class="px-4 py-2 mr-2 rounded-md hover:bg-blue-500 hover:text-white transition-all duration-300 whitespace-nowrap flex flex-row items-center {{ request()->is('all_shipment/3') ? 'bg-blue-500 text-white' : 'bg-white text-blue-500' }}">
                <i class="bi bi-layers"></i>
                <div class="ml-3">
                    <div class="font-bold">
                        {{__('translation.Shopee Shipments')}}
                    </div>
                    <div class="text-xs">
                        @php
                        $total = \App\Models\Shipment::getAllShipmentsCount(3);
                        @endphp
                       {{__('translation.Total')}} ( {!! $status['count'] !!} )
                    </div>
                </div>
            </a> 
            @endforeach

            <a href="{{ route('all_shipment_index', 4) }}" class="px-4 py-2 mr-2 rounded-md hover:bg-blue-500 hover:text-white transition-all duration-300 whitespace-nowrap flex flex-row items-center {{ request()->is('all_shipment/4') ? 'bg-blue-500 text-white' : 'bg-white text-blue-500' }}">
                <i class="bi bi-layers"></i>
                <div class="ml-3">
                    <div class="font-bold">
                        {{__('translation.Lazada Shipments')}}
                    </div>
                    <div class="text-xs">
                        @php
                        $total = \App\Models\Shipment::getAllShipmentsCount(4);
                        @endphp
                        {{__('translation.Total')}} ({{count($total)}})
                    </div>
                </div>
            </a> 

            <a href="{{ route('all_shipment_index', 5) }}" class="px-4 py-2 mr-2 rounded-md  hover:bg-blue-500 hover:text-white transition-all duration-300 whitespace-nowrap flex flex-row items-center {{ request()->is('all_shipment/5') ? 'bg-blue-500 text-white' : 'bg-white text-blue-500' }}">
                <i class="bi bi-layers"></i>
                <div class="ml-3">
                    <div class="font-bold">
                        {{__('translation.Woo Shipments')}}
                    </div>
                    <div class="text-xs">
                       @php
                        $total = \App\Models\Shipment::getAllShipmentsCount(5);
                        @endphp
                        {{__('translation.Total')}} ({{count($total)}})
                    </div>
                </div>
            </a> 

        </div>
    </div>
</div>
</div>