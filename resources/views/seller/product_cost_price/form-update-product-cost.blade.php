@csrf

<div class="mt-4" style="width: 49%; float: left; margin-right: 1%;">
    <input type="hidden" name="id" id="id" value="{{ $id }}">
    <x-label>
        Cost Price
    </x-label>
    <x-input  type="number" step="0.01" name="cost_price" id="cost_price" :value="old('cost_price') ?? $data->cost_price">
    </x-input>
</div>

<div style="width: 49%; float: left; margin-right: 1%; margin-top: 1.3rem !important;">
    <x-label>
        Cost Currency
    </x-label>
    <x-select name="cost_currency" id="cost_currency">
        <option value="" selected>
            {{ '- ' . __('translation.Select Currency') . ' -' }}
        </option>
        @foreach ($exchangeRates as $exchangeRate)
            <option value="{{ $exchangeRate->id }}" @if ($exchangeRate->id == $data->cost_currency) selected @endif>
                {{ $exchangeRate->name }}
            </option>
        @endforeach
    </x-select>
</div>

<div class="mt-4" style="width: 49%; float: left;">
    <x-label>
        Ship Cost
    </x-label>
    <x-input type="text" name="ship_cost" id="ship_cost" :value="old('ship_cost') ?? $data->ship_cost">
    </x-input>
</div>

<div class="mt-4" style="width: 49%; float: right; margin-right: 1%;">
    <x-label>
       Lowest Sell Price
    </x-label>
    <x-input type="text" name="lowest_value" id="lowest_value" :value="old('lowest_value') ?? $data->lowest_value">
    </x-input>
</div>

<div class="mt-4" style="width: 100%; float: left; margin-bottom: 10px;">
    <x-label>
       Supplier Name
    </x-label>
    <x-select name="supplier_id" id="supplier_id">
        <option disabled selected value="0">
            {{ __('translation.Select Supplier')}}
        </option>
        @foreach ($suppliers as $supplier)
            <option value="{{ $supplier->id }}" @if ($supplier->id === $supplier_id) selected @endif> {{ $supplier->supplier_name }} </option>
        @endforeach
    </x-select>
</div>



<div class="justify-end py-4"  style="width: 100%; float: left; margin-bottom: 10px;">
    <x-button color="blue" class="mt-3">Update</x-button>
</div>
