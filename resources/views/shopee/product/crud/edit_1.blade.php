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
            .edit_preview_image_div {
                position: relative;
            } 
            .edit_preview_image_div .fa-trash {
                position: absolute;
                top: 5px;
                margin-left: 5px;
                z-index: 99999;
            }
            .edit_preview_image_div .fa-trash:hover {
                cursor: pointer;
            }
            .variation_option_label {
                font-style: italic;
            }
            .shopee_variation_specific_img:hover {
                cursor: pointer;
            }
            input[name="variation_price[]"], input[name="variation_stock[]"], input[name="variation_sku[]"] {
                max-width: 200px;
            }
            #shopeeProductVariationFormTable thead td:first-child, #shopeeProductVariationFormTable thead td:nth-child(2) {
                min-width: 175px;
            }
        </style>
    @endpush

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Shopee - Order'))
    <div class="col-span-12">
        <x-card.card-default>
            <x-card.header>
                <x-card.title>
                    {{ ucwords(__('translation.shopee_product_edit')) }} 
                </x-card.title>
            </x-card.header>

            <x-card.body>
                @csrf
                <input type="hidden" name="id" value="{{ $id }}">
                <input type="hidden" name="product_id" id="product_id" value="{{$product->product_id}}">
                <input type="hidden" name="website_id" id="website_id" value="{{$product->website_id}}">
                <input type="hidden" name="product_type" id="product_type" value="{{$product->type}}">

                <div class="grid grid-cols-2 gap-4 gap-x-8">
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.product')) . ' ID' }} 
                            <a href="{{ $shopee_product_page_link_live }}" class="mx-3 btn-action--blue" target="_blank">
                                <i class="bi bi-search"></i>
                            </a>
                        </x-label>
                        <x-input type="text" class="bg-gray-200" value="{{ '#' . $product->product_id }}" readonly />
                    </div>
                    <div></div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.shop')) }} 
                        </x-label>
                        <x-input type="text" class="bg-gray-200" value="{{ $shop_name }}" readonly />
                    </div>
                    <div></div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.product_name')) }} <x-form.required-mark/>
                        </x-label>
                        @if ($product->parent_id)
                            <x-input type="text" name="name" id="name" :value="old('name') ?? $product->product_name" class="bg-gray-200" required readonly />
                        @else
                            <x-input type="text" name="name" id="name" :value="old('name') ?? $product->product_name" required />
                        @endif
                    </div>
                    <div >
                        <x-label>
                            {{ ucwords(__('translation.product_code')) }}
                        </x-label>
                        <x-input type="text" name="sku" id="sku" :value="old('sku') ?? $product->product_code" required />
                    </div>

                    @if (in_array($product->type, ['simple','variable']))
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.shopee_parent_category')) }}
                        </x-label>
                        <x-select name="shopee_category_parent_id" id="shopee_category_parent_id">
                            <option disabled selected value="0">Select a parent category</option>
                            @foreach ($shopee_product_parent_categories as $parent_category)
                            <option value="{{ $parent_category->category_id }}" {{ $parent_category->category_id==$shopee_category_parent_id?'selected':''}}> {{ $parent_category->category_name }} </option>
                            @endforeach
                        </x-select>
                    </div>
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.shopee_sub_category')) }}
                        </x-label>
                        <x-select name="shopee_category_parent_id_1" id="shopee_category_parent_id_1">
                            <option disabled selected value="0">Select a parent category</option>
                            @foreach ($shopee_product_parent_categories_1 as $parent_category)
                            <option value="{{ $parent_category->category_id }}" {{ $parent_category->category_id==$shopee_category_parent_id_1?'selected':''}}> {{ $parent_category->category_name }} </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.shopee_sub_sub_category')) }}
                        </x-label>
                        <x-select name="shopee_category_id" id="shopee_category_id">
                            <option disabled selected value="0">Select a category</option>
                            @foreach ($shopee_product_categories as $category)
                            <option value="{{ $category->category_id }}" {{ $category->category_id==$shopee_category_id?'selected':''}}> {{ $category->category_name }} </option>
                            @endforeach
                        </x-select>
                    </div>
                    <div></div>
                    @endif

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.price')) }} <x-form.required-mark/>
                        </x-label>
                        @if ($product->type=='variable')
                        <x-input type="number" name="price" id="price" :value="old('price') ?? $product->price" steps="0.001" required class="bg-gray-200" readonly/>
                        @else
                        <x-input type="number" name="price" id="price" :value="old('price') ?? $product->price" steps="0.001" required/>
                        @endif
                    </div>
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.quantity')) }} <x-form.required-mark/>
                        </x-label>
                        @if ($product->type=='variable')
                        <x-input type="number" name="quantity" id="quantity" :value="old('quantity') ?? $product->quantity" required class="bg-gray-200" readonly/>
                        @else
                        <x-input type="number" name="quantity" id="quantity" :value="old('quantity') ?? $product->quantity" required/>
                        @endif
                    </div>

                    <div class="col-span-2 mb-2">
                        <x-label>
                            {{ ucwords(__('translation.product_description')) }}
                        </x-label>
                        <x-textarea name="description" id="description" rows="15" required>{{ old('specifications') ?? $product->specifications }}</x-textarea>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 gap-x-8 mb-2">
                    <x-label class="col-span-2">
                        {{ __('translation.Product Images') }} <x-form.required-mark />
                    </x-label>
                </div>
                <div class="grid grid-cols-6 gap-4 gap-x-8" id="shopee_product_images_div">
                    @foreach($product_images as $image)
                    <div>
                        @if (!empty($image))
                            <div class="mb-5 edit_preview_image_div">
                                <i class="fa fa-trash remove_shopee_product_image"></i>
                                <img width="100" height="100" src="{{$image}}" alt="image">
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-2 gap-4 gap-x-8">
                    <div>
                        <div class="mb-5">
                            <x-label>
                                {{ __('translation.Image') }}s <x-form.required-mark />
                            </x-label>
                            <x-input type="file" name="new_cover_image" id="new_cover_image"/>
                        </div>
                    </div>
                </div>


                @if(isset($product->type) and $product->type == "variable")
                <div class="col-span-2"><hr/></div>
                <div class="grid grid-cols-2 gap-4 gap-x-8 mb-2">
                    <x-label class="col-span-2">
                        {{ __('translation.option') }} <x-form.required-mark />
                    </x-label>
                    <p style="font-size:12px;font-style:italic;">
                    Your product options should start from A more important option, such as color or model, is the first choice.
                    The second option should be the size or specification
                    </p>
                </div>
                <div id="variation_options_extra"></div>


                <div class="grid grid-cols-2 gap-4 gap-x-8 mb-2">
                    <x-label class="col-span-2">
                        {{ __('translation.variation options') }} <x-form.required-mark />
                    </x-label>
                </div>
                <div id="option_wise_variations_table"></div>


                <div class="col-span-2"><hr/></div>
                <div id="tier_variation_options_extra"></div>

                <div class="grid grid-cols-2 gap-4 gap-x-8 mb-4">
                    <div class="col-span-2">
                        <x-label>
                            {{ __('translation.Variation Specific Image') }}
                        </x-label>
                        <p style="font-size:12px;font-style:italic;">
                        Your have to upload all the images for variation.
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-6 gap-4 gap-x-8 mb-4 shopee_product_variation">
                    @foreach($option_1_options as $index => $option)    
                    <div class="mb-5 edit_variation_preview_image_div" id="edit_variation_preview_image_div_{{ $index }}">
                        <x-label class="mb-2" class="variation_option_label">
                            {{ $option }}
                        </x-label>
                        @if (isset($option_1_images_url[$index]))
                        <img height="100" width="100" class="shopee_variation_specific_img" data-index="{{ $index }}" src="{{$option_1_images_url[$index]}}" alt="{{ $option }}" class="mb-3" id="shopee_variation_specific_img_{{$index}}"/>
                        @else  
                        <img height="100" width="100" class="shopee_variation_specific_img" data-index="{{ $index }}" src="{{asset('img/No_Image_Available.jpg')}}" alt="{{ $option }}" class="mb-3" id="shopee_variation_specific_img_{{$index}}"/>
                        @endif
                        <x-input type="file" class="shopee_variation_specific_img_file hide" data-index="{{ $index }}" id="shopee_variation_specific_img_file_{{$index}}" name="shopee_variation_specific_img_file"/>
                    </div>
                    @endforeach
                </div>

                <div class="col-span-2"><hr/></div>
                <div id="variation_options">
                </div>
                @endif

                <div class="">
                    <p class="" id="form-message"></p>
                </div>

                <div class="mt-5 pb-3">
                    <div class="flex flex-row items-center justify-center gap-2">
                        <x-button type="submit" color="blue" data-website_id="{{$product->website_id}}" data-product_id="{{$product->product_id}}" id="btn_submit_product">
                            {{ __('translation.update_data') }}
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
            let tier_variation_options = {!!$tier_variation_options!!};
            let tier_variation_with_index = {!! $tier_variation_with_index !!};
            let disable_single_image_upload = false;


            $(document).ready(function() {
                let product_type = $("#product_type").val();
                if (product_type === "variable") {
                    addVariationOptionNameInputFieldHtml();
                    updateOptionWiseVariationsTableHtml();
                }
            });


            $(document).on("click", "#btn_submit_product", function(e) {
                e.preventDefault();
                $("#form-message").html("");
                $('#form-message').removeClass("alert alert-danger alert-success alert-danger");

                var id = $(this).data('product_id');
                if (typeof(id) === "undefined" || id === "") {
                    alert("Id not valid");
                    return;
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

                var price = $('#price').val();
                if (typeof(price) === "undefined" || price === "") {
                    alert("Price is not valid");
                    return;
                }

                var description = $('#description').val();
                if (typeof(description) === "undefined") {
                    alert("Description is not valid");
                    return;
                }

                var quantity = $('#quantity').val();
                if (typeof(quantity) === "undefined" || quantity === "") {
                    alert("Quantity is not valid");
                    return;
                }

                var website_id = $(this).data('website_id');
                if (typeof(website_id) === "undefined" || website_id === "") {
                    return;
                }

                var product_type = $('#product_type').val();
                if (typeof(product_type) === "undefined" || product_type === "") {
                    return;
                }

                var shopee_category_id = $("#shopee_category_id option:selected").val();
                if (shopee_category_id === 0 || shopee_category_id === "") {
                    let conf = confirm("No categroy is selected for this product, are you sure?");
                    if (!conf) {
                        return;
                    }
                }
                
                let conf = confirm("Please confirm your want to update this product");
                if (!conf) {
                    return;
                }

                $("#btn_submit_product").prop("disabled", true);

                var formData = new FormData();
                formData.append('id', id);
                formData.append('name', name);
                formData.append('sku', code);
                formData.append('type', product_type);
                formData.append('price', price);
                formData.append('description', description);
                formData.append('quantity', quantity);
                formData.append('shopee_category_id', shopee_category_id);
                formData.append('website_id', website_id);
                formData.append('_token', $('meta[name=csrf-token]').attr('content'));

                if (product_type == "variable") {
                    formData.append('option_name_1', $("#option_name_1").val());
                    formData.append('option_name_2', $("#option_name_2").val());

                    var variation_name = $("input[name='variation_name[]']").map(function(){return $(this).val();}).get();
                    var variation_id = $("input[name='variation_id[]']").map(function(){return $(this).val();}).get();
                    var variation_sku = $("input[name='variation_sku[]']").map(function(){return $(this).val();}).get();
                    var variation_price = $("input[name='variation_price[]']").map(function(){return $(this).val();}).get();
                    var variation_stock = $("input[name='variation_stock[]']").map(function(){return $(this).val();}).get();
                    var variation_sku_old = $("input[name='variation_sku_old[]']").map(function(){return $(this).val();}).get();
                    var variation_price_old = $("input[name='variation_price_old[]']").map(function(){return $(this).val();}).get();
                    var variation_stock_old = $("input[name='variation_stock_old[]']").map(function(){return $(this).val();}).get();

                    var tier_variation_choices_1__option_val = $("input[name='tier_variation_choices_1__option_val[]']").map(function(){return $(this).val();}).get();
                    var tier_variation_choices_2__option_val = $("input[name='tier_variation_choices_2__option_val[]']").map(function(){return $(this).val();}).get();
                    
                    formData.append('variation_id', JSON.stringify(variation_id));
                    formData.append('variation_sku', JSON.stringify(variation_sku));
                    formData.append('variation_price', JSON.stringify(variation_price));
                    formData.append('variation_stock', JSON.stringify(variation_stock));
                    formData.append('variation_sku_old', JSON.stringify(variation_sku_old));
                    formData.append('variation_price_old', JSON.stringify(variation_price_old));
                    formData.append('variation_stock_old', JSON.stringify(variation_stock_old));

                    formData.append('tier_variation_choices_1__option_val', JSON.stringify(tier_variation_choices_1__option_val));
                    formData.append('tier_variation_choices_2__option_val', JSON.stringify(tier_variation_choices_2__option_val));

                    for (i=0; i<$(".tier_variation_choices_1__option_val").length; i++) {
                        let file = $('#shopee_variation_specific_img_file_'+i).get(0).files[0];
                        if (typeof(file) !== "undefined") {
                            formData.append('new_variation_option_image_'+i, $('#shopee_variation_specific_img_file_'+i).get(0).files[0]);
                        }
                    }
                }

                $.ajax({
                    url: '{{ route("shopee.product.update_product") }}',
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


            const editPreviewFile = (input) => {
                var preview_div = $("#edit_preview_image_div");
                if($(preview_div).hasClass('hide'))
                {
                    $(preview_div).removeClass('hide');
                    $(preview_div).addClass('show');
                }

                var file = $("#edit_image").get(0).files[0];

                if(file){
                    var reader = new FileReader();
                    reader.onload = function(){
                        $("#editPreviewImg").attr("src", reader.result);
                    }
                    reader.readAsDataURL(file);
                }
            }


            $(document).on('change', '#new_cover_image', function () {
                var preview_div = $("#edit_preview_image_div");

                var id = $('#product_id').val();
                if (typeof(id) === "undefined" || id === "") {
                    alert("Id not valid");
                    return;
                }

                var website_id = $('#website_id').val();
                if (typeof(website_id) === "undefined" || website_id === "") {
                    return;
                }

                var product_type = $('#product_type').val();
                if (typeof(product_type) === "undefined" || product_type === "") {
                    return;
                }

                var file = $("#new_cover_image").get(0).files[0];
                if(file) {
                    var formData = new FormData();
                    formData.append('id', id);
                    formData.append('image', $("#new_cover_image")[0].files[0]);
                    formData.append('website_id', website_id);
                    formData.append('product_type', product_type);
                    formData.append('_token', $('meta[name=csrf-token]').attr('content'));
                    $.ajax({
                        url: '{{ route("shopee.product.update_product_images") }}',
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function() {}
                    }).done(function(response) {
                        if (response.success) {
                            if (typeof(response.data) !== "undefined") {
                                updateShopeeProductImagesDivHtml(response.data);
                            }
                        } else {
                            if (typeof(response.message) !== "undefined") {
                                alert(response.message);
                            }
                        }
                    });
                } 
            });


            $(document).on('click', '.shopee_variation_specific_img', function () {
                let index = $(this).data("index");
                $("#shopee_variation_specific_img_file_"+index).val("");
                $("#shopee_variation_specific_img_file_"+index).trigger("click");
            });


            $(document).on('change', '.shopee_variation_specific_img_file', function () {
                let index = $(this).data("index");
                let target = $("#shopee_variation_specific_img_"+index);

                let product_id = $("#product_id").val();
                if (typeof(product_id) === "undefined" || product_id === "") {
                    return;
                }

                var website_id = $('#website_id').val();
                if (typeof(website_id) === "undefined" || website_id === "") {
                    return;
                }

                var product_type = $('#product_type').val();
                if (typeof(product_type) === "undefined" || product_type === "") {
                    return;
                }

                var file = $(this).get(0).files[0];

                if(file) {
                    var reader = new FileReader();
                    reader.onload = function(){
                        target.attr("src", reader.result);
                    }
                    reader.readAsDataURL(file);

                    if (disable_single_image_upload) {
                        return;
                    }

                    var formData = new FormData();
                    formData.append('product_id', product_id);
                    formData.append('website_id', website_id);
                    formData.append('product_type', product_type);
                    formData.append('_token', $('meta[name=csrf-token]').attr('content'));

                    for (i=0; i<$(".tier_variation_choices_1__option_val").length; i++) {
                        formData.append('new_variation_option_image_'+i, $('#shopee_variation_specific_img_file_'+i).get(0).files[0]);
                    }

                    $.ajax({
                        url: '{{ route("shopee.product.update_variation_product_image") }}',
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function() {}
                    }).done(function(response) {
                        if (response.success) {
                            if (typeof(response.data) !== "undefined" && typeof(response.data.image) !== "undefined") {
                                target.attr("src", response.data.image);
                            }
                        } else {
                            if (typeof(response.message) !== "undefined") {
                                if (typeof(response.data.show) !== "undefined" && !response.data.show) {
                                    return;
                                }
                                alert(response.message);
                            }
                        }
                    });
                }
            });


            $(document).on('click', '.remove_shopee_product_image', function() {
                let conf = confirm("Are you sure you want to remove this image?");
                if (!conf) {
                    return;
                }

                var id = $('#product_id').val();
                if (typeof(id) === "undefined" || id === "") {
                    alert("Id not valid");
                    return;
                }

                var image = $(this).parent('.edit_preview_image_div').children('img').attr('src');
                if (typeof(image) == 'undefined' || image === '') {
                    return;
                }

                var website_id = $('#website_id').val();
                if (typeof(website_id) === "undefined" || website_id === "") {
                    return;
                }

                var formData = new FormData();
                formData.append('id', id);
                formData.append('website_id', website_id);
                formData.append('image', image);
                $.ajax({
                    url: '{{ route("shopee.product.delete_product_images") }}',
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {}
                }).done(function(response) {
                    if (response.success) {
                        if (typeof(response.data) !== "undefined") {
                            updateShopeeProductImagesDivHtml(response.data);
                        }
                    } else {
                        if (typeof(response.message) !== "undefined") {
                            alert(response.message);
                        }
                    }
                });
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


            $(document).on('change', '#shopee_category_parent_id', function() {
                var category_parent_id = $(this).val();
                if (typeof(category_parent_id) === "undefined" || category_parent_id === "") {
                    return;
                }

                var website_id = $('#website_id').val();
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
                        let html = '<option disabled selected value="0">Select a sub category</option>';
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

                var website_id = $('#website_id').val();
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


            $('body').on('click', '.shopee_product_delete_btn', function() {
                var id = $(this).data('id');
                if (typeof(id) === "undefined") {
                    return;
                }
                let drop = confirm('Are you sure you want to delete this variation?');
                if (drop) {
                    $(".shopee_product_delete_btn").prop('disabled', true);
                    $.ajax({
                        url: '{{ route("shopee.product.delete_product") }}',
                        type: 'POST',
                        data: {
                            'id': id,
                            '_token': $('meta[name=csrf-token]').attr('content')
                        },
                        beforeSend: function() {
                        }
                    }).done(function(response) {
                        if (response.success) {
                            $(".shopee_product_variation_new_"+id).remove();
                            if (typeof(response.message) !== "undefined") {
                                Swal.fire(
                                    'Success!',
                                    response.message,
                                    'success'
                                );
                            }
                            setTimeout(function() {
                                window.location.href = '{{ route("shopee.product.index") }}';
                            }, 3500);
                        } else {
                            if (typeof(response.message) !== "undefined") {
                                alert(response.message);
                            }
                            $(".shopee_product_delete_btn").prop('disabled', false);
                        }
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        let message = "";
                        $.each(jqXHR.responseJSON.errors, function(index, error) {
                            message += error[0];
                        });
                        alert(message);
                        $(".shopee_product_delete_btn").prop('disabled', false);
                    });
                }
            });


            let variation_option_choices_1 = [];
            let variation_option_choices_2 = [];
            const addVariationOptionNameInputFieldHtml = () => {
                let val_1 = "";
                let val_2 = "";
                let html_tier_variation_choices_1 = ``;
                let html_tier_variation_choices_2 = ``;
                if (typeof(tier_variation_options[0]) !== "undefined") {
                    val_1 = (typeof(tier_variation_options[0].name) !== "undefined")?tier_variation_options[0].name:"";
                    if (typeof(tier_variation_options[0].options) !== "undefined") {
                        $.each(tier_variation_options[0].options, function (index, option) {
                            variation_option_choices_1.push(option);
                            html_tier_variation_choices_1 += getTierVariationOptionInputHtml(1, option, index);
                        });
                    }
                }
                html_tier_variation_choices_1 += `<button class="add_new_tier_variation_choice_option btn-action--blue mt-2" data-option_no="1">Add Option</button>`;

                if (typeof(tier_variation_options[1]) !== "undefined") {
                    val_2 = (typeof(tier_variation_options[1].name) !== "undefined")?tier_variation_options[1].name:"";
                    if (typeof(tier_variation_options[1].options) !== "undefined") {
                        $.each(tier_variation_options[1].options, function (index, option) {
                            variation_option_choices_2.push(option);
                            html_tier_variation_choices_2 += getTierVariationOptionInputHtml(2, option);
                        });
                    }
                }
                html_tier_variation_choices_2 += `<button class="add_new_tier_variation_choice_option btn-action--blue mt-2" data-option_no="2">Add Option</button>`;                    

                let html = `
                <div class="grid grid-cols-2 gap-4 gap-x-8">
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.Option Name')) }} 1<x-form.required-mark/>
                        </x-label>
                        <x-input type="text" name="option_name_1" id="option_name_1" value="`+val_1+`" style="max-width:250px;" required/>
                        <span class="tvco_val_length">`+val_1.length+`/20</span>
                        <div id="tier_variation_choices">`+html_tier_variation_choices_1+`
                        </div>
                    </div>
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.Option Name')) }} 2
                        </x-label>
                        <x-input type="text" name="option_name_2" id="option_name_2" value="`+val_2+`" style="max-width:250px;" required/>
                        <span class="tvco_val_length">`+val_2.length+`/20</span>
                        <div id="tier_variation_choices_2">`+html_tier_variation_choices_2+`
                        </div>
                    </div>
                    <div class="col-span-2"><hr/></div>
                </div>
                `;
                $("#variation_options_extra").append(html);
            }


            $(document).on('keyup', '#option_name_1, #option_name_2', function() {
                checkTierVariationOptionLength(this);
            });


            $(document).on('click', '.add_new_tier_variation_choice_option', function () {
                let option_no = $(this).data("option_no");
                let can_add = true;
                let targets = $(".tier_variation_choices_"+option_no+"__option_val");
                $.each(targets, function(i, el) {
                    if($(el).val() === "") {
                        can_add = false;
                    }
                });
                if (!can_add) {
                    alert('No option can be empty');
                    return;
                }
                if (option_no === 1) {
                    disable_single_image_upload = true;

                    let index = $(".shopee_product_variation").find(".edit_variation_preview_image_div").length;
                    $(this).before(getTierVariationOptionInputHtml(option_no, "", index));

                    let new_el = getNewVariationOptionImageDivHtml();
                    $(".shopee_product_variation").append(new_el);
                } else {
                    $(this).before(getTierVariationOptionInputHtml(option_no, ""));
                }
            });


            const getTierVariationOptionInputHtml = (option_no, option, index=-1) => {
                return `<div id="tier_variation_choices_`+option_no+`__option" class="mt-1">
                    <input type="text" value="`+option+`" data-index="`+index+`"
                    name="tier_variation_choices_`+option_no+`__option_val[]"
                    class="w-full h-10 px-3 py-2 rounded-md shadow-sm outline-none focus:outline-none border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 tier_variation_choices_`+option_no+`__option_val" 
                    style="max-width:250px;">
                    <span class="tvco_val_length">`+option.length+`/20</span>
                </div>`;
            }


            const getNewVariationOptionImageDivHtml = () => {
                let index = $(".shopee_product_variation").find(".edit_variation_preview_image_div").length;
                let no_image_found_img_url = "{!! asset('img/No_Image_Available.jpg') !!}";
                return `
                <div class="mb-5 edit_variation_preview_image_div" id="edit_variation_preview_image_div_`+index+`">
                    <label class="block font-medium text-sm text-gray-700 mb-1 mb-2 variation_option_label"> -- </label>
                    <img height="100" width="100" class="shopee_variation_specific_img" data-index="`+index+`" src="`+no_image_found_img_url+`" alt="Blue" id="shopee_variation_specific_img_`+index+`">
                    <input type="file" class="w-full h-10 px-3 py-2 rounded-md shadow-sm outline-none focus:outline-none border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1 shopee_variation_specific_img_file hide" data-index="`+index+`" id="shopee_variation_specific_img_file_`+index+`" name="shopee_variation_specific_img_file">                    
                </div>
                `;
            } 


            $(document).on('keyup', '.tier_variation_choices_1__option_val', function() {
                checkTierVariationOptionLength(this);
                let index = $(this).data('index');
                let val = $(this).val();
                if (val.length === 0) {
                    val = "--";
                }
                $("#edit_variation_preview_image_div_"+index).find(".variation_option_label").html(val);
            });


            $(document).on('keyup', '.tier_variation_choices_2__option_val', function() {
                checkTierVariationOptionLength(this);
            });


            const checkTierVariationOptionLength = (el, limit=20) => {
                let val = $(el).val();
                if (val.length > limit) {
                    val = val.substring(0, 20);
                    $(el).val(val);
                } else {
                    let target = $(el).parent().children(".tvco_val_length");
                    target.html(val.length+"/20");
                    updateOptionWiseVariationsTableHtml();
                }
            }


            const updateOptionWiseVariationsTableHtml = () => {
                let option_1 = "";
                if (typeof($("#option_name_1").val()) !== "undefined") {
                    option_1 = $("#option_name_1").val();
                }
                let has_option_2 = false;
                let option_2 = "";
                if (typeof($("#option_name_2").val()) !== "undefined" && $("#option_name_2").val() !== "") {
                    option_2 = $("#option_name_2").val();
                    has_option_2 = true;
                }

                let html_thead = `<thead class="bg-gray"><tr>`;
                html_thead += `<td>`+option_1+`</td>`;
                if (has_option_2) {
                    html_thead += `<td>`+option_2+`</td>`;
                }
                html_thead += `<td>Price</td><td>Stock</td><td>SKU</td>`;
                html_thead += `</tr></thead>`;
                
                /* Option 1 */
                let option_1_input_targets = $(".tier_variation_choices_1__option_val");
                let option_1_total_count = option_1_input_targets.length;
                variation_option_choices_1 = [];
                $.each(option_1_input_targets, function(i, el) {
                    variation_option_choices_1.push($(el).val());
                });

                /* Option 2 */
                let option_2_input_targets = $(".tier_variation_choices_2__option_val");
                let option_2_total_count = option_2_input_targets.length;
                variation_option_choices_2 = [];
                let v = [];
                $.each(option_2_input_targets, function(i, el) {
                    variation_option_choices_2.push($(el).val());
                });

                let html_tbody = `<tbody>`;
                for (i=0; i<option_1_total_count; i++) {
                    html_tbody += `<tr class="`+((i%2!=0)?"bg-gray":"")+`">`;
                    html_tbody += `<td>`+variation_option_choices_1[i]+`</td>`;
                    if (option_2_total_count > 0) {
                        html_tbody += `<td>`;
                        for (m=0; m<option_2_total_count; m++) {
                            html_tbody += `<span class="block font-medium text-sm text-gray-700 h-10 py-2">`+variation_option_choices_2[m]+`</span>`;
                        }
                        html_tbody += `</td>`;
                        for (n=0; n<3; n++) {
                            html_tbody += `<td>`;
                            for (j=0; j<option_2_total_count; j++) {
                                let data = tier_variation_with_index[i+"_"+j];
                                if (n==0) {
                                    let variation_id = "0";
                                    let variation_price = 0;
                                    if (typeof(data) !== "undefined") {
                                        if (typeof(data.variation_id) !== "undefined") {
                                            variation_id = data.variation_id;
                                        }
                                        if (typeof(data.variation_price) !== "undefined") {
                                            variation_price = data.variation_price;
                                        }
                                    }
                                    html_tbody += `<div>
                                        <input type="hidden" name="variation_id[]" value="`+variation_id+`"/>
                                        <input type="number" name="variation_price[]" value="`+variation_price+`"/>
                                        <input type="hidden" name="variation_price_old[]" value="`+variation_price+`"/>
                                    </div>`;
                                } else if (n==1) {
                                    let variation_stock = 0;
                                    if (typeof(data) !== "undefined" && typeof(data.variation_stock) !== "undefined") {
                                        variation_stock = data.variation_stock;
                                    }
                                    html_tbody += `<div><input type="number" name="variation_stock[]" value="`+variation_stock+`"/>
                                    <input type="hidden" name="variation_stock_old[]" value="`+variation_stock+`"/></div>`;
                                } else { 
                                    let variation_sku = "";
                                    if (typeof(data) !== "undefined" && typeof(data.variation_sku) !== "undefined") {
                                        variation_sku = data.variation_sku;
                                    }
                                    html_tbody += `<div><input type="text" name="variation_sku[]" value="`+variation_sku+`"/>
                                    <input type="hidden" name="variation_sku_old[]" value="`+variation_sku+`"/></div>`;
                                }
                            }
                            html_tbody += `</td>`;
                        }
                    } else {
                        let data = tier_variation_with_index[i];
                        let variation_id = "0";
                        let variation_price = 0;
                        if (typeof(data) !== "undefined") {
                            if (typeof(data.variation_id) !== "undefined") {
                                variation_id = data.variation_id;
                            }
                            if (typeof(data.variation_price) !== "undefined") {
                                variation_price = data.variation_price;
                            }
                        }
                        html_tbody += `<td>
                            <input type="hidden" name="variation_id[]" value="`+variation_id+`"/>
                            <input type="number" name="variation_price[]" value="`+variation_price+`"/>
                            <input type="hidden" name="variation_price_old[]" value="`+variation_price+`"/>
                        </td>`;
                        let variation_stock = 0;
                        if (typeof(data) !== "undefined" && typeof(data.variation_stock) !== "undefined") {
                            variation_stock = data.variation_stock;
                        }
                        html_tbody += `<td><input type="number" name="variation_stock[]" value="`+variation_stock+`"/>
                        <input type="hidden" name="variation_stock_old[]" value="`+variation_stock+`"/></td>`;
                        let variation_sku = "";
                        if (typeof(data) !== "undefined" && typeof(data.variation_sku) !== "undefined") {
                            variation_sku = data.variation_sku;
                        }
                        html_tbody += `<td><input type="text" name="variation_sku[]" value="`+variation_sku+`"/>
                        <input type="hidden" name="variation_sku_old[]" value="`+variation_sku+`"/></td>`;
                    }
                    html_tbody += `</tr>`;
                }
                html_tbody += `</tbody>`;
                
                let html = `<table id="shopeeProductVariationFormTable" class="table-auto border-collapse w-full border mt-4">`+html_thead+``+html_tbody+`</table>`;
                
                $("#option_wise_variations_table").html(html);
                $("#shopeeProductVariationFormTable").find("td").addClass("p-2");
                $("#shopeeProductVariationFormTable").find("input").addClass("w-full h-10 px-3 py-2 rounded-md shadow-sm outline-none focus:outline-none border border-solid border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50");
            }


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
        </script>
    @endpush

</x-app-layout>