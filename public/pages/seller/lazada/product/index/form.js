let lazada_product_normal_attributes = [];
let lazada_product_sku_attributes = [];
let lazada_product_normal_attributes_html = "";
let lazada_product_sku_attributes_html = "";


const getProductAttributesInfoFromLazada = () => {
    var website_id = $('#lazada_shop option:selected').val();
    if (typeof(website_id) === "undefined" || website_id === "") {
        return;
    }

    var lazada_category_id = $("#lazada_category_id option:selected").val();
    if (lazada_category_id === 0 || lazada_category_id === "") {
        return;
    }

    var formData = new FormData();
    formData.append('id', website_id);
    formData.append('lazada_category_id', lazada_category_id);
    $.ajax({
        url: route__lazada_product_get_category_wise_attributes,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {}
    }).done(function(response) {
        if (typeof(response.data) !== "undefined") {
            if (typeof(response.data.sku_attributes) !== "undefined") {
                lazada_product_sku_attributes = response.data.sku_attributes;
            }
            if (typeof(response.data.normal_attributes) !== "undefined") {
                lazada_product_normal_attributes = response.data.normal_attributes;
                getHtmlForLazadaProductAttributes();
                if (typeof(is_edit) !== "undefined" && is_edit) {
                    updateVariationSkuAttributeValuesInEditForm();
                }
            }
        }
    });    
}

let single_select_new_dropdown_id = [];
let mulitple_select_new_dropdown_id = [];
const getHtmlForLazadaProductAttributes = () => {
    let html = `<div class="grid grid-cols-2 gap-4 gap-x-8">`;
    $.each(lazada_product_normal_attributes, function (index, param) {
        if (param.input_type == "text" && !["name"].includes(param.name)) {
            html += getHtmlForTextInputField(param);
        } else if (param.input_type == "numeric") {
            html += getHtmlForNumericInputField(param);
        } else if (param.input_type == "singleSelect") {
            html += getHtmlForSingleSelectDropdown(param);
        } else if (param.input_type == "multiSelect") {
            html += getHtmlForMultipleSelectDropdown(param);
        } else if (param.input_type == "richText") {
            html += getHtmlForSingleSelectDropdown(param);
        } else if (param.input_type == "multiEnumInput") {
            html += getHtmlForMultipleSelectDropdown(param);
        }
    });
    html += `<div></div><div class="col-span-2"><hr/></div></div>`;
    $("#lazada_product_attributes").html(html);
    
    $.each(single_select_new_dropdown_id, function(index, el_id) {
        $("#"+el_id).select2();
    });
    $.each(mulitple_select_new_dropdown_id, function(index, el_id) {
        $("#"+el_id).select2();
    });
}


const getHtmlForTextInputField = (param) => {
    let selected_val = "";
    if (is_edit && typeof(param.name) !== "undefined" && typeof(variation_products_normal_attr_data[param.name]) !== "undefined") {
        selected_val = variation_products_normal_attr_data[param.name];
    }

    let html = `<div>
        <label>`+param.label+` <span class="text-red-600">*</span></label>
        <input type="text" name="`+getDomElementNameForLazadaProductAttribute(param)+`" data-id="`+param.id+`" id="`+getDomElementIdForLazadaProductAttribute(param)+`" 
        class="w-full h-10 px-3 py-2 rounded-md shadow-sm outline-none focus:outline-none border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" 
        value="`+selected_val+`" required/>
    </div>`;
    return html;
}


const getHtmlForNumericInputField = (param) => {
    let selected_val = "";
    if (is_edit && typeof(param.name) !== "undefined" && typeof(variation_products_normal_attr_data[param.name]) !== "undefined") {
        selected_val = variation_products_normal_attr_data[param.name];
    }

    let html = `<div>
        <label>`+param.label+` <span class="text-red-600">*</span></label>
        <input type="number" min="0" name="`+getDomElementNameForLazadaProductAttribute(param)+`" data-id="`+param.id+`" id="`+getDomElementIdForLazadaProductAttribute(param)+`" 
        class="w-full h-10 px-3 py-2 rounded-md shadow-sm outline-none focus:outline-none border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" 
        value="`+selected_val+`" required/>
    </div>`;
    return html;
}


