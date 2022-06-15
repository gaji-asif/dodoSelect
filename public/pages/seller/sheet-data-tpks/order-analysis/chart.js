/* eslint-disable no-undef */

const options = {
    chart: {
        type: 'line',
        height: 400
    },
    stroke: {
        width: [0, 4]
    },
    series: [],
    xaxis: {
        categories: []
    },
    yaxis: [
        {
            title: {
                text: 'Total Amount (Baht)'
            }
        },
        {
            opposite: true,
            title: {
                text: 'Total Orders'
            }
        }
    ],
    legend: {
        show: true,
        position: 'top'
    },
    noData: {
        text: 'Loading...'
    }
};

const orderAnalysisChart = new ApexCharts(document.querySelector('#order-analysis-chart'), options);
orderAnalysisChart.render();

const loadChartData = (filterData) => {
    $.ajax({
        type: 'GET',
        url: route('sheet-data-tpks.order-analysis-chart'),
        data: filterData,
        success: function (response) {
            const chartData = response.data.data;

            const dateDataset = [];
            const totalAmountDataset = [];
            const totalOrdersDataset = [];

            chartData.map(item => {
                dateDataset.push(item.str_date);
                totalAmountDataset.push(Math.round(item.total_amount * 100) / 100);
                totalOrdersDataset.push(parseInt(item.total_orders));
            });

            orderAnalysisChart.updateOptions({
                xaxis: {
                    categories: dateDataset
                }
            });

            orderAnalysisChart.updateSeries([
                {
                    name: 'Total Amount',
                    type: 'column',
                    data: totalAmountDataset
                },
                {
                    name: 'Total Orders',
                    type: 'line',
                    data: totalOrdersDataset
                }
            ]);
        }
    });
};

loadChartData(tableFilter);
