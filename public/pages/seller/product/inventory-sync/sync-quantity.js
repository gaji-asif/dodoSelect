/* eslint-disable no-undef */
$('#__btnSyncQuantity').click(function () {
    $('#__modalConfirmSyncQty').doModal('show');
    modalBackToTop();
});

$('#__btnCancelConfirmSyncQty').on('click', function () {
    $('.alert')
        .addClass('hidden')
        .find('.alert-content').html(null);

    $('#__modalConfirmSyncQty').doModal('hide');
});

$('#__btnSubmitConfirmSyncQty').on('click', function () {
    $.ajax({
        url: route('product.inventory_sync.quantity'),
        type: 'POST',
        data: {
            productSyncId: productSyncId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function () {
            $('#__btnCancelConfirmSyncQty').attr('disabled', true);
            $('#__btnSubmitConfirmSyncQty').attr('disabled', true);
            $('.alert')
                .addClass('hidden')
                .find('.alert-content').html(null);
        },
        success: function (response) {
            const alertMessage = response.message;

            $('#__btnCancelConfirmSyncQty').attr('disabled', false);
            $('#__btnSubmitConfirmSyncQty').attr('disabled', false);
            $('#__modalConfirmSyncQty').doModal('hide');

            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: alertMessage,
                showConfirmButton: false,
                toast: true,
                timer: 3000,
                position: 'top-end'
            });
        },
        error: function (error) {
            const response = error.responseJSON;
            let alertMessage = response.message;

            if (error.status === 422) {
                alertMessage = '';

                const errorFields = Object.keys(errorResponse.errors);
                $.each(errorFields, function (key, field) {
                    alertMessage += `- ${errorResponse.errors[field][0]} <br>`;
                });
            }

            $('#__alertDangerConfirmSyncQty')
                .removeClass('hidden')
                .find('.alert-content').html(alertMessage);

            $('#__btnCancelConfirmSyncQty').attr('disabled', false);
            $('#__btnSubmitConfirmSyncQty').attr('disabled', false);
        }
    });
});
