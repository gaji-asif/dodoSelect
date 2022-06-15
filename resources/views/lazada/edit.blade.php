@csrf
<input type="hidden" name="id" value="{{ $id }}">

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
        @if ($wooProduct->parent_id)
            <x-input type="text" name="name" id="name" :value="old('name') ?? $wooProduct->product_name" class="bg-gray-200" required readonly />
        @else
            <x-input type="text" name="name" id="name" :value="old('name') ?? $wooProduct->product_name" required />
        @endif
    </div>
    <div class="col-span-2">
        <x-label>
            {{ ucwords(__('translation.product_code')) }} <x-form.required-mark/>
        </x-label>
        <x-input type="text" name="sku" id="sku" :value="old('sku') ?? $wooProduct->product_code" required />
    </div>
    <div>
        <x-label>
            {{ ucwords(__('translation.price')) }} <x-form.required-mark/>
        </x-label>
        <x-input type="number" name="price" id="price" :value="old('price') ?? $wooProduct->price" steps="0.001" required />
    </div>
    <div>
        <x-label>
            {{ ucwords(__('translation.quantity')) }} <x-form.required-mark/>
        </x-label>
        <x-input type="number" name="quantity" id="quantity" :value="old('quantity') ?? $wooProduct->quantity" required />
    </div>
</div>

<div class="mt-5 pb-3">
    <div class="flex flex-row items-center justify-center gap-2">
        <x-button type="reset" color="gray" class="btn-close_edit-product" id="__btnCancelUpdateLazadaProduct">
            {{ __('translation.cancel') }}
        </x-button>
        {{--<x-button type="submit" color="blue" data-website_id="{{$wooProduct->website_id}}" data-id="{{$wooProduct->product_id}}" id="btnSubmitProduct">
            {{ __('translation.update_data') }}
        </x-button>--}}
    </div>
</div>
