/* eslint-disable no-undef */
$('body').on('change', '.select__shop', function () {
    selectedWebsiteId = $(this).val();
    loadDataTable(selectedWebsiteId, selectedInventoryStatus, selectedType, discount_range);
});

$('body').on('change', '.select__inventory-status', function () {
    selectedInventoryStatus = $(this).val();
    loadDataTable(selectedWebsiteId, selectedInventoryStatus, selectedType, discount_range);
});

$('body').on('change', '.select__type', function () {
    selectedType = $(this).val();
    loadDataTable(selectedWebsiteId, selectedInventoryStatus, selectedType, discount_range);
});

$('body').on('change', '.discount_range', function () {
    discount_range = $(this).val();
    loadDataTable(selectedWebsiteId, selectedInventoryStatus, selectedType, discount_range);
});

const loadDataTable = (websiteId = null, inventoryStatus = null, type = null, discount_range = null) => {
    $('#wooProductTable').DataTable({
        bDestroy: true,
        processing: true,
        serverSide: true,
        dom: '<"#dt-top-toolbar">frt<"#dt-bottom-toolbar"lip><"clear">',
        ajax: {
            type: 'GET',
            url: wooProductDatatableUrl,
            data: {
                website_id: websiteId,
                inventory_status: inventoryStatus,
                type: type,
                discount_range:discount_range
            }
        },
        initComplete: function () {
            $('#dt-top-toolbar').append(
                $('<div/>', {
                    class: 'flex flex-col sm:flex-row items-center gap-2 mb-4'
                }).append(
                    $('<div/>', {
                        class: 'flex flex-col sm:flex-row items-center gap-2'
                    }).append(
                        $('.btn__sync-selected').clone(),
                        $('.btn__sync-product').clone(),
                        $('.btn__export-excel-linked-catalog').clone()
                    )
                )
            );

            $('.btn__sync-selected').find('.btn__sync-selected__count').html(0);
        },
        columns: [
            {
                name: 'checkbox',
                data: 'checkbox',
                orderable: false,
                checkboxes: {
                    selectRow: true
                }
            },
            {
                name: 'woo_product_detail',
                data: 'woo_product_detail'
            },
            {
                name: 'action',
                data: 'action',
                orderable: false,
                className: 'text-center'
            }
        ],
        select: {
            style: 'multi'
        }
    });
};

loadDataTable();

const editProduct = (el) => {
    const detailUrl = el.getAttribute('data-detail-url');

    $.ajax({
        type: 'GET',
        url: detailUrl,
        beforeSend: function () {
            $('.alert').addClass('hidden').find('.alert-content').html(null);
        },
        success: function (response) {
            const wooProduct = response.data.woo_product;

            $('#__idEditProduct').val(wooProduct.id);
            $('#__idDisplayEditProduct').val(`#${wooProduct.id}`);
            $('#__product_nameEditProduct').val(wooProduct.product_name).attr('disabled', false).removeClass('bg-gray-200');
            $('#__product_codeEditProduct').val(wooProduct.product_code).attr('disabled', false).removeClass('bg-gray-200');
            $('#__priceEditProduct').val(wooProduct.price).attr('disabled', false).removeClass('bg-gray-200');
            $('#__quantityEditProduct').val(wooProduct.quantity).attr('disabled', false).removeClass('bg-gray-200');

            $('#__alertMessageIfHasParent').hide();
            $('#__alertMessageIfVariable').hide();

            if (typeof (wooProduct.parent.id) !== 'undefined') {
                $('#__alertMessageIfHasParent').show();
                $('#__product_nameEditProduct').attr('disabled', true).addClass('bg-gray-200');
                $('#__product_codeEditProduct').attr('disabled', true).addClass('bg-gray-200');
            }

            if (typeof (wooProduct.parent.id) === 'undefined' && wooProduct.type === 'variable') {
                $('#__alertMessageIfVariable').show();
                $('#__priceEditProduct').attr('disabled', true).addClass('bg-gray-200');
                $('#__quantityEditProduct').attr('disabled', true).addClass('bg-gray-200');
            }

            $('#__modalEditProduct').doModal('open');
        },
        error: function (error) {
            const response = error.responseJSON;

            alert(response.message);
        }
    });
};

