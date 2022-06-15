
    <div class="row">
        <div class="col-lg-6 mb-4">
            <x-select class="text-sm" name="__btnShopFilterLazada" id="__btnShopFilterLazada">
                <option selected value="0">- All Shops -</option>
                @if (isset($shops))
                @foreach ($shops as $shop)
                <option value="{{$shop->id}}">{{$shop->shop_name}} ( {{$shop->processing_orders_count}} To Process )</option>
                @endforeach
                @endif
            </x-select>
        </div>
        <div class="col-lg-6 mb-4">
            <x-select class="text-sm" name="order-status-filter" id="order-status-filter">
                <option disabled value="0">- Select Status -</option>
                @foreach ($statusMainSchema as $idx => $status)
                @if($idx == 0)
                @foreach ($status['sub_status'] as $subStatus)
                <option value="{{ $subStatus['id'] }}">
                    {{ $subStatus['text'] }} ( {{ $subStatus['count'] }} )
                </option>
                @endforeach
                @endif
                @endforeach

            </x-select>
        </div>
    </div>


@push('bottom_js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#__btnShopFilterLazada').select2({
            placeholder: '- All Shops -',
            allowClear: true
        });

        let shopId = this.value;
        let statusId = $('#order-status-filter').val();
        let active = $("ul.nav li a.active");
        const parentStatusId = active.data('id');
        const parentStatusIdNew = 'P2'; 

        loadOrderStatusList(parentStatusIdNew, shopId, true);
    });

    $('#__btnShopFilterLazada').on('change', function () {
        let shopId = this.value;
        let statusId = $('#order-status-filter').val();
        let active = $("ul.nav li a.active");
        const parentStatusId = active.data('id');
        const parentStatusIdNew = 'P2';

        loadOrderStatusList(parentStatusIdNew, shopId);

        loadOrderManagementTable(statusId, shopId);

        let index = $(this)[0].selectedIndex;

        //$('#shopee_shipment_method_filter option')[0].selected = true;
        $('#searchbar').val("");
    });


    const loadOrderStatusList = (parentStatusId, shopId, loadShopeeShopsDropdown=false) => {
        $.ajax({
            url: '{{ route("lazada.order.get_status_custom_list") }}',
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
                        $("#order-status-filter").append('<option value="' + value.id + '">' + value.text + ' (' + value.count + ')</option>');
                    });
                }

                if (loadShopeeShopsDropdown && typeof(response.shopsToProcessCounts) !== "undefined") {
                    let shopId = $('#__btnShopFilterLazada').val();
                    let html = '<option value="0">- All Shops -</option>';
                    $.each(response.shopsToProcessCounts, function(index, shop) {
                        html += '<option value="'+shop.id+'" '+(shop.id === parseInt(shopId)?"selected":"")+'>';
                        html += shop.shop_name+' ( '+shop.processing_orders_count+' To Process )</option>';
                    });
                    $("#__btnShopFilterLazada").html(html);
                    $("#__btnShopFilterLazada").select2();
                }
            }
        });
    }

    $("#searchbar").keyup(function() {
        shopeeOrderPurchaseTable.search(this.value).draw();
    });
</script>
@endpush