/* eslint-disable no-undef */
const sheetDataTpkTable = $('#__sheetDataTpkTable').DataTable({
    serverSide: true,
    processing: true,
    bDestroy: true,
    ajax: {
        type: 'GET',
        url: route('sheet-data-tpks.datatable')
    },
    columns: [
        {
            data: 'id',
            name: 'id',
            searchable: true,
            orderable: false,
            checkboxes: true
        },
        {
            data: 'sheet_name',
            name: 'sheet_name',
            searchable: false,
            orderable: true
        },
        {
            data: 'str_date_amount',
            name: 'str_date_amount',
            searchable: false,
            orderable: true
        },
        {
            data: 'more',
            name: 'more',
            searchable: false,
            orderable: false
        }
    ],
    select: {
        style: 'multi'
    }
});

$('body').on('change', '.dt-checkboxes-select-all input[type="checkbox"]', function () {
    $('div.dataTables_wrapper div.dataTables_paginate').show();
    $('#__btnBulkDeleteTpkPackingData').attr('disabled', true);
    $('#__totalSelectedRows').html(0);

    if ($(this).prop('checked')) {
        const totalChecked = $('td.dt-checkboxes-cell input[type="checkbox"]:checked').length;

        $('div.dataTables_wrapper div.dataTables_paginate').hide();
        $('#__btnBulkDeleteTpkPackingData').attr('disabled', false);
        $('#__totalSelectedRows').html(totalChecked);
    }
});

$('body').on('change', 'td.dt-checkboxes-cell input[type="checkbox"]', function () {
    const totalChecked = $('td.dt-checkboxes-cell input[type="checkbox"]:checked').length;

    $('div.dataTables_wrapper div.dataTables_paginate').show();
    $('#__btnBulkDeleteTpkPackingData').attr('disabled', true);
    $('#__totalSelectedRows').html(0);

    if (totalChecked > 0) {
        $('div.dataTables_wrapper div.dataTables_paginate').hide();
        $('#__btnBulkDeleteTpkPackingData').attr('disabled', false);
        $('#__totalSelectedRows').html(totalChecked);
    }
});

$('#__btnBulkDeleteTpkPackingData').on('click', function () {
    $('#__modalDeleteSheetDataTpk').doModal('show');
});

$('#__btnCancelDeleteSheetDataTpk').on('click', function () {
    $('#__modalDeleteSheetDataTpk').doModal('hide');
    $('#__formDeleteSheetDataTpk')[0].reset();
});

$('#__formDeleteSheetDataTpk').on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData($(this)[0]);

    const selectedRows = sheetDataTpkTable.column(0).checkboxes.selected();
    $.each(selectedRows, function (index, sheetDataId) {
        formData.append('ids[]', sheetDataId);
    });

    $.ajax({
        type: 'POST',
        url: route('sheet-data-tpks.batch-delete'),
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#__btnCancelDeleteSheetDataTpk').attr('disabled', true);
            $('#__btnSubmitDeleteSheetDataTpk').attr('disabled', true);
        },
        success: function (response) {
            const alertMessage = response.message;

            $('#__btnCancelDeleteSheetDataTpk').attr('disabled', false);
            $('#__btnSubmitDeleteSheetDataTpk').attr('disabled', false);
            $('#__modalDeleteSheetDataTpk').doModal('hide');

            $('div.dataTables_wrapper div.dataTables_paginate').show();
            $('#__btnBulkDeleteTpkPackingData').attr('disabled', true);
            $('#__totalSelectedRows').html(0);

            sheetDataTpkTable.ajax.reload(null, false);
            $('#__formDeleteSheetDataTpk')[0].reset();

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

            $('#__btnCancelDeleteSheetDataTpk').attr('disabled', false);
            $('#__btnSubmitDeleteSheetDataTpk').attr('disabled', false);

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
