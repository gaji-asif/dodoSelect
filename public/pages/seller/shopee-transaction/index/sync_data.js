/* eslint-disable no-undef */
$('#__flatpickrSyncData').flatpickr({
    wrap: true,
    mode: 'range',
    disableMobile: true,
    // defaultDate: [moment().subtract(30, 'days').format('YYYY-MM-DD'), moment().format('YYYY-MM-DD')],
    onChange: function (selectedDates, dateStr, instance) {
        if (selectedDates.length === 1) {
            instance.config.minDate = moment(dateStr).subtract(90, 'days').format('YYYY-MM-DD');
            instance.config.maxDate = moment(dateStr).add(90, 'days').format('YYYY-MM-DD');
        }
    },
    onClose: function (selectedDates, dateStr, instance) {
        instance.config.minDate = null;
        instance.config.maxDate = null;
    }
});

$('#__btnSyncData').on('click', function () {
    $('#__modalSyncData').doModal('show');
});

$('#__btnCancelSyncData').on('click', function () {
    $('#__modalSyncData').doModal('hide');
    $('.alert').addClass('hidden').find('.alert-content').html(null);
});

$('#__formSyncData').on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData($(this)[0]);

    $.ajax({
        type: 'POST',
        url: route('shopee-transaction.store'),
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#__btnCancelSyncData').attr('disabled', true);
            $('#__btnSubmitSyncData').attr('disabled', true);

            $('.alert').addClass('hidden').find('.alert-content').html(null);
        },
        success: function (response) {
            const alertMessage = response.message;

            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: alertMessage,
                toast: true,
                timer: 3000,
                timerProgressBar: true,
                position: 'top-end',
                showConfirmButton: false
            });

            syncStatusCheck();

            $('#__modalSyncData').doModal('hide');
            $('#__btnCancelSyncData').attr('disabled', false);
            $('#__btnSubmitSyncData').attr('disabled', false);
        },
        error: function (error) {
            const response = error.responseJSON;
            let alertMessage = response.message;

            if (error.status === 422) {
                alertMessage = '';
                const errorFields = Object.keys(response.errors);
                $.each(errorFields, function (index, value) {
                    alertMessage += `- ${response.errors[value][0]} <br>`;
                });
            }

            $('#__alertDangerSyncData')
                .removeClass('hidden')
                .find('.alert-content')
                .html(alertMessage);

            $('#__btnCancelSyncData').attr('disabled', false);
            $('#__btnSubmitSyncData').attr('disabled', false);
        }
    });

    return false;
});
