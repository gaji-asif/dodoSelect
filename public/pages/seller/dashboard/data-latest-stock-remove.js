/* eslint-disable array-callback-return */
/* eslint-disable no-undef */
$(document).ready(function () {
    fetchLatestStockRemoveProducts();
});

const fetchLatestStockRemoveProducts = () => {
    $.ajax({
        type: 'GET',
        url: route('dashboard.data.latest-stock-products', { type: 'remove' }),
        success: function (response) {
            const data = response.data;
            const latestStockProducts = data.latest_stock_products;

            $('#__latestStockProductRemoveWrapper').html(null);
            $('#__latestStockProductRemoveWrapper').append(
                $('<div/>', {
                    class: 'p-6 text-center',
                    html: '<i>No items</i>'
                })
            );

            if (latestStockProducts.length > 0) {
                $('#__latestStockProductRemoveWrapper').html(null);

                latestStockProducts.map((item) => {
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
                    if (typeof (item.main_stock.quantity) !== 'undefined') {
                        productQty = item.main_stock.quantity;
                    }

                    const productQtyChange = `(-${thousandFormat(item.quantity)})`;

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
                        return html.replaceAll('{product_qty_change}', productQtyChange);
                    });

                    $productItemTemplate.html(function (index, html) {
                        return html.replaceAll('{datetime}', moment(item.date).format('YYYY-MM-DD hh:mm:ss'));
                    });

                    $productItemTemplate.html(function (index, html) {
                        return html.replaceAll('{seller_name}', sellerName);
                    });

                    $('#__latestStockProductRemoveWrapper').append(
                        $productItemTemplate.html()
                    );
                });
            }
        }
    });
};
