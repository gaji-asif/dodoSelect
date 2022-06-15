@csrf

<button type="button" class="btn-action--blue" data-id="{{$id}}" id="__btnAddReorderInput">
<i class="fas fa-plus"></i>
</button>

<div id="reorderData" class="w-full">
<input type="hidden" name="product_id" class="product_id" value="{{$id}}">
@if(isset($product_reorders))
    @foreach($product_reorders as $product_reorder)
    <div class="item w-full">
        <div class="mt-4" style="width: 30%; float: left;  margin-bottom: 10px; margin-right: 1%;">
            <x-label>Status </x-label>
            <x-select name="status[]" class=" relative top-1">
                <option value="" selected disabled>
                    {{ '- ' . __('translation.Select Status') . ' -' }}
                </option>
                <option value="low_stock" @if($product_reorder->status=='low_stock') selected @endif >Low Stock</option>
                <option value="out_of_stock" @if($product_reorder->status=='out_of_stock') selected @endif >Out Of Stock</option>
                <option value="over_stock" @if($product_reorder->status=='over_stock') selected @endif >Over Stock</option>
            </x-select>
        </div>
        <div class="mt-4" style="width: 30%; float: left;  margin-bottom: 10px; margin-right: 1%;">
            <x-label>
                Type
            </x-label>
            <x-select name="type[]" class="type relative top-1">
                <option value="" selected disabled>
                    {{ '- ' . __('translation.Select Ship Type') . ' -' }}
                </option>
                @if(isset($shipTypes))
                    @foreach($shipTypes as $shiptype)
                    <option value="{{$shiptype->id}}" @if($shiptype->id == $product_reorder->type) selected @endif >{{$shiptype->name}}</option>
                    @endforeach
                @endif
            </x-select>
        </div>

        <div class="mt-4" style="width: 30%; float: left;  margin-bottom: 10px; margin-right: 1%;">
            <x-label>Qty(Pieces) </x-label>
            <x-input  type="text" step="0.01" name="quantity[]" id="quantity" :value="old('$quantity') ?? $product_reorder->quantity">
            </x-input>
        </div>
        <div class="mt-4" style="width: 5%; float: left;  margin-bottom: 10px; margin-right: 1%;">
        <x-label>  &nbsp;  </x-label>
            <button type="button" class="btn-action--red mt-2" data-id="1" id="btnRemoveReorder"><i class="fas fa-trash-alt"></i></button>
        </div>
    </div>
    @endforeach
    @endif

</div>

<div class="justify-end py-4 ">
    <x-button color="blue" class="mt-3">Update</x-button>
</div>




