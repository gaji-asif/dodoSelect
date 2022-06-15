/* eslint-disable no-undef */
$('#__lang_switcher').on('change', function () {
    const selectedLang = $(this).val();

    window.location.href = route('lang-switcher', { lang: selectedLang });
});
