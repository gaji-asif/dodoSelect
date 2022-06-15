@csrf

<div class="mt-4" style="width: 32%; float: left; margin-right: 1%;">
    <input type="hidden" name="id" id="id" value="{{ $id }}">
    <x-label>
        Low Stock Reorder
    </x-label>
    <x-input  type="number" step="0.01" name="low_stock_reorder" id="low_stock_reorder" :value="old('low_stock_reorder') ?? $data->low_stock_reorder">
    </x-input>
</div>
<div class="mt-4" style="width: 32%; float: left; margin-bottom: 10px; margin-right: 1%;">
    <x-label>
       Out of Stock Reorder
    </x-label>
    <x-input type="text" name="out_of_stock_reorder" id="out_of_stock_reorder" :value="old('out_of_stock_reorder') ?? $data->out_of_stock_reorder">
    </x-input>
</div>

<div class="mt-4" style="width: 32%; float: left; margin-bottom: 10px; margin-right: 1%;">
    <x-label>
       Supplier Name
    </x-label>
    <x-select name="supplier_id" id="supplier_id">
        <option disabled selected value="0">
            {{ __('translation.Select Supplier') }}
        </option>
        @foreach ($suppliers as $supplier)
        <option value="{{ $supplier->id }}" @if ($supplier->id === $supplier_id) selected @endif> {{ $supplier->supplier_name }} </option>
        @endforeach
    </x-select>
</div>


<div class="justify-end py-4 ">
    <x-button color="blue" class="mt-3">Update</x-button>
</div>
