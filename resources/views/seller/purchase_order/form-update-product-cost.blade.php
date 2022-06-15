@csrf

<div class="mt-4" style="width: 100%; float: left;  margin-bottom: 10px; margin-right: 1%;">
    <input type="hidden" name="id" id="id" value="{{ $id }}">
    <x-label>
        Lowest Sell Price
    </x-label>
    <x-input  type="number" step="0.01" name="lowest_value" id="lowest_value" :value="old('lowest_value') ?? $data->lowest_value">
    </x-input>
</div>

<div class="justify-end py-4 ">
    <x-button color="blue" class="mt-3">Update</x-button>
</div>

