/* eslint-disable no-undef */
$('input[name="tax_enable"]').on('change', function () {
    calculateCartTotal();

    $('#__taxRateCartTotal').html(`${taxRateValue}%`);

    $('#__taxCompanyInfoWrapper').hide('slow');
    $('#__taxRateRowCartTotals').hide('slow');

    if (parseInt($(this).val()) === taxEnableYes) {
        $('#__taxCompanyInfoWrapper').show('slow');
    }

    if (parseInt($(this).val()) === taxEnableYes && taxRateValue > 0) {
        $('#__taxRateRowCartTotals').show('slow');
    }
});

$('#__company_provinceBuyerPage').select2({
    width: 'resolve',
    placeholder: '- Select Province -',
    ajax: {
        type: 'GET',
        url: selectProvinceUrl,
        data: function (params) {
            return {
                page: params.page || 1,
                search: params.term
            };
        },
        delay: 500
    }
});

$('#__company_districtBuyerPage').select2({
    width: 'resolve',
    placeholder: '- Select District -',
    ajax: {
        type: 'GET',
        url: selectDistrictUrl,
        data: function (params) {
            return {
                page: params.page || 1,
                search: params.term,
                province_code: companyAddress.provinceCode
            };
        },
        delay: 500
    }
});

$('#__company_sub_districtBuyerPage').select2({
    width: 'resolve',
    placeholder: '- Select Sub District -',
    ajax: {
        type: 'GET',
        url: selectSubDistrictUrl,
        data: function (params) {
            return {
                page: params.page || 1,
                search: params.term,
                district_code: companyAddress.districtCode
            };
        },
        delay: 500
    }
});

$('#__company_postcodeBuyerPage').select2({
    width: 'resolve',
    placeholder: '- Select Postal Code -',
    ajax: {
        type: 'GET',
        url: selectPostCodeUrl,
        data: function (params) {
            return {
                page: params.page || 1,
                search: params.term,
                sub_district_code: companyAddress.subDistrictCode
            };
        },
        delay: 500
    }
});

$('#__company_provinceBuyerPage').on('select2:select', function (event) {
    const selectedData = event.params.data;
    companyAddress.provinceCode = selectedData.code;
    companyAddress.districtCode = -1;
    companyAddress.subDistrictCode = -1;

    $('#__company_districtBuyerPage').attr('disabled', false).trigger('change');
    $('#__company_sub_districtBuyerPage').attr('disabled', true).trigger('change');
    $('#__company_postcodeBuyerPage').attr('disabled', true).trigger('change');

    $('#__company_districtBuyerPage').val(null).trigger('change');
    $('#__company_sub_districtBuyerPage').val(null).trigger('change');
    $('#__company_postcodeBuyerPage').val(null).trigger('change');
});

$('#__company_provinceBuyerPage').on('select2:clear', function (event) {
    companyAddress.provinceCode = -1;
    companyAddress.districtCode = -1;
    companyAddress.subDistrictCode = -1;

    $('#__company_districtBuyerPage').attr('disabled', true).trigger('change');
    $('#__company_sub_districtBuyerPage').attr('disabled', true).trigger('change');
    $('#__company_postcodeBuyerPage').attr('disabled', true).trigger('change');

    $('#__company_districtBuyerPage').val(null).trigger('change');
    $('#__company_sub_districtBuyerPage').val(null).trigger('change');
    $('#__company_postcodeBuyerPage').val(null).trigger('change');
});

$('#__company_districtBuyerPage').on('select2:select', function (event) {
    const selectedData = event.params.data;
    companyAddress.districtCode = selectedData.code;
    companyAddress.subDistrictCode = -1;

    $('#__company_sub_districtBuyerPage').attr('disabled', false).trigger('change');
    $('#__company_postcodeBuyerPage').attr('disabled', true).trigger('change');

    $('#__company_sub_districtBuyerPage').val(null).trigger('change');
    $('#__company_postcodeBuyerPage').val(null).trigger('change');
});

$('#__company_districtBuyerPage').on('select2:clear', function (event) {
    companyAddress.districtCode = -1;
    companyAddress.subDistrictCode = -1;

    $('#__company_sub_districtBuyerPage').attr('disabled', true).trigger('change');
    $('#__company_postcodeBuyerPage').attr('disabled', true).trigger('change');

    $('#__company_sub_districtBuyerPage').val(null).trigger('change');
    $('#__company_postcodeBuyerPage').val(null).trigger('change');
});

$('#__company_sub_districtBuyerPage').on('select2:select', function (event) {
    const selectedData = event.params.data;
    companyAddress.subDistrictCode = selectedData.code;

    $('#__company_postcodeBuyerPage').attr('disabled', false).trigger('change');

    $('#__company_postcodeBuyerPage').val(null).trigger('change');
});

$('#__company_sub_districtBuyerPage').on('select2:clear', function (event) {
    companyAddress.subDistrictCode = -1;

    $('#__company_postcodeBuyerPage').attr('disabled', true).trigger('change');

    $('#__company_postcodeBuyerPage').val(null).trigger('change');
});
