/* eslint-disable no-undef */
const syncNowSheetName = (el) => {
    const id = el.getAttribute('data-id');
    const sheetName = el.getAttribute('data-sheet-name');

    $('#__syncNameSyncNowSheetName').html(sheetName);

    $('#__idSyncNowSheetName').val(id);
    $('#__modalSyncNowSheetName').doModal('show');
};

$('#__btnSyncNowSheetName').on('click', function () {
    $('#__modalSyncNowSheetName').doModal('show');
});

$('#__btnCancelSyncNowSheetName').on('click', function () {
    $('#__modalSyncNowSheetName').doModal('hide');
    $('#__formSyncNowSheetName')[0].reset();
});

$('#__formSyncNowSheetName').on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData($(this)[0]);

    $.ajax({
        type: 'POST',
        url: route('sheet-names.sync-now', { sheetDoc: sheetDocId, id: formData.get('id') }),
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#__btnCancelSyncNowSheetName').attr('disabled', true);
            $('#__btnSubmitSyncNowSheetName').attr('disabled', true);
        },
        success: function (response) {
            const alertMessage = response.message;

            $('#__btnCancelSyncNowSheetName').attr('disabled', false);
            $('#__btnSubmitSyncNowSheetName').attr('disabled', false);
            $('#__modalSyncNowSheetName').doModal('hide');

            sheetNameTable.ajax.reload(null, false);
            $('#__formSyncNowSheetName')[0].reset();

            Swal.fire({
                icon: 'success',
                title: 'Success',
                html: alertMessage,
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

                const errorFields = Object.keys(response.errors);
                $.each(errorFields, function (key, field) {
                    alertMessage += `- ${response.errors[field][0]} <br>`;
                });
            }

            $('#__btnCancelSyncNowSheetName').attr('disabled', false);
            $('#__btnSubmitSyncNowSheetName').attr('disabled', false);

            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: alertMessage,
                showConfirmButton: false,
                toast: true,
                timer: 3000,
                position: 'top-end'
            });
        }
    });

    return false;
});