$('#__btnCancelEditProduct').on('click', function () {
    $('#__modalEditProduct').doModal('close');

    $('.alert').addClass('hidden').find('.alert-content').html(null);

    $('#__alertMessageIfHasParent').hide();
    $('#__alertMessageIfVariable').hide();
});

$('#__formEditProduct').on('submit', function (event) {
    event.preventDefault();

    const actionUrl = $(this).attr('action');
    const formData = new FormData($(this)[0]);

    $.ajax({
        type: 'POST',
        url: actionUrl,
        contentType: false,
        processData: false,
        data: formData,
        beforeSend: function () {
            $('#__btnCancelEditProduct').attr('disabled', false);
            $('#__btnSubmitEditProduct').attr('disabled', false).html(textProcessing);
            $('.alert').addClass('hidden').find('.alert-content').html(null);
        },
        success: function (response) {
            $('#wooProductTable').DataTable().ajax.reload(null, false);

            $('#__alertSuccessWooProductTable').removeClass('hidden').find('.alert-content').html(alertMessage);

            $('#__modalEditProduct').doModal('close');
            $('#__btnCancelEditProduct').attr('disabled', false);
            $('#__btnSubmitEditProduct').attr('disabled', false).html(textUpdateData);
        },
        error: function (error) {
            const response = error.responseJSON;
            let alertMessage = response.message;

            if (error.status === 422) {
                const errorFields = Object.keys(response.errors);
                alertMessage += '<br>';
                $.each(errorFields, function (index, field) {
                    alertMessage += response.errors[field][0] + '<br>';
                });
            }

            $('#__alertDangerEditProduct').removeClass('hidden').find('.alert-content').html(alertMessage);
            $('#__btnCancelEditProduct').attr('disabled', false);
            $('#__btnSubmitEditProduct').attr('disabled', false).html(textUpdateData);
        }
    });
});

const deleteProduct = (el) => {
    const id = el.getAttribute('data-id');

    $('#__idDeleteProduct').val(id);
    $('#__modalDeleteProduct').doModal('open');
};

$('#__btnCancelDeleteProduct').on('click', function () {
    $('#__modalDeleteProduct').doModal('close');
    $('.alert').addClass('hidden').find('.alert-content').html(null);
});

$('#__formDeleteProduct').on('submit', function (event) {
    event.preventDefault();

    const actionUrl = $(this).attr('action');
    const formData = new FormData($(this)[0]);

    $.ajax({
        type: 'POST',
        url: actionUrl,
        contentType: false,
        processData: false,
        data: formData,
        beforeSend: function () {
            $('#__btnCancelDeleteProduct').attr('disabled', false);
            $('#__btnSubmitDeleteProduct').attr('disabled', false).html(textProcessing);
            $('.alert').addClass('hidden').find('.alert-content').html(null);
        },
        success: function (response) {
            $('#wooProductTable').DataTable().ajax.reload(null, false);

            $('#__alertSuccessWooProductTable').removeClass('hidden').find('.alert-content').html(alertMessage);

            $('#__modalDeleteProduct').doModal('close');
            $('#__btnCancelDeleteProduct').attr('disabled', false);
            $('#__btnSubmitDeleteProduct').attr('disabled', false).html(textYesDelete);
        },
        error: function (error) {
            const response = error.responseJSON;
            let alertMessage = response.message;

            if (error.status === 422) {
                const errorFields = Object.keys(response.errors);
                alertMessage += '<br>';
                $.each(errorFields, function (index, field) {
                    alertMessage += response.errors[field][0] + '<br>';
                });
            }

            $('#__alertDangerDeleteProduct').removeClass('hidden').find('.alert-content').html(alertMessage);
            $('#__btnCancelDeleteProduct').attr('disabled', false);
            $('#__btnSubmitDeleteProduct').attr('disabled', false).html(textYesDelete);
        }
    });
});