const getHtmlForTextarea = (param) => {
    let selected_val = "";
    if (is_edit && typeof(param.name) !== "undefined" && typeof(variation_products_normal_attr_data[param.name]) !== "undefined") {
        selected_val = variation_products_normal_attr_data[param.name];
    }
    
    let html = `<div>
        <label>`+param.label+` <span class="text-red-600">*</span></label>
        <textarea name="`+getDomElementNameForLazadaProductAttribute(param)+`" data-id="`+param.id+`" id="`+getDomElementIdForLazadaProductAttribute(param)+`" 
        class="w-full rounded-md shadow-sm px-3 py-2 border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 resize-none" 
        rows="15" required>`+selected_val+`</textarea>
    </div>`;
    return html;
}


const getHtmlForSingleSelectDropdown = (param) => {
    if (typeof(param.options) !== "undefined") {
        let options = [];
        if (param.options.length > 0) {
            options = param.options;
        } else {
            if (param.name === "brand") {
                getProductBrandsFromLazada(param);
            } else {
                return "";
            }
        }

        single_select_new_dropdown_id.push(getDomElementIdForLazadaProductAttribute(param));
        let html = `<div>
            <label>`+param.label+` <span class="text-red-600">*</span></label>
            <select name="`+getDomElementNameForLazadaProductAttribute(param)+`" id="`+getDomElementIdForLazadaProductAttribute(param)+`" 
            data-id="`+param.id+`" class="block w-full h-10 px-2 py-2 rounded-md shadow-sm border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 bg-white disabled:bg-gray-100">
            <option disabled="" selected="" value="0">Select a `+param.label+`</option>`;

        let selected_val = "";
        let html_option_selected = false;
        if (is_edit && typeof(param.name) !== "undefined" && typeof(variation_products_normal_attr_data[param.name]) !== "undefined") {
            selected_val = variation_products_normal_attr_data[param.name];
        }
        
        $.each(param.options, function (index, option) {
            if (is_edit && selected_val==option.name) {
                html_option_selected = true;
            }
            html += `<option data-id="`+option.id+`" value="`+option.name+`" `+((is_edit && selected_val==option.name)?"selected":"")+`>`+option.en_name+`</option>`;
        });

        /* In case selected value is missing in "param.options" */
        if (is_edit && !html_option_selected && selected_val !== "") {
            html += `<option value="`+selected_val+`" selected>`+selected_val+`</option>`;
        }
        
        html += `</select></div>`;
        return html;
    }
    return "";
}


const getHtmlForMultipleSelectDropdown = (param) => {
    if (typeof(param.options) !== "undefined" && param.options.length) {
        mulitple_select_new_dropdown_id.push(getDomElementIdForLazadaProductAttribute(param));

        // let html = `<div>
        //     <label>`+param.label+` <span class="text-red-600">*</span></label>
        //     <select name="`+getDomElementNameForLazadaProductAttribute(param)+`" id="`+getDomElementIdForLazadaProductAttribute(param)+`" 
        //     data-id="`+param.id+`" class="block w-full h-10 px-2 py-2 rounded-md shadow-sm border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 bg-white disabled:bg-gray-100"
        //     multiple>
        //     <option disabled="" selected="" value="0">Select a `+param.label+`</option>`;

        let html = `<div>
            <label>`+param.label+` <span class="text-red-600">*</span></label>
            <select name="`+getDomElementNameForLazadaProductAttribute(param)+`" id="`+getDomElementIdForLazadaProductAttribute(param)+`" 
            data-id="`+param.id+`" class="block w-full h-10 px-2 py-2 rounded-md shadow-sm border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 bg-white disabled:bg-gray-100"
            >
            <option disabled="" selected="" value="0">Select a `+param.label+`</option>`;
        $.each(param.options, function (index, option) {
            html += `<option data-id="`+option.id+`" value="`+option.name+`">`+option.en_name+`</option>`;
        });
        html += `</select></div>`;
        return html;
    }
    return "";
}


const getDomElementIdForLazadaProductAttribute = (param) => {
    if (param.attribute_type == "normal") {
        return "product_normal_attribute_"+param.name;
    } else {
        return "product_sku_attribute_"+param.name;
    }
}


