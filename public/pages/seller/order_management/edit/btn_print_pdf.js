$('.btn-print-pdf').on('click', function () {
    const downloadUrl = $(this).data('url');
    window.location.href = downloadUrl;
});
