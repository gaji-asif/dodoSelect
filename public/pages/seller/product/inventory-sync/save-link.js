/* eslint-disable no-undef */
$('#__btnSaveProductLinks').on('click', function () {
    const drop = confirm('Confirm product links?');

    if (drop) {
        location.reload();
    }
});

$(document).on('click', 'a.link_product_id', function (e) {
    const target = e.target;
    const syncProductId = target.id;
    const salesChannel = $('#__selectChannelFilter').val();

    $.ajax({
        url: route('product.save_link'),
        type: 'post',
        data: {
            productSyncId: productSyncId,
            sync_product_id: syncProductId,
            action: 'attach',
            salesChannel: salesChannel,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            $('#link_product_'+syncProductId).html('<a href="javascript:void(0)" id="'+syncProductId+'" class="btn-action--yellow unlink_product_id" title="Unlink product"><i id="'+syncProductId+'" class="fas fa-unlink"></i></a> ' +
                '<a href="'+response.data.linked_to_url+'" id="inventory_link_'+syncProductId+'" class="btn-action--green" title="Inventory Link">' +
                '<i class="fas fa-vector-square"></i></a>');
        },
        error: function (error) {
            const responseJson = error.responseJSON;
            alert(responseJson.message);
        }
    });
});

$(document).on('click', 'a.unlink_product_id', function (e) {
    const target = e.target;
    const syncProductId = target.id;
    const salesChannel = $('#__selectChannelFilter').val();
    $.ajax({
        url: route('product.save_link'),
        type: 'post',
        data: {
            productSyncId: productSyncId,
            sync_product_id: syncProductId,
            action: 'detach',
            salesChannel: salesChannel,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            $('#link_product_'+syncProductId).html('<a href="javascript:void(0)" id="'+syncProductId+'" class="btn-action--blue link_product_id" title="Link product"><i id="'+syncProductId+'" class="fas fa-link"></i></a>');
        },
        error: function (error) {
            const responseJson = error.responseJSON;
            alert(responseJson.message);
        }
    });
});

$(document).on('change', '.dt-checkboxes-select-all', function (e) {
    const target = e.target;
    const syncProductId = [];
    const salesChannel = $('#__selectChannelFilter').val();

    if ($(target).is(':checked')) {
        $('input:checkbox[name="sync_product_id"]:checked').each(function () {
            syncProductId.push($(this).attr('id'));
        });

        $.ajax({
            url: route('product.save_multiple_links'),
            type: 'post',
            data: {
                productSyncId: productSyncId,
                sync_product_id: syncProductId,
                action: 'attach',
                salesChannel: salesChannel,
                _token: $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function (result) {
            if (result.status !== 1) {
                alert(result.message);
            }
        });
    } else {
        $('input:checkbox[name="sync_product_id"]').each(function () {
            syncProductId.push($(this).attr('id'));
        });
        $.ajax({
            url: route('product.save_multiple_links'),
            type: 'post',
            data: {
                productSyncId: productSyncId,
                sync_product_id: syncProductId,
                action: 'detach',
                salesChannel: salesChannel,
                _token: $('meta[name=csrf-token]').attr('content')
            }
        }).done(function (result) {
            if (result.status !== 1) {
                alert(result.message);
            }
        });
    }
});
