/* eslint-disable camelcase */
/* eslint-disable no-undef */
const filterTable = {
    shop_id: null,
    date_from: moment().subtract(30, 'days').format('YYYY-MM-DD'),
    date_to: moment().format('YYYY-MM-DD')
};

$('#__summaryDateFrom').html(moment(filterTable.date_from).format('DD MMM YYYY'));
$('#__summaryDateTo').html(moment(filterTable.date_to).format('DD MMM YYYY'));

const fetchSummary = () => {
    $.ajax({
        type: 'GET',
        url: route('shopee-transaction.summary'),
        data: filterTable,
        success: function (response) {
            const transaction_summary = response.data.transaction_summary;
            const shop = response.data.shop;

            $('#__summaryShopName').html(textAll);
            if (shop !== null) {
                $('#__summaryShopName').html(shop.shop_name);
            }

            $('#__summaryDateFrom').html(moment(filterTable.date_from).format('DD MMM YYYY'));
            $('#__summaryDateTo').html(moment(filterTable.date_to).format('DD MMM YYYY'));
            $('#__summaryAmountTotal').html(transaction_summary.amount_total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__summaryTransactionFeeTotal').html(transaction_summary.transaction_fee_total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__summaryWalletBalanceAmount').html(transaction_summary.wallet_balance.amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__summaryWalletBalanceDate').html(moment(transaction_summary.wallet_balance.datetime).format('DD MMM YYYY'));
        }
    });
};

const loadTable = (filterTable) => {
    $('#__shopeeTransactionTable').DataTable({
        serverSide: true,
        processing: true,
        bDestroy: true,
        ajax: {
            type: 'GET',
            url: route('shopee-transaction.datatable'),
            data: filterTable
        },
        columns: [
            {
                data: 'detail',
                name: 'detail',
                searchable: false,
                orderable: true
            },
            {
                data: 'col_amount',
                name: 'col_amount',
                searchable: false,
                orderable: true
            },
            {
                data: 'actions',
                name: 'actions',
                searchable: false,
                orderable: false
            }
        ],
        lengthMenu: [
            [50, 100],
            [50, 100]
        ],
        order: [[0, 'desc']]
    });
};

loadTable(filterTable);

$('#__shopeeTransactionTable').DataTable().on('draw', function () {
    fetchSummary();
});

$('#__filterDate').flatpickr({
    wrap: true,
    mode: 'range',
    disableMobile: true,
    defaultDate: [filterTable.date_from, filterTable.date_to],
    onChange: function (selectedDates, dateStr, instance) {
        if (selectedDates.length === 1) {
            instance.config.minDate = moment(dateStr).subtract(30, 'days').format('YYYY-MM-DD');
            instance.config.maxDate = moment(dateStr).add(30, 'days').format('YYYY-MM-DD');
        } else if (selectedDates.length === 2) {
            const splittedDate = dateStr.split(' to ');
            filterTable.date_from = splittedDate[0];
            filterTable.date_to = splittedDate[1];
            loadTable(filterTable);
        }
    },
    onClose: function (selectedDates, dateStr, instance) {
        instance.config.minDate = null;
        instance.config.maxDate = null;
    }
});

$('#__filterBtnReset').on('click', function () {
    filterTable.shop_id = null;
    filterTable.date_from = null;
    filterTable.date_to = null;

    loadTable(filterTable);

    $('#__filterShop').val('');
});

$('#__filterShop').on('change', function () {
    filterTable.shop_id = $(this).val();

    loadTable(filterTable);
});

const showTransaction = el => {
    const id = el.getAttribute('data-id');

    $.ajax({
        type: 'GET',
        url: route('shopee-transaction.show', { id: id }),
        success: function (response) {
            const transaction = response.data.transaction;
            const timestamp = new Date(transaction.create_time * 1000);

            $('#__shop_nameDetails').html(transaction.shopee.shop_name);
            $('#__timestampDetails').html(moment(timestamp).format('DD/MM/YYYY hh:mm a'));
            $('#__order_snDetails').html(transaction.ordersn);
            $('#__refund_snDetails').html(transaction.refund_sn);
            $('#__buyer_nameDetails').html(transaction.buyer_name);
            $('#__statusDetails').html(transaction.status);
            $('#__amountDetails').html(transaction.amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__transaction_feeDetails').html(transaction.transaction_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__current_balanceDetails').html(transaction.current_balance.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__wallet_typeDetails').html(transaction.wallet_type);
            $('#__transaction_idDetails').html(transaction.transaction_id);
            $('#__transaction_typeDetails').html(transaction.transaction_type);
            $('#__descriptionDetails').html(transaction.description);
            $('#__reasonDetails').html(transaction.reason);

            $('#__modalDetails').doModal('show');

            modalBackToTop();
        },
        error: function (error) {
            const response = error.responseJSON;
            const alertMessage = response.message;

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: alertMessage,
                toast: true,
                position: 'top-end',
                showConfirmButton: true
            });
        }
    });
};

$('#__btnCloseModalDetails').on('click', function () {
    $('#__modalDetails').doModal('hide');
});

const syncStatusCheck = () => {
    $.ajax({
        type: 'GET',
        url: route('shopee-transaction.sync-status'),
        success: function (response) {
            const syncData = response.data.sync;

            $('#__alertInfoSyncingData').addClass('hidden');
            if (syncData.is_processing) {
                $('#__alertInfoSyncingData').removeClass('hidden');
            }
        },
        error: function (err) {
            const response = err.responseJSON;
            const alertMessage = response.message;

            if (err.status === 401) {
                alert(alertMessage);
                window.location.href = route('signin');
                return;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: alertMessage,
                toast: true,
                position: 'top-end',
                showConfirmButton: true
            });
        }
    });
};

setInterval(() => {
    syncStatusCheck();
}, 10000);

syncStatusCheck();
