/* eslint-disable array-callback-return */
/* eslint-disable no-undef */
$(document).ready(function () {
    fetchHighestStockProducts();
});

const fetchHighestStockProducts = () => {
    $.ajax({
        type: 'GET',
        url: route('dashboard.data.highest-stock-products'),
        success: function (response) {
            const data = response.data;
            const highestStockProducts = data.highest_stock_products;

            $('#__highestStockProductWrapper').html(null);
            $('#__highestStockProductWrapper').append(
                $('<div/>', {
                    class: 'p-6 text-center',
                    html: '<i>No items</i>'
                })
            );

            if (highestStockProducts.length > 0) {
                $('#__highestStockProductWrapper').html(null);

                highestStockProducts.map((item) => {
                    const $productItemTemplate = $('#__templateProductItem').clone();

                    let productImageUrl = '#';
                    if (typeof (item.product.image_url) !== 'undefined') {
                        productImageUrl = item.product.image_url;
                    }

                    let productName = '';
                    if (typeof (item.product.product_name) !== 'undefined') {
                        productName = item.product.product_name;
                    }

                    let productCode = '';
                    if (typeof (item.product.product_code) !== 'undefined') {
                        productCode = item.product.product_code;
                    }

                    let productQty = 0;
                    if (typeof (item.quantity) !== 'undefined') {
                        productQty = item.quantity;
                    }

                    const productQtyitem = '';

                    let sellerName = '-';
                    if (typeof (item.product.seller) !== 'undefined') {
                        sellerName = item.product.seller.name;
                    }

                    $productItemTemplate.html(function (index, html) {
                        return html.replaceAll('###', productImageUrl);
                    });

                    $productItemTemplate.html(function (index, html) {
                        return html.replaceAll('{product_name}', productName);
                    });

                    $productItemTemplate.html(function (index, html) {
                        return html.replaceAll('{product_code}', productCode);
                    });

                    $productItemTemplate.html(function (index, html) {
                        return html.replaceAll('{product_qty}', thousandFormat(productQty));
                    });

                    $productItemTemplate.html(function (index, html) {
                        return html.replaceAll('{product_qty_change}', productQtyitem);
                    });

                    $productItemTemplate.html(function (index, html) {
                        return html.replaceAll('{datetime}', moment(item.created_at).format('YYYY-MM-DD hh:mm:ss'));
                    });

                    $productItemTemplate.html(function (index, html) {
                        return html.replaceAll('{seller_name}', sellerName);
                    });

                    $('#__highestStockProductWrapper').append(
                        $productItemTemplate.html()
                    );
                });
            }
        }
    });
};
