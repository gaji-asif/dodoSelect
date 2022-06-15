@csrf
<input type="hidden" name="id" id="product_id" value="{{ $product->id }}">

<div class="grid md:grid-cols-2 md:gap-x-5">
    <div class="mb-5">
        <x-label>
            {{ __('translation.Select Category') }}
        </x-label>
        <x-select name="edit_parent_category_id" id="edit_parent_category_id">
            <option value="0">
                {{ __('translation.Select Category') }}
            </option>
            @foreach ($categories as $category)
                @if ($product->category->parent)
                    <option value="{{ $category->id }}" @if ($category->id === $product->category->parent->id) selected @endif>
                        {{ $category->cat_name }}
                    </option>
                @else
                    <option value="{{ $category->id }}">
                        {{ $category->cat_name }}
                    </option>
                @endif
            @endforeach
        </x-select>
    </div>

    <div class="mb-5">
        <x-label>
            {{ __('translation.Select Sub Category') }}
        </x-label>
        <x-select name="category_id" id="edit_category_id">
            <option value="{{ $product->category_id }}">
                {{ $product->category->cat_name }}
            </option>
        </x-select>
    </div>

    <div class="mb-5">
        <x-label>
            {{ __('translation.Product Name') }} <x-form.required-mark />
        </x-label>
        <x-input type="text" name="product_name" id="product_name" value="{{ old('product_name') ?? $product->product_name }}" required />
    </div>

    <div class="mb-5">
        <x-label>
            {{ __('translation.Product Code') }} <x-form.required-mark />
        </x-label>
        <x-input type="text" name="product_code" id="product_code" value="{{ old('product_code') ?? $product->product_code }}" required />
    </div>
</div>

<div>
    <div class="mb-5">
        <x-label>
            {{ __('translation.Specifications') }}
        </x-label>
        <x-form.textarea name="specifications" id="specifications" rows="4">{{ old('specifications') ?? $product->specifications }}</x-form.textarea>
    </div>
</div>

<div class="grid md:grid-cols-2 md:gap-x-5">
    <div class="mb-5">
        <x-label>
            {{ __('translation.Price') }} <x-form.required-mark />
        </x-label>
        <x-input type="number" name="price" id="price" value="{{ old('price') ?? $product->price }}" required />
    </div>

    <div class="mb-5">
        <x-label>
            {{ __('translation.Dropship Price') }}
        </x-label>
        <x-input type="number" name="dropship_price" id="dropship_price" value="{{ old('dropship_price') ?? $product->dropship_price }}" />
    </div>

    <div class="mb-5">
        <x-label>
            {{ __('translation.Weight') }} <x-form.required-mark />
        </x-label>
        <x-input type="text" name="weight" id="weight" value="{{ old('weight') ?? $product->weight }}" required />
    </div>

    <div class="mb-5">
        <x-label>
            {{ __('translation.Pieces / Pack') }} <x-form.required-mark />
        </x-label>
        <x-input type="number" name="pack" id="pack" value="{{ old('pack') ?? $product->pack }}" required />
    </div>

    <div class="mb-5">
        <x-label>
            {{ __('translation.Alert Stock') }} <x-form.required-mark />
        </x-label>
        <x-input type="number" name="alert_stock" id="alert_stock" value="{{ old('alert_stock') ?? $product->alert_stock }}" required />
    </div>

    <div class="mb-5">
        <x-label>
            {{ __('translation.Select Product Tag') }}
        </x-label>
        <select name="product_tag[]" id="form_edit_product_tag" data-placeholder="Select Product Tag" multiple>
            @if (isset($product_tags))
                @foreach ($product_tags as $product_tag)
                    <option value="{{$product_tag->id}}" @foreach($product->productTags as $tag){{$tag->id == $product_tag->id  ? 'selected': ''}} @endforeach>{{$product_tag->name}}</option>
                @endforeach
            @endif
        </select>
    </div>

    <div class="mb-5">
        <x-label>
           Product Status
        </x-label>
        <x-select name="product_status" id="product_status">

            <option value="1" @if($product->product_status == 1) selected @endif>Active</option>
            <option value="0" @if($product->product_status == 0) selected @endif>Not Active</option>
        </x-select>
    </div>

    <div class="mb-5">
        <x-label>
            {{ __('translation.Image') }} <x-form.required-mark />
        </x-label>
        <x-input type="file" onchange="editPreviewFile(this);" name="image" id="edit_image" value="{{ old('image') }}" />
    </div>

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

<div class="flex justify-end py-6">
    <x-button type="reset" color="gray" class="mr-1" id="__btnCancelModalUpdate">
        {{ __('translation.Cancel') }}
    </x-button>
    <x-button type="submit" color="blue" id="__btnUpdateSubmit">
        {{ __('translation.Update') }}
    </x-button>
</div>

<script>
    $(document).ready(function() {
        $('#closeModalUpdate').click(function() {
            $('body').removeClass('modal-open');
            $('#__modalUpdate').addClass('modal-hide');
        });

        $('#__btnCancelModalUpdate').click(function() {
            $('body').removeClass('modal-open');
            $('#__modalUpdate').addClass('modal-hide');
        });

        new lc_select('select[id="form_edit_product_tag"]', {
            wrap_width : '100%',
            enable_search : true,
        });


        $('#__btnAddNewShopPriceEdit').click(function() {
            let newShopPriceTemplate = $('#__newShopPriceTemplateEdit').html();
            $('#__wrapperAdditionalShopPriceEdit').append(newShopPriceTemplate);

            initialRemoveShopPriceButtonEdit();
        });

        const initialRemoveShopPriceButtonEdit = () => {
            $('.__btnRemoveShopPriceEdit').click(function() {
                $(this).parents(".additional-shop-price--edit").remove();
            });
        }

        initialRemoveShopPriceButtonEdit();
    });

    $(document).ready(function () {
        $('#edit_parent_category_id').on('change', function () {
            var idParent = this.value;
            $("#edit_category_id").html('');
            $.ajax({
                url: "{{route('fetch sub categories')}}",
                type: "POST",
                data: {
                    parent_category_id: idParent,
                    _token: '{{csrf_token()}}'
                },
                dataType: 'json',
                success: function (result) {
                    $('#edit_category_id').html('<option value="">Select Sub Category</option>');
                    $.each(result.sub_categories, function (key, value) {
                        $("#edit_category_id").append('<option value="' + value
                            .id + '">' + value.cat_name + '</option>');
                    });
                }
            });
        });
    });

    function editPreviewFile(input){
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

</script>
