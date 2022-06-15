@csrf

<div >
    <input type="hidden" name="id" value="{{ $id }}">
    <x-label>
        Full Name
    </x-label>
    <x-input type="text" name="name" id="name" :value="old('name') ?? $data->name" required>
    </x-input>
</div>
<div class="mt-6">
    <x-label>
       Phone
    </x-label>
    <x-input type="text" name="phone" id="phone" :value="old('phone') ?? $data->phone" required>
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
    <x-label>
        Role
    </x-label>
    <x-select name="role" id="role" required>
        <option disabled selected value="0">
            {{ __('translation.Select Role') }}
        </option>
        @foreach ($roles as $role)
            <option value="{{ $role->id }}" @if ($data->getRoleNames()->first() == $role->name) selected @endif> {{ $role->name }} </option>
        @endforeach
    </x-select>
</div>

{{-- <div class="mt-6">
    <div class="mt-6">
        <x-label>
            Password
        </x-label>
        <x-input placeholder="(unchanged)" type="password" name="password" id="password" :value="old('password')">
        </x-input>
    </div>
</div> --}}
<div class="mt-6">
    <x-label>
        Address
    </x-label>
    <x-textarea  name="address" id="address">@if(isset($data->address)) {{$data->address}} @else {{old('address')}} @endif </x-textarea>
</div>

<div class="flex justify-end py-4">
    <x-button color="blue">Update</x-button>
</div>
