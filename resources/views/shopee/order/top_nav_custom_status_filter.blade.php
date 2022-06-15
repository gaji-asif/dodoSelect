<div class="w-full flex flex-col sm:flex-row sm:justify-end items-end mb-3">
    <div class="w-full sm:w-1/4 md:w-1/3 lg:w-1/3 relative -top-1" >
        <x-select class="text-sm" name="__btnShopFilter" id="__btnShopFilter">
            <option selected value="0">- All Shops -</option>
            @if (isset($shops))
                @foreach ($shops as $shop)
                    <option id="shopee_shop_with_order_count_option_{{$shop->id}}" value="{{$shop->id}}">{{$shop->shop_name}}</option>
                @endforeach
            @endif
        </x-select>
    </div>
</div>

<div class="w-full mb-5">
    <div class="py-4 border-0 sm:border border-solid border-gray-300 rounded-md bg-white sm:bg-gray-50">
        <div class="mb-5">
            <div class="w-full sm:w-2/5 lg:w-2/5 xl:w-1/3 sm:mx-auto">
                <ul class="nav justify-content-center grid grid-cols-2 gap-2">
                    @foreach ($statusMainSchema as $idx => $status)
                        <li class="nav-item border border-solid border-gray-300 rounded-md bg-gray-500">
                            <a class="nav-link order-status-filter__tab top-status-filter__tab-{{ $status['id'] }} top-status-filter__tab shadow-lg text-white flex flex-col items-center justify-center text-center cursor-pointer @if ($idx == 0) active underline @endif" 
                            data-toggle="tab" role="tab" data-id="{{ $status['id'] }}" data-sub-status-id="{{ array_column($status['sub_status'], 'id')[0] }}" data-status-ids="{{ implode(',', array_column($status['sub_status'], 'id')) }}" data-status-type="top" id="status-filter__{{ $idx }}">
                                <span class="mb-2">
                                    {!! $status['icon'] !!}
                                </span>
                                <span class="hidden sm:block">
                                    {{ $status['text'] }}
                                </span>
                                <span class="text-sm" id="__tabCount_{{ $status['id'] }}">
                                        ( {!! $status['count'] !!} )
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div>
            <div class="mb-5">
                <div class="w-full sm:w-2/5 lg:w-2/5 xl:w-1/3 sm:mx-auto">
                    <ul class="nav justify-content-center grid grid-cols-3 gap-2">
                        @foreach ($statusSecondarySchema as $idx => $status)
                            <li class="nav-item border border-solid border-gray-300 rounded-md bg-white">
                                <a class="nav-link order-status-filter__tab secondary-status-filter__tab secondary-status-filter__tab-{{ $status['id'] }} flex flex-col items-center justify-center text-center cursor-pointer text-xs" data-toggle="tab" role="tab" data-id="{{ $status['id'] }}"data-sub-status-id="{{ array_column($status['sub_status'], 'id')[0] }}" data-status-ids="{{ implode(',', array_column($status['sub_status'], 'id')) }}" data-status-type="secondary" id="status-filter__{{ $idx }}">
                                    <span class="mb-2">
                                        {!! $status['icon'] !!}
                                    </span>
                                    <span class="hidden sm:block">
                                        {{ $status['text'] }}
                                    </span>
                                    <span id="__tabCount_{{ $status['id'] }}">
                                            ( {!! $status['count'] !!} )
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        
    </div>
</div>

