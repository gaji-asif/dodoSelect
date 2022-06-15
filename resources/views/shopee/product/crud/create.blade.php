<x-app-layout>
    @section('title')
        {{ __('translation.Purchase Order') }}
    @endsection

    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.12.1/jquery-ui.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
        <link rel="stylesheet" href="{{ asset('css/custom_css.css') }}">
    @endpush

    @push('bottom_css')
        <style>
            .dataTable tbody tr td {
                border-width: 0px !important;
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }
            .card:hover {
                cursor: pointer;
            }
            .card-active {
                background-color: #d5dade;
                color: #ffffff !important;
            }
            .shopee_shop_for_order_sync_form .select2-selection--multiple {
                height: 120px !important;
                overflow-x: scroll !important;
            }
            .add_preview_image_div {
                position: relative;
            } 
            .add_preview_image_div .fa-trash {
                position: absolute;
                top: 5px;
                margin-left: 5px;
                z-index: 99999;
            }
            .add_preview_image_div .fa-trash:hover {
                cursor: pointer;
            }
            .hide {
                display: none !important;
            }
        </style>
    @endpush

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Shopee - Product'))
    <div class="col-span-12">
        <x-card.card-default>
            <x-card.header>
                <x-card.title>
                    {{ ucwords(__('translation.shopee_product_create')) }} 
                </x-card.title>
            </x-card.header>

            <x-card.body>
                @csrf
                <div class="grid grid-cols-2 gap-4 gap-x-8">
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.shopee_select_shop')) }}
                        </x-label>
                        <x-select name="shopee_shop" id="shopee_shop">
                            <option disabled selected value="0">Select a shop</option>
                            @foreach ($shopee_shops as $shop)
                            <option value="{{ $shop->shop_id }}" > {{ $shop->shop_name }} </option>
                            @endforeach
                        </x-select>
                    </div>
                    <div></div>
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.product_name')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="text" name="name" id="name" :value="old('name') ?? ''" required />
                    </div>
                    <div >
                        <x-label>
                            {{ ucwords(__('translation.product_code')) }}
                        </x-label>
                        <x-input type="text" name="sku" id="sku" :value="old('sku') ?? ''" required />
                    </div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.product_type')) }} <x-form.required-mark/>
                        </x-label>
                        <x-select name="product_type" id="product_type">
                            <option disabled selected value="0">Select a product type</option>
                            <option value="variable">Variable</option>
                            <option value="simple">Simple</option>
                        </x-select>
                    </div>
                    <div></div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.price')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="number" name="price" id="price" :value="old('price') ?? ''" steps="0.001" required/>
                    </div>
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.quantity')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="number" name="quantity" id="quantity" :value="old('quantity') ?? ''" required/>
                    </div>

                    <div class="col-span-2">
                        <x-label>
                            {{ ucwords(__('translation.product_description')) }}
                        </x-label>
                        <x-textarea name="description" id="description" rows="15" required>{{ old('specifications') ?? '' }}</x-textarea>
                    </div>

                    <div class="col-span-2"><hr/></div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.shopee_parent_category')) }}
                        </x-label>
                        <x-select name="shopee_category_parent_id" id="shopee_category_parent_id">
                            <option disabled selected value="0">Select a category</option>
                        </x-select>
                    </div>
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.shopee_sub_category')) }}
                        </x-label>
                        <x-select name="shopee_category_parent_id_1" id="shopee_category_parent_id_1">
                            <option disabled selected value="0">Select a category</option>
                        </x-select>
                    </div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.shopee_sub_sub_category')) }}
                        </x-label>
                        <x-select name="shopee_category_id" id="shopee_category_id">
                            <option disabled selected value="0">Select a category</option>
                        </x-select>
                    </div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.brand')) }} <x-form.required-mark/>
                        </x-label>
                        <x-select name="brand" id="brand" data-attribute_id="-1">
                            <option disabled selected value="0">Select a brand</option>
                        </x-select>
                    </div>

                    <div class="col-span-2"><hr/></div>
                </div>

                <div id="shopee_product_attributes"></div>
                
                <div id="shopee_product_logistics"></div>

                <div class="grid grid-cols-2 gap-4 gap-x-8">
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.weight')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="number" name="weight" id="weight" :value="old('weight') ?? ''" required/>
                    </div>
                    <div></div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.package_length')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="number" name="package_length" id="package_length" :value="old('package_length') ?? ''" required/>
                    </div>
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.package_width')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="number" name="package_width" id="package_width" :value="old('package_width') ?? ''" required/>
                    </div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.package_height')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="number" name="package_height" id="package_height" :value="old('package_height') ?? ''" required/>
                    </div>

                    <div class="col-span-2"><hr/></div>
                </div>

                <div class="grid grid-cols-2 gap-4 gap-x-8 mb-2">
                    <x-label class="col-span-2">
                        {{ __('translation.Product Images') }} <x-form.required-mark />
                    </x-label>
                </div>
                <div class="grid grid-cols-6 gap-4 gap-x-8" id="shopee_product_cover_images_div">
                </div>

                <div class="grid grid-cols-2 gap-4 gap-x-8">
                    <div>
                        <div class="mb-5">
                            <x-input type="file" class="add_shopee_cover_image_files" name="product_images" id="product_images" multiple/>
                        </div>
                    </div>
                </div>

                <div class="col-span-2"><hr/></div>
                <div id="variation_options"></div>

                <div class="">
                    <p class="" id="form-message"></p>
                </div>

                <div class="mt-5 pb-3">
                    <div class="flex flex-row items-center justify-center gap-2">
                        <x-button type="submit" color="blue" id="btn_submit_product">
                            {{ __('translation.save_data') }}
                        </x-button>
                    </div>
                </div>
            </x-card.body>
        </x-card.card-default>
    </div>
    @endif

    @push('bottom_js')
        <script src="{{ asset('js/jquery.validate.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.12.1/jquery-ui.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>

        <script>
            const getLogisticsInfoFromShopee = () => {
                var website_id = $('#shopee_shop option:selected').val();
                if (typeof(website_id) === "undefined" || website_id === "") {
                    return;
                }

                var formData = new FormData();
                formData.append('shopee_shop_id', website_id);
                $.ajax({
                    url: '{{ route("shopee.product.get_logistics") }}',
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {}
                }).done(function(response) {
                    if (typeof(response.data) !== "undefined") {
                        if (response.data.length > 0) {
                            let html = `<div class="grid grid-cols-2 gap-4 gap-x-8 mb-2">
                                <label class="col-span-2">
                                    Logistic <span class="text-red-600">*</span>
                                </label>
                            </div>
                            <div class="grid grid-cols-2 gap-4 gap-x-8 p-4 mb-4 bg-gray-200 bg-opacity-80 rounded-md shopee_product_logistic mt-5">`;
                            $.each(response.data, function(index, logistic) {
                                html += `<div class="mb-2">
                                    <x-label class="">
                                        <strong>`+logistic.logistic_name+`</strong>
                                        <x-input type="hidden" name="logistic_id[]" value="`+logistic.logistic_id+`" required/>
                                    </x-label>

                                    <input type="radio" name="logistic_enabled_`+logistic.logistic_id+`" value="yes" class="mx-2"><label>Enable</label>
                                    <input type="radio" name="logistic_enabled_`+logistic.logistic_id+`" value="no" class="mx-2" checked><label>Disable</label>
                                </div>`;
                            });
                            html += `</div><div class="col-span-2"><hr/></div>`;
                            $("#shopee_product_logistics").html(html);
                        } else {
                            $("#shopee_product_logistics").html("");
                        }
                    }
                });    
            }

            const getAttributesInfoFromShopee = () => {
                var website_id = $('#shopee_shop option:selected').val();
                if (typeof(website_id) === "undefined" || website_id === "") {
                    return;
                }

                var shopee_category_id = $("#shopee_category_id option:selected").val();
                if (shopee_category_id === 0 || shopee_category_id === "") {
                    return;
                }

                var formData = new FormData();
                formData.append('shopee_shop_id', website_id);
                formData.append('shopee_category_id', shopee_category_id);
                $.ajax({
                    url: '{{ route("shopee.product.get_attributes") }}',
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {}
                }).done(function(response) {
                    if (typeof(response.data) !== "undefined") {
                        $.each(response.data, function(index, attribute) {
                            if (attribute.attribute_name == "Brand") {
                                $("#brand").data("attribute_id", attribute.attribute_id);
                                html = '<option disabled selected value="0">Select a brand</option>';
                                $.each(attribute.options, function(index, option) {
                                    html += '<option value="'+option+'">'+option+'</option>';
                                });
                                $("#brand").html(html);
                            }
                        })
                    }
                });    
            }

            $(document).on("click", "#btn_submit_product", function(e) {
                e.preventDefault();
                $("#form-message").html("");
                $('#form-message').removeClass("alert alert-danger alert-success alert-danger");

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

                var website_id = $('#shopee_shop').val();
                if (typeof(website_id) === "undefined" || website_id === "") {
                    alert("Website is not valid");
                    return;
                }

                var shopee_category_id = $("#shopee_category_id option:selected").val();
                if (shopee_category_id === 0 || shopee_category_id === "") {
                    let conf = confirm("No categroy is selected for this product, are you sure?");
                    if (!conf) {
                        return;
                    }
                }

                var product_type = $('#product_type').val();
                if (typeof(product_type) === "undefined" || product_type === "") {
                    alert("Product type is not valid");
                    return;
                }

                if (shopee_product_cover_image_files.length === 0) {
                    alert("Need at least 1 cover image");
                    return;
                } else if (shopee_product_cover_image_files.length > 9) {
                    alert("At most 9 images can be uploaded");
                    return;
                }

                var brand = $("#brand option:selected").val();
                if (typeof(brand) === "undefined" || brand === "") {
                    alert("Brand is not valid");
                    return;
                }
                var brand_attribute_id = $("#brand").data("attribute_id");
                if (typeof(brand_attribute_id) === "undefined" || brand_attribute_id === "-1") {
                    return;
                }

                var weight = $('#weight').val();
                if (typeof(weight) === "undefined" || weight === "") {
                    alert("Weight is not valid");
                    return;
                }

                var package_length = $('#package_length').val();
                if (typeof(package_length) === "undefined" || package_length === "") {
                    alert("Package length is not valid");
                    return;
                }

                var package_width = $('#package_width').val();
                if (typeof(package_width) === "undefined" || package_width === "") {
                    alert("Package width is not valid");
                    return;
                }

                var package_height = $('#package_height').val();
                if (typeof(package_height) === "undefined" || package_height === "") {
                    alert("Package height is not valid");
                    return;
                }

                var logistic_ids = $("input[name='logistic_id[]']").map(function(){return $(this).val();}).get();
                var logistic_data = [];
                $.each(logistic_ids, function(index, logistic_id) {
                    logistic_data.unshift({
                        'logistic_id': logistic_id,
                        'logistic_enabled': $("input[name='logistic_enabled_"+logistic_id+"']:checked").val()
                    });
                });

                var price = 0;
                var quantity = 0;
                var formData = new FormData();
                formData.append('_token', $('meta[name=csrf-token]').attr('content'));
                if (product_type === "variable") {
                    formData.append('total_variations', $(".shopee_product_variation").length);

                    var variation_name = $("input[name='variation_name[]']").map(function(){return $(this).val();}).get();
                    var variation_sku = $("input[name='variation_sku[]']").map(function(){return $(this).val();}).get();
                    var variation_price = $("input[name='variation_price[]']").map(function(){return $(this).val();}).get();
                    var variation_stock = $("input[name='variation_stock[]']").map(function(){return $(this).val();}).get();
                    
                    formData.append('variation_name', JSON.stringify(variation_name));
                    formData.append('variation_sku', JSON.stringify(variation_sku));
                    formData.append('variation_price', JSON.stringify(variation_price));
                    formData.append('variation_stock', JSON.stringify(variation_stock));

                    // formData.append('variation_image', variation_image);
                    var variation_image = $("input[name='variation_image[]']").map(function(){return $(this).val();}).get();
                    $.each(variation_image, function(index, file) {
                        var variation_image = $("input[name='variation_image[]']").get(index).files[0];
                        if (typeof(variation_image) !== "undefined") {
                            formData.append('variation_image_'+index, variation_image);
                        } else {
                            formData.append('variation_image_'+index, "");
                        }
                    });
                    formData.append('variation_images_count', variation_image.length);

                    let option_name = $("#option_name").val();
                    if (typeof(option_name) === "undefined" || option_name === "") {
                        alert("Option name is not valid")
                        return;
                    }
                    formData.append('variation_option_name', option_name);
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
                formData.append('name', name);
                formData.append('sku', code);
                formData.append('type', product_type);
                formData.append('price', price);
                formData.append('description', description);
                formData.append('quantity', quantity);
                formData.append('shopee_category_id', shopee_category_id);
                formData.append('website_id', website_id);
                formData.append('weight', weight);
                formData.append('package_length', package_length);
                formData.append('package_width', package_width);
                formData.append('package_height', package_height);
                
                $.each(shopee_product_cover_image_files, function(index, file) {
                    formData.append('cover_image_'+index, file);
                });
                formData.append('cover_images_count', shopee_product_cover_image_files.length);

                formData.append('brand', brand);
                formData.append('brand_attribute_id', brand_attribute_id);
                formData.append('logistic_data', JSON.stringify(logistic_data));
                
                let conf = confirm("Please confirm your want to create this product");
                if (!conf) {
                    return;
                }

                $("#btn_submit_product").prop("disabled", true);

                $.ajax({
                    url: '{{ route("shopee.product.store_product") }}',
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
                                window.location.href = '{{ route("shopee.product.index") }}';
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
            
            var shopee_product_cover_image_files = [];
            var cover_images_counter = 0;
            $(document).on("change", ".add_shopee_cover_image_files", function(el) {
                let image_files = $(this).get(0).files;
                $.each(image_files, function (index, file) {
                    shopee_product_cover_image_files.unshift(file);
                });
                
                let html = "";
                $.each(shopee_product_cover_image_files, function (index, file) {
                    html += `<div class="mb-5 add_preview_image_div">
                        <i class="fa fa-trash remove_shopee_product_image" data-image_index="`+index+`" data-image_file_name="`+file.name+`"></i>
                        <img width="100" height="100" src="{{asset('img/No_Image_Available.jpg')}}" alt="image" id="shopee_product_cover_image_`+index+`" class="mb-3"/>
                    </div>`;
                    var reader = new FileReader();
                    reader.onload = function() {
                        $("#shopee_product_cover_image_"+index).attr("src", reader.result);
                    }
                    reader.readAsDataURL(file);
                });

                if (html.length > 0) {
                    $("#shopee_product_cover_images_div").html(html);
                }
            });

            $(document).on('click', '.remove_shopee_product_image', function() {
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

                $("#shopee_product_cover_image_"+image_index).closest(".add_preview_image_div").remove();
                
                shopee_product_cover_image_files = shopee_product_cover_image_files.filter(function(file) {
                    return file.name !== image_file_name;
                });
            });

            $(document).on("change", ".add_variation_image_file", function(el) {
                var file = $(this).get(0).files[0];
                var target = $(this).closest(".edit_variation_preview_image_div").find("img");
                if(file){
                    var reader = new FileReader();
                    reader.onload = function() {
                        target.attr("src", reader.result);
                    }
                    reader.readAsDataURL(file);
                }
            });

            const updateShopeeProductImagesDivHtml = (data) => {
                let html = '';
                $.each(data, function (index, image) {
                    html += '<div>';
                    html += '<div class="mb-5 edit_preview_image_div">';
                    html += '<i class="fa fa-trash remove_shopee_product_image"></i>';
                    html += '<img width="100" height="100" src="'+image+'" alt="image">';
                    html += '</div>';
                    html += '</div>';
                });

                if (html.length > 0) {
                    $("#shopee_product_images_div").html(html);
                }
            }


            $(document).on('change', '#shopee_shop', function() {
                var website_id = $(this).val();
                if (typeof(website_id) === "undefined" || website_id === "") {
                    return;
                }

                $("#shopee_category_parent_id").html('<option disabled selected value="0">Select a category</option>');
                $("#shopee_category_parent_id_1").html('<option disabled selected value="0">Select a category</option>');
                $("#shopee_category_id").html('<option disabled selected value="0">Select a category</option>');
                $("#brand").html('<option disabled selected value="0">Select a brand</option>');

                getLogisticsInfoFromShopee();

                var formData = new FormData();
                formData.append('shopee_shop_id', website_id);
                $.ajax({
                    url: '{{ route("shopee.product.get_category") }}',
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
                        $("#shopee_category_parent_id").html(html);
                    }
                });
            });

            $(document).on('change', '#shopee_category_parent_id', function() {
                var category_parent_id = $(this).val();
                if (typeof(category_parent_id) === "undefined" || category_parent_id === "") {
                    return;
                }

                var website_id = $('#shopee_shop option:selected').val();
                if (typeof(website_id) === "undefined" || website_id === "") {
                    return;
                }
                
                var formData = new FormData();
                formData.append('category_parent_id', category_parent_id);
                formData.append('shopee_shop_id', website_id);
                $.ajax({
                    url: '{{ route("shopee.product.get_sub_sub_category") }}',
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
                        $("#shopee_category_parent_id_1").html(html);
                        $("#shopee_category_id").html("");
                    }
                });
            });

            $(document).on('change', '#shopee_category_parent_id_1', function() {
                var category_parent_id = $(this).val();
                if (typeof(category_parent_id) === "undefined" || category_parent_id === "") {
                    return;
                }

                var website_id = $('#shopee_shop option:selected').val();
                if (typeof(website_id) === "undefined" || website_id === "") {
                    return;
                }
                
                var formData = new FormData();
                formData.append('category_parent_id', category_parent_id);
                formData.append('shopee_shop_id', website_id);
                $.ajax({
                    url: '{{ route("shopee.product.get_sub_sub_category") }}',
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
                        $("#shopee_category_id").html(html);
                    }
                });
            });

            $(document).on('change', '#shopee_category_id', function() {
                var category_parent_id = $(this).val();
                if (typeof(category_parent_id) === "undefined" || category_parent_id === "") {
                    return;
                }
                
                getAttributesInfoFromShopee();
            });

            let variation_index = 1;
            $(document).on('change', '#product_type', function() {
                var product_type = $(this).val();
                if (typeof(product_type) === "undefined" || product_type === "") {
                    return;
                }
                variation_index = 1;
                if (product_type === "variable") {
                    $("#price").prop("readonly", true);
                    $("#price").val("");
                    $("#price").addClass("bg-gray-200");
                    $("#quantity").prop("readonly", true);
                    $("#quantity").val("");
                    $("#quantity").addClass("bg-gray-200");
                    addVariationOptionNameInputFieldHtml();
                    addVariationOptionHtml();
                } else {
                    $("#price").prop("readonly", false);
                    $("#price").removeClass("bg-gray-200");
                    $("#quantity").prop("readonly", false);
                    $("#quantity").removeClass("bg-gray-200");
                    $("#variation_options").html("");
                }
            });

            const addVariationOptionHtml = () => {
                let html = `<div class="grid grid-cols-2 gap-4 gap-x-8 p-4 mb-4 rounded-md shopee_product_variation mt-5">
                    <div class="col-span-2">
                        <x-label>
                            <strong class="variation_index">Variation #`+variation_index+`</strong>
                            <hr/>
                        </x-label>
                    </div>
                    <div class="mb-2">
                        <div class="mb-2">
                            <x-label class="col-span-2">
                                Variation Name
                            </x-label>
                            <x-input type="text" name="variation_name[]" value="" required/>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2">
                            <x-label class="col-span-2">
                                Variation SKU
                            </x-label>
                            <x-input type="text" name="variation_sku[]" value="" required />
                        </div>
                    </div>
                    <div>
                        <div class="mb-2">
                            <x-label class="col-span-2">
                                Variation Price"
                            </x-label>
                            <x-input type="number" name="variation_price[]" value="0" required />
                        </div>
                    </div>

                    <div>
                        <div class="mb-2">
                            <x-label class="col-span-2">
                                Stock
                            </x-label>
                            <x-input type="number" name="variation_stock[]" value="0" required />
                        </div>
                    </div>

                    <div class="col-span-2">
                        <x-label>
                            Variation Specific Image
                        </x-label>
                    </div>

                    <div class="mb-5 edit_variation_preview_image_div">
                        <img width="100" height="100" src="{{asset('img/No_Image_Available.jpg')}}" alt="image" class="mb-3" id="shopee_variation_specific_img_`+variation_index+`"/>
                        <x-input type="file" class="add_variation_image_file" id="shopee_variation_specific_img_file_`+variation_index+`" name="variation_image[]"/>
                    </div>
                    <div></div>

                    <div>
                        <button class="add_more_variation_btn btn-action--blue">Add Another Option</button>
                        `+(variation_index !== 1?`<button class="remove_variation_btn btn-action--red">Remove</button>`:``)+`
                    </div>
                </div>
                `;
                $("#variation_options").append(html);
            }

            const addVariationOptionNameInputFieldHtml = () => {
                let html = `
                 <div class="grid grid-cols-2 gap-4 gap-x-8">
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.Option Name')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="text" name="option_name" id="option_name" value="" required/>
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
                $(this).closest(".shopee_product_variation").remove();
                variation_index = variation_index-1;
                let target = $(".shopee_product_variation:last-child").find(".add_more_variation_btn");
                if (typeof(target) !== "undefined" && target.hasClass("hide")) {
                    target.removeClass("hide");
                }
                if (variation_index > 1) {
                    $(".shopee_product_variation").each(function(index, el) {
                        $(this).find(".variation_index").html("Variation #"+(index+1));
                    });
                }
            });
        </script>
    @endpush

</x-app-layout>