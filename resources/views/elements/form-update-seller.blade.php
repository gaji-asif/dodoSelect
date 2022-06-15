@csrf
<div>
    <input type="hidden" name="id" value="{{ $id }}">
    <x-label>
        Shop ID
    </x-label>
    <x-input type="text" name="shop_id" id="shop_id" :value="old('shop_id') ?? $data->shop_id" required>
    </x-input>
</div>
<div class="mt-6">
    <input type="hidden" name="id" value="{{ $id }}">
    <x-label>
        Name
    </x-label>
    <x-input type="text" name="name" id="name" :value="old('name') ?? $data->name" required>
    </x-input>
</div>
<div class="mt-6">
    <x-label>
        Email
    </x-label>
    <x-input type="text" name="email" id="email" :value="old('email') ?? $data->email" required>
    </x-input>
</div>
<div class="mt-6">
    <x-label>
        Status
    </x-label>
    <x-select name="is_active" id="shipper">
        <option value="1" @if ($data->is_active == '1') selected @endif>Active</option>
        <option value="0" @if ($data->is_active == '0') selected @endif>Suspended</option>
    </x-select>
</div>

<div class="mt-6">
    <div class="mt-6">
        <x-label>
            Password
        </x-label>
        <x-input placeholder="(unchanged)" type="password" name="password" id="password" :value="old('password')">
        </x-input>
    </div>
</div>

<div class="flex justify-end py-4">
    <x-button color="blue">Update</x-button>
</div>
