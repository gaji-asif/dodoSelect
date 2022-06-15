/* eslint-disable no-undef */
let jobIsProcessing = false;
let checkingJobStatus = null;
// const selectedTranslationIds = [];

const translationTable = $('#__translationTable').DataTable({
    serverSide: true,
    processing: true,
    ajax: {
        type: 'GET',
        url: route('translation.datatable')
    },
    dom: '<"#dt-top-toolbar">frt<"#dt-bottom-toolbar"lip><"clear">',
    initComplete: function () {
        $('#dt-top-toolbar').append(
            $('<div/>', {
                class: 'flex flex-row items-center justify-center sm:justify-start gap-2 mb-4'
            }).append(
                $('.__btnWordScan').clone(),
                $('.__btnDeleteTranslation').clone()
            )
        );
    },
    columns: [
        {
            name: 'id',
            data: 'id',
            checkboxes: {
                selectRow: true
            }
        },
        {
            name: 'keyword',
            data: 'keyword'
        },
        {
            name: 'lang_en',
            data: 'lang_en'
        },
        {
            name: 'lang_th',
            data: 'lang_th'
        },
        {
            name: 'action',
            data: 'action',
            orderable: false,
            className: 'text-center'
        }
    ],
    select: {
        style: 'multi'
    }
});

const editTranslation = (el) => {
    const detailUrl = el.getAttribute('data-detail-url');

    $.ajax({
        type: 'GET',
        url: detailUrl,
        beforeSend: function () {
            $('.alert').addClass('hidden');
            $('.alert').find('.alert-content').html(null);
        },
        success: function (response) {
            const data = response.data;
            const translation = data.translation;

            $('#__idEditTranslation').val(translation.id);
            $('#__keyEditTranslation').val(translation.key);
            $('#__lang_enEditTranslation').val(translation.lang_en);
            $('#__lang_thEditTranslation').val(translation.lang_th);
            $('#__modalEditTranslation').doModal('open');
        },
        error: function (error) {
            const response = error.responseJSON;

            $('#__alertDangerTable').removeClass('hidden');
            $('#__alertDangerTable').find('.alert-content').html(response.message);
        }
    });
};

$('#__btnCancelEditTranslation').on('click', function () {
    $('.alert').addClass('hidden').find('.alert-content').html(null);

    $('#__modalEditTranslation').doModal('close');
});

$('#__formEditTranslation').on('submit', function (event) {
    event.preventDefault();

    const formData = new FormData($(this)[0]);

    $.ajax({
        type: 'POST',
        processData: false,
        contentType: false,
        url: $(this).attr('action'),
        data: formData,
        beforeSend: function () {
            $('.alert').addClass('hidden');
            $('.alert').find('.alert-content').html(null);

            $('#__btnCancelEditTranslation').attr('disabled', true);
            $('#__btnSubmitEditTranslation').attr('disabled', true).html(textProcessing);
        },
        success: function (response) {
            translationTable.ajax.reload(null, false);

            $('#__alertSuccessTable').removeClass('hidden');
            $('#__alertSuccessTable').find('.alert-content').html(response.message);

            $('#__formEditTranslation')[0].reset();
            $('#__modalEditTranslation').doModal('close');

            $('#__btnCancelEditTranslation').attr('disabled', false);
            $('#__btnSubmitEditTranslation').attr('disabled', false).html(textUpdateData);
        },
        error: function (error) {
            const response = error.responseJSON;
            let alertMessage = response.message;

            if (error.status === 422) {
                const errorFields = Object.keys(response.errors);

                alertMessage = '';
                $.each(errorFields, function (field) {
                    alertMessage += `- ${response.errors[field][0]} <br>`;
                });
            }

            $('#__alertDangerEditTranslation').removeClass('hidden');
            $('#__alertDangerEditTranslation').find('.alert-content').html(alertMessage);

            $('#__btnCancelEditTranslation').attr('disabled', false);
            $('#__btnSubmitEditTranslation').attr('disabled', false).html(textUpdateData);
        }
    });

    return false;
});

