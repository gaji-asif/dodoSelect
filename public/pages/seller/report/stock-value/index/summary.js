/* eslint-disable no-undef */
$(document).ready(function () {
    fetchStockSummary();
});

const fetchStockSummary = () => {
    $.ajax({
        type: 'GET',
        url: route('report.stock-value.summary'),
        success: function (response) {
            const data = response.data;

            $('#__summaryTotalStockValue').html(thousandFormat(data.total_stock_value));
            $('#__summaryTotalStockCostValue').html(thousandFormat(data.total_stock_cost_value));
        }
    });
};