const getDomElementNameForLazadaProductAttribute = (param) => {
    if (param.attribute_type == "normal") {
        return "lazada_"+param.name;
    } else {
        return "variation_"+param.name+"[]";
    }
}


const resetLazadaProductAttributesHtml = () => {
    $("#lazada_product_attributes").html("");
}


const resetLazadaVariableProductHtml = () => {
    variation_index = 1;
    $("#variation_options").html("");
}


const getProductBrandsFromLazada = (param) => {
    $.ajax({
        url: route__lazada_product_get_brands,
        type: "POST",
        processData: false,
        contentType: false,
        beforeSend: function() {}
    }).done(function(response) {
        if (typeof(response.data) !== "undefined") {
            let html = `<option disabled `+(is_edit?"":"selected")+` value="0">Select a `+param.label+`</option>`;
            let selected_brand = "";
            if (typeof(variation_products_normal_attr_data) !== "undefined" && typeof(variation_products_normal_attr_data.brand) !== "undefined") {
                selected_brand = variation_products_normal_attr_data.brand;
            }
            $.each(response.data, function(index, option) {
                if (index === 0) {
                    html += `<option value="No Brand" `+((is_edit && selected_brand==option.name)?"selected":"")+`>No Brand</option>`;
                }
                // html += `<option data-id="`+option.brand_id+`" value="`+option.global_identifier+`">`+option.name+`</option>`;
                html += `<option data-id="`+option.brand_id+`" value="`+option.name+`" `+((is_edit && selected_brand==option.name)?"selected":"")+`>`+option.name+`</option>`;
            });
            $("#"+getDomElementIdForLazadaProductAttribute(param)).html(html);
        }
    });    
}


let variation_index = 1;
$(document).on('change', '#product_type', function() {
    var product_type = $(this).val();
    if (typeof(product_type) === "undefined" || product_type === "") {
        return;
    }
    
    // resetLazadaProductAttributesHtml();

    if (product_type === "variable") {
        $("#price").prop("readonly", true);
        $("#price").val("");
        $("#quantity").prop("readonly", true);
        $("#quantity").val("");
        // addVariationOptionNameInputFieldHtml();
        var category_id = $("#lazada_category_id").val();
        if (typeof(category_id) === "undefined" || category_id === null || category_id === "") {
            resetLazadaVariableProductHtml();
        } else {
            addVariationOptionHtml();
        }
    } else {
        $("#price").prop("readonly", false);
        $("#quantity").prop("readonly", false);
        resetLazadaVariableProductHtml();
    }
});


const addVariationOptionHtml = () => {
    let html = `
    <div class="lazada_product_variation" id="lazada_product_variation_`+variation_index+`">
        <div class="grid grid-cols-2 gap-4 gap-x-8 p-4 mb-4 rounded-md mt-5">
            <div class="col-span-2">
                <label>
                    <strong class="variation_index">Variation #`+variation_index+`</strong>
                </label>
                <hr/>
            </div>

            <div class="`+(is_edit?"":"hide")+`">
                <div class="mb-2`+(is_edit?"":"hide")+`">
                    <label class="col-span-2">
                        Variation SKU
                    </label>
                    <input type="text" name="variation_sku[]" 
                    class="w-full h-10 px-3 py-2 rounded-md shadow-sm outline-none focus:outline-none border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 variation_seller_sku" 
                    value="" required />
                </div>
            </div>
            <div class="hide">
                <div class="mb-2">
                    <label class="col-span-2">
                        Variation Name
                    </label>
                    <input type="text" name="variation_name[]" 
                    class="w-full h-10 px-3 py-2 rounded-md shadow-sm outline-none focus:outline-none border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" 
                    value="" class="bg-gray-200" required/>
                </div>
            </div>

            <div>
                <div class="mb-2">
                    <label class="col-span-2">
                        Variation Price
                    </label>
                    <input type="number" name="variation_price[]" 
                    class="w-full h-10 px-3 py-2 rounded-md shadow-sm outline-none focus:outline-none border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1" 
                    value="0" required />
                </div>
            </div>

            <div>
                <div class="mb-2">
                    <label class="col-span-2">
                        Stock
                    </label>
                    <input type="number" name="variation_stock[]" 
                    class="w-full h-10 px-3 py-2 rounded-md shadow-sm outline-none focus:outline-none border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1"  
                    value="0" required />
                </div>
            </div>

            <div class="col-span-2">
                <label>
                    Variation Specific Image
                </label>
            </div>

            <div class="col-span-2" id="lazada_product_variation_existing_images_div_`+variation_index+`"></div>
            <div class="col-span-2" id="lazada_product_variation_images_div_`+variation_index+`"></div>

            <div class="mb-5 edit_variation_preview_image_div">
                <input type="file" name="variation_image[]" 
                class="w-full h-10 px-3 py-2 rounded-md shadow-sm outline-none focus:outline-none border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 add_variation_image_file" 
                id="lazada_variation_specific_img_file_`+variation_index+`" data-variation_index="`+variation_index+`" multiple/>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 gap-x-8 p-4 mb-4 rounded-md mt-5" id="lazada_product_sku_attributes_`+variation_index+`_div">
        </div>

        <div class="grid grid-cols-2 gap-4 gap-x-8 p-4 mb-4 rounded-md mt-5">
            <div>
                <button class="add_more_variation_btn btn-action--blue">Add Another Option</button>
                `+(variation_index !== 1?`<button class="remove_variation_btn btn-action--red">Remove</button>`:``)+`
            </div>
        </div>
    </div>
    `;
    $("#variation_options").append(html);

    getHtmlForLazadaVariableProductSkuAttributes();
}


