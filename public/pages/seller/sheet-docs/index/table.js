/* eslint-disable no-undef */
const sheetDocsTable = $('#__sheetDocsTable').DataTable({
    serverSide: true,
    processing: true,
    ajax: {
        type: 'GET',
        url: route('sheet-docs.datatable')
    },
    columns: [
        {
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            searchable: false,
            orderable: false
        },
        {
            data: 'file_name',
            name: 'file_name',
            searchable: true,
            orderable: true
        },
        {
            data: 'spreadsheet_id',
            name: 'spreadsheet_id',
            searchable: true,
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

$('#__btnAddSheetDoc').on('click', function () {
    $('#__modalAddSheetDoc').doModal('show');
});

$('#__btnCancelAddSheetDoc').on('click', function () {
    $('#__modalAddSheetDoc').doModal('hide');
    $('#__formAddSheetDoc')[0].reset();
});

$('#__formAddSheetDoc').on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData($(this)[0]);

    $.ajax({
        type: 'POST',
        url: route('sheet-docs.store'),
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#__btnCancelAddSheetDoc').attr('disabled', true);
            $('#__btnSubmitAddSheetDoc').attr('disabled', true);
        },
        success: function (response) {
            const alertMessage = response.message;

            $('#__btnCancelAddSheetDoc').attr('disabled', false);
            $('#__btnSubmitAddSheetDoc').attr('disabled', false);
            $('#__modalAddSheetDoc').doModal('hide');

            sheetDocsTable.ajax.reload(null, false);
            $('#__formAddSheetDoc')[0].reset();

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

            $('#__btnCancelAddSheetDoc').attr('disabled', false);
            $('#__btnSubmitAddSheetDoc').attr('disabled', false);

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

const editSheetDoc = (el) => {
    const sheetDocId = el.getAttribute('data-id');

    $.ajax({
        type: 'GET',
        url: route('sheet-docs.edit', { id: sheetDocId }),
        success: function (response) {
            const sheetDoc = response.data.sheet_doc;

            $('#__idEditSheetDoc').val(sheetDoc.id);
            $('#__fileNameEditSheetDoc').val(sheetDoc.file_name);
            $('#__spreadsheetIdEditSheetDoc').val(sheetDoc.spreadsheet_id);

            $('#__modalEditSheetDoc').doModal('show');
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

$('#__btnEditSheetDoc').on('click', function () {
    $('#__modalEditSheetDoc').doModal('show');
});

$('#__btnCancelEditSheetDoc').on('click', function () {
    $('#__modalEditSheetDoc').doModal('hide');
    $('#__formEditSheetDoc')[0].reset();
});

$('#__formEditSheetDoc').on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData($(this)[0]);

    $.ajax({
        type: 'POST',
        url: route('sheet-docs.update', { id: formData.get('id') }),
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#__btnCancelEditSheetDoc').attr('disabled', true);
            $('#__btnSubmitEditSheetDoc').attr('disabled', true);
        },
        success: function (response) {
            const alertMessage = response.message;

            $('#__btnCancelEditSheetDoc').attr('disabled', false);
            $('#__btnSubmitEditSheetDoc').attr('disabled', false);
            $('#__modalEditSheetDoc').doModal('hide');

            sheetDocsTable.ajax.reload(null, false);
            $('#__formEditSheetDoc')[0].reset();

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

            $('#__btnCancelEditSheetDoc').attr('disabled', false);
            $('#__btnSubmitEditSheetDoc').attr('disabled', false);

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

const deleteSheetDoc = (el) => {
    const sheetDocId = el.getAttribute('data-id');
    $('#__idDeleteSheetDoc').val(sheetDocId);

    $('#__modalDeleteSheetDoc').doModal('show');
};

$('#__btnDeleteSheetDoc').on('click', function () {
    $('#__modalDeleteSheetDoc').doModal('show');
});

$('#__btnCancelDeleteSheetDoc').on('click', function () {
    $('#__modalDeleteSheetDoc').doModal('hide');
    $('#__formDeleteSheetDoc')[0].reset();
});

$('#__formDeleteSheetDoc').on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData($(this)[0]);

    $.ajax({
        type: 'POST',
        url: route('sheet-docs.delete', { id: formData.get('id') }),
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#__btnCancelDeleteSheetDoc').attr('disabled', true);
            $('#__btnSubmitDeleteSheetDoc').attr('disabled', true);
        },
        success: function (response) {
            const alertMessage = response.message;

            $('#__btnCancelDeleteSheetDoc').attr('disabled', false);
            $('#__btnSubmitDeleteSheetDoc').attr('disabled', false);
            $('#__modalDeleteSheetDoc').doModal('hide');

            sheetDocsTable.ajax.reload(null, false);
            $('#__formDeleteSheetDoc')[0].reset();

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

            $('#__btnCancelDeleteSheetDoc').attr('disabled', false);
            $('#__btnSubmitDeleteSheetDoc').attr('disabled', false);

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
