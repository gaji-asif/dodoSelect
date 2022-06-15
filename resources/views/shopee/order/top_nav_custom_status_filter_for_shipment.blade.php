      <style type="text/css">
          #order-status-filter-for-shipment{
            display: none;
          }
      </style>
      <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="row">
                <div class="col-8">
                    <x-input type="text" id="shipment_no" placeholder="Search by Shipment No" name="shipment_no" autocomplete="off" />
                </div>
                <div class="col-4">
                    <button type="button" id="__search" class="btn btn-primary margin_top_5" title="{{ __('translation.Find By Grid') }}">
                        <span><i class="fa fa-search"></i></span>
                        <span>{{ __('translation.Search') }}</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="col-lg-1"></div>
        <div class="col-lg-3 col-xs-12 col-sm-12 mb-4">
            <select class="text-sm form-control" name="__btnShopFilter" id="__btnShopFilter">
                <option selected value="0">- All Shops -</option>
                @if (isset($shops))
                @foreach ($shops as $shop)
                <option value="{{$shop->id}}">{{$shop->shop_name}}</option>
                @endforeach
                @endif
            </select>
        </div>
        <div class="col-lg-3 mb-4 col-xs-12 col-sm-12">
            <select class="text-sm form-control height_status_filter" name="order-status-filter" id="order-status-filter">
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

            </select>

            <select class="text-sm form-control height_status_filter" name="order-status-filter-for-shipment" id="order-status-filter-for-shipment">
                <option disabled value="0">- Select Status -</option>
                @foreach ($statusMainSchema as $idx => $status)
                @if($idx == 0)
                @foreach ($status['sub_status'] as $subStatus)
                <option value="{{ $subStatus['id'] }}" @if($subStatus['id'] == 'SHIPPED_TO_WAREHOUSE') selected @endif>
                    {{ $subStatus['text'] }} ( {{ $subStatus['count'] }} )
                </option>
                @endforeach
                @endif
                @endforeach

            </select>
        </div>
    </div>
    <div class="col-lg-12 mb-3">
    <div class="row">
    <div class="" id="error_found_message"></div>
    <input type="hidden" name="" id="order_id">
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
            const parentStatusIdNew = 'P2'; 

            loadOrderStatusList(parentStatusIdNew, shopId, true);
        });

        $('#__btnShopFilter').on('change', function () {
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
                        $("#order-status-filter").append('<option value="' + value.id + '">' + value.text + ' (' + value.count + ')</option>');
                    });
                }

                if (loadShopeeShopsDropdown && typeof(response.shopsToProcessCounts) !== "undefined") {
                    let shopId = $('#__btnShopFilter').val();
                    let html = '<option value="0">- All Shops -</option>';
                    $.each(response.shopsToProcessCounts, function(index, shop) {
                        html += '<option value="'+shop.id+'" '+(shop.id === parseInt(shopId)?"selected":"")+'>';
                        html += shop.shop_name+' ( '+shop.processing_orders_count+' To Process )</option>';
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

    $(document).on('click', '#__search', function() {
        var shipment_no = $("#shipment_no").val();
        
        $("#error_found_message").hide();
        let shopId = $("#__btnShopFilter").val();
        let statusId = $('#order-status-filter').val();
        let active = $("ul.nav li a.active");
        const parentStatusId = active.data('id');
        const parentStatusIdNew = 'P2';


        loadOrderStatusList(parentStatusIdNew, shopId);
        loadOrderManagementTableForShipment(shipment_no);
        //loadOrderManagementTable(statusId, shopId);
    });
</script>
@endpush