$('body').on('click', '.__btnWordScan', function () {
    $.ajax({
        type: 'POST',
        url: route('translation.store'),
        beforeSend: function () {
            $('.__btnWordScan').attr('disabled', true);
            $('.alert').addClass('hidden');
        },
        success: function (response) {
            const fiveSeconds = 5000;

            $('#__alertInfoTable').removeClass('hidden').find('.alert-content').html(response.message);

            checkingJobStatus = setInterval(() => {
                fetchJobStatus();
            }, fiveSeconds);
        },
        error: function (error) {
            const response = error.responseJSON;

            $('#__alertDangerTable').removeClass('hidden').find('.alert-content').html(response.message);

            $('.__btnWordScan').attr('disabled', false);
        }
    });
});

const fetchJobStatus = () => {
    $.ajax({
        type: 'GET',
        url: route('scan-translation-job.index'),
        success: function (response) {
            const responseData = response.data;
            jobIsProcessing = responseData.isProcessing;

            if (jobIsProcessing === false) {
                clearInterval(checkingJobStatus);
                window.location.reload();
            }
        },
        error: function () {
            $('.__btnWordScan').attr('disabled', false);
        }
    });
};

$('body').on('change', '.dt-checkboxes-select-all input[type="checkbox"]', function () {
    $('div.dataTables_wrapper div.dataTables_paginate').show();
    $('.__btnDeleteTranslation').attr('disabled', true);
    $('.__totalSelectedRows').html(0);

    if ($(this).prop('checked')) {
        const totalChecked = $('td.dt-checkboxes-cell input[type="checkbox"]:checked').length;

        $('div.dataTables_wrapper div.dataTables_paginate').hide();
        $('.__btnDeleteTranslation').attr('disabled', false);
        $('.__totalSelectedRows').html(totalChecked);
    }
});

$('body').on('change', 'td.dt-checkboxes-cell input[type="checkbox"]', function () {
    const totalChecked = $('td.dt-checkboxes-cell input[type="checkbox"]:checked').length;

    $('div.dataTables_wrapper div.dataTables_paginate').show();
    $('.__btnDeleteTranslation').attr('disabled', true);
    $('.__totalSelectedRows').html(0);

    if (totalChecked > 0) {
        $('div.dataTables_wrapper div.dataTables_paginate').hide();
        $('.__btnDeleteTranslation').attr('disabled', false);
        $('.__totalSelectedRows').html(totalChecked);
    }
});

$('body').on('click', '.__btnDeleteTranslation', function () {
    $('#__modalDeleteTranslation').doModal('open');
});

$('#__btnCancelDeleteTranslation').on('click', function () {
    $('#__modalDeleteTranslation').doModal('close');
});

$('#__btnYesDeleteTranslation').on('click', function () {
    const formData = new FormData();

    const selectedRows = translationTable.column(0).checkboxes.selected();
    $.each(selectedRows, function (index, transitionId) {
        formData.append('ids[]', transitionId);
    });

    $('.alert').addClass('hidden');

    $.ajax({
        type: 'POST',
        url: route('translation.delete'),
        processData: false,
        contentType: false,
        data: formData,
        beforeSend: function () {
            $('#__btnCancelDeleteTranslation').attr('disabled', true);
            $('#__btnYesDeleteTranslation').attr('disabled', true).html('Processing...');
            $('.alert').addClass('hidden').find('.alert-content').html(null);
        },
        success: function (response) {
            $('#__alertSuccessTable').removeClass('hidden').find('.alert-content').html(response.message);

            translationTable.ajax.reload(null, false);
            $('.__btnDeleteTranslation').attr('disabled', true);
            $('.__totalSelectedRows').html(0);

            $('div.dataTables_wrapper div.dataTables_paginate').show();

            $('#__modalDeleteTranslation').doModal('close');
            $('#__btnCancelDeleteTranslation').attr('disabled', false);
            $('#__btnYesDeleteTranslation').attr('disabled', false).html('Yes, Delete');
        },
        error: function (error) {
            const response = error.responseJSON;
            let alertMessage = response.message;

            if (error.status === 422) {
                const errorFields = Object.keys(response.errors);

                alertMessage = '';
                errorFields.each(function (field) {
                    alertMessage += `- ${response.errors[field][0]} <br>`;
                });
            }

            $('#__alertDangerDeleteTranslation').removeClass('hidden');
            $('#__alertDangerDeleteTranslation').find('.alert-content').html(alertMessage);

            $('#__btnCancelDeleteTranslation').attr('disabled', false);
            $('#__btnYesDeleteTranslation').attr('disabled', false).html('Yes, Delete');
        }
    });
});
