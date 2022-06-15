@csrf
<input type="hidden" name="id" value="{{ $id }}">

@if ($product->parent_id != 0)
    <x-alert-danger>
        {{ ucfirst(__('translation.variable_product_name_can_not_be_updated_from_the_child')) }}
    </x-alert-danger>
@endif
@if ($product->parent_id == 0 && $product->type == 'variable')
    <x-alert-danger>
        {{ ucfirst(__('translation.qty_or_Price_does_not_allow_to_change_from_parent_product')) }}
    </x-alert-danger>
@endif

<div class="grid grid-cols-2 gap-4 gap-x-8">
    <div class="col-span-2">
        <x-label>
            {{ ucwords(__('translation.product')) . ' ID' }} <x-form.required-mark/>
        </x-label>
        <x-input type="text" class="bg-gray-200" value="{{ '#' . $id }}" readonly />
    </div>
    <div class="col-span-2">
        <x-label>
            {{ ucwords(__('translation.product_name')) }} <x-form.required-mark/>
        </x-label>
        @if ($product->parent_id != 0)
            <x-input type="text" name="name" id="name" :value="old('name') ?? $product->product_name" class="bg-gray-200" required readonly />
        @else
            <x-input type="text" name="name" id="name" :value="old('name') ?? $product->product_name" required />
        @endif
    </div>
    <div class="col-span-2">
        <x-label>
            {{ ucwords(__('translation.product_code')) }} <x-form.required-mark/>
        </x-label>
        @if ($product->parent_id != 0)
            <x-input type="text" name="sku" id="sku" :value="old('sku') ?? $product->product_code" class="bg-gray-200" required readonly />
        @else
            <x-input type="text" name="sku" id="sku" :value="old('sku') ?? $product->product_code" required />
        @endif
    </div>
    <div>
        <x-label>
            {{ ucwords(__('translation.price')) }} <x-form.required-mark/>
        </x-label>
        @if ($product->parent_id == 0 && $product->type == 'variable')
            <x-input type="number" name="price" id="price" :value="old('price') ?? $product->price" class="bg-gray-200" required readonly />
        @else
            <x-input type="number" name="price" id="price" :value="old('price') ?? $product->price" steps="0.001" required />
        @endif
    </div>
    <div>
        <x-label>
            {{ ucwords(__('translation.quantity')) }} <x-form.required-mark/>
        </x-label>
        @if ($product->parent_id == 0 && $product->type == 'variable')
            <x-input type="number" name="quantity" id="quantity" :value="old('quantity') ?? $product->quantity" class="bg-gray-200" required readonly />
        @else
            <x-input type="number" name="quantity" id="quantity" :value="old('quantity') ?? $product->quantity" required />
        @endif
    </div>
</div>

<div class="mt-5 pb-3">
    <div class="flex flex-row items-center justify-center gap-2">
        <x-button type="reset" color="gray" class="btn-close_edit-product" id="__btnCancelUpdateproduct">
            {{ __('translation.cancel') }}
        </x-button>
        <x-button type="submit" color="blue" data-website_id="{{$product->website_id}}" data-id="{{$product->product_id}}" id="btnSubmitProduct">
            {{ __('translation.update_data') }}
        </x-button>
    </div>
</div>
