/* eslint-disable no-undef */
$('#__shipping_provinceEditOrder').select2({
    width: 'resolve',
    placeholder: '- Select Province -',
    allowClear: true,
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

$('#__shipping_districtEditOrder').select2({
    width: 'resolve',
    placeholder: '- Select District -',
    allowClear: true,
    ajax: {
        type: 'GET',
        url: selectDistrictUrl,
        data: function (params) {
            return {
                page: params.page || 1,
                search: params.term,
                province_code: shippingAddress.provinceCode
            };
        },
        delay: 500
    }
});

$('#__shipping_sub_districtEditOrder').select2({
    width: 'resolve',
    placeholder: '- Select Sub District -',
    allowClear: true,
    ajax: {
        type: 'GET',
        url: selectSubDistrictUrl,
        data: function (params) {
            return {
                page: params.page || 1,
                search: params.term,
                district_code: shippingAddress.districtCode
            };
        },
        delay: 500
    }
});

$('#__shipping_postcodeEditOrder').select2({
    width: 'resolve',
    placeholder: '- Select Postal Code -',
    allowClear: true,
    ajax: {
        type: 'GET',
        url: selectPostCodeUrl,
        data: function (params) {
            return {
                page: params.page || 1,
                search: params.term,
                sub_district_code: shippingAddress.subDistrictCode
            };
        },
        delay: 500
    }
});

$('#__shipping_provinceEditOrder').on('select2:select', function (event) {
    const selectedData = event.params.data;
    shippingAddress.provinceCode = selectedData.code;

    $('#__shipping_districtEditOrder').attr('disabled', false).trigger('change');

    $('#__shipping_districtEditOrder').val(null).trigger('change');
    $('#__shipping_sub_districtEditOrder').val(null).trigger('change');
    $('#__shipping_postcodeEditOrder').val(null).trigger('change');
});

$('#__shipping_provinceEditOrder').on('select2:clear', function (event) {
    shippingAddress.provinceCode = -1;
    shippingAddress.districtCode = -1;
    shippingAddress.subDistrictCode = -1;

    $('#__shipping_districtEditOrder').attr('disabled', true).trigger('change');
    $('#__shipping_sub_districtEditOrder').attr('disabled', true).trigger('change');
    $('#__shipping_postcodeEditOrder').attr('disabled', true).trigger('change');

    $('#__shipping_districtEditOrder').val(null).trigger('change');
    $('#__shipping_sub_districtEditOrder').val(null).trigger('change');
    $('#__shipping_postcodeEditOrder').val(null).trigger('change');
});

$('#__shipping_districtEditOrder').on('select2:select', function (event) {
    const selectedData = event.params.data;
    shippingAddress.districtCode = selectedData.code;

    $('#__shipping_sub_districtEditOrder').attr('disabled', false).trigger('change');

    $('#__shipping_sub_districtEditOrder').val(null).trigger('change');
    $('#__shipping_postcodeEditOrder').val(null).trigger('change');
});

$('#__shipping_districtEditOrder').on('select2:clear', function (event) {
    shippingAddress.districtCode = -1;
    shippingAddress.subDistrictCode = -1;

    $('#__shipping_sub_districtEditOrder').attr('disabled', true).trigger('change');
    $('#__shipping_postcodeEditOrder').attr('disabled', true).trigger('change');

    $('#__shipping_sub_districtEditOrder').val(null).trigger('change');
    $('#__shipping_postcodeEditOrder').val(null).trigger('change');
});

$('#__shipping_sub_districtEditOrder').on('select2:select', function (event) {
    const selectedData = event.params.data;
    shippingAddress.subDistrictCode = selectedData.code;

    $('#__shipping_postcodeEditOrder').attr('disabled', false).trigger('change');

    $('#__shipping_postcodeEditOrder').val(null).trigger('change');
});

$('#__shipping_sub_districtEditOrder').on('select2:clear', function (event) {
    shippingAddress.subDistrictCode = -1;

    $('#__shipping_postcodeEditOrder').attr('disabled', true).trigger('change');

    $('#__shipping_postcodeEditOrder').val(null).trigger('change');
});
