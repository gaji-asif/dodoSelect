/* eslint-disable no-undef */
const filterTable = {
    shop_id: null
};

const loadTable = (filterTable) => {
    $('#__shopeeIncomeTable').DataTable({
        serverSide: true,
        processing: true,
        bDestroy: true,
        ajax: {
            type: 'GET',
            url: route('shopee-income.datatable'),
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
                data: 'actions',
                name: 'actions',
                searchable: false,
                orderable: false
            }
        ],
        lengthMenu: [
            [50, 100],
            [50, 100]
        ]
    });
};

loadTable();

// $('#__filterDate').flatpickr({
//     wrap: true,
//     mode: 'range',
//     disableMobile: true,
//     onChange: function (selectedDates, dateStr, instance) {
//         if (selectedDates.length === 1) {
//             instance.config.minDate = moment(dateStr).subtract(30, 'days').format('YYYY-MM-DD');
//             instance.config.maxDate = moment(dateStr).add(30, 'days').format('YYYY-MM-DD');
//         } else if (selectedDates.length === 2) {
//             const splittedDate = dateStr.split(' to ');
//             filterTable.date_from = splittedDate[0];
//             filterTable.date_to = splittedDate[1];
//             loadTable(filterTable);
//         }
//     },
//     onClose: function (selectedDates, dateStr, instance) {
//         instance.config.minDate = null;
//         instance.config.maxDate = null;
//     }
// });

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

const showIncome = el => {
    const id = el.getAttribute('data-id');

    $.ajax({
        type: 'GET',
        url: route('shopee-income.show', { id: id }),
        success: function (response) {
            const income = response.data.income;

            $('#__shop_nameDetails').html(income.shopee.shop_name);
            $('#__ordersnDetails').html(income.ordersn);
            $('#__buyer_user_nameDetails').html(income.buyer_user_name);
            $('#__escrow_amountDetails').html(income.escrow_amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__buyer_total_amountDetails').html(income.buyer_total_amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__original_priceDetails').html(income.original_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__seller_discountDetails').html(income.seller_discount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__shopee_discountDetails').html(income.shopee_discount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__voucher_from_sellerDetails').html(income.voucher_from_seller.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__voucher_from_shopeeDetails').html(income.voucher_from_shopee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__coinsDetails').html(income.coins.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__buyer_paid_shipping_feeDetails').html(income.buyer_paid_shipping_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__buyer_transaction_feeDetails').html(income.buyer_transaction_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__cross_border_taxDetails').html(income.cross_border_tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__payment_promotionDetails').html(income.payment_promotion.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__commission_feeDetails').html(income.commission_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__service_feeDetails').html(income.service_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__seller_transaction_feeDetails').html(income.seller_transaction_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__seller_lost_compensationDetails').html(income.seller_lost_compensation.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__seller_coin_cash_backDetails').html(income.seller_coin_cash_back.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__escrow_taxDetails').html(income.escrow_tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__final_shipping_feeDetails').html(income.final_shipping_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__actual_shipping_feeDetails').html(income.actual_shipping_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__shopee_shipping_rebateDetails').html(income.shopee_shipping_rebate.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__shipping_fee_discount_from_3plDetails').html(income.shipping_fee_discount_from_3pl.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__seller_shipping_discountDetails').html(income.seller_shipping_discount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__estimated_shipping_feeDetails').html(income.estimated_shipping_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__seller_voucher_codeDetails').html(income.seller_voucher_code.join(','));
            $('#__drc_adjustable_refundDetails').html(income.drc_adjustable_refund.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__escrow_amount_affDetails').html(income.escrow_amount_aff.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__escrow_amount_affDetails').html(income.escrow_amount_aff.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__exchange_rateDetails').html(income.exchange_rate.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__local_currencyDetails').html(income.local_currency.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__escrow_currencyDetails').html(income.escrow_currency.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__reverse_shipping_feeDetails').html(income.reverse_shipping_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));

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

$('.__btnCloseModalDetails').on('click', function () {
    $('#__modalDetails').doModal('hide');
});
