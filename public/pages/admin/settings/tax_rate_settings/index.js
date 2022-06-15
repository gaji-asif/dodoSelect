$('#__formUpdateTaxRate').on('submit', function (event) {
    event.preventDefault();

    const formData = new FormData($(this)[0]);

    $.ajax({
        type: 'POST',
        url: $(this).attr('action'),
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#__btnSubmitUpdateTaxRate').attr('disabled', true).html('Processing...');
        },
        success: function (response) {
            const alertMessage = response.message;

            $('#__btnSubmitUpdateTaxRate').attr('disabled', false).html('Update Date');

            // eslint-disable-next-line no-undef
            Swal.fire({
                icon: 'success',
                html: alertMessage
            });
        },
        error: function (error) {
            const errorResponse = error.responseJSON;
            let alertMessage = '';

            $('#__btnSubmitUpdateTaxRate').attr('disabled', false).html('Update Date');

            if (error.status === 422) {
                const errorFields = Object.keys(errorResponse.errors);
                errorFields.map(field => {
                    alertMessage += `- ${errorResponse.errors[field][0]} <br>`;
                });
            } else {
                alertMessage = errorResponse.message;
            }

            // eslint-disable-next-line no-undef
            Swal.fire({
                icon: 'error',
                html: alertMessage
            });
        }
    });

    return false;
});
