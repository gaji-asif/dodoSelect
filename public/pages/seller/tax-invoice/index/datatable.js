$('#__taxInvoiceTable').DataTable({
    serverSide: true,
    processing: true,
    ajax: {
        type: 'GET',
        // eslint-disable-next-line no-undef
        url: taxInvoiceTableUrl
    },
    columns: [
        {
            name: 'DT_RowIndex',
            data: 'DT_RowIndex',
            orderable: false,
            searchable: false
        },
        {
            name: 'order_tax_id',
            data: 'order_tax_id'
        },
        {
            name: 'company_info',
            data: 'company_info'
        },
        {
            name: 'order_date',
            data: 'order_date'
        },
        {
            name: 'str_in_total',
            data: 'str_in_total'
        },
        {
            name: 'action',
            data: 'action',
            orderable: false,
            searchable: false
        }
    ]
});
