@csrf

<div class="mt-6">
    <input type="hidden" name="id" value="{{ $id }}">
    <x-label>
        Quantity update
    </x-label>
    <x-input type="text" name="quantity" id="quantity" :value="old('quantity') ?? $data->quantity" required>
    </x-input>
</div>


<div class="flex justify-end py-4">
    <x-button color="blue">Update</x-button>
</div>