<div class="flex flex-col sm:flex-row sm:justify-between items-start mb-2" id="datatableBtns">
    <div class="w-full sm:mb-0">
        <div class="flex flex-col sm:flex-row">
            <div class="w-full sm:w-1/4 xl:ml-1 mb-1 sm:mb-0 relative -top-1">
                <x-input type="text" id="searchbar" placeholder="Search"></x-input>
            </div>
            <div class="w-full sm:w-1/2 flex flex-col sm:flex-row">
                <div class="sm:ml-2">
                    <x-button class="mb-3 sm:mb-0 sm:ml-2" color="green" data-toggle="modal" data-target="#SyncModalOrder" id="BtnSyncModalOrder">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                            <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                            <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                        </svg>
                        <span class="ml-2">{{__("shopee.order.sync_order")}}</span>
                    </x-button>
                </div>
            </div>

            <div class="w-full sm:w-1/4 relative -top-1 sm:justify-end" >
                <x-select class="text-sm" name="order-status-filter" id="order-status-filter">
                    <option disabled value="0">- Select Status -</option>
                    @foreach ($statusMainSchema as $idx => $status)
                        @if($idx == 0)
                            @foreach ($status['sub_status'] as $subStatus)
                                <option value="{{ $subStatus['id'] }}">
                                    {{ $subStatus['text'] }} ({{ $subStatus['count'] }})
                                </option>
                            @endforeach
                        @endif
                    @endforeach

                </x-select>
            </div>
        </div>
    </div>
</div>


@push('bottom_js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#__btnShopFilter').select2({
            placeholder: '- All Shops -',
            allowClear: true
        });

        let shopId = this.value;
        let statusId = $('#order-status-filter').val();
        let active = $("ul.nav li a.active");
        const parentStatusId = active.data('id');

        // loadOrderStatusListForShopee(parentStatusId, shopId, true);
    });

    $('#__btnShopFilter').on('change', function () {
        let shopId = this.value;
        let statusId = $('#order-status-filter').val();

        let active = $("ul.nav li a.active");
        const parentStatusId = active.data('id');

        setTimeout(function() {
            let option_text = $("#select2-__btnShopFilter-container").html();
            let option_text_shop = option_text.split("(")[0];
            $("#select2-__btnShopFilter-container").html(option_text_shop+"( ... )");
        }, 10);

        storeSelectedShopAndStatusFilterInfo();
        selectedShopId = shopId;

        loadOrderStatusListForShopee(parentStatusId, shopId, true);

        loadOrderManagementTable(statusId, shopId);
        // setTimeout(function() {
        //     loadOrderManagementTable(orderStatusId, shopId);
        // }, 2000);

        let index = $(this)[0].selectedIndex;

        $('#shopee_shipment_method_filter option')[0].selected = true;
        $('#searchbar').val("");
    });


    const loadOrderStatusListForShopee = (parentStatusId, shopId, loadShopeeShopsDropdown=false) => {
        $.ajax({
            url: '{{ route("shopee.order.get_status_custom_list") }}',
            type: "POST",
            data: {
                parentStatusId: parentStatusId,
                shopId: shopId
            },
            dataType: 'json',
            success: function (response) {
                if (typeof(response.tabCounts) !== "undefined") {
                    $.each(response.tabCounts, function (key, value) {
                        $('#__tabCount_' + key).html(`( ${value} )`);
                    });
                }

                $('#order-status-filter').html('<option disabled value="0">- Select Status -</option>');
                if (typeof(response.orderStatusCounts) !== "undefined") {
                    $.each(response.orderStatusCounts, function (key, value) {
                        $("#order-status-filter").append('<option value="' + value.id + '"'+(selectedStatusIds==value.id?"selected":"")+'>' + value.text + ' (' + value.count + ')</option>');
                    });
                }

                if (loadShopeeShopsDropdown && typeof(response.shopsToProcessCounts) !== "undefined") {
                    let shopId;
                    if(typeof(selectedShopId) !== "undefined" && selectedShopId !== 0) {
                        shopId = selectedShopId;
                    } else {
                        shopId = $('#__btnShopFilter').val();
                    }
                    
                    let html = '<option value="0">- All Shops -</option>';
                    $.each(response.shopsToProcessCounts, function(index, shop) {
                        html += '<option value="'+shop.id+'" '+(shop.id === parseInt(shopId)?"selected":"")+' id="shopee_shop_with_order_count_option_'+shop.id+'">';
                        html += shop.shop_name+' ( '+shop.processing_orders_count+' To Process )</option>';
                        if (shop.id === parseInt(shopId)) {

                        }
                    });
                    $("#__btnShopFilter").html(html);
                    $("#__btnShopFilter").select2();
                }
            }
        });
    }

    $("#searchbar").keyup(function() {
        shopeeOrderPurchaseTable.search(this.value).draw();
    });
</script>
@endpush