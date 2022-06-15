/* eslint-disable array-callback-return */
/* eslint-disable no-undef */
$(document).ready(function () {
    fetchLastChangeProducts();
});

const fetchLastChangeProducts = () => {
    $.ajax({
        type: 'GET',
        url: route('dashboard.data.last-change-products'),
        success: function (response) {
            const data = response.data;
            const lastChangeProducts = data.last_change_products;

            $('#__lastChangeProductWrapper').html(null);
            $('#__lastChangeProductWrapper').append(
                $('<div/>', {
                    class: 'p-6 text-center',
                    html: '<i>No items</i>'
                })
            );

            if (lastChangeProducts.length > 0) {
                $('#__lastChangeProductWrapper').html(null);

                lastChangeProducts.map((change) => {
                    const $lastChangeItemTemplate = $('#__templateProductItem').clone();

                    let productImageUrl = '#';
                    if (typeof (change.product.image_url) !== 'undefined') {
                        productImageUrl = change.product.image_url;
                    }

                    let productName = '';
                    if (typeof (change.product.product_name) !== 'undefined') {
                        productName = change.product.product_name;
                    }

                    let productCode = '';
                    if (typeof (change.product.product_code) !== 'undefined') {
                        productCode = change.product.product_code;
                    }

                    let productQty = 0;
                    if (typeof (change.main_stock.quantity) !== 'undefined') {
                        productQty = change.main_stock.quantity;
                    }

                    let productQtyChange = `(+${change.quantity})`;
                    if (change.str_in_out === 'Remove') {
                        productQtyChange = `(-${change.quantity})`;
                    }

                    let sellerName = '-';
                    if (typeof (change.seller.name) !== 'undefined') {
                        sellerName = change.seller.name;
                    }

                    $lastChangeItemTemplate.html(function (index, html) {
                        return html.replaceAll('###', productImageUrl);
                    });

                    $lastChangeItemTemplate.html(function (index, html) {
                        return html.replaceAll('{product_name}', productName);
                    });

                    $lastChangeItemTemplate.html(function (index, html) {
                        return html.replaceAll('{product_code}', productCode);
                    });

                    $lastChangeItemTemplate.html(function (index, html) {
                        return html.replaceAll('{product_qty}', thousandFormat(productQty));
                    });

                    $lastChangeItemTemplate.html(function (index, html) {
                        return html.replaceAll('{product_qty_change}', productQtyChange);
                    });

                    $lastChangeItemTemplate.html(function (index, html) {
                        return html.replaceAll('{datetime}', moment(change.date).format('YYYY-MM-DD hh:mm:ss'));
                    });

                    $lastChangeItemTemplate.html(function (index, html) {
                        return html.replaceAll('{seller_name}', sellerName);
                    });

                    $('#__lastChangeProductWrapper').append(
                        $lastChangeItemTemplate.html()
                    );
                });
            }
        }
    });
};
