@csrf
<div>
    <input type="hidden" name="id" value="{{ $id }}">
    <x-label>
        Name
    </x-label>
    <x-input type="text" name="name" id="name" :value="old('name') ?? $data->buyer" required>
    </x-input>
</div>
<div class="mt-6">
    <x-label>
        Phone
    </x-label>
    <x-input type="text" name="phone" id="phone" :value="old('phone') ?? $data->phone"
    required>
</x-input>
</div>
<div class="mt-6">
    <x-label>
        Tracking Id
    </x-label>
    <x-input type="text" name="tracking-id" id="trackingId" :value="old('tracking-id') ?? $data->tracking_id"
    required>
</x-input>
</div>
<div class="mt-6">
    <x-label>
        Shipper
    </x-label>
    <x-select name="shipper" id="shipper">
        @foreach ($shippers as $shipper)
        <option value="{{ $shipper->id }}" @if($data->shipper_id == $shipper->id) selected @endif> {{ $shipper->name }} </option>
        @endforeach
    </x-select>
</div>
<div class="flex justify-end mt-4">
<x-button color="blue">Save</x-button>
</div>