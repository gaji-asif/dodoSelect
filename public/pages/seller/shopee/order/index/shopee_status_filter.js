$('.order-status-filter__tab').on('shown.bs.tab', function (event) {
    const $thisElement = $(event.target);
    const parentStatusId = $thisElement.data('id');
    const orderStatusId = $thisElement.data('sub-status-id');
    const orderStatusType = $thisElement.data('status-type');
    const shopId = $('#__btnShopFilter').val();

    localStorage.setItem('shopee_selected_parent_status_id', parentStatusId);
    localStorage.setItem('shopee_selected_order_status_type', orderStatusType);
    localStorage.setItem('shopee_selecte_shop_id', shopId);
    if (parentStatusId == "P1") {
        localStorage.setItem('shopee_selected_status_ids', "PROCESSING");
    } else if (parentStatusId == "P2") {
        localStorage.setItem('shopee_selected_status_ids', "NOT_PRINTED");
    } else if (parentStatusId == "P3") {
        localStorage.setItem('shopee_selected_status_ids', "TO_PAY");
    } else if (parentStatusId == "P4") {
        localStorage.setItem('shopee_selected_status_ids', "CANCELLED");
    } else if (parentStatusId == "P5") {
        localStorage.setItem('shopee_selected_status_ids', "COMPLETED");
    } else {
        localStorage.setItem('shopee_selected_status_ids', "");
    }

    $("#searchbar").val("");
    
    selectedStatusIds = orderStatusId;

    toggelShopeeShippingMethodFilter(selectedStatusIds);

    loadOrderStatusListForShopee(parentStatusId, shopId, true);

    updateClassesForShopeeTopNavbar(orderStatusType, parentStatusId);

    setTimeout(function() {
        loadOrderManagementTable(orderStatusId, shopId);
    }, 2000);
});

const updateClassesForShopeeTopNavbar = (orderStatusType="top", parentStatusId="P1") => {
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
    const shopId = $('#__btnShopFilter').val();

    toggelShopeeShippingMethodFilter(selectedStatusIds);

    // eslint-disable-next-line no-undef
    loadOrderManagementTable(selectedStatusIds, shopId);
});

$('#shopee_shipment_method_filter').on('change', function () {
    $("#searchbar").val("");
    
    const selectedStatusIds = $('#order-status-filter').val();
    const shopId = $('#__btnShopFilter').val();
    const shippingMethod = $(this).val();

    // eslint-disable-next-line no-undef
    loadOrderManagementTable(selectedStatusIds, shopId, shippingMethod);
});

const reloadOrderStatusListForShopee = (reloadShopWiseStatusCount=false) => {
    const parentStatusId = $('.order-status-filter__tab.active').data('id');
    const shopId = $('#__btnShopFilter').val();
    loadOrderStatusListForShopee(parentStatusId, shopId, reloadShopWiseStatusCount);   
}

const storeSelectedShopAndStatusFilterInfo = () => {
    const parentStatusId = $('.order-status-filter__tab.active').data('id');
    const orderStatusType = $('.order-status-filter__tab.active').data('status-type');
    const selectedStatusIds = $('#order-status-filter').val();
    const shopId = $('#__btnShopFilter').val();
    localStorage.setItem('shopee_selected_parent_status_id', parentStatusId);
    localStorage.setItem('shopee_selected_order_status_type', orderStatusType);
    localStorage.setItem('shopee_selected_status_ids', selectedStatusIds);
    localStorage.setItem('shopee_selecte_shop_id', shopId);
}

const getSelectedShopAndStatusFilterInfoForShopee = () => {
    return {
        "selectedParentStatusId":localStorage.getItem('shopee_selected_parent_status_id'),
        "selectedOrderStatusType":localStorage.getItem('shopee_selected_order_status_type'),
        "selectedStatusIds":localStorage.getItem('shopee_selected_status_ids'),
        "selectedShopId":localStorage.getItem('shopee_selecte_shop_id')
    }
}

const updateSelectedShopAndStatusFilterHtmlAfterBatchInitForShopee = () => {
    let parentStatusId = "P2";
    let statusIds = "NOT_PRINTED";
    let shopId = 0;
    let orderStatusType = "top";
    localStorage.setItem('shopee_selected_parent_status_id', parentStatusId);
    localStorage.setItem('shopee_selected_order_status_type', orderStatusType);
    localStorage.setItem('shopee_selected_status_ids', statusIds);
    localStorage.setItem('shopee_selecte_shop_id', shopId);

    updateClassesForShopeeTopNavbar(orderStatusType, parentStatusId);
    loadOrderStatusListForShopee(parentStatusId, shopId, true);

    toggelShopeeShippingMethodFilter(statusIds);

    loadOrderManagementTable(statusIds, shopId);

    let target = $("div#preloader");
    if (typeof(target) !== "undefined" && target !== null) {
        target.css("display", "block");
        setTimeout(function() {
            target.css("display", "none");
        }, 2500);
    }
}