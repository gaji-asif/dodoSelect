@csrf
<input type="hidden" name="id" id="product_id" value="{{ $product->id }}">

<div class="mb-5">
    <x-label>
        {{ __('translation.Select Product Tag') }}
    </x-label>
    <select name="product_tag[]" id="table_edit_product_tag" data-placeholder="Select Product Tag" multiple>
        @if (isset($product_tags))
            @foreach ($product_tags as $product_tag)
                <option value="{{$product_tag->id}}" @foreach($product->productTags as $tag){{$tag->id == $product_tag->id  ? 'selected': ''}} @endforeach>{{$product_tag->name}}</option>
            @endforeach
        @endif
    </select>
</div>

<div class="flex justify-end py-6">
    <x-button type="reset" color="gray" class="mr-1" id="btnCancelModalProductTag">
        {{ __('translation.Cancel') }}
    </x-button>
    <x-button type="submit" color="blue" id="btnProductTagSubmit">
        {{ __('translation.Update') }}
    </x-button>
</div>

<script>
    $(document).ready(function() {
        $('#btnCancelModalProductTag').click(function() {
            $('body').removeClass('modal-open');
            $('.modal-product-tag').addClass('modal-hide');
        });

        new lc_select('select[id="table_edit_product_tag"]', {
            wrap_width : '100%',
            enable_search : true,
        });
    });
</script>