const addVariationOptionNameInputFieldHtml = () => {
    let html = `
     <div class="grid grid-cols-2 gap-4 gap-x-8">
        <div>
            <label>
                {{ ucwords(__('translation.Option Name')) }} <x-form.required-mark/>
            </label>
            <input type="text" name="option_name" id="option_name" value="" required/>
        </div>
        <div></div>

        <div class="col-span-2"><hr/></div>
    </div>
    `;
    $("#variation_options").append(html);
}


$(document).on("click", ".add_more_variation_btn", function() {
    $(".add_more_variation_btn").each(function(index, el) {
        if(!$(this).hasClass("hide")) {
            $(this).addClass("hide");
        }
    });
    variation_index = variation_index+1;
    addVariationOptionHtml();
});


$(document).on("click", ".remove_variation_btn", function() {
    let conf = confirm("Are you sure you want to remove this variation?");
    if (!conf) {
        return;
    }
    $(this).closest(".lazada_product_variation").remove();
    variation_index = variation_index-1;
    let target = $(".lazada_product_variation:last-child").find(".add_more_variation_btn");
    if (typeof(target) !== "undefined" && target.hasClass("hide")) {
        target.removeClass("hide");
    }
    if (variation_index > 1) {
        $(".lazada_product_variation").each(function(index, el) {
            $(this).find(".variation_index").html("Variation #"+(index+1));
        });
    }
});


const getHtmlForLazadaVariableProductSkuAttributes = () => {
    let html = ``;
    $.each(lazada_product_sku_attributes, function (index, param) {
        if (param.input_type == "text" && !["SellerSku"].includes(param.name)) {
            html += getHtmlForTextInputField(param);
        } else if (param.input_type == "numeric" && !["price"].includes(param.name)) {
            html += getHtmlForNumericInputField(param);
        } else if (param.input_type == "singleSelect") {
            html += getHtmlForSingleSelectDropdown(param);
        } else if (param.input_type == "multiSelect") {
            html += getHtmlForMultipleSelectDropdown(param);
        } else if (param.input_type == "richText") {
            html += getHtmlForSingleSelectDropdown(param);
        }
    });
    $("#lazada_product_sku_attributes_"+variation_index+"_div").html(html);
}


const resetLazadaSubCategoryHtml = () => {
    $("#lazada_category_parent_id_1").html('<option disabled="" selected="" value="0">Select a category</option>');
}


const resetLazadaSubSubCategorytHtml = () => {
    $("#lazada_category_id").html('<option disabled="" selected="" value="0">Select a category</option>');
}


const getLazadaProductAttributeNamesArray = () => {
    let variable_attributes = [];
    $.each(lazada_product_normal_attributes, function(index, attribute) {
        if (!["name"].includes(attribute.name)) {
            variable_attributes.push(attribute.name);
        }
    });
    return variable_attributes;
}


