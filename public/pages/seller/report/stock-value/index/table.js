/* eslint-disable no-undef */
$('#__stockValueTable').DataTable({
    serverSide: true,
    processing: true,
    ajax: {
        type: 'GET',
        url: route('report.stock-value.datatable')
    },
    columns: [
        {
            name: 'id',
            data: 'id',
            searchable: false
        },
        {
            name: 'product_image',
            data: 'product_image',
            orderable: false
        },
        {
            name: 'details',
            data: 'details',
            orderable: true
        },
        {
            name: 'actions',
            data: 'actions',
            orderable: false,
            searchable: false
        }
    ]
});

const sortStockValueTable = sortBy => {
    switch (sortBy) {
        case 'id__asc':
            $('#__stockValueTable').DataTable().order([0, 'asc']).draw();
            break;

        case 'id__desc':
            $('#__stockValueTable').DataTable().order([0, 'desc']).draw();
            break;

        case 'profit_margin__asc':
            $('#__stockValueTable').DataTable().order([1, 'asc']).draw();
            break;

        case 'profit_margin__desc':
            $('#__stockValueTable').DataTable().order([1, 'desc']).draw();
            break;

        case 'product_name__asc':
            $('#__stockValueTable').DataTable().order([2, 'asc']).draw();
            break;

        case 'product_name__desc':
            $('#__stockValueTable').DataTable().order([2, 'desc']).draw();
            break;

        case 'stock_value__asc':
            $('#__stockValueTable').DataTable().order([3, 'asc']).draw();
            break;

        case 'stock_value__desc':
            $('#__stockValueTable').DataTable().order([3, 'desc']).draw();
            break;

        default:
            $('#__stockValueTable').DataTable().order([0, 'asc']).draw();
            break;
    }
};

$('#__sortByToolbar').on('change', function () {
    sortStockValueTable($(this).val());
});

const showStockValue = (el) => {
    $.ajax({
        type: 'GET',
        url: route('report.stock-value.show', { id: el.getAttribute('data-id') }),
        success: function (response) {
            const product = response.data.product;

            let productQuantity = 0;
            if (typeof (product.product_main_stock.quantity) !== 'undefined') {
                productQuantity = thousandFormat(product.product_main_stock.quantity);
            }

            let supplierName = '-';
            if (typeof (product.preferred_product_cost.supplier) !== 'undefined') {
                supplierName = product.preferred_product_cost.supplier.supplier_name;
            }

            let productCostCost = 0;
            if (typeof (product.preferred_product_cost.cost) !== 'undefined') {
                productCostCost = thousandFormat(product.preferred_product_cost.cost);
            }

            let productCostExchangeName = '-';
            if (typeof (product.preferred_product_cost.exchange_rate) !== 'undefined') {
                productCostExchangeName = product.preferred_product_cost.exchange_rate.name;
            }

            let productCostExchangeRate = '-';
            if (typeof (product.preferred_product_cost.exchange_rate) !== 'undefined') {
                productCostExchangeRate = thousandFormat(product.preferred_product_cost.exchange_rate.rate);
            }

            let productCostPiecesPerPack = 0;
            if (typeof (product.preferred_product_cost.pieces_per_pack) !== 'undefined') {
                productCostPiecesPerPack = thousandFormat(product.preferred_product_cost.pieces_per_pack);
            }

            let productCostPiecesPerCarton = 0;
            if (typeof (product.preferred_product_cost.pieces_per_carton) !== 'undefined') {
                productCostPiecesPerCarton = thousandFormat(product.preferred_product_cost.pieces_per_carton);
            }

            let productCostOperationCost = 0;
            if (typeof (product.preferred_product_cost.operation_cost) !== 'undefined') {
                productCostOperationCost = thousandFormat(product.preferred_product_cost.operation_cost);
            }

            $('#__product_nameModalDetail').html(product.product_name);
            $('#__product_codeModalDetail').html(product.product_code);
            $('#__quantityModalDetail').html(productQuantity);
            $('#__priceModalDetail').html(thousandFormat(product.price));
            $('#__product_cost_supplier_nameModalDetail').html(supplierName);
            $('#__product_cost_costModalDetail').html(productCostCost);
            $('#__product_cost_exchange_nameModalDetail').html(productCostExchangeName);
            $('#__product_cost_exchange_rateModalDetail').html(productCostExchangeRate);
            $('#__product_cost_pieces_per_packModalDetail').html(productCostPiecesPerPack);
            $('#__product_cost_pieces_per_cartonModalDetail').html(productCostPiecesPerCarton);
            $('#__product_cost_operation_costModalDetail').html(productCostOperationCost);

            $('#__modalDetail').doModal('show');
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

$('.btn-close__modalDetail').on('click', function () {
    $('#__modalDetail').doModal('hide');
});

$('#__btnExportExcel').on('click', function () {
    window.location.href = route('report.stock-value.export-excel');
});
