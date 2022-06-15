@csrf
<style type="text/css">
    .district_new, .sub_district_new, .province_new, .postcode_new{
        display: none;
    }
    .reset_button{
        border-radius: 5px;
    }
</style>

<input type="hidden" name="id" id="id" value="{{ $editData->id ?? '' }}">

<div class="grid md:grid-cols-2 md:gap-x-5">
    <div class="form-group">
        <label for="shop_id" class="font-weight-bold">Shop Name <span class="text-red-600">*</span></label>
        <x-select name="shop_id" id="__selectShop" required>
            <option disabled selected value="">{{ __('translation.Select Shop') }}</option>
            @if(isset($shops))
                @foreach ($shops as $shop)
                    <option value="{{ $shop->id }}" {{ isset($editData) && $editData->shop_id == $shop->id ? 'selected' : '' }}> {{ $shop->name }} </option>
                @endforeach
            @endif
        </x-select>
    </div>

    <div class="form-group">
        <label for="customer_id" class="font-weight-bold">Customer Name <span class="text-red-600">*</span></label>
        <x-select name="customer_id" id="__selectCustomer" required>
            <option value="" selected disabled>{{ '- ' . __('translation.Select Customer') . ' -' }}</option>
            @if(isset($customers))
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" {{ isset($editData) && $editData->customer_id == $customer->id ? 'selected' : '' }}> {{ $customer->customer_name }} ({{ $customer->contact_phone }})</option>
                @endforeach
            @endif
        </x-select>
    </div>

    <div class="form-group">
        <label for="contactname" class="font-weight-bold">Contact Name <span class="text-red-600">*</span></label>
        <x-input type="text" name='contactname' value="{{ $editData->contactname ?? old('contactname') }}" required></x-input>
    </div>

    <div class="form-group">
        <label for="role" class="font-weight-bold">Role</label>
        <x-select name="role" id="__selectRole" class="form-control">
            <option disabled selected value="">{{ __('translation.Select Role') }}</option>
            @if(isset($roles))
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" {{ isset($editData) && $editData->getRoleNames()->first() == $role->name ? 'selected' : '' }}> {{ $role->name }} </option>
                @endforeach
            @endif
        </x-select>
    </div>

    <div class="form-group">
        <label for="email" class="font-weight-bold">Email <span class="text-red-600">*</span></label>
        <x-input type="email" name='email' class="form-control" required value="{{ $editData->email ?? old('email') }}"></x-input>
    </div>

    <div class="form-group">
        <label for="phone" class="font-weight-bold">Phone <span class="text-red-600">*</span></label>
        <x-input type="text" name='phone' class="form-control" required value="{{ $editData->phone ?? old('phone') }}"></x-input>
    </div>

    @if(!isset($editData->phone))
    <div class="form-group">
        <label for="password" class="font-weight-bold">Password <span class="text-red-600">*</span></label>
        <x-input type="password" name='password' class="form-control" required value="{{ old('password') }}"></x-input>
    </div>
    @endif
</div>

<div class="form-group">
    <label for="address" class="font-weight-bold">Address</label>
    <x-input type="text" name='address' class="form-control" value="{{ $editData->dropshipperAddress->address ?? old('address') }}"></x-input>
</div>

<a style="float: right; cursor: pointer;" class="btn-action--red reset_button" onclick="reset_address();">Reset Address</a>
<br>

<div class="main_div main_div grid md:grid-cols-2 md:gap-x-5 w-full">
    <div class="form-group">
        <label for="district" class="font-weight-bold mb-1">District</label>
        <div id="the-basicss">
            <input type="text" class="typeaheads district form-control" id="district" name="district" value="{{ $editData->dropshipperAddress->district ?? '' }}">
            <input type="text" class="typeaheads district_new form-control" name="district_new" id="district_new">
        </div>
    </div>

    <div class="form-group">
        <label for="sub_district" class="font-weight-bold mb-1">Sub District</label>
        <div id="the-basicss1">
            <input type="text" class="typeaheads sub_district form-control" name="sub_district" id="sub_district" value="{{$editData->dropshipperAddress->sub_district ?? ''}}">
            <input type="text" class="typeaheads sub_district_new form-control" name="sub_district_new" id="sub_district_new">
        </div>
    </div>

    <div class="form-group">
        <label for="province" class="font-weight-bold mb-1">Province</label>
        <div id="the-basicss2">
            <input type="text" class="typeaheads province form-control" name="province" id="province" value="{{$editData->dropshipperAddress->province ?? ''}}">
            <input type="text" class="typeaheads province_new form-control" name="province_new" id="province_new">
        </div>
    </div>

    <div class="form-group">
        <label for="postcode" class="font-weight-bold mb-1">Postcode</label>
        <div id="the-basicss3">
            <input type="text" class="typeaheads postcode form-control" name="postcode" id="postcode" value="{{$editData->dropshipperAddress->postcode ?? ''}}">
            <input type="text" class="typeaheads postcode_new form-control" name="postcode_new" id="postcode_new">
        </div>
    </div>

    <input type="hidden" id="full_address" name="full_address" value="@if(isset($editData->dropshipperAddress->district)){{$editData->dropshipperAddress->district.'/'.$editData->dropshipperAddress->sub_district.'/'.$editData->dropshipperAddress->province.'/'.$editData->dropshipperAddress->postcode}}@endif">

</div>

@include('layouts.thailand_address')

<div class="grid md:grid-cols-2 md:gap-x-5">
    <div class="form-group">
        <label for="logo" class="font-weight-bold mb-1">Upload Logo</label>
        <input type="file" onchange="previewFile(this);" class="logo" name="logo" id="logo" style="height: auto">
    </div>
</div>

@if(!empty($editData->logo))
    <img id="previewImg" class="mt-2 previewImg" width="130" height="130" src="{{asset($editData->logo)}}" alt="image">
@else
    <img id="previewImg" class="mt-2 previewImg" width="130" height="130" src="{{asset('img/No_Image_Available.jpg')}}" alt="image">
@endif

<div class="flex justify-end py-6">
    <x-button type="reset" color="gray" class="mr-1 cancelModalUpdate" id="cancelModalUpdate">
        {{ __('translation.Cancel') }}
    </x-button>
    <x-button type="submit" color="blue">
        {{ __('translation.Submit') }}
    </x-button>
</div>

<script>
    $(document).ready(function() {
        $('.cancelModalUpdate').click(function() {
            $('body').removeClass('modal-open');
            $('.modal-insert').addClass('modal-hide');
            $('.modal-update').addClass('modal-hide');
        });
    });

    $(document).ready(function() {
        $('#__selectCustomer').select2({
            placeholder: '- Select Customer -',
        });
        $('#__selectShop').select2({
            placeholder: '- Select Shop -',
        });
        $('#__selectRole').select2({
            placeholder: '- Select Role -',
        });
    });

    function previewFile(input){
        var file = $(".logo").get(0).files[0];

        if(file){
            var reader = new FileReader();
            reader.onload = function(){
                $(".previewImg").attr("src", reader.result);
            }
            reader.readAsDataURL(file);
        }
    }
</script>
