/* eslint-disable no-undef */
$('#__btnAutoLink').click(function () {
    $('#__modalConfirmAutoLink').doModal('show');
    modalBackToTop();
});

$('#__btnCancelConfirmAutoLink').on('click', function () {
    $('.alert')
        .addClass('hidden')
        .find('.alert-content').html(null);

    $('#__modalConfirmAutoLink').doModal('hide');
});

$('#__btnSubmitConfirmAutoLink').on('click', function () {
    $.ajax({
        url: route('product.inventory_auto_link'),
        type: 'POST',
        data: {
            product_id: productSyncId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function () {
            $('#__btnCancelConfirmAutoLink').attr('disabled', true);
            $('#__btnSubmitConfirmAutoLink').attr('disabled', true);
            $('.alert')
                .addClass('hidden')
                .find('.alert-content').html(null);
        },
        success: function (response) {
            const alertMessage = response.message;

            $('#__btnCancelConfirmAutoLink').attr('disabled', false);
            $('#__btnSubmitConfirmAutoLink').attr('disabled', false);
            $('#__modalConfirmAutoLink').doModal('hide');

            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: alertMessage,
                showConfirmButton: false,
                toast: true,
                timer: 3000,
                position: 'top-end'
            });

            // setTimeout(() => {
            //     window.location.reload();
            // }, 2500);
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

            $('#__alertDangerConfirmAutoLink')
                .removeClass('hidden')
                .find('.alert-content').html(alertMessage);

            $('#__btnCancelConfirmAutoLink').attr('disabled', false);
            $('#__btnSubmitConfirmAutoLink').attr('disabled', false);
        }
    });
});