const getLazadaVariableProductSkuAttributeNamesArray = () => {
    let variable_sku_attributes = [];
    $.each(lazada_product_sku_attributes, function(index, attribute) {
        variable_sku_attributes.push(attribute.name);
    });
    return variable_sku_attributes;
}


$(document).on('change', '#lazada_shop', function() {
    var website_id = $(this).val();
    if (typeof(website_id) === "undefined" || website_id === "") {
        return;
    }

    resetLazadaVariableProductHtml();
    resetLazadaProductAttributesHtml();
    resetLazadaSubCategoryHtml();
    resetLazadaSubSubCategorytHtml();

    var formData = new FormData();
    formData.append('id', website_id);
    $.ajax({
        url: route__lazada_product_get_categories,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {}
    }).done(function(response) {
        if (typeof(response.data) !== "undefined") {
            let html = '<option disabled selected value="0">Select a category</option>';
            $.each(response.data, function(index, val) {
                html += '<option value="'+val.category_id+'">'+val.category_name+'</option>';
            });
            $("#lazada_category_parent_id").html(html);
            $("#lazada_category_parent_id").select2();
        }
    });
});


$(document).on('change', '#lazada_category_parent_id', function() {
    var category_parent_id = $(this).val();
    if (typeof(category_parent_id) === "undefined" || category_parent_id === "") {
        return;
    }

    var website_id = $('#lazada_shop option:selected').val();
    if (typeof(website_id) === "undefined" || website_id === "") {
        return;
    }

    resetLazadaVariableProductHtml();
    resetLazadaProductAttributesHtml();
    resetLazadaSubCategoryHtml();
    resetLazadaSubSubCategorytHtml();
    
    var formData = new FormData();
    formData.append('category_parent_id', category_parent_id);
    formData.append('id', website_id);
    $.ajax({
        url: route__lazada_product_get_sub_categories,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {}
    }).done(function(response) {
        if (typeof(response.data) !== "undefined") {
            let html = '<option disabled selected value="0">Select a category</option>';
            $.each(response.data, function(index, val) {
                html += '<option value="'+val.category_id+'">'+val.category_name+'</option>';
            });
            $("#lazada_category_parent_id_1").html(html);
            $("#lazada_category_parent_id_1").select2();
        }
    });
});


$(document).on('change', '#lazada_category_parent_id_1', function() {
    var category_parent_id_1 = $(this).val();
    if (typeof(category_parent_id_1) === "undefined" || category_parent_id_1 === "") {
        return;
    }

    var category_parent_id = $('#lazada_category_parent_id option:selected').val();
    if (typeof(category_parent_id) === "undefined" || category_parent_id === "") {
        return;
    }

    var website_id = $('#lazada_shop option:selected').val();
    if (typeof(website_id) === "undefined" || website_id === "") {
        return;
    }

    resetLazadaVariableProductHtml();
    resetLazadaProductAttributesHtml();
    resetLazadaSubSubCategorytHtml();

    var formData = new FormData();
    formData.append('category_parent_id', category_parent_id_1);
    formData.append('id', website_id);
    $.ajax({
        url: route__lazada_product_get_sub_sub_categories,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {}
    }).done(function(response) {
        if (typeof(response.data) !== "undefined") {
            let html = '<option disabled selected value="0">Select a category</option>';
            $.each(response.data, function(index, val) {
                html += '<option value="'+val.category_id+'">'+val.category_name+'</option>';
            });
            $("#lazada_category_id").html(html);
            $("#lazada_category_id").select2();
        }
    });
});


$(document).on('change', '#lazada_category_id', function() {
    var category_parent_id = $(this).val();
    if (typeof(category_parent_id) === "undefined" || category_parent_id === "") {
        return;
    }

    resetLazadaVariableProductHtml();
    resetLazadaProductAttributesHtml();

    getProductAttributesInfoFromLazada();

    let variation_option_display_interval = setInterval(function () {
        if (lazada_product_sku_attributes.length > 0) {
            var product_type = $("#product_type").find("option:selected").val();
            if (product_type === "variable") {            
                addVariationOptionHtml();
            }
            clearInterval(variation_option_display_interval);
        }
    }, 500);
});


