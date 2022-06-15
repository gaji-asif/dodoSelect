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
            .lazada_shop_for_order_sync_form .select2-selection--multiple {
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
            .lazada_shop_for_order_sync_form .select2-selection--multiple {
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

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Lazada - Product'))
    <div class="col-span-12">
        <x-card.card-default>
            <x-card.header>
                <x-card.title>
                    {{ ucwords(__('translation.lazada_product_edit')) }} 
                </x-card.title>
            </x-card.header>

            <x-card.body>
                @csrf
                <div class="grid grid-cols-2 gap-4 gap-x-8">
                    <div>
                        <x-input type="hidden" name="id" id="id" value="{{ $id }}" required />
                        <x-label>
                            {{ ucwords(__('translation.product_name')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="text" name="name" id="name" value="{{ isset($attributes, $attributes->name)?$attributes->name:'' }}" required />
                    </div>
                    <div >
                        <x-label>
                            {{ ucwords(__('translation.product_code')) }}
                        </x-label>
                        <x-input type="text" name="sku" id="sku" value="{{ $product->product_code }}" required />
                    </div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.price')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="number" name="price" id="price" value="{{ $product->price }}" steps="0.001" required/>
                    </div>
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.quantity')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="number" name="quantity" id="quantity" value="{{ $product->quantity }}" required/>
                    </div>

                    <div class="col-span-2">
                        <x-label>
                            {{ ucwords(__('translation.product_description')) }}
                        </x-label>
                        <x-textarea name="description" id="description" rows="15" required>{{ isset($attributes, $attributes->description)?$attributes->description:"" }}</x-textarea>
                    </div>

                    <div class="col-span-2">
                        <x-label>
                            {{ ucwords(__('translation.product_short_description')) }}
                        </x-label>
                        <x-textarea name="short_description" id="short_description" rows="5" required>{{ isset($attributes, $attributes->short_description)?$attributes->short_description:"" }}</x-textarea>
                    </div>

                    <div class="col-span-2">
                        <x-label>
                            {{ ucwords(__('translation.product_description_en')) }}
                        </x-label>
                        <x-textarea name="description_en" id="description_en" rows="15" required>{{ isset($attributes, $attributes->description_en)?$attributes->description_en:"" }}</x-textarea>
                    </div>

                    <div class="col-span-2">
                        <x-label>
                            {{ ucwords(__('translation.product_short_description_en')) }}
                        </x-label>
                        <x-textarea name="short_description_en" id="short_description_en" rows="5" required>{{ isset($attributes, $attributes->short_description_en)?$attributes->short_description_en:"" }}</x-textarea>
                    </div>

                    <div class="col-span-2"><hr/></div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.lazada_select_shop')) }}
                        </x-label>
                        <x-select name="lazada_shop" id="lazada_shop">
                            <option disabled selected value="0">Select a shop</option>
                            @foreach ($lazada_shops as $shop)
                            <option value="{{ $shop->id }}" {{$shop->id==$product->website_id?"selected":""}}> {{ $shop->shop_name }} </option>
                            @endforeach
                        </x-select>
                    </div>
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.lazada_parent_category')) }}
                        </x-label>
                        <x-select name="lazada_category_parent_id" id="lazada_category_parent_id">
                            @foreach ($lazada_product_parent_categories as $parent_category)
                            <option value="{{ $parent_category->category_id }}" {{ $parent_category->category_id==$lazada_category_parent_id?'selected':''}}> {{ $parent_category->category_name }} </option>
                            @endforeach
                        </x-select>
                    </div>
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.lazada_sub_category')) }}
                        </x-label>
                        <x-select name="lazada_category_parent_id_1" id="lazada_category_parent_id_1">
                            @foreach ($lazada_product_parent_categories_1 as $parent_category)
                            <option value="{{ $parent_category->category_id }}" {{ $parent_category->category_id==$lazada_category_parent_id_1?'selected':''}}> {{ $parent_category->category_name }} </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.lazada_sub_sub_category')) }}
                        </x-label>
                        <x-select name="lazada_category_id" id="lazada_category_id">
                            @foreach ($lazada_product_categories as $category)
                            <option value="{{ $category->category_id }}" {{ $category->category_id==$lazada_category_id?'selected':''}}> {{ $category->category_name }} </option>
                            @endforeach
                        </x-select>
                    </div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.product_type')) }} <x-form.required-mark/>
                        </x-label>
                        
                        <x-select name="product_type" id="product_type">
                            <option value="variable" disabled selected >Variable</option>
                        </x-select>
                    </div>

                    <div class="col-span-2"><hr/></div>
                </div>

                <div class="grid grid-cols-2 gap-4 gap-x-8 mb-2">
                    <x-label class="col-span-2">
                        {{ __('translation.Product Images') }} <x-form.required-mark />
                    </x-label>
                </div>
                <div class="grid grid-cols-6 gap-4 gap-x-8" id="lazada_product_images_div">
                    @foreach($product_images as $image)
                    <div>
                        @if (!empty($image))
                            <div class="mb-5 edit_preview_image_div">
                                <i class="fa fa-trash remove_lazada_product_image"></i>
                                <img width="100" height="100" src="{{$image}}" alt="image">
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                <div class="grid grid-cols-6 gap-4 gap-x-8" id="lazada_product_cover_images_div">
                </div>

                <div class="grid grid-cols-2 gap-4 gap-x-8">
                    <div>
                        <div class="mb-5">
                            <x-input type="file" class="add_lazada_cover_image_files" name="product_images" id="product_images" multiple/>
                        </div>
                    </div>
                </div>

                <div class="col-span-2"><hr/></div>
                <div id="lazada_product_attributes"></div>

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
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            let route__lazada_product_get_category_wise_attributes = '{{ route("lazada.product.get_category_wise_attributes") }}';
            let route__lazada_product_get_brands = '{{ route("lazada.product.get_brands") }}';
            let route__lazada_product_get_categories = '{{ route("lazada.product.get_category") }}';
            let route__lazada_product_get_sub_categories = '{{ route("lazada.product.get_sub_category") }}';
            let route__lazada_product_get_sub_sub_categories = '{{ route("lazada.product.get_sub_sub_category") }}';
            let route__lazada_product_delete_product_image = '{{ route("lazada.product.delete_product_variation_image") }}';
            
            let no_image_available_img_src = '{{asset("img/No_Image_Available.jpg")}}';

            let is_edit = true;
            let lazada_product_form_url = '{{ route("lazada.product.update_product") }}';
            let total_variations_count = '{{ $total_variations_count }}';
            let variation_products_normal_attr_data = {!! json_encode($attributes) !!};
            let variation_products_sku_attr_data = {!! json_encode($variations) !!};
            let lazada_product_variation_image_files = [];

            $(document).ready(function() {
                $("#product_type").select2();
                $("#lazada_shop").select2();
                $("#lazada_category_parent_id").select2();
                $("#lazada_category_parent_id_1").select2();
                $("#lazada_category_id").select2();

                var category_parent_id = $("#lazada_category_id").find("option:selected").val();
                if (typeof(category_parent_id) === "undefined" || category_parent_id === "") {
                    return;
                }
                getProductAttributesInfoFromLazada();
            });


            const updateVariationSkuAttributeValuesInEditForm = () => {
                for (i=1;i<=total_variations_count;i++) {
                    variation_index = i;
                    addVariationOptionHtml();
                }
                $.each(variation_products_sku_attr_data, function(i,v) {
                    /* Assign attribute values. */
                    $("#lazada_product_variation_"+(i+1)).find('input[name="variation_name[]"]').val(v.name);
                    $("#lazada_product_variation_"+(i+1)).find('input[name="variation_sku[]"]').val(v.sku);
                    $("#lazada_product_variation_"+(i+1)).find('input[name="variation_price[]"]').val(v.price);
                    $("#lazada_product_variation_"+(i+1)).find('input[name="variation_stock[]"]').val(v.stock);

                    

                    /* Update variation product images. */
                    let variation_images_html = `<div class="grid grid-cols-6 gap-4 gap-x-8 rounded-md mt-5">`;
                    $.each(v.images, function(j, image_url) {
                        if (image_url.length > 0) {
                            variation_images_html += `<div class="mb-5 edit_preview_image_div">
                                <i class="fa fa-trash remove_lazada_product_exiting_variation_image"></i>
                                <img width="100" height="100" src="`+image_url+`" alt="image">
                            </div>`;
                        }
                    });
                    variation_images_html += `</div>`;
                    $("#lazada_product_variation_existing_images_div_"+(i+1)).html(variation_images_html);
                    
                    /* Update sku product attribtues. */
                    $.each(lazada_product_sku_attributes, function (index, value) {
                        let target = $("#lazada_product_variation_"+(i+1)).find('input[name="variation_'+value.name+'[]"]');
                        if (typeof(target !== "undefined" || target !== null)) {
                            let tag = target.get(0).tagName;
                            if (tag === "INPUT") {
                                target.val(v[value.name]);
                            } else if (tag === "SELECT") {
                                $(target.find("option")).each(function () {
                                    if ($(this).val() === v[value.name]) {
                                        $(this).attr('selected', 'selected');
                                        target.select2();
                                    }
                                });
                            }
                        }
                    });
                });
            }
        </script>

        <script src="{{ asset('pages/seller/lazada/product/index/form.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>