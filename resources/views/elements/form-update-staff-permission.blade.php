@csrf

<div >
    <input type="hidden" name="id" value="{{ $id }}">
    <x-label>
        Permission
    </x-label>
    <x-input type="text" name="name" id="name" :value="old('name') ?? $data->name" required autocomplete="off">
    </x-input>
</div>

<div class="flex justify-end py-4">
    <x-button color="blue">Update</x-button>
</div>
