@csrf

<div>
    <input type="hidden" name="id" value="{{ $id }}">
    <x-label>
        Role Name
    </x-label>
    <x-input type="text" name="name" id="name" :value="old('name') ?? $data->name" required autocomplete="off">
    </x-input>
</div>
<div class="mt-6">
    <x-label>
        Description
    </x-label>
    <x-textarea  name="description" id="description">@if(isset($data->description)) {{$data->description}} @else {{old('description')}} @endif </x-textarea>
</div>

<div class="flex justify-end py-4">
    <x-button color="blue">Update</x-button>
</div>
