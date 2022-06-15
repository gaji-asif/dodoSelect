/* eslint-disable no-undef */
const tableFilter = {
    salesChannel: 'woo',
    shopFilter: 0,
    selectionFilter: 'all'
};

const loadProductSyncTable = (productId, tableFilter) => {
    $('#__productSyncTable').DataTable({
        serverSide: true,
        processing: true,
        bDestroy: true,
        bDeferRender: true,
        ajax: {
            type: 'GET',
            url: route('product.inventory_sync.datatable'),
            data: {
                productSyncId: productId,
                salesChannel: tableFilter.salesChannel,
                shopFilter: tableFilter.shopFilter,
                selectionFilter: tableFilter.selectionFilter
            }
        },
        columnDefs: [
            {
                targets: [0],
                orderable: false,
                render: function (data, type, row, meta) {
                    data = `<input type="checkbox" class="dt-checkboxes sync_product_id" name="sync_product_id">`;

                    if (row[2].includes('checked')) {
                        data = `<input type="checkbox" class="dt-checkboxes sync_product_id" name="sync_product_id" checked disabled>`;
                    }

                    if (row[2].includes('linked')) {
                        data = `<input type="checkbox" class="dt-checkboxes sync_product_id" name="sync_product_id" checked disabled>`;
                    }

                    return data;
                },
                checkboxes: {
                    selectRow: true
                }
            }
        ],
        select: {
            style: 'multiple'
        },
        order: [
            [1, 'desc']
        ]
    });
};

loadProductSyncTable(productSyncId, tableFilter);

const loadShopsList = (salesChannel) => {
    $.ajax({
        url: route('product.inventory_sync.shop_list'),
        type: 'POST',
        data: {
            salesChannel: salesChannel,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function (result) {
            $('#__selectShopFilter').html('<option selected value="0">- Select Shop -</option>');

            if (salesChannel === 'woo') {
                $.each(result.shopList, function (key, shop) {
                    $('#__selectShopFilter').append(
                        $('<option/>', {
                            value: shop.id,
                            text: shop.shops.name
                        })
                    );
                });
            } else {
                $.each(result.shops, function (key, shop) {
                    $('#__selectShopFilter').append(
                        $('<option/>', {
                            value: shop.id,
                            text: shop.name
                        })
                    );
                });
            }
        }
    });
};

const loadFilterQuantities = (salesChannel) => {
    $.ajax({
        url: route('product.inventory_sync.filter_quantities'),
        type: 'POST',
        data: {
            productSyncId: productSyncId,
            salesChannel: salesChannel,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function (result) {
            $('#__selectionFilter').html('<option disabled value="0">- Sort By -</option>');

            $('#__selectionFilter').append(
                $('<option/>', {
                    value: 'all',
                    text: `All (${result.productsCount})`
                })
            );

            $('#__selectionFilter').append(
                $('<option/>', {
                    value: 'selected',
                    text: `Selected (${result.productSelectedCount})`
                })
            );

            $('#__selectionFilter').append(
                $('<option/>', {
                    value: 'available',
                    text: `Available (${result.productAvailableCount})`
                })
            );

            $('#__selectionFilter').append(
                $('<option/>', {
                    value: 'unavailable',
                    text: `Unavailable (${result.productUnavailableCount})`
                })
            );
        }
    });
};

$('#__selectChannelFilter').on('change', function () {
    const salesChannel = $(this).val();
    tableFilter.salesChannel = salesChannel;

    loadShopsList(salesChannel);
    loadFilterQuantities(salesChannel);

    loadProductSyncTable(productSyncId, tableFilter);
});

$('#__selectShopFilter').on('change', function () {
    const shopId = $(this).val();
    $('#__selectionFilter').val('all');

    tableFilter.shopFilter = shopId;
    loadProductSyncTable(productSyncId, tableFilter);
});

$('#__selectionFilter').on('change', function () {
    const selectionFilter = $(this).val();
    tableFilter.selectionFilter = selectionFilter;

    loadProductSyncTable(productSyncId, tableFilter);
});
