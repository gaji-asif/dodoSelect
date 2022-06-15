/* eslint-disable no-undef */
const sheetNameTable = $('#__sheetNameTable').DataTable({
    serverSide: true,
    processing: false,
    ajax: {
        type: 'GET',
        url: route('sheet-names.datatable', { sheetDoc: sheetDocId }),
        error: function (error) {
            const response = error.responseJSON;

            if (error.status === 401) {
                alert(response.message);
                window.location.reload();
            }
        }
    },
    columns: [
        {
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            searchable: false,
            orderable: false
        },
        {
            data: 'sheet_name',
            name: 'sheet_name',
            searchable: true,
            orderable: true
        },
        {
            data: 'str_allow_to_sync',
            name: 'str_allow_to_sync',
            searchable: false,
            orderable: true
        },
        {
            data: 'str_last_sync',
            name: 'str_last_sync',
            searchable: false,
            orderable: true
        },
        {
            data: 'str_sync_status',
            name: 'str_sync_status',
            searchable: false,
            orderable: true
        },
        {
            data: 'actions',
            name: 'actions',
            searchable: false,
            orderable: false
        }
    ]
});

setInterval(() => {
    sheetNameTable.ajax.reload(null, false);
}, 10000);

$('#__btnAddSheetName').on('click', function () {
    $('#__modalAddSheetName').doModal('show');
});

$('#__btnCancelAddSheetName').on('click', function () {
    $('#__modalAddSheetName').doModal('hide');
    $('#__formAddSheetName')[0].reset();
});

$('#__formAddSheetName').on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData($(this)[0]);

    $.ajax({
        type: 'POST',
        url: route('sheet-names.store', { sheetDoc: sheetDocId }),
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#__btnCancelAddSheetName').attr('disabled', true);
            $('#__btnSubmitAddSheetName').attr('disabled', true);
        },
        success: function (response) {
            const alertMessage = response.message;

            $('#__btnCancelAddSheetName').attr('disabled', false);
            $('#__btnSubmitAddSheetName').attr('disabled', false);
            $('#__modalAddSheetName').doModal('hide');

            sheetNameTable.ajax.reload(null, false);
            $('#__formAddSheetName')[0].reset();

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

            $('#__btnCancelAddSheetName').attr('disabled', false);
            $('#__btnSubmitAddSheetName').attr('disabled', false);

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

const editSheetName = (el) => {
    const sheetNameId = el.getAttribute('data-id');

    $.ajax({
        type: 'GET',
        url: route('sheet-names.edit', { sheetDoc: sheetDocId, id: sheetNameId }),
        success: function (response) {
            const sheetName = response.data.sheet_name;

            $('#__idEditSheetName').val(sheetName.id);
            $('#__sheetNameEditSheetName').val(sheetName.sheet_name);

            $('#__allow_to_syncEditSheetName_1').attr('checked', false);
            $('#__allow_to_syncEditSheetName_0').attr('checked', true);

            if (sheetName.allow_to_sync) {
                $('#__allow_to_syncEditSheetName_0').attr('checked', false);
                $('#__allow_to_syncEditSheetName_1').attr('checked', true);
            }

            $('#__modalEditSheetName').doModal('show');
        },
        error: function (error) {
            const response = error.responseJSON;
            const alertMessage = response.message;

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
};

$('#__btnEditSheetName').on('click', function () {
    $('#__modalEditSheetName').doModal('show');
});

$('#__btnCancelEditSheetName').on('click', function () {
    $('#__modalEditSheetName').doModal('hide');
    $('#__formEditSheetName')[0].reset();
});

$('#__formEditSheetName').on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData($(this)[0]);

    $.ajax({
        type: 'POST',
        url: route('sheet-names.update', { sheetDoc: sheetDocId, id: formData.get('id') }),
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#__btnCancelEditSheetName').attr('disabled', true);
            $('#__btnSubmitEditSheetName').attr('disabled', true);
        },
        success: function (response) {
            const alertMessage = response.message;

            $('#__btnCancelEditSheetName').attr('disabled', false);
            $('#__btnSubmitEditSheetName').attr('disabled', false);
            $('#__modalEditSheetName').doModal('hide');

            sheetNameTable.ajax.reload(null, false);
            $('#__formEditSheetName')[0].reset();

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

            $('#__btnCancelEditSheetName').attr('disabled', false);
            $('#__btnSubmitEditSheetName').attr('disabled', false);

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

const deleteSheetName = (el) => {
    const sheetNameId = el.getAttribute('data-id');
    $('#__idDeleteSheetName').val(sheetNameId);

    $('#__modalDeleteSheetName').doModal('show');
};

$('#__btnDeleteSheetName').on('click', function () {
    $('#__modalDeleteSheetName').doModal('show');
});

$('#__btnCancelDeleteSheetName').on('click', function () {
    $('#__modalDeleteSheetName').doModal('hide');
    $('#__formDeleteSheetName')[0].reset();
});

$('#__formDeleteSheetName').on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData($(this)[0]);

    $.ajax({
        type: 'POST',
        url: route('sheet-names.delete', { sheetDoc: sheetDocId, id: formData.get('id') }),
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#__btnCancelDeleteSheetName').attr('disabled', true);
            $('#__btnSubmitDeleteSheetName').attr('disabled', true);
        },
        success: function (response) {
            const alertMessage = response.message;

            $('#__btnCancelDeleteSheetName').attr('disabled', false);
            $('#__btnSubmitDeleteSheetName').attr('disabled', false);
            $('#__modalDeleteSheetName').doModal('hide');

            sheetNameTable.ajax.reload(null, false);
            $('#__formDeleteSheetName')[0].reset();

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

            $('#__btnCancelDeleteSheetName').attr('disabled', false);
            $('#__btnSubmitDeleteSheetName').attr('disabled', false);

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
