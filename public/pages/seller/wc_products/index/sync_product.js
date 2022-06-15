/* eslint-disable no-undef */
$('body').on('click', '.btn__sync-product', function () {
    $('#__modalSyncProduct').doModal('open');
});

$('#__btnCancelSyncProduct').on('click', function () {
    $('#__modalSyncProduct').doModal('close');
    $('.alert').addClass('hidden').find('alert-content').html(null);
});

$('#__formSyncProduct').on('submit', function (event) {
    event.preventDefault();

    const actionUrl = $(this).attr('action');
    const formData = new FormData($(this)[0]);

    $.ajax({
        type: 'POST',
        url: actionUrl,
        processData: false,
        contentType: false,
        data: formData,
        beforeSend: function () {
            $('#__btnCancelSyncProduct').attr('disabled', true);
            $('#__btnSubmitSyncProduct').attr('disabled', true).html(textProcessing);
            $('.alert').addClass('hidden').find('.alert-content').html(null);
        },
        success: function (response) {
            const alertMessage = response.message;

            setTimeout(() => {
                $('#__alertInfoWooProductTable').removeClass('hidden').find('.alert-content').html(alertMessage);

                $('#__formSyncProduct')[0].reset();

                $('#__modalSyncProduct').doModal('close');
                $('#__btnCancelSyncProduct').attr('disabled', false);
                $('#__btnSubmitSyncProduct').attr('disabled', false).html(textLoadData);
            }, 1500);
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

            $('#__alertDangerSyncProduct').removeClass('hidden').find('.alert-content').html(alertMessage);
            $('#__btnCancelSyncProduct').attr('disabled', false);
            $('#__btnSubmitSyncProduct').attr('disabled', false).html(textLoadData);
        }
    });

    return false;
});