// const updateLazadaProductImagesDivHtml = (data) => {
//     let html = '';
//     $.each(data, function (index, image) {
//         html += '<div>';
//         html += '<div class="mb-5 edit_preview_image_div">';
//         html += '<i class="fa fa-trash remove_lazada_product_image"></i>';
//         html += '<img width="100" height="100" src="'+image+'" alt="image">';
//         html += '</div>';
//         html += '</div>';
//     });

//     if (html.length > 0) {
//         $("#lazada_product_images_div").html(html);
//     }
// }


$(document).on("change", ".add_variation_image_file", function(el) {
    let variation_index_1 = $(this).data("variation_index");
    if (typeof(variation_index_1) === "undefined") {
        return;
    }

    let image_files = $(this).get(0).files;
    $.each(image_files, function (index, file) {
        if (typeof(lazada_product_variation_image_files[variation_index_1]) === "undefined") {
            lazada_product_variation_image_files[variation_index_1] = [];
        }
        lazada_product_variation_image_files[variation_index_1].unshift(file);
    });
    
    let html = ``;
    if (lazada_product_variation_image_files[variation_index_1].length > 0) {
        html += `<div class="grid grid-cols-6">`;
        $.each(lazada_product_variation_image_files[variation_index_1], function (index, file) {
            html += `<div class="mb-5 add_preview_image_div grid grid-cols-6">
                <i class="fa fa-trash remove_lazada_product_new_variation_image" data-variation_index="`+variation_index_1+`" data-image_index="`+index+`" data-image_file_name="`+file.name+`"></i>
                <img width="100" height="100" src="{{asset('img/No_Image_Available.jpg')}}" alt="image" id="lazada_product_variation_image_`+variation_index_1+`_`+index+`" class="mb-3"/>
            </div>`;
            var reader = new FileReader();
            reader.onload = function() {
                $("#lazada_product_variation_image_"+variation_index_1+"_"+index).attr("src", reader.result);
            }
            reader.readAsDataURL(file);
        });
        html += `</div>`;
    }

    if (html.length > 0) {
        $("#lazada_product_variation_images_div_"+variation_index_1).html(html);
    }
});

            
var lazada_product_cover_image_files = [];
var cover_images_counter = 0;
$(document).on("change", ".add_lazada_cover_image_files", function(el) {
    let image_files = $(this).get(0).files;
    $.each(image_files, function (index, file) {
        lazada_product_cover_image_files.unshift(file);
    });
    
    let html = "";
    $.each(lazada_product_cover_image_files, function (index, file) {
        html += `<div class="mb-5 add_preview_image_div add_preview_image_cover_div">
            <i class="fa fa-trash remove_lazada_cover_product_image" data-image_index="`+index+`" data-image_file_name="`+file.name+`"></i>
            <img width="100" height="100" src="{{asset('img/No_Image_Available.jpg')}}" alt="image" id="lazada_product_cover_image_`+index+`" class="mb-3"/>
        </div>`;
        var reader = new FileReader();
        reader.onload = function() {
            $("#lazada_product_cover_image_"+index).attr("src", reader.result);
        }
        reader.readAsDataURL(file);
    });

    if (html.length > 0) {
        $("#lazada_product_cover_images_div").html(html);
    }
});


$(document).on('click', '.remove_lazada_cover_product_image', function() {
    let conf = confirm("Are you sure you want to remove this image?");
    if (!conf) {
        return;
    }

    var image_index = $(this).data("image_index");
    if (typeof(image_index) === "undefined") {
        return;
    }

    var image_file_name = $(this).data("image_file_name");
    if (typeof(image_file_name) === "undefined") {
        return;
    }

    $("#lazada_product_cover_image_"+image_index).closest(".add_preview_image_div").remove();
    
    lazada_product_cover_image_files = lazada_product_cover_image_files.filter(function(file) {
        return file.name !== image_file_name;
    });
});


$(document).on('click', '.remove_lazada_product_new_variation_image', function() {
    let conf = confirm("Are you sure you want to remove this image?");
    if (!conf) {
        return;
    }

    var image_index = $(this).data("image_index");
    if (typeof(image_index) === "undefined") {
        return;
    }

    var image_file_name = $(this).data("image_file_name");
    if (typeof(image_file_name) === "undefined") {
        return;
    }

    $(this).closest(".add_preview_image_div").remove();
    
    let variation_index_1 = $(this).data("variation_index");
    if (typeof(variation_index_1) !== "undefined" && typeof(lazada_product_variation_image_files[variation_index_1]) !== "undefined") {
        lazada_product_variation_image_files[variation_index_1] = lazada_product_variation_image_files[variation_index_1].filter(function(file) {
            return file.name !== image_file_name;
        });
    }
});


