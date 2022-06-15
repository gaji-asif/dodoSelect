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
        </style>
    @endpush

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Lazada - Product'))
    <div class="col-span-12">
        <x-card.card-default>
            <x-card.header>
                <x-card.title>
                    {{ ucwords(__('translation.lazada_product_create')) }} 
                </x-card.title>
            </x-card.header>

            <x-card.body>
                @csrf
                <div class="grid grid-cols-2 gap-4 gap-x-8">
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.product_name')) }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="text" name="name" id="name" :value="old('name') ?? ''" required />
                    </div>
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.product_code')) }}
                        </x-label>
                        <x-input type="text" name="sku" id="sku" :value="old('sku') ?? ''" required />
                    </div>

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

                    <div class="col-span-2">
                        <x-label>
                            {{ ucwords(__('translation.product_short_description')) }}
                        </x-label>
                        <x-textarea name="short_description" id="short_description" rows="5" required>{{ old('short_description') ?? '' }}</x-textarea>
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
                            <option value="{{ $shop->id }}" > {{ $shop->shop_name }} </option>
                            @endforeach
                        </x-select>
                    </div>
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.lazada_parent_category')) }}
                        </x-label>
                        <x-select name="lazada_category_parent_id" id="lazada_category_parent_id">
                            <option disabled selected value="0">Select a category</option>
                        </x-select>
                    </div>
                    <div>
                        <x-label>
                            {{ ucwords(__('translation.lazada_sub_category')) }}
                        </x-label>
                        <x-select name="lazada_category_parent_id_1" id="lazada_category_parent_id_1">
                            <option disabled selected value="0">Select a category</option>
                        </x-select>
                    </div>

                    <div>
                        <x-label>
                            {{ ucwords(__('translation.lazada_sub_sub_category')) }}
                        </x-label>
                        <x-select name="lazada_category_id" id="lazada_category_id">
                            <option disabled selected value="0">Select a category</option>
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

            let no_image_available_img_src = '{{asset("img/No_Image_Available.jpg")}}';

            let is_edit = false;
            let lazada_product_form_url = '{{ route("lazada.product.store_product") }}';
            let lazada_product_variation_image_files = [];

            $(document).ready(function() {
                $("#product_type").select2();
                $("#lazada_shop").select2();
                $("#lazada_category_parent_id").select2();
                $("#lazada_category_parent_id_1").select2();
                $("#lazada_category_id").select2();
            });
    
        </script>

        <script src="{{ asset('pages/seller/lazada/product/index/form.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>