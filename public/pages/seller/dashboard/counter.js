/* eslint-disable no-undef */
$(document).ready(function () {
    fetchOrdersTodayCount();
    fetchOrdersToProcessCount();
    fetchShipmentToShipCount();
    fetchLowStockCount();
    fetchOutOfStockCount();
    fetchDefectStockCount();
});

const fetchOrdersTodayCount = () => {
    $.ajax({
        type: 'GET',
        url: route('dashboard.counter.orders-today'),
        success: function (response) {
            const data = response.data;

            $('#__countOrdersToday').html(thousandFormat(data.orders_today));
        }
    });
};

const fetchOrdersToProcessCount = () => {
    $.ajax({
        type: 'GET',
        url: route('dashboard.counter.orders-to-process'),
        success: function (response) {
            const data = response.data;

            $('#__countOrdersToProcess').html(thousandFormat(data.orders_to_process));
        }
    });
};

const fetchShipmentToShipCount = () => {
    $.ajax({
        type: 'GET',
        url: route('dashboard.counter.shipment-to-ship'),
        success: function (response) {
            const data = response.data;

            $('#__countShipmentToShip').html(thousandFormat(data.shipment_to_ship));
        }
    });
};

const fetchLowStockCount = () => {
    $.ajax({
        type: 'GET',
        url: route('dashboard.counter.low-stock'),
        success: function (response) {
            const data = response.data;

            $('#__countLowStock').html(thousandFormat(data.low_stock));
        }
    });
};

const fetchOutOfStockCount = () => {
    $.ajax({
        type: 'GET',
        url: route('dashboard.counter.out-of-stock'),
        success: function (response) {
            const data = response.data;

            $('#__countOutOfStock').html(thousandFormat(data.out_of_stock));
        }
    });
};

const fetchDefectStockCount = () => {
    $.ajax({
        type: 'GET',
        url: route('dashboard.counter.defect-stock'),
        success: function (response) {
            const data = response.data;

            $('#__countDefectStock').html(thousandFormat(data.defect_stock));
        }
    });
};