$(document).on('click', '.remove_lazada_product_exiting_variation_image', function() {
    let conf = confirm("Are you sure you want to remove this image?");
    if (!conf) {
        return;
    }
    
    let sku = $(this).closest(".lazada_product_variation").find(".variation_seller_sku").val();
    let image = $(this).parent(".edit_preview_image_div").find("img").attr("src");

    $(this).closest(".edit_preview_image_div").remove();
    
    if (typeof(sku) !== "undefined" && typeof(image) !== "undefined") {
        var formData = new FormData();
        formData.append('_token', $('meta[name=csrf-token]').attr('content'));
        formData.append('sku', sku);
        formData.append('image', image);
        
        $.ajax({
            url: route__lazada_product_delete_product_image,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {}
        }).done(function(response) {
            if (response.success) {
                $(this).closest(".add_preview_image_div").remove();
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
        });
    }
});


$(document).on("click", "#btn_submit_product", function(e) {
    e.preventDefault();
    $("#form-message").html("");
    $('#form-message').removeClass("alert alert-danger alert-success alert-danger");
    
    var product_type = $('#product_type').find('option:selected').val();
    if (typeof(product_type) === "undefined" || product_type === "") {
        alert("Product type is not valid");
        return;
    }

    var website_id = $('#lazada_shop').val();
    if (typeof(website_id) === "undefined" || website_id === "") {
        alert("Website is not valid");
        return;
    }

    var lazada_category_id = $("#lazada_category_id option:selected").val();
    if (lazada_category_id === 0 || lazada_category_id === "") {
        let conf = confirm("No categroy is selected for this product, are you sure?");
        if (!conf) {
            return;
        }
    }

    var name = $('#name').val();
    if (typeof(name) === "undefined" || name === "") {
        alert("Name is not valid");
        return;
    }

    var code = $('#sku').val();
    if (typeof(code) === "undefined" || sku === "") {
        alert("SKU is not valid");
        return;
    }

    var description = $('#description').val();
    if (typeof(description) === "undefined") {
        alert("Description is not valid");
        return;
    }
    var short_description = $('#short_description').val();
    var description_en = $('#description_en').val();
    var short_description_en = $('#short_description_en').val();

    if (lazada_product_cover_image_files.length === 0) {
        if (!is_edit) {
            alert("Need at least 1 cover image");
            return;
        } else {
            if($(".edit_preview_image_div").length === 0 && $("#lazada_product_cover_images_div").find(".add_preview_image_div").length === 0) {
                alert("Need at least 1 cover image");
                return;
            }
        }
    } else if (lazada_product_cover_image_files.length > 8) {
        alert("At most 8 images can be uploaded");
        return;
    }

    var price = 0;
    var quantity = 0;
    
    var formData = new FormData();
    formData.append('_token', $('meta[name=csrf-token]').attr('content'));
    formData.append('website_id', website_id);
    formData.append('type', product_type);
    formData.append('lazada_category_id', lazada_category_id);
    formData.append('name', name);
    formData.append('sku', code);
    formData.append('description', description);
    formData.append('short_description', short_description);
    formData.append('description_en', description_en);
    formData.append('short_description_en', short_description_en);

    if (is_edit) {
        var id = $('#id').val();
        if (typeof(id) === "undefined" || id === "") {
            alert("Id is not valid");
            return;
        }
        formData.append('item_id', id);
    }

    if (product_type === "variable") {
        formData.append('total_variations', $(".lazada_product_variation").length);

        var variation_name = $("input[name='variation_name[]']").map(function(){return $(this).val();}).get();
        var variation_sku = $("input[name='variation_sku[]']").map(function(){return $(this).val();}).get();
        var variation_price = $("input[name='variation_price[]']").map(function(){return $(this).val();}).get();
        var variation_stock = $("input[name='variation_stock[]']").map(function(){return $(this).val();}).get();
        
        /* Variation product basic info. */
        formData.append('variation_name', JSON.stringify(variation_name));
        formData.append('variation_sku', JSON.stringify(variation_sku));
        formData.append('variation_price', JSON.stringify(variation_price));
        formData.append('variation_stock', JSON.stringify(variation_stock));

        /* Variation product images. */
        var variation_images = lazada_product_variation_image_files;
        for (j=1; j<variation_images.length; j++) {
            formData.append('variation_images_count_'+(j-1), variation_images[j].length);
            $.each(variation_images[j], function (index, file) {
                formData.append('variation_image_'+(j-1)+'_'+index, file);
            });
        }
        if (variation_images.length == 0) {
            formData.append('variation_images_count', 0);
        } else {
            /* "variation_images", index 0 is empty, thats why 1 is subtracted. */
            formData.append('variation_images_count', variation_images.length-1);
        }

        /* Noraml Attributes. */
        let variable_attribute_names = getLazadaProductAttributeNamesArray();
        if (variable_attribute_names.length > 0) {
            $.each(variable_attribute_names, function (index, name) {
                let variation_data = "";
                let tag = $("#product_normal_attribute_"+name).get(0).tagName;
                if (tag === "INPUT") {
                    variation_data = $("#product_normal_attribute_"+name).val();
                } else if (tag === "SELECT") {
                    variation_data = $("#product_normal_attribute_"+name).find("option:selected").val();
                }
                if (typeof(variation_data) !== "undefined" && variation_data !== null && variation_data !== "") {
                    formData.append('lazada_'+name, variation_data);
                } 
            });
        }

        let variable_sku_attributes = getLazadaVariableProductSkuAttributeNamesArray();
        if (variable_sku_attributes.length > 0) {
            $.each(variable_sku_attributes, function (index, name) {
                let variation_data = $("input[name='variation_"+name+"[]']").map(function(){return $(this).val();}).get();
                formData.append('variation_'+name, JSON.stringify(variation_data));
            });
        }
    } else {
        price = $('#price').val();
        if (typeof(price) === "undefined" || price === "") {
            alert("Price is not valid");
            return;
        }

        quantity = $('#quantity').val();
        if (typeof(quantity) === "undefined" || quantity === "") {
            alert("Quantity is not valid");
            return;
        }
    }

    /* Price & Quantity */
    formData.append('price', price);
    formData.append('quantity', quantity);
    
    $.each(lazada_product_cover_image_files, function(index, file) {
        formData.append('cover_image_'+index, file);
    });
    formData.append('cover_images_count', lazada_product_cover_image_files.length);

    /* Brand */
    var brand = "No Brand";
    var selected_brand = $('#product_normal_attribute_brand').find('option:selected').val();
    if (typeof(selected_brand) !== "undefined" && selected_brand !== "" && selected_brand !== "0") {
        brand = selected_brand;
    }
    formData.append('lazada_brand', brand);
    
    let confirm_message = "Please confirm your want to create this new product";
    if (is_edit) {
        confirm_message = "Please confirm your want to update this product";
    }
    let conf = confirm(confirm_message);
    if (!conf) {
        return;
    }

    $("#btn_submit_product").prop("disabled", true);

    $.ajax({
        url: lazada_product_form_url,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#form-message').html("Please wait");
            $('#form-message').addClass("alert alert-warning");
        }
    }).done(function(response) {
        $('#form-message').removeClass("alert-warning");
        if (response.success) {
            if (typeof(response.message) !== "undefined") {
                $("#form-message").html(response.message);
                $('#form-message').addClass("alert-success");
                Swal.fire(
                    'Success!',
                    response.message,
                    'success'
                );
                setTimeout(function() {
                    window.location.href = '{{ route("lazada.product.index") }}';
                }, 2000);
            }
        } else {
            $("#btn_submit_product").prop("disabled", false);
            if (typeof(response.message) !== "undefined") {
                $("#form-message").html(response.message);
                $('#form-message').addClass("alert-danger");
            }
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        $('#form-message').removeClass("alert-warning");
        $('#form-message').addClass("alert-danger");
        let html = "";
        $.each(jqXHR.responseJSON.errors, function(index, error) {
            html += "<p>"+error[0]+"</p>";
        });
        $('#form-message').html(html);
        $("#btn_submit_product").prop("disabled", false);
    });
});