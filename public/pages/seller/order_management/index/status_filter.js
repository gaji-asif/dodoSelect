$('.order-status-filter__tab').on('shown.bs.tab', function (event) {
    const $thisElement = $(event.target);
    const parentStatusId = $thisElement.data('id');
    const orderStatusId = $thisElement.data('sub-status-id');
    const orderStatusType = $thisElement.data('status-type');

    // eslint-disable-next-line no-undef
    selectedStatusIds = orderStatusId;
    // eslint-disable-next-line no-undef
    loadOrderStatusList(parentStatusId);

    // eslint-disable-next-line no-undef
    loadOrderManagementTable(orderStatusId);

    // eslint-disable-next-line eqeqeq,no-empty
    if (parentStatusId === 'P2') {
        $('#batch_print').removeClass('hidden');
        $('#bulk_shipment').addClass('hidden');
    } else {
        $('#batch_print').addClass('hidden');
        $('#bulk_shipment').removeClass('hidden');
    }

    $('.top-status-filter__tab').addClass('text-white').removeClass('underline').removeClass('active');
    $('.secondary-status-filter__tab').addClass('text-gray-900').removeClass('text-blue-500').removeClass('active');
    // eslint-disable-next-line eqeqeq
    if (orderStatusType === 'top') {
        $($thisElement).addClass('active').addClass('underline').removeClass('text-gray-900');
    } else {
        $($thisElement).addClass('active').addClass('text-blue-500').removeClass('text-gray-900');
    }
});

$('#order-status-filter').on('change', function () {
    // eslint-disable-next-line no-undef
    const selectedStatusIds = $('#order-status-filter').val();

    // eslint-disable-next-line no-undef
    loadOrderManagementTable(selectedStatusIds);
});
