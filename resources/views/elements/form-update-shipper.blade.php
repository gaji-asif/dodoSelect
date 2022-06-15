@csrf
<div>
    <input type="hidden" name="id" value="{{ $id }}">
    <x-label>
        Name
    </x-label>
    <x-input type="text" name="name" id="name" :value="old('name') ?? $data->name" required>
    </x-input>
</div>
<div class="flex justify-end mt-4">
<x-button color="blue">Save</x-button>
</div>