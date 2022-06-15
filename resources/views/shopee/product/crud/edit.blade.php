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

                    @if(false)
                    <div>
                        <div class="mb-5">
                            <x-label>
                                {{ __('translation.Image') }} <x-form.required-mark />
                            </x-label>
                            <x-input type="file" onchange="editPreviewFile(this);" name="image" id="edit_image" value="{{ old('image') }}" />
                        </div>
                    </div>

                    <div class="col-span-2">
                        <div class="mb-5"></div>
                        @if (!empty($product->image) && file_exists(public_path($product->image)))
                            <div class="mb-5" id="edit_preview_image_div">
                                <x-label>
                                    {{ __('translation.Preview Image') }}
                                </x-label>
                                <img id="editPreviewImg" width="100" height="100" src="{{asset($product->image)}}" alt="image">
                            </div>
                        @else
                            <div class="mb-5 hide" id="edit_preview_image_div">
                                <x-label>
                                    {{ __('translation.Preview Image') }}
                                </x-label>
                                <img id="editPreviewImg" width="100" height="100" src="{{asset('img/No_Image_Available.jpg')}}" alt="image">
                            </div>
                        @endif
                    </div>
                    @endif
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
                            <x-input type="file" onchange="editPreviewFile1(this);" name="product_images" id="product_images"/>
                        </div>
                    </div>
                </div>

                <div class="col-span-2"><hr/></div>
                <div id="variation_options_extra"></div>

                @if(isset($product->type) and $product->type == "variable")
                @foreach($variations as $index => $variation)
                <div class="grid grid-cols-2 gap-4 gap-x-8 p-4 mb-4 bg-gray-200 bg-opacity-80 shopee_product_variation rounded-md shopee_product_variation_{{$variation->variation_id}}">
                    <div class="col-span-2">
                        <x-label>
                            <strong>{{ __('translation.Variations') }} #{{ $index+1 }}</strong>
                            <button type="button" class="shopee_product_delete_btn btn-action--red mx-4" data-id="{{$variation->variation_id}}">
                                <i class="bi bi-trash"></i>
                            </button>
                            <x-input type="hidden" name="variation_id[]" :value="$variation->variation_id"/>
                            <x-input type="hidden" name="variation_status[]" :value="$variation->status"/>
                            <x-input type="hidden" name="variation_set_content[]" value="none"/>
                            <x-input type="hidden" name="variation_discount_id[]" :value="$variation->discount_id"/>
                            <x-input type="hidden" name="variation_update_time[]" :value="$variation->update_time"/>
                            <x-input type="hidden" name="variation_create_time[]" :value="$variation->create_time"/>
                            <x-input type="hidden" name="variation_is_set_item[]" :value="$variation->is_set_item?'yes':'no'"/>
                            <hr/>
                        </x-label>
                    </div>
                    <div class="mb-2">
                        <div class="mb-2">
                            <x-label class="col-span-2">
                                {{ __('translation.Variation Name') }} <x-form.required-mark /> 
                            </x-label>
                            <x-input type="text" name="variation_name[]" :value="$variation->name" required/>
                            <x-input type="hidden" name="variation_name_old[]" :value="$variation->name" required />
                        </div>
                    </div>

                    <div>
                        <div class="mb-2">
                            <x-label class="col-span-2">
                                {{ __('translation.Variation SKU') }} <x-form.required-mark />
                            </x-label>
                            <x-input type="text" name="variation_sku[]" :value="$variation->variation_sku" required />
                            <x-input type="hidden" name="variation_sku_old[]" :value="$variation->variation_sku" required />
                        </div>
                    </div>
                    <div>
                        <div class="mb-2">
                            <x-label class="col-span-2">
                                {{ __('translation.Variation Price') }} <x-form.required-mark />
                            </x-label>
                            <x-input type="number" name="variation_price[]" :value="$variation->price ?? 0" required />
                            <x-input type="hidden" name="variation_price_old[]" :value="$variation->price ?? 0" required />
                            <x-input type="hidden" name="variation_original_price[]" :value="$variation->original_price ?? 0" required />
                        </div>
                    </div>

                    <div>
                        <div class="mb-2">
                            <x-label class="col-span-2">
                                {{ __('translation.Stock') }} <x-form.required-mark />
                            </x-label>
                            <x-input type="number" name="variation_stock[]" :value="$variation->stock ?? 0" required />
                            <x-input type="hidden" name="variation_stock_old[]" :value="$variation->stock ?? 0" required />
                            <x-input type="hidden" name="variation_reserved_stock[]" :value="$variation->reserved_stock ?? 0" required />
                        </div>
                    </div>

                    <div class="col-span-2">
                        <x-label>
                            {{ __('translation.Variation Specific Image') }}
                        </x-label>
                    </div>
                    <div class="mb-5 edit_variation_preview_image_div">
                    @if (isset($variation_specific_image[$variation->variation_id]))
                        <img width="100" height="100" src="{{$variation_specific_image[$variation->variation_id]}}" alt="image" class="mb-3" id="shopee_variation_specific_img_{{$variation->variation_id}}"/>
                    @else  
                        <img width="100" height="100" src="{{asset('img/No_Image_Available.jpg')}}" alt="image" class="mb-3" id="shopee_variation_specific_img_{{$variation->variation_id}}"/>
                    @endif
                        <x-input type="file" onchange="editPreviewFile2(this);" id="shopee_variation_specific_img_file_{{$variation->variation_id}}" name="image"/>
                    </div>
                </div>
                @endforeach
                @endif

                <div class="col-span-2"><hr/></div>
                <div id="variation_options">
                </div>

                </div>

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
            $(document).ready(function() {
                let product_type = $("#product_type").val();
                if (product_type === "variable") {
                    addVariationOptionNameInputFieldHtml("{{$variation_option_name}}");
                    addInitButtonVariationOptionHtml();
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
                if (product_type === "variable") {
                    /* For old variation */
                    var variation_id = $("input[name='variation_id[]']").map(function(){return $(this).val();}).get();
                    var variation_status = $("input[name='variation_status[]']").map(function(){return $(this).val();}).get();
                    var variation_set_content = $("input[name='variation_set_content[]']").map(function(){return $(this).val();}).get();
                    var variation_discount_id = $("input[name='variation_discount_id[]']").map(function(){return $(this).val();}).get();
                    var variation_update_time = $("input[name='variation_update_time[]']").map(function(){return $(this).val();}).get();
                    var variation_create_time = $("input[name='variation_create_time[]']").map(function(){return $(this).val();}).get();
                    var variation_is_set_item = $("input[name='variation_is_set_item[]']").map(function(){return $(this).val();}).get();
                    var variation_name = $("input[name='variation_name[]']").map(function(){return $(this).val();}).get();
                    var variation_name_old = $("input[name='variation_name_old[]']").map(function(){return $(this).val();}).get();
                    var variation_sku = $("input[name='variation_sku[]']").map(function(){return $(this).val();}).get();
                    var variation_sku_old = $("input[name='variation_sku_old[]']").map(function(){return $(this).val();}).get();
                    var variation_price = $("input[name='variation_price[]']").map(function(){return $(this).val();}).get();
                    var variation_price_old = $("input[name='variation_price_old[]']").map(function(){return $(this).val();}).get();
                    var variation_original_price = $("input[name='variation_original_price[]']").map(function(){return $(this).val();}).get();
                    var variation_stock = $("input[name='variation_stock[]']").map(function(){return $(this).val();}).get();
                    var variation_stock_old = $("input[name='variation_stock_old[]']").map(function(){return $(this).val();}).get();
                    var variation_reserved_stock = $("input[name='variation_reserved_stock[]']").map(function(){return $(this).val();}).get();

                    formData.append('variation_id', JSON.stringify(variation_id));
                    formData.append('variation_status', JSON.stringify(variation_status));
                    formData.append('variation_set_content', JSON.stringify(variation_set_content));
                    formData.append('variation_discount_id', JSON.stringify(variation_discount_id));
                    formData.append('variation_update_time', JSON.stringify(variation_update_time));
                    formData.append('variation_create_time', JSON.stringify(variation_create_time));
                    formData.append('variation_is_set_item', JSON.stringify(variation_is_set_item));
                    formData.append('variation_name', JSON.stringify(variation_name));
                    formData.append('variation_name_old', JSON.stringify(variation_name_old));
                    formData.append('variation_sku', JSON.stringify(variation_sku));
                    formData.append('variation_sku_old', JSON.stringify(variation_sku_old));
                    formData.append('variation_price', JSON.stringify(variation_price));
                    formData.append('variation_price_old', JSON.stringify(variation_price_old));
                    formData.append('variation_original_price', JSON.stringify(variation_original_price));
                    formData.append('variation_stock', JSON.stringify(variation_stock));
                    formData.append('variation_stock_old', JSON.stringify(variation_stock_old));
                    formData.append('variation_reserved_stock', JSON.stringify(variation_reserved_stock));

                    formData.append('total_variations', $(".shopee_product_variation").length);

                    /* For new variation */
                    formData.append('total_variations_new', $(".shopee_product_variation_new").length);

                    var variation_name_new = $("input[name='variation_name_new[]']").map(function(){return $(this).val();}).get();
                    var variation_sku_new = $("input[name='variation_sku_new[]']").map(function(){return $(this).val();}).get();
                    var variation_price_new = $("input[name='variation_price_new[]']").map(function(){return $(this).val();}).get();
                    var variation_stock_new = $("input[name='variation_stock_new[]']").map(function(){return $(this).val();}).get();
                    
                    formData.append('variation_name_new', JSON.stringify(variation_name_new));
                    formData.append('variation_sku_new', JSON.stringify(variation_sku_new));
                    formData.append('variation_price_new', JSON.stringify(variation_price_new));
                    formData.append('variation_stock_new', JSON.stringify(variation_stock_new));

                    // formData.append('variation_image', variation_image);
                    var variation_image_new = $("input[name='variation_image_new[]']").map(function(){return $(this).val();}).get();
                    $.each(variation_image_new, function(index, file) {
                        var variation_image_new = $("input[name='variation_image_new[]']").get(index).files[0];
                        if (typeof(variation_image_new) !== "undefined") {
                            formData.append('variation_image_new_'+index, variation_image_new);
                        } else {
                            formData.append('variation_image_new_'+index, "");
                        }
                    });
                    formData.append('variation_images_count_new', variation_image_new.length);

                    let option_name = $("#option_name").val();
                    if (typeof(option_name) === "undefined" || option_name === "") {
                        // alert("Option name is not valid")
                        // return;
                    }
                    formData.append('variation_option_name', option_name);
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


            const editPreviewFile1 = (input) => {
                var preview_div = $("#edit_preview_image_div1");

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

                var file = $("#product_images").get(0).files[0];
                if(file) {
                    var formData = new FormData();
                    formData.append('id', id);
                    formData.append('image', $("#product_images")[0].files[0]);
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
            }


            const editPreviewFile2 = (el) => {
                var el_id = $(el).attr("id");
                var target = $("#"+el_id).closest(".edit_variation_preview_image_div").find("img");

                var id_info = el_id.split("shopee_variation_specific_img_file_");
                if (typeof(id_info[1]) === "undefined") {
                    alert("Id not valid");
                    return;
                }
                var product_id = id_info[1];

                var website_id = $('#website_id').val();
                if (typeof(website_id) === "undefined" || website_id === "") {
                    return;
                }

                var product_type = $('#product_type').val();
                if (typeof(product_type) === "undefined" || product_type === "") {
                    return;
                }

                var file = $(el).get(0).files[0];
                if(file) {
                    var formData = new FormData();
                    formData.append('variation_product_id', product_id);
                    formData.append('image', $(el).get(0).files[0]);
                    formData.append('website_id', website_id);
                    formData.append('product_type', product_type);
                    formData.append('_token', $('meta[name=csrf-token]').attr('content'));
                    $.ajax({
                        url: '{{ route("shopee.product.update_variation_product_image") }}',
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function() {}
                    }).done(function(response) {
                        $("#"+el_id).val("");
                        if (response.success) {
                            if (typeof(response.data) !== "undefined" && typeof(response.data.image) !== "undefined") {
                                target.attr("src", response.data.image);
                            }
                        } else {
                            if (typeof(response.message) !== "undefined") {
                                alert(response.message);
                            }
                        }
                    });
                }
            }


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


            let variation_index = 0;

            const addInitButtonVariationOptionHtml = () => {
                let html = `<div class="grid grid-cols-2 gap-4 gap-x-8 p-4 mb-4 rounded-md mt-5"  id="init_add_more_variation_div">
                    <div>
                        <button class="add_more_variation_btn btn-action--blue">Add Another Option</button>
                    </div>
                </div>`;
                $("#variation_options").append(html);
            }

            const addVariationOptionHtml = () => {
                let html = `<div class="grid grid-cols-2 gap-4 gap-x-8 p-4 mb-4 rounded-md shopee_product_variation_new mt-5">
                    <div class="col-span-2">
                        <x-label>
                            <strong class="variation_index">New Variation #`+variation_index+`</strong>
                            <hr/>
                        </x-label>
                    </div>
                    <div class="mb-2">
                        <div class="mb-2">
                            <x-label class="col-span-2">
                                Variation Name
                            </x-label>
                            <x-input type="text" name="variation_name_new[]" value="" class="bg-gray-200" required/>
                        </div>
                    </div>

                    <div>
                        <div class="mb-2">
                            <x-label class="col-span-2">
                                Variation SKU
                            </x-label>
                            <x-input type="text" name="variation_sku_new[]" value="" required />
                        </div>
                    </div>
                    <div>
                        <div class="mb-2">
                            <x-label class="col-span-2">
                                Variation Price"
                            </x-label>
                            <x-input type="number" name="variation_price_new[]" value="0" required />
                        </div>
                    </div>

                    <div>
                        <div class="mb-2">
                            <x-label class="col-span-2">
                                Stock
                            </x-label>
                            <x-input type="number" name="variation_stock_new[]" value="0" required />
                        </div>
                    </div>

                    <div class="col-span-2">
                        <x-label>
                            Variation Specific Image
                        </x-label>
                    </div>

                    <div class="mb-5 edit_variation_preview_image_div">
                        <img width="100" height="100" src="{{asset('img/No_Image_Available.jpg')}}" alt="image" class="mb-3" id="shopee_variation_specific_img_`+variation_index+`"/>
                        <x-input type="file" class="add_variation_image_file" id="shopee_variation_specific_img_file_`+variation_index+`" name="variation_image_new[]"/>
                    </div>
                    <div></div>

                    <div>
                        <button class="add_more_variation_btn btn-action--blue">Add Another Option</button>
                        <button class="remove_variation_btn btn-action--red">Remove</button>
                    </div>
                </div>
                `;
                $("#variation_options").append(html);
            }


            const addVariationOptionNameInputFieldHtml = (val) => {
                if (val === "") {
                    return;
                }
                let html = `
                <div class="grid grid-cols-2 gap-4 gap-x-8">
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.Option Name')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="text" name="option_name" id="option_name" value="`+val+`" required/>
                    </div>
                    <div></div>

                    <div class="col-span-2"><hr/></div>
                </div>
                `;
                $("#variation_options_extra").append(html);
            }


            $(document).on("click", ".add_more_variation_btn", function() {
                $("#init_add_more_variation_div").remove();
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
                $(this).closest(".shopee_product_variation_new").remove();
                variation_index = variation_index-1;
                let target = $(".shopee_product_variation_new:last-child").find(".add_more_variation_btn");
                if (typeof(target) !== "undefined" && target.hasClass("hide")) {
                    target.removeClass("hide");
                }
                if (variation_index > 0) {
                    $(".shopee_product_variation_new").each(function(index, el) {
                        $(this).find(".variation_index").html("Variation #"+(index+1));
                    });
                } else {
                    addInitButtonVariationOptionHtml();
                }
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
        </script>
    @endpush

</x-app-layout>