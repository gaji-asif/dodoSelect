/* eslint-disable no-undef */
const tableFilter = {
    shop_id: -1,
    date_range: `${moment().format('YYYY-01-01')} to ${moment().format('YYYY-12-31')}`,
    interval: $('#__intervalFilter').val(),
    channel: $('#__channelFilter').val()
};

$('#__shopFilter').select2({
    ajax: {
        type: 'GET',
        url: route('shop.select'),
        data: function (params) {
            return {
                page: params.page || 1,
                search: params.term,
                extends: {
                    options: [
                        {
                            id: '-1',
                            text: '- All Shops -'
                        }
                    ]
                }
            };
        },
        delay: 500
    }
});

$('#__date_rangeFilter').flatpickr({
    mode: 'range',
    disableMobile: true,
    // defaultDate: [moment().subtract(30, 'days').format('YYYY-MM-DD'), moment().format('YYYY-MM-DD')],
    onChange: function (selectedDates, dateStr, instance) {
        if (selectedDates.length === 1) {
            instance.config.minDate = moment(dateStr).subtract(5, 'years').format('YYYY-MM-DD');
            instance.config.maxDate = moment(dateStr).add(5, 'years').format('YYYY-MM-DD');
        } else if (selectedDates.length === 2) {
            tableFilter.date_range = dateStr;
            loadOrderAnalysisTable(tableFilter);
            loadChartData(tableFilter);
        }
    },
    onClose: function (selectedDates, dateStr, instance) {
        instance.config.minDate = null;
        instance.config.maxDate = null;
    }
});

const loadOrderAnalysisTable = filter => {
    $('#__orderAnalysisTable').DataTable({
        bDestroy: true,
        searching: false,
        ordering: false,
        serverSide: true,
        processing: true,
        ajax: {
            type: 'GET',
            url: route('sheet-data-tpks.order-analysis-datatable'),
            data: filter
        },
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                searchable: false,
                orderable: false
            },
            {
                data: 'str_shop_name',
                name: 'str_shop_name',
                searchable: false,
                orderable: false
            },
            {
                data: 'str_date',
                name: 'str_date',
                searchable: false,
                orderable: false
            },
            {
                data: 'total_orders',
                name: 'total_orders',
                searchable: false,
                orderable: false
            },
            {
                data: 'total_amount',
                name: 'total_amount',
                searchable: false,
                orderable: false
            }
        ],
        lengthMenu: [
            [100], [100]
        ]
    });
};

loadOrderAnalysisTable(tableFilter);

$('#__shopFilter').on('select2:select', function (e) {
    const selected = e.params.data;
    tableFilter.shop_id = selected.id;

    loadOrderAnalysisTable(tableFilter);
    loadChartData(tableFilter);
});

$('#__intervalFilter').on('change', function () {
    tableFilter.interval = $(this).val();
    loadOrderAnalysisTable(tableFilter);
    loadChartData(tableFilter);
});

$('#__channelFilter').on('change', function () {
    tableFilter.channel = $(this).val();
    loadOrderAnalysisTable(tableFilter);
    loadChartData(tableFilter);
});
