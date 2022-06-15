$('.order-status-filter__tab').on('shown.bs.tab', function (event) {
    const $thisElement = $(event.target);
    const parentStatusId = $thisElement.data('id');
    const orderStatusId = $thisElement.data('sub-status-id');
    const orderStatusType = $thisElement.data('status-type');
    const shopId = $('#__btnShopFilterLazada').val();

    localStorage.setItem('lazada_selected_parent_status_id', parentStatusId);
    localStorage.setItem('lazada_selected_order_status_type', orderStatusType);
    localStorage.setItem('lazada_selecte_shop_id', shopId);
    if (parentStatusId == "P1") {
        localStorage.setItem('lazada_selected_status_ids', "PROCESSING");
    } else if (parentStatusId == "P2") {
        localStorage.setItem('lazada_selected_status_ids', "NOT_PRINTED");
    } else if (parentStatusId == "P3") {
        localStorage.setItem('lazada_selected_status_ids', "TO_PAY");
    } else if (parentStatusId == "P4") {
        localStorage.setItem('lazada_selected_status_ids', "CANCELED");
    } else if (parentStatusId == "P5") {
        localStorage.setItem('lazada_selected_status_ids', "COMPLETED");
    } else {
        localStorage.setItem('lazada_selected_status_ids', "");
    }

    selectedStatusIds = orderStatusId;

    toggelLazadaShippingMethodFilter(selectedStatusIds);

    loadOrderStatusList(parentStatusId, shopId, true);

    loadOrderManagementTable(orderStatusId, shopId);

    updateClassesForLazadaTopNavbar(orderStatusType, parentStatusId);

    $("#searchbar").val("");
});

const updateClassesForLazadaTopNavbar = (orderStatusType="top", parentStatusId="P1") => {
    $('.top-status-filter__tab').addClass('text-white').removeClass('underline').removeClass('active');
    $('.secondary-status-filter__tab').addClass('text-gray-900').removeClass('text-blue-500').removeClass('active');
    if (orderStatusType === 'top') {
        $(".top-status-filter__tab-"+parentStatusId).addClass('active').addClass('underline').removeClass('text-gray-900');
    } else {
        $(".secondary-status-filter__tab-"+parentStatusId).addClass('active').addClass('text-blue-500').removeClass('text-gray-900');
    }
    $("#searchbar").val("");
}

$('#order-status-filter').on('change', function () {
    $("#searchbar").val("");
    storeSelectedShopAndStatusFilterInfo();

    // eslint-disable-next-line no-undef
    const selectedStatusIds = $(this).val();
    const shopId = $('#__btnShopFilterLazada').val();

    toggelLazadaShippingMethodFilter(selectedStatusIds);

    // eslint-disable-next-line no-undef
    loadOrderManagementTable(selectedStatusIds, shopId, -1, getAdditionalData());
});

$('#lazada_shipment_method_filter').on('change', function () {
    $("#searchbar").val("");
    
    const selectedStatusIds = $('#order-status-filter').val();
    const shopId = $('#__btnShopFilterLazada').val();
    const shippingMethod = $(this).val();

    // eslint-disable-next-line no-undef
    loadOrderManagementTable(selectedStatusIds, shopId, -1, getAdditionalData());
});

const reloadOrderStatusList = (reloadShopWiseStatusCount=false) => {
    const parentStatusId = $('.order-status-filter__tab.active').data('id');
    const shopId = $('#__btnShopFilterLazada').val();
    loadOrderStatusList(parentStatusId, shopId, reloadShopWiseStatusCount);   
}

const storeSelectedShopAndStatusFilterInfo = () => {
    const parentStatusId = $('.order-status-filter__tab.active').data('id');
    const orderStatusType = $('.order-status-filter__tab.active').data('status-type');
    const selectedStatusIds = $('#order-status-filter').val();
    const shopId = $('#__btnShopFilterLazada').val();
    localStorage.setItem('lazada_selected_parent_status_id', parentStatusId);
    localStorage.setItem('lazada_selected_order_status_type', orderStatusType);
    localStorage.setItem('lazada_selected_status_ids', selectedStatusIds);
    localStorage.setItem('lazada_selecte_shop_id', shopId);
}

const getSelectedShopAndStatusFilterInfo = () => {
    return {
        "selectedParentStatusId":localStorage.getItem('lazada_selected_parent_status_id'),
        "selectedOrderStatusType":localStorage.getItem('lazada_selected_order_status_type'),
        "selectedStatusIds":localStorage.getItem('lazada_selected_status_ids'),
        "selectedShopId":localStorage.getItem('lazada_selecte_shop_id')
    }
}

const getAdditionalData = () => {
    const selectedStatusIds = $("#order-status-filter").find("option:selected").val();
    let additionalData = [];
    if (selectedStatusIds.toLowerCase() === "processing") {
        additionalData["derived_status"] = $("#lazada_processing_status_filter").find("option:selected").val();
    }
    return additionalData;
}

$('#lazada_processing_status_filter').on('change', function () {
    $("#searchbar").val("");
    storeSelectedShopAndStatusFilterInfo();

    // eslint-disable-next-line no-undef
    const selectedStatusIds = $("#order-status-filter").find("option:selected").val();
    const shopId = $('#__btnShopFilterLazada').val();

    toggelLazadaShippingMethodFilter(selectedStatusIds);

    // eslint-disable-next-line no-undef
    loadOrderManagementTable(selectedStatusIds, shopId, -1, getAdditionalData());
});