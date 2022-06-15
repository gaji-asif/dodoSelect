$('body').on('change', '.company_logo__field', function (event) {
    const selectedImage = event.target.files[0];
    const fileReader = new FileReader();
    const productId = $(this).data('id');

    const $productImageWrapper = $(this).parent('.company_logo__wrapper');
    const $imgThumbnailElement = $productImageWrapper.find('.company_logo__thumbnail');
    const $removeButtonThumbnailElement = $productImageWrapper.find('.company_logo__remove_button');

    fileReader.onload = fileEvent => {
        const imageUrl = fileEvent.target.result;

        $imgThumbnailElement.removeClass('hidden');
        $imgThumbnailElement.attr('src', imageUrl);

        $removeButtonThumbnailElement.removeClass('hidden');
        $removeButtonThumbnailElement.addClass('block');
    };

    fileReader.readAsDataURL(selectedImage);
});

$('body').on('click', '.company_logo__remove_button', function () {
    const $productImageWrapper = $(this).closest('.company_logo__wrapper');
    const $imageInputField = $productImageWrapper.find('.company_logo__field');
    const $imageThumbnail = $productImageWrapper.find('.company_logo__thumbnail');

    $(this).addClass('hidden').removeClass('block');

    $imageThumbnail.addClass('hidden');
    $imageThumbnail.attr('src', '#');

    $imageInputField.val(null);
});

$('#__formUpdateCompanyInfo').on('submit', function (event) {
    event.preventDefault();

    const formData = new FormData($(this)[0]);

    $.ajax({
        type: 'POST',
        url: $(this).attr('action'),
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#__btnSubmitUpdateCompanyInfo').attr('disabled', true).html('Processing...');
        },
        success: function (response) {
            const alertMessage = response.message;

            $('#__btnSubmitUpdateCompanyInfo').attr('disabled', false).html('Update Date');

            // eslint-disable-next-line no-undef
            Swal.fire({
                icon: 'success',
                html: alertMessage
            });
        },
        error: function (error) {
            const errorResponse = error.responseJSON;
            let alertMessage = '';

            $('#__btnSubmitUpdateCompanyInfo').attr('disabled', false).html('Update Date');

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
