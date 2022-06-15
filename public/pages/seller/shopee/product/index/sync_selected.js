/* eslint-disable no-undef */
$('body').on('change', '.dt-checkboxes-select-all input[type="checkbox"]', function () {
    $('div.dataTables_wrapper div.dataTables_paginate').show();
    $('.btn__sync-selected').attr('disabled', true);
    $('.btn__sync-selected__count').html(0);

    if ($(this).prop('checked')) {
        const totalChecked = $('td.dt-checkboxes-cell input[type="checkbox"]:checked').length;

        $('div.dataTables_wrapper div.dataTables_paginate').hide();
        $('.btn__sync-selected').attr('disabled', false);
        $('.btn__sync-selected__count').html(totalChecked);
    }
});

$('body').on('change', 'td.dt-checkboxes-cell input[type="checkbox"]', function () {
    const totalChecked = $('td.dt-checkboxes-cell input[type="checkbox"]:checked').length;

    $('div.dataTables_wrapper div.dataTables_paginate').show();
    $('.btn__sync-selected').attr('disabled', true);
    $('.btn__sync-selected__count').html(0);

    if (totalChecked > 0) {
        $('div.dataTables_wrapper div.dataTables_paginate').hide();
        $('.btn__sync-selected').attr('disabled', false);
        $('.btn__sync-selected__count').html(totalChecked);
    }
});

$('body').on('click', '.btn__sync-selected', function () {
    const selectedRows = $('#shopeeTable').DataTable().column(0).checkboxes.selected();

    const productIds = [];
    $.each(selectedRows, function (index, rowId) {
        productIds[index] = rowId;
    });

    $.ajax({
        type: 'POST',
        url: syncSelectedUrl,
        data: {
            jSonData: JSON.stringify(productIds)
        },
        beforeSend: function () {
            $('.alert').addClass('hidden').find('.alert-content').html(null);
            $('.btn__sync-selected').attr('disabled', true).find('.btn__sync-selected__text').html(`${textSyncing}...`);
        },
        success: function (response) {
            $('#__alertSuccessShopeeTable').removeClass('hidden').find('.alert-content').html(response.message);
            $('.btn__sync-selected').find('.btn__sync-selected__text').html(`${textSyncSelected}`);
            $('.btn__sync-selected').find('.btn__sync-selected__count').html(0);

            $('#shopeeTable').DataTable().ajax.reload(null, false);
        },
        error: function (error) {
            const response = error.responseJSON;

            $('#__alertDangerShopeeTable').removeClass('hidden').find('.alert-content').html(response.message);
            $('.btn__sync-selected').attr('disabled', false).find('.btn__sync-selected__text').html(`${textSyncSelected}`);
        }
    });
});
