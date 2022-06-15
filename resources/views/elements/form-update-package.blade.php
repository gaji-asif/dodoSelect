@csrf

<div class="mt-6">
    <input type="hidden" name="id" value="{{ $id }}">
    <x-label>
        Name
    </x-label>
    <x-input type="text" name="name" id="name" :value="old('name') ?? $data->package_name" required>
    </x-input>
</div>
<div class="mt-6">
    <x-label>
        Price
    </x-label>
    <x-input type="text" name="price" id="price" :value="old('price') ?? $data->price" required>
    </x-input>
</div>
{{-- <div class="mt-6">
    <x-label>
        Details
    </x-label>
    <textarea name="details" id="details" class="border-radius border-gray-300" cols="45" rows="5">@if(isset($data->details)) {{$data->details}} @else {{old('details')}} @endif </textarea>
    <x-input type="text" name="details" id="details" :value="old('details') ?? $data->details" >
    </x-input>
</div> --}}

<div class="mt-6">
    <x-label>
        Max Limit
    </x-label>
    <x-input type="text" name="max_limit" id="max_limit" :value="old('max_limit')  ?? $data->max_limit">
    </x-input>
</div>
<div class="mt-6">
    <x-label>
        Package Type
    </x-label>
    <x-select name="package_type" id="shipper">
      <option disabled selected value="0">Package Type</option>
       @if($data->package_type == 1)
            <option value="1" selected>Daily</option>
            <option value="2">Monthly</option>
       @else
            <option value="1" >Daily</option>
            <option value="2" selected>Monthly</option>
       @endif
      
    </x-select>
  </div>

<div class="flex justify-end py-4">
    <x-button color="blue">Update</x-button>
</div>
