/* eslint-disable no-undef */
const filterTable = {
    shop_id: null,
    date_from: moment().subtract(30, 'days').format('YYYY-MM-DD'),
    date_to: moment().format('YYYY-MM-DD'),
    status: null
};

$('#__summaryDateFrom').html(moment(filterTable.date_from).format('DD MMM YYYY'));
$('#__summaryDateTo').html(moment(filterTable.date_to).format('DD MMM YYYY'));

const fetchSummary = () => {
    $.ajax({
        type: 'GET',
        url: route('shopee-order.summary'),
        data: filterTable,
        success: function (response) {
            const orderSummary = response.data.order_summary;
            const shop = response.data.shop;

            $('#__summaryShopName').html(textAll);
            if (shop !== null) {
                $('#__summaryShopName').html(shop.shop_name);
            }

            $('#__summaryDateFrom').html(moment(filterTable.date_from).format('DD MMM YYYY'));
            $('#__summaryDateTo').html(moment(filterTable.date_to).format('DD MMM YYYY'));
            $('#__summaryAmountTotal').html(orderSummary.amount_total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        }
    });
};

const loadTable = (filterTable) => {
    $('#__shopeeOrderTable').DataTable({
        serverSide: true,
        processing: true,
        bDestroy: true,
        ajax: {
            type: 'GET',
            url: route('shopee-order.datatable'),
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
        ]
    });
};

loadTable(filterTable);

$('#__shopeeOrderTable').DataTable().on('draw', function () {
    fetchSummary();
});

$('#__filterTableWrapper').flatpickr({
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
    filterTable.status = null;

    loadTable(filterTable);

    $('#__filterShop').val('');
    $('#__filterStatus').val('');
});

$('#__filterShop').on('change', function () {
    filterTable.shop_id = $(this).val();

    loadTable(filterTable);
});

$('#__filterStatus').on('change', function () {
    filterTable.status = $(this).val();

    loadTable(filterTable);
});

const showOrder = el => {
    const id = el.getAttribute('data-id');

    $.ajax({
        type: 'GET',
        url: route('shopee-order.show', { id: id }),
        success: function (response) {
            const order = response.data.order;
            const orderDate = moment(order.order_date).format('DD/MM/YYYY hh:mm a');
            const shippingLine = JSON.parse(order.shipping_lines);
            const billing = JSON.parse(order.billing);
            const shipping = JSON.parse(order.shipping);
            const income = order.shopee_income;

            $('#__shop_nameDetails').html(order.shopee.shop_name);
            $('#__order_idDetails').html(order.order_id);
            $('#__order_dateDetails').html(orderDate);
            $('#__total_amountDetails').html(order.total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
            $('#__payment_method_titleDetails').html(order.payment_method_title);
            $('#__statusDetails').html(order.status);

            $('#__shipping_line_carrierDetails').html(shippingLine.shipping_carrier);
            $('#__shipping_line_serviceDetails').html(shippingLine.checkout_shipping_carrier);
            $('#__shipping_line_tracking_numberDetails').html(order.tracking_number);

            $('#__billing_nameDetails').html(billing.name);
            $('#__billing_phoneDetails').html(billing.phone);
            $('#__billing_addressDetails').html(billing.full_address);

            $('#__shipping_nameDetails').html(shipping.name);
            $('#__shipping_phoneDetails').html(shipping.phone);
            $('#__shipping_addressDetails').html(shipping.full_address);

            let incomeBuyerTotalAmount = 0;
            if (typeof (income.buyer_total_amount) !== 'undefined') {
                incomeBuyerTotalAmount = income.buyer_total_amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            let incomeEscrowAmount = 0;
            if (typeof (income.escrow_amount) !== 'undefined') {
                incomeEscrowAmount = income.escrow_amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__income_buyer_total_amountDetails').html(`${incomeBuyerTotalAmount}`);
            $('#__income_escrow_amountDetails').html(`${incomeEscrowAmount}`);

            $('#__shopee_income_buyer_total_amountDetails').html(`${incomeBuyerTotalAmount}`);
            $('#__shopee_income_escrow_amountDetails').html(`${incomeEscrowAmount}`);

            let incomeOriginalPrice = 0;
            if (typeof (income.original_price) !== 'undefined') {
                incomeOriginalPrice = income.original_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_original_priceDetails').html(`${incomeOriginalPrice}`);

            let incomeSellerDiscount = 0;
            if (typeof (income.seller_discount) !== 'undefined') {
                incomeSellerDiscount = income.seller_discount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_seller_discountDetails').html(`${incomeSellerDiscount}`);

            let incomeShopeeDiscount = 0;
            if (typeof (income.shopee_discount) !== 'undefined') {
                incomeShopeeDiscount = income.shopee_discount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_shopee_discountDetails').html(`${incomeShopeeDiscount}`);

            let incomeVoucherFromSeller = 0;
            if (typeof (income.voucher_from_seller) !== 'undefined') {
                incomeVoucherFromSeller = income.voucher_from_seller.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_voucher_from_sellerDetails').html(`${incomeVoucherFromSeller}`);

            let incomeVoucherFromShopee = 0;
            if (typeof (income.voucher_from_shopee) !== 'undefined') {
                incomeVoucherFromShopee = income.voucher_from_shopee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_voucher_from_shopeeDetails').html(`${incomeVoucherFromShopee}`);

            let incomeCoins = 0;
            if (typeof (income.coins) !== 'undefined') {
                incomeCoins = income.coins.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_coinsDetails').html(`${incomeCoins}`);

            let incomeBuyerPaidShippingFee = 0;
            if (typeof (income.buyer_paid_shipping_fee) !== 'undefined') {
                incomeBuyerPaidShippingFee = income.buyer_paid_shipping_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_buyer_paid_shipping_feeDetails').html(`${incomeBuyerPaidShippingFee}`);

            let incomeBuyerTransactionFee = 0;
            if (typeof (income.buyer_transaction_fee) !== 'undefined') {
                incomeBuyerTransactionFee = income.buyer_transaction_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_buyer_transaction_feeDetails').html(`${incomeBuyerTransactionFee}`);

            let incomeCrossBorderTax = 0;
            if (typeof (income.cross_border_tax) !== 'undefined') {
                incomeCrossBorderTax = income.cross_border_tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_cross_border_taxDetails').html(`${incomeCrossBorderTax}`);

            let incomePaymentPromotion = 0;
            if (typeof (income.payment_promotion) !== 'undefined') {
                incomePaymentPromotion = income.payment_promotion.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_payment_promotionDetails').html(`${incomePaymentPromotion}`);

            let incomeCommissionFee = 0;
            if (typeof (income.commission_fee) !== 'undefined') {
                incomeCommissionFee = income.commission_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_commission_feeDetails').html(`${incomeCommissionFee}`);

            let incomeServiceFee = 0;
            if (typeof (income.service_fee) !== 'undefined') {
                incomeServiceFee = income.service_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_service_feeDetails').html(`${incomeServiceFee}`);

            let incomeSellerTransactionFee = 0;
            if (typeof (income.seller_transaction_fee) !== 'undefined') {
                incomeSellerTransactionFee = income.seller_transaction_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_seller_transaction_feeDetails').html(`${incomeSellerTransactionFee}`);

            let incomeSellerLostCompensation = 0;
            if (typeof (income.seller_lost_compensation) !== 'undefined') {
                incomeSellerLostCompensation = income.seller_lost_compensation.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_seller_lost_compensationDetails').html(`${incomeSellerLostCompensation}`);

            let incomeSellerCoinCashback = 0;
            if (typeof (income.seller_coin_cash_back) !== 'undefined') {
                incomeSellerCoinCashback = income.seller_coin_cash_back.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_seller_coin_cash_backDetails').html(`${incomeSellerCoinCashback}`);

            let incomeEscrowTax = 0;
            if (typeof (income.escrow_tax) !== 'undefined') {
                incomeEscrowTax = income.escrow_tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_escrow_taxDetails').html(`${incomeEscrowTax}`);

            let incomeFinalShippingFee = 0;
            if (typeof (income.final_shipping_fee) !== 'undefined') {
                incomeFinalShippingFee = income.final_shipping_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_final_shipping_feeDetails').html(`${incomeFinalShippingFee}`);

            let incomeActualShippingFee = 0;
            if (typeof (income.actual_shipping_fee) !== 'undefined') {
                incomeActualShippingFee = income.actual_shipping_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_actual_shipping_feeDetails').html(`${incomeActualShippingFee}`);

            let incomeShopeeShippingRebate = 0;
            if (typeof (income.shopee_shipping_rebate) !== 'undefined') {
                incomeShopeeShippingRebate = income.shopee_shipping_rebate.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_shopee_shipping_rebateDetails').html(`${incomeShopeeShippingRebate}`);

            let incomeShippingFeeDiscountFrom3pl = 0;
            if (typeof (income.shipping_fee_discount_from_3pl) !== 'undefined') {
                incomeShippingFeeDiscountFrom3pl = income.shipping_fee_discount_from_3pl.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_shipping_fee_discount_from_3plDetails').html(`${incomeShippingFeeDiscountFrom3pl}`);

            let incomeSellerShippingDiscount = 0;
            if (typeof (income.seller_shipping_discount) !== 'undefined') {
                incomeSellerShippingDiscount = income.seller_shipping_discount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_seller_shipping_discountDetails').html(`${incomeSellerShippingDiscount}`);

            let incomeEstimatedShippingFee = 0;
            if (typeof (income.estimated_shipping_fee) !== 'undefined') {
                incomeEstimatedShippingFee = income.estimated_shipping_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_estimated_shipping_feeDetails').html(`${incomeEstimatedShippingFee}`);

            let incomeSellerVoucherCode = '-';
            if (typeof (income.seller_voucher_code) !== 'undefined') {
                incomeSellerVoucherCode = income.seller_voucher_code.join('<br/>');
            }

            $('#__shopee_income_seller_voucher_codeDetails').html(`${incomeSellerVoucherCode}`);

            let incomeDrcAdjustableRefund = 0;
            if (typeof (income.drc_adjustable_refund) !== 'undefined') {
                incomeDrcAdjustableRefund = income.drc_adjustable_refund.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_drc_adjustable_refundDetails').html(`${incomeDrcAdjustableRefund}`);

            let incomeEscrowAmountAff = 0;
            if (typeof (income.escrow_amount_aff) !== 'undefined') {
                incomeEscrowAmountAff = income.escrow_amount_aff.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_escrow_amount_affDetails').html(`${incomeEscrowAmountAff}`);

            let incomeExchangeRate = 0;
            if (typeof (income.exchange_rate) !== 'undefined') {
                incomeExchangeRate = income.exchange_rate.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_exchange_rateDetails').html(`${incomeExchangeRate}`);

            let incomeLocalCurrency = 0;
            if (typeof (income.local_currency) !== 'undefined') {
                incomeLocalCurrency = income.local_currency.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_local_currencyDetails').html(`${incomeLocalCurrency}`);

            let incomeEscrowCurrency = 0;
            if (typeof (income.escrow_currency) !== 'undefined') {
                incomeEscrowCurrency = income.escrow_currency.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_escrow_currencyDetails').html(`${incomeEscrowCurrency}`);

            let incomeReverseShippingFee = 0;
            if (typeof (income.reverse_shipping_fee) !== 'undefined') {
                incomeReverseShippingFee = income.reverse_shipping_fee.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            $('#__shopee_income_reverse_shipping_feeDetails').html(`${incomeReverseShippingFee}`);

            let incomeReturnSnList = '-';
            if (typeof (income.returnsn_list) !== 'undefined') {
                incomeReturnSnList = income.returnsn_list.join('<br/>');
            }

            $('#__shopee_income_returnsn_listDetails').html(`${incomeReturnSnList}`);

            let incomeRefunIdList = '-';
            if (typeof (income.refund_id_list) !== 'undefined') {
                incomeRefunIdList = income.refund_id_list.join('<br/>');
            }

            $('#__shopee_income_refund_id_listDetails').html(`${incomeRefunIdList}`);

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
