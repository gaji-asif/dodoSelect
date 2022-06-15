/* eslint-disable no-undef */
const loadLinkedProductDatatable = () => {
    $('#__tblLinkedCatalog').DataTable({
        bDestroy: true,
        serverSide: true,
        processing: true,
        ajax: {
            type: 'GET',
            url: linkedCatalogDatatableUrl
        },
        dom: '<"#dt-top-toolbar">frt<"#dt-bottom-toolbar"lip><"clear">',
        columns: [
            {
                name: 'image_with_id',
                data: 'image_with_id',
                orderable: false
            },
            {
                name: 'product_details',
                data: 'product_details'
            }
        ]
    });
};

const editLinkedCatalog = (el) => {
    const id = el.getAttribute('data-id');
    const detailUrl = el.getAttribute('data-detail-url');

    shopeeProductId = id;

    $.ajax({
        type: 'GET',
        url: detailUrl,
        success: function (response) {
            const responseData = response.data;
            const product = responseData.product;
            const catalog = product.catalog;

            $('#__linkedProductFoundWrapper').hide();
            $('#__linkedProductNotFoundWrapper').show();

            $('#__linkedCatalogProductName').html(null);
            $('#__linkedCatalogProductCode').html(null);

            if (typeof (catalog.product_name) !== 'undefined') {
                $('#__linkedProductNotFoundWrapper').hide();
                $('#__linkedProductFoundWrapper').show();

                $('#__linkedCatalogProductName').html(catalog.product_name);
                $('#__linkedCatalogProductCode').html(catalog.product_code);
            }

            loadLinkedProductDatatable();
            $('#__modalEditLinkedCatalog').doModal('open');
        }
    });
};

$('#__btnCloseModalEditLinkedCatalog').on('click', function () {
    $('#__modalEditLinkedCatalog').doModal('close');
});

const linkCatalogToShopeeProduct = (el) => {
    const productId = el.getAttribute('data-product-id');

    const formData = new FormData();
    formData.append('shopee_product_id', shopeeProductId);
    formData.append('product_id', productId);

    $.ajax({
        type: 'POST',
        url: linkWooProductToCatalogUrl,
        processData: false,
        contentType: false,
        data: formData,
        beforeSend: function () {
            $('.btn-link-shopee-product').attr('disabled', true);
            $('.alert').addClass('hidden').find('.alert-content').html(null);
        },
        success: function (response) {
            const alertMessage = response.message;

            $('#__alertSuccessShopeeTable').removeClass('hidden').find('.alert-content').html(alertMessage);
            $('#shopeeTable').DataTable().ajax.reload(null, false);

            $('#__modalEditLinkedCatalog').doModal('close');
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

            $('#__alertDangerEditLinkedCatalog').removeClass('hidden').find('.alert-content').html(alertMessage);
            $('.btn-link-shopee-product').attr('disabled', false);
        }
    });
};